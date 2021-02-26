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
class isys_global_cluster_service_export_helper extends isys_export_helper
{
    /**
     * Export helper for application licence.
     *
     * @param   integer $p_id
     *
     * @return  mixed  Either boolean false or isys_export_data.
     */
    public function clusterServiceStatus($serviceStatus)
    {
        return [
            'id'         => $serviceStatus,
            'title'      => $serviceStatus,
            'title_lang' => isys_cmdb_dao_category_g_cluster_service::getServiceStatus($serviceStatus)
        ];
    }

    /**
     * Export helper for application licence.
     *
     * @param   integer $p_id
     *
     * @return  mixed  Either boolean false or isys_export_data.
     */
    public function clusterServiceStatus_import($exportData)
    {
        return (isset($exportData['id']) ? $exportData['id'] : (isset($exportData[C__DATA__VALUE]) ? $exportData[C__DATA__VALUE] : false));
    }
}