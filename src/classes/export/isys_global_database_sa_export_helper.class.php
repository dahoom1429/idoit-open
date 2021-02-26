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
class isys_global_database_sa_export_helper extends isys_export_helper
{
    public function assignedDbmsSa($id)
    {
        $return =  [];

        if ($id > 0) {
            $dao = isys_cmdb_dao_category_g_application::instance($this->m_database);
            $dbmsData = $dao->get_data($id)->get_row();
            $objectData = $dao->get_object($dbmsData['isys_catg_application_list__isys_obj__id'])->get_row();

            return [
                'id'  => $dbmsData['isys_catg_application_list__id'],
                'title' => $dbmsData['isys_obj__title'],
                'type'  => 'C__CATG__APPLICATION',
            ];
        }

        return new isys_export_data($return);
    }

    public function assignedDbmsSa_import($data)
    {
        if ($data['id'] > 0 &&
            defined('C__CATG__APPLICATION') &&
            isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$data['id']])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__APPLICATION')][$data['id']];
        }

        return null;
    }

    public function assignedInstance($id)
    {
        $return =  [];

        if ($id > 0) {
            $databaseData = isys_cmdb_dao_category_g_database::instance($this->m_database)->get_data($id)->get_row();
            $return[] = [
                'id' => $databaseData['isys_catg_database_list__id'],
                'title' => $databaseData["isys_catg_database_list__instance_name"],
                'type'         => 'C__CATG__DATABASE'
            ];
        }

        return new isys_export_data($return);
    }

    public function assignedInstance_import($data)
    {
        $id = 0;

        if (is_array($data[C__DATA__VALUE]) && isset($data[C__DATA__VALUE][0]['id'])) {
            $id = $data[C__DATA__VALUE][0]['id'];
        } elseif (array_key_exists('id', $data)) {
            $id = $data['id'];
        }

        if ($id > 0 &&
            defined('C__CATG__DATABASE') &&
            isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE')][$id])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE')][$id];
        }

        return null;
    }
}
