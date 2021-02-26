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

class DirectoryRightsCheck extends Check
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Directory: ' . $this->path . ' should exist and be writable';
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    public function check()
    {
        return is_dir($this->path) && is_writable($this->path);
    }
}
