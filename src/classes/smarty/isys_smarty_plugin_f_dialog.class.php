<?php

/**
 * i-doit
 *
 * Smarty plugin for Dialog(+)
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     Andre Woesten <awoesten@i-doit.org> - 25.08.05
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_dialog extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            "p_strSelectedID",
            "p_strTable",
            "p_arData"
        ];
    }

    /**
     * Navigation view for dialog-fields.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        $lang = isys_application::instance()->container->get('language');

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = 'f_button';
        $this->m_strPluginName = $p_params['name'];

        $l_strValue = '';

        if ($p_params['p_bEditMode']) {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        $l_arData = [];

        if (!empty($p_params['p_arData'])) {
            if (is_array($p_params['p_arData'])) {
                $l_arData = $p_params['p_arData'];
            } elseif (is_string($p_params['p_arData'])) {
                $l_arData = unserialize($p_params['p_arData']);
            }
        } elseif (!empty($p_params['p_strTable'])) {
            if ($p_params['status'] == 0) {
                $l_status = null;
            } else {
                $l_status = C__RECORD_STATUS__NORMAL;
            }

            $l_arData = $this->get_array_data($p_params['p_strTable'], $l_status, $p_params['order'], $p_params['condition']);
        }

        if (is_array($l_arData) && isset($p_params['p_strDbFieldNN'])) {
            $l_arData[-1] = $lang->get($p_params['p_strDbFieldNN']);
        }

        // Evaluate current value
        if (isset($p_params['p_strSelectedID'])) {
            if ($l_arData != null) {
                $l_multiple = (strpos($p_params['p_strSelectedID'], ',') !== false);
                $l_multiple_items = explode(',', $p_params['p_strSelectedID']);

                foreach ($l_arData as $l_content) {
                    if (is_array($l_content)) {
                        if (isset($l_content[$p_params['p_strSelectedID']])) {
                            $l_value = $l_content[$p_params['p_strSelectedID']];
                            $l_strValue = isys_glob_htmlentities(isys_glob_str_stop($lang->get($l_value), isys_tenantsettings::get('maxlength.dialog_plus', 110)));

                            continue;
                        }
                    } else {
                        if ($l_multiple) {
                            $l_strValue = [];

                            foreach ($l_multiple_items as $l_item) {
                                $l_strValue[] = isys_glob_htmlentities(isys_glob_str_stop(
                                    $lang->get($l_arData[$l_item]),
                                    isys_tenantsettings::get('maxlength.dialog_plus', 110)
                                ));
                            }

                            $p_params['p_bInfoIconSpacer'] = 0;
                            $l_strValue = '<ul class="m0 ml20 list-style-none"><li>' . implode('</li><li>', $l_strValue) . '</li></ul>';
                        } elseif (isset($l_arData[$p_params['p_strSelectedID']])) {
                            $l_value = $l_arData[$p_params['p_strSelectedID']];
                            $l_strValue = (is_numeric($l_value)) ? $l_value : isys_glob_htmlentities(isys_glob_str_stop(
                                $lang->get($l_value),
                                isys_tenantsettings::get('maxlength.dialog_plus', 110)
                            ));
                        }

                        continue;
                    }
                }
            }
        } else {
            $l_strValue = '-';
        }

        if (empty($l_strValue) && isset($p_params['p_strValue'])) {
            $l_strValue = $p_params['p_strValue'];
        }

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        // ID-4515: Adding condition to avoid highlighting when the setting is turned off
        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]) && (bool)isys_tenantsettings::get('search.highlight-search-string', 1)) {
            $l_strValue = isys_string::highlight($_GET[C__SEARCH__GET__HIGHLIGHT], $l_strValue);
        }

        return $this->getInfoIcon($p_params) . $l_strValue;
    }

    /**
     * Returns the data from a table in an array.
     *
     * @param   string  $p_strTablename
     * @param   integer $p_status
     * @param   string  $p_order
     * @param   string  $p_condition
     *
     * @return  array
     * @throws Exception
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_array_data($p_strTablename, $p_status = C__RECORD_STATUS__NORMAL, $p_order = null, $p_condition = null, $selectQuery = null)
    {
        $l_return = [];

        if ($selectQuery !== null) {
            $l_tblres = isys_application::instance()->container->get('cmdb_dao')->retrieve($selectQuery);
        } else {
            $l_tblres = isys_glob_get_data_by_table($p_strTablename, null, $p_status, $p_order, $p_condition);
        }

        if ($l_tblres != null) {
            if ($l_tblres->num_rows() > 0) {
                while ($l_tblrow = $l_tblres->get_row()) {
                    $l_return[$l_tblrow[$p_strTablename . "__id"]] = isys_application::instance()->container->get('language')
                        ->get($l_tblrow[$p_strTablename . "__title"]);
                }
            }
        }

        return $l_return;
    }

    /**
     * Parameters are given in an array $p_params:
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
     *     InfoIcon parameters
     *         p_bInfoIcon             -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer       -> if set to 0 no image is shown at all
     *     Style parameters
     *         p_strID                 -> id
     *         p_strClass              -> class
     *         p_strStyle              -> style
     *         p_bSelected             -> preselected, looks like onMouseOver style
     *         p_bEditMode             -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible            -> don't show anything at all
     *         p_bDisabled             -> disabled (and add a hidden field to save the value when sending the form)
     *         p_bReadonly             -> readonly
     *     JavaScript parameters
     *         p_onClick               -> onClick
     *         p_onChange              -> onChange
     *         p_onMouseOver           -> onMouseOver
     *         p_onMouseOut            -> onMouseOut
     *         p_onMouseMove           -> onMouseMove
     *         p_onKeyDown             -> onKeyDown
     *         p_onKeyPress            -> onKeyPress
     *     Special parameters
     *         p_bSort                 -> Sort the given p_arData or not (boolean)
     *         p_nSize                 -> size
     *         p_nRows                 -> rows
     *         p_nCols                 -> cols
     *         p_nMaxLen               -> maxlen
     *         p_strTable              -> name of the database table to use for filling the plug in list
     *         p_arData                -> array with data to fill the plug in list
     *         p_bDbFieldNN            -> field is NaN (not a number):
     *         p_strSelectedID         -> pre selected value in the list
     *         p_bPlus                 -> Show + button to allow non-sysop users to add entries
     *         p_optionsTable          -> name of the database table to use for the options
     *         p_const                 -> constant to get values of the specific constant
     *         p_dataCallback          -> Callback method to retrieve array data
     *         p_dataCallbackParameter -> Parameter which data callback needs to execute
     *     Parameters for a combined dialogbox
     *
     *     Parameters needed for the first dialog box
     *         p_ajaxTable             -> Target table where the data lies
     *         p_ajaxIdentifier        -> Identifier of the second dialog box
     *     Parameters needed for the second dialog box
     *         p_strSecTableIdentifier -> Identifier of the parent dialog box
     *
     *       Parameter needed to determine if its a chosen dialog box
     *           chosen                   -> true or false
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @throws  Exception
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        $lang = isys_application::instance()->container->get('language');

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        try {
            global $g_dirs;

            $p_params["p_additional"] = '';

            $this->m_strPluginClass = 'f_button';
            $this->m_strPluginName = $p_params["name"];

            // p_editMode
            if (isset($p_params["p_editMode"]) && !$p_params["p_editMode"]) {
                return $this->navigation_view($p_tplclass, $p_params);
            }

            $l_strHidden = '';
            $l_arData = [];

            if ($p_params["p_bDisabled"]) {
                $l_strHidden = '<input type="hidden" name="' . $p_params['name'] . '" value="' . $p_params['p_strSelectedID'] . '" />';
            }

            $l_arDisabled = [];

            if (is_array($p_params["p_arDisabled"])) {
                $l_arDisabled = $p_params["p_arDisabled"];
            } elseif (is_string($p_params["p_arDisabled"])) {
                $l_arDisabled = unserialize($p_params["p_arDisabled"]);
            }

            // Show select box?
            if ($p_params["p_bInvisible"] == "1") {
                return "";
            }

            // Check for provided data callback function
            if (!empty($p_params['p_dataCallback'])) {
                $callback = $p_params['p_dataCallback'];
                $callbackParameter = $p_params['p_dataCallbackParameter'];

                // Processing desired callback method
                if (is_string($callback)) {
                    try {
                        // Decode json
                        $callback = isys_format_json::decode($callback, true);

                        // Validate structure
                        if (!is_array($callback) || count($callback) !== 2) {
                            $callback = null;
                        }
                    } catch (\idoit\Exception\JsonException $exception) {
                        $callback = null;
                    }
                }

                // Processing desired parameters for execution
                if (is_string($callbackParameter)) {
                    try {
                        // Decode json
                        $callbackParameter = isys_format_json::decode($callbackParameter, true);

                        // Ensure that it is an array
                        if (!is_array($callbackParameter)) {
                            $callbackParameter = [];
                        }
                    } catch (\idoit\Exception\JsonException $exception) {
                        $callbackParameter = [];
                    }
                }

                // Check whether specified method exists
                if (is_array($callback) && method_exists($callback[0], $callback[1])) {
                    // Create isys_callback instance for execution in the below if branch
                    $p_params['p_arData'] = new isys_callback($callback, $callbackParameter);
                }
            }

            if (!empty($p_params["p_arData"])) {
                // GET-array with data from $p_params
                if (is_array($p_params["p_arData"])) {
                    $l_arData = $p_params["p_arData"];
                } elseif (is_string($p_params["p_arData"])) {
                    $l_arData = unserialize($p_params["p_arData"]);
                } elseif ($p_params["p_arData"] instanceof isys_callback) {
                    $l_arData = $p_params["p_arData"]->execute();
                }
            } else {
                // Get array from table
                if (!empty($p_params["p_strTable"])) {
                    if ($p_params["status"] == 0) {
                        $l_status = null;
                    } else {
                        $l_status = C__RECORD_STATUS__NORMAL;
                    }

                    if ($p_params["p_identifier"]) {
                        if ($p_params["p_strTable"] === "isys_dialog_plus_custom") {
                            $p_params["condition"] = "isys_dialog_plus_custom__identifier = '" . $p_params["p_identifier"] . "'";
                        }
                    }

                    if ($p_params["p_const"]) {
                        $p_params["condition"] = ($p_params["condition"] ?: '') . $p_params["p_strTable"] . "__const = '" . $p_params["p_const"] . "'";
                    }

                    if (isset($p_params['conditionValue'])) {
                        $p_params['condition'] = sprintf($p_params['condition'], $p_params['conditionValue']);
                    }

                    if (isset($p_params['select'])) {
                        $p_params['select'] = $p_params['select'] . ' WHERE ' . $p_params['condition'];
                    }

                    if ($_GET["secTable"] && $_GET["secTableID"]) {
                        $p_params["condition"] = $p_params["p_strTable"] . "__" . $_GET["secTable"] . "__id = '" . $_GET["secTableID"] . "'";
                    } elseif ($p_params["secTable"] && $p_params["secTableID"]) {
                        if (is_object($p_params["secTableID"])) {
                            if (isset($_GET[C__CMDB__GET__OBJECT])) {
                                $l_request = isys_request::factory()
                                    ->set_object_id($_GET[C__CMDB__GET__OBJECT]);
                                $p_params["secTableID"] = $p_params["secTableID"]->execute($l_request);
                                $p_params["condition"] = $p_params["p_strTable"] . "__" . $p_params["secTable"] . "__id = '" . $p_params["secTableID"] . "'";
                            }
                        } else {
                            $p_params["condition"] = $p_params["p_strTable"] . "__" . $p_params["secTable"] . "__id = '" . $p_params["secTableID"] . "'";
                        }
                    } elseif ($p_params["secTable"]) {
                        $p_params["condition"] = $p_params["p_strTable"] . "__id = FALSE";
                    }

                    /**
                     * @see ID-5540
                     */

                    /**
                     * Status condition for retrieving only selectable dialog entries -
                     * Those in status <> C__RECORD_STATUS__ARCHIVED and C__RECORD_STATUS__DELETED
                     */
                    if (empty($l_status) && isys_application::instance()->container->get('database')->is_field_existent($p_params['p_strTable'], $p_params['p_strTable'] . '__status')) {
                        $statusCondition = ' ' . $p_params['p_strTable'] . '__status NOT IN(' . C__RECORD_STATUS__ARCHIVED . ', ' . C__RECORD_STATUS__DELETED . ')';

                        // Check for already setted condition for right concatenation
                        if (is_string($p_params['condition']) && $p_params['condition'] !== '') {
                            $p_params['condition'] .= ' AND ' . $statusCondition;
                        } else {
                            $p_params['condition'] = $statusCondition;
                        }
                    }

                    $l_arData = $this->get_array_data($p_params["p_strTable"], null, $p_params["order"], $p_params["condition"], ($p_params['select'] ?: null));
                }
            }

            if (isset($p_params["id"])) {
                $p_params["p_strID"] = $p_params["id"];
            }

            if (!isset($p_params["p_strClass"])) {
                $p_params["p_strClass"] = '';
            }

            $p_params["p_strClass"] = "input " . $p_params["p_strClass"];

            // Enable chosen?
            if (isset($p_params['chosen']) && $p_params['chosen']) {
                $p_params['p_strClass'] .= ' chosen-select';
            }

            if ($p_params['placeholder']) {
                $p_params["p_additional"] .= 'data-placeholder="' . $lang->get($p_params['placeholder']) . '"';
            }
            if (isset($p_params['extra-params'])) {
                foreach ($p_params['extra-params'] as $k => $param) {
                    $p_params["p_additional"] .= "$k = '$param'";
                }
            }

            // Handle secidentifier
            $l_attribute_secidentifier = '';
            if (isset($p_params['p_strSecTableIdentifier'])) {
                $l_attribute_secidentifier = 'data-secidentifier="' . $p_params['p_strSecTableIdentifier'] . '" ';
            }

            $p_params = $this->prepare_input_group($p_params);
            $this->getStandardAttributes($p_params);
            $this->getJavascriptAttributes($p_params);

            if (empty($l_arData) && isset($p_params['emptyMessage']) && $p_params['emptyMessage'] && !isset($p_params["p_bPlus"])) {
                $l_strOut = $this->getInfoIcon($p_params) . '<span class="emptyMessage">' . $lang->get($p_params['emptyMessage']) . '</span> <input type="hidden" ' . $p_params["name"] . ' value="" />';
            } else {
                $l_strOut = $this->getInfoIcon($p_params) . "<select " . $p_params["name"] . " " . $p_params["p_strClass"] . " " . $p_params["p_strStyle"] . " " .
                    $p_params["p_strTitle"] . " " . $p_params["p_strID"] . " " . $p_params["p_onClick"] . " " . $p_params["p_onChange"] . " " . $p_params["p_bDisabled"] .
                    " " . $p_params["p_bReadonly"] . " " . $p_params["p_strTabIndex"] . " " . $p_params["p_nSize"] . " " . $p_params["p_onKeyPress"] . " " .
                    $p_params["p_onKeyDown"] . " " . $p_params["p_onMouseOver"] . " " . $p_params['p_dataIdentifier'] . " " . $p_params["p_onMouseOut"] . " " .
                    $l_attribute_secidentifier . " " . $p_params["p_additional"] . " " . $p_params['p_validation_mandatory'] . " " . $p_params['p_validation_rule'] . " " .
                    $p_params["p_multiple"] . " " . ">\n";

                if ($p_params["p_bDbFieldNN"] != "1") {
                    $l_strOut .= $this->get_option(isset($p_params["p_strDbFieldNN"])
                        ? $lang->get($p_params["p_strDbFieldNN"])
                        : ' - ', '-1', ($p_params["p_strSelectedID"] == '-1' || $p_params["p_strSelectedID"] == ''), false);
                }

                if ($p_params["exclude"]) {
                    $l_exc = explode(";", $p_params["exclude"]);
                    if (!$l_exc) {
                        $l_exc = explode(",", $p_params["exclude"]);
                    }

                    foreach ($l_exc as $l_exclude) {
                        if (defined($l_exclude)) {
                            $l_excludes[constant($l_exclude)] = true;
                        } else {
                            $l_excludes[$l_exclude] = true;
                        }
                    }
                }

                if (is_array($l_arData)) {
                    if (isset($p_params['allow_empty']) && $p_params['allow_empty'] && !isset($l_arData[-1])) {
                        $l_arData[-1] = '';
                    }
                    // Sort the Array.
                    if (is_array($l_arData)) {
                        if (!isset($p_params['p_bSort']) || $p_params['p_bSort']) {
                            uasort($l_arData, function ($a, $b) use ($lang) {
                                if ($a == $b) {
                                    return 0;
                                }

                                if (is_array($a) || is_array($b)) {
                                    if (is_array($a)) {
                                        uasort($a, function ($a2, $b2) use ($lang) {
                                            if ($a2 == $b2) {
                                                return 0;
                                            }

                                            return strcasecmp($lang->get($a2), $lang->get($b2));
                                        });
                                    }

                                    if (is_array($b)) {
                                        uasort($b, function ($a2, $b2) use ($lang) {
                                            if ($a2 == $b2) {
                                                return 0;
                                            }

                                            return strcasecmp($lang->get($a2), $lang->get($b2));
                                        });
                                    }

                                    return 0;
                                }

                                return strcasecmp($lang->get($a), $lang->get($b));
                            });
                        }
                    }

                    // Needs to be converted to string otherwise this case is true (2 == '2_4') = true
                    $p_params["p_strSelectedID"] .= '';

                    $l_multiple = (strpos($p_params["p_strSelectedID"], ',') !== false);
                    $l_multiple_values = (isys_format_json::is_json_array($p_params['p_strSelectedID'])) ? isys_format_json::decode($p_params['p_strSelectedID']) :
                        explode(',', $p_params['p_strSelectedID']);

                    // Needs to be converted to string otherwise this case is true (2 == '2_4') = true
                    $p_params["p_strSelectedID"] .= '';

                    foreach ($l_arData as $l_key => $l_content) {
                        if (is_array($l_content)) {
                            if (isset($p_params["sort"]) && $p_params["sort"]) {
                                asort($l_content);
                            }

                            $l_strOut .= "<optgroup label=\"" . $l_key . "\">";

                            foreach ($l_content as $l_contentkey => $l_value) {
                                if (isset($l_excludes[$l_contentkey])) {
                                    continue;
                                }

                                $l_contentkey .= '';

                                $l_strOut .= $this->get_option(
                                    $l_value,
                                    $l_contentkey,
                                    (isset($p_params["p_strSelectedID"]) &&
                                    ($p_params["p_strSelectedID"] == $l_contentkey || $l_multiple && in_array($l_contentkey, $l_multiple_values))),
                                    ($l_arDisabled[$l_contentkey] == true)
                                );
                            }

                            $l_strOut .= "</optgroup>";
                        } else {
                            if (isset($l_excludes[$l_key])) {
                                continue;
                            }

                            $l_key .= '';

                            $l_strOut .= $this->get_option(
                                $l_content,
                                $l_key,
                                (isset($p_params["p_strSelectedID"]) && ($p_params["p_strSelectedID"] == $l_key || $l_multiple && in_array($l_key, $l_multiple_values))),
                                ($l_arDisabled[$l_key] == true)
                            );
                        }
                    }
                }

                $l_strOut .= "</select>";

                if (isset($p_params['secTable']) && (!empty($p_params['secTable']) && empty($p_params['secTableID'])) && $p_params['p_strSecTableIdentifier'] !== 'null') {
                    // Load data via ajax
                    $l_strOut .= '<script type="text/javascript">';
                    $l_strOut .= "if ($('" . $p_params['p_strSecTableIdentifier'] . "').value != -1) new Ajax.Request('?call=combobox&func=load&ajax=1',
                                {
                                    parameters:{
                                        'table':'" . $p_params['p_strTable'] . "',
                                        'parent_table':'" . $p_params['secTable'] . "',
                                        'parent_table_id':$('" . $p_params['p_strSecTableIdentifier'] . "').value
                                    },
                                    method:'post',
                                    onSuccess:function (transport) {
                                        var dialog_field = $('" . $this->m_strPluginName . "').update(''),
                                            json = [];
                                        if (transport.responseText != '[]') {
                                            json = new Hash(transport.responseJSON);
                                        }
                                        " . (((int)$p_params['p_bDbFieldNN'] == 0) ? "dialog_field.insert(new Element('option', {value: '-1'}).update('-'));" : "") . "
                                        json.each(function(item) {
                                            if(item.key == '" . $p_params['p_strSelectedID'] . " '){
                                                dialog_field.insert(new Element('option', {value: item.key, selected: 'selected'}).update(item.value));
                                            } else{
                                                dialog_field.insert(new Element('option', {value: item.key}).update(item.value));
                                            }
                                        });
                                    }
                                });";
                    $l_strOut .= '</script>';
                }

                if (isset($p_params["p_bPlus"]) && !empty($p_params["p_bPlus"]) && $p_params["p_bPlus"] != 'off') {
                    /** @noinspection HtmlUnknownTarget */
                    $l_strOut .= '<a href="javascript:" class="' . str_replace('[]', '', $this->m_strPluginName) .
                        ' dialog-plus input-group-addon input-group-addon-clickable" title="' . $lang->get("LC__UNIVERSAL__NEW_VALUE") . '" onClick="' . $p_params["p_strLink"] . '">' . '<img src="' . $g_dirs["images"] .
                        'icons/silk/page_white_stack.png" alt="" />' . '</a>';
                }
            }

            return $this->render_input_group($l_strOut . $this->attach_wiki($p_params) . $l_strHidden, $p_params);
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }

        return '';
    }

    /**
     * Method for retrieving the option-field.
     *
     * @param   string  $p_value
     * @param   string  $p_key
     * @param   boolean $p_selected
     * @param   boolean $p_disabled
     *
     * @return  string
     * @throws Exception
     */
    private function get_option($p_value, $p_key, $p_selected = false, $p_disabled = false)
    {
        $l_strSelected = ($p_selected) ? ' selected="selected"' : '';
        $l_disabled = ($p_disabled) ? ' disabled="disabled"' : '';

        /* @see  ID-2234 */
        // We decode the HTML entities once, so that we don't have to deal with double-encoded values ("&lt;", "&amp;amp;" ...).
        $p_value = html_entity_decode($p_value, null, $GLOBALS['g_config']['html-encoding']);

        $p_value = isys_glob_str_stop(isys_application::instance()->container->get('language')
            ->get(htmlentities($p_value, null, $GLOBALS['g_config']['html-encoding'])), isys_tenantsettings::get('maxlength.dialog_plus', 110));

        return '<option value="' . $p_key . '" ' . $l_strSelected . $l_disabled . '>' . $p_value . "</option>";
    }
}
