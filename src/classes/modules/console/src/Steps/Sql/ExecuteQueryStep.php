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

namespace idoit\Module\Console\Steps\Sql;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use isys_component_database;

class ExecuteQueryStep implements Step, Undoable
{
    /**
     * @var isys_component_database
     */
    private $database;

    private $query;

    /**
     * @var null
     */
    private $undoQuery;

    public function __construct(isys_component_database $database, $query, $undoQuery = null)
    {
        $this->database = $database;
        $this->query = $query;
        $this->undoQuery = $undoQuery;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Execute sql query';
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
        $messages->addMessage(new StepMessage($this, 'Execute query ' . $this->query, ErrorLevel::DEBUG));
        try {
            return $this->database->query($this->query);
        } catch (\Exception $exception) {
            $messages->addMessage(new StepMessage($this, $exception->getMessage(), ErrorLevel::ERROR));
        }

        return false;
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
        if ($this->undoQuery) {
            $messages->addMessage(new StepMessage($this, 'Undo query ' . $this->undoQuery, ErrorLevel::DEBUG));
            try {
                return $this->database->query($this->undoQuery);
            } catch (\Exception $exception) {
                $messages->addMessage(new StepMessage($this, $exception->getMessage(), ErrorLevel::ERROR));
            }

            return false;
        }

        return true;
    }
}
