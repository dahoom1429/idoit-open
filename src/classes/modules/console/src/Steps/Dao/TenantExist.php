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
use isys_component_dao_mandator;
use isys_component_database;

class TenantExist implements Step, Undoable
{
    /**
     * @var isys_component_dao_mandator
     */
    private $dao;

    private $dbName;

    private $title;

    public function __construct(
        isys_component_database $database,
        $title,
        $dbName
    ) {
        $this->dao = isys_component_dao_mandator::instance($database);
        $this->title = $title;
        $this->dbName = $dbName;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Check tenant exist with ' . $this->title . ' and DB ' . $this->dbName;
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
        $exists = $this->dao->retrieve("SELECT COUNT(1) AS exist FROM isys_mandator WHERE isys_mandator__db_name = '{$this->dbName}' OR isys_mandator__title = '{$this->title}';")
                ->get_row()['exist'] > 0;

        $messages->addMessage(new StepMessage($this, $exists ? 'exists' : 'does not exist', $exists ? ErrorLevel::INFO : ErrorLevel::ERROR));

        return $exists;
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
