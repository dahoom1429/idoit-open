<?php

namespace idoit\Module\Console\Steps\Update;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_component_dao;
use isys_component_database;
use isys_update;

/**
 * Class UpdateDatabase
 */
class UpdateDatabase implements Step
{
    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * @var bool
     */
    private $doVersionChange;

    /**
     * @var string
     */
    private $updateFile;

    /**
     * FileCopy constructor.
     *
     * @param string                  $updateFile
     * @param isys_component_database $database
     * @param bool                    $doVersionChange
     */
    public function __construct(string $updateFile, isys_component_database $database, bool $doVersionChange)
    {
        $this->updateFile = $updateFile;
        $this->database = $database;
        $this->doVersionChange = $doVersionChange;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Update `' . $this->database->get_db_name() . '`` with ' . $this->updateFile;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return bool
     */
    public function process(Messages $messages)
    {
        $update = new isys_update();

        $dao = new isys_component_dao($this->database);

        $messages->addMessage(new StepMessage($this, 'Start update', ErrorLevel::DEBUG));

        $success = $update->update_database($this->updateFile, $this->database, $this->doVersionChange, $dao);

        if (!$success) {
            $messages->addMessage(new StepMessage($this, 'Update failed', ErrorLevel::ERROR));

            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Update is successful', ErrorLevel::INFO));

        return $dao->apply_update();
    }
}
