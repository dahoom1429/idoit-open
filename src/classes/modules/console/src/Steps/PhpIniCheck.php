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

class PhpIniCheck extends Check
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $settingName;

    /**
     * PhpIniCheck constructor.
     *
     * @param          $settingName
     * @param callable $check
     */
    public function __construct($settingName, callable $check, int $level = ErrorLevel::INFO)
    {
        $this->settingName = $settingName;
        $this->callback = $check;
        $this->level = $level;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Ini Setting: ' . $this->settingName;
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    public function check()
    {
        $value = ini_get($this->settingName);
        $callback = $this->callback;
        $res = (bool)$callback($value);
        return $res;
    }
}
