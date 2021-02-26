<?php

use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Context\Context;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: specific category for persons with assigned groups
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_assigned_groups extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_assigned_groups';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_person_2_group__isys_obj__id__group';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

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
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_person_2_group__isys_obj__id__person';

    /**
     * The category's table.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_cats_person_group_list';

    /**
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Callback function for dataretrieval for UI.
     *
     * @param   isys_request $p_request
     *
     * @return  isys_component_dao_result
     */
    public function callback_property_connected_object(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        return $this->get_data(null, $l_obj_id);
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  null
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        return null;
    }

    /**
     * Method for attaching a new group.
     *
     * @param   integer $p_person_id
     * @param   integer $p_group_id
     *
     * @return  boolean
     */
    public function attach_group($p_person_id, $p_group_id)
    {
        $l_sql = "INSERT INTO isys_person_2_group
            SET isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_person_id) . ",
            isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($p_group_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_dao->handle_relation($this->get_last_insert_id(), "isys_person_2_group", defined_or_default('C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS'), null, $p_group_id, $p_person_id);

            // delete auth cache of the person
            isys_caching::factory('auth-' . $p_person_id)
                ->clear();

            return $l_last_id;
        } else {
            return false;
        }
    }

    /**
     * Save method.
     *
     * @param   integer $p_objID
     * @param   array   $p_groups
     *
     * @return  boolean
     */
    public function save($p_objID, $p_groups)
    {
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_objID, $this->get_category_const());

        if (!$l_edit_right) {
            return false;
        }

        $l_current_groups = [];
        $l_data = $this->get_data(null, $p_objID);

        while ($l_row = $l_data->get_row()) {
            if (!empty($l_row)) {
                $l_current_groups[$l_row["isys_person_2_group__isys_obj__id__group"]] = $l_row["isys_person_2_group__id"];
            }
        }

        if (is_countable($p_groups) && count($p_groups) > 0) {
            foreach ($p_groups as $l_group) {
                if (!$l_current_groups[$l_group] && !empty($l_group)) {
                    $this->attach_group($p_objID, $l_group);
                }
            }
        }

        return $this->apply_update();
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return bool|int|null
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_edit_right = isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $p_object_id, $this->get_category_const());

        $logbookDescription = '';

        if (Context::instance()->getContextCustomer() === Context::CONTEXT_LDAP_SYNC) {
            $logbookDescription = 'Dependency created from ldap-sync';
        }

        if (!$l_edit_right) {
            return 0;
        }

        $l_existing = [];
        $l_save = [];
        $language = isys_application::instance()->container->get('language');
        // Removed: isys_rs_system
        $daoRelation = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->database);

        // Select all items from the database-table for deleting them.
        $l_res = $this->get_assigned_contacts($p_object_id);

        $l_auth_obj = isys_auth_cmdb::instance();
        $l_not_allowed_objects = [];
        $l_update = "DELETE FROM isys_person_2_group WHERE FALSE";

        while ($l_row = $l_res->get_row()) {
            $l_existing[] = $l_row['isys_obj__id'];

            // Collect only items, which are not to be saved.
            if (!in_array($l_row['isys_obj__id'], $p_objects)) {
                if ($l_auth_obj->is_allowed_to(isys_auth::EDIT, 'OBJ_ID/' . $l_row['isys_obj__id'])) {
                    $l_update .= " OR isys_person_2_group__id = " . $this->convert_sql_id($l_row['isys_person_2_group__id']);

                    $l_relation_id = $l_row['isys_person_2_group__isys_catg_relation_list__id'];

                    if ($l_relation_id > 0) {
                        $daoRelation->delete_relation($l_relation_id);
                    }
                } else {
                    $l_not_allowed_objects[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
                }
            }
        }
        $this->update($l_update);

        // Now insert new items.
        foreach ($p_objects as $l_object) {
            // But don't insert any items, that already exist!
            if (!in_array($l_object, $l_existing)) {
                if ($l_object > 0) {
                    // Create the new items.
                    if ($l_auth_obj->is_allowed_to(isys_auth::EDIT, 'OBJ_ID/' . $l_object)) {
                        $l_save[] = $l_object;
                    } elseif (!array_key_exists($l_object, $l_not_allowed_objects)) {
                        $l_not_allowed_objects[$l_object] = $this->get_obj_name_by_id_as_string($l_object);
                    }
                }
            }

            if (count($l_save) > 0) {
                $l_id = $this->save($p_object_id, $l_save);
                isys_event_manager::getInstance()
                    ->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        $logbookDescription,
                        $p_object_id,
                        $this->get_objTypeID($p_object_id),
                        $language->get('LC__CONTACT__TREE__GROUP_MEMBERS'),
                        serialize([
                            'isys_cmdb_dao_category_s_person_assigned_groups::connected_object' => [
                                'from' => '',
                                'to'   => $this->get_obj_name_by_id_as_string($l_object),
                            ],
                        ]),
                        $language->get('LC__DEPENDENCY__CREATED')
                    );
            }
            if (count($l_not_allowed_objects) > 0) {
                $l_message = 'Error: No rights to modify person groups (';
                foreach ($l_not_allowed_objects as $l_obj_id => $l_obj_name) {
                    $l_message .= $l_obj_name . ', ';
                }
                $l_message = rtrim($l_message, ', ') . ').';
                isys_component_template_infobox::instance()
                    ->set_message($l_message, 1, null, null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 4));
            }
        }

        // delete auth cache of the person
        isys_caching::factory('auth-' . $p_object_id)
            ->clear();

        return $l_id;
    }

    /**
     * This method gets the assigned contacts by an object-id for the contact-browser.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_assigned_contacts($p_obj_id)
    {
        // Prepare SQL statement for retrieving contacts, assigned to a certain object.
        $l_sql = 'SELECT p2g.isys_person_2_group__id, obj.isys_obj__id, obj.isys_obj__title, obj.isys_obj__isys_obj_type__id, obj.isys_obj__sysid, p2g.isys_person_2_group__isys_catg_relation_list__id ' .
            'FROM isys_person_2_group AS p2g ' . 'LEFT JOIN isys_obj AS obj ON obj.isys_obj__id = p2g.isys_person_2_group__isys_obj__id__group ' .
            'WHERE p2g.isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_obj_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_person_list__id) AS count FROM isys_person_2_group " .
            "INNER JOIN isys_cats_person_list ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id " .
            "INNER JOIN isys_cats_person_group_list ON isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($this->m_object_id)) {
            $l_sql .= "AND isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($l_obj_id);
        }

        return $this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_data($p_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *, mail_person.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, ' .
            'mail_pgroup.isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address ' . 'FROM isys_person_2_group ' .
            'INNER JOIN isys_cats_person_list ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id ' .
            'INNER JOIN isys_cats_person_group_list ON isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id ' .
            'LEFT JOIN isys_catg_mail_addresses_list AS mail_person ON mail_person.isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND mail_person.isys_catg_mail_addresses_list__primary = 1 ' .
            'LEFT JOIN isys_catg_mail_addresses_list AS mail_pgroup ON mail_pgroup.isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_group_list__isys_obj__id AND mail_pgroup.isys_catg_mail_addresses_list__primary = 1 ' .
            'WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_id !== null) {
            $l_sql .= ' AND isys_person_2_group__id = ' . $this->convert_sql_id($p_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        return $this->retrieve($l_sql . ' GROUP BY isys_cats_person_group_list__id;');
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
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__person ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__TREE__GROUP_MEMBERS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Person group memberships'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_cats_person_group_list__isys_obj__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__PERSON_ASSIGNED_GROUPS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_s_person_assigned_groups',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_s_person_assigned_groups',
                        true
                    ]),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                         FROM isys_person_2_group
                         INNER JOIN isys_obj ON isys_obj__id = isys_person_2_group__isys_obj__id__group',
                        'isys_person_2_group',
                        '',
                        'isys_person_2_group__isys_obj__id__person',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_person_2_group__isys_obj__id__person'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_person_2_group',
                            'LEFT',
                            'isys_person_2_group__isys_obj__id__person',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_person_group_list',
                            'LEFT',
                            'isys_person_2_group__isys_obj__id__group',
                            'isys_cats_person_group_list__isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__PERSON_ASSIGNED_GROUPS__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATS__PERSON_GROUP_MASTER;C__CATS__PERSON_GROUP",
                        isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                        isys_popup_browser_object_ng::C__DATARETRIEVAL  => new isys_callback([
                            'isys_cmdb_dao_category_s_person_assigned_groups',
                            'callback_property_connected_object'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'role' => new DialogPlusProperty(
                'C__CONTACT__ORGANISATION_ROLE',
                'LC__CONTACT__PERSON_ROLE',
                'isys_person_2_group__isys_contact_tag__id',
                'isys_person_2_group',
                'isys_contact_tag'
            ),
            'contact'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CONTACT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Contact'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_person_2_group__isys_obj__id__group'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'person_property_contact'
                    ]
                ]
            ])
        ];
    }

    /**
     * @param  int    $categoryEntryId
     * @param  int    $direction
     * @param  string $table
     * @param  null   $checkMethod
     * @param  bool   $purge
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function rank_record($categoryEntryId, $direction, $table, $checkMethod = null, $purge = false)
    {
        if ($direction == C__CMDB__RANK__DIRECTION_RECYCLE) {
            return true;
        }

        if ($categoryEntryId && isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $this->get_category_const())) {
            $sql = 'DELETE FROM isys_person_2_group WHERE isys_person_2_group__id = ' . $this->convert_sql_id($categoryEntryId) . ';';

            $relationId = $this->get_data($categoryEntryId)->get_row_value('isys_person_2_group__isys_catg_relation_list__id');

            if ($relationId > 0) {
                isys_cmdb_dao_category_g_relation::instance($this->get_database_component())->delete_relation($relationId);
            }

            return ($this->update($sql) && $this->apply_update());
        }

        return true;
    }

    /**
     *
     * @param   array   $p_objects
     * @param   integer $p_direction
     * @param   string  $p_table
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        if ($p_direction == C__CMDB__RANK__DIRECTION_RECYCLE) {
            return true;
        }

        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_update = "DELETE FROM isys_person_2_group WHERE FALSE";

        if (is_array($p_objects)) {
            foreach ($p_objects as $l_id) {
                $l_edit_right = isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $_GET[C__CMDB__GET__OBJECT], $this->get_category_const());

                if (!$l_edit_right) {
                    continue;
                }

                $l_update .= " OR isys_person_2_group__id = " . $this->convert_sql_id($l_id);

                $l_relation_id = $this->get_data($l_id)
                    ->get_row_value('isys_person_2_group__isys_catg_relation_list__id');

                if ($l_relation_id > 0) {
                    $l_relation_dao->delete_relation($l_relation_id);
                }
            }
        }

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @todo: Implement removing of associations while syncing by mass change
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed.
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $p_category_data['data_id'] = $this->attach_group($p_object_id, $p_category_data['properties']['connected_object'][C__DATA__VALUE]);

                if ($p_category_data['data_id'] > 0) {
                    $l_indicator = true;
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    public function getAssignedGroupsIDs($p_person_id)
    {
        $l_sql = 'SELECT p2g.isys_person_2_group__isys_obj__id__group AS group_id ' .
            'FROM isys_person_2_group AS p2g ' .
            'WHERE p2g.isys_person_2_group__isys_obj__id__person = ' . $this->convert_sql_int($p_person_id) . ';';
        $l_res = $this->retrieve($l_sql);
        $l_existing = [];
        while ($l_row = $l_res->get_row()) {
            $l_existing[] = (int)$l_row['group_id'];
        }
        return $l_existing;
    }
}
