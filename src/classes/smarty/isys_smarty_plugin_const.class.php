<?php

/**
 * i-doit
 *
 * Smarty plugin for constants.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_const extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return ["ident"];
    }

    /**
     * Returns constant value.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "const";
        $this->m_strPluginName = $p_params["name"];

        return (array_key_exists("ident", $p_params)) ? constant($p_params["ident"]) : null;
    }

    /**
     * Alias method for navigation_view.
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