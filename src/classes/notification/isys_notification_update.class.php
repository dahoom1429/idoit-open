<?php

/**
 * i-doit
 *
 * Notification: Check for i-doit updates.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_update extends isys_notification
{

    /**
     * Handles a notification. This method is used to handle each notification
     * for this notification type.
     *
     * @param array $p_notification Information about notification
     *
     * @return int|null
     */
    protected function handle_notification($p_notification)
    {
        $l_update = new isys_update();

        // @see  ID-6872  System settings can now provide a update-XML URL.
        if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
            $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
        } else {
            $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
        }

        $l_xml = $l_update->fetch_file($updateXmlUrl);
        $l_available_updates = $l_update->get_new_versions($l_xml);
        $l_info = $l_update->get_isys_info();

        $l_updates = [];

        foreach ($l_available_updates as $l_available_update) {
            if ($l_available_update['revision'] > $l_info['revision']) {
                $l_updates[] = $l_available_update;
                $this->m_log->notice(sprintf('i-doit update %s found', $l_available_update['version']));
            }
        }

        $l_num = count($l_updates);

        if ($l_num == 0) {
            $this->m_log->debug('There are no updates available. Skip notification.');

            return $this->reset_counter($p_notification);
        }

        $this->m_log->debug(sprintf('Amount of updates: %s', $l_num));

        // Write messages:
        if ($this->write_messages($p_notification) > 0) {
            return $this->increase_counter($p_notification);
        }

        // Do not increase or reset counter...
        return null;
    }
}
