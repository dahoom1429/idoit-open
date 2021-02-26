<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_checkbox extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Navigation mode.
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

        if ($p_params['p_bEditMode'] != '1' || !isset($p_params['p_bEditMode'])) {
            $p_params["p_bDisabled"] = true;
        }

        return $this->navigation_edit($p_tplclass, $p_params);
    }

    /**
     * Edit mode.
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

        if ($p_params["p_strID"]) {
            $p_params["id"] = $p_params["p_strID"];
        }

        if (empty($p_params["id"])) {
            $p_params["id"] = $p_params["name"];
        }

        $l_attributes = [
            'id'   => $p_params["id"],
            'name' => $p_params["name"]
        ];

        if ($p_params["p_strClass"]) {
            $l_attributes['class'] = $p_params["p_strClass"];
        }

        if ($p_params["p_strStyle"]) {
            $l_attributes['style'] = $p_params["p_strStyle"];
        }

        if ($p_params["p_bDisabled"]) {
            $l_attributes['disabled'] = "disabled";
        }

        if ($p_params["p_strOnClick"]) {
            $l_attributes['onclick'] = $p_params["p_strOnClick"];
        }

        if ($p_params["p_bChecked"]) {
            $l_attributes['checked'] = "checked";
        }

        if (isset($p_params["p_strValue"])) {
            $l_attributes['value'] = $p_params["p_strValue"];
        }

        if (!empty($p_params['p_dataIdentifier'])) {
            $l_attributes['data-identifier'] = $p_params['p_dataIdentifier'];
        }

        $l_attribut_string = '';

        foreach ($l_attributes as $l_key => $l_value) {
            $l_attribut_string .= ' ' . $l_key . '="' . $l_value . '"';
        }

        if (empty($p_params["p_strTitle"])) {
            return isys_smarty_plugin_f::getInfoIcon($p_params) . '<input type="checkbox" ' . $l_attribut_string . ' />';
        }

        return isys_smarty_plugin_f::getInfoIcon($p_params) . '<label><input type="checkbox" ' . $l_attribut_string . " /> " .
            isys_application::instance()->container->get('language')
                ->get($p_params["p_strTitle"]) . "</label>";
    }
}
