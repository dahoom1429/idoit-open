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
class isys_notification_stored_objects extends isys_notification_count_objects_by_cmdb_status
{

    /**
     * Initiates notification.
     *
     * @param array $p_type Information about this notification type
     */
    public function init($p_type)
    {
        $this->m_cmdb_status = defined_or_default('C__CMDB_STATUS__STORED');
        parent::init($p_type);
    }

}

?>