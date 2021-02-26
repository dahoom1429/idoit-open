<?php

/**
 * i-doit
 *
 * Smarty plugin for Selection lists.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_dialog_list extends isys_smarty_plugin_f implements isys_smarty_plugin
{

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [
            "p_strSelectedID",
            "p_arData",
        ];
    }

    /**
     * If the parameter 'p_bLinklist' is set to '1' a list with optional given links will be shown in the view mode. Only the list with the selected values will be used!
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

        $l_strInfoIcon = $this->getInfoIcon($p_params);

        if (!is_array($p_params["p_arData"])) {
            $l_arParams = unserialize($p_params["p_arData"]);
        } else {
            $l_arParams = $p_params["p_arData"];
        }

        if (isset($p_params['p_strSelectedBit'])) {
            $l_bitSelection = $p_params['p_strSelectedBit'];
        }

        if (is_array($l_arParams)) {
            $l_strOut = $l_strInfoIcon . '<div class="chosen-container chosen-container-multi"><ul class="chosen-choices" style="border:none;background:transparent">';

            // Divide selected and not selected into 2 arrays.
            foreach ($l_arParams as $l_val_ar) {
                $l_bURL = false;

                $l_value = $l_val_ar["val"];

                if (isset($l_bitSelection)) {
                    $l_sel = $l_val_ar['id'] & $l_bitSelection;
                } else {
                    $l_sel = $l_val_ar["sel"];
                }

                $l_url = $l_val_ar["url"];

                if ($l_sel) {
                    $l_strOut .= "<li class='search-choice' style='float:none; margin: 3px 0 3px 0'>";

                    if (strlen($l_url) >= 1) {
                        $l_bURL = true;
                        $l_strOut .= "<a href=\"" . $l_url . "\">";
                    }

                    $l_strOut .= "<span>" . $l_value . "</span>";

                    if ($l_bURL) {
                        $l_strOut .= "</a>";
                    }

                    $l_strOut .= "</li>";
                }
            }

            $l_strOut .= "</ul></div>";
        } else {
            $l_strOut = '<span class="ml20">-</span>';
        }

        return $l_strOut;
    }

    /**
     * Returns html/javascript dialogue list
     *       - when you have more than 1 call of this function at once,
     *         set $p_params["name"] for every call
     *       - to get a comma-seperated list of all IDs you have to query
     *         name + "__available_values" and name +
     *         "__selected_values"
     *       - the selectboxes will get a name with the postfixes
     *         "_available_box" and "_selected_box"
     *
     *       Parameters are given in an array $p_param[]
     *       -----------------------------------------------------------------
     *       //basic parameters
     *       name                -> name
     *       type                -> smarty plug in type
     *       p_strPopupType      -> pop up type
     *       p_strPopupLink      -> link for the pop up image
     *       p_strValue          -> value
     *       p_nTabIndex         -> tabindex
     *       p_nTabOffset        -> taboffset
     *       p_strTitle          -> title (and tooltip)
     *       p_strAlt            -> alt tag for the pop up image
     *
     *       //InfoIcon parameters
     *       p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *       p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *       //Style parameters
     *       p_strID             -> id
     *       p_strClass          -> class
     *       p_strStyle          -> style
     *       p_bSelected         -> preselected, looks like onMouseOver style
     *       p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *       p_bInvisible        -> don't show anything at all
     *       p_bDisabled         -> disabled
     *       p_bReadonly         -> readonly
     *
     *       //JavaScript parameters
     *       p_onClick           -> onClick
     *       p_onChange          -> onChange
     *       p_onMouseOver       -> onMouseOver
     *       p_onMouseOut        -> onMouseOut
     *       p_onMouseMove       -> onMouseMove
     *       p_onKeyDown         -> onKeyDown
     *       p_onKeyPress        -> onKeyPress
     *
     *       //Special parameters
     *       p_bSort             -> Sort the options by title
     *       p_nSize             -> size
     *       p_nRows             -> rows
     *       p_nCols             -> cols
     *       p_nMaxLen           -> maxlen
     *       p_arData            -> array with data to fill the plug in list
     *       p_bDbFieldNN        -> field is NaN (not a number):
     *       p_strSelectedID     -> pre selected value in the list
     *       p_strSelectedBit    -> Bit Value for the selected values in the list. This works only if ids are Bitwise
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName = $p_params["name"];

        $l_arrSelectedValues = [];

        // If disabled is set the value will not be send with the formdata.
        $l_extra = (($p_params["p_bDisabled"] == "1") ? "disabled=\"disabled\" " : "") . (($p_params["p_bReadonly"] == "1") ? "readonly=\"readonly\" " : "");

        // CallBack Preparation.
        if (isset($p_params["add_callback"])) {
            $l_add_callback = $p_params["add_callback"];
        } else {
            $l_add_callback = '';
        }

        if (isset($p_params["remove_callback"])) {
            $l_remove_callback = $p_params["remove_callback"];
        } else {
            $l_remove_callback = '';
        }

        // Name-Handling.
        if ($p_params["name"] != "") {
            $l_strOptionsName = $p_params["name"] . "__selected_box";
            $l_strSelectedValues = $p_params["name"] . "__selected_values";
        } else {
            $l_strOptionsName = "SelectBox__selected_box";
            $l_strSelectedValues = "SelectBox__selected_values";
        }

        // Unserialize data if needed.
        if (is_string($p_params["p_arData"])) {
            $p_params["p_arData"] = unserialize($p_params["p_arData"]);
        }

        if (isset($p_params['p_strSelectedBit'])) {
            $l_selectedBit = intval($p_params['p_strSelectedBit']);
        }

        // Extract data to selected / unselected.
        if (is_array($p_params["p_arData"])) {
            if (count($p_params["p_arData"]) > 0) {
                $l_options = [];

                foreach ($p_params["p_arData"] as $l_val_ar) {
                    if (is_array($l_val_ar)) {
                        $l_id = isset($l_val_ar['id']) ? $l_val_ar['id'] : '';
                        $l_value = isset($l_val_ar['val']) ? $l_val_ar['val'] : '';

                        if (isset($l_selectedBit)) {
                            $l_selected = ($l_id & $l_selectedBit) ? ' selected="selected"' : '';
                        } else {
                            $l_selected = isset($l_val_ar['sel']) && $l_val_ar['sel'] ? ' selected="selected"' : '';
                        }

                        $l_sticky = isset($l_val_ar['sticky']) ? $l_val_ar['sticky'] : '';

                        if ($l_sticky) {
                            $l_arSticky[$l_id] = $l_sticky;
                        }

                        if ($l_selected) {
                            $l_arrSelectedValues[$l_id] = $l_value;
                        }

                        $l_options[$l_id] = '<option value="' . $l_id . '" ' . $l_selected . '>' . $l_value . '</option>';
                    } else {
                        return '<div class="error p5 ml20">Error: dialog_list structure incompatible: ' . nl2br(var_export($p_params["p_arData"], true)) .
                            '<br />Use: array(array(id => int, val = string, sel = 1/0, sticky = 1/0))</div>';
                    }
                }

                if (!isset($p_params['p_bSort']) || $p_params['p_bSort']) {
                    asort($l_options);
                }

                $p_params['chosen-btn-all'] = isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__CHOOSE_ALL');
                $p_params['chosen-btn-inverted'] = isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__CHOOSE_INVERTED');
                $p_params['chosen-btn-none'] = isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__CHOOSE_NONE');
                $p_params['additional_value_field'] = $l_strSelectedValues;

                $l_out = $this->getInfoIcon($p_params) . '<input type="hidden" name="' . $l_strSelectedValues . '" id="' . $l_strSelectedValues . '" value="' .
                    implode(',', array_keys($l_arrSelectedValues)) . '" />' . "<select name='{$l_strOptionsName}[]' id='{$l_strOptionsName}' multiple class=\"input " .
                    $p_params['p_strClass'] . "\"
						data-placeholder=\"" . isys_application::instance()->container->get('language')
                        ->get(isset($p_params['placeholder']) ? $p_params['placeholder'] : 'LC__SMARTY__PLUGIN__DIALOGLIST__CHOSEN') . "\"
	                    onChange=\"updateDialogList(this.id, '{$l_strSelectedValues}');{$l_add_callback};{$l_remove_callback}\" {$l_extra}>" . implode('', $l_options) .
                    "</select>";

                $l_js = '<script type="text/javascript">(function() {
				    var $field = $("' . $l_strOptionsName . '");
				    new Chosen($field, {"no_results_text": "' . isys_application::instance()->container->get('language')
                        ->get('LC__SMARTY__PLUGIN__DIALOGLIST__NO_RESULTS') . '", search_contains: true});
				    new ChosenExtension($field, ' . isys_format_json::encode($p_params) . ');
				    })();</script>';

                return $l_out . $l_js;
            } else if (isset($p_params['emptyMessage'])) {
                return $this->getInfoIcon($p_params) . '<span class="emptyMessage">' . isys_application::instance()->container->get('language')
                        ->get($p_params['emptyMessage']) . '</span>';
            }
        } else if (isset($p_params['emptyMessage'])) {
            return $this->getInfoIcon($p_params) . '<span class="emptyMessage">' . isys_application::instance()->container->get('language')
                    ->get($p_params['emptyMessage']) . '</span>';
        }

        return '';
    }
}
