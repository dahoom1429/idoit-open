<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_ip_export_helper extends isys_export_helper
{
    /**
     * Export ip information by ip id from specific category ip addresses list.
     *
     * @param $categoryIpId
     *
     * @return array
     */
    public function exportIpReference($ipReferenceId)
    {
        $exportData = [];

        $ipAddressDao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_database);
        $ipReferenceData = $ipAddressDao->get_data($ipReferenceId)
            ->get_row();

        if ($ipReferenceData && isset($ipReferenceData['isys_obj__isys_obj_type__id']) && isset($ipReferenceData['isys_cats_net_ip_addresses_list__title'])) {
            $objectTypeConst = $ipAddressDao->get_objtype($ipReferenceData['isys_obj__isys_obj_type__id'])
                ->get_row_value('isys_obj_type__const');

            $exportData = [
                'id'        => $ipReferenceData['isys_cats_net_ip_addresses_list__isys_obj__id'],
                'type'      => $objectTypeConst,
                'title'     => $ipReferenceData['isys_obj__title'],
                'sysid'     => $ipReferenceData['isys_obj__sysid'],
                'ref_id'    => $ipReferenceId,
                'ref_title' => $ipReferenceData['isys_cats_net_ip_addresses_list__title'],
                'ref_type'  => 'C__CATS__NET_IP_ADDRESSES'
            ];
        }

        return $exportData;
    }

    public function exportPrimaryIpReference($ipReferenceId)
    {
        $exportData = [];

        $ipAddressDao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->m_database);
        $ipReferenceData = $ipAddressDao->get_data($ipReferenceId, null, ' AND isys_catg_ip_list__primary = 1')
            ->get_row();

        if (
            $ipReferenceData &&
            isset($ipReferenceData['isys_obj__isys_obj_type__id']) &&
            isset($ipReferenceData['isys_cats_net_ip_addresses_list__title'])
        ) {
            $objectTypeConst = $ipAddressDao->get_objtype($ipReferenceData['isys_obj__isys_obj_type__id'])
                ->get_row_value('isys_obj_type__const');

            $exportData = [
                'id'        => $ipReferenceData['isys_cats_net_ip_addresses_list__isys_obj__id'],
                'type'      => $objectTypeConst,
                'title'     => $ipReferenceData['isys_obj__title'],
                'sysid'     => $ipReferenceData['isys_obj__sysid'],
                'ref_id'    => $ipReferenceId,
                'ref_title' => $ipReferenceData['isys_cats_net_ip_addresses_list__title'],
                'ref_type'  => 'C__CATS__NET_IP_ADDRESSES'
            ];
        }

        return $exportData;
    }

    public function exportPrimaryIpReference_import($importData)
    {
        return $importData['ref_title'];
    }

    /**
     * Import method for IP references.
     *
     * @param   array $importData
     *
     * @return  string
     */
    public function exportIpReference_import($importData)
    {
        return $importData['ref_title'];
    }

    /**
     * Export hostname
     *
     * @param $hostname
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportHostname($hostname)
    {
        return $hostname;
    }

    /**
     * Import hostname and prevent the creation of duplicate hostnames if configuration cmdb.unique.hostname is set.
     *
     * @param $hostnameData
     *
     * @return null|string
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportHostname_import($hostnameData)
    {
        if (!isset($hostnameData[C__DATA__VALUE])) {
            return null;
        }

        $hostname = $hostnameData[C__DATA__VALUE];
        /* Is uniquecheck for hostnames activated? */
        if (isys_tenantsettings::get('cmdb.unique.hostname') && !empty($hostname)) {
            $dao = isys_cmdb_dao::instance($this->m_database);

            // Object Id of the assigned layer-3 net for the ip
            $netObjectId = (is_numeric($this->m_property_data['net'][C__DATA__VALUE])) ? $this->m_property_data['net'][C__DATA__VALUE] : $this->m_property_data['net']['id'];

            // Object Id of the current object which is being imported
            $currentObjectId = $this->m_object_ids[isys_import_handler_cmdb::get_stored_objectID()];

            $query = 'SELECT isys_catg_ip_list__id FROM isys_catg_ip_list
 					INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id
					WHERE 
					  isys_catg_ip_list__isys_obj__id != ' . $dao->convert_sql_id($currentObjectId) . ' AND 
					  isys_catg_ip_list__status = ' . C__RECORD_STATUS__NORMAL . ' AND 
					  isys_catg_ip_list__hostname = ' . $dao->convert_sql_text($hostname) . ' AND 
					  isys_cats_net_ip_addresses_list__isys_obj__id = ' . $dao->convert_sql_id($netObjectId) . ' LIMIT 1';

            $resultCheck = $dao->retrieve($query);
            /* Is there an existing hostname */
            if ($resultCheck->num_rows() > 0) {
                $hostname = null;
            }
        }

        return $hostname;
    }

    /**
     * Method for exporting hostadress aliases.
     *
     * @param  integer $categoryIpId
     *
     * @return isys_export_data
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportHostaddressAliases($categoryIpId)
    {
        $exportData = [];
        $result = isys_cmdb_dao_category_g_ip::instance($this->m_database)
            ->get_hostname_pairs($categoryIpId);

        if (is_countable($result) && count($result)) {
            while ($ipData = $result->get_row()) {
                $exportData[] = [
                    'id'       => $ipData['isys_hostaddress_pairs__id'],
                    'hostname' => $ipData['isys_hostaddress_pairs__hostname'],
                    'domain'   => $ipData['isys_hostaddress_pairs__domain'],
                ];
            }
        }

        return new isys_export_data($exportData);
    }

    /**
     * Import method for hostaddress aliases.
     *
     * @param   array $importData
     *
     * @return  array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportHostaddressAliases_import($importData)
    {
        $hostaddressAliasData = [];

        if (is_array($importData[C__DATA__VALUE]) && count($importData[C__DATA__VALUE])) {
            foreach ($importData[C__DATA__VALUE] as $data) {
                $hostaddressAliasData[] = [
                    'host'   => $data['hostname'],
                    'domain' => $data['domain']
                ];
            }
        }

        return $hostaddressAliasData;
    }

    /**
     * Method for exporting fully qualified domain name.
     *
     * @param  integer $categoryIpId
     *
     * @return isys_export_data
     * @author Oscar Pohl <opohl@i-doit.com>
     */
    public function exportFqdn($categoryIpId)
    {
        $exportData = [];
        $result = isys_cmdb_dao_category_g_ip::instance($this->m_database)
            ->get_data_by_id($categoryIpId, "AND isys_catg_ip_list__hostname != ''");

        if (is_countable($result) && count($result)) {
            $ipData = $result->get_row();

            if ($ipData['isys_catg_ip_list__domain']) {
                $ipData['isys_catg_ip_list__hostname'] .= '.' . $ipData['isys_catg_ip_list__domain'];
            }

            $exportData[] = [
                'id'       => $ipData['isys_catg_ip_list__id'],
                'title' => $ipData['isys_catg_ip_list__hostname'],
            ];
        }

        return new isys_export_data($exportData);
    }
}
