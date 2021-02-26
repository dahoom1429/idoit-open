<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_ocs_category_selection extends isys_component_popup
{
    /**
     *
     * @return string
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     *
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template&
     * @throws Exception
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_categories = array_merge([
            'LC__OBJTYPE__OPERATING_SYSTEM' => 'operating_system',
        ], filter_array_by_value_of_defined_constants([
            'LC__CMDB__CATG__COMPUTING_RESOURCES__CPU' => 'C__CATG__CPU',
            'LC__CMDB__CATG__MEMORY'                   => 'C__CATG__MEMORY',
            'LC__CMDB__CATG__APPLICATION'              => 'C__CATG__APPLICATION',
            'LC__CMDB__CATG__NETWORK'                  => 'C__CATG__NETWORK',
            'LC__UNIVERSAL__DEVICES'                   => 'C__CATG__STORAGE',
            'LC__UNIVERSAL__DRIVES'                    => 'C__CATG__DRIVE',
            'LC__CMDB__CATG__GRAPHIC'                  => 'C__CATG__GRAPHIC',
            'LC__CMDB__CATG__SOUND'                    => 'C__CATG__SOUND',
            'LC__CMDB__CATG__MODEL'                    => 'C__CATG__MODEL',
            'LC__CMDB__CATG__UNIVERSAL_INTERFACE'      => 'C__CATG__UNIVERSAL_INTERFACE'
        ]));

        $this->template
            ->assign('categories', $l_categories)
            ->display('popup/ocs_category_selection.tpl');
        die;
    }
}
