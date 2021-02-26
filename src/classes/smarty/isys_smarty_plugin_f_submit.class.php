<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_submit extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Navigation view.
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

        return (new isys_smarty_plugin_f_button)->set_edit_mode(false)
            ->navigation_view($p_tplclass, $p_params);
    }

    /**
     * Edit view.
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

        return (new isys_smarty_plugin_f_button)->set_edit_mode(true)
            ->navigation_edit($p_tplclass, $p_params);
    }
}