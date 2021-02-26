<?php
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogDataCaseProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Component\Property\Type\DialogPlusCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogCategoryDependencyProperty;

use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;

/**
 * i-doit
 *
 * DAO: global category for cloud_subscriptionss.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_users extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_users';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ASSIGNED_USERS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_assigned_subscriptions_list__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_object';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_table = 'isys_catg_assigned_subscriptions_list';

    /** IP_DONE
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_subscriptions($p_objID)
    {
        $l_sql = 'SELECT * FROM isys_catg_assigned_subscriptions_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id
			INNER JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
			ORDER BY isys_obj__isys_obj_type__id;';

        return $this->retrieve($l_sql);
    }

    /** IP_DONE
     * Save global category cloud_subscriptions element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @return  int|null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
        }

        if (isys_format_json::is_json_array($_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'])) {
            $_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'] = isys_format_json::decode($_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN']);
        }

        if (is_array($_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'])) {
            $_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'] = current($_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN']);
        }

        if ($p_create) {
            // Overview page and no input was given
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CATG__ASSIGNED_USERS__UUID'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_USERS', 'C__CATG__ASSIGNED_USERS')]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_id) {
                $l_catdata['isys_catg_application_list__id'] = $l_id;
                $l_bRet = true;
                $p_cat_level = null;
            } else {
                throw new isys_exception_dao("Could not create category element application");
            }

            return $l_id;
        } else {
            $l_catdata = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_catg_assigned_subscriptions_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_assigned_subscriptions_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CATG__ASSIGNED_USERS__UUID'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_USERS', 'C__CATG__ASSIGNED_USERS')]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        if ($p_create) {
            if (defined('C__CATG__OVERVIEW') && $_GET[C__CMDB__GET__CATG] == C__CATG__OVERVIEW && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                return $l_catdata["isys_catg_assigned_subscriptions_list__id"];
            }
        }

        return ($l_bRet == true) ? null : $l_intErrorCode;
    }

    /**
     * @param $p_cat_level
     * @param $p_newRecStatus
     * @param $p_cloud_subscriptionsObjectId
     * @param $p_assignedObjectId
     * @param $p_uuid
     * @param $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_cloud_subscriptionsObjectId,
        $p_assignedObjectId,
        $p_uuid,
        $p_description
    ) {
        $l_old_data = $this->get_data($p_cat_level)
            ->get_row();

        $sql_setUpdateFields = 'isys_catg_assigned_subscriptions_list__status = ' . $this->convert_sql_int($p_newRecStatus) . ' ';
        $sql_setUpdateFields .= ', isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_assignedObjectId) . ' ' .
            ', isys_catg_assigned_subscriptions_list__cloud_subscr__id = ' . $this->convert_sql_int($p_uuid) . ' ' .
            ', isys_catg_assigned_subscriptions_list__description = ' . $this->convert_sql_text($p_description) . ' ';

        // Update subscriptions assignment
        $l_strSql = "UPDATE isys_catg_assigned_subscriptions_list 
            SET " . $sql_setUpdateFields . "
            WHERE isys_catg_assigned_subscriptions_list__id = " .
            $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /** IP_DONE
     * Return Category Data - Note: Cannot use generic method because of the second left join.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *, isys_obj__id as connectedObjectId, isys_obj__title as connectedObjectTitle 
            FROM isys_catg_assigned_subscriptions_list
            INNER JOIN isys_connection on isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id
            INNER JOIN isys_obj on isys_obj__id = isys_catg_assigned_subscriptions_list__isys_obj__id
			WHERE TRUE ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= ' ' . $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_assigned_subscriptions_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_assigned_subscriptions_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ' ' . $p_condition . ';');
    }

    /** IP_DONE
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }
    }

    /** STATUS!!! Dialog
     * Method for returning the properties.
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'assigned_object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT',
                'LC__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT',
                'isys_catg_assigned_subscriptions_list__isys_obj__id',
                'isys_catg_assigned_subscriptions_list',
                '',
                [],
                'C__CATG__ASSIGNED_SUBSCRIPTIONS'
            ))->mergePropertyUiParams([
                \isys_popup_browser_object_ng::C__MULTISELECTION => false
            ])->setPropertyCheck([
                Property::C__PROPERTY__CHECK__MANDATORY => true
            ]),
            'uuid' => (new DialogProperty(
                'C__CMDB__CATG__ASSIGNED_USERS__UUID',
                'LC__CMDB__CATG__ASSIGNED_USERS__UUID',
                'isys_catg_assigned_subscriptions_list__cloud_subscr__id',
                'isys_catg_assigned_subscriptions_list',
                'isys_catg_cloud_subscriptions_list',
                false,
                [
                    'isys_global_assigned_subscriptions_export_helper',
                    'assignedUsersSubscriptionsUuid'
                ],
                'isys_catg_cloud_subscriptions_list__uuid'
            ))->mergePropertyUiParams(
                [
                    'p_arData' => new isys_callback([
                        'isys_cmdb_dao_category_g_assigned_users',
                        'callback_property_uuid'
                    ]),
                    'p_strTable' => null
                ]
            )->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_cloud_subscriptions_list__uuid
                            FROM isys_catg_assigned_subscriptions_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id
                            INNER JOIN isys_catg_cloud_subscriptions_list ON isys_catg_cloud_subscriptions_list__id = isys_catg_assigned_subscriptions_list__cloud_subscr__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_assigned_subscriptions_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_assigned_subscriptions_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_cloud_subscriptions_list',
                        'LEFT',
                        'isys_catg_assigned_subscriptions_list__cloud_subscr__id',
                        'isys_catg_cloud_subscriptions_list__id'
                    )
                ]
            ]),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_USERS', 'C__CATG__ASSIGNED_USERS'),
                'isys_catg_assigned_subscriptions_list__description',
                'isys_catg_assigned_subscriptions_list'
            )
        ];
    }

    /**
     * Callback method for property uuid.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     */
    public function callback_property_uuid(isys_request $p_request)
    {
        global $g_comp_database;

        $l_hosts = [];
        $l_hosts_res = isys_cmdb_dao_category_g_cloud_subscriptions::instance($g_comp_database)
            ->get_data(null, $p_request->get_object_id());

        if (is_countable($l_hosts_res) && count($l_hosts_res)) {
            while ($l_row = $l_hosts_res->get_row()) {
                $l_hosts[$l_row['isys_catg_cloud_subscriptions_list__id']] = $l_row['isys_catg_cloud_subscriptions_list__uuid'];
            }
        }

        return $l_hosts;
    }

    /** IP_DONE
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['assigned_object'][C__DATA__VALUE],
                            $p_category_data['properties']['uuid'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_object_id,
                            $p_category_data['properties']['assigned_object'][C__DATA__VALUE],
                            $p_category_data['properties']['uuid'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
            }
        }

        return false;
    }

    /**
     * @param $p_cloud_subscriptionsObjectId
     * @param $p_newRecStatus
     * @param $p_assignedSubscriptionObjectId
     * @param $p_uuid
     * @param $p_description
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function create(
        $p_cloud_subscriptionsObjectId,
        $p_newRecStatus,
        $p_assignedSubscriptionObjectId,
        $p_uuid,
        $p_description
    ) {
        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);

        $l_sql = 'INSERT INTO isys_catg_assigned_subscriptions_list SET
                  isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_assignedSubscriptionObjectId) . ' 
			    , isys_catg_assigned_subscriptions_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
                , isys_catg_assigned_subscriptions_list__isys_connection__id = ' . $this->convert_sql_id($l_connection->add_connection($p_cloud_subscriptionsObjectId)) . ' 
                , isys_catg_assigned_subscriptions_list__cloud_subscr__id = ' . $this->convert_sql_id($p_uuid) . ' 
                , isys_catg_assigned_subscriptions_list__description = ' . $this->convert_sql_text($p_description) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            return $l_last_id;
        } else {
            return false;
        }
    }

    /** IP_DONE
     * Set Status for category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = 'UPDATE isys_catg_assigned_subscriptions_list SET isys_catg_assigned_subscriptions_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_catg_assigned_subscriptions_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Deletes connection between cloud_subscriptions and object.
     *
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function delete($p_cat_level)
    {
        $l_old_data = $this->get_data($p_cat_level)
            ->get_row();
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection->delete($l_old_data["isys_catg_assigned_subscriptions_list__isys_connection__id"]);

        $this->update('DELETE FROM isys_catg_assigned_subscriptions_list WHERE isys_catg_assigned_subscriptions_list__id = ' . $this->convert_sql_id($p_cat_level) . ';');

        if ($this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not delete id '" . $p_cat_level . "' in table isys_catg_assigned_subscriptions_list.");
        }
    }

    /**
     * @param int    $objectId
     * @param string $categoryTable
     * @param bool   $hasRelation
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function clear_data($objectId, $categoryTable, $hasRelation = true)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        $l_old_dataResult = $this->retrieve('select * from isys_catg_assigned_subscriptions_list 
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id
            where isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($objectId));
        $l_old_data = $l_old_dataResult->__to_array();
        $deleteIds = [];
        foreach ($l_old_data as $row) {
            $l_connection->delete($row['isys_catg_assigned_subscriptions_list__isys_connection__id']);
            $deleteIds[] = $row['isys_catg_assigned_subscriptions_list'];
        }

        if (!empty($deleteIds)) {
            $l_sql = 'DELETE FROM isys_catg_assigned_subscriptions_list WHERE isys_catg_assigned_subscriptions_list__id IN (' . implode(',', $deleteIds) . ');';
        }

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * @param $licenseObjID
     * @param $personObjID
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getCategoryIdByLicenceAndPerson($licenseObjID, $personObjID)
    {
        $l_sql = 'SELECT cat.isys_catg_assigned_subscriptions_list__id AS id
                    FROM isys_catg_assigned_subscriptions_list AS cat
                    INNER JOIN isys_connection AS conn 
                        ON conn.isys_connection__id = cat.isys_catg_assigned_subscriptions_list__isys_connection__id
                    WHERE (cat.isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($personObjID) . ') 
                    AND (conn.isys_connection__isys_obj__id = ' . $this->convert_sql_int($licenseObjID) . ') LIMIT 1;';
        return $this->retrieve($l_sql)->get_row_value('id');
    }

    /**
     * @param int $objectId
     *
     * @return bool|int
     * @throws isys_exception_database
     */
    public function get_count($objectId = null)
    {
        $table = 'isys_catg_assigned_subscriptions_list';

        if ($objectId === null || $objectId <= 0) {
            $objectId = $this->m_object_id;
        }


        if ($table && $objectId > 0) {
            $sql = 'SELECT COUNT(' . $table . '__id) as count
				FROM ' . $table . '
				INNER JOIN isys_connection ON isys_connection__id = ' . $table . '__isys_connection__id
				WHERE ' . $table . '__status ' . $this->prepare_in_condition([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__TEMPLATE]) . '
				AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            return (int) $this->retrieve($sql)->get_row_value('count');
        }

        return false;
    }
}
