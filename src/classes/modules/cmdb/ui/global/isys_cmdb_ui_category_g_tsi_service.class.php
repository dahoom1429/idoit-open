<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_tsi_service extends isys_cmdb_ui_category_global
{

    /**
     * @return void
     *
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_arTypeData = [];
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        $l_rules = [];

        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_catdata = $p_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                ->__to_array();

            $l_rules["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"]["p_strValue"] = $l_catdata["isys_catg_tsi_service_list__tsi_service_id"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
            $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_tsi_service_list__description"];
        }

        if (!$p_cat->get_validation()) {
            $l_rules["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"]["p_strValue"] = $l_posts["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_posts["C__CMDB__CAT__COMMENTARY"];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Apply rules
        isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * isys_cmdb_ui_category_g_tsi_service constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__tsi_service.tpl");
    }
}