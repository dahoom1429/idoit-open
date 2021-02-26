<?php

/**
 * i-doit
 *
 * CMDB Memory
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @version    Niclas Potthast <npotthast@i-doit.org> - 2006-09-21
 * @version    Dennis Blümer <dbluemer@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_ws_net_type extends isys_cmdb_ui_category_specific
{

    /**
     * @return void
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-09-21
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT], "", null, C__RECORD_STATUS__NORMAL)
            ->__to_array();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_rules["C__CATS__WS_NET_TYPE_TITLE_ID"]["p_strSelectedID"] = $l_catdata["isys_cats_ws_net_type_list__isys_net_type_title__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_ws_net_type_list__description"];

        if (!$p_cat->get_validation()) {
            $l_rules["C__CATS__WS_NET_TYPE_TITLE_ID"]["p_strSelectedID"] = $l_posts["C__CATS__WS_NET_TYPE_TITLE_ID"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_posts["C__CMDB__CAT__COMMENTARY"];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Apply rules
        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $this->activate_commentary($p_cat);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * isys_cmdb_ui_category_s_ws_net_type constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__ws_net_type.tpl");
    }
}
