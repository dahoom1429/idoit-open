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

class ConstantStep implements Step, Undoable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool value to return
     */
    private $value;

    /**
     * @var int ErrorLevel
     */
    private $level;

    /**
     * ConstantStep constructor.
     *
     * @param     $name
     * @param     $value
     * @param int $level
     */
    public function __construct($name, $value, $level = ErrorLevel::DEBUG)
    {
        $this->name = $name;
        $this->value = $value;
        $this->level = $level;
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
        $messages->addMessage(new StepMessage($this, $this->value, $this->level));
        return $this->value;
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
        return true;
    }
}
