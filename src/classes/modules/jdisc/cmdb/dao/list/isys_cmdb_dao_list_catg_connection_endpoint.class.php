<?php

/**
 * Class isys_cmdb_dao_list_catg_connection_endpoint
 */
class isys_cmdb_dao_list_catg_connection_endpoint extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CONNECTION_ENDPOINT');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @return bool
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Gets result list.
     *
     * @param string     $table
     * @param int|null   $objectId
     * @param int|null   $recordStatus
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_result($table = 'isys_catg_connection_endpoint_list', $objectId = null, $recordStatus = null)
    {
        $status = $recordStatus ?: $this->get_rec_status();
        $objectId = $objectId ?: $this->m_cat_dao->get_object_id();

        $query = 'SELECT 
                ' . $this->convert_sql_int($objectId) . ' as mainObject,
                objA.isys_obj__id as objA_ID, 
                objA.isys_obj__title as objA_Title, 
                objB.isys_obj__id as objB_ID, 
                objB.isys_obj__title as objB_Title,
                isys_catg_connection_endpoint_list.*,
                isys_port_type__title,
                isys_port_speed__id,
                isys_port_speed__title,
                isys_port_speed__factor,
                isys_catg_netp_list.*
            FROM isys_catg_connection_endpoint_list
                INNER JOIN isys_obj objA ON objA.isys_obj__id = isys_catg_connection_endpoint_list__isys_obj__id
                INNER JOIN isys_obj objB ON objB.isys_obj__id = isys_catg_connection_endpoint_list__isys_obj__id__connectedto
                LEFT JOIN isys_catg_netp_list ON isys_catg_netp_list__id = isys_catg_connection_endpoint_list__isys_catg_netp_list__id
                LEFT JOIN isys_port_type ON isys_port_type__id = isys_catg_connection_endpoint_list__isys_port_type__id
                LEFT JOIN isys_port_speed ON isys_port_speed__id = isys_catg_connection_endpoint_list__isys_port_speed__id
			WHERE TRUE';

        if ($objectId) {
            $query .= ' AND (isys_catg_connection_endpoint_list__isys_obj__id = ' . $this->convert_sql_int($objectId) . ' OR 
                isys_catg_connection_endpoint_list__isys_obj__id__connectedto = ' . $this->convert_sql_int($objectId) . ')';
        }

        if ($status) {
            $query .= ' AND isys_catg_connection_endpoint_list__status = ' . $this->convert_sql_int($status);
        }

        return $this->retrieve($query);
    }

    /**
     * Method for retrieving the field-names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'title' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__TITLE',
            'isys_catg_netp_list__title' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__INTERFACE',
            'isys_port_type__title' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__TYPE',
            'isys_catg_connection_endpoint_list__speed' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__SPEED',
            'connectedTo' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO',
            'connectedToTitle' => 'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO_TITLE',
        ];
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $quickinfo = isys_ajax_handler_quick_info::instance();
        if ($row['mainObject'] === $row['objA_ID']) {
            $row['title'] = $row['isys_catg_connection_endpoint_list__title'];
            $row['connectedTo'] = $quickinfo->get_quick_info($row['objB_ID'], $row['objB_Title'], C__LINK__OBJECT);
            $row['connectedToTitle'] = $row['isys_catg_connection_endpoint_list__title__connectedto'];
        } else {
            $row['title'] = $row['isys_catg_connection_endpoint_list__title__connectedto'];
            $row['connectedTo'] = $quickinfo->get_quick_info($row['objA_ID'], $row['objA_Title'], C__LINK__OBJECT);
            $row['connectedToTitle'] = $row['isys_catg_connection_endpoint_list__title'];
        }

        if (!empty($row['isys_catg_connection_endpoint_list__speed'])) {
            $row['isys_catg_connection_endpoint_list__speed'] = isys_convert::speed(
                $row['isys_catg_connection_endpoint_list__speed'],
                $row['isys_port_speed__id'],
                C__CONVERT_DIRECTION__BACKWARD
            ) . ' ' . isys_application::instance()->container->get('language')
                    ->get($row['isys_port_speed__title']);
        } else {
            $row['isys_catg_connection_endpoint_list__speed'] = 'N/A';
        }
    }

    /**
     * Method for retrieving the row-link.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return "#";
    }
}
