<?php

/**
 * i-doit
 *
 * Export helper for specific category net
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_specific_net_export_helper extends isys_export_helper
{
    /**
     * Exports property gateway from specific category net
     *
     * @return array
     */
    public function exportGateway($categoryIpId)
    {
        $dao = isys_cmdb_dao::instance($this->m_database);

        $query = 'SELECT isys_obj__id, isys_obj__sysid, isys_obj_type__const, isys_obj__title, isys_cats_net_ip_addresses_list__title FROM isys_catg_ip_list ' .
            'INNER JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id ' . 'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' .
            'INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id ' .
            'WHERE isys_catg_ip_list__id = ' . $dao->convert_sql_id($categoryIpId);

        $result = $dao->retrieve($query);

        if (is_countable($result) && count($result) > 0) {
            $data = $result->get_row();

            return [
                'id'        => $data['isys_obj__id'],
                'sysid'     => $data['isys_obj__sysid'],
                'type'      => $data['isys_obj_type__const'],
                'title'     => $data['isys_obj__title'],
                'ref_id'    => $categoryIpId,
                'ref_title' => $data['isys_cats_net_ip_addresses_list__title'],
                'ref_type'  => 'C__CATG__IP'
            ];
        }

        return null;
    }

    /**
     * Import method for export method exportGateway. Get the category id from global category ip address.
     * If it exists for the object return it otherwise create a new entry and return the id of it.
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function exportGateway_import($data)
    {
        if (!is_array($data)) {
            return null;
        }

        if (isset($data['id'])) {

            $daoIpAddress = isys_cmdb_dao_category_g_ip::instance($this->m_database);

            if (!isset($this->m_object_ids[$data['id']])) {
                $objectId = $data['id'];
            } else {
                $objectId = $this->m_object_ids[$data['id']];
            }

            $result = $daoIpAddress->get_ips_by_obj_id($objectId, false, false,
                null, ' AND isys_cats_net_ip_addresses_list__title = ' . $daoIpAddress->convert_sql_text($data['ref_title']));

            if (is_countable($result) && count($result) > 0) {
                return $result->get_row_value('isys_catg_ip_list__id');
            } else {
                $result = $daoIpAddress->get_object($objectId);
                if(is_countable($result) && count($result)) {
                    return $daoIpAddress->create($objectId, '', null, $data['ref_title'], '', null, '', '', '', null, null, null, '',
                        C__RECORD_STATUS__NORMAL);
                }
            }
        }
    }

    /**
     * Gets dns server for specific category net or global category hostaddress
     *
     * @param int $p_id
     *
     * @return isys_export_data|null
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function exportDnsServer($id)
    {
        $dao = isys_cmdb_dao::instance($this->m_database);
        $referenceInfo = $this->m_data_info[C__PROPERTY__DATA__REFERENCES];

        if (is_array($referenceInfo) && count($referenceInfo) > 0) {
            $referenceTable = $referenceInfo[0];
            $referenceConditionField = $referenceInfo[1];

            // Get the correct refencefield for the join
            if ($referenceTable == 'isys_catg_ip_list_2_isys_catg_ip_list') {
                $referenceJoinField = 'isys_catg_ip_list__id__dns';
            } else {
                $referenceJoinField = 'isys_catg_ip_list__id';
            }

            $l_sql = 'SELECT ip.*, isys_obj.*, isys_cats_net_ip_addresses_list.*, isys_obj_type.* FROM ' . $referenceTable . ' AS main ' .
                'INNER JOIN isys_catg_ip_list AS ip ON ip.isys_catg_ip_list__id = main.' . $referenceJoinField . ' ' .
                'INNER JOIN isys_cats_net_ip_addresses_list ON ip.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' .
                'INNER JOIN isys_obj ON ip.isys_catg_ip_list__isys_obj__id = isys_obj__id ' . 'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' .
                'WHERE main.' . $referenceConditionField . ' = ' . $dao->convert_sql_id($id);

            $result = $dao->retrieve($l_sql);
            if (is_countable($result) && count($result) > 0) {
                $return = [];
                while ($data = $result->get_row()) {
                    $return[] = [
                        'id'        => $data['isys_obj__id'],
                        'sysid'     => $data['isys_obj__sysid'],
                        'type'      => $data['isys_obj_type__const'],
                        'title'     => $data['isys_obj__title'],
                        'ref_id'    => $data['isys_catg_ip_list__id'],
                        'ref_title' => $data['isys_cats_net_ip_addresses_list__title'],
                        'ref_type'  => 'C__CATG__IP'
                    ];
                }

                return new isys_export_data($return);
            }
        }

        return null;
    }

    /**
     * Import method for exportDnsServer information which retrieves the id of the category entry from global category ip.
     * If it does not exists then it will be created.
     *
     * @param   array $p_value
     *
     * @return  array|null
     */
    public function exportDnsServer_import($importData)
    {
        if (!is_array($importData[C__DATA__VALUE]) || count($importData[C__DATA__VALUE]) === 0) {
            return null;
        }

        $data = $importData[C__DATA__VALUE];
        $daoIp = isys_cmdb_dao_category_g_ip::instance($this->m_database);
        $return = [];
        foreach ($data as $value) {
            if (!isset($value['id'])) {
                continue;
            }

            if (!isset($this->m_object_ids[$value['id']])) {
                $objectId = $value['id'];
            } else {
                $objectId = $this->m_object_ids[$value['id']];
            }

            $result = $daoIp->get_ips_by_obj_id($objectId, false, false,
                null, ' AND isys_cats_net_ip_addresses_list__title = ' . $daoIp->convert_sql_text($value['ref_title']));

            if (is_countable($result) && count($result) > 0) {
                $return[] = $result->get_row_value('isys_catg_ip_list__id');
            } else {
                $result = $daoIp->get_object($objectId);
                if(is_countable($result) && count($result)) {
                    $return[] = $daoIp->create($objectId, '', null, $value['ref_title'], '', null, '', '', null, null, null, '', C__RECORD_STATUS__NORMAL);
                }
            }
        }

        return $return;
    }

    /**
     * Export layer 2 assignment for the net
     *
     * @param $p_value
     *
     * @return isys_export_data|null
     */
    public function exportLayer2Assignments($ids)
    {
        $return = null;
        $daoNet = isys_cmdb_dao_category_s_net::instance($this->m_database);

        $lay2NetObjectIds = $daoNet->get_assigned_layer_2_ids($ids, true);
        if ($lay2NetObjectIds === null) {
            return null;
        }

        foreach ($lay2NetObjectIds AS $netObjectId) {
            $objectInfo = $daoNet->get_object_by_id($netObjectId)
                ->get_row();
            $return[] = [
                'id'    => $objectInfo['isys_obj__id'],
                'title' => $objectInfo['isys_obj__title'],
                'sysid' => $objectInfo['isys_obj__sysid'],
                'type'  => $objectInfo['isys_obj_type__const'],
            ];
        }

        return new isys_export_data($return);
    }

    /**
     * Import helper for specific category net for property layer_2_assignments
     *
     * @param $p_value
     *
     * @return array|null
     */
    public function exportLayer2Assignments_import($importData)
    {
        $data = $importData[C__DATA__VALUE];

        if (!is_array($data) || count($data) === 0) {
            return null;
        }

        $return = [];
        foreach ($data AS $layer2NetObject) {
            if (isset($this->m_object_ids[$layer2NetObject['id']])) {
                $return[] = $this->m_object_ids[$layer2NetObject['id']];
            }
        }

        return $return;
    }
}