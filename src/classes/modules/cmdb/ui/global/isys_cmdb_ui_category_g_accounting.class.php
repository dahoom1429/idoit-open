<?php

/**
 * i-doit
 *
 * CMDB UI: Global category (category type is accounting)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_cmdb_ui_category_g_accounting extends isys_cmdb_ui_category_global
{
    /**
     * Process method for displaying the template.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_accounting &$p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Initializing some variables.
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Creating some further, more specific, rules.
        $l_person_ids = $p_cat
            ->callback_property_contact(isys_request::factory()
            ->set_object_id($l_catdata['isys_obj__id']));

        $l_rules["C__CATG__PURCHASE_CONTACT"]["p_strSelectedID"] = (is_countable($l_person_ids) && count($l_person_ids) > 0) ? isys_format_json::encode($l_person_ids) : null;
        $l_rules["C__CATG__ACCOUNTING_GUARANTEE_STATUS"]["p_strValue"] = isys_settings::get('gui.empty_value', '-');
        $l_rules["C__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE"]["p_strValue"] = isys_settings::get('gui.empty_value', '-');

        if ($l_catdata["isys_catg_accounting_list__isys_guarantee_period_unit__id"] > 0 && $l_catdata["isys_catg_accounting_list__guarantee_period"]) {
            $l_row = $p_cat->get_dialog("isys_guarantee_period_unit", $l_catdata["isys_catg_accounting_list__isys_guarantee_period_unit__id"])
                ->get_row();
            $l_now = time();

            switch ($l_catdata['isys_catg_accounting_list__guarantee_period_base']) {
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE:
                    $l_date = strtotime($l_catdata['isys_catg_accounting_list__delivery_date']);
                    break;
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__ORDER_DATE:
                    $l_date = strtotime($l_catdata['isys_catg_accounting_list__order_date']);
                    break;
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE:
                    $l_date = strtotime($l_catdata['isys_catg_accounting_list__acquirementdate']);
                    break;
                default:
                    $l_date = $l_now;
                    break;
            }

            if ($l_date) {
                $l_rules["C__CATG__ACCOUNTING_GUARANTEE_STATUS"]["p_strValue"] = $p_cat->calculate_guarantee_status(
                    $l_date,
                    $l_catdata["isys_catg_accounting_list__guarantee_period"],
                    $l_row["isys_guarantee_period_unit__const"]);
                $l_calculated_date = $p_cat->calculate_guarantee_date(
                    $l_date,
                    $l_catdata["isys_catg_accounting_list__guarantee_period"],
                    $l_row["isys_guarantee_period_unit__const"]);
                $l_rules["C__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE"]["p_strValue"] = ($l_calculated_date
                    ? date(isys_application::instance()->container->get('locales')->get_date_format(true), $l_calculated_date)
                    : isys_tenantsettings::get('gui.empty_value', '-'));
            }
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}