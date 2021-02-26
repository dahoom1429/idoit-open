<?php

namespace idoit\Module\Console\Steps\FileSystem;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_update_files;

class Unzip implements Step
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $zipPath;

    /**
     * Unzip constructor.
     *
     * @param string $zipPath
     * @param string $destination
     */
    public function __construct(string $zipPath, string $destination)
    {
        $this->zipPath = $zipPath;
        $this->destination = $destination;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Unzip ' . $this->zipPath;
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
        // Checking for zlib and the ZipArchive class to solve #4853
        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            $messages->addMessage(new StepMessage($this, 'Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.', ErrorLevel::FATAL));
            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Zip file is extracted', ErrorLevel::DEBUG));

        if (!(new isys_update_files())->read_zip($this->zipPath, $this->destination, false, true)) {
            $messages->addMessage(new StepMessage($this, 'Error: Could not read zip package.', ErrorLevel::FATAL));
            return false;
        }

        $messages->addMessage(new StepMessage($this, 'Zip file is read', ErrorLevel::DEBUG));

        return true;
    }
}
