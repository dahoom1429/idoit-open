<?php

/**
 * i-doit
 *
 * Smarty plugin for file upload
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stückn <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_file extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return ["p_strAccept"];
    }

    /**
     * Returns the content value.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if ($p_params["p_bEditMode"] == "1") {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        return $this->getInfoIcon($p_params) . html_entity_decode(stripslashes($p_params["p_strValue"]));
    }

    /**
     * General Params:
     *   $p_params["p_strAccept"]     => comma seperated list of accepted mime types
     *   $p_params["p_bDisabled"]     => disable
     *   $p_params["p_strName"]       => name
     *   $p_params["p_strSize"]       => size
     *   $p_params["p_strStyle"]      => set the style
     *   $p_params["p_strClass"]      => set the class
     *   $p_params["p_strTitle"]      => title for e.g. tooltip
     *   $p_params["p_Tab"]           => tabindex
     *   $p_params["p_strOnFocus"]    => onfocus handler
     *   $p_params["p_strOnClick"]    => onclick handler
     *   $p_params["p_strMouseOver"]  => onmouseover handler
     *   $p_params["p_strMouseDown"]  => onmousedown handler
     *   $p_params["p_strOnKeyPress"] => onkeypress handler
     *
     * Input specific params:
     *   $p_params["p_strError"]          => error flag (1 or 0)
     *   $p_params["p_bInfoIconDisabled"] => disable the InfoIcon
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Dennis Stücken <dstuecke@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "f_file";
        $this->m_strPluginName = $p_params["name"];

        $p_params["p_strClass"] = "input input-file " . $p_params["p_strClass"];

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        return $this->getInfoIcon($p_params) . "<input " . $p_params["name"] . " " . "type=\"file\" " . $p_params["p_strAccept"] . " " . $p_params["p_bDisabled"] . " " .
            $p_params["p_strName"] . " " . $p_params["p_strSize"] . " " . $p_params["p_strStyle"] . " " . $p_params["p_strClass"] . " " . $p_params["p_nSize"] . " " .
            $p_params["p_strTitle"] . " " . $p_params["p_Tab"] . " " . $p_params["p_strOnFocus"] . " " . $p_params["p_strOnClick"] . " " . $p_params["p_strMouseDown"] . " " .
            $p_params["p_onKeyDown"] . " " . $p_params['p_validation_mandatory'] . " " . $p_params['p_validation_rule'] . " />";
    }
}