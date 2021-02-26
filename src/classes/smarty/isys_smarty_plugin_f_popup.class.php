<?php

/**
 * i-doit
 *
 * Smarty plugin for popups.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_popup extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Gets the map for Smarty Meta Map (SM²).
     *
     * @return  array
     */
    public static function get_meta_map()
    {
        // LF: "multiselect" is necessary for isys_cmdb_action_category_change.
        return [
            "p_strPopupType",
            "p_strSelectedID",
            "p_arData",
            "p_strTable",
            "p_strValue",
            "multiselect"
        ];
    }

    /**
     * Provides HTML code for viewing.
     *
     * @param   isys_component_template $p_tplclass Template
     * @param   array                   $p_params   Parameters
     *
     * @return  string  Returns null on error.
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        $p_params['disableInputGroup'] = true;

        return $this->navigation_edit($p_tplclass, $p_params);
    }

    /**
     * Provides HTML code for editing.
     *
     * @param   isys_component_template $p_tplclass Template
     * @param   array                   $p_params   Parameters
     *
     * @return  string  Returns null on error.
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        // ID-4178 This is necessary to display the wiki-link. Please check if this causes unwanted side-effects
        $this->m_strPluginName = $p_params['name'];

        if (isset($p_params["p_strPopupType"])) {
            $l_popuptype = $p_params["p_strPopupType"];
            $l_classname = "isys_popup_" . $l_popuptype;

            if (class_exists($l_classname)) {
                $l_instance = new $l_classname;
                if (@is_object($l_instance)) {
                    if (isset($p_params['p_bEnableMetaMap'])) {
                        if ($p_params['p_bEnableMetaMap']) {
                            $this->m_enableMetaMap = true;
                        } else {
                            $this->m_enableMetaMap = false;
                        }
                    }

                    $l_params = $this->prepare_input_group($p_params);

                    $l_no_wiki_temp = $l_params["nowiki"];

                    // ID-4178 "Wiki-Link" was displayed before the popup icon - so we remove it here and display it later.
                    $l_params["nowiki"] = 1;

                    $l_return = $l_instance->handle_smarty_include($p_tplclass, $l_params);

                    $l_params["nowiki"] = $l_no_wiki_temp;

                    if ($p_tplclass->editmode() || $l_params["p_bEditMode"] == true) {
                        $l_return .= $this->attach_wiki($l_params);
                    }

                    return $this->render_input_group($l_return, $l_params);
                }
            }
        }

        return null;
    }
}
