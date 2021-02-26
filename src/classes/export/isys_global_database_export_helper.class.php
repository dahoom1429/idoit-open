<?php

/**
 * i-doit
 *
 * Export helper for global category database table.
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_database_export_helper extends isys_export_helper
{
    public function assignedDbms($id)
    {
        $return =  [];

        if ($id > 0) {
            $dbmsData = isys_cmdb_dao_category_g_application::instance($this->m_database)->get_data($id)->get_row();

            return [
                'id'      => $dbmsData['isys_catg_application_list__id'],
                'title'   => $dbmsData['isys_obj__title'],
                'type'    => 'C__CATG__APPLICATION',
            ];
        }

        return new isys_export_data($return);
    }

    public function assignedDbms_import($data)
    {
        if (isset($data['sysid']) && isset($data['id'])) {
            return $this->m_object_ids[$data['id']] ?: null;
        }

        $id = 0;
        if (is_array($data[C__DATA__VALUE]) && isset($data[C__DATA__VALUE][0]['id'])) {
            $id = $data[C__DATA__VALUE][0]['id'];
        } elseif (array_key_exists('id', $data)) {
            $id = $data['id'];
        }

        if ($id > 0 &&
            defined('C__CATG__APPLICATION') &&
            isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$id])) {
            return isys_cmdb_dao_category_g_application::instance($this->m_database)
                ->get_data($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$id])
                ->get_row_value('isys_obj__id');
        }

        return null;
    }
}
