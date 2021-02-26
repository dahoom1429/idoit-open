<?php

/**
 * i-doit
 *
 * Notification: A CMDB status ends.
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_status_planning_end extends isys_notification_status_planning
{

    /**
     * Initiates notification.
     *
     * @param array $p_type Information about this notification type
     */
    public function init($p_type)
    {
        $this->m_property = 'end';
        parent::init($p_type);
    }

}

?>