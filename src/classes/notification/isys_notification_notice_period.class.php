<?php

/**
 * i-doit
 *
 * Notification: Notice period of a contract has been reached.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_notice_period extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    protected function handle_notification($p_notification)
    {
        // Check threshold and its unit:

        if (!isset($p_notification['threshold'])) {
            $this->m_log->warning('Threshold is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        }

        if (!isset($p_notification['threshold_unit'])) {
            $this->m_log->warning('Threshold unit is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        }

        // Fetch objects selected by notification:
        $l_notification_objects = $this->m_dao->get_objects($p_notification['id']);

        /** @var isys_cmdb_dao_category_s_contract $l_contract_dao */
        $l_contract_dao = isys_cmdb_dao_category_s_contract::instance($this->m_db);

        /** @var isys_cmdb_dao_category_g_contract_assignment $l_contract_dao_global */
        $l_contract_dao_global = isys_cmdb_dao_category_g_contract_assignment::instance($this->m_db);

        $l_objects = [];

        // Get object types with contract category
        $l_valid_obj_types = $l_contract_dao->get_object_types_by_category(defined_or_default('C__CATS__CONTRACT_INFORMATION'), 's', false);

        if (!is_array($l_valid_obj_types)) {
            $l_valid_obj_types = [];
        }

        foreach ($l_notification_objects AS $l_object) {
            // Does object contain specific contract category?
            if (in_array($l_object['isys_obj__isys_obj_type__id'], $l_valid_obj_types)) {
                // 1. Get data from specific maintenance category
                $l_contract_data = $l_contract_dao->get_data(null, $l_object['isys_obj__id'])
                    ->__to_array();

                $l_objects[] = $l_contract_data;

                // 2. Get all contract assignments with achievement certificate
                $l_res = $l_contract_dao_global->get_data(null, null,
                    ' AND isys_cats_contract_list__isys_obj__id = ' . $l_contract_dao_global->convert_sql_id($l_contract_data['isys_obj__id']) .
                    ' AND (isys_catg_contract_assignment_list__contract_start IS NOT NULL OR ' . 'isys_catg_contract_assignment_list__contract_end IS NOT NULL);');

                // Are there contract achievement certificate
                if ($l_res->num_rows()) {
                    while ($l_row = $l_res->get_row()) {
                        $l_certificate_different = false;

                        // Let us set values from 'Achievement certificate'
                        if (!empty($l_row['isys_catg_contract_assignment_list__contract_start']) &&
                            $l_row['isys_catg_contract_assignment_list__contract_start'] != $l_row['isys_cats_contract_list__start_date']) {
                            $l_certificate_different = true;
                            $l_row['isys_cats_contract_list__start_date'] = $l_row['isys_catg_contract_assignment_list__contract_start'];
                        }

                        if (!empty($l_row['isys_catg_contract_assignment_list__contract_end']) &&
                            $l_row['isys_cats_contract_list__end_date'] != $l_row['isys_catg_contract_assignment_list__contract_end']) {
                            $l_certificate_different = true;
                            $l_row['isys_cats_contract_list__end_date'] = $l_row['isys_catg_contract_assignment_list__contract_end'];
                        }

                        // Is it necessary to handle?
                        if ($l_certificate_different) {
                            $l_objects[] = $l_row;
                        }
                    }
                }
            }
        }

        unset ($l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num == 0) {
            $this->m_log->warning('No contracts have been set to report! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of licenses: %s', $l_num));
        }

        // Check whether period has been reached:
        $l_contracts = [];

        $l_now = time();
        $l_threshold = null;

        $l_unit = $this->m_dao->get_unit($this->m_type['unit']);
        $l_unit_parameters = $this->m_dao->get_unit_parameters($l_unit['table']);
        $l_notification_threshold = $p_notification['threshold'];

        // Get the right unit parameter:
        foreach ($l_unit_parameters AS $l_parameter) {
            if ($l_parameter[$l_unit['table'] . '__id'] == $p_notification['threshold_unit']) {
                $l_day = (int)date('d', $l_now);
                $l_month = (int)date('m', $l_now);
                $l_year = (int)date('Y', $l_now);

                switch ($l_parameter[$l_unit['table'] . '__const']) {
                    case 'C__CMDB__UNIT_OF_TIME__MONTH':

                        while ($l_month < $l_notification_threshold) {
                            $l_month += 12;
                            $l_year--;
                        }
                        $l_month -= $l_notification_threshold;
                        $l_days_in_month = date('t', strtotime($l_year . '-' . $l_month . '-01'));
                        if ($l_day > $l_days_in_month) {
                            $l_day = $l_days_in_month;
                        }
                        $l_threshold = $l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day);

                        break;
                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        $l_year = $l_year - $l_notification_threshold;
                        $l_threshold = $l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day);
                        break;
                    default:
                        $l_threshold = $l_parameter[$l_unit['table'] . '__factor'] * $l_notification_threshold;
                        break;
                }
                break;
            }
        }

        if ($l_threshold === null) {
            $this->m_log->warning('Threshold unit is not set! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        }

        foreach ($l_objects AS $l_object) {
            $l_text = (in_array($l_object['isys_obj__isys_obj_type__id'],
                $l_valid_obj_types)) ? 'Handling contract "%s"...' : 'Handling contract achievement certificate for "%s"...';
            $this->m_log->info(sprintf($l_text, $l_object['isys_obj__title']));

            // Check whether contract is currently running:
            if (isset($l_object['isys_cats_contract_list__start_date'])) {
                $l_start = strtotime($l_object['isys_cats_contract_list__start_date']);
                if ($l_start > $l_now) {
                    $this->m_log->debug('Contract will start in the future.');
                    continue;
                }
            }

            if (isset($l_object['isys_cats_contract_list__end_date'])) {
                $l_end = strtotime($l_object['isys_cats_contract_list__end_date']);
                if ($l_end < $l_now) {
                    $this->m_log->debug('Contract ended in the past.');
                    continue;
                }
            }

            // Check whether notice period type is set:
            if (empty($l_object['isys_cats_contract_list__notice_period_unit__id'])) {
                $this->m_log->debug('Notice period type is not set.');
                continue;
            }

            // Calculate end period on contract end
            if ($l_object['isys_cats_contract_list__isys_contract_notice_period_type__id'] == defined_or_default('C__CONTRACT__ON_CONTRACT_END')) {
                $dateToCalculate = $l_object['isys_cats_contract_list__end_date'];

                $l_end_of_period = $l_contract_dao->calculate_noticeperiod($dateToCalculate, $l_object['isys_cats_contract_list__notice_period'],
                    $l_object['isys_cats_contract_list__notice_period_unit__id']);
            } else {
                // Calculate end period on notice date
                $dateToCalculate = $l_object['isys_cats_contract_list__notice_date'];

                $l_end_of_period = $l_contract_dao->calculate_maintenanceperiod($dateToCalculate, $l_object['isys_cats_contract_list__notice_period'],
                    $l_object['isys_cats_contract_list__notice_period_unit__id']);
            }

            // Not enough data provided. Skipping.
            if (!isset($l_end_of_period)) {
                continue;
            }

            $l_end_of_period = strtotime($l_end_of_period);
            $l_destinated_date = $l_end_of_period - $l_threshold;
            $l_formatted_threshold = date('Y-m-d H:i:s', $l_destinated_date);

            if ($l_destinated_date > $l_now) {
                $this->m_log->debug(sprintf('Threshold not exceeded (%s). Skip contract.', $l_formatted_threshold));
                continue;
            }

            $this->m_log->debug(sprintf('Threshold exceeded (%s)!', $l_formatted_threshold));

            $l_contracts[] = $l_object;
        }

        unset($l_cmdb_dao);

        $l_num = count($l_contracts);

        if ($l_num == 0) {
            $this->m_log->debug('There are no contracts left to report. Skip notification.');

            return $this->reset_counter($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of contracts which match the criterias: %s', $l_num));
        }

        // Write messages:

        if ($this->write_messages($p_notification, $l_contracts) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
    }

}

?>