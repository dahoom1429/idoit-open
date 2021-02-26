<?php

/**
 * i-doit
 *
 * Smarty plugin for text input fields.
 *
 * @deprecated  Use a normal isys_smarty_plugin_f_text instead!
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_ip extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     * @author André Wösten <awoesten@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
    }

    /**
     * Returns the content value.
     *
     * @param   isys_component_template & $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if ($p_params['p_bInvisible'] == true) {
            return '';
        }

        if (is_null($p_params['p_strValue']) && isset($p_params['default'])) {
            $p_params['p_strValue'] = $p_params['default'];
        }

        if ($p_params['p_bEditMode'] == '1') {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        return $this->getInfoIcon($p_params) . '<span>' . stripslashes(html_entity_decode(stripslashes($p_params['p_strValue']))) . '</span>';
    }

    /**
     * Display in edit mode.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        $l_strOut = '';

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $p_params['p_nMaxLen'] = '3';

        $this->m_strPluginClass = "f_ip";
        $this->m_strPluginName = $p_params["name"];

        $l_class_iterator = null;

        // This is necessary for multi edit
        if (isset($p_params["classIterator"])) {
            $l_class_iterator = $p_params["p_strClass"];
        }

        // Default css class.
        $l_strClasses = $p_params["p_strClass"] = 'input ' . $p_params["p_strClass"];

        // Is the error flag set?
        if (!empty($p_params["p_strError"])) {
            $p_params["p_strError"] = $p_params["p_strError"] . "Error";
        }

        // Unescape and strip the value.
        $p_params["p_strValue"] = stripslashes($p_params["p_strValue"]);
        $p_params["p_strValue"] = htmlentities(isys_glob_unescape($p_params["p_strValue"]), null, $GLOBALS['g_config']['html-encoding']);

        // IP Type (ipv4, ipv6).
        $l_type = $p_params["p_strType"];
        $l_value = $p_params["p_strValue"];

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        // Show InfoIcon.
        $l_strOut .= $this->getInfoIcon($p_params);

        switch ($l_type) {
            case "ipv6":
                $l_strOut .= "<input " . "name=\"" . $this->m_strPluginName . "\" " . "type=\"text\" " . "value=\"" . $l_value . "\" " . "id=\"" . $this->m_strPluginName .
                    "\" " . $p_params["p_strTitle"] . " " . $p_params["p_strClass"] . " " . $p_params["p_bDisabled"] . " " . $p_params["p_bReadonly"] . " " .
                    $p_params["p_strTab"] . " " . $p_params["p_onMouseOver"] . " " . $p_params["p_onMouseOut"] . " " . $p_params["p_onChange"] . " " . $p_params["p_onClick"] .
                    " " . $p_params["p_additional"] . " />";
                break;

            case "ipv4":
            default:
                // Explode address mask.
                $l_value = explode(".", $l_value);

                for ($i = 0;$i <= 3;$i++) {
                    $l_keyup = "this.value = this.value.replace(/\D/,''); " . "if (this.value > 255) { this.value = '255'; } " . "if (this.value < 0) { this.value = '1'; } ";

                    // This is a more "user-friendly" way of navigating through the IP-inputs.
                    $l_keydown = (($i < 3) ? "if (event.keyCode == Event.KEY_RIGHT) { $('" . $this->m_strPluginName . "[" . ($i + 1) . "]').select(); event.stop(); }" : '') .
                        (($i > 0) ? "if (event.keyCode == Event.KEY_LEFT) { $('" . $this->m_strPluginName . "[" . ($i - 1) . "]').select(); event.stop(); }" : '');

                    if ($l_class_iterator !== null) {
                        $p_params["p_strClass"] = 'class="' . $l_strClasses . ' ' . $l_class_iterator . '_' . $i . '"';
                    }

                    $l_border_style = (($i < 3) ? 'border-right:none;' : '');

                    $l_strOut .= '<input name="' . $this->m_strPluginName . '[]" type="text" value="' . $l_value[$i] . '" ' . 'style="width:35px; text-align:center; ' .
                        $l_border_style . '" id="' . $this->m_strPluginName . '[' . $i . ']" ' . 'onkeyup="' . $l_keyup . '" onkeydown="' . $l_keydown . '" maxlength="3" ' .
                        $p_params["p_strTitle"] . ' ' . $p_params["p_strClass"] . ' ' . $p_params["p_bDisabled"] . ' ' . $p_params["p_bReadonly"] . ' ' .
                        $p_params["p_strTab"] . ' ' . $p_params["p_onMouseOver"] . ' ' . $p_params["p_onMouseOut"] . ' ' . $p_params["p_onChange"] . ' ' .
                        $p_params['p_dataIdentifier'] . ' ' . $p_params["p_onClick"] . ' ' . $p_params["p_additional"] . ' />';
                }
        }

        return $l_strOut;
    }
}