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

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use idoit\Module\License\Event\Tenant\TenantDeactivatedEvent;
use idoit\Module\License\LicenseService;
use isys_component_dao_mandator;
use isys_component_database;

class TenantDisable implements Step, Undoable
{
    /**
     * @var isys_component_dao_mandator
     */
    private $dao;

    private $id;

    private $done;

    /**
     * @var LicenseService
     */
    private $licenseService;

    public function __construct(isys_component_database $database, $id, LicenseService $licenseService)
    {
        $this->dao = isys_component_dao_mandator::instance($database);
        $this->id = $id;
        $this->licenseService = $licenseService;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Disable Tenant ' . $this->id;
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
        $this->done = false;
        $mandator = $this->dao->get_mandator($this->id, false)->get_row();
        if (empty($mandator)) {
            $messages->addMessage(new StepMessage($this, 'does not exist', ErrorLevel::ERROR));
            return false;
        }
        if (isset($mandator['isys_mandator__active']) && !$mandator['isys_mandator__active']) {
            $messages->addMessage(new StepMessage($this, 'is already deactivated', ErrorLevel::INFO));
            return true;
        }
        $result = $this->dao->deactivate_mandator($this->id);
        if ($result) {
            $this->done = true;
        }
        $this->licenseService->getEventDispatcher()->dispatch(
            new TenantDeactivatedEvent(),
            TenantDeactivatedEvent::NAME
        );

        $messages->addMessage(new StepMessage($this, $result ? 'deactivated' : 'is not deactivated', $result ? ErrorLevel::INFO : ErrorLevel::ERROR));

        return $result;
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
        if ($this->done) {
            $result = $this->dao->activate_mandator($this->id);
            $messages->addMessage(new StepMessage($this, $result ? 'activated' : 'is not activated', $result ? ErrorLevel::INFO : ErrorLevel::ERROR));
            return $result;
        }
        return true;
    }
}
