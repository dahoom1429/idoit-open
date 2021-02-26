<?php

namespace idoit\Module\Console\Steps\Update;

use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use isys_update_files;

/**
 * Class CopyUpdateFiles
 */
class CopyUpdateFiles implements Step
{
    /**
     * @var string
     */
    private $from;

    /**
     * FileCopy constructor.
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
        return 'Copy files from ' . $this->from;
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

        return $updateFiles->copy();
    }
}
