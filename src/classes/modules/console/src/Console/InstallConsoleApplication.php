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

namespace idoit\Module\Console\Console;

use idoit\Console\IdoitConsoleApplication;
use idoit\Module\Console\Console\Command\idoit\InstallCommand;

class InstallConsoleApplication extends IdoitConsoleApplication
{
    protected function loadCommands()
    {
        $this->add(new InstallCommand());
    }
}
