<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_organization_master extends isys_cmdb_ui_category_specific
{
    /**
     * Organisation process method.
     *
     * @param   isys_cmdb_dao_category $dao
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $dao)
    {
        $categoryData = $dao->get_general_data();

        $organizations = [];
        $result = $dao->get_objects_by_cats_id(defined_or_default('C__CATS__ORGANIZATION'), C__RECORD_STATUS__NORMAL);

        while ($row = $result->get_row()) {
            $organizations[$row['isys_obj__id']] = $row['isys_obj__title'];
        }

        // @todo  Maybe rewrite this to use `$this->fill_formfields(...)`?

        // Make rules.
        $l_rules = [
            'C__CMDB__CAT__COMMENTARY_' . $dao->get_category_type() . defined_or_default('C__CATS__ORGANIZATION')             => [
                'p_strValue' => $categoryData['isys_cats_organization_list__description']
            ],
            'C__CMDB__CAT__COMMENTARY_' . $dao->get_category_type() . defined_or_default('C__CATS__ORGANIZATION_MASTER_DATA') => [
                'p_strValue' => $categoryData['isys_cats_organization_list__description']
            ],
            'C__CONTACT__ORGANISATION_TITLE'                                                                                  => [
                'p_strValue' => $categoryData['isys_cats_organization_list__title']
            ],
            'C__CONTACT__ORGANISATION_PHONE'                                                                                  => [
                'p_strValue' => $categoryData['isys_cats_organization_list__telephone']
            ],
            'C__CONTACT__ORGANISATION_FAX'                                                                                    => [
                'p_strValue' => $categoryData['isys_cats_organization_list__fax']
            ],
            'C__CONTACT__ORGANISATION_WEBSITE'                                                                                => [
                'p_strValue' => $categoryData['isys_cats_organization_list__website']
            ],
            'C__CONTACT__ORGANISATION_ASSIGNMENT'                                                                             => [
                'p_arData'        => $organizations,
                'p_strSelectedID' => $categoryData['isys_connection__isys_obj__id'],
                'p_arDisabled'    => serialize([$categoryData['isys_cats_organization_list__isys_obj__id'] => true])
            ]
        ];

        // Apply rules.
        $this->get_template_component()->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
