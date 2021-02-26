<?php

namespace idoit\Module\Console\Steps\Update;

use Exception;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_update_files;

/**
 * Class RemoveUpdateFiles
 */
class RemoveUpdateFiles implements Step
{
    /**
     * @var string
     */
    private $from;

    /**
     * RemoveUpdateFiles constructor.
     *
     * @param string $from
     */
    public function __construct(string $from)
    {
        $this->from = $from;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Remove update files using ' . $this->from . '/update_files.xml';
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
        $updateFiles = new isys_update_files($this->from);

        try {
            $updateFiles->delete($this->from);
        } catch (Exception $e) {
            $messages->addMessage(new StepMessage($this, 'Removing failed', ErrorLevel::ERROR));

            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Removing done', ErrorLevel::INFO));

        return true;
    }
}
