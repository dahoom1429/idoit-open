<?php

/**
 * i-doit
 *
 * Smarty plugin for some data ONLY for view mode!!!
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_data extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Method for view-mode. Following parameters are being used:
     *     p_strValue       -> Value to diplay.
     *     default          -> Value to display, if p_strValue is empty.
     *     p_plain          -> If set to true, will return a plain string of the value.
     *     len              -> Limits the value to a given amount of characters.
     *     append           -> The string, that will be appended, if cut of (default "..").
     *     p_strStyle       -> String, which will be loaded into the "style" attribute.
     *
     * + all parameters, which are relevant for the infoicon.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        $l_style = $l_id = $l_class = '';

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if (empty($p_params["p_strValue"]) && !empty($p_params["default"])) {
            $p_params["p_strValue"] = $p_params["default"];
        }

        if ($p_params["p_plain"]) {
            return $p_params["p_strValue"];
        }

        $p_params["p_strValue"] = html_entity_decode(stripslashes($p_params["p_strValue"]), null, $GLOBALS['g_config']['html-encoding']);

        $this->m_strPluginClass = "f_data";
        $this->m_strPluginName = $p_params["name"];

        if (isset($p_params["len"]) && $p_params["len"] > 0) {
            $l_append = "..";

            if (!empty($p_params["append"])) {
                $l_append = $p_params["append"];
            }

            $p_params["p_strValue"] = isys_glob_cut_string($p_params["p_strValue"], $p_params["len"], $l_append);
        }

        if (!empty($p_params["p_strStyle"])) {
            $l_style = ' style="' . $p_params["p_strStyle"] . '"';
        }

        if (!empty($p_params["p_strID"])) {
            $l_id = ' id="' . $p_params["p_strID"] . '"';
        } else if (!empty($p_params["id"])) {
            $l_id = ' id="' . $p_params["id"] . '"';
        }

        if (!empty($p_params["p_strClass"])) {
            $l_class = ' class="' . $p_params["p_strClass"] . '"';
        }

        if (!$p_params["p_strValue"] && isset($p_params["default"])) {
            $p_params["p_strValue"] = $p_params["default"];
        }

        if ($l_style . $l_id . $l_class) {
            return $this->getInfoIcon($p_params) . "<span" . $l_style . $l_id . $l_class . ">" . $p_params["p_strValue"] . "</span>";
        } else {
            return $this->getInfoIcon($p_params) . $p_params["p_strValue"];
        }
    }

    /**
     * Method for edit-mode.
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