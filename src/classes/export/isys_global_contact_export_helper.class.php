<?php

/**
 * i-doit
 *
 * Export helper for global category contact
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_contact_export_helper extends isys_export_helper
{
    /**
     * Export helper for contact information
     *
     * @return array
     * @throws isys_exception_database
     */
    public function exportContactAssignment()
    {
        // Export contact related information for xml processing.
        return $this->export_contact($this->m_row['isys_connection__isys_obj__id']);
    }
}
