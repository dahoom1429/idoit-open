<?php

/**
 * i-doit
 *
 * DAO: Global category view managed objects
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_managed_objects extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'managed_objects';

    /**
     * @var string
     */
    protected $m_category_const = 'C__CATG__MANAGED_OBJECTS';

    /**
     * @var string
     */
    protected $m_table = 'isys_catg_virtual_list';

    /**
     * @param int $p_cat_level
     * @param int $p_new_id
     *
     * @return NULL
     * @throws Exception
     */
    public function create_element($p_cat_level, &$p_new_id)
    {
        $p_new_id = false;
        $l_connected_objects = $_POST[C__POST__POPUP_RECEIVER];

        if (!is_null($l_connected_objects)) {
            // Get the array of ID's from our json-string.
            $l_objects = (array)isys_format_json::decode($l_connected_objects);

            // Select all items from the database-table for deleting them.
            $l_assigned_devices = $this->get_assigned_objects($_GET[C__CMDB__GET__OBJECT], true, false);

            // Now insert new items.
            if (is_array($l_objects)) {
                $l_assigned_objects = [];
                if (is_countable($l_assigned_devices) && count($l_assigned_devices)) {
                    $l_assigned_objects = array_flip($l_assigned_devices);
                }

                $l_res_objtypes = $this->get_obj_type_by_catg(filter_defined_constants(['C__CATG__CLUSTER_ROOT']));
                $l_allowed_objecttypes = [];
                while ($l_row = $l_res_objtypes->get_row()) {
                    $l_allowed_objecttypes[$l_row['isys_obj_type__id']] = true;
                }

                foreach ($l_objects as $l_object) {
                    if (!isset($l_assigned_objects[$l_object])) {
                        $l_is_cluster = isset($l_allowed_objecttypes[$this->get_objTypeID($l_object)]);

                        // Add administration service to object
                        $this->update_administration_service($_GET[C__CMDB__GET__OBJECT], $l_object, $l_is_cluster);
                    } else {
                        // unset object from array for deletion
                        unset($l_assigned_objects[$l_object]);
                    }
                }

                if (count($l_assigned_objects)) {
                    $l_delete_assignment = array_flip($l_assigned_objects);
                    foreach ($l_delete_assignment AS $l_obj_id) {
                        $l_is_cluster = isset($l_allowed_objecttypes[$this->get_objTypeID($l_obj_id)]);
                        $this->remove_administration_service($_GET[C__CMDB__GET__OBJECT], $l_obj_id, $l_is_cluster);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Save global category mail addresses element.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   bool    $p_create
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        return true;
    }

    /**
     * Creates a new entry for the category
     *
     * @param   array   $p_obj_id
     * @param   integer $p_status
     * @param   string  $p_title
     * @param   integer $p_primary
     * @param   string  $p_description
     *
     * @return  mixed
     * @author  Van Quyen Hoang
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_primary = null, $p_description = '')
    {
        return true;
    }

    /**
     * Updates a category entry by the given category entry id
     *
     * @param   int     $p_id
     * @param   mixed   $p_status
     * @param   string  $p_title
     * @param   integer $p_primary
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save($p_id, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_primary = null, $p_description = '')
    {
        return true;
    }

    /**
     * Method to retrieve all clusters which are assigned to the current object as administration service
     *
     * @param            $p_obj_id
     * @param bool|false $p_as_array
     * @param bool|true  $p_raw
     *
     * @return array|isys_component_dao_result
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_all_assigned_clusters($p_obj_id, $p_as_array = false, $p_raw = true)
    {
        $l_selection = 'isys_catg_cluster_adm_service_list__isys_obj__id AS id';
        $l_join = ' INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id ';

        if ($p_raw) {
            $l_selection = ' * ';
            $l_join .= ' INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_adm_service_list__isys_obj__id ';
        }

        $l_sql = 'SELECT ' . $l_selection . ' FROM isys_catg_cluster_adm_service_list ' . $l_join . '
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        $l_res = $this->retrieve($l_sql);
        if ($p_as_array) {
            $l_return = [];
            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_raw) {
                        $l_return[$l_row['id']] = $l_row;
                    } else {
                        $l_return[] = $l_row['id'];
                    }
                }
            }

            return $l_return;
        } else {
            return $l_res;
        }
    }

    /**
     * Method which retrieves all assigned virtual hosts by administration service
     *
     * @param $p_obj_id        int|null
     * @param $p_as_array      bool
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_all_physical_devices($p_obj_id, $p_as_array = false, $p_raw = true)
    {
        $l_selection = 'isys_catg_virtual_host_list__isys_obj__id AS id';
        $l_join = ' INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_host_list__administration_service ';

        if ($p_raw) {
            $l_selection = ' * ';
            $l_join .= ' INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_host_list__isys_obj__id ';
        }

        $l_sql = 'SELECT ' . $l_selection . ' FROM isys_catg_virtual_host_list ' . $l_join . '
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        // Get cluster members
        /*
         $l_sql .= ' UNION
         SELECT ' . $l_union_selection . ' FROM isys_catg_cluster_members_list INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
         INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
         WHERE isys_catg_cluster_members_list__isys_obj__id IN
         (SELECT isys_catg_cluster_adm_service_list__isys_obj__id FROM isys_catg_cluster_adm_service_list
         INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
         WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ')';
        */

        $l_res = $this->retrieve($l_sql);
        if ($p_as_array) {
            $l_return = [];
            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_raw) {
                        $l_return[$l_row['id']] = $l_row;
                    } else {
                        $l_return[] = $l_row['id'];
                    }
                }
            }

            return $l_return;
        } else {
            return $l_res;
        }
    }

    /**
     * Method which retrieves all virtual machines
     *
     * @param null       $p_obj_id
     * @param bool|false $p_as_array
     * @param bool|true  $p_raw
     *
     * @return array|bool|isys_component_dao_result
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_all_virtual_machines($p_obj_id = null, $p_as_array = false, $p_raw = true)
    {
        $l_physical_devices = $this->get_all_physical_devices($p_obj_id, true, false);
        if (is_countable($l_physical_devices) && count($l_physical_devices)) {
            $l_selection = 'isys_catg_virtual_machine_list__isys_obj__id AS id ';
            $l_join = 'INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id';
            if ($p_raw) {
                $l_selection = ' * ';
                $l_join .= ' INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_machine_list__isys_obj__id ';
            }

            $l_sql = 'SELECT ' . $l_selection . ' FROM isys_catg_virtual_machine_list ' . $l_join . '
				WHERE isys_connection__isys_obj__id IN (' . implode(',', $l_physical_devices) . ');';

            $l_res = $this->retrieve($l_sql);
            if ($p_as_array) {
                $l_return = [];
                if ($l_res->num_rows()) {
                    while ($l_row = $l_res->get_row()) {
                        if ($p_raw) {
                            $l_return[$l_row['id']] = $l_row;
                        } else {
                            $l_return[] = $l_row['id'];
                        }
                    }
                }

                return $l_return;
            } else {
                return $l_res;
            }
        }

        return false;
    }

    /**
     * Method for the object browser which retrieves all administration service assignments from clusters and virtual hosts
     *
     * @param            $p_obj_id
     * @param bool|false $p_as_array
     * @param bool|true  $p_raw
     *
     * @return array|isys_component_dao_result
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_objects($p_obj_id, $p_as_array = false, $p_raw = true)
    {
        $l_sql = 'SELECT isys_obj.* FROM isys_catg_virtual_host_list
			INNER JOIN isys_connection AS con ON con.isys_connection__id = isys_catg_virtual_host_list__administration_service
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_host_list__isys_obj__id
			WHERE con.isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        $l_sql .= ' UNION
			SELECT isys_obj.* FROM isys_catg_cluster_adm_service_list
		 	INNER JOIN isys_connection AS con2 ON con2.isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
		 	INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_adm_service_list__isys_obj__id
		 	WHERE con2.isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        // Get cluster members
        /*
        $l_res_objtypes = $this->get_obj_type_by_catg(array(C__CATG__VIRTUAL_MACHINE__ROOT));
       $l_allowed_objecttypes = '';
       while($l_row = $l_res_objtypes->get_row())
       {
           $l_allowed_objecttypes .= $l_row['isys_obj_type__id'] . ',';
       }
       $l_allowed_objecttypes = rtrim($l_allowed_objecttypes, ',');

        $l_sql .= ' UNION
        SELECT isys_obj.* FROM isys_catg_cluster_members_list INNER JOIN isys_connection AS con1 ON con1.isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
        INNER JOIN isys_obj ON isys_obj__id = con1.isys_connection__isys_obj__id
        WHERE isys_catg_cluster_members_list__isys_obj__id IN
        (SELECT isys_catg_cluster_adm_service_list__isys_obj__id FROM isys_catg_cluster_adm_service_list
        INNER JOIN isys_connection AS con2 ON con2.isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
        WHERE con2.isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') AND isys_obj__isys_obj_type__id IN (' . $l_allowed_objecttypes . ')';
        */

        $l_res = $this->retrieve($l_sql);
        if ($p_as_array) {
            $l_return = [];
            if ($l_res->num_rows()) {
                while ($l_row = $l_res->get_row()) {
                    if ($p_raw) {
                        $l_return[$l_row['isys_obj__id']] = $l_row;
                    } else {
                        $l_return[] = $l_row['isys_obj__id'];
                    }
                }
            }

            return $l_return;
        } else {
            return $l_res;
        }
    }

    /**
     * Get count method
     *
     * @param int|null $p_obj_id
     *
     * @return bool
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        $l_sql = '(SELECT isys_catg_virtual_host_list__isys_obj__id AS id FROM isys_catg_virtual_host_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_host_list__administration_service
				WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1)';

        $l_sql .= ' UNION
			(SELECT isys_catg_cluster_adm_service_list__isys_obj__id AS id FROM isys_catg_cluster_adm_service_list
		 	INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
		 	WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1)';

        return (bool)$this->retrieve($l_sql)
            ->num_rows();
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_virtual_host_list__administration_service',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(d.title, \' {\', d.reference, \'}\') FROM (
                                (SELECT NULL AS id, isys_connection__isys_obj__id AS objectID,
                                  isys_obj__title AS title, isys_obj__id AS reference
                                  FROM isys_connection
                                  INNER JOIN isys_catg_virtual_host_list ON isys_catg_virtual_host_list__administration_service = isys_connection__id
                                  INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_host_list__isys_obj__id)
                              UNION
                                (SELECT  NULL AS id, isys_connection__isys_obj__id AS objectID,
                                  isys_obj__title AS title, isys_catg_cluster_adm_service_list__isys_obj__id AS reference
                                  FROM isys_catg_cluster_adm_service_list
                                  INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_adm_service_list__isys_obj__id
                                  INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id)
                              ) AS d', 'isys_obj', '', 'd.objectID', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['d.objectID'], 'CONCAT(d.title, \' {\', d.reference, \'}\')'))
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__GROUP__OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATG__VIRTUAL_HOST_ROOT'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ]),
        ];

    }

    /**
     * Helper method which removes the administration service assignment of the virtual host or cluster
     *
     * @param            $p_object_id
     * @param            $p_host_id
     * @param bool|false $p_is_cluster
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function remove_administration_service($p_object_id, $p_host_id, $p_is_cluster = false)
    {
        if ($p_is_cluster) {
            $l_sql = 'SELECT isys_catg_cluster_adm_service_list__id
				FROM isys_catg_cluster_adm_service_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id
				WHERE isys_catg_cluster_adm_service_list__isys_obj__id = ' . $this->convert_sql_id($p_host_id) . '
				AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_object_id);

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows()) {
                $l_data_id = $l_res->get_row_value('isys_catg_cluster_adm_service_list__id');

                return $this->delete_entry($l_data_id, 'isys_catg_cluster_adm_service_list');
            }
        } else {
            $l_sql = 'SELECT isys_catg_virtual_host_list__administration_service FROM isys_catg_virtual_host_list
				WHERE isys_catg_virtual_host_list__isys_obj__id = ' . $this->convert_sql_id($p_host_id);
            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows()) {
                $l_connection_id = $l_res->get_row_value('isys_catg_virtual_host_list__administration_service');
                if ($l_connection_id) {
                    $l_update = 'UPDATE isys_connection SET isys_connection__isys_obj__id = NULL WHERE isys_connection__id = ' . $this->convert_sql_id($l_connection_id);
                    $this->update($l_update);
                }
            }

            return $this->apply_update();
        }
    }

    /**
     * Helper method which adds the administration service assignment to the virtual host or cluster
     *
     * @param            $p_object_id
     * @param            $p_virtual_host
     * @param bool|false $p_is_cluster
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function update_administration_service($p_object_id, $p_connected_host, $p_is_cluster = false)
    {
        if ($p_is_cluster) {
            isys_cmdb_dao_category_g_cluster_adm_service::instance(isys_application::instance()->database)
                ->create($p_connected_host, C__RECORD_STATUS__NORMAL, $p_object_id);
        } else {
            /**
             * @var $l_dao isys_cmdb_dao_category_g_virtual_host
             */
            $l_dao = isys_cmdb_dao_category_g_virtual_host::instance(isys_application::instance()->database);

            $l_sql = 'SELECT * FROM isys_catg_virtual_host_list
				LEFT JOIN isys_connection ON isys_connection__id = isys_catg_virtual_host_list__license_server
				WHERE isys_catg_virtual_host_list__isys_obj__id = ' . $this->convert_sql_id($p_connected_host);
            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows()) {
                $l_row = $l_res->get_row();

                return $l_dao->save($l_row['isys_catg_virtual_host_list__id'], C__RECORD_STATUS__NORMAL, $l_row['isys_catg_virtual_host_list__virtual_host'],
                    $l_row['isys_connection__isys_obj__id'], $p_object_id, $l_row['isys_catg_virtual_host_list__description']);
            } else {
                $l_dao->create($p_connected_host, C__RECORD_STATUS__NORMAL, null, null, $p_object_id, null);

                return true;
            }
        }
    }
}