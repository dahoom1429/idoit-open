<?php

namespace idoit\Console\Command\Idoit;

use Exception;
use idoit\Console\Command\AbstractCommand;
use isys_settings;
use isys_update;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCheckCommand extends AbstractCommand
{
    const NAME           = 'system-checkforupdates';
    const REQUIRES_LOGIN = false;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Checks for i-doit core updates';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition();
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $g_absdir, $g_product_info;

        $newUpdate = null;

        // Add a "green" success format.
        $output->getFormatter()
            ->setStyle('green', new OutputFormatterStyle('green'));

        if (!extension_loaded('curl')) {
            throw new Exception('You need to install the php-curl extension in order to run this script!');
        }

        if (file_exists($g_absdir . '/updates/classes/isys_update.class.php')) {
            include_once($g_absdir . '/updates/classes/isys_update.class.php');

            $updater = new isys_update;

            $output->writeln('Checking... Please wait..');

            // @see  ID-6872  System settings can now provide a update-XML URL.
            if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
                $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
            } else {
                $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
            }

            $versionResponse = $updater->fetch_file($updateXmlUrl);

            $versions = $updater->get_new_versions($versionResponse);
            $systemInfo = $updater->get_isys_info();

            if (is_array($versions) && count($versions) > 0) {
                foreach ($versions as $version) {
                    if ($systemInfo['revision'] < $version['revision']) {
                        $newUpdate = $version;
                    }
                }
            } else {
                throw new Exception('Update check failed. Is the i-doit server connected to the internet?');
            }
        }

        if ($newUpdate !== null) {
            $output->writeln([
                '',
                'There is a new i-doit version available: ' . $newUpdate['version'],
                'Your current version is: ' . $g_product_info['version'],
                'Use the i-doit updater to update to the latest version.'
            ]);
        } else {
            $output->writeln('<green>You already got the latest i-doit version (' . $g_product_info['version'] . ')</green>');
        }
        return Command::SUCCESS;
    }
}
