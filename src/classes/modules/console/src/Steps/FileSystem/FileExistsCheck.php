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

namespace idoit\Module\Console\Steps\FileSystem;

use idoit\Module\Console\Steps\Check;
use idoit\Module\Console\Steps\Message\ErrorLevel;

class FileExistsCheck extends Check
{
    /**
     * @var bool
     */
    private $expected;

    private $path;

    public function __construct($path, $expected = true, $level = ErrorLevel::INFO)
    {
        $this->path = $path;
        $this->expected = $expected;
        $this->level = $level;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'File: ' . $this->path . ' ' . ($this->expected ? 'should exist' : 'should not exist');
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    public function check()
    {
        return is_file($this->path) === $this->expected;
    }
}
