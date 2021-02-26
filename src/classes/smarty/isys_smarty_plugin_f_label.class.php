<?php

/**
 * i-doit
 *
 * Smarty plugin for label fields.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_label extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Method for retrieving the output on editmode.
     *    'name': name (string);
     *    'ident': translation (string);
     *    'description': add optional description (string);
     *    'default': add optional default value (mixed);
     *    'mandatory': mark optional mandatory field (bool)
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

        $this->m_strPluginClass = "f_label";
        $this->m_strPluginName = $p_params['name'];

        $l_description = null;

        if (isset($p_params['description']) && !empty($p_params['description'])) {
            assert(is_string($p_params["description"]) && !empty($p_params["description"]));

            $l_description = PHP_EOL . '<p style="font-size: smaller;">' . isys_application::instance()->container->get('language')
                    ->get($p_params['description']) . '</p>';
        }

        $l_mandatory = null;

        if (array_key_exists('mandatory', $p_params) && filter_var($p_params['mandatory'], FILTER_VALIDATE_BOOLEAN)) {
            $l_mandatory = '<span class="text-red text-bold">*</span>';
        }

        return sprintf('<label for="%s" style="%s">%s</label>%s%s', $p_params['name'], $p_params['p_strStyle'], isys_application::instance()->container->get('language')
            ->get($p_params['ident']), $l_mandatory, $l_description);
    }

    /**
     * Method for retrieving the output on viewmode.
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

        $this->m_strPluginClass = "f_label";
        $this->m_strPluginName = $p_params['name'];

        return isys_application::instance()->container->get('language')
            ->get($p_params['ident']);
    }
}
