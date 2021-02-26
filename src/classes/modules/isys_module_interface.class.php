<?php

/**
 * Interface isys_module_interface
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_module_interface
{
    /**
     * Signal Slot initialization
     *
     * @return void
     */
    public function initslots();

    /**
     * Method for starting the module.
     */
    public function start();
}
