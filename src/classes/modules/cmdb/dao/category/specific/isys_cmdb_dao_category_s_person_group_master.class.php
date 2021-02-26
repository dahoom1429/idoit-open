<?php

/**
 * i-doit
 *
 * DAO: specific category for masters of person groups.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_group_master extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'person_group_master';

    /**
     * Category's table.
     *
     * @fixme  No standard behavior!
     */
    protected $m_table = 'isys_cats_person_group_list';

    /**
     * UI Template.
     *
     * @var  string
     */
    protected $m_tpl = 'cats__person_group.tpl';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   array   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address
			FROM isys_cats_person_group_list
			INNER JOIN isys_obj ON isys_cats_person_group_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id
			AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_person_group_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_TITLE'
                ]
            ]),
            'email_address' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_EMAIL_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'EMail'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_mail_addresses_list__title  FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' isys_catg_mail_addresses_list__primary = 1'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_mail_addresses_list',
                            'LEFT',
                            'isys_catg_mail_addresses_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_EMAIL_ADDRESS'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'phone'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__GROUP_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Phone'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__phone'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_PHONE'
                ]
            ]),
            'ldap_group'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PERSON_GROUPS__LDAP_MAPPING',
                    C__PROPERTY__INFO__DESCRIPTION => 'LDAP Group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__ldap_group'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CONTACT__GROUP_LDAP'
                ]
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_group_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP_MASTER', 'C__CATS__PERSON_GROUP_MASTER')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            /*
             * @see  ID-5546  Overwrite "properties->title" if "properties->value" is set.
             * This is necessary during duplication/import when the contact assignment category is beeing imported,
             * because in this case "title" will be set to "LC__CMDB__CATG__GLOBAL_CONTACT" and "value" holds the real organization name.
             */
            if (isset($p_category_data['properties']['value'][C__DATA__VALUE]) && !empty($p_category_data['properties']['value'][C__DATA__VALUE])) {
                /*
                 * @see  ID-7171  When creating objects out of templates we'll receive an array here - in this case we empty the "title".
                 * If the title is empty it will be filled a few lines further down.
                 */
                if (is_array($p_category_data['properties']['value'][C__DATA__VALUE])) {
                    $p_category_data['properties']['title'][C__DATA__VALUE] = '';
                } else {
                    $p_category_data['properties']['title'][C__DATA__VALUE] = $p_category_data['properties']['value'][C__DATA__VALUE];
                }
            }

            // @see  ID-5519  Always add the title or else we end up with a unnamed object.
            if (!isset($p_category_data['properties']['title'][C__DATA__VALUE]) || empty($p_category_data['properties']['title'][C__DATA__VALUE])) {
                $p_category_data['properties']['title'][C__DATA__VALUE] = $this->get_obj_name_by_id_as_string($p_object_id);
            }

            // Create category data identifier if needed.
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_person_group_list', $p_object_id);
            } else {
                // This part is needed because the function insert_new_obj automatically creates a new entry into the specific category person group.
                $l_catdata = $this->get_data(null, $p_object_id)->get_row();

                $p_category_data['data_id'] = $l_catdata['isys_cats_person_group_list__id'];
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['email_address'][C__DATA__VALUE],
                    $p_category_data['properties']['phone'][C__DATA__VALUE],
                    $p_category_data['properties']['ldap_group'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category person group master.
     *
     * @param  integer $p_cat_level        level to save, default 0
     * @param  integer &$p_intOldRecStatus __status of record before update
     *
     * @return integer
     * @throws Exception
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_person_group_list__status"];

        $l_list_id = $l_catdata["isys_cats_person_group_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_person_group_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                $_POST["C__CONTACT__GROUP_TITLE"],
                $_POST["C__CONTACT__GROUP_EMAIL_ADDRESS"],
                $_POST["C__CONTACT__GROUP_PHONE"],
                $_POST["C__CONTACT__GROUP_LDAP"],
                (empty($_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . defined_or_default('C__CATS__PERSON_GROUP')]) ? $_POST["C__CMDB__CAT__COMMENTARY_" .
                $this->get_category_type() . defined_or_default('C__CATS__PERSON_GROUP_MASTER')] : $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . defined_or_default('C__CATS__PERSON_GROUP')])
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * @param integer $p_catlevel
     * @param string  $p_title
     * @param string  $p_mail
     * @param string  $p_phone
     * @param string  $p_ldapGroup
     * @param string  $p_description
     * @param integer $p_status
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function save($p_catlevel, $p_title, $p_mail, $p_phone, $p_ldapGroup, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_old_data = $this->get_data($p_catlevel)->get_row();

        $changes = [
            'isys_cats_person_group_list__status' => $p_status,
            'isys_obj__status' => $p_status
        ];

        if ($p_title !== null) {
            $changes['isys_cats_person_group_list__title'] = $this->convert_sql_text($p_title);
            $changes['isys_obj__title'] = $this->convert_sql_text($p_title);
        }
        if ($p_mail !== null) {
            $changes['isys_cats_person_group_list__email_address'] = $this->convert_sql_text($p_mail);
        }
        if ($p_phone !== null) {
            $changes['isys_cats_person_group_list__phone'] = $this->convert_sql_text($p_phone);
        }
        if ($p_ldapGroup !== null) {
            $changes['isys_cats_person_group_list__ldap_group'] = $this->convert_sql_text($p_ldapGroup);
        }
        if ($p_description !== null) {
            $changes['isys_cats_person_group_list__description'] = $this->convert_sql_text($p_description);
        }
        if ($p_status !== null) {
            $changes['isys_cats_person_group_list__status'] = $this->convert_sql_id($p_status);
        }
        $changes = array_map(function ($k, $v) {
            return $k . ' = ' . $v;
        }, array_keys($changes), $changes);

        $l_sql = 'UPDATE isys_cats_person_group_list
            INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_group_list__isys_obj__id
              SET ' . implode(', ', $changes) . '
            WHERE isys_cats_person_group_list__id = ' . $this->convert_sql_id($p_catlevel);

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_dao_mail = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_db);
            if (($l_mail_id = $l_dao_mail->mail_address_exists($l_old_data['isys_cats_person_group_list__isys_obj__id'], $p_mail))) {
                $l_dao_mail->set_primary_mail($l_old_data['isys_cats_person_group_list__isys_obj__id'], $l_mail_id);
            } else {
                if ($p_mail != '') {
                    if ($l_old_data['isys_cats_person_group_list__email_address'] == '') {
                        $l_dao_mail->create($l_old_data['isys_cats_person_group_list__isys_obj__id'], C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    } else {
                        $l_dao_mail->save($l_old_data['isys_catg_mail_addresses_list__id'], C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    }
                } else {
                    $l_dao_mail->delete_primary_mail($l_old_data['isys_cats_person_group_list__isys_obj__id']);
                }
            }

            $l_dao_global = new isys_cmdb_dao_category_g_global($this->m_db);
            $l_dao_global->handle_template_status($l_old_data["isys_obj__status"], $l_old_data["isys_obj__id"]);

            return true;
        }

        return false;
    }

    /**
     * Create method.
     *
     * @param  integer $p_object_id
     * @param  string  $p_title
     * @param  string  $p_mail
     * @param  string  $p_phone
     * @param  string  $p_ldapGroup
     * @param  string  $p_description
     * @param  integer $p_status
     *
     * @return mixed
     * @throws isys_exception_dao
     */
    public function create($p_object_id, $p_title, $p_mail = '', $p_phone = '', $p_ldapGroup = '', $p_description = '', $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "INSERT IGNORE INTO isys_cats_person_group_list 
            SET isys_cats_person_group_list__title = " . $this->convert_sql_text($p_title) . ",
            isys_cats_person_group_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
            isys_cats_person_group_list__email_address = " . $this->convert_sql_text($p_mail) . ",
            isys_cats_person_group_list__phone = " . $this->convert_sql_text($p_phone) . ",
            isys_cats_person_group_list__ldap_group = " . $this->convert_sql_text($p_ldapGroup) . ",
            isys_cats_person_group_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_cats_person_group_list__status = " . $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $l_dao_mail = isys_cmdb_dao_category_g_mail_addresses::instance($this->m_db);

                if (($l_mail_id = $l_dao_mail->mail_address_exists($p_object_id, $p_mail))) {
                    $l_dao_mail->set_primary_mail($p_object_id, $l_mail_id);
                } else {
                    if (!empty($p_mail)) {
                        $l_dao_mail->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_mail, 1);
                    }
                }

                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Returns the group title by a given ID.
     *
     * @param  integer $p_id
     *
     * @return string
     * @throws isys_exception_database
     */
    public function get_group_title_by_id($p_id)
    {
        $l_sql = 'SELECT isys_cats_person_group_list__title FROM isys_obj
			LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_obj__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->retrieve($l_sql)->get_row_value('isys_cats_person_group_list__title');
    }

    /**
     * Get person-data by group ID.
     *
     * @param  integer $p_group_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_persons_by_id($p_group_id)
    {
        $l_sql = 'SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			LEFT JOIN isys_person_2_group ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE
			AND isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_group_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for simply updating the person group title.
     *
     * @param   integer $p_person_group_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     * @throws  isys_exception_dao
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_person_group_title($p_person_group_object_id, $p_title)
    {
        $l_sql = 'UPDATE isys_cats_person_group_list
			SET isys_cats_person_group_list__title = ' . $this->convert_sql_text($p_title) . '
			WHERE isys_cats_person_group_list__isys_obj__id = ' . $this->convert_sql_id($p_person_group_object_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * @param        $p_ldapGroupSID
     * @param string $p_ldapGroupName
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function isGroupWithLdapSIDExist($p_ldapGroupSID, $p_ldapGroupName = '')
    {
        $sqlOR = '';
        if ($p_ldapGroupName !== '') {
            $sqlOR = " OR LCASE(isys_cats_person_group_list__ldap_group) = " . $this->convert_sql_text(strtolower($p_ldapGroupName)) . " ";
        }
        $sql = "select count(isys_cats_person_group_list__id) as c 
            from isys_cats_person_group_list
            where isys_cats_person_group_list__ldap_group = " . $this->convert_sql_text($p_ldapGroupSID) .
            $sqlOR .
            " limit 1";
        return ($this->retrieve($sql)->get_row_value('c') > 0);
    }

    /**
     * @param        $p_ldapGroupSID
     * @param string $p_ldapGroupName
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getGroupIDByLdapSyncID($p_ldapGroupSID, $p_ldapGroupName = '')
    {
        $sqlOR = '';
        if ($p_ldapGroupName !== '') {
            $sqlOR = " OR LCASE(isys_cats_person_group_list__ldap_group) = " . $this->convert_sql_text(strtolower($p_ldapGroupName)) . " ";
        }

        $sql = "select isys_cats_person_group_list__isys_obj__id as id 
            from isys_cats_person_group_list
            where isys_cats_person_group_list__ldap_group = " . $this->convert_sql_text($p_ldapGroupSID) .
            $sqlOR .
            " limit 1";
        return $this->retrieve($sql)->get_row_value('id');
    }
}
