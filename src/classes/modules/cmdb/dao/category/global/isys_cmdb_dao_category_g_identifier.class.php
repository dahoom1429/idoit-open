<?php

/**
 * i-doit
 *
 * DAO: global category for custom identifiers
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @author        Selcuk Kekec <skekec@i-doit.com>
 * @author        Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_identifier extends isys_cmdb_dao_category_global
{
    /**
     * Member variable for identifier key
     *
     * @var null
     */
    private static $m_identifier_key = null;

    /**
     * Member variable for identifier type
     *
     * @var null
     */
    private static $m_identifier_type = null;

    /**
     * Cache all objects which have no entry in the identifier category
     *
     * @var null
     */
    private static $m_missing_identifier = [];

    /**
     * @var array
     */
    private static $m_objects_cache = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'identifier';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__IDENTIFIER';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'type';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Setter for member variable $m_identifier_type
     *
     * @param $p_value
     */
    public static function set_identifier_type($p_value)
    {
        self::$m_identifier_type = $p_value;
    }

    /**
     * Setter for member variable $m_identifier_key
     *
     * @param $p_value
     */
    public static function set_identifier_key($p_value)
    {
        self::$m_identifier_key = $p_value;
    }

    public static function get_identifier_key()
    {
        return self::$m_identifier_key;
    }

    public static function get_identifier_type()
    {
        return self::$m_identifier_type;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author      Selcuk Kekec <skekec@i-doit.com>
     * @author      Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'key'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__KEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Key'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_identifier_list__key',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__key FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__KEY'
                ]
            ]),
            'value'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__VALUE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Value'
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_identifier_list__value',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__value FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__VALUE'
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__MANDATORY => false
                ]
            ]),
            'last_edited'  => array_replace_recursive(isys_cmdb_dao_category_pattern::datetime(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_EDITED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Value',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD    => 'isys_catg_identifier_list__datetime',
                    C__PROPERTY__DATA__READONLY => true,
                    C__PROPERTY__DATA__SELECT   => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__datetime FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_EDITED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => true,
                        'p_strStyle'  => 'width:70%;',
                        'p_strClass'  => 'input-small'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__CHECK    => false
            ]),
            'type'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Type'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_identifier_list__isys_catg_identifier_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_catg_identifier_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_identifier_type',
                        'isys_catg_identifier_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_type__title
                            FROM isys_catg_identifier_list
                            INNER JOIN isys_catg_identifier_type ON isys_catg_identifier_type__id = isys_catg_identifier_list__isys_catg_identifier_type__id',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_identifier_list',
                            'LEFT',
                            'isys_catg_identifier_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_identifier_type',
                            'LEFT',
                            'isys_catg_identifier_list__isys_catg_identifier_type__id',
                            'isys_catg_identifier_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_identifier_type'
                    ]
                ]
            ]),
            'group'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_identifier_list__group',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__group FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__IDENTIFIER__GROUP'
                ]
            ]),
            'last_scan'    => array_replace_recursive(isys_cmdb_dao_category_pattern::datetime(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_SCAN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Last scan',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD    => 'isys_catg_identifier_list__last_scan',
                    C__PROPERTY__DATA__READONLY => true,
                    C__PROPERTY__DATA__SELECT   => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__last_scan FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_SCAN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => true,
                        'p_strStyle'  => 'width:70%;',
                        'p_strClass'  => 'input-small'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__CHECK    => false
            ]),
            'last_updated' => array_replace_recursive(isys_cmdb_dao_category_pattern::datetime(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IDENTIFIER__LAST_UPDATED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Last updated',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD    => 'isys_catg_identifier_list__last_updated',
                    C__PROPERTY__DATA__READONLY => true,
                    C__PROPERTY__DATA__SELECT   => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__last_updated FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__IDENTIFIER__LAST_UPDATED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => true,
                        'p_strStyle'  => 'width:70%;',
                        'p_strClass'  => 'input-small'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ],
                C__PROPERTY__CHECK    => false
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_identifier_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_identifier_list__description FROM isys_catg_identifier_list',
                        'isys_catg_identifier_list',
                        'isys_catg_identifier_list__id',
                        'isys_catg_identifier_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_identifier_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__IDENTIFIER', 'C__CATG__IDENTIFIER')
                ]
            ])
        ];
    }

    /**
     * Get the object id by identifier
     *
     * @param $p_device_id
     *
     * @return bool|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_object_id_by_identifer($p_key)
    {
        return (self::$m_objects_cache[$p_key] ?: false);
    }

    /**
     * Add identifier to the object cache
     *
     * @param $p_object_id
     * @param $p_device_id
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function set_object_id_by_identifier($p_object_id, $p_key)
    {
        self::$m_objects_cache[$p_key] = $p_object_id;
    }

    /**
     * @param $p_key
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function remove_object_id_by_identifier($p_key)
    {
        unset(self::$m_objects_cache[$p_key]);
    }

    /**
     * Get cached objects by identifier
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_cached_objects()
    {
        return (is_array(self::$m_objects_cache)) ? self::$m_objects_cache : [];
    }

    /**
     * Get cached objects which have no entry in the identifier category
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function get_missing_identifiers()
    {
        return self::$m_missing_identifier;
    }

    /**
     * Add new entry to the cached objects which have no entry in the identifier category
     *
     * @param $p_obj_id
     * @param $p_id
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public static function set_missing_identifiers($p_obj_id, $p_id)
    {
        self::$m_missing_identifier[$p_obj_id] = $p_id;
    }

    /**
     * Checks if identifier is missing in i-doit
     *
     * @param $p_obj_id
     *
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function is_identifier_missing($p_obj_id)
    {
        return isset(self::$m_missing_identifier[$p_obj_id]);
    }

    /**
     * Remove from missing identifiers
     *
     * @param $p_obj_id
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function remove_missing_identifier($p_obj_id)
    {
        unset(self::$m_missing_identifier[$p_obj_id]);
    }

    /**
     * Creates new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  mixed  Returns created entity's identifier (int) or false (bool).
     */
    public function create($p_data)
    {
        // Set last_edited field
        $p_data['last_edited'] = date('Y-m-d H:i:s');

        return parent::create_data($p_data); // TODO: Change the autogenerated stub
    }

    /**
     * Get value by object id, type and key
     *
     * @param $p_obj_id
     * @param $p_type
     * @param $p_key
     *
     * @return bool|int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_value($p_obj_id, $p_type, $p_key, $p_group = null)
    {
        if (!($p_type = $this->check_identifier_type($p_type))) {
            return false;
        }

        $l_sql = 'SELECT isys_catg_identifier_list__value FROM isys_catg_identifier_list USE INDEX (identifier_universal)
          WHERE
          isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
          AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
          AND isys_catg_identifier_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
          AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_group !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
        }

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__value');
    }

    /**
     * Get object id by type, key and value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_object_id_by_key_value($p_type, $p_key, $p_value, $p_group_name = null)
    {
        if (!($p_type = $this->check_identifier_type($p_type))) {
            return false;
        }
        $l_sql = 'SELECT isys_catg_identifier_list__isys_obj__id FROM isys_catg_identifier_list USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_group_name !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group_name);
        }

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__isys_obj__id');
    }

    /**
     * Get Object id and entry id by type, key and value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_identifier_by_key_value($p_type, $p_key, $p_value, $p_group = null)
    {
        if (!($p_type = $this->check_identifier_type($p_type))) {
            return false;
        }
        $l_sql = 'SELECT CONCAT_WS(\'_\', isys_catg_identifier_list__isys_obj__id, isys_catg_identifier_list__id) AS identifier FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_group !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
        }

        return $this->retrieve($l_sql)
            ->get_row_value('identifier');
    }

    /**
     * Gets id by key value
     *
     * @param $p_type
     * @param $p_key
     * @param $p_value
     *
     * @return bool|mixed
     * @throws isys_exception_database
     */
    public function get_id_by_key_value($p_type, $p_key, $p_value, $p_group = null)
    {
        if (!($p_type = $this->check_identifier_type($p_type))) {
            return false;
        }
        $l_sql = 'SELECT isys_catg_identifier_list__id FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_group !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
        }

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_identifier_list__id');
    }

    /**
     * Get objects by type and key
     *
     * @param $p_type
     * @param $p_key
     *
     * @return bool|isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_objects_by_type_key($p_type, $p_key, $p_group = null)
    {
        if (!($p_type = $this->check_identifier_type($p_type))) {
            return false;
        }

        // Using Index Key "identifier_unique_index"
        $l_sql = 'SELECT * FROM isys_catg_identifier_list
          USE INDEX (identifier_universal)
          WHERE
          isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
          AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
          AND isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($p_group !== null) {
            if (is_array($p_group)) {
                $l_sql .= ' AND isys_catg_identifier_list__group' . $this->prepare_in_condition($p_group);
            } else {
                $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
            }
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Wrapper function to get objects by type and key as array
     *
     * @param $p_type
     * @param $p_key
     *
     * @return array|bool
     */
    public function get_objects_by_type_key_as_array($p_type, $p_key, $p_obj_id_as_key = true)
    {
        $l_res = $this->get_objects_by_type_key($p_type, $p_key);
        if ($l_res) {
            $l_arr = [];
            while ($l_row = $l_res->get_row()) {
                if ($p_obj_id_as_key) {
                    $l_arr[$l_row['isys_catg_identifier_list__isys_obj__id']] = $l_row['isys_catg_identifier_list__value'];
                } else {
                    $l_arr[$l_row['isys_catg_identifier_list__value']] = $l_row['isys_catg_identifier_list__isys_obj__id'];
                }
            }

            return $l_arr;
        }

        return false;
    }

    /**
     * @param        $p_obj_id
     * @param        $p_type
     * @param        $p_key
     * @param        $p_value
     * @param string $p_description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function set_identifier($p_obj_id, $p_type, $p_key, $p_value, $p_description = '', $p_group = null, $p_scantime = '', $p_check_status = C__RECORD_STATUS__NORMAL)
    {
        try {
            $l_sql = 'SELECT isys_catg_identifier_list__id, isys_catg_identifier_list__value, isys_catg_identifier_list__isys_obj__id, isys_catg_identifier_list__group, isys_catg_identifier_list__description FROM isys_catg_identifier_list
            WHERE
            isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . '
            AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . '
            AND isys_catg_identifier_list__status = ' . $this->convert_sql_int($p_check_status);

            if (is_array($p_group)) {
                $l_sql .= ' AND (';
                foreach ($p_group as $l_group) {
                    $l_sql .= ' isys_catg_identifier_list__group = ' . $this->convert_sql_text($l_group) . ' OR';
                }
                $l_sql = rtrim($l_sql, ' OR');
                $l_sql .= ')';

                $p_group = array_flip($p_group);
            } elseif ($p_group !== null) {
                $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group);
            }

            $l_sql .= ' AND isys_catg_identifier_list__value NOT LIKE \'%obsolet%\'';

            $l_res = $this->retrieve($l_sql);
            $l_amount = $l_res->num_rows();

            $l_data = [
                'isys_catg_identifier_list__key'                           => $p_key,
                'isys_catg_identifier_list__value'                         => $p_value,
                'isys_catg_identifier_list__isys_catg_identifier_type__id' => $p_type,
                'isys_catg_identifier_list__last_scan'                     => $p_scantime,
                'isys_catg_identifier_list__description'                   => $p_description,
                'isys_catg_identifier_list__status'                        => $p_check_status
            ];
            $l_found_id = [];
            $ignoredIds = $deleteIds = '';
            $l_create = true;

            if ($l_amount >= 1) {
                while ($l_row = $l_res->get_row()) {
                    $l_category_data = $l_data;
                    $l_category_data['isys_catg_identifier_list__description'] = $l_row['isys_catg_identifier_list__description'];

                    if ($l_row['isys_catg_identifier_list__isys_obj__id'] == $p_obj_id) {
                        if ($p_group !== null) {
                            if (is_array($p_group)) {
                                if (isset($p_group[$l_row['isys_catg_identifier_list__group']])) {
                                    // Remove group
                                    unset($p_group[$l_row['isys_catg_identifier_list__group']]);
                                    $l_category_data['isys_catg_identifier_list__group'] = $l_row['isys_catg_identifier_list__group'];
                                }
                            } elseif ($l_row['isys_catg_identifier_list__group'] == $p_group) {
                                $l_category_data['isys_catg_identifier_list__group'] = $p_group;
                                unset($p_group);
                            }
                        }
                        $l_found_id[$l_row['isys_catg_identifier_list__id']] = $l_category_data;
                        continue;
                    }
                }

                if (count($l_found_id)) {
                    ksort($l_found_id);
                    $foundUnique = array_unique($l_found_id);
                    foreach ($l_found_id as $l_id => $l_data) {
                        if (!isset($foundUnique[$l_id])) {
                            $deleteIds .= $l_id . ',';
                            continue;
                        }

                        unset($l_data['old_value']);
                        $this->update_identifier($l_id, $l_data);
                        $ignoredIds .= $l_id . ',';
                        $l_create = false;
                    } //
                }
            }

            // Remove all duplicate identifiers
            if ($deleteIds !== '') {
                $deleteSql = 'DELETE FROM isys_catg_identifier_list 
                  WHERE isys_catg_identifier_list__id IN (' . rtrim($deleteIds, ',') . ');';
                $this->update($deleteSql) && $this->apply_update();
            }

            // Set all values which have the same id to obsolete
            $obsoleteSql = 'UPDATE isys_catg_identifier_list SET 
                  isys_catg_identifier_list__value = CONCAT(isys_catg_identifier_list__value, ' . $this->convert_sql_text(' (obsolet)') . ') 
                  WHERE
                    isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key) . ' AND 
                    isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value) . ' AND 
                    isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type);
            if ($ignoredIds != '') {
                $obsoleteSql .= ' AND isys_catg_identifier_list__id NOT IN (' . rtrim($ignoredIds, ',') . ')';
            }
            $this->update($obsoleteSql) && $this->apply_update();

            if ($l_create) {
                if (is_array($p_group) && count($p_group) > 0) {
                    foreach ($p_group as $l_group => $l_puffer) {
                        $l_data['isys_catg_identifier_list__group'] = $l_group;
                        $l_data['isys_catg_identifier_list__isys_obj__id'] = $p_obj_id;

                        $this->insert_identifier($l_data);
                    }
                } else {
                    $l_data['isys_catg_identifier_list__group'] = $p_group;
                    $l_data['isys_catg_identifier_list__isys_obj__id'] = $p_obj_id;

                    $this->insert_identifier($l_data);
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        } // try/catch
    }

    /**
     * Helper Method for the update
     *
     * @param $p_id
     * @param $p_data
     *
     * @return bool
     * @throws isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function update_identifier($p_id, $p_data)
    {
        $l_update = 'UPDATE isys_catg_identifier_list SET ';

        foreach ($p_data as $l_db_field => $l_value) {
            $l_update .= $l_db_field . ' = ' . (is_numeric($l_value) ? $this->convert_sql_int($l_value) : $this->convert_sql_text($l_value)) . ',';
        }

        $l_update = rtrim($l_update, ',') . ' WHERE isys_catg_identifier_list__id = ' . $this->convert_sql_id($p_id);

        return $this->update($l_update) && $this->apply_update();
    }

    /**
     * Helper Method for the insert
     *
     * @param $p_data
     *
     * @return bool
     * @throws isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function insert_identifier($p_data)
    {
        if (!isset($p_data['isys_catg_identifier_list__datetime'])) {
            $p_data['isys_catg_identifier_list__datetime'] = date('Y-m-d H:i:s');
        }

        $l_update = 'INSERT IGNORE INTO isys_catg_identifier_list SET ';

        foreach ($p_data as $l_db_field => $l_value) {
            if ($l_db_field == 'isys_catg_identifier_list__isys_obj__id') {
                $l_update .= $l_db_field . ' = ' . $this->convert_sql_id($l_value) . ',';
            } else {
                $l_update .= $l_db_field . ' = ' . (is_numeric($l_value) ? $this->convert_sql_int($l_value) : $this->convert_sql_text($l_value)) . ',';
            }
        }
        
        return $this->update(rtrim($l_update, ',')) && $this->apply_update();
    }

    /**
     * Clears identifiers by type, key or value if set.
     *
     * @param        $p_type
     * @param string $p_key
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function clear_identifiers($p_type, $p_key = null, $p_value = null, $p_group_name = null)
    {
        $l_sql = 'UPDATE isys_catg_identifier_list SET isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__ARCHIVED) .
            ' WHERE isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . ' ';

        if ($p_key !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key);
        }

        if ($p_value !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__value = ' . $this->convert_sql_text($p_value);
        }

        if ($p_group_name !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__group = ' . $this->convert_sql_text($p_group_name);
        }

        if ($this->update($l_sql) && $this->apply_update()) {
            if ($p_value !== null) {
                if (isset(self::$m_objects_cache[$p_value])) {
                    unset(self::$m_objects_cache[$p_value]);
                }
            } else {
                self::$m_objects_cache = [];
            }

            return true;
        }

        return false;
    }

    /**
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function recover_identifiers($p_type, $p_key = null)
    {
        $l_sql = 'UPDATE isys_catg_identifier_list SET isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            ' WHERE isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($p_type) . ' ';

        $l_sql .= ' AND isys_catg_identifier_list__value NOT LIKE \'%obsolet%\' ';

        if ($p_key !== null) {
            $l_sql .= ' AND isys_catg_identifier_list__key = ' . $this->convert_sql_text($p_key);
        }

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * @param $type
     * @param $key
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function deleteArchivedEntries($type, $key)
    {
        $delete = 'DELETE FROM isys_catg_identifier_list WHERE isys_catg_identifier_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__ARCHIVED). '
            AND isys_catg_identifier_list__isys_catg_identifier_type__id = ' . $this->convert_sql_id($type) . '
            AND isys_catg_identifier_list__key = ' . $this->convert_sql_text($key);

        return $this->update($delete) && $this->apply_update();
    }

    /**
     * Create object map by identifier
     *
     * @param $p_jdisc_server
     *
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function map_existing_objects_by_identifier($p_type, $p_key, $p_obj_id_as_array = false)
    {
        self::$m_objects_cache = $this->get_objects_by_type_key_as_array($p_type, $p_key, $p_obj_id_as_array);
    }

    /**
     * Sets mapping for the identifiers
     *
     * @param $p_value
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_mapping($p_value)
    {
        self::$m_objects_cache = $p_value;
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_category_data_id Entity's identifier
     * @param   array   $p_data             Properties in a associative array with tags as keys and their corresponding
     *                                      values as values.
     *
     * @return  boolean
     * @author      Selcuk Kekec <skekec@i-doit.com>
     */
    public function save_data($p_category_data_id, $p_data)
    {
        return parent::save_data($p_category_data_id, $p_data);
    }

    /**
     * Private method which checks the identifier type
     *
     * @param $p_type
     *
     * @return bool|mixed
     */
    private function check_identifier_type($p_type)
    {
        if (!is_numeric($p_type)) {
            if (defined($p_type)) {
                $p_type = constant($p_type);
            } else {
                // Identifier type is not defined
                $p_type = false;
            }
        }

        return $p_type;
    }
}
