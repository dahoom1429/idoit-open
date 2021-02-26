<?php

/**
 * i-doit
 *
 * Smarty plugin for buttons
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_button extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            "p_strValue"
        ];
    }

    /**
     * Parameters are given in an array $p_params[].
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

        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName = $p_params["name"];

        if ($p_params["p_bInvisible"] == "1") {
            return '';
        }

        if ($p_params["p_bEditMode"] == "1") {
            $this->m_bEditMode = true;
        }

        $p_params["p_strClass"] = $p_params["p_strClass"] . ' btn';

        if (empty($p_params["p_strStyle"])) {
            $p_params["p_strStyle"] = "margin-right:5px;";
        }

        // If button is disabled empty javascript and change color
        if ($p_params["p_bDisabled"] == "0") {
            $l_disabled = "";
        } else if ($p_params["p_bDisabled"] == "1" || $this->m_bEditMode == false) {
            // @todo  Check if the CSS class "disabled" is still necessary - the button styling should still work (via the attribute).
            $p_params["p_strClass"] .= " disabled";
            $l_disabled = "disabled=\"disabled\"";
        } else {
            $l_disabled = '';
        }

        if ($p_params["type"] == "f_submit") {
            $p_params["type"] = "submit";
        }

        if ($p_params["type"] == "f_button") {
            $p_params["type"] = "button";
        }

        $l_value = isys_application::instance()->container->get('language')
            ->get($p_params["p_strValue"]);

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        //show InfoIcon
        $p_params["p_bInfoIconSpacer"] = "0";

        $l_icon = '';

        if (isset($p_params['icon'])) {
            $l_icon = '<img src="' . $p_params['icon'] . '" />';
        }

        return $this->getInfoIcon($p_params) . "<button " . $p_params["name"] . " " . $p_params["type"] . " " . $p_params["p_strAccessKey"] . " " . $p_params["p_strID"] .
            " " . $p_params["p_strTitle"] . " " . $p_params["p_strClass"] . " " . $p_params["p_strStyle"] . " " . $p_params["p_onClick"] . " " . $p_params["p_onMouseOver"] .
            " " . $p_params["p_onMouseOut"] . " " . $p_params["p_onMouseMove"] . " " . $p_params['p_strValue'] . " " . $l_disabled . '>' . $l_icon .
            (!empty($l_value) ? '<span' . (empty($l_icon) ? '' : ' class="ml5"') . '>' . $l_value . '</span>' : '') . '</button>';
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
        $this->m_bEditMode = true;

        return $this->navigation_view($p_tplclass, $p_params);
    }
}
