<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_group_contact_assign extends isys_cmdb_ui_category_specific
{

    /**
     * @desc   define if this sub category is multivalued or not
     * @author Dennis Stücken <dstuecken@synetics.de>
     * @return boolean
     */
    public function is_multivalued()
    {
        return true;
    }

    /**
     * @global                       $index_includes
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @desc show the detail-template for specific category monitor
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_rules["C__CONTACT__ORGANISATION_TARGET_OBJECT"]["p_strSelectedID"] = $l_catdata["isys_catg_contact_list__isys_obj__id"];
        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strTable"] = "isys_contact_tag";
        $l_rules["C__CONTACT__ORGANISATION_ROLE"]["p_strSelectedID"] = $l_catdata["isys_catg_contact_list__isys_contact_tag__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_contact_list__description"];

        // Apply rules
        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();

        return true;
    }

    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__contact_assign.tpl");
        parent::__construct($p_template);
    }
}
