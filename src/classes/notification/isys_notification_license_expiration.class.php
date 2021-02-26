<?php

/**
 * i-doit
 *
 * Notification: License has expired.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_license_expiration extends isys_notification
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

        // Get objects of type license:

        $l_objects = [];

        if (defined('C__OBJTYPE__LICENCE')) {
            foreach ($l_notification_objects as $l_object) {
                if ($l_object['isys_obj__isys_obj_type__id'] == C__OBJTYPE__LICENCE) {
                    $l_objects[] = $l_object;
                }
            }
        }

        unset ($l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num == 0) {
            $this->m_log->warning('No licenses have been set to report! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of licenses: %s', $l_num));
        }

        // Check whether license is expired:
        $l_licenses = [];

        $l_cmdb_dao = new isys_cmdb_dao_category_s_lic($this->m_db);

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

        // Iterate through each license:
        foreach ($l_objects as $l_object) {
            $this->m_log->info(sprintf('Handling license "%s"...', $l_object['isys_obj__title']));

            // Fetch category data from database:
            $result = $l_cmdb_dao->get_data(null, $l_object['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

            if(!is_countable($result) || count($result) == 0) {
                $this->m_log->debug(sprintf('No license keys defined. Skip license.'));
                continue;
            }

            while ($catData = $result->get_row()) {

                if (in_array($l_object, $l_licenses)) {
                    continue;
                }

                // Expiration date:
                $l_expiration_date = $catData['isys_cats_lic_list__expire'];

                // Not enough data provided. Skipping.
                if (!isset($l_expiration_date) || $l_expiration_date === '0000-00-00 00:00:00' || $l_expiration_date === '0000-00-00' || $l_expiration_date === '1970-01-01') {
                    $this->m_log->debug('Expiration date not set. Skip object.');
                    continue;
                }

                $l_expiration_date = strtotime($l_expiration_date);

                $l_calculated_threshold = $l_expiration_date - $l_threshold;
                $l_formatted_threshold_date = date('Y-m-d H:i:s', $l_calculated_threshold);

                $this->m_log->debug(sprintf('Threshold date is: %s', $l_formatted_threshold_date));

                if ($l_calculated_threshold > $l_now) {
                    $this->m_log->debug(sprintf('Threshold date not exceeded (%s). Skip license.', $l_formatted_threshold_date));
                    continue;
                }

                $this->m_log->debug('Threshold exceeded! Add license to the list.');

                $l_licenses[] = $l_object;
            }
        }

        unset($l_cmdb_dao);

        $l_num = count($l_licenses);

        if ($l_num == 0) {
            $this->m_log->debug('There are no licenses left to report. Skip notification.');

            return $this->reset_counter($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of licenses which match the criterias: %s', $l_num));
        }

        // Write messages:
        if ($this->write_messages($p_notification, $l_licenses) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
    }

}

?>
