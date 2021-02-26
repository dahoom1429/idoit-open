<?php

/**
 * i-doit
 *
 * Notification: Count licenses
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_count_licenses extends isys_notification
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
            $this->m_log->info(sprintf('Amount of licenses: %s', $l_num));
        }

        // Count licenses:
        $l_licenses = [];

        // Iterate through each license:
        foreach ($l_objects as $l_object) {
            $this->m_log->info(sprintf('Handling license "%s"...', $l_object['isys_obj__title']));

            $l_cmdb_dao = new isys_cmdb_dao_licences($this->m_db, $l_object['isys_obj__id']);

            $l_res = $l_cmdb_dao->get_licences(true);

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_free = $l_row['isys_cats_lic_list__amount'] - $l_cmdb_dao->get_licences_in_use(C__RECORD_STATUS__NORMAL, $l_row['isys_cats_lic_list__id'])
                            ->num_rows();

                    if ($l_row['isys_cats_lic_list__type'] === C__LICENCE_TYPE__SINGLE && $l_free >= 0) {
                        continue;
                    }

                    if ($l_free < 0) {
                        $this->m_log->debug(sprintf('License-Key (%s) is too often in use! (%d)', $l_row['isys_cats_lic_list__key'], $l_free));
                    }

                    if ($l_free > $p_notification['threshold']) {
                        $this->m_log->debug(sprintf('Licence-Key (%s) did not exceeded threshold (%d). Skip License-Key.', $l_row['isys_cats_lic_list__key'],
                            $p_notification['threshold']));
                        continue;
                    }

                    $this->m_log->debug('Threshold exceeded! Add license to the list and jump to the next licence object.');
                    $l_licenses[] = $l_object;
                    break;
                }
            }
        }

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