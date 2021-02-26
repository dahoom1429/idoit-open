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
use idoit\Module\License\LicenseService;
use isys_component_dao_mandator;
use isys_component_database;
use isys_module_licence;

class AssignLicense implements Step
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var LicenseService
     */
    private $licenseService;

    private $tenantId;

    public function __construct(LicenseService $licenseService, $tenantId, $count)
    {
        $this->tenantId = $tenantId;
        $this->count = $count;
        $this->licenseService = $licenseService;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Assign ' . $this->count . ' licensed objects to tenant ' . $this->tenantId;
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
        try {
            $messages->addMessage(new StepMessage($this, '', ErrorLevel::INFO));
            return $this->licenseService->setLicenseObjectsForTenants([$this->tenantId => $this->count], [$this->tenantId]);
        } catch (\Exception $e) {
            $messages->addMessage(new StepMessage($this, $e->getMessage(), ErrorLevel::ERROR));
        }

        return false;
    }
}
