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

class PhpExtensionCheck extends Check
{
    private $extensionName;

    public function __construct($extensionName, $level = ErrorLevel::ERROR)
    {
        $this->extensionName = $extensionName;
        $this->level = $level;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'php-ext: ' . $this->extensionName;
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    protected function check()
    {
        return \extension_loaded($this->extensionName);
    }
}
