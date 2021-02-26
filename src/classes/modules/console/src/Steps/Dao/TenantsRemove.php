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

namespace idoit\Module\Console\Steps\Dao;

use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use idoit\Module\License\LicenseService;
use isys_component_dao_mandator;
use isys_component_database;

class TenantsRemove implements Step, Undoable
{
    /**
     * @var isys_component_dao_mandator
     */
    private $dao;

    /**
     * @var LicenseService
     */
    private $licenseService;

    public function __construct(isys_component_database $database, LicenseService $licenseService)
    {
        $this->dao = isys_component_dao_mandator::instance($database);
        $this->licenseService = $licenseService;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Remove Tenants';
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
        $fetch = $this->dao->get_mandator(null, false);
        while ($mandator = $fetch->get_row()) {
            $step = new TenantRemove($this->dao->get_database_component(), $mandator['isys_mandator__id'], $this->licenseService);
            if (!$step->process($messages)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Undo the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function undo(Messages $messages)
    {
        return true;
    }
}
