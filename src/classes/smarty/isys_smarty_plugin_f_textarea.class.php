<?php

/**
 * i-doit
 *
 * Smarty plugin for textarea input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_textarea extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
    }

    /**
     * View mode.
     *
     * @return string $p_params["p_strValue"]
     *
     * @param isys_component_template &$p_tplclass
     * @param                         $p_params
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_dirs;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $l_strSource = $g_dirs["images"] . "empty.gif";

        $l_spacer_img = '<img class="infoIcon vam" src="' . $l_strSource . '" alt="" height="15px" title="" ' . 'width="15px" style="margin-right:5px;" />';

        if ($p_params["p_bEditMode"] == 1) {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        $l_content_textarea = "";

        $p_params["p_strValue"] = isys_helper_textformat::strip_scripts_tags($p_params["p_strValue"], $p_params["htmlEnabled"]);
        $p_params["p_strValue"] = str_replace("\\n", "\n", $p_params["p_strValue"]);
        $p_params["p_strValue"] = str_replace("\\r", "\r", $p_params["p_strValue"]);

        $l_arValue = explode("\n", $p_params["p_strValue"]);
        $l_first = true;
        foreach ($l_arValue as $value) {
            $l_content_textarea .= ((!$l_first) ? $l_spacer_img : '') . str_replace("\\r", "", $value) . "<br />";
            $l_first = false;
        }

        $l_content_textarea = rtrim($l_content_textarea);

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName = $p_params["name"];

        $l_content_textarea = $this->getInfoIcon($p_params) . "<span>" . $l_content_textarea . "</span>";

        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]) && (bool)isys_tenantsettings::get('search.highlight-search-string', 1)) {
            $l_content_textarea = isys_string::highlight($_GET[C__SEARCH__GET__HIGHLIGHT], $l_content_textarea);
        }

        return $l_content_textarea;
    }

    /**
     * Edit mode - Parameters are given in an array $p_params:
     *     Basic parameters
     *         name                    -> name
     *         type                    -> smarty plug in type
     *         p_strPopupType          -> pop up type
     *         p_strPopupLink          -> link for the pop up image
     *         p_strValue              -> value
     *         p_nTabIndex             -> tabindex
     *         p_nTabOffset            -> taboffset
     *         p_strTitle              -> title (and tooltip)
     *         p_strAlt                -> alt tag for the pop up image
     *
     *     InfoIcon parameters
     *         p_bInfoIcon             -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer       -> if set to 0 no image is shown at all
     *
     *     Style parameters
     *         p_strID                 -> id
     *         p_strClass              -> class
     *         p_strStyle              -> style
     *         p_bSelected             -> preselected, looks like onMouseOver style
     *         p_bEditMode             -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible            -> don't show anything at all
     *         p_bDisabled             -> disabled
     *         p_bReadonly             -> readonly
     *
     *     JavaScript parameters
     *         p_onClick               -> onClick
     *         p_onChange              -> onChange
     *         p_onMouseOver           -> onMouseOver
     *         p_onMouseOut            -> onMouseOut
     *         p_onMouseMove           -> onMouseMove
     *         p_onKeyDown             -> onKeyDown
     *         p_onKeyPress            -> onKeyPress
     *
     *     Special parameters
     *         p_nSize                 -> size
     *         p_nRows                 -> rows
     *         p_nCols                 -> cols
     *         p_nMaxLen               -> maxlen
     *         p_strTable              -> name of the database table to use for filling the plug in list
     *         p_arData                -> array with data to fill the plug in list
     *         p_bDbFieldNN            -> field is NaN (not a number):
     *         p_strSelectedID         -> pre selected value in the list
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName = $p_params["name"];

        $p_params["p_strValue"] = isys_helper_textformat::strip_scripts_tags($p_params["p_strValue"], $p_params["htmlEnabled"]);

        $l_name = null;

        if ($p_params["name"] !== null) {
            $l_name = $p_params["name"];
            $p_params["name"] = 'name="' . $p_params["name"] . '"';
        }

        if ($p_params["p_strID"] !== null) {
            $p_params["p_strID"] = 'id="' . $p_params["p_strID"] . '"';
        } else if ($p_params["id"] !== null) {
            $p_params["p_strID"] = 'id="' . $p_params["id"] . '"';
        } else if (isset($l_name)) {
            $p_params["p_strID"] = 'id="' . $l_name . '"';
        }

        $l_extra = "";
        $l_content_textarea = "";
        $l_strTitle = "";
        $l_strClass = "input " . $p_params['p_strClass'];
        $l_nRows = 10;

        // Is the error flag set?
        if (!empty($p_params["p_strError"])) {
            $l_strClass = $l_strClass . "Error";
        }

        $this->getJavascriptAttributes($p_params);

        // Rows and columns.
        if (!empty($p_params["p_nRows"])) {
            $l_nRows = $p_params["p_nRows"];
        }

        if (!empty($p_params["p_nCols"])) {
            $l_nCols = $p_params["p_nCols"];
        } else {
            $l_nCols = 32;
        }

        $p_params["p_strValue"] = str_replace("\\n", "\n", $p_params["p_strValue"]);
        $p_params["p_strValue"] = str_replace("\\r", "\r", $p_params["p_strValue"]);

        $l_arValue = explode("\n", $p_params["p_strValue"]);

        foreach ($l_arValue as $value) {
            $l_content_textarea .= str_replace("\\r", "", $value) . "\n";
        }

        $l_content_textarea = rtrim($l_content_textarea, "\n");

        if (isset($p_params['p_bDisabled']) && $p_params['p_bDisabled']) {
            $l_extra .= 'disabled="disabled" ';
        }

        if (isset($p_params['p_bReadonly']) && $p_params['p_bReadonly']) {
            $l_extra .= 'readonly="readonly" ';
        }

        if (isset($p_params['p_validation_mandatory']) && $p_params['p_validation_mandatory']) {
            $l_extra .= 'data-mandatory-rule="1" ';
        }

        if (isset($p_params['p_validation_rule']) && $p_params['p_validation_rule']) {
            $l_extra .= 'data-validation-rule="1" ';
        }

        return $this->getInfoIcon($p_params) . "<textarea " . $l_extra . $p_params["p_strID"] . $p_params["name"] . $p_params["p_onChange"] . $p_params["p_onKeyUp"] .
            $p_params["p_onKeyDown"] . $p_params["p_onKeyPress"] . $p_params["p_onClick"] . "data-identifier=\"" . $p_params['p_dataIdentifier'] . "\" " . "class=\"" .
            $l_strClass . "\" " . "style=\"" . $p_params["p_strStyle"] . "\" " . "title=\"" . $l_strTitle . "\" " . "tabindex=\"" . $p_params["p_strTab"] . "\" " . "rows=\"" .
            $l_nRows . "\" " . "cols=\"" . $l_nCols . "\">" . $l_content_textarea . "</textarea>";
    }
}