<?php

/**
 * i-doit
 *
 * UI: global category for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_contract_assignment extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        // Make rules.
        $l_rules["C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START"]["p_strValue"] = $l_catdata["isys_catg_contract_assignment_list__contract_start"];
        $l_rules["C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END"]["p_strValue"] = $l_catdata["isys_catg_contract_assignment_list__contract_end"];
        $l_rules["C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS"]["p_strValue"] = $l_catdata["isys_connection__isys_obj__id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() .
        $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_contract_assignment_list__description"];
        $l_rules["C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD"]['p_strValue'] = isys_tenantsettings::get('gui.empty_value', '-');
        $l_rules["C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"]["p_strSelectedID"] = $l_catdata["isys_catg_contract_assignment_list__reaction_rate__id"];

        $l_subcontract = false;

        if ((!empty($l_catdata["isys_catg_contract_assignment_list__contract_start"]) &&
                $l_catdata["isys_catg_contract_assignment_list__contract_start"] != '1970-01-01 01:00:00') ||
            (!empty($l_catdata["isys_catg_contract_assignment_list__contract_end"]) &&
                $l_catdata["isys_catg_contract_assignment_list__contract_end"] != '1970-01-01 01:00:00')) {
            $l_subcontract = true;
        }

        $l_connection_dao = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connected_contract_id = $l_connection_dao->get_object_id_by_connection($l_catdata['isys_catg_contract_assignment_list__isys_connection__id']);

        if ($l_connected_contract_id) {
            $l_contract_dao = new isys_cmdb_dao_category_s_contract($this->get_database_component());
            $l_contract_data = $l_contract_dao->get_data(null, $l_connected_contract_id)
                ->get_row();

            $l_contract_information = [
                'LC__CMDB__CATS__CONTRACT__TYPE'               => 'isys_contract_type__title',
                'LC__CMDB__CATS__CONTRACT__CONTRACT_NO'        => 'isys_cats_contract_list__contract_no',
                'LC__CMDB__CATS__CONTRACT__CUSTOMER_NO'        => 'isys_cats_contract_list__customer_no',
                'LC__CMDB__CATS__CONTRACT__INTERNAL_NO'        => 'isys_cats_contract_list__internal_no',
                'LC__CMDB__CATS__CONTRACT__COSTS'              => 'isys_cats_contract_list__costs',
                'LC__CMDB__CATS__CONTRACT__PRODUCT'            => 'isys_cats_contract_list__product',
                'LC__CMDB__CATS__CONTRACT__REACTION_RATE'      => 'isys_contract_reaction_rate__title',
                'LC__CMDB__CATS__CONTRACT__STATUS'             => 'isys_contract_status__title',
                'LC__CMDB__CATS__CONTRACT__START_DATE'         => 'isys_cats_contract_list__start_date',
                'LC__CMDB__CATS__CONTRACT__END_DATE'           => 'isys_cats_contract_list__end_date',
                'LC__CMDB__CATS__CONTRACT__END_TYPE'           => 'isys_contract_end_type__title',
                'LC__CMDB__CATS__CONTRACT__NOTICE_DATE'        => 'isys_cats_contract_list__notice_date',
                'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE'       => $l_contract_data['isys_cats_contract_list__notice_period'] . " " .
                    isys_application::instance()->container->get('language')
                        ->get($l_contract_data['notice_title']),
                'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD' => $l_contract_data['isys_cats_contract_list__maintenance_period'] . " " .
                    isys_application::instance()->container->get('language')
                        ->get($l_contract_data['main_title']),
            ];

            $dateFields = [
                'isys_cats_contract_list__start_date'  => true,
                'isys_cats_contract_list__end_date'    => true,
                'isys_cats_contract_list__notice_date' => true,
                'notice_end'                           => true,
                'maintenance_end'                      => true,
            ];

            if (!$l_subcontract) {
                $l_rules["C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START"]["p_strValue"] = $l_contract_data['isys_cats_contract_list__start_date'];
                $l_rules["C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END"]["p_strValue"] = $l_contract_data['isys_cats_contract_list__end_date'];
                $l_rules["C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"]["p_strSelectedID"] = $l_catdata["isys_cats_contract_list__isys_contract_reaction_rate__id"];
            }

            if (!empty($l_contract_data['isys_cats_contract_list__notice_period']) && !empty($l_contract_data["isys_cats_contract_list__notice_period_unit__id"])) {
                $l_contract_data['notice_end'] = $l_contract_dao->calculate_noticeperiod($l_contract_data['isys_cats_contract_list__notice_period'],
                    $l_contract_data["isys_cats_contract_list__notice_period_unit__id"]);

                $l_contract_information['LC__CMDB__CATS__CONTRACT__CONTRACT_END'] = 'notice_end';
            }

            if (!empty($l_contract_data["isys_cats_contract_list__maintenance_period"]) && !empty($l_contract_data["isys_cats_contract_list__maintenance_period_unit__id"]) &&
                !empty($l_contract_data["isys_cats_contract_list__start_date"])) {
                $l_contract_data['maintenance_end'] = $l_contract_dao->calculate_maintenanceperiod($l_contract_data["isys_cats_contract_list__start_date"],
                    $l_contract_data["isys_cats_contract_list__maintenance_period"], $l_contract_data["isys_cats_contract_list__maintenance_period_unit__id"]);

                $l_contract_information['LC__CMDB__CATS__CONTRACT__MAINTENANCE_END'] = 'maintenance_end';
            }

            if ((!empty($l_catdata["isys_catg_contract_assignment_list__contract_start"]) || !empty($l_contract_data["isys_cats_contract_list__start_date"])) &&
                !empty($l_contract_data["isys_cats_contract_list__maintenance_period"]) && !empty($l_contract_data["isys_cats_contract_list__maintenance_period_unit__id"])) {
                $l_universal_startdate = (!empty($l_catdata["isys_catg_contract_assignment_list__contract_start"]) &&
                    $l_catdata["isys_catg_contract_assignment_list__contract_start"] !=
                    '1970-01-01 01:00:00') ? $l_catdata["isys_catg_contract_assignment_list__contract_start"] : $l_contract_data["isys_cats_contract_list__start_date"];

                if ($l_universal_startdate != '1970-01-01 00:00:00') {
                    $l_rules["C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD"]['p_strValue'] = $l_contract_dao->calculate_maintenanceperiod($l_universal_startdate,
                        $l_contract_data["isys_cats_contract_list__maintenance_period"], $l_contract_data["isys_cats_contract_list__maintenance_period_unit__id"]);
                }
            }

            $this->get_template_component()
                ->assign("subcontract", $l_subcontract)
                ->assign("dateFields", $dateFields)
                ->assign("contract_information", $l_contract_information)
                ->assign("contract", $l_contract_data);
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
