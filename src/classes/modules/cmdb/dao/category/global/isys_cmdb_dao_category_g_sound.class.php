<?php

/**
 * i-doit
 *
 * DAO: global category for sound cards
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sound extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'sound';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_sound_list
			INNER JOIN isys_obj ON isys_catg_sound_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_sound_manufacturer ON isys_sound_manufacturer__id = isys_catg_sound_list__isys_sound_manufacturer__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_sound_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_sound_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_sound_list__isys_sound_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_sound_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_sound_manufacturer',
                        'isys_sound_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_sound_manufacturer__title
                            FROM isys_catg_sound_list
                            INNER JOIN isys_sound_manufacturer ON isys_sound_manufacturer__id = isys_catg_sound_list__isys_sound_manufacturer__id', 'isys_catg_sound_list',
                        'isys_catg_sound_list__id', 'isys_catg_sound_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sound_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_sound_list', 'LEFT', 'isys_catg_sound_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_sound_manufacturer', 'LEFT', 'isys_catg_sound_list__isys_sound_manufacturer__id',
                            'isys_sound_manufacturer__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__SOUND__MANUFACTURER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_sound_manufacturer'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'title'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sound_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_sound_list__title FROM isys_catg_sound_list',
                        'isys_catg_sound_list', 'isys_catg_sound_list__id', 'isys_catg_sound_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sound_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SOUND__TITLE'
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_sound_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_sound_list__description FROM isys_catg_sound_list',
                        'isys_catg_sound_list', 'isys_catg_sound_list__id', 'isys_catg_sound_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_sound_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SOUND', 'C__CATG__SOUND'),
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create($p_object_id, $this->get_property('title'), C__RECORD_STATUS__NORMAL, $this->get_property('description'),
                        $this->get_property('manufacturer')))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], $this->get_property('title'), C__RECORD_STATUS__NORMAL, $this->get_property('description'),
                        $this->get_property('manufacturer'));
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Import-Handler for this category
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        $l_ids = [];
        $this->get_general_data();

        if (is_countable($p_data) && count($p_data) > 0) {
            // Iterate through Graphic-Adapters.
            foreach ($p_data as $l_entry) {
                /* Save / Create */
                $l_status = 0;
                /* Cat-New: 0, Cat-Save: ? */
                $l_cat = -1;

                $_POST["C__CATG__SOUND__MANUFACTURER"] = isys_import::check_dialog("isys_sound_manufacturer", $l_entry["manufacturer"]);

                $_POST["C__CATG__SOUND__TITLE"] = $l_entry["name"];

                $l_ids[] = $this->save_element($l_cat, $l_status, true);
            }
        }

        return $l_ids;
    }

    /**
     * Add new soundcard.
     *
     * @param   array   $p_object_id
     * @param   string  $p_title
     * @param   integer $p_status
     * @param   string  $p_description
     * @param   integer $p_manufacturer
     *
     * @return  mixed
     */
    public function create($p_object_id, $p_title, $p_status, $p_description, $p_manufacturer)
    {
        $l_sql = "INSERT INTO isys_catg_sound_list
			SET isys_catg_sound_list__isys_sound_manufacturer__id = " . $this->convert_sql_id($p_manufacturer) . ",
			isys_catg_sound_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_sound_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_sound_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_sound_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL = $l_sql;

            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Updates an existing soundcard.
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   integer $p_status
     * @param   string  $p_description
     * @param   integer $p_manufacturer
     *
     * @return  boolean
     */
    public function save($p_id, $p_title, $p_status, $p_description, $p_manufacturer)
    {
        $l_sql = "UPDATE isys_catg_sound_list
			SET isys_catg_sound_list__isys_sound_manufacturer__id = " . $this->convert_sql_id($p_manufacturer) . ",
			isys_catg_sound_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_sound_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_sound_list__description = " . $this->convert_sql_text($p_description) . "
			WHERE isys_catg_sound_list__id = " . $this->convert_sql_id($p_id) . ";";

        if ($this->update($l_sql)) {
            $this->m_strLogbookSQL = $l_sql;

            return $this->apply_update();
        }
    }

    /**
     * @param   integer $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        if ($p_create) {
            $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], $_POST["C__CATG__SOUND__TITLE"], C__RECORD_STATUS__NORMAL,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()], $_POST["C__CATG__SOUND__MANUFACTURER"]);

            if ($l_id > 0) {
                $p_cat_level = null;

                return $l_id;
            }
        } else {
            $l_catdata = $this->get_general_data();

            if ($this->save($l_catdata["isys_catg_sound_list__id"], $_POST["C__CATG__SOUND__TITLE"], $l_catdata["isys_catg_sound_list__status"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()], $_POST["C__CATG__SOUND__MANUFACTURER"])) {
                return null;
            }
        }

        return false;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array(array $p_data = [])
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_sound_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }
}
