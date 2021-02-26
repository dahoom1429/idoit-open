<?php

/**
 * i-doit
 *
 * Smarty plugin for text input fields
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_text extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return [
            'p_strValue',
            'p_bDisabled'
        ];
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

        if ($p_params['p_bInvisible'] == true) {
            return '';
        }

        if ((!isset($p_params['p_strValue']) || is_null($p_params['p_strValue'])) && isset($p_params['default'])) {
            $p_params['p_strValue'] = $p_params['default'];
        }

        if ($p_params['p_bEditMode'] == '1') {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        $l_strRet = $this->getInfoIcon($p_params);

        if ($p_params['p_bPassword']) {
            return $l_strRet . (empty($p_params['p_strValue']) ? '' : str_repeat('*', strlen($p_params['p_strValue'])));
        }

        if ($p_params['p_strStyle']) {
            $p_params['p_strStyle'] = ' style=\'' . $p_params['p_strStyle'] . '\'';
        }

        if (isset($p_params['p_bStripSlashes']) && $p_params['p_bStripSlashes']) {
            $p_params['p_strValue'] = stripslashes($p_params['p_strValue']);
        }

        if (is_scalar($p_params['p_strValue'])) {
            if (isset($p_params['p_bHtmlDecode']) && $p_params['p_bHtmlDecode']) {
                $p_params['p_strValue'] = isys_glob_html_entity_decode($p_params['p_strValue']);
            }

            if (isset($_GET[C__SEARCH__GET__HIGHLIGHT]) && (bool)isys_tenantsettings::get('search.highlight-search-string', 1)) {
                if (preg_match('/<("[^"]*"|\'[^\']*\'|[^\'">])*>/', $p_params['p_strValue'])) {
                    if (strpos($p_params['p_strValue'], '<script')) {
                        $p_params['p_strValue'] = preg_replace('((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))', '', $p_params['p_strValue']);
                    }
                    if (isys_tenantsettings::get('cmdb.limits.obj-browser.objects-rendering', 'comma') === 'comma') {
                        $p_params['p_strValue'] = strip_tags($p_params['p_strValue']);
                    } else {
                        $p_params['p_strValue'] = strip_tags($p_params['p_strValue'], '<ul><li><a>');
                    }
                }

                $p_params['p_strValue'] = isys_string::highlight($_GET[C__SEARCH__GET__HIGHLIGHT], $p_params['p_strValue']);
            }
        }

        return $l_strRet . '<span' . $p_params['p_strStyle'] . '>' . $p_params['p_strValue'] . '</span>';
    }

    /**
     * Parameters are given in an array $p_params
     *       -----------------------------------------------------------------
     *       // Basic parameters
     *       name                -> name
     *       type                -> smarty plug in type
     *       p_strPopupType      -> pop up type
     *       p_strPopupLink      -> link for the pop up image
     *       p_strValue          -> value
     *       p_nTabIndex         -> tabindex
     *       p_nTabOffset        -> taboffset
     *       p_strTitle          -> title (and tooltip)
     *       p_strAlt            -> alt tag for the pop up image
     *       p_strPlaceholder    -> HTML5 Placeholder attribute
     *         p_bPassword         -> Type password
     *         p_bPasswordHideValue -> Show *** in Field or nothing
     *
     *       // InfoIcon parameters
     *       p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *       p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *       // Style parameters
     *       p_strID             -> id
     *       id                  -> id
     *       p_strClass          -> class
     *       p_strStyle          -> style
     *       p_bSelected         -> preselected, looks like onMouseOver style
     *       p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *       p_bInvisible        -> don't show anything at all
     *       p_bDisabled         -> disabled
     *       p_bReadonly         -> readonly
     *
     *       // JavaScript parameters
     *       p_onClick           -> onClick
     *       p_onChange          -> onChange
     *       p_onMouseOver       -> onMouseOver
     *       p_onMouseOut        -> onMouseOut
     *       p_onMouseMove       -> onMouseMove
     *       p_onKeyDown         -> onKeyDown
     *       p_onKeyPress        -> onKeyPress
     *       p_onKeyUp           -> onKeyUp
     *
     *       // Special parameters
     *       p_nSize             -> size
     *       p_nRows             -> rows
     *       p_nCols             -> cols
     *       p_nMaxLen           -> maxlen
     *       p_strTable          -> name of the database table to use for filling the plug in list
     *       p_arData            -> array with data to fill the plug in list
     *       p_bDbFieldNN        -> field is NaN (not a number):
     *       p_strSelectedID     -> pre selected value in the list
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $p_params = $this->prepare_input_group($p_params);

        // This can be defined in the validation config.
        if (isset($p_params['force_dialog'])) {
            if ($p_params['force_dialog'] === true) {
                $l_dialog = new isys_smarty_plugin_f_dialog();

                $p_params['p_bSort'] = false;
                $p_params['p_bDbFieldNN'] = true;
                $p_params['p_arData'] = ['' => ''];
                $p_params['p_strSelectedID'] = $p_params['p_strValue'];

                if (is_array($p_params['force_dialog_data'])) {
                    foreach ($p_params['force_dialog_data'] as $l_data) {
                        if (is_scalar($l_data)) {
                            $p_params['p_arData'][addslashes(strip_tags($l_data))] = addslashes(strip_tags($l_data));
                        }
                    }
                }

                if (!isset($p_params['p_arData'][isys_glob_htmlentities($p_params['p_strValue'])])) {
                    $p_params['p_arData'][isys_glob_htmlentities($p_params['p_strValue'])] = isys_glob_htmlentities($p_params['p_strValue']);
                    // @todo  Please check what's going on here. This parameter should usually be of type boolean.
                    $p_params["p_arDisabled"] = serialize([isys_glob_htmlentities($p_params['p_strValue']) => true]);
                }

                return $l_dialog->navigation_edit($p_tplclass, $p_params);
            }
        }

        $this->m_strPluginClass = 'f_text';
        $this->m_strPluginName = $p_params['name'];

        // Default css class.
        $p_params['p_strClass'] = 'input ' . (isset($p_params['p_strClass']) ? $p_params['p_strClass'] : '');

        if ((!isset($p_params['p_strValue']) || is_null($p_params['p_strValue'])) && isset($p_params['default'])) {
            $p_params['p_strValue'] = $p_params['default'];
        }

        // Is the error flag set?
        if (!empty($p_params['p_strError'])) {
            $p_params['p_strError'] = $p_params['p_strError'] . 'Error';
        }

        if (isset($p_params['p_bStripSlashes']) && $p_params['p_bStripSlashes']) {
            $p_params['p_strValue'] = stripslashes($p_params['p_strValue']);
        }

        if (isset($p_params['p_strValue']) && is_scalar($p_params['p_strValue'])) {
            $p_params['p_strValue'] = htmlentities($p_params['p_strValue'], ENT_QUOTES, $GLOBALS['g_config']['html-encoding']);
        }

        if (!isset($p_params['p_nSize']) || is_null($p_params['p_nSize'])) {
            $p_params['p_nSize'] = '65';
        }

        if (isset($p_params['p_strPlaceholder'])) {
            $p_params['p_strPlaceholder'] = ' placeholder="' . isys_application::instance()->container->get('language')
                    ->get($p_params['p_strPlaceholder']) . '" ';
        }

        $l_description_tag = '';

        if (isset($p_params['p_description']) && !empty($p_params['p_description'])) {
            $l_description_tag = '<p class="mt5" style="font-size: smaller;">' . $this->getInfoIcon($p_params) . isys_application::instance()->container->get('language')
                    ->get($p_params['p_description']) . '</p>';
        }

        if (isset($p_params['p_strSuggest']) && empty($p_params['p_strSuggestView'])) {
            $p_params['p_strSuggestView'] = $p_params['name'];
        }

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        if (isset($p_params['p_bPassword']) && $p_params['p_bPassword']) {
            $l_input_type = 'password';
            if (isset($p_params['p_bPasswordHideValue'])) {
                unset($p_params['p_strValue']);
            }
        } else {
            $l_input_type = 'text';
        }

        if (isset($p_params['p_bInvisible']) && $p_params['p_bInvisible']) {
            $l_input_type = 'hidden';
        }

        if (isset($p_params['inputType']) && !empty($p_params['inputType'])) {
            $l_input_type = $p_params['inputType'];
        }

        $extra = '';

        if (isset($p_params['extra-params'])) {
            foreach ($p_params['extra-params'] as $k => $param) {
                $extra .= " $k='$param'";
            }
        }

        $l_strOut = $this->getInfoIcon($p_params) . '<input ' . $p_params['name'] . ' ' . 'type=\'' . $l_input_type . '\' ' . $p_params['p_strID'] . ' ' .
            $p_params['p_strTitle'] . ' ' . $p_params['p_strClass'] . ' ' . $p_params['p_strPlaceholder'] . ' ' . $p_params['p_bDisabled'] . ' ' . $p_params['p_bReadonly'] .
            ' ' . $p_params['p_strStyle'] . ' ' . $p_params['p_strValue'] . ' ' . $p_params['p_nTabIndex'] . ' ' . $p_params['p_nSize'] . ' ' . $p_params['p_nMaxLen'] . ' ' .
            $p_params['p_onMouseOver'] . ' ' . $p_params['p_onMouseOut'] . ' ' . $p_params['p_onChange'] . ' ' . $p_params['p_onClick'] . ' ' . $p_params['p_onKeyPress'] .
            ' ' . $p_params['p_onKeyUp'] . ' ' . $p_params['p_onFocus'] . ' ' . $p_params['p_onBlur'] . ' ' . $p_params['p_dataIdentifier'] . ' ' . $p_params['p_onKeyDown'] .
            ' ' . $p_params['p_additional'] . ' ' . $p_params['p_validation_mandatory'] . ' ' . $p_params['p_validation_rule'] . $extra . ' />';

        // Attach WIKI Link.
        $l_strOut .= $this->attach_wiki($p_params);

        if (isset($p_params['p_strSuggest'])) {
            $l_suggestField = $p_params['p_strSuggestView'] ?: $p_params['name'];
            $l_parameters = $p_params['p_strSuggestParameters'] ?: '';

            // @see  ID-4514  In order to set options later, we store the suggestion in the view elements "store".
            $l_strOut .= '<script type=\'text/javascript\'>' .
                '$(\'' . $l_suggestField . '\').store(\'suggestion\', new idoit.Suggest(\'' . $p_params['p_strSuggest'] . '\', \'' . $l_suggestField . '\', \'' . $p_params['p_strSuggestHidden'] . '\', {' . $l_parameters . '}));' .
                '</script>';
        }

        return $this->render_input_group($l_strOut . $l_description_tag, $p_params);
    }
}
