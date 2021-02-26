<?php

/**
 * i-doit
 *
 * Popup class for commentaries for saving changes into the LogBook
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_commentary extends isys_component_popup
{
    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Create location browser popup.
        $this->template->display('popup/commentary.tpl');

        die();
    }
}
