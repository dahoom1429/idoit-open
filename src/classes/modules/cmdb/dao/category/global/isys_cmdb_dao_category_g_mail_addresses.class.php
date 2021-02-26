<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for e-mail addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_mail_addresses extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'mail_addresses';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic callback for the primary email address.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_primary_email($p_row)
    {
        $l_email = $this->get_primary_mail_as_string_by_obj_id($p_row['isys_catg_mail_addresses_list__isys_obj__id']);

        if ($l_email && filter_var($l_email, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . $l_email . '">' . $l_email . '</a>';
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Overview always creates an empty email address if create_connector is not overridden
     *
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_primary_email' => new DynamicProperty(
                'LC__CMDB__CATG__MAIL_ADDRESSES__PRIMARY_EMAIL',
                'isys_catg_mail_addresses_list__isys_obj__id',
                'isys_catg_mail_addresses_list',
                [
                    $this,
                    'dynamic_property_callback_primary_email'
                ]
            )
        ];
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
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Email address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_mail_addresses_list__title
                                FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_mail_addresses_list__isys_obj__id'])
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
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__MAIL_ADDRESSES__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'primary_mail' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MAIL_ADDRESSES__PRIMARY_EMAIL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary email address'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_mail_addresses_list__title
                                FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_mail_addresses_list__primary = 1']),
                        null,
                        '',
                        1
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
                        C__PROPERTY__UI__ID => 'C__CMDB__CATG__MAIL_ADDRESSES__TITLE'
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__LIST   => true,
                        C__PROPERTY__PROVIDES__REPORT => false,
                        C__PROPERTY__PROVIDES__IMPORT => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT => false
                    ]
                ]),
            'primary'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_LIST__PRIMARY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__primary',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_mail_addresses_list__primary = \'1\' THEN \'LC__UNIVERSAL__YES\'
                                    WHEN isys_catg_mail_addresses_list__primary = \'0\' THEN \'LC__UNIVERSAL__NO\' END)
                                FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_mail_addresses_list__isys_obj__id'])
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
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__MAIL_ADDRESSES__PRIMARY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_mail_addresses_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_mail_addresses_list__description FROM isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list',
                        'isys_catg_mail_addresses_list__id',
                        'isys_catg_mail_addresses_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_mail_addresses_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__MAIL_ADDRESSES', 'C__CATG__MAIL_ADDRESSES')
                ]
            ])
        ];
    }

    /**
     * Sync method for import, export and duplicating.
     *
     * @param   integer $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
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
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
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
        $l_intErrorCode = -1; // ErrorCode

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_mail_addresses_list__status"];

        if ($_POST['C__CMDB__CATG__MAIL_ADDRESSES__TITLE'] != '') {
            if ($p_create) {
                if (!$this->mail_address_exists($_GET[C__CMDB__GET__OBJECT], $_POST['C__CMDB__CATG__MAIL_ADDRESSES__TITLE'])) {
                    $l_id = $this->create(
                        $_GET[C__CMDB__GET__OBJECT],
                        C__RECORD_STATUS__NORMAL,
                        $_POST['C__CMDB__CATG__MAIL_ADDRESSES__TITLE'],
                        $_POST['C__CMDB__CATG__MAIL_ADDRESSES__PRIMARY'],
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                    );

                    if ($l_id != false) {
                        $this->m_strLogbookSQL = $this->get_last_query();
                    }

                    $p_cat_level = null;

                    return $l_id;
                }
            } else {
                if ($l_catdata['isys_catg_mail_addresses_list__id'] != "") {
                    $l_bRet = $this->save(
                        $l_catdata['isys_catg_mail_addresses_list__id'],
                        C__RECORD_STATUS__NORMAL,
                        $_POST['C__CMDB__CATG__MAIL_ADDRESSES__TITLE'],
                        $_POST['C__CMDB__CATG__MAIL_ADDRESSES__PRIMARY'],
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                    );

                    $this->m_strLogbookSQL = $this->get_last_query();

                    return $l_bRet == true ? null : $l_intErrorCode;
                }

                return $l_intErrorCode;
            }
        }
    }

    /**
     * Creates a new entry for the category
     *
     * @param   int     $p_obj_id
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
        $l_id = false;

        $l_update = 'INSERT INTO isys_catg_mail_addresses_list SET
			isys_catg_mail_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
			isys_catg_mail_addresses_list__status = ' . $this->convert_sql_int($p_status) . ',
			isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text($p_title) . ',
			isys_catg_mail_addresses_list__primary = ' . $this->convert_sql_int($p_primary) . ',
			isys_catg_mail_addresses_list__description = ' . $this->convert_sql_text($p_description) . ';';

        if ($this->update($l_update) && $this->apply_update()) {
            $l_id = $this->get_last_insert_id();

            if ($p_primary > 0) {
                $this->set_primary_mail($p_obj_id, $l_id);
            }
        }

        return $l_id;
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
        if (is_array($p_status)) {
            $l_tmp = $p_status;
            $p_status = $l_tmp['status'];
            $p_title = $l_tmp['title'];
            $p_primary = $l_tmp['primary'];
            $p_description = $l_tmp['description'];

            if (empty($p_primary)) {
                $p_primary = 0;
            }
        }

        $l_data = $this->get_data_by_id($p_id)
            ->__to_array();

        $l_update = 'UPDATE isys_catg_mail_addresses_list SET
			isys_catg_mail_addresses_list__status = ' . $this->convert_sql_int($p_status) . ',
			isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text($p_title) . ',
			isys_catg_mail_addresses_list__primary = ' . $this->convert_sql_int($p_primary) . ',
			isys_catg_mail_addresses_list__description = ' . $this->convert_sql_text($p_description) . '
			WHERE isys_catg_mail_addresses_list__id = ' . $this->convert_sql_id($p_id) . ';';

        if ($this->update($l_update) && $this->apply_update()) {
            if ($p_primary > 0) {
                $this->set_primary_mail($l_data['isys_catg_mail_addresses_list__isys_obj__id'], $p_id);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the given mail address exists.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_mail_address
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function mail_address_exists($p_obj_id, $p_mail_address)
    {
        $l_sql = 'SELECT isys_catg_mail_addresses_list__id
			FROM isys_catg_mail_addresses_list
			WHERE isys_catg_mail_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_mail_addresses_list__title = ' . $this->convert_sql_text($p_mail_address) . ';';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_mail_addresses_list__id') ?: false;
    }

    /**
     * Sets primary mail address.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_primary_mail($p_obj_id, $p_cat_id)
    {
        if ($this->unset_primary_mail($p_obj_id)) {
            $l_update = 'UPDATE isys_catg_mail_addresses_list
				SET isys_catg_mail_addresses_list__primary = 1
				WHERE isys_catg_mail_addresses_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

            return ($this->update($l_update) && $this->apply_update());
        }

        return true;
    }

    /**
     * Deletes the primary email address from the category table.
     *
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function delete_primary_mail($p_obj_id)
    {
        $l_sql = 'DELETE FROM isys_catg_mail_addresses_list
			WHERE isys_catg_mail_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_mail_addresses_list__primary = 1;';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Retrieves the primary mail address as string.
     *
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_primary_mail_as_string_by_obj_id($p_obj_id)
    {
        $l_sql = 'SELECT isys_catg_mail_addresses_list__title
			FROM isys_catg_mail_addresses_list
			WHERE isys_catg_mail_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_mail_addresses_list__primary = 1;';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_mail_addresses_list__title') ?: false;
    }

    /**
     * Updates the mail address field for person or person group.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_table
     * @param   string  $p_mail_address
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function update_contact_mail($p_obj_id, $p_table, $p_mail_address)
    {
        $l_update_field = ($p_table == 'isys_cats_person_list') ? 'isys_cats_person_list__mail_address' : 'isys_cats_person_group_list__email_address';

        $l_update = 'UPDATE ' . $p_table . ' SET ' . $l_update_field . ' = ' . $this->convert_sql_text($p_mail_address) . ' WHERE ' . $p_table . '__isys_obj__id = ' .
            $this->convert_sql_id($p_obj_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Sets the primary field in all category entries for the given object id with 0.
     *
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function unset_primary_mail($p_obj_id)
    {
        $l_sql = 'UPDATE isys_catg_mail_addresses_list
			SET isys_catg_mail_addresses_list__primary = 0
			WHERE isys_catg_mail_addresses_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @param     $p_email
     * @param int $p_status
     *
     * @return bool|mixed|null
     * @throws isys_exception_database
     */
    public function getObjectIDByEmail($p_email, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "SELECT cat.isys_catg_mail_addresses_list__isys_obj__id AS id 
                    FROM isys_catg_mail_addresses_list AS cat 
                    INNER JOIN isys_obj AS obj ON obj.isys_obj__id = cat.isys_catg_mail_addresses_list__isys_obj__id
                    WHERE (isys_catg_mail_addresses_list__title = " . $this->convert_sql_text($p_email) . ")
                    AND (isys_catg_mail_addresses_list__status = " . $this->convert_sql_int($p_status) . ")
                    AND (obj.isys_obj__status = " . $this->convert_sql_int($p_status) . ")
                    LIMIT 1;";

        return $this->retrieve($l_sql)->get_row_value('id') ?: false;
    }
}
