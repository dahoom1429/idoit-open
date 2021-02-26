<?php

/**
 * i-doit
 *
 * smarty plugin: link
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stückn <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_smarty_plugin_f_link extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return ['p_strValue'];
    }

    /**
     * View mode returns the content value.
     *
     * @global  array                   $g_dirs
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_dirs;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if (isys_glob_is_edit_mode() || (isset($p_params["p_editMode"]) && $p_params["p_editMode"])) {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        return $this->getInfoIcon($p_params) . isys_helper_link::create_anker($p_params["p_strValue"], $p_params["p_strTarget"],
                '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" alt="Link" class="vam" /> <span class="vam">', '</span>');
    }

    /**
     * Parameters are given in an array $p_params:
     *     Basic parameters
     *         p_strAccept          -> comma seperated list of accepted mime types
     *         p_bDisabled          -> disable
     *         p_strName            -> name
     *         p_strSize            -> size
     *         p_strPlaceholder     -> HTML5 Placeholder attribute
     *         p_strStyle           -> set the style
     *         p_strClass           -> set the class
     *         p_strTitle           -> title for e.g. tooltip
     *         p_Tab                -> tabindex
     *         p_strOnFocus         -> onfocus handler
     *         p_strOnClick         -> onclick handler
     *         p_strMouseOver       -> onmouseover handler
     *         p_strMouseDown       -> onmousedown handler
     *         p_strOnKeyPress      -> onkeypress handler
     *
     *     Input specific params
     *         p_strError           -> error flag (1 or 0)
     *         p_bInfoIconDisabled  -> disable the InfoIcon
     *
     * @param   isys_component_template $p_tplclass
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

        $this->m_strPluginClass = "f_link";
        $this->m_strPluginName = $p_params["name"];

        if (empty($p_params["p_strClass"])) {
            $p_params["p_strClass"] = "input";
        }

        // ID-ID-3233  Bugfix!
        //$p_param["p_strValue"] = str_replace("\\\\", "\\", $p_param["p_strValue"]);

        if (is_null($p_params['p_nSize'])) {
            $p_params['p_nSize'] = '65';
        }

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        if ($p_params["p_bInvisible"] == true) {
            $l_input_type = "hidden";
        } else {
            $l_input_type = "text";
        }

        $l_description_tag = '';

        if (!empty($p_params['p_description'])) {
            $l_description_tag = '<p class="mt5 ml20" style="font-size: smaller;">' . isys_application::instance()->container->get('language')
                    ->get($p_params['p_description']) . '</p>';
        }

        if (isset($p_params['p_strPlaceholder'])) {
            $p_params['p_strPlaceholder'] = ' placeholder="' . isys_application::instance()->container->get('language')
                    ->get($p_params['p_strPlaceholder']) . '" ';
        } else {
            $p_params['p_strPlaceholder'] = ' placeholder="http://" ';
        }

        return $this->getInfoIcon($p_params) . '<input ' . $p_params['name'] . ' type="' . $l_input_type . '" ' . $p_params['p_strID'] . ' ' . $p_params['p_strTitle'] . ' ' .
            $p_params['p_strClass'] . ' ' . $p_params['p_bDisabled'] . ' ' . $p_params['p_bReadonly'] . ' ' . $p_params['p_strStyle'] . ' ' . $p_params['p_strValue'] . ' ' .
            $p_params['p_strPlaceholder'] . ' ' . $p_params['p_strTab'] . ' ' . $p_params['p_nSize'] . ' ' . $p_params['p_nMaxLen'] . ' ' . $p_params['p_onMouseOver'] . ' ' .
            $p_params['p_onMouseOut'] . ' ' . $p_params['p_onClick'] . ' ' . $p_params['p_onKeyPress'] . ' ' . $p_params['p_onKeyDown'] . ' ' .
            $p_params['p_validation_mandatory'] . ' ' . $p_params['p_validation_rule'] . '/>' . $l_description_tag;
    }
}
