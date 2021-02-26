<?php

/**
 * i-doit
 *
 * CMDB Specific category PDU Branch.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_pdu extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_pdu $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_branch_dao = new isys_cmdb_dao_category_s_pdu_branch($this->get_database_component());
        $l_snmp_dao = new isys_cmdb_dao_category_g_snmp($this->get_database_component());

        try {
            if (isys_tenantsettings::get('snmp.pdu.queries', false)) {
                $l_snmp = new isys_library_snmp($l_branch_dao->get_snmp_host($_GET[C__CMDB__GET__OBJECT]), $l_snmp_dao->get_community($_GET[C__CMDB__GET__OBJECT]));

                // PDU default.
                if (defined('C__OBJTYPE__PDU') && !$l_catdata["isys_cats_pdu_list__pdu_id"] && !$_POST["C__CMDB__CATS__PDU__PDU_ID"]) {
                    $l_catdata["isys_cats_pdu_list__pdu_id"] = $p_cat->count_objects_by_type(C__OBJTYPE__PDU);
                }

                $l_pdu = $l_catdata["isys_cats_pdu_list__pdu_id"];

                $l_rules["C__CMDB__CATS__PDU__ACC_ENERGY_PDU"]["p_strValue"] = $l_branch_dao->decimal_shift($l_snmp->cleanup($l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduPsEntryEnergyAccum"),
                    $l_pdu, 1, 0)}));
                $l_rules["C__CMDB__CATS__PDU__ACC_POWER_PDU"]["p_strValue"] = $l_snmp->cleanup($l_snmp->{$l_branch_dao->format($l_branch_dao->get_snmp_path("lgpPduPsEntryPwrTotal"),
                    $l_pdu, 1, 0)});
            }
        } catch (Exception $e) {
            isys_notify::warning($e->getMessage());
        }

        // Assign rules.
        $l_rules["C__CMDB__CATS__PDU__SNMP_QUERIES"]["p_arData"] = get_smarty_arr_YES_NO();
        $l_rules["C__CMDB__CATS__PDU__SNMP_QUERIES"]["p_strSelectedID"] = isys_tenantsettings::get('snmp.pdu.queries', false) ? '1' : '0';

        $l_rules["C__CMDB__CATS__PDU__PDU_ID"]["p_strValue"] = $l_catdata["isys_cats_pdu_list__pdu_id"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_pdu_list__description"];

        if ($_POST["C__CMDB__CATS__PDU__PDU_ID"]) {
            $l_rules["C__CMDB__CATS__PDU__PDU_ID"]["p_strValue"] = $_POST["C__CMDB__CATS__PDU__PDU_ID"];
        }

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}