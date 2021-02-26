<?php

/**
 * Interface which has to be implemented into SMARTY plugins. Please care for correct prototype typing!
 */
interface isys_smarty_plugin
{
    /**
     * Defines wheather the sm2 meta map is enabled or not
     *
     * @return mixed
     */
    public function enable_meta_map();

    /**
     * Method for navigation-view.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_param
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_param = null);

    /**
     * Method for navigation-edit.
     *
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_param
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null);

    /**
     * Method for retrieving the meta map.
     *
     * @return array
     */
    public static function get_meta_map();
}