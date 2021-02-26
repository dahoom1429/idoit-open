<?php

namespace idoit\Module\Console\Steps\FileSystem;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;

class DeleteFiles implements Step
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * DeleteFiles constructor.
     *
     * @param string $name
     * @param string $path
     */
    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        $deleted = 0;
        $undeleted = 0;

        if (is_dir($this->path)) {
            $messages->addMessage(new StepMessage($this, 'Deleting ' . $this->path, ErrorLevel::DEBUG));
            isys_glob_delete_recursive($this->path, $deleted, $undeleted);
        } else {
            $messages->addMessage(new StepMessage($this, $this->path . ' does not exist', ErrorLevel::DEBUG));

            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Removed ' . $deleted, ErrorLevel::DEBUG));
        $messages->addMessage(new StepMessage($this, 'Not removed ' . $undeleted, ErrorLevel::DEBUG));

        return true;
    }
}
