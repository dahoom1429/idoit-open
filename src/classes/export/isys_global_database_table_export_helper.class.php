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
class isys_global_database_table_export_helper extends isys_export_helper
{
    public function assignedDatabaseSchema($id)
    {
        $return =  [];

        if ($id > 0) {
            $databaseData = isys_cmdb_dao_category_g_database_sa::instance($this->m_database)->get_data($id)->get_row();
            $return[] = [
                'id' => $databaseData['isys_catg_database_sa_list__id'],
                'title' => $databaseData["isys_catg_database_sa_list__title"],
                'type'         => 'C__CATG__DATABASE_SA'
            ];
        }

        return new isys_export_data($return);
    }

    public function assignedDatabaseSchema_import($data)
    {
        $id = 0;

        if (is_array($data[C__DATA__VALUE]) && isset($data[C__DATA__VALUE][0]['id'])) {
            $id = $data[C__DATA__VALUE][0]['id'];
        } elseif (array_key_exists('id', $data)) {
            $id = $data['id'];
        }

        if ($id > 0 &&
            defined('C__CATG__DATABASE_SA') &&
            isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE_SA')][$id])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE_SA')][$id];
        }

        return null;
    }
}
