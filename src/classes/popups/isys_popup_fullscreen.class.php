<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_fullscreen extends isys_component_popup
{
    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This will be called directly via JS / URL.
    }

    /**
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Unpack module request.
        switch ($_POST['tpl']) {
            default:
            case 'license-warning':
                $l_template_file = isys_module_licence::getPath() . 'templates/nagscreen.tpl';
                break;
        }

        $this->template->activate_editmode()
            ->assign('params', isys_format_json::decode($_POST['parameters']))
            ->display($l_template_file);

        die;
    }
}
