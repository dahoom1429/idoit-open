<?php

/**
 * i-doit
 *
 * Smarty plugin for language constants
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_lang extends isys_smarty_plugin_f
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public static function get_meta_map()
    {
        return ['ident'];
    }

    /**
     * Method for translating language constants.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_params
     *
     * @return null|string
     * @throws Exception
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = 'lang';
        $this->m_strPluginName = $p_params['name'];

        $l_values = null;

        if (isset($p_params['values'])) {
            $l_values = $p_params['values'];
        }

        $l_strRet = null;

        if (array_key_exists('ident', $p_params)) {
            $l_strRet = isys_application::instance()->container->get('language')->get($p_params['ident'], $l_values);
        }

        if (!empty($p_params['truncate'])) {
            $l_strRet = isys_glob_str_stop($l_strRet, (int)$p_params['truncate'], '..');
        }

        if (isset($p_params['p_func'])) {
            // Possible functions: strtoupper, strtolower, ucfirst ...
            $l_func = $p_params['p_func'];

            if (function_exists($l_func)) {
                $l_strRet = $l_func($l_strRet);
            }
        }

        if ($p_params['p_bHtmlEncode'] || !isset($p_params['p_bHtmlEncode'])) {
            $l_strRet = isys_glob_htmlentities($l_strRet);
        }

        return $l_strRet;
    }

    /**
     * This is an alias function of "navigation_view".
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    }
}
