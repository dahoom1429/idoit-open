<?php

/**
 * i-doit
 *
 * Smarty plugin for formating money numbers
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_money_number extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM2).
     *
     * @author  André Wösten <awoesten@i-doit.org>
     * @return  array
     */
    public static function get_meta_map()
    {
        return ["p_strValue"];
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

        if ($p_params["p_bEditMode"] == "1") {
            return $this->navigation_edit($p_tplclass, $p_params);
        }

        // Format number.
        $this->format($p_params);

        return $this->getInfoIcon($p_params) . $p_params["p_strValueFormatted"] . ' <strong>' . $p_params['p_strMonetary'] . '</strong>';
    }

    /**
     * Format numbers
     *
     * @param array &$p_params
     */
    public function format(&$p_params)
    {
        $l_objLoc = isys_application::instance()->container->locales->get(isys_application::instance()->container->database,
            isys_application::instance()->container->session->get_user_id());

        if (is_null($p_params["p_strValue"]) && isset($p_params["default"])) {
            $p_params["p_strValue"] = $p_params["default"];
        }

        // Decimal seperator from the user configuration.
        $l_monetary = $l_objLoc->fmt_monetary($p_params["p_strValue"]);
        $l_monetary_tmp = explode(" ", $l_monetary);

        $p_params["p_strValueFormatted"] = $l_monetary_tmp[0];
        $p_params["p_strMonetary"] = $l_monetary_tmp[1];
    }

    /**
     *
     *
     * @see     isys_smarty_plugin_f_text  For all parameters.
     *
     * @param   isys_component_template $p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.de>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        if ($p_params["p_bEditMode"] == "0") {
            return $this->navigation_view($p_tplclass, $p_params);
        }

        $this->m_strPluginClass = "f_text";
        $this->m_strPluginName = $p_params["name"];
        $p_params = $this->prepare_input_group($p_params);

        // default value should only be on view
        unset($p_params['default']);

        // Format number.
        $this->format($p_params);

        $p_params['p_bInfoIconSpacer'] = 0;

        return $this->render_input_group(isys_factory::get_instance('isys_smarty_plugin_f_text')
                ->navigation_edit($p_tplclass, (['disableInputGroup' => true] + $p_params)) . '<span class="input-group-addon text-bold">' . $p_params['p_strMonetary'] .
            '</span>', $p_params);
    }
}