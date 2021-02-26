<?php

use idoit\Component\Property\Configuration\PropertyDependency;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogDataCaseProperty;
use idoit\Component\Property\Type\DialogPlusCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

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
class isys_cmdb_dao_category_g_assigned_subscriptions extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_subscriptions';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_subscription';

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
    protected $m_object_id_field = 'isys_catg_assigned_subscriptions_list__isys_obj__id';

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
			WHERE isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . '
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

        if (isys_format_json::is_json_array($_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'])) {
            $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'] =
                isys_format_json::decode($_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN']);
        }

        if (is_array($_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'])) {
            $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'] =
                current($_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN']);
        }

        if ($p_create) {
            // Overview page and no input was given
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_SUBSCRIPTIONS', 'C__CATG__ASSIGNED_SUBSCRIPTIONS')]
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
            $p_intOldRecStatus = $l_catdata["isys_catg_cloud_subscriptions_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_assigned_subscriptions_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_SUBSCRIPTIONS', 'C__CATG__ASSIGNED_SUBSCRIPTIONS')]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        if ($p_create) {
            if (defined('C__CATG__OVERVIEW') && $_GET[C__CMDB__GET__CATG] == C__CATG__OVERVIEW && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                return $l_catdata["isys_catg_cloud_subscriptions_list__id"];
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

        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection->update_connection($l_old_data["isys_catg_assigned_subscriptions_list__isys_connection__id"], $p_assignedObjectId);

        $sql_setUpdateFields = 'isys_catg_assigned_subscriptions_list__status = ' . $this->convert_sql_int($p_newRecStatus) . ' ';
        $sql_setUpdateFields .= ', isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_cloud_subscriptionsObjectId) . ' ' .
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
        $l_sql = 'SELECT *, isys_obj__id as connectedObjectId, isys_obj__title as connectedObjectTitle FROM isys_catg_assigned_subscriptions_list
            INNER JOIN isys_connection on isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id
            INNER JOIN isys_obj on isys_obj__id = isys_connection__isys_obj__id
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
                return ' AND (isys_catg_assigned_subscriptions_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
            'assigned_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'The cloud subscription enabled object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_assigned_subscriptions_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_assigned_subscriptions',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_assigned_subscriptions']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                            INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                            INNER JOIN isys_catg_assigned_subscriptions_list ON isys_catg_assigned_subscriptions_list__isys_connection__id = isys_connection__id',
                        'isys_catg_assigned_subscriptions_list',
                        '',
                        'isys_catg_assigned_subscriptions_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_assigned_subscriptions_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_assigned_subscriptions_list',
                            'LEFT',
                            'isys_catg_assigned_subscriptions_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_assigned_subscriptions_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATG__ASSIGNED_USERS'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'uuid'         => (new DialogPlusCategoryDependencyProperty(
                'C__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID',
                'LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID',
                'isys_catg_assigned_subscriptions_list__cloud_subscr__id',
                'isys_catg_assigned_subscriptions_list',
                'isys_catg_cloud_subscriptions_list',
                'assigned_object',
                new isys_callback([
                    'isys_cmdb_dao_category_g_assigned_subscriptions',
                    'callback_property_uuid'
                ]),
                [
                    'isys_global_assigned_subscriptions_export_helper',
                    'assignedSubscriptionsUuid'
                ],
                'isys_catg_cloud_subscriptions_list__uuid'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__INDEX => true,
                Property::C__PROPERTY__DATA__REFERENCES => [
                    'isys_catg_cloud_subscriptions_list',
                    'isys_catg_cloud_subscriptions_list__id',
                    'isys_catg_cloud_subscriptions_list__uuid'
                ]
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__LIST => true,
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => true,
                Property::C__PROPERTY__PROVIDES__FILTERABLE => true
            ])->mergePropertyDependency([
                Property::C__PROPERTY__DEPENDENCY__SELECT => SelectSubSelect::factory(
                    'SELECT *, isys_catg_cloud_subscriptions_list__uuid as isys_catg_cloud_subscriptions_list__title FROM isys_catg_cloud_subscriptions_list',
                    'isys_catg_cloud_subscriptions_list',
                    'isys_catg_cloud_subscriptions_list__id',
                    'isys_catg_cloud_subscriptions_list__isys_obj__id'
                )
            ]),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_SUBSCRIPTIONS', 'C__CATG__ASSIGNED_SUBSCRIPTIONS'),
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
        $objectId = $p_request->get_object_id();
        $categoryId = (int)$p_request->get_category_data_id();
        $l_hosts_res = null;
        $db = isys_application::instance()->container->get('database');

        if ($categoryId > 0) {
            $dao = isys_cmdb_dao_category_g_assigned_subscriptions::instance($db);
            $query = 'SELECT isys_catg_cloud_subscriptions_list__id, isys_catg_cloud_subscriptions_list__uuid 
                FROM isys_catg_cloud_subscriptions_list
                INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_catg_cloud_subscriptions_list__isys_obj__id
                INNER JOIN isys_catg_assigned_subscriptions_list ON isys_catg_assigned_subscriptions_list__isys_connection__id = isys_connection__id
                WHERE isys_catg_assigned_subscriptions_list__id = ' . $dao->convert_sql_id($categoryId);

            $l_hosts_res = $dao->retrieve($query);
        } elseif (!empty($objectId)) {
            $l_hosts_res = isys_cmdb_dao_category_g_cloud_subscriptions::instance($db)
                ->get_data(null, $objectId);
        } else {
            return [];
        }


        $l_hosts = [];

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
                  isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_cloud_subscriptionsObjectId) . ' 
			    , isys_catg_assigned_subscriptions_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
                , isys_catg_assigned_subscriptions_list__isys_connection__id = ' . $this->convert_sql_id($l_connection->add_connection($p_assignedSubscriptionObjectId)) . ' 
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
            where isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($objectId));
        $l_old_data = $l_old_dataResult->__to_array();
        foreach ($l_old_data as $row) {
            $l_connection->delete($row["isys_catg_assigned_subscriptions_list__isys_connection__id"]);
        }

        $l_sql = 'DELETE FROM isys_catg_assigned_subscriptions_list WHERE isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($objectId);

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * @param $personObjID
     * @param $licenseObjID
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getCategoryIdByPersonAndLicence($personObjID, $licenseObjID)
    {
        $l_sql = 'SELECT cat.isys_catg_assigned_subscriptions_list__id AS id
                    FROM isys_catg_assigned_subscriptions_list AS cat
                    INNER JOIN isys_connection AS conn 
                        ON conn.isys_connection__id = cat.isys_catg_assigned_subscriptions_list__isys_connection__id
                    WHERE (cat.isys_catg_assigned_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($personObjID) . ') 
                    AND (conn.isys_connection__isys_obj__id = ' . $this->convert_sql_int($licenseObjID) . ') LIMIT 1;';
        return $this->retrieve($l_sql)->get_row_value('id');
    }
}
