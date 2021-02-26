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

namespace idoit\Module\Console\Steps\Message;

use idoit\Module\Console\Steps\Step;

class StepMessage extends Message
{
    /**
     * @var Step
     */
    private $step;

    /**
     * @return Step
     */
    public function getStep()
    {
        return $this->step;
    }

    public function __construct(Step $step, $message, $level)
    {
        $name = $step->getName();
        if ('' !== $message) {
            $name .= ': ' . $message;
        }
        parent::__construct($name, $level);
        $this->step = $step;
    }
}
