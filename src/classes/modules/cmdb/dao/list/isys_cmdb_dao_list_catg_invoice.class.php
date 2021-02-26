<?php

/**
 * @package   i-doit
 * @subpackage
 * @author    Dennis Stücken <dstuecken@i-doit.org>
 * @version   1.0
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_invoice extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Method for modifying single field contents before rendering.
     *
     * @param  array &$p_row
     */
    public function format_row(&$p_row)
    {
        $l_date_format_user = isys_locale::get_instance()
            ->get_user_settings(LC_TIME);
        $l_date_format = str_replace('%', '', $l_date_format_user['d_fmt_m']);
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        $p_row["isys_catg_invoice_list__amount"] = isys_locale::get_instance()
            ->fmt_monetary($p_row["isys_catg_invoice_list__amount"]);
        $p_row["isys_catg_invoice_list__date"] = ($p_row["isys_catg_invoice_list__date"] != null) ? date($l_date_format,
            strtotime($p_row["isys_catg_invoice_list__date"])) : $l_empty_value;
        $p_row["isys_catg_invoice_list__edited"] = ($p_row["isys_catg_invoice_list__edited"] != null) ? $p_row["isys_catg_invoice_list__edited"] = date($l_date_format,
            strtotime($p_row["isys_catg_invoice_list__edited"])) : $l_empty_value;
        $p_row["isys_catg_invoice_list__financial_accounting_delivery"] = ($p_row["isys_catg_invoice_list__financial_accounting_delivery"] !=
            null) ? $p_row["isys_catg_invoice_list__financial_accounting_delivery"] = date($l_date_format,
            strtotime($p_row["isys_catg_invoice_list__financial_accounting_delivery"])) : $l_empty_value;
    }

    /**
     * Retrieves the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__INVOICE');
    }

    /**
     * Retrieves the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for modifying single field contents before rendering.
     *
     * @param  array $p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_catg_invoice_list__charged"] = ($p_row["isys_catg_invoice_list__charged"] == '1') ? isys_application::instance()->container->get('language')
            ->get("LC__UNIVERSAL__YES") : isys_application::instance()->container->get('language')
            ->get("LC__UNIVERSAL__NO");
    }

    /**
     * Retrieve an array of fields to display.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_catg_invoice_list__denotation"                    => "LC__CMDB__CATG__TITLE",
            "isys_catg_invoice_list__amount"                        => "LC__CMDB__CATG__INVOICE__AMOUNT",
            "isys_catg_invoice_list__date"                          => "LC__CMDB__CATG__INVOICE__DATE",
            "isys_catg_invoice_list__edited"                        => "LC__CMDB__CATG__INVOICE__EDITED",
            "isys_catg_invoice_list__financial_accounting_delivery" => "LC__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY",
            "isys_catg_invoice_list__charged"                       => "LC__CMDB__CATG__INVOICE__CHARGED",
        ];
    }
}
