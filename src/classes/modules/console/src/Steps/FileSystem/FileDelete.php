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

namespace idoit\Module\Console\Steps\FileSystem;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;

class FileDelete implements Step, Undoable
{
    private $path;

    private $newPath;

    private $safeMode = false;

    public function __construct($path, $safeMode = false)
    {
        $this->path = $path;
        $this->safeMode = $safeMode;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Remove file: ' . $this->path;
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
        if (file_exists($this->path)) {
            $messages->addMessage(new StepMessage($this, 'File is found: ' . $this->path, ErrorLevel::DEBUG));
            if ($this->safeMode === true) {
                $newPath = $this->path . '.' . date('YmdHis');
                rename($this->path, $newPath);
                $this->newPath = $newPath;
            } else {
                unlink($this->path);
            }
        }
        return true;
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
        if ($this->safeMode === true && $this->newPath !== null && file_exists($this->newPath)) {
            rename($this->newPath, $this->path);
            $this->newPath = null;
        }
        return true;
    }
}
