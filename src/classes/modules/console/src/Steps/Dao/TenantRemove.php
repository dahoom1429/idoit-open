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
use idoit\Module\Console\Steps\Sql\DropDatabase;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use idoit\Module\License\Event\Tenant\TenantDeletedEvent;
use idoit\Module\License\LicenseService;
use isys_component_dao_mandator;
use isys_component_database;

class TenantRemove implements Step, Undoable
{
    /**
     * @var isys_component_dao_mandator
     */
    private $dao;

    private $id;

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
        return 'Remove Tenant ' . $this->id;
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
        $mandator = $this->dao->get_mandator($this->id, false)->get_row();
        if (empty($mandator)) {
            $messages->addMessage(new StepMessage($this, 'already removed', ErrorLevel::INFO));
            return true;
        }

        $dropDb = new DropDatabase(
            $mandator['isys_mandator__db_host'],
            $mandator['isys_mandator__db_user'],
            isys_component_dao_mandator::getPassword($mandator),
            $mandator['isys_mandator__db_name'],
            $mandator['isys_mandator__db_port']
        );
        if (!$dropDb->process($messages)) {
            return false;
        }
        $result = $this->dao->delete($this->id);

        $messages->addMessage(new StepMessage($this, $result ? 'deleted' : 'did not delete', $result ? ErrorLevel::INFO : ErrorLevel::ERROR));

        $this->licenseService->getEventDispatcher()->dispatch(
            new TenantDeletedEvent(),
            TenantDeletedEvent::NAME
        );

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
        return true;
    }
}
