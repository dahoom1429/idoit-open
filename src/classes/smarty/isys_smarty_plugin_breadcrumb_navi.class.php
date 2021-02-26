<?php

/**
 * i-doit
 *
 * Smarty plugin for breadcrumb navigation.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_breadcrumb_navi extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Shows the hierarchical breadcrumb navigation.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_comp_session;

        if (!$g_comp_session->is_logged_in()) {
            return "Login";
        }

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if (!empty($p_params["p_strValue"])) {
            $p_params["p_strValue"] = html_entity_decode(stripslashes($p_params["p_strValue"]), null, $GLOBALS['g_config']['html-encoding']);
        }

        $this->m_strPluginClass = "breadcrumb_navi";
        $this->m_strPluginName = $p_params["name"];

        $l_objBreadcrumb = new isys_component_template_breadcrumb();

        if (isset($p_params['p_home']) && $p_params['p_home']) {
            $l_objBreadcrumb->include_home();
        }

        $l_output = stripslashes($l_objBreadcrumb->process($p_params["p_plain"], $p_params["p_append"], null, $p_params["p_prepend"]));

        // have to use substr instead of rtrim. Reason rtrim('<li>Global</li>', '</li>') = '<li>Globa'.
        if (substr($l_output, -(strlen($p_params["p_append"])), strlen($l_output)) == $p_params["p_append"]) {
            $l_output = substr($l_output, 0, -(strlen($p_params["p_append"])));
        }

        return $l_output;
    }

    /**
     * Wrapper for the navigation_view.
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