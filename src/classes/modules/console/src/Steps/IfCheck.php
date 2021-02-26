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

class IfCheck implements Step, Undoable
{
    /**
     * @var Step
     */
    private $check;

    private $name;

    /**
     * @var Step
     */
    private $onFail;

    /**
     * @var Step
     */
    private $onSuccess;

    /**
     * @var null|callable
     */
    private $rollback;

    /**
     *
     * @param           $name
     * @param Step      $check
     * @param Step|null $onSuccess
     * @param Step|null $onFail
     */
    public function __construct($name, Step $check, Step $onSuccess = null, Step $onFail = null)
    {
        $this->name = $name;
        $this->check = $check;
        $this->onSuccess = $onSuccess;
        $this->onFail = $onFail;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'If ' . $this->name;
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
        $result = $this->check->process($messages);
        $message = new StepMessage($this, $result ? 'Success' : 'Fail', ErrorLevel::DEBUG);
        $messages->addMessage($message);

        $toDo = $result ? $this->onSuccess : $this->onFail;

        if ($toDo instanceof Step) {
            if ($toDo instanceof Undoable) {
                $this->rollback = function ($msgs) use ($toDo) {
                    return $toDo->undo($msgs);
                };
            }

            $result = $toDo->process($messages);

            if (!$result) {
                $messages->addMessage(new StepMessage($toDo, '', ErrorLevel::ERROR));
            }

            return $result;
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
        $result = true;

        if ($this->rollback !== null) {
            $result = \call_user_func($this->rollback, $messages);
        }

        $this->rollback = null;

        return $result;
    }
}
