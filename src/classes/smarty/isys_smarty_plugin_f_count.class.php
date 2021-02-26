<?php

/**
 * i-doit
 *
 * Smarty plugin for numerical input fields with arrows to change the value.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_count extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @return  array
     * @author  André Wösten <awoesten@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ['p_strValue'];
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

        if ($p_params['p_bEditMode']) {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        return $this->getInfoIcon($p_params) . html_entity_decode(stripslashes($p_params['p_strValue']));
    }

    /**
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_dirs;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $l_strID = '';
        $inputType = 'text';

        $this->m_strPluginClass = 'f_count';
        $this->m_strPluginName = $p_params['name'];

        // Default css class.
        $p_params['p_strClass'] = 'input ' . $p_params['p_strClass'];

        // Standard ID.
        if (empty($p_params['p_strID'])) {
            $p_params['p_strID'] = $p_params['name'];
            $l_strID = $p_params['p_strID'];
        }

        // Standard value is 1.
        if (empty($p_params['p_strValue'])) {
            $p_params['p_strValue'] = 1;
        }

        // Standard size.
        if (empty($p_params['p_nSize'])) {
            $p_params['p_nSize'] = 3;
        }

        // Is the error flag set?
        if (!empty($p_params['p_strError'])) {
            $p_params['p_strError'] .= 'Error';
        }

        $allowNegative = 0;

        // @todo  Remove check for "$p_params['p_bNeg']" in i-doit 1.12.
        if ($p_params['allowNegative'] || $p_params['p_bNeg']) {
            $allowNegative = 1;
        }

        $allowZero = 0;

        if ($p_params['allowZero']) {
            $allowZero = 1;
        }

        $l_onchange = $p_params['p_onChange'];
        $p_params['p_onChange'] = "\$('" . $l_strID . "').setValue(checkNumber(\$F('" . $l_strID . "'), " . $allowZero . ", " . $allowNegative . ")); " . $p_params['p_onChange'];

        $p_params = $this->prepare_input_group($p_params);
        $this->getStandardAttributes($p_params);
        $this->getJavascriptAttributes($p_params);

        if ($p_params['p_bInvisible']) {
            $inputType = 'hidden';
        }

        $decreaseButton = '<span class="input-group-addon input-group-addon-clickable" onClick="$(\'' . $l_strID . '\').setValue(decreaseNumber($F(\'' . $l_strID . '\'))).simulate(\'change\');' . $l_onchange . '" ' . $p_params['p_onAlter'] . '>' .
            '<img src="' . $g_dirs['images'] . 'icons/dec_arr.png" /></span>';
        
        $inputField = '<input ' . $p_params['name'] .
            ' type="' . $inputType . '" ' .
            $p_params['p_strID'] . ' ' .
            $p_params['p_strTitle'] . ' ' .
            $p_params['p_strClass'] . ' ' .
            $p_params['p_bDisabled'] . ' ' .
            $p_params['p_bReadonly'] . ' ' .
            $p_params['p_strStyle'] . ' ' .
            $p_params['p_strValue'] . ' ' .
            $p_params['p_strTab'] . ' ' .
            $p_params['p_nSize'] . ' ' .
            $p_params['p_nMaxLen'] . ' ' .
            $p_params['p_onMouseOver'] . ' ' .
            $p_params['p_onMouseOut'] .' ' .
            $p_params['p_onChange'] . ' ' .
            $p_params['p_onClick'] . ' ' .
            $p_params['p_onKeyPress'] . ' ' .
            $p_params['p_onKeyUp'] . ' ' .
            $p_params['p_onKeyDown'] .' ' .
            $p_params['p_additional'] . ' ' .
            $p_params['p_dataIdentifier'] . ' ' .
            $p_params['p_validation_mandatory'] . ' ' .
            $p_params['p_validation_rule'] . ' />';
        
        $increaseButton = '<span class="input-group-addon input-group-addon-clickable" onClick="$(\'' . $l_strID . '\').setValue(increaseNumber($F(\'' . $l_strID . '\'))).simulate(\'change\'); ' . $l_onchange . '" ' . $p_params['p_onAlter'] . '>' .
            '<img src="' . $g_dirs['images'] . 'icons/inc_arr.png" /></span>';
        
        return $this->render_input_group($decreaseButton . $inputField . $increaseButton, $p_params);
    }
}
