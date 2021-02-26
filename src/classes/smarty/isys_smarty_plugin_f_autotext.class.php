<?php

/**
 * i-doit
 *
 * Smarty plugin for text input fields
 *
 * @package    i-doit
 * @subpackage Smarty_Plugins
 * @author     Van Quyen Hoang <qhoang@synetics.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_autotext extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Parameters are given in an array $p_params[]
     *     Basic parameters
     *         name                -> name
     *         type                -> smarty plug in type
     *         p_strPopupType      -> pop up type
     *         p_strPopupLink      -> link for the pop up image
     *         p_strValue          -> value
     *         p_nTabIndex         -> tabindex
     *         p_nTabOffset        -> taboffset
     *         p_strTitle          -> title (and tooltip)
     *         p_strAlt            -> alt tag for the pop up image
     *
     *     InfoIcon parameters
     *         p_bInfoIcon         -> if set to 0 an empty image is shown, otherwise the InfoIcon
     *         p_bInfoIconSpacer   -> if set to 0 no image is shown at all
     *
     *     Style parameters
     *         p_strID             -> id
     *         id                   -> id
     *         p_strClass          -> class
     *         p_strStyle          -> style
     *         p_bSelected         -> preselected, looks like onMouseOver style
     *         p_bEditMode         -> if set to 1 the plug in is always shown in edit style
     *         p_bInvisible        -> don't show anything at all
     *         p_bDisabled         -> disabled
     *         p_bReadonly         -> readonly
     *
     *     JavaScript parameters
     *         p_onClick           -> onClick
     *         p_onChange          -> onChange
     *         p_onMouseOver       -> onMouseOver
     *         p_onMouseOut        -> onMouseOut
     *         p_onMouseMove       -> onMouseMove
     *         p_onKeyDown         -> onKeyDown
     *         p_onKeyPress        -> onKeyPress
     *         p_onKeyUp           -> onKeyUp
     *
     *     Special parameters
     *         p_nSize             -> size
     *         p_nRows             -> rows
     *         p_nCols             -> cols
     *         p_nMaxLen           -> maxlen
     *         p_strTable          -> name of the database table to use for filling the plug in list
     *         p_arData            -> array with data to fill the plug in list
     *         p_bDbFieldNN        -> field is NaN (not a number):
     *         p_strSelectedID     -> pre selected value in the list
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = 'f_autotext';
        $this->m_strPluginName = $p_params['name'];

        $p_params['p_strClass'] = 'input ' . $p_params['p_strClass'];

        // Is the error flag set?
        if (!empty($p_params['p_strError'])) {
            $p_params['p_strError'] = $p_params['p_strError'] . 'Error';
        }

        if (isset($p_params['p_bStripSlashes']) && $p_params['p_bStripSlashes']) {
            $p_params['p_strValue'] = stripslashes($p_params['p_strValue']);
        }

        $p_params['p_strValue'] = htmlentities($p_params['p_strValue'], null, $GLOBALS['g_config']['html-encoding']);

        if (is_null($p_params['p_nSize'])) {
            $p_params['p_nSize'] = '65';
        }

        if ($p_params['p_strSuggest'] && $p_params["p_strSuggestParameters"] && $p_params['p_strValue'] > 0) {
            $l_condition = '';
            $p_params['p_strSelectedID'] = $p_params['p_strValue'];

            preg_match("/\".*.\"/", $p_params["p_strSuggestParameters"], $l_matches);
            $l_condition_info = explode(',', trim(str_replace('"', '', $l_matches[0])));
            $l_table = $l_condition_info[0];

            if ($p_params['p_strValue'] > 0) {
                $l_condition = $l_table . '__id = ' . $p_params['p_strValue'];
            }

            $l_value = $this->get_array_data($l_table, $l_table . '__id', $l_condition);
            $p_params['p_strValue'] = $l_value;
        }

        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        // Show InfoIcon
        $l_strOut = $this->getInfoIcon($p_params) . '<input ' . $p_params['name'] . ' ' . 'type=\'text\' ' . $p_params['p_strID'] . ' ' . $p_params['p_strTitle'] . ' ' .
            $p_params['p_strClass'] . ' ' . $p_params['p_bDisabled'] . ' ' . $p_params['p_bReadonly'] . ' ' . $p_params['p_strStyle'] . ' ' . $p_params['p_strValue'] . ' ' .
            $p_params['p_nTabIndex'] . ' ' . $p_params['p_nSize'] . ' ' . $p_params['p_nMaxLen'] . ' ' . $p_params['p_onMouseOver'] . ' ' . $p_params['p_onMouseOut'] . ' ' .
            $p_params['p_onChange'] . ' ' . $p_params['p_onClick'] . ' ' . $p_params['p_onKeyPress'] . ' ' . $p_params['p_onKeyUp'] . ' ' . $p_params['p_dataIdentifier'] .
            ' ' . $p_params['p_onKeyDown'] . ' ' . $p_params['p_validation_mandatory'] . ' ' . $p_params['p_validation_rule'] . ' ' . $p_params['p_additional'] . ' />';

        /* Attach WIKI Link */
        $l_strOut .= $this->attach_wiki($p_params);

        if (isset($p_params['p_strSuggest'])) {
            if (isset($p_params['p_strSuggestView'])) {
                $l_suggestField = $p_params['p_strSuggestView'];
            } else {
                $l_suggestField = $p_params['name'];
            }

            if (isset($p_params['p_strSuggestParameters'])) {
                $l_parameters = $p_params['p_strSuggestParameters'];
            } else {
                $l_parameters = '';
            }

            $l_strOut .= '<input type=\'hidden\' value=\'' . $p_params['p_strSelectedID'] . '\' name=\'' . $p_params['p_strSuggestHidden'] . '\' id=\'' .
                $p_params['p_strSuggestHidden'] . '\'>';

            $l_strOut .= '<script type=\'text/javascript\'>' . 'new idoit.Suggest(\'' . $p_params['p_strSuggest'] . '\', \'' . $l_suggestField . '\', \'' .
                $p_params['p_strSuggestHidden'] . '\', {' . $l_parameters . '});' . '</script>';
        }

        return $l_strOut;
    }

    /**
     * Returns the data from a table in an array.
     *
     * @param   string $p_strTablename
     * @param   string $p_order
     * @param   string $p_condition
     *
     * @return  array
     */
    public function get_array_data($p_strTablename, $p_order = null, $p_condition = null)
    {
        $l_tblres = isys_glob_get_data_by_table($p_strTablename, null, null, $p_order, $p_condition);

        if ($l_tblres !== null && is_countable($l_tblres) && count($l_tblres) > 0) {
            $l_tblrow = $l_tblres->get_row();

            return isys_application::instance()->container->get('language')
                ->get($l_tblrow[$p_strTablename . "__title"]);
        }

        return null;
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

        if (is_null($p_params['p_strValue']) && isset($p_params['default'])) {
            $p_params['p_strValue'] = $p_params['default'];
        }

        if ($p_params['p_strSuggest'] && $p_params["p_strSuggestParameters"] && $p_params['p_strValue'] > 0) {
            $p_params['p_strSelectedID'] = $p_params['p_strValue'];

            preg_match("/\".*.\"/", $p_params["p_strSuggestParameters"], $l_matches);
            $l_condition_info = explode(',', trim(str_replace('"', '', $l_matches[0])));
            $l_table = $l_condition_info[0];

            $l_value = $this->get_array_data($l_table, $l_table . '__id', $l_table . '__id = ' . $p_params['p_strValue']);
            $p_params['p_strValue'] = $l_value;
        }

        if ($p_params['p_bEditMode'] == '1') {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        if ($p_params['p_bInvisible'] == true) {
            return '';
        }

        if ($p_params['p_strStyle']) {
            $p_params['p_strStyle'] = ' style=\'' . $p_params['p_strStyle'] . '\'';
        }

        if (isset($_GET[C__SEARCH__GET__HIGHLIGHT])) {
            $p_params['p_strValue'] = isys_string::highlight($_GET[C__SEARCH__GET__HIGHLIGHT], $p_params['p_strValue']);
        }

        if (isset($p_params['p_bStripSlashes']) && $p_params['p_bStripSlashes']) {
            $p_params['p_strValue'] = stripslashes($p_params['p_strValue']);
        }

        return $this->getInfoIcon($p_params) . '<span' . $p_params['p_strStyle'] . '>' .
            html_entity_decode($p_params['p_strValue'], null, $GLOBALS['g_config']['html-encoding']) . '</span>';
    }
}
