<?php

/**
 * i-doit
 *
 * User interface: Specific category for contract
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_contract extends isys_cmdb_ui_category_specific
{
    /**
     * Show the detail-template for specific category contract.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @throws isys_exception_dao_cmdb
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $language = isys_application::instance()->container->get('language');
        $locales = isys_application::instance()->container->get('locales');

        $l_catdata = $p_cat->get_general_data();

        parent::process($p_cat);

        $l_maintenance_end = $l_contract_end = $l_expiration_date = isys_tenantsettings::get('gui.empty_value', '-');

        if ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__ON_CONTRACT_END')) {
            $contractEnd = strtotime($l_catdata['isys_cats_contract_list__end_date']);

            if (!empty($l_catdata['isys_cats_contract_list__end_date']) && $contractEnd > 0) {
                $l_contract_end = $locales->fmt_date($contractEnd);

                $l_expiration_date = $p_cat->calculate_noticeperiod(
                    $l_contract_end,
                    $l_catdata['isys_cats_contract_list__notice_period'],
                    $l_catdata['isys_cats_contract_list__notice_period_unit__id']);
            } else {
                $l_contract_end = $language->get('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                $l_expiration_date = $language->get('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
            }
        } elseif ($l_catdata["isys_cats_contract_list__isys_contract_notice_period_type__id"] == defined_or_default('C__CONTRACT__FROM_NOTICE_DATE')) {
            $contractNoticeDate = strtotime($l_catdata['isys_cats_contract_list__notice_date']);

            if (!empty($l_catdata['isys_cats_contract_list__notice_date']) && $contractNoticeDate > 0) {
                $l_expiration_date = $language->get('LC__UNIVERSAL__ANYTIME');
                $l_contract_end = $p_cat->calculate_next_contract_end_date(
                    $l_catdata['isys_cats_contract_list__notice_date'],
                    $l_catdata['isys_cats_contract_list__notice_period'],
                    $l_catdata['isys_cats_contract_list__notice_period_unit__id']);
            } else {
                $l_contract_end = $language->get('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
                $l_expiration_date = $language->get('LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED');
            }
        }

        if (!empty($l_catdata["isys_cats_contract_list__maintenance_period"]) && !empty($l_catdata["isys_cats_contract_list__maintenance_period_unit__id"]) && !empty($l_catdata["isys_cats_contract_list__start_date"])) {
            $l_maintenance_end = $p_cat->calculate_maintenanceperiod(
                $l_catdata["isys_cats_contract_list__start_date"],
                $l_catdata["isys_cats_contract_list__maintenance_period"],
                $l_catdata["isys_cats_contract_list__maintenance_period_unit__id"]);
        }

        $l_date_format = $locales->get_date_format();

        $this->get_template_component()
            ->assign('current_date_format_splitter', (strpos($l_date_format, '.') ? '.' : '-'))
            ->assign('current_date_format', str_replace(['.', '-'], ['', ''], $l_date_format))
            ->assign("description_date_format", $language->get('LC__CATG__OVERVIEW__DATE_FORMAT') . ': ' . $l_date_format . ' (' . date($l_date_format, time()) . ')')
            ->assign("contract_end", $l_contract_end)
            ->assign("maintenance_end", $l_maintenance_end)
            ->assign("expiration_date", $l_expiration_date)
            ->assign("date_format", $l_date_format);
    }
}
