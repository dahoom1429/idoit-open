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

class CollectionStep implements Step, Undoable
{
    /**
     * @var bool
     */
    private $isAnd;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $sequent;

    /**
     * @var Step[]
     */
    private $steps = [];

    /**
     * @var array
     */
    private $stepsToRollback = [];

    /**
     * CollectionStep constructor.
     *
     * @param       $name
     * @param array $steps
     * @param bool  $isAnd
     * @param bool  $sequent
     */
    public function __construct($name, array $steps = [], $isAnd = true, $sequent = true)
    {
        $this->name = $name;
        foreach ($steps as $step) {
            $this->addStep($step);
        }
        $this->isAnd = $isAnd;
        $this->sequent = $sequent;
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
     * @param Step $step
     *
     * @return self
     */
    public function addStep(Step $step)
    {
        $this->steps[] = $step;

        return $this;
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
        $this->stepsToRollback = [];
        $result = $this->isAnd;

        $resultMessage = new StepMessage($this, '', ErrorLevel::INFO);
        $messages->addMessage($resultMessage);

        foreach ($this->steps as $step) {
            $res = (bool)$step->process($messages);
            if ($this->isAnd) {
                $result = $result && $res;
            } else {
                $result = $result || $res;
            }
            array_unshift($this->stepsToRollback, $step);
            if ($this->sequent && $result !== $this->isAnd) {
                break;
            }
        }

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
        $result = $this->isAnd;
        $resultMessage = new StepMessage($this, 'Undo', ErrorLevel::INFO);
        if (!empty($this->stepsToRollback)) {
            $messages->addMessage($resultMessage);
        }

        foreach ($this->stepsToRollback as $step) {
            $res = true;

            if ($step instanceof Undoable) {
                $res = $step->undo($messages);
            }

            if ($this->isAnd) {
                $result = $result && $res;
            } else {
                $result = $result || $res;
            }
        }

        $this->stepsToRollback = [];

        return $result;
    }
}
