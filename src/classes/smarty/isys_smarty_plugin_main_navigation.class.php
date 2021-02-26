<?php

/**
 * i-doit
 *
 * Smarty plugin for the main navigation.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_smarty_plugin_main_navigation extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return bool
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [];
    }

    /**
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_menu, $g_bDefaultTooltips;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName = $p_params["name"];

        if (!isys_application::instance()->session->is_logged_in()) {
            return "";
        }

        $l_strRet = "";
        $l_ii = 0;
        $l_active_menu = $g_menu->get_active_menuobj();

        // Iterate through menuobjects
        while ($l_ii < $g_menu->count_new_menuobj()) {
            $l_mi = $g_menu->get_menuobj_by_nr($l_ii++); // act menuItem
            $l_strLink = $l_mi->get_member('m_link');

            // Tabindex - add the tabindex_offset, given by template to the tab-value of each menuItem
            $p_params["p_nTabIndex"] = $p_params["p_nTabOffset"] + $l_mi->get_member('m_tab');

            // Choose class for correct display
            if ($l_mi->__get('name') == $l_active_menu) {
                $l_strClass = $p_params["p_strClassSelected"];
            } else {
                $l_strClass = $p_params["p_strClass"];
            }

            if ($g_bDefaultTooltips) {
                if (strlen($l_mi->get_member('m_rn_tooltip')) > 0) {
                    $p_params["p_strTitle"] = $l_mi->get_member('m_rn_tooltip');
                }
            }

            $l_strRet .= "<a id=\"mainnavi_" . $l_mi->get_member('m_name') . "\" href=\"" . $l_strLink . "\" ";

            if ($p_params["p_strTarget"]) {
                $l_strRet .= "target=\"" . $p_params["p_strTarget"] . "\" ";
            }

            if ($l_strClass) {
                $l_strRet .= "class=\"" . $l_strClass . "\" ";
            }

            if ($p_params["p_strStyle"]) {
                $l_strRet .= "style=\"" . $p_params["p_strStyle"] . "\" ";
            }

            $l_strRet .= "onclick=\"" . $l_mi->get_member('m_onclick') . ";" . "$$('#mainNavi a." . $p_params["p_strClassSelected"] .
                "').each(function(i){i.className='mainNaviLink';});" . "this.className='" . $p_params["p_strClassSelected"] . "'\" ";

            if (strlen($p_params["p_strTitle"]) > 0) {
                $l_strRet .= "title=\"" . $p_params["p_strTitle"] . "\" ";
            }

            $l_strRet .= "tabindex=\"" . $p_params["p_nTabIndex"] . "\" >" . "&nbsp;&nbsp;" . $l_mi->get_member('m_rn_title') . "&nbsp;&nbsp;" . "</a>\n";
        }

        return $l_strRet;
    }

    /**
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    }
}