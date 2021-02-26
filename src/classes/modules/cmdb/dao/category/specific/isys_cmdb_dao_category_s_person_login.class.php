<?php

/**
 * i-doit
 *
 * DAO: specific category for person logins
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_login extends isys_cmdb_dao_category_s_person
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var   string
     */
    protected $m_category = 'person_login';

    /**
     * Category's talbe.
     *
     * @var     string
     * @fixme   No standard behavior!
     */
    protected $m_table = 'isys_cats_person_list';

    /**
     * Method for saving new user-login data.
     *
     * @param   integer $p_id
     * @param   string  $p_username
     * @param   string  $p_pass
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   boolean $p_generate_md5
     * @param   integer $disabledLogin
     *
     * @return  boolean
     */
    public function save($p_id, $p_username, $p_pass, $p_description, $p_status = C__RECORD_STATUS__NORMAL, $p_generate_md5 = true, $disabledLogin = 0)
    {
        return parent::save_login($p_id, $p_username, $p_pass, $p_description, $p_status, $p_generate_md5, $disabledLogin);
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_person_list__status"];

        $l_list_id = $l_catdata["isys_cats_person_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_person_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                $_POST["C__CONTACT__PERSON_USER_NAME"],
                $_POST["C__CONTACT__PERSON_PASSWORD"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                true,
                (int) $_POST['C__CONTACT__PERSON__DISABLED_LOGIN']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return ($l_bRet == true) ? $l_list_id : -1;
    }

    /**
     * Method for simply changing a persons password.
     *
     * @param   integer $p_cat_id
     * @param   string  $p_password
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function change_password($p_cat_id, $p_password)
    {
        $l_sql = 'UPDATE isys_cats_person_list
			SET isys_cats_person_list__user_pass = MD5(' . $this->convert_sql_text($p_password) . ')
			WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @return array
     */
    public function custom_properties()
    {
        return [];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'disabled_login' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_LOGIN_DISABLED',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CONTACT__PERSON_LOGIN_DISABLED'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_person_list__disabled_login',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_person_list__disabled_login = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . ' WHEN isys_cats_person_list__disabled_login = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_cats_person_list',
                        'isys_cats_person_list',
                        'isys_cats_person_list__id',
                        'isys_cats_person_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_person_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_person_list', 'LEFT', 'isys_cats_person_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON__DISABLED_LOGIN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_USER_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'User name'
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__title'
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_USER_NAME',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ]
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__UNIQUE_GLOBAL => true
                ]
            ]),
            'user_pass'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_PASSWORD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Password'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__user_pass'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_PASSWORD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__EXPORT => false
                ]
            ]),
            'user_pass2'  => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_PASSWORD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Password'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_PASSWORD_SECOND',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ],
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_LOGIN', 'C__CATS__PERSON_LOGIN')
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
            $l_data = $this->get_data_by_object($p_object_id);
            if ($p_status === isys_import_handler_cmdb::C__CREATE && $l_data->num_rows() == 0) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_person_list', $p_object_id);
            } else {
                $l_data = $l_data->__to_array();
                $p_category_data['data_id'] = $l_data['isys_cats_person_list__id'];
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['user_pass'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL,
                    true,
                    $p_category_data['properties']['disabled_login'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @param   array $p_data
     * @param   mixed $p_prepend_table_field
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_return = [];

        $l_password_minlength = (int)isys_tenantsettings::get('minlength.login.password', 4);

        // @todo  Please observe if this works correctly for all situations (API, GUI, Import, ...)
        if (isset($p_data['user_pass'], $p_data['user_pass2']) && $p_data['user_pass'] != $p_data['user_pass2']) {
            $l_return['user_pass'] = $l_return['user_pass2'] = isys_application::instance()->container->get('language')
                ->get("LC__LOGIN__PASSWORDS_DONT_MATCH");
        }

        if (isset($p_data['user_pass']) && mb_strlen($p_data['user_pass']) < $l_password_minlength && $p_data['user_pass'] != '') {
            $l_return['user_pass'] = isys_application::instance()->container->get('language')
                ->get("LC__LOGIN__SAVE_ERROR", $l_password_minlength);
        }

        if (isset($p_data['user_pass2']) && mb_strlen($p_data['user_pass2']) < $l_password_minlength && $p_data['user_pass2'] != '') {
            $l_return['user_pass2'] = isys_application::instance()->container->get('language')
                ->get("LC__LOGIN__SAVE_ERROR", $l_password_minlength);
        }

        if (count($l_return)) {
            return $l_return;
        }

        return parent::validate($p_data);
    }
}
