<?php

/**
 * i-doit
 *
 * Smarty plugin for cmdb object links
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_cmdb_link implements isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return bool
     */
    public function enable_meta_map()
    {
        return true;
    }

    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return [C__CMDB__GET__OBJECT];
    }

    /**
     * Navigation mode.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_param = null)
    {
        $this->m_strPluginClass = "text";
        $this->m_strPluginName = "cmdb_link";

        $l_get = [
            C__CMDB__GET__OBJECT   => $p_params[C__CMDB__GET__OBJECT],
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY,
            C__CMDB__GET__CATG     => (isset($p_params[C__CMDB__GET__CATG])) ? $p_params[C__CMDB__GET__CATG] : defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT
        ];

        if (isset($p_params[C__CMDB__GET__CATLEVEL])) {
            $l_get[C__CMDB__GET__CATLEVEL] = $p_params[C__CMDB__GET__CATLEVEL];
        }

        if (isset($p_params["linkonly"]) && $p_params["linkonly"]) {
            return isys_helper_link::create_url($l_get);
        }

        if (isset($p_params["quickinfo"]) && $p_params["quickinfo"]) {
            $l_quick_info = new isys_ajax_handler_quick_info();

            if (isset($p_params["style"])) {
                $l_quick_info->set_style($p_params["style"]);
            }

            if (isset($p_params["class"])) {
                $l_quick_info->set_class($p_params["class"]);
            }

            return $l_quick_info->get_quick_info($p_params[C__CMDB__GET__OBJECT], $p_params['p_strValue'], C__LINK__OBJECT, false, $l_get);
        } else {
            $l_class = (isset($p_params['class'])) ? ' class="' . $p_params["class"] . '"' : '';
            $l_style = (isset($p_params['style'])) ? ' style="' . $p_params["style"] . '"' : '';

            return '<a href="' . isys_helper_link::create_url($l_get) . '"' . $l_class . $l_style . '>' . $p_params["p_strValue"] . '</a>';
        }
    }

    /**
     * Edit mode.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    }
}