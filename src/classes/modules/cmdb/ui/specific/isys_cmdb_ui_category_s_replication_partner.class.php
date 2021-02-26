<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_replication_partner extends isys_cmdb_ui_category_specific
{

    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_result()
            ->__to_array();

        $l_rules["C__CATS__REPLICATION_PARTNER__TYPE"]["p_strSelectedID"] = $l_catdata["isys_cats_replication_partner_list__isys_replication_type__id"];
        $l_rules["C__CATS__REPLICATION_PARTNER__OBJ"]["p_strValue"] = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
        $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_replication_partner_list__description"];

        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $this->activate_commentary($p_cat);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * isys_cmdb_ui_category_s_replication_partner constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__replication_partner.tpl");
    }
}
