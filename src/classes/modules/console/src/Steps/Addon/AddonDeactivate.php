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

use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use isys_module_manager;

class AddonDeactivate implements Step, Undoable
{
    private $done;

    private $id;

    private $tenantId;

    /**
     * @var isys_module_manager
     */
    private $manager;

    public function __construct($id, $tenantId, isys_module_manager $manager)
    {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->manager = $manager;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Deactivate Addon ' . $this->id . ' for tenant ' . $this->tenantId;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function process(Messages $messages)
    {
        $this->done = false;

        if (!$this->manager->is_active($this->id)) {
            return true;
        }
        $result = $this->manager->deactivateAddOn($this->id);
        $this->done = $result;
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
        if (!$this->done) {
            return true;
        }
        return $this->manager->activateAddOn($this->id);
    }
}
