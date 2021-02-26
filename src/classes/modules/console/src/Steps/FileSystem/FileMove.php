<?php

namespace idoit\Module\Console\Steps\FileSystem;

use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;

class FileMove implements Step
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Move ' . $this->from . ' to ' . $this->to;
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
        return rename($this->from, $this->to);
    }
}
