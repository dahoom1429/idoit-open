<?php

/**
 * i-doit
 *
 * Notification: Count objects by their CMDB status
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_notification_count_objects_by_cmdb_status extends isys_notification
{
    protected $m_cmdb_status;

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     */
    protected function handle_notification($p_notification)
    {
        // Fetch objects selected by notification:
        $l_notification_objects = $this->m_dao->get_objects($p_notification['id']);

        // Get objects which match destinated CMDB status:

        $l_objects = [];

        foreach ($l_notification_objects as $l_object) {
            if ($l_object['isys_obj__isys_cmdb_status__id'] == $this->m_cmdb_status) {
                $l_objects[] = $l_object;
            }
        }

        unset($l_notification_objects);

        $l_num = count($l_objects);
        $p_notification['threshold'] = (int)$p_notification['threshold'];

        if (($l_num === 0) && $p_notification['threshold'] === 0) {
            $this->m_log->debug('There are no objects left to report. Skip notification.');

            return $this->reset_counter($p_notification);
        } else {
            $this->m_log->debug(sprintf('Amount of objects which match the criterias: %s', $l_num));
        }

        // Check whether threshold is exceeded:
        if (($p_notification['threshold'] > 0 && $l_num <= $p_notification['threshold']) ||
            ($p_notification['threshold'] < 0 && $l_num >= ($p_notification['threshold'] * -1))) {
            $this->m_log->debug(sprintf('Threshold not exceeded (%s). Skip notification.', $p_notification['threshold']));

            return $this->reset_counter($p_notification);
        }

        $this->m_log->debug(sprintf('Threshold exceeded (%s)!', $p_notification['threshold']));

        // Write messages:

        if ($this->write_messages($p_notification, $l_objects) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
    }
}
