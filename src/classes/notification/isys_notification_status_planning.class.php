<?php

/**
 * i-doit
 *
 * Notification: A CMDB status begins or ends.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_notification_status_planning extends isys_notification
{

    protected $m_property;

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

        $l_num = is_countable($l_notification_objects) ? count($l_notification_objects) : 0;

        if ($l_num === 0) {
            $this->m_log->warning('No domains (neither objects, objects types nor reports) have been set! Skip notification.');

            return $this->mark_notification_as_incomplete($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of objects to check: %s', $l_num));
        }

        // Determine threshold:

        $l_date_format = 'Y-m-d H:i:s';
        $l_now = time();
        $l_threshold = null;

        $l_unit = $this->m_dao->get_unit($this->m_type['unit']);
        $l_unit_parameters = $this->m_dao->get_unit_parameters($l_unit['table']);
        $l_notification_threshold = (int)$p_notification['threshold'];

        // Get the right unit parameter:
        foreach ($l_unit_parameters AS $l_parameter) {
            if ($l_parameter[$l_unit['table'] . '__id'] == $p_notification['threshold_unit']) {
                $l_day = (int)date('d', $l_now);
                $l_month = (int)date('m', $l_now);
                $l_year = (int)date('Y', $l_now);

                switch ($l_parameter[$l_unit['table'] . '__const']) {
                    case 'C__CMDB__UNIT_OF_TIME__MONTH':

                        if ($l_notification_threshold < 0) {
                            $l_notification_threshold = $l_notification_threshold * -1;
                            while ($l_month < $l_notification_threshold) {
                                $l_month += 12;
                                $l_year--;
                            }
                            $l_month -= $l_notification_threshold;
                            $l_days_in_month = date('t', strtotime($l_year . '-' . $l_month . '-01'));
                            if ($l_day > $l_days_in_month) {
                                $l_day = $l_days_in_month;
                            }
                            $l_threshold = ($l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day)) * -1;
                        } else {
                            $l_month += $l_notification_threshold;
                            while ($l_month > 12) {
                                $l_month -= 12;
                                $l_year++;
                            }
                            $l_days_in_month = date('t', strtotime($l_year . '-' . $l_month . '-01'));
                            if ($l_day > $l_days_in_month) {
                                $l_day = $l_days_in_month;
                            }
                            $l_threshold = (strtotime($l_year . '-' . $l_month . '-' . $l_day) - $l_now);
                        }

                        break;
                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        $l_year = $l_year + $l_notification_threshold;
                        if ($l_notification_threshold < 0) {
                            $l_threshold = ($l_now - strtotime($l_year . '-' . $l_month . '-' . $l_day)) * -1;
                        } else {
                            $l_threshold = strtotime($l_year . '-' . $l_month . '-' . $l_day) - $l_now;
                        }
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

        $l_threshold = $l_now + $l_threshold;

        // Check for any status plans:

        $l_objects = [];

        $l_planning_dao = new isys_cmdb_dao_category_g_planning($this->m_db);

        $l_properties = $l_planning_dao->get_properties();
        $l_field = $l_properties[$this->m_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
        unset($l_properties);

        if (is_countable($l_notification_objects)) {
            foreach ($l_notification_objects as $l_object) {
                $this->m_log->debug(sprintf('Handling CMDB object "%s" [%s]...', $l_object['isys_obj__title'], $l_object['isys_obj__id']));

                $l_plans = $l_planning_dao->get_data(null, $l_object['isys_obj__id'])
                    ->__as_array();

                if (is_countable($l_plans) && count($l_plans) === 0) {
                    $this->m_log->debug('There are no plans for this object.');

                    continue;
                }

                foreach ($l_plans as $l_plan) {
                    if (!is_numeric($l_plan[$l_field])) {
                        $this->m_log->debug('Date is not set.');

                        continue;
                    }

                    $l_date = $l_plan[$l_field];

                    // Check whether current timestamp is between determined
                    // threshold and the property date:

                    if (($p_notification['threshold'] <= 0 && $l_now >= $l_threshold && $l_now <= $l_date) ||
                        ($p_notification['threshold'] >= 0 && $l_now >= $l_date && $l_now <= $l_threshold)) {
                        $this->m_log->debug(sprintf('Threshold exceeded! %s is between %s and %s.', date($l_date_format, $l_now), date($l_date_format, $l_threshold),
                            date($l_date_format, $l_date)));

                        $l_objects[] = $l_object;
                    } else {
                        $this->m_log->debug(sprintf('Threshold not exceeded! %s is not between %s and %s.', date($l_date_format, $l_now), date($l_date_format, $l_threshold),
                            date($l_date_format, $l_date)));

                        continue;
                    }
                }
            }
        }

        unset($l_planning_dao, $l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num == 0) {
            $this->m_log->debug('No objects are left to report!');

            return $this->reset_counter($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of CMDB objects to report: %s', $l_num));
        }

        // Write messages:

        if ($this->write_messages($p_notification, $l_objects) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
    }

}

?>