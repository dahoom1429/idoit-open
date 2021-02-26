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

class EqualsStep extends Check
{
    private $name;

    private $value1;

    private $value2;

    public function __construct($name, $value1, $value2, $level = ErrorLevel::INFO)
    {
        $this->name = $name;
        $this->value1 = $value1;
        $this->value2 = $value2;
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
     * Check
     *
     * @return mixed
     */
    public function check()
    {
        return $this->value1 === $this->value2;
    }
}
