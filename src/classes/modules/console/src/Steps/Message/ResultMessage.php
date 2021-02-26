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

class ResultMessage extends StepMessage
{
    private $result;

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     *
     * @return ResultMessage
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    public function __construct(Step $step, $message, $result = null, $errorLevel = ErrorLevel::INFO)
    {
        parent::__construct($step, $message, $errorLevel);
        $this->result = $result;
    }
}
