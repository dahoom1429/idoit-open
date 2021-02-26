<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: specific category for person group members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_group_members extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_group_members';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

    /**
     * Flag which defines if the category is only a list with an object browser.
     *
     * @var  boolean
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'connected_object';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = true;

    /**
     * Category's table.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_cats_person_list';

    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_person_list__id) AS count FROM isys_person_2_group " . "INNER JOIN isys_cats_person_list " . "ON " .
            "isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id " . "INNER JOIN isys_cats_person_group_list " . "ON " .
            "isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($this->m_object_id)) {
            $l_sql .= " AND (isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_data = $this->retrieve($l_sql)
            ->__to_array();

        return $l_data["count"];
    }

    /**
     * @param integer $p_id
     * @param mixed   $p_obj_id
     * @param string  $p_condition
     * @param mixed   $p_filter
     * @param integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT isys_cats_person_list.*, isys_person_2_group.*, isys_cats_person_group_list.*, persongroup.*, " . // Containing person...
            "person.isys_obj__id as `person_id`, person.isys_obj__title as `person_title`, person.isys_obj__sysid as `person_sysid`, person.isys_obj__isys_obj_type__id as `person_type`, " .
            // Containing the email...
            "mail_person.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, " . "mail_pgroup.isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address
            FROM isys_person_2_group 
            INNER JOIN isys_cats_person_list ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id 
            INNER JOIN isys_cats_person_group_list ON isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id 
            INNER JOIN isys_obj persongroup ON persongroup.isys_obj__id = isys_cats_person_group_list__isys_obj__id 
            INNER JOIN isys_obj person ON person.isys_obj__id = isys_cats_person_list__isys_obj__id 
            LEFT JOIN isys_catg_mail_addresses_list AS mail_person ON mail_person.isys_catg_mail_addresses_list__isys_obj__id = person.isys_obj__id AND mail_person.isys_catg_mail_addresses_list__primary = 1 
            LEFT JOIN isys_catg_mail_addresses_list AS mail_pgroup ON mail_pgroup.isys_catg_mail_addresses_list__isys_obj__id = persongroup.isys_obj__id AND mail_pgroup.isys_catg_mail_addresses_list__primary = 1 
            WHERE TRUE " . $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_id)) {
            $l_sql .= " AND (isys_person_2_group__id = '{$p_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_person_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__group ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'first_name'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_FIRST_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'First name'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__first_name'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__FIRST_NAME'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'last_name'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_LAST_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Last name'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__last_name'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__LAST_NAME'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'department'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_DEPARTMENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Department'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__department'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__DEPARTMENT'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'phone_company'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_TELEPHONE_COMPANY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Telephone company'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__phone_company'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__PHONE_COMPANY'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'email_address'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_EMAIL_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'EMail'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__mail_address'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__EMAIL_ADDRESS'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'organization'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_ASSIGNED_ORGANISATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Organisation'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__isys_connection__id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__ORGANIZATION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => ''
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'title'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__title'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__MEMBERS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Person group memberships'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_person_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                              FROM isys_person_2_group
                              INNER JOIN isys_obj ON isys_obj__id = isys_person_2_group__isys_obj__id__person',
                        'isys_person_2_group',
                        'isys_person_2_group__id',
                        'isys_person_2_group__isys_obj__id__group',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_person_2_group__isys_obj__id__group'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_person_2_group', 'LEFT', 'isys_person_2_group__isys_obj__id__group', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_person_2_group__isys_obj__id__person', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PERSON_GROUP_MEMBERS__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__PERSON',
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ])
        ];
    }

    /**
     * @param int    $entryId
     * @param int    $direction
     * @param string $table
     * @param null   $checkMethod
     * @param bool   $purge
     *
     * @return bool
     */
    public function rank_record($entryId, $direction, $table, $checkMethod = null, $purge = false)
    {
        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__QUICK_PURGE || $_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE) {
            $direction = C__CMDB__RANK__DIRECTION_DELETE;
        }

        if ($direction == C__CMDB__RANK__DIRECTION_DELETE) {
            $this->detach_person(null, null, $entryId);
        }

        return true;
    }

    /**
     * @param array  $objects
     * @param int    $direction
     * @param string $table
     * @param null   $checkMethod
     * @param bool   $purge
     *
     * @return bool
     */
    public function rank_records($objects, $direction = C__CMDB__RANK__DIRECTION_DELETE, $table = 'isys_obj', $checkMethod = null, $purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($_POST['id']) && is_array($_POST['id'])) {
                    foreach ($_POST['id'] as $l_val) {
                        $this->detach_person(null, null, $l_val);
                    }

                    unset($_POST['id']);
                }
        }

        return true;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $personObjectId = $p_category_data['properties']['connected_object'][C__DATA__VALUE];
            $personExists = $this->get_data(null, $p_object_id, ' AND person.isys_obj__id = ' . $this->convert_sql_id($personObjectId));

            // @see  ID-4833  We do not check the status. We check, if the given object has already been attached.
            if (!is_countable($personExists) || !count($personExists)) {
                $p_category_data['data_id'] = $this->attach_person($p_object_id, $personObjectId);

                if ($p_category_data['data_id']) {
                    return $p_category_data['data_id'];
                }
            }

            // @see  API-5133  Return a value, if the person is already assigned to the group.
            return $personExists->get_row_value('isys_person_2_group__id') ?: false;
        }

        return false;
    }

    /**
     * Save specific category monitor
     *
     * @param int $p_cat_level        level to save, default 0
     * @param int &$p_intOldRecStatus __status of record before update
     *
     * @return null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

    /**
     * Attach a person to a group.
     *
     * @param   integer $p_group_id
     * @param   integer $p_person_id
     *
     * @return  integer|null
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     */
    public function attach_person($p_group_id, $p_person_id)
    {
        $l_sql = "INSERT INTO isys_person_2_group 
            SET isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_person_id) . ", 
            isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($p_group_id) . ";";

        if ($this->update($l_sql)) {
            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation($this->get_last_insert_id(), "isys_person_2_group", defined_or_default('C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS'), null, $p_group_id, $p_person_id);

            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.beforeUserGroupChanged', $p_person_id, $p_group_id, 'attach-person');

            // @see  API-5133  Return a value, if the person is assigned to the group.
            return $this->get_last_id_from_table('isys_person_2_group');
        }

        return null;
    }

    /**
     * Detaches a person and removes its relation
     *
     * @param integer $p_group_id
     * @param integer $p_person_id
     * @param null    $p_cat_list_id
     *
     * @return bool
     */
    public function detach_person($p_group_id, $p_person_id, $p_cat_list_id = null)
    {
        if ($p_cat_list_id > 0) {
            $l_data = $this->get_data($p_cat_list_id)
                ->get_row();
        } else {
            $l_data = $this->get_data(null, $p_group_id, " AND isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_person_id))
                ->get_row();
        }

        if ($l_data["isys_person_2_group__isys_catg_relation_list__id"] > 0) {
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->delete_relation($l_data["isys_person_2_group__isys_catg_relation_list__id"]);
        }

        $l_sql = 'DELETE FROM isys_person_2_group WHERE TRUE';

        if ($p_person_id) {
            $l_sql .= ' AND isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_person_id);
        }

        if ($p_group_id) {
            $l_sql .= ' AND isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_group_id);
        }

        if (!empty($p_cat_list_id)) {
            $l_sql .= ' AND isys_person_2_group__id = ' . $this->convert_sql_id($p_cat_list_id);
        }

        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUserGroupChanged', $p_person_id, $p_group_id, 'detach-person');

        return $this->update($l_sql . ';') && $this->apply_update();
    }

    /**
     * Updates category data.
     *
     * @param   integer $p_objID   Object identifier
     * @param   array   $p_persons Arrays of integers with person's object identifiers
     *
     * @return  boolean  Success?
     */
    public function save($p_objID, $p_persons)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_objID, $this->get_category_const());

        if (!$l_edit_right) {
            return false;
        }

        $l_data = $this->get_data(null, $p_objID);

        while ($l_row = $l_data->get_row()) {
            $l_current_persons[$l_row["isys_person_2_group__isys_obj__id__person"]] = $l_row["isys_person_2_group__id"];
        }

        if (is_array($p_persons)) {
            foreach ($p_persons as $l_person) {
                if (!isset($l_current_persons[$l_person]) || !$l_current_persons[$l_person]) {
                    $this->attach_person($p_objID, $l_person);
                }
            }
        } elseif (is_scalar($p_persons)) {
            if (!isset($l_current_persons[$p_persons]) || !$l_current_persons[$p_persons]) {
                $this->attach_person($p_objID, $l_current_persons[$p_persons]);
            }
        }

        return $this->apply_update();
    }

    /**
     * @param int   $p_object_id
     * @param array $p_persons
     *
     * @return bool|null
     */
    public function attachObjects($p_object_id, array $p_persons)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_object_id, $this->get_category_const());

        if (!$l_edit_right) {
            return null;
        }

        $l_existing = [];
        $l_save = [];

        // Select all items from the database-table for deleting them.
        $l_res = $this->get_selected_persons($p_object_id);

        // Get the array of ID's from our json-string.
        while ($l_row = $l_res->get_row()) {
            $l_existing[] = $l_row['isys_obj__id'];

            // Delete entries from tables.
            if (!in_array($l_row['isys_obj__id'], $p_persons)) {
                $this->detach_person($p_object_id, $l_row['isys_obj__id']);
            }
        }

        foreach ($p_persons as $l_person) {
            // But don't insert any items, that already exist!
            if (!in_array($l_person, $l_existing)) {
                if ($l_person > 0) {
                    // Collect persons for one single method call.
                    $l_save[] = $l_person;
                }
            }
        }

        if (count($l_save) > 0) {
            return $this->save($p_object_id, $l_save);
        }

        return null;
    }

    /**
     * Get the preselection for the object-browser.
     *
     * @param   integer $p_group_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_selected_persons($p_group_id)
    {
        $l_sql = 'SELECT * FROM isys_person_2_group p2g 
            LEFT JOIN isys_obj AS obj ON p2g.isys_person_2_group__isys_obj__id__person = obj.isys_obj__id 
            WHERE p2g.isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_group_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Entry identifier for logbook entries.
     *
     * @param   array $row
     *
     * @see     API-48 or Zendesk #1655
     * @return  null|string
     */
    public function get_entry_identifier($row)
    {
        try {
            if ($row['isys_person_2_group__isys_obj__id__person'] > 0) {
                return $this->get_obj_name_by_id_as_string($row['isys_person_2_group__isys_obj__id__person']);
            }

            if ($row['isys_person_2_group__isys_obj__id__group'] > 0) {
                return $this->get_obj_name_by_id_as_string($row['isys_person_2_group__isys_obj__id__group']);
            }
        } catch (Exception $e) {
            ; // Do nothing.
        }

        return null;
    }

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.beforeUserGroupChanged', ['isys_cmdb_dao_category_s_person', 'slotBeforeUserGroupChanged']);
    }

    public function getBuiltinPersonsIDs()
    {
        $l_sql = "SELECT isys_obj__id AS id, isys_obj__title, isys_obj__isys_obj_type__id
                    FROM isys_obj
                    inner join isys_obj_type as t
                    on t.isys_obj_type__id = isys_obj.isys_obj__isys_obj_type__id
                    WHERE isys_obj__const IS NOT NULL and t.isys_obj_type__const = 'C__OBJTYPE__PERSON';";
        $result = $this->retrieve($l_sql);
        $ids = [];
        while ($row = $result->get_row()) {
            $ids[] = (int)$row['id'];
        }
        return $ids;
    }

    /**
     * @param $assignmentId
     * @param $contactTagId
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function setPersonGroupMemberRole($assignmentId, $contactTagId)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.contact.beforeSaveTag', $this, $assignmentId, $contactTagId);
        $oldData = $this->get_data($assignmentId)
            ->__to_array();

        $updateQuery = "UPDATE isys_person_2_group " . "SET isys_person_2_group__isys_contact_tag__id = " . $this->convert_sql_id($contactTagId) . " " .
            "WHERE isys_person_2_group__id = " . $this->convert_sql_id($assignmentId) . ";";

        if ($this->update($updateQuery)) {
            $newData = $this->get_data($assignmentId)
                ->__to_array();

            // Get tags
            $tags = isys_factory_cmdb_dialog_dao::get_instance('isys_contact_tag', $this->m_db)
                ->get_data();

            // Build changes array
            $changes = [
                'isys_cmdb_dao_category_s_person_group_members::role' => [
                    'from' => $tags[$oldData['isys_person_2_group__isys_contact_tag__id']]['title'],
                    'to'   => $tags[$contactTagId]['title']
                ]
            ];

            // Create logbook entry
            $logbookDao = new isys_component_dao_logbook($this->m_db);

            $logbookDao->set_entry(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                $this->get_last_query(),
                null,
                defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1),
                $newData['isys_obj__id'],
                $newData['isys_obj__title'],
                $this->get_obj_type_name_by_obj_id($newData['isys_obj__id']),
                'LC__CONTACT__TREE__MEMBERS',
                null,
                serialize($changes),
                isys_application::instance()->container->get('language')
                    ->get('LC__CONTACT__TREE__MEMBER_HAS_BEEN_UPDATED'),
                null
            );

            return $this->apply_update();
        }

        return false;
    }
}
