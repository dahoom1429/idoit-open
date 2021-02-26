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

namespace idoit\Module\Console\Steps\Addon;

use idoit\Module\Console\Steps\Check;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\Dao\TenantExistById;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use isys_component_database;
use isys_module_manager;

class IsAddonInstalled extends Check
{
    private $id;

    protected $level = ErrorLevel::ERROR;

    /**
     * @var isys_module_manager
     */
    private $manager;

    public function __construct($id, isys_module_manager $manager)
    {
        $this->id = $id;
        $this->manager = $manager;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Check, if addon ' . $this->id . ' is installed';
    }

    protected function check()
    {
        return $this->manager->is_installed($this->id) !== false;
    }
}
