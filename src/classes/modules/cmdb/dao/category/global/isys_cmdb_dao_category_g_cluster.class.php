<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for clusters
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cluster extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'cluster';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLUSTER';

    /**
     * Category entry is purgable.
     *
     * @var boolean
     */
    protected $m_is_purgable = true;

    /**
     * Trigger save process of global category cluster.
     *
     * @param   integer $p_cat_level        level to save, default 0
     * @param   integer &$p_intOldRecStatus __status of record before update
     *
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $return = false;
        $categoryData = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])->get_row();

        $p_intOldRecStatus = $categoryData['isys_catg_cluster_list__status'];

        if (!empty($categoryData['isys_catg_cluster_list__id'])) {
            $return = $this->save(
                $categoryData['isys_catg_cluster_list__id'],
                $_POST['C__CATG__CLUSTER__QUORUM'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $return == true ? null : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $categoryId.
     *
     * @param   integer $categoryId
     * @param   integer $quorum
     * @param   string  $description
     * @param   integer $recordStatus
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($categoryId, $quorum, $description = null, $recordStatus = C__RECORD_STATUS__NORMAL)
    {
        $sql = 'UPDATE isys_catg_cluster_list SET
			isys_catg_cluster_list__description = ' . $this->convert_sql_text($description) . ',
			isys_catg_cluster_list__quorum = ' . $this->convert_sql_id($quorum) . ',
			isys_catg_cluster_list__status = ' . $this->convert_sql_int($recordStatus) . '
			WHERE isys_catg_cluster_list__id = ' . $this->convert_sql_id($categoryId) . ';';

        return $this->update($sql) && $this->apply_update();
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_cluster__id $p_fk_id.
     *
     * @param   integer $objectId
     * @param   boolean $quorum
     * @param   string  $description
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($objectId, $quorum = false, $description = "")
    {
        $categoryId = $this->create_connector('isys_catg_cluster_list', $objectId);

        if ($this->save($categoryId, $quorum, $description)) {
            return $categoryId;
        }

        return false;
    }

    /**
     *
     * @param   integer $objectId
     * @param   integer $categoryId
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_cluster_services($objectId = null, $categoryId = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_cluster_service_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_cluster_service_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($objectId !== null) {
            $l_sql .= ' AND isys_catg_cluster_service_list__isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        if ($categoryId !== null) {
            $l_sql .= ' AND isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($categoryId);
        }

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $objectId
     * @param   integer $categoryId
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_cluster_members($objectId = null, $categoryId = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_cluster_members_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_cluster_members_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($objectId !== null) {
            $l_sql .= ' AND isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        if ($categoryId !== null) {
            $l_sql .= ' AND isys_catg_cluster_members_list__id = ' . $this->convert_sql_id($categoryId);
        }

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $objectId
     * @param   integer $categoryId
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_administration_services($objectId = null, $categoryId = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_cluster_adm_service_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_catg_cluster_adm_service_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($objectId !== null) {
            $l_sql .= ' AND isys_catg_cluster_adm_service_list__isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        if ($categoryId !== null) {
            $l_sql .= ' AND isys_catg_cluster_adm_service_list__id = ' . $this->convert_sql_id($categoryId);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_administration_service' => new DynamicProperty(
                'LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE',
                'isys_catg_cluster_adm_service_list__isys_obj__id',
                'isys_catg_cluster_adm_service_list',
                [
                    'isys_cmdb_dao_category_g_cluster_adm_service',
                    'dynamic_property_callback_administration_service'
                ]
            ),
            '_cluster_members' => new DynamicProperty(
                'LC__CMDB__CATG__CLUSTER_MEMBERS',
                'isys_catg_cluster_adm_service_list__isys_obj__id',
                'isys_catg_cluster_adm_service_list',
                [
                    'isys_cmdb_dao_category_g_cluster_adm_service',
                    'dynamic_property_callback_cluster_members'
                ]
            ),
            '_cluster_service' => new DynamicProperty(
                'LC__CMDB__CATG__CLUSTER_SERVICES',
                'isys_catg_cluster_adm_service_list__isys_obj__id',
                'isys_catg_cluster_adm_service_list',
                [
                    'isys_cmdb_dao_category_g_cluster_adm_service',
                    'dynamic_property_callback_cluster_service'
                ]
            )
        ];
    }

    /**
     * Returns how many entries exists. The folder always returns 1.
     *
     * @param   integer $objectId
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($objectId = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATG__CLUSTER_ROOT')) {
            return 1;
        }

        return parent::get_count($objectId);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        return [
            'quorum'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__QUORUM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Quorum'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_list__quorum',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('(CASE WHEN isys_catg_cluster_list__quorum = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                WHEN isys_catg_cluster_list__quorum = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)', 'isys_catg_cluster_list'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cluster_list', 'LEFT', 'isys_catg_cluster_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__CLUSTER__QUORUM',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ],
                    C__PROPERTY__UI__DEFAULT => 0
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'administration_service' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Administration service'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_adm_service_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\' ) FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_adm_service_list ON isys_catg_cluster_adm_service_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_adm_service_list',
                        '',
                        'isys_catg_cluster_adm_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cluster_adm_service_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'cluster_members'        => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_MEMBERS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster members'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_members_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_members_list ON isys_catg_cluster_members_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_members_list',
                        '',
                        'isys_catg_cluster_members_list__isys_obj__id',
                        '',
                        '',
                        \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
                            'AND isys_catg_cluster_members_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL)
                        ]),
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cluster_members_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'cluster_member_count' => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_MEMBERS_COUNT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster member count'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_members_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT COUNT(isys_catg_cluster_members_list__isys_obj__id) FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_members_list ON isys_catg_cluster_members_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_members_list',
                        '',
                        'isys_catg_cluster_members_list__isys_obj__id',
                        '',
                        '',
                        \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            'AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
                            'AND isys_catg_cluster_members_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL)
                        ])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'cluster_service'        => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster services'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_service_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_service_list ON isys_catg_cluster_service_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_service_list',
                        '',
                        'isys_catg_cluster_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cluster_service_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description'            => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_cluster_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CLUSTER', 'C__CATG__CLUSTER')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param  array   $categoryData
     * @param  integer $objectId
     * @param  integer $saveMode
     *
     * @return int|bool Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($categoryData, $objectId, $saveMode = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($categoryData) && isset($categoryData['properties'])) {
            $this->m_sync_catg_data = $categoryData;

            if ($saveMode === isys_import_handler_cmdb::C__CREATE) {
                if ($objectId > 0) {
                    return $this->create(
                        $objectId,
                        $categoryData['properties']['quorum'][C__DATA__VALUE],
                        $categoryData['properties']['description'][C__DATA__VALUE]
                    );
                }
            } elseif ($saveMode === isys_import_handler_cmdb::C__UPDATE) {
                if ($categoryData['data_id'] > 0) {
                    $this->save(
                        $categoryData['data_id'],
                        $categoryData['properties']['quorum'][C__DATA__VALUE],
                        $categoryData['properties']['description'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL
                    );

                    return $categoryData['data_id'];
                }
            }
        }

        return false;
    }
}
