<?php

use idoit\Component\Property\Type\DialogCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\IntWithUnitProperty;
use idoit\Component\Property\Type\MemoryProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\TextProperty;

class isys_cmdb_dao_category_g_connection_endpoint extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'connection_endpoint';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATG__CONNECTION_ENDPOINT';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CONNECTION_ENDPOINT';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_connection_endpoint_list__isys_obj__id';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_connection_endpoint_list__isys_obj__id__connectedto';

    /**
     * @param       $entryId
     * @param       $recordStatus
     * @param false $create
     *
     * @return null
     */
    public function save_element(&$entryId, &$recordStatus, $create = false)
    {
        return null;
    }

    /**
     * @param null     $entryId
     * @param null     $objectId
     * @param string   $condition
     * @param null     $filter
     * @param int|null $status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($entryId = null, $objectId = null, $condition = '', $filter = null, $status = C__RECORD_STATUS__NORMAL)
    {
        $condition .= $this->prepare_filter($filter);

        $query = 'SELECT isys_catg_connection_endpoint_list.*, objA.isys_obj__id as objectA_id, objB.isys_obj__id as objectB_id, objA.isys_obj__title as objectA_title, objB.isys_obj__title as objectB_title FROM isys_catg_connection_endpoint_list
            LEFT JOIN isys_obj as objA ON objA.isys_obj__id = isys_catg_connection_endpoint_list__isys_obj__id
            LEFT JOIN isys_obj as objB ON objB.isys_obj__id = isys_catg_connection_endpoint_list__isys_obj__id__connectedto
            WHERE TRUE ' . $condition . ' AND isys_catg_connection_endpoint_list__status = ' . $this->convert_sql_int($status);

        if ($entryId !== null) {
            $query .= ' AND isys_catg_connection_endpoint_list__id = ' . $this->convert_sql_int($entryId);
        }

        if ($objectId !== null) {
            $query .= ' AND (isys_catg_connection_endpoint_list__isys_obj__id = ' . $this->convert_sql_int($objectId) . ' 
                OR isys_catg_connection_endpoint_list__isys_obj__id__connectedto = ' . $this->convert_sql_int($objectId) . ')';
        }

        return $this->retrieve($query);
    }

    /**
     * @param int|null $objId
     *
     * @return false|int|mixed
     * @throws isys_exception_database
     */
    public function get_count($objId = null)
    {
        $objectId = $objId ?: $this->m_object_id;

        $query = "SELECT COUNT(isys_catg_connection_endpoint_list__id) AS count FROM isys_catg_connection_endpoint_list WHERE TRUE ";

        if ($objectId !== null) {
            $query .= ' AND (isys_catg_connection_endpoint_list__isys_obj__id = ' . $this->convert_sql_int($objectId) . ' 
                OR isys_catg_connection_endpoint_list__isys_obj__id__connectedto = ' . $this->convert_sql_int($objectId) . ')';
        }

        $query .= " AND (isys_catg_connection_endpoint_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        return $this->retrieve($query)
            ->get_row_value('count');
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function properties()
    {
        return [
            'title' => new TextProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__TITLE',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__TITLE',
                'isys_catg_connection_endpoint_list__title',
                'isys_catg_connection_endpoint_list'
            ),
            'interface' => new DialogProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__INTERFACE',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__INTERFACE',
                'isys_catg_connection_endpoint_list__isys_catg_netp_list__id',
                'isys_catg_connection_endpoint_list',
                'isys_catg_netp_list',
                false,
                [
                    'isys_export_helper',
                    'interface_p'
                ]
            ),
            'connectedToTitle' => new TextProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO_TITLE',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO_TITLE',
                'isys_catg_connection_endpoint_list__title__connectedto',
                'isys_catg_connection_endpoint_list'
            ),
            'connectedTo' => (new ObjectBrowserProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__CCONNECTED_TO',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO',
                'isys_catg_connection_endpoint_list__isys_obj__id__connectedto',
                'isys_catg_connection_endpoint_list'
            ))->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__CONNECTION_ENDPOINT')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_connection_endpoint',
                    'callback_property_relation_handler'
                ], [
                    'isys_cmdb_dao_category_g_connection_endpoint',
                    true
                ])
            ),
            'type' => new DialogProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__TYPE',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__TYPE',
                'isys_catg_connection_endpoint_list__isys_port_type__id',
                'isys_catg_connection_endpoint_list',
                'isys_port_type'
            ),
            'speedUnit'  => new DialogProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__SPEED_UNIT',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__SPEED_UNIT',
                'isys_catg_connection_endpoint_list__isys_port_speed__id',
                'isys_catg_connection_endpoint_list',
                'isys_port_speed'
            ),
            'speed' => new IntWithUnitProperty(
                'C__CMDB__CATG__CONNECTION_ENDPOINT__SPEED',
                'LC__CMDB__CATG__CONNECTION_ENDPOINT__SPEED',
                'isys_catg_connection_endpoint_list__speed',
                'isys_catg_connection_endpoint_list',
                'isys_port_speed',
                'speedUnit',
                [
                    'isys_export_helper',
                    'convert',
                    ['speed']
                ],
                'isys_catg_connection_endpoint_list__isys_port_speed__id'
            ),
        ];
    }
}
