<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_contract extends isys_ajax_handler
{
    /**
     * Initialization method.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function init()
    {
        $locales = isys_application::instance()->container->locales;

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
            'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE'       => '',
            'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD' => ''
        ];

        $l_contractDao = new isys_cmdb_dao_category_s_contract(isys_application::instance()->container->database);
        $l_contractRow = $l_contractDao->get_data(null, $_POST['contractID']);
        $l_output = "";

        if ($l_contractRow->num_rows() > 0) {
            $l_contractRow = $l_contractRow->get_row();

            $l_contract_information['LC__CMDB__CATS__CONTRACT__NOTICE_VALUE'] = $l_contractRow['isys_cats_contract_list__notice_period'] . " " .
                isys_application::instance()->container->get('language')
                    ->get($l_contractRow['notice_title']);
            $l_contract_information['LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD'] = $l_contractRow['isys_cats_contract_list__maintenance_period'] . " " .
                isys_application::instance()->container->get('language')
                    ->get($l_contractRow['main_title']);

            $dateFields = [
                'isys_cats_contract_list__start_date'  => true,
                'isys_cats_contract_list__end_date'    => true,
                'isys_cats_contract_list__notice_date' => true,
                'notice_end'                           => true,
                'maintenance_end'                      => true,
            ];

            foreach ($l_contract_information AS $l_title => $l_value) {
                if (is_array($l_contractRow) && array_key_exists($l_value, $l_contractRow)) {
                    $l_row_value = $l_contractRow[$l_value];

                    if (isset($dateFields[$l_value])) {
                        $l_row_value = $locales->fmt_date($l_row_value);
                    } else if ($l_value == 'isys_cats_contract_list__costs') {
                        $param = ['p_strValue' => $l_row_value];

                        (new isys_smarty_plugin_f_money_number())->format($param);

                        $l_row_value = $param['p_strValueFormatted'] . ' ' . $param['p_strMonetary'];
                    }
                } else {
                    $l_row_value = $l_value;
                }

                $l_output .= '<tr><td class="key">' . isys_application::instance()->container->get('language')
                        ->get($l_title) . '</td><td class="value pl20">' . isys_application::instance()->container->get('language')
                        ->get($l_row_value) . '</td></tr>';
            }

            if (!empty($l_contractRow['isys_cats_contract_list__notice_period']) && !empty($l_contractRow["isys_cats_contract_list__notice_period_unit__id"])) {
                $l_contractRow['notice_end'] = $l_contractDao->calculate_noticeperiod($l_contractRow['isys_cats_contract_list__end_date'],
                    $l_contractRow['isys_cats_contract_list__notice_period'], $l_contractRow["isys_cats_contract_list__notice_period_unit__id"]);
                $l_contract_information['LC__CMDB__CATS__CONTRACT__CONTRACT_END'] = 'notice_end';
            }

            if (!empty($l_contractRow["isys_cats_contract_list__maintenance_period"]) && !empty($l_contractRow["isys_cats_contract_list__maintenance_period_unit__id"]) &&
                !empty($l_contractRow["isys_cats_contract_list__start_date"])) {
                $l_contractRow['maintenance_end'] = $l_contractDao->calculate_maintenanceperiod($l_contractRow["isys_cats_contract_list__start_date"],
                    $l_contractRow["isys_cats_contract_list__maintenance_period"], $l_contractRow["isys_cats_contract_list__maintenance_period_unit__id"]);
                $l_contract_information['LC__CMDB__CATS__CONTRACT__MAINTENANCE_END'] = 'maintenance_end';
            }

            echo $l_output . "<input type='hidden' id='assigned_contract__startdate' data-view='" .
                date("d.m.Y", strtotime($l_contractRow['isys_cats_contract_list__start_date'])) . "' value='" . $l_contractRow['isys_cats_contract_list__start_date'] .
                "' />" . "<input type='hidden' id='assigned_contract__enddate' data-view='" . date("d.m.Y", strtotime($l_contractRow['isys_cats_contract_list__end_date'])) .
                "' value='" . $l_contractRow['isys_cats_contract_list__end_date'] . "' />" . "<input type='hidden' id='reaction_rate' value='" .
                $l_contractRow['isys_cats_contract_list__isys_contract_reaction_rate__id'] . "' />";
        } else {
            foreach ($l_contract_information AS $l_title => $l_value) {
                $l_output .= '<tr><td class="key">' . isys_application::instance()->container->get('language')
                        ->get($l_title) . '</td><td class="value"></td></tr>';
            }

            echo $l_output;
        }

        $this->_die();
    }

    /**
     * This method defines, if the hypergate has to be included for this handler.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function needs_hypergate()
    {
        return false;
    }
}
