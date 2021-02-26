<?php

namespace idoit\Module\Console\Console\Command\Addon;

use Exception;
use isys_application;
use isys_component_dao_mandator;
use isys_component_database;
use isys_module_manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListAddonsCommand extends Command
{
    /**
     * Pre configure child commands
     */
    protected function configure()
    {
        $this->setName('addon-list');
        $this->setDescription('Shows list of installed addons');
        $this->addOption('tenant', 't', InputOption::VALUE_OPTIONAL, 'Tenant Id');
        $this->addOption('addon', 'a', InputOption::VALUE_OPTIONAL, 'Add-on Id');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addonId = $input->getOption('addon');
        $language = isys_application::instance()->container->get('language');
        $table = new Table($output);
        $exitCode = Command::SUCCESS;

        $output->writeln('<info>Available add-ons:</info>');

        $table->setHeaders(['ID', 'Name', 'Version', 'Tenant', 'Licensed', 'Installed']);
        global $g_absdir;
        $modules = $this->findModulesInFolder($g_absdir . '/src/classes/modules/');

        $rows = [];

        $dao = new isys_component_dao_mandator(isys_application::instance()->container->get('database_system'));
        $tenants = $dao->get_mandator($input->getOption('tenant'), 0);

        try {
            while ($tenantData = $tenants->get_row()) {
                $license = $tenantData['isys_licence__data'];
                $tenantTitle = $tenantData['isys_mandator__id'] . ': ' . $tenantData['isys_mandator__title'];
                try {
                    $license = unserialize($license);
                    $licensedAddons = array_keys($license[C__LICENCE__DATA]);
                    if ($license) {
                        $licensedAddons[] = 'pro';
                    }
                } catch (Exception $e) {
                    $output->writeln('Cannot get the license of tenant ' . $tenantTitle, OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }

                $db = isys_component_database::factory(
                    'mysql',
                    $tenantData['isys_mandator__db_host'],
                    $tenantData['isys_mandator__db_port'],
                    $tenantData['isys_mandator__db_user'],
                    isys_component_dao_mandator::getPassword($tenantData),
                    $tenantData['isys_mandator__db_name']
                );
                if (!$db->is_connected()) {
                    $output->writeln('Cannot connect to the database of tenant ' . $tenantTitle, OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }
                $manager = new isys_module_manager($db);
                foreach ($modules as $id => $module) {
                    if ($addonId && $id !== $addonId) {
                        continue;
                    }
                    $licensed = !isset($module['licence']) || in_array($module['licence'], $licensedAddons, true);
                    $rows[] = [
                        $module['identifier'],
                        $language->get($module['name']),
                        $module['version'],
                        $tenantTitle,
                        $licensed ? '<fg=black;bg=green>Licensed</fg=black;bg=green>' : '<error>Not Licensed</error>',
                        $manager->is_installed($module['identifier']) ? ($manager->is_active($module['identifier']) ? '<fg=black;bg=green>Active</fg=black;bg=green>' : '<fg=black;bg=green>Installed</fg=black;bg=green>') : '<error>Not Installed</error>',
                    ];
                }
                foreach ($licensedAddons as $addon) {
                    if ($addonId && $id !== $addonId) {
                        continue;
                    }
                    if (!array_key_exists($addon, $modules)) {
                        $rows[] = [
                            $addon,
                            $addon,
                            '?',
                            $tenantTitle,
                            '<fg=black;bg=green>Licensed</fg=black;bg=green>',
                            '<error>Missing</error>'
                        ];
                    }
                }
                $db->close();
            }

            $table->setRows($rows);
            $table->render();
        } catch (Exception $e) {
            $output->writeln('<error>Something went wrong with message: ' . $e->getMessage() . '</error>');
            $exitCode = Command::FAILURE;
        }

        return $exitCode;
    }

    /**
     * Find add-ons in the given directory
     *
     * @param $folder
     *
     * @return array
     */
    private function findModulesInFolder($folder)
    {
        // Create directory handle
        $handle = opendir($folder);
        $modules = [];

        while (($file = readdir($handle)) !== false) {
            if (is_dir($folder . $file) && !in_array($file, ['.', 'open'], true)) {
                $packageFile = $folder . $file . '/package.json';
                if (!file_exists($packageFile)) {
                    continue;
                }
                try {
                    $package = \GuzzleHttp\json_decode(file_get_contents($packageFile), true);
                } catch (\Exception $ex) {
                    continue;
                }
                if ($package['type'] === 'core') {
                    continue;
                }
                $modules[$package['identifier']] = $package;
            }
        }

        closedir($handle);
        unset($handle);

        return $modules;
    }
}
