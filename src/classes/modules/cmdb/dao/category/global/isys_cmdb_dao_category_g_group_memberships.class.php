<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;

/**
 * i-doit
 *
 * DAO: global category for group memberships.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_group_memberships extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'group_memberships';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__GROUP_MEMBERSHIPS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_cats_group_list__isys_obj__id';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

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
     * Field for the object id
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  Breaks with developer guidelines!
     */
    protected $m_table = 'isys_cats_group_list';


    /**
     * Executes the query to create the category entry referenced by isys_catg_backup__id $p_fk_id
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param String $p_title
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_connectedObjID = null, $p_description = null)
    {
        // Preparation
        $selection = $p_connectedObjID;

        // Check whether data was delivered as json
        if (isys_format_json::is_json_array($selection)) {
            $selection = isys_format_json::decode($selection);
        }

        // Cast selection to array
        $selection = (array)$selection;

        // 1. Get all groups that already assigned to object
        $resource = $this->getAssignedGroups($p_objID);
        $assignedGroups = [];

        if ($resource->num_rows() > 0) {
            // Get assignment
            while ($assignment = $resource->get_row()) {
                // Store group id
                $assignedGroups[] = $assignment['groupId'];
            }
        }

        // 2. Calculate unassigned groups
        $unassignedGroups = array_diff($selection, $assignedGroups);

        // 3. Create assignments if there are any left

        // Check whether there are unassigned groups left
        if (!empty($unassignedGroups)) {
            $connectionDao = new isys_cmdb_dao_connection($this->get_database_component());
            $relationDao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

            foreach ($unassignedGroups as $groupId) {
                // Prepare insert statement
                $sql = "INSERT INTO isys_cats_group_list SET " .
                    "isys_cats_group_list__isys_connection__id = " . $this->convert_sql_id($connectionDao->add_connection($p_objID)) . ", " .
                    "isys_cats_group_list__description = " . $this->convert_sql_text($p_description) . ", " .
                    "isys_cats_group_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
                    "isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($groupId);

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    // Create relation object
                    $relationDao->handle_relation($this->get_last_insert_id(), "isys_cats_group_list", defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'), null, $p_objID, $groupId);
                }
            }
        }

        return true;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param int    $p_connectionID
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID = null, $p_description = null)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_strSql = "UPDATE isys_cats_group_list SET " . "isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ", " .
            "isys_cats_group_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_cats_group_list__status = " .
            $this->convert_sql_id($p_newRecStatus) . " " . "WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql)) {
            if ($this->apply_update()) {
                $l_data = $this->get_data($p_cat_level)
                    ->__to_array();
                $l_dao_relation->handle_relation(
                    $p_cat_level,
                    "isys_cats_group_list",
                    defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                    $l_data["isys_cats_group_list__isys_catg_relation_list__id"],
                    $l_data["isys_connection__isys_obj__id"],
                    $p_connectedObjID
                );

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get assigned groups
     *
     * @param $objectId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getAssignedGroups($objectId)
    {
        // Create sql for retrieving assigned groups
        $sql = 'SELECT gl.isys_cats_group_list__isys_obj__id AS groupId FROM isys_cats_group_list gl
            INNER JOIN isys_connection con ON con.isys_connection__id = gl.isys_cats_group_list__isys_connection__id 
            WHERE con.isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

        // Create resource by querying assignments
        return $this->retrieve($sql);
    }

    /**
     * Set Status for category entry
     *
     * @param int $p_cat_id
     * @param int $p_status
     *
     * @return bool
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = "UPDATE isys_cats_group_list SET isys_cats_group_list__status = " . $this->convert_sql_id($p_status) . " " . "WHERE isys_cats_group_list__id = " .
            $this->convert_sql_id($p_cat_id);
        if ($this->update($l_sql) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes connection between group and object
     *
     * @param int $p_cat_level
     * @param int $p_objtype_id
     *
     * @return bool
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @version Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function delete($p_cat_level, $p_objtype_id = null)
    {
        if ($p_objtype_id === null && defined('C__OBJECT_TYPE__GROUP')) {
            $p_objtype_id = C__OBJECT_TYPE__GROUP;
        }
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_catdata = $this->get_data($p_cat_level)
            ->__to_array();

        if ($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"] > 0) {
            $l_dao_relation->delete_relation($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"]);
        } else {
            $l_sql = "DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);
            $this->update($l_sql);
        }

        if ($this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not delete id '{$p_cat_level}' in table isys_catg_application_list.");
        }
    }

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_group_list__id) AS count FROM isys_cats_group_list " .
            "LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_cats_group_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * Return Category Data
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_group_list " . "INNER JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id " .
            "LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_cats_group_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_cats_group_list__status = " . $this->convert_sql_id($p_status) . ") ";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'connected_object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATG__GROUP__OBJECT',
                'LC__CMDB__CATG__GLOBAL_GROUP',
                'isys_cats_group_list__isys_obj__id',
                'isys_cats_group_list',
                '',
                []
            ))->setPropertyDataOffset(
                \idoit\Component\Property\Property::C__PROPERTY__DATA__INDEX,
                true
            )->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__GROUP;C__CATS__GROUP_TYPE',
                isys_popup_browser_object_ng::C__MULTISELECTION => true
            ])->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_group_memberships',
                    'callback_property_relation_handler'
                ], ['isys_cmdb_dao_category_g_group_memberships'])
            ),
            // deprecated has to be removed in the next version
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_group_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__GROUP_MEMBERSHIPS', 'C__CATG__GROUP_MEMBERSHIPS')
                ],
                // @See ID-5991 in comment from 16.10.2018 14:10
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                    C__PROPERTY__PROVIDES__IMPORT       => false,
                    C__PROPERTY__PROVIDES__EXPORT       => false,
                    C__PROPERTY__PROVIDES__REPORT       => false,
                    C__PROPERTY__PROVIDES__LIST         => false,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__VIRTUAL      => false
                ],
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_object'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Attach $objects to $object_id
     *
     * @param int   $object_id
     * @param array $objects
     *
     * @return int last inserted id
     */
    public function attachObjects($object_id, array $objects)
    {
        // 1. Get all groups that already assigned to object
        $resource = $this->getAssignedGroups($object_id);
        $assignedGroups = [];

        if ($resource->num_rows() > 0) {
            // Get assignment
            while ($assignment = $resource->get_row()) {
                // Store group id
                $assignedGroups[] = $assignment['groupId'];
            }
        }

        // 2. Calculate unassigned groups
        $missingGroups = array_diff($objects, $assignedGroups);

        $connectionDao = new isys_cmdb_dao_connection($this->get_database_component());
        $relationDao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_logbook_dao = new isys_component_dao_logbook($this->get_database_component());

        // Check whether there are unassigned groups left
        if (!empty($missingGroups)) {
            foreach ($missingGroups as $groupId) {
                // Prepare insert statement
                $sql = "INSERT INTO isys_cats_group_list SET
                    isys_cats_group_list__isys_connection__id = " . $this->convert_sql_id($connectionDao->add_connection($object_id)) . ",
                    isys_cats_group_list__status = " . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . ",
                    isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($groupId);

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    $entryId = $this->get_last_insert_id();
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                            $sql,
                            $object_id,
                            $this->get_object_type_id(),
                            $this->getCategoryTitle(),
                            serialize([
                                    'isys_cmdb_dao_category_g_group_memberships::group' => [
                                        'from' => '',
                                        'to'   => $this->get_obj_name_by_id_as_string($groupId),
                                    ],
                                ]),
                            null,
                            isys_application::instance()->container->get('language')
                                ->get('LC__DEPENDENCY__CREATED')
                        );

                    // Create relation object
                    $relationDao->handle_relation($entryId, "isys_cats_group_list", defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'), null, $object_id, $groupId);
                }
            }
        }

        $obsoleteGroups = array_diff($assignedGroups, $objects);

        if (!empty($obsoleteGroups)) {
            foreach ($obsoleteGroups as $groupId) {
                $entryData = $this->get_data(null, $object_id, ' AND isys_cats_group_list__isys_obj__id = ' . $this->convert_sql_id($groupId))->get_row();

                if (empty($entryData['isys_cats_group_list__id'])) {
                    continue;
                }

                $sql = 'DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = ' . $this->convert_sql_id($entryData['isys_cats_group_list__id']);

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__CATEGORY_DELETED',
                            $this->get_last_query(),
                            $object_id,
                            $this->get_objTypeID($object_id),
                            $this->getCategoryTitle(),
                            serialize([
                                    'isys_cmdb_dao_category_g_group_memberships::group' => [
                                        'from' => $this->get_obj_name_by_id_as_string($groupId),
                                        'to'   => '',
                                    ],
                                ]),
                            isys_application::instance()->container->get('language')
                                ->get('LC__DEPENDENCY__DELETED')
                        );

                    $relationDao->delete_relation($entryData['isys_cats_group_list__isys_catg_relation_list__id']);
                }
            }

            return true;
        }
    }
}
