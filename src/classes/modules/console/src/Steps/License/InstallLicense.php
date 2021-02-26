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

namespace idoit\Module\Console\Steps\License;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseServiceFactory;
use isys_component_dao_mandator;
use isys_component_database;

class InstallLicense implements Step
{
    /**
     * @var isys_component_database
     */
    private $database;

    private $licenseToken;

    private $path;

    /**
     * @var null
     */
    private $tenantId;

    public function __construct(isys_component_database $database, $path, $licenseToken, $tenantId = null)
    {
        $this->path = $path;
        $this->tenantId = $tenantId;
        $this->database = $database;
        $this->licenseToken = $licenseToken;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Install license from path: ' . $this->path;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function process(Messages $messages)
    {
        $db = null;
        if ($this->tenantId) {
            $tenant = isys_component_dao_mandator::instance($this->database)
                ->get_mandator($this->tenantId, false)->get_row();
            if (!empty($tenant) && is_countable($tenant)) {
                $db = isys_component_database::factory(
                    'mysql',
                    $tenant['isys_mandator__db_host'],
                    $tenant['isys_mandator__db_port'],
                    $tenant['isys_mandator__db_user'],
                    isys_component_dao_mandator::getPassword($tenant),
                    $tenant['isys_mandator__db_name']
                );
            }
        }
        $licenseService = LicenseServiceFactory::createDefaultLicenseService($this->database, $this->licenseToken);

        try {
            $license = $licenseService->parseLicenseFile($this->path);
            if (is_array($license)) {
                $messages->addMessage(new StepMessage($this, 'License is parsed as a prevgen license', ErrorLevel::DEBUG));

                $licenseService->installLegacyLicense($license);
                $messages->addMessage(new StepMessage($this, 'License is installed', ErrorLevel::DEBUG));
            } elseif (is_object($license)) {
            }
        } catch (\Exception $exception) {
            $messages->addMessage(new StepMessage($this, $exception->getMessage(), ErrorLevel::ERROR));
            return false;
        }

        return true;
    }
}
