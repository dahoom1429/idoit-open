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

class NotCheck implements Step, Undoable
{
    /**
     * @var Step
     */
    private $check;

    /**
     * @var bool
     */
    private $rollback = false;

    public function __construct(Step $check)
    {
        $this->check = $check;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Not ' . $this->check->getName();
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
        $this->rollback = false;
        $result = $this->check->process($messages);
        $message = new StepMessage($this, $result ? 'Success' : 'Fail', ErrorLevel::DEBUG);
        $messages->addMessage($message);
        $this->rollback = true;

        return $result;
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
        if ($this->rollback && $this->check instanceof Undoable) {
            $result = $this->check->undo($messages);
        }
        $this->rollback = false;

        return $result;
    }
}
