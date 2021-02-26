<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_group_master extends isys_cmdb_ui_category_specific
{

    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_person_group_master $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_comp_session;

        $l_rules = [];
        $l_ldap = false;
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        if (is_object($g_comp_session->get_ldap_module())) {
            $l_ldap = true;
            $l_rules["C__CONTACT__GROUP_LDAP"]["p_strValue"] = $l_catdata["isys_cats_person_group_list__ldap_group"];
        }

        $l_rules['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP', 'C__CATS__PERSON_GROUP')]['p_strValue'] = $l_rules['C__CMDB__CAT__COMMENTARY_' .
        C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP_MASTER', 'C__CATS__PERSON_GROUP_MASTER')]['p_strValue'];

        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign("ldap", $l_ldap);
    }
}
