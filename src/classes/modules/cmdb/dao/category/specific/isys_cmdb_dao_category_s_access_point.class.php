<?php
use idoit\Component\Property\Type\DialogYesNoProperty;

/**
 * i-doit
 *
 * DAO: specific category for access points.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_access_point extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'access_point';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *
			FROM isys_cats_access_point_list
			LEFT JOIN isys_obj ON isys_cats_access_point_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_wlan_encryption ON isys_wlan_encryption__id = isys_cats_access_point_list__isys_wlan_encryption__id
			LEFT JOIN isys_wlan_function ON isys_wlan_function__id = isys_cats_access_point_list__isys_wlan_function__id
			LEFT JOIN isys_wlan_channel ON isys_wlan_channel__id = isys_cats_access_point_list__isys_wlan_channel__id
			LEFT JOIN isys_wlan_standard ON isys_wlan_standard__id = isys_cats_access_point_list__isys_wlan_standard__id
			LEFT JOIN isys_wlan_auth ON isys_wlan_auth__id = isys_cats_access_point_list__isys_wlan_auth__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_access_point_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_access_point_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method for returning the properties.
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_access_point_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_access_point_list__title FROM isys_cats_access_point_list',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__ACCESS_POINT_TITLE'
                ]
            ]),
            'function'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_FUNCTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Function'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_access_point_list__isys_wlan_function__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wlan_function',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wlan_function',
                        'isys_wlan_function__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wlan_function__title
                            FROM isys_cats_access_point_list
                            INNER JOIN isys_wlan_function ON isys_wlan_function__id = isys_cats_access_point_list__isys_wlan_function__id',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_access_point_list',
                            'LEFT',
                            'isys_cats_access_point_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wlan_function',
                            'LEFT',
                            'isys_cats_access_point_list__isys_wlan_function__id',
                            'isys_wlan_function__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ACCESS_POINT_FUNCTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_wlan_function'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'standard'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_STANDARD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Standard'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_access_point_list__isys_wlan_standard__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wlan_standard',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wlan_standard',
                        'isys_wlan_standard__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wlan_standard__title
                            FROM isys_cats_access_point_list
                            INNER JOIN isys_wlan_standard ON isys_wlan_standard__id = isys_cats_access_point_list__isys_wlan_standard__id',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_access_point_list',
                            'LEFT',
                            'isys_cats_access_point_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wlan_standard',
                            'LEFT',
                            'isys_cats_access_point_list__isys_wlan_standard__id',
                            'isys_wlan_standard__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ACCESS_POINT_STANDARD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_wlan_standard'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'channel'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_CHANNEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Channel'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_access_point_list__isys_wlan_channel__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wlan_channel',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wlan_channel',
                        'isys_wlan_channel__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wlan_channel__title
                            FROM isys_cats_access_point_list
                            INNER JOIN isys_wlan_channel ON isys_wlan_channel__id = isys_cats_access_point_list__isys_wlan_channel__id',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_access_point_list',
                            'LEFT',
                            'isys_cats_access_point_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wlan_channel',
                            'LEFT',
                            'isys_cats_access_point_list__isys_wlan_channel__id',
                            'isys_wlan_channel__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ACCESS_POINT_CHANNEL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_wlan_channel'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'broadcast_ssid' => (new DialogYesNoProperty(
                'C__CATS__ACCESS_POINT_BRODCAST_SSID',
                'LC__CMDB__CATS__ACCESS_POINT_BRODCAST_SSID',
                'isys_cats_access_point_list__broadcast_ssid',
                'isys_cats_access_point_list'
            )),
            'ssid'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_SSID',
                    C__PROPERTY__INFO__DESCRIPTION => 'SSID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_access_point_list__ssid',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_access_point_list__ssid FROM isys_cats_access_point_list',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__ACCESS_POINT_SSID'
                ]
            ]),
            'mac_filter' => (new DialogYesNoProperty(
                'C__CATS__ACCESS_POINT_MAC_FILTER',
                'LC__CMDB__CATS__ACCESS_POINT_MAC_FILTER',
                'isys_cats_access_point_list__mac_filter',
                'isys_cats_access_point_list'
            )),
            'auth'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_AUTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Authentification'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_access_point_list__isys_wlan_auth__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wlan_auth',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wlan_auth',
                        'isys_wlan_auth__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wlan_auth__title
                            FROM isys_cats_access_point_list
                            INNER JOIN isys_wlan_auth ON isys_wlan_auth__id = isys_cats_access_point_list__isys_wlan_auth__id',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_access_point_list',
                            'LEFT',
                            'isys_cats_access_point_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wlan_auth',
                            'LEFT',
                            'isys_cats_access_point_list__isys_wlan_auth__id',
                            'isys_wlan_auth__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ACCESS_POINT_AUTH',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_wlan_auth'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'encryption_id'  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ACCESS_POINT_ENCRYPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Encryption'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_cats_access_point_list__encryption',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_wlan_encryption',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_wlan_encryption',
                        'isys_wlan_encryption__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_wlan_encryption__title
                            FROM isys_cats_access_point_list
                            INNER JOIN isys_wlan_encryption ON isys_wlan_encryption__id = isys_cats_access_point_list__encryption',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_access_point_list',
                            'LEFT',
                            'isys_cats_access_point_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_wlan_encryption',
                            'LEFT',
                            'isys_cats_access_point_list__isys_wlan_encryption__id',
                            'isys_wlan_encryption__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__ACCESS_POINT_ENCYPTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_wlan_encryption'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'key'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__REGEDIT__KEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Key'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_access_point_list__key',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_access_point_list__key FROM isys_cats_access_point_list',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__ACCESS_POINT_KEY'
                ]
            ]),
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_access_point_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_access_point_list__description FROM isys_cats_access_point_list',
                        'isys_cats_access_point_list',
                        'isys_cats_access_point_list__id',
                        'isys_cats_access_point_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_access_point_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ACCESS_POINT', 'C__CATS__ACCESS_POINT')
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
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_access_point_list', $p_object_id, 'isys_cats_distributor');
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['ssid'][C__DATA__VALUE],
                    $p_category_data['properties']['key'][C__DATA__VALUE],
                    $p_category_data['properties']['encryption_id'][C__DATA__VALUE],
                    $p_category_data['properties']['broadcast_ssid'][C__DATA__VALUE],
                    $p_category_data['properties']['mac_filter'][C__DATA__VALUE],
                    $p_category_data['properties']['function'][C__DATA__VALUE],
                    $p_category_data['properties']['channel'][C__DATA__VALUE],
                    $p_category_data['properties']['standard'][C__DATA__VALUE],
                    $p_category_data['properties']['auth'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * @param  integer $p_cat_level
     * @param  integer &$p_intOldRecStatus
     *
     * @return  mixed
     * @author  Dennis Stuecken
     * @author  Van Quyen Hoang
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_access_point_list__status"];

        $l_list_id = $l_catdata["isys_cats_access_point_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_access_point_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $l_bRet = $this->save(
            $l_list_id,
            $_POST['C__CATS__ACCESS_POINT_TITLE'],
            $_POST["C__CATS__ACCESS_POINT_SSID"],
            $_POST['C__CATS__ACCESS_POINT_KEY'],
            $_POST["C__CATS__ACCESS_POINT_ENCYPTION"],
            $_POST["C__CATS__ACCESS_POINT_BRODCAST_SSID"],
            $_POST["C__CATS__ACCESS_POINT_MAC_FILTER"],
            $_POST['C__CATS__ACCESS_POINT_FUNCTION'],
            $_POST['C__CATS__ACCESS_POINT_CHANNEL'],
            $_POST['C__CATS__ACCESS_POINT_STANDARD'],
            $_POST["C__CATS__ACCESS_POINT_AUTH"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            C__RECORD_STATUS__NORMAL
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_ssid
     * @param   string  $p_key
     * @param   string  $p_encryption
     * @param   string  $p_broadcast_ssid
     * @param   string  $p_mac_filter
     * @param   integer $p_wlan_function_id
     * @param   integer $p_wlan_channel_id
     * @param   integer $p_wlan_standard_id
     * @param   integer $p_wlan_auth_id
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save(
        $p_id,
        $p_title,
        $p_ssid,
        $p_key,
        $p_encryption,
        $p_broadcast_ssid,
        $p_mac_filter,
        $p_wlan_function_id,
        $p_wlan_channel_id,
        $p_wlan_standard_id,
        $p_wlan_auth_id,
        $p_description,
        $p_status
    ) {
        $l_sql = "UPDATE isys_cats_access_point_list
			SET isys_cats_access_point_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_cats_access_point_list__ssid = " . $this->convert_sql_text($p_ssid) . ",
			isys_cats_access_point_list__key = " . $this->convert_sql_text($p_key) . ",
			isys_cats_access_point_list__encryption = " . $this->convert_sql_text($p_encryption) . ",
			isys_cats_access_point_list__broadcast_ssid = " . $this->convert_sql_text($p_broadcast_ssid) . ",
			isys_cats_access_point_list__mac_filter = " . $this->convert_sql_text($p_mac_filter) . ",
			isys_cats_access_point_list__isys_wlan_encryption__id = " . $this->convert_sql_id($p_encryption) . ",
			isys_cats_access_point_list__isys_wlan_function__id = " . $this->convert_sql_id($p_wlan_function_id) . ",
			isys_cats_access_point_list__isys_wlan_channel__id = " . $this->convert_sql_id($p_wlan_channel_id) . ",
			isys_cats_access_point_list__isys_wlan_standard__id = " . $this->convert_sql_id($p_wlan_standard_id) . ",
			isys_cats_access_point_list__isys_wlan_auth__id = " . $this->convert_sql_id($p_wlan_auth_id) . ",
			isys_cats_access_point_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_access_point_list__status = " . $this->convert_sql_int($p_status) . "
			WHERE isys_cats_access_point_list__id = " . $this->convert_sql_id($p_id) . ";";

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_key
     * @param   string  $p_title
     * @param   integer $p_standardID
     * @param   integer $p_functionID
     * @param   integer $p_channelID
     * @param   integer $p_encID
     * @param   integer $p_broadcastSSID
     * @param   integer $p_ssID
     * @param   integer $p_macFilterID
     * @param   integer $p_authID
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_key,
        $p_title,
        $p_standardID,
        $p_functionID,
        $p_channelID,
        $p_encID,
        $p_broadcastSSID,
        $p_ssID,
        $p_macFilterID,
        $p_authID,
        $p_description
    ) {
        $l_strSql = "INSERT INTO isys_cats_access_point_list
			SET isys_cats_access_point_list__key  = " . $this->convert_sql_text($p_key) . ",
			isys_cats_access_point_list__title  = " . $this->convert_sql_text($p_title) . ",
			isys_cats_access_point_list__isys_wlan_standard__id  = " . $this->convert_sql_id($p_standardID) . ",
			isys_cats_access_point_list__isys_wlan_function__id  = " . $this->convert_sql_id($p_functionID) . ",
			isys_cats_access_point_list__isys_wlan_channel__id  = " . $this->convert_sql_id($p_channelID) . ",
			isys_cats_access_point_list__encryption = " . $this->convert_sql_id($p_encID) . ",
			isys_cats_access_point_list__broadcast_ssid = " . $this->convert_sql_id($p_broadcastSSID) . ",
			isys_cats_access_point_list__ssid = " . $this->convert_sql_text($p_ssID) . ",
			isys_cats_access_point_list__mac_filter = " . $this->convert_sql_id($p_macFilterID) . ",
			isys_cats_access_point_list__isys_wlan_auth__id = " . $this->convert_sql_id($p_authID) . ",
			isys_cats_access_point_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_access_point_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_cats_access_point_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }
}
