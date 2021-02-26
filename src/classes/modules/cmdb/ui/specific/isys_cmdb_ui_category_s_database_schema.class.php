<?php

/**
 * i-doit
 *
 * CMDB Specific category Database Schema
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_database_schema extends isys_cmdb_ui_category_specific
{
    /**
     * @param isys_cmdb_dao_category $catDao
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $catDao)
    {
        $categoryData = $catDao->get_general_data();

        if (empty($categoryData["isys_cats_database_schema_list__title"])) {
            $rules["C__CMDB__CATS__DB_SCHEMA__TITLE"]["p_strValue"] = $catDao->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);
        } else {
            $rules["C__CMDB__CATS__DB_SCHEMA__TITLE"]["p_strValue"] = $categoryData["isys_cats_database_schema_list__title"];
        }

        $rules["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"]["p_strSelectedID"] = $categoryData["isys_connection__isys_obj__id"];

        $instances = [];
        $instanceDao = isys_cmdb_dao_category_s_database_instance::instance($catDao->get_database_component())
            ->get_data();
        $on = strtolower(isys_application::instance()->container->get('language')
            ->get("LC__UNIVERSAL__ON"));

        $quickInfo = new isys_ajax_handler_quick_info();
        while ($l_row = $instanceDao->get_row()) {
            if(!($l_row["isys_obj__status"] == C__RECORD_STATUS__NORMAL
                || $categoryData["isys_connection__isys_obj__id"] == $l_row["isys_obj__id"])) {
                continue;
            }
            $instances[$l_row["isys_obj__id"]] = $l_row["isys_obj__title"] . " " . $on . " " . $catDao->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);
            if ($l_row["isys_obj__id"] == $categoryData["isys_connection__isys_obj__id"]) {
                $this->m_template->assign("runsOnStrValue", $quickInfo->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT) . " " . $on . " " .
                    $quickInfo->get_quick_info($l_row["isys_connection__isys_obj__id"], $catDao->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]),
                        C__LINK__OBJECT));
            }
        }

        $rules["C__CMDB__CATS__DB_SCHEMA__RUNS_ON"]["p_arData"] = $instances;
        $rules["C__CMDB__CATS__DB_SCHEMA__STORAGE_ENGINE"]["p_strValue"] = $categoryData["isys_cats_database_schema_list__storage_engine"];
        $rules["C__CMDB__CAT__COMMENTARY_" . $catDao->get_category_type() .
        $catDao->get_category_id()]["p_strValue"] = $categoryData["isys_cats_database_schema_list__description"];

        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }
}
