<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_location_error extends isys_component_popup
{
    /**
     * Method for displaying the object-browser UI fields.
     *
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * This method gets called by the Ajax request to display the browser.
     *
     * @param isys_module_request $p_modreq
     *
     * @return void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $this->template->display('popup/location_error.tpl');
        die;
    }
}
