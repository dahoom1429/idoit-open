<?php

/**
 * i-doit
 *
 * Notification: Generic report.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_generic_report extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     *
     * @return bool|int
     */
    protected function handle_notification($p_notification)
    {
        $this->m_log->set_log_level(isys_log::C__ALL);
        $this->m_log->set_verbose_level(isys_log::C__ALL);

        // Number of reports
        $l_count_objects = 0;

        // Array of report results
        $l_objects_of_report = [];

        // Get all available domains first
        $l_domains = $this->m_dao->get_domains($p_notification['id']);

        // Check for selected reports
        if (is_array($l_domains) && isset($l_domains['reports']) && is_countable($l_domains['reports']) && count($l_domains['reports'])) {
            $l_report_ids = $l_domains['reports'];
            $l_report_dao = isys_report_dao::instance(isys_application::instance()->database_system);
            $l_cmdb_dao = isys_cmdb_dao::instance($this->m_dao->get_database_component());

            foreach ($l_report_ids AS $l_report_id) {
                // Get report specific data
                $l_report_data = $l_report_dao->get_report($l_report_id);

                // Get results of report
                $l_report_result = $l_report_dao->query($l_report_data['isys_report__query']);

                // Check for existing results
                if (is_array($l_report_result) && isset($l_report_result['num']) && $l_report_result['num'] > 0) {
                    // Get object ids
                    foreach ($l_report_result['content'] AS $l_report_resultset) {
                        // Get object data
                        $l_objects_of_report[$l_report_data['isys_report__id']][] = $l_cmdb_dao->get_object($l_report_resultset['__id__'])
                            ->get_row();

                        // Increase object count
                        $l_count_objects++;
                    }
                }
            }
        }

        // Do we have any results
        if (is_array($l_objects_of_report) && count($l_objects_of_report) && $l_count_objects > (int)$p_notification['threshold']) {
            // Send for each report
            foreach ($l_objects_of_report AS $l_report_id => $l_objects) {
                // Write messages:
                $this->write_messages($p_notification, $l_objects);
            }

            return $this->increase_counter($p_notification);
        } else {
            return $this->reset_counter($p_notification);
        }
    }

}
