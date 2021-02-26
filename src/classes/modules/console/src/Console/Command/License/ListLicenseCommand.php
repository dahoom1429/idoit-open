<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command\License;

use Exception;
use Carbon\Carbon;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use isys_module_manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListLicenseCommand extends Command
{
    /**
     * Pre configure child commands
     */
    protected function configure()
    {
        $this->setName('license-list');
        $this->setDescription('List of the licenses:  id, type of license, assigned tenant, organisation, valid from, expire date, amount of licensed objects, amount of used objects, licensed add-ons');
        $this->addOption('tenant', 't', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Id of assigned tenant to filter', []);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        isys_module_manager::instance()->module_loader();
        $table = new Table($output);
        $exitCode = Command::SUCCESS;

        $output->writeln('<info>Available licenses:</info>');

        $table->setHeaders(['ID', 'Product', 'Type', 'From', 'Expire', 'Licensed', 'Tenants', 'Environment']);

        $rows = [];

        $db = isys_application::instance()->container->get('database_system');
        $tenants = $input->getOption('tenant');
        global $g_license_token;

        try {
            $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token);

            // New licenses
            $licenseEntities = $licenseService->getLicenses();

            foreach ($licenseEntities as $id => $licenseEntity) {
                $start = Carbon::createFromTimestamp($licenseEntity->getValidityFrom()
                    ->getTimestamp());
                $end = Carbon::createFromTimestamp($licenseEntity->getValidityTo()
                    ->getTimestamp());

                $invalid = !(Carbon::now()
                    ->between($start, $end));

                $start = $start->format('l, F j, Y');
                $end = $end->format('l, F j, Y');

                $rows[] = [
                    $id,
                    $licenseEntity->getProductName() ?: $licenseEntity->getProductIdentifier(),
                    $licenseEntity->getProductType(),
                    $start,
                    $end,
                    $licenseEntity->getObjects(),
                    $licenseEntity->getTenants(),
                    $licenseEntity->getEnvironment(),
                ];
            }

            $oldLicenses = $licenseService->getLegacyLicenses();

            foreach ($oldLicenses as $oldLicense) {
                $start = Carbon::createFromTimestamp($oldLicense[LicenseService::C__LICENCE__REG_DATE]);
                $end = Carbon::parse($oldLicense[LicenseService::LEGACY_LICENSE_EXPIRES]);

                $invalid = !(\Carbon\Carbon::now()
                    ->between($start, $end));

                $start = $start->format('l, F j, Y');
                $end = $end->format('l, F j, Y');

                $label = 'Subscription (Classic)';
                $tenants = 1;

                if (in_array($oldLicense[LicenseService::LEGACY_LICENSE_TYPE], LicenseService::LEGACY_LICENSE_TYPES_HOSTING, false)) {
                    $label = 'Hosting (Classic)';
                    $tenants = 50;
                }

                $product = 'i-doit';
                if (!empty($oldLicense[LicenseService::C__LICENCE__DATA])) {
                    $product .= ' (+' . implode(', ', array_keys($oldLicense[LicenseService::C__LICENCE__DATA])) . ')';
                }
                $rows[] = [
                    $oldLicense[LicenseService::LEGACY_LICENSE_ID],
                    $product,
                    $label,
                    $start,
                    $end,
                    $oldLicense[LicenseService::C__LICENCE__OBJECT_COUNT],
                    $tenants,
                    'production',
                    //                $invalid
                ];
            }

            $table->setRows($rows);
            $table->render();
        } catch (Exception $e) {
            $output->writeln('<error>Something went wrong with message: ' . $e->getMessage() . '</error>');
            $exitCode = Command::FAILURE;
        }

        return $exitCode;
    }
}
