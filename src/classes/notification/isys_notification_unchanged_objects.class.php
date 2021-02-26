<?php

/**
 * i-doit
 *
 * Notification: Informs about objects which have been unchanged since a period
 * of time
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_unchanged_objects extends isys_notification
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

        // Get objects which have been unchanged since a period of time:

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

        $l_threshold_date = $l_now - $l_threshold;
        $l_formatted_threshold = date('Y-m-d H:i:s', $l_threshold_date);

        $this->m_log->debug(sprintf('Collecting objects which have not been updated since %s...', $l_formatted_threshold));

        $l_objects = [];

        foreach ($l_notification_objects as $l_object) {
            $this->m_log->debug(sprintf('Handling object "%s" [%s]...', $l_object['isys_obj__title'], $l_object['isys_obj__id']));

            $l_updated = strtotime($l_object['isys_obj__updated']);

            if ($l_updated === 0) {
                $this->m_log->debug('Object has not been updated yet. Skip it.');

                continue;
            }

            if ($l_threshold_date < $l_updated) {
                $this->m_log->debug(sprintf('Object has been changed at %s that is after %s. Skip it.', $l_object['isys_obj__updated'], $l_formatted_threshold));

                continue;
            }

            $this->m_log->debug(sprintf('Object has been changed at %s that is before %s. Append it to the list.', $l_object['isys_obj__updated'], $l_formatted_threshold));

            $l_objects[] = $l_object;
        }

        unset ($l_notification_objects);

        $l_num = count($l_objects);

        if ($l_num === 0) {
            $this->m_log->info('There are no objects left to report. Skip notification.');

            return $this->reset_counter($p_notification);
        } else {
            $this->m_log->info(sprintf('Amount of objects which have been unchanged since %s: %s', $l_formatted_threshold, $l_num));
        }

        // Write messages:

        if ($this->write_messages($p_notification, $l_objects) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
    }

}

?>