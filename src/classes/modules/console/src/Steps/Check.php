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

namespace idoit\Module\Console\Steps;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

abstract class Check implements Step, Undoable
{
    protected $level = ErrorLevel::INFO;

    abstract protected function check();

    final public function process(Messages $messages)
    {
        $result = $this->check();
        if (!$result) {
            $messages->addMessage(new StepMessage($this, ' failed', $this->level));
        } else {
            $messages->addMessage(new StepMessage($this, '', ErrorLevel::DEBUG));
        }
        if ($this->level < ErrorLevel::ERROR) {
            return true;
        }

        return $result;
    }

    /**
     * Undo the work - does nothing
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    final public function undo(Messages $messages)
    {
        return true;
    }
}
