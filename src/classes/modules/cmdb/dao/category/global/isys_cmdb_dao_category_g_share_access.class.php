<?php

/**
 * i-doit
 *
 * DAO: global category for share access.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_share_access extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'share_access';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'mountpoint';

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
    protected $m_object_id_field = 'isys_catg_share_access_list__isys_obj__id';

    /**
     * Callback method for the dialog-field of shares
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_shares(isys_request $p_request)
    {
        $l_return = [];

        $l_connected_obj = $p_request->get_row('isys_connection__isys_obj__id');

        if ($l_connected_obj > 0) {
            $l_res = isys_cmdb_dao_category_g_shares::instance($this->get_database_component())
                ->get_data(null, $l_connected_obj);

            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row["isys_catg_shares_list__id"]] = $l_row["isys_catg_shares_list__title"];
            }
        }

        return $l_return;
    }

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
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_share_access_list
			LEFT JOIN isys_catg_shares_list ON isys_catg_shares_list__id = isys_catg_share_access_list__isys_catg_shares_list__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_share_access_list__isys_connection__id
			INNER JOIN isys_obj ON isys_catg_share_access_list__isys_obj__id = isys_obj__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_share_access_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_share_access_list__status = " . $this->convert_sql_int($p_status);
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
            'assigned_objects' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__POPUP__BROWSER__SELECTED_OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_share_access_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__SHARE_ACCESS'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_share_access',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_share_access',
                        true
                    ]),
                    C__PROPERTY__DATA__FIELD_ALIAS      => 'access_object',
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_share_access_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_share_access_list__isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id', 'isys_catg_share_access_list', 'isys_catg_share_access_list__id',
                        'isys_catg_share_access_list__isys_obj__id', '', '', idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_share_access_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_share_access_list',
                            'LEFT',
                            'isys_catg_share_access_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_share_access_list__isys_connection__id',
                            'isys_connection__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS',
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'mountpoint'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARE_ACCESS__MOUNTPOINT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Mountpoint'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_share_access_list__mountpoint',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_share_access_list__mountpoint FROM isys_catg_share_access_list',
                        'isys_catg_share_access_list', 'isys_catg_share_access_list__id', 'isys_catg_share_access_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_share_access_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__SHARE_ACCESS__MOUNTPOINT'
                ]
            ]),
            'shares'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SHARES__SHARE_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Share title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_share_access_list__isys_catg_shares_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_shares_list',
                        'isys_catg_shares_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' > \', isys_catg_shares_list__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_share_access_list
                                INNER JOIN isys_catg_shares_list ON isys_catg_shares_list__id = isys_catg_share_access_list__isys_catg_shares_list__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_shares_list__isys_obj__id
                                ', 'isys_catg_share_access_list', 'isys_catg_share_access_list__id', 'isys_catg_share_access_list__isys_obj__id', '', '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_share_access_list__isys_obj__id'])),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_share_access_list', 'LEFT', 'isys_catg_share_access_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_shares_list', 'LEFT', 'isys_catg_share_access_list__isys_catg_shares_list__id',
                            'isys_catg_shares_list__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__SHARE_ACCESS__ASSIGNED_SHARE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_share_access',
                            'callback_property_shares'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'share_access'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_share_access_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_share_access_list__description FROM isys_catg_share_access_list',
                        'isys_catg_share_access_list', 'isys_catg_share_access_list__id', 'isys_catg_share_access_list__isys_obj__id', '', '', null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_share_access_list__isys_obj__id']))
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SHARE_ACCESS', 'C__CATG__SHARE_ACCESS')
                ]
            ])
        ];
    }

    /**
     * Sync method
     *
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $this->get_property('assigned_objects'), $this->get_property('shares'),
                        $this->get_property('mountpoint'), $this->get_property('description'));
                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $this->get_property('assigned_objects'), $this->get_property('shares'),
                        $this->get_property('mountpoint'), $this->get_property('description'));
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method save_element.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        if ($_GET[C__CMDB__GET__CATLEVEL] != -1 && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
            $l_ret = $this->save($_GET[C__CMDB__GET__CATLEVEL], C__RECORD_STATUS__NORMAL, isys_format_json::decode($_POST['C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS__HIDDEN']),
                $_POST['C__CATG__SHARE_ACCESS__SHARE'], $_POST['C__CATG__SHARE_ACCESS__MOUNTPOINT'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
        } else {
            $l_ret = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, isys_format_json::decode($_POST['C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS__HIDDEN']),
                $_POST['C__CATG__SHARE_ACCESS__SHARE'], $_POST['C__CATG__SHARE_ACCESS__MOUNTPOINT'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            $p_cat_level = -1;
        }

        return $l_ret;
    }

    /**
     * Method save.
     *
     * @param   integer $p_cat_level_id
     * @param   mixed   $p_status
     * @param   integer $p_assigned_objects
     * @param   integer $p_assigned_share
     * @param   string  $p_mountpoint
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_cat_level_id, $p_status = C__RECORD_STATUS__NORMAL, $p_assigned_objects, $p_assigned_share, $p_mountpoint, $p_description)
    {
        $l_cat_data = $this->get_data($p_cat_level_id)
            ->get_row();

        if (empty($l_cat_data['isys_catg_share_access_list__isys_connection__id'])) {
            $l_connection__id = isys_factory::get_instance('isys_cmdb_dao_connection', $this->get_database_component())
                ->add_connection($p_assigned_objects);

            $l_sql = 'UPDATE isys_catg_share_access_list SET isys_catg_share_access_list__isys_connection__id = ' . $this->convert_sql_id($l_connection__id) . ', ';
        } else {
            $l_sql = 'UPDATE isys_catg_share_access_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_share_access_list__isys_connection__id
				SET isys_connection__isys_obj__id  = ' . $this->convert_sql_id($p_assigned_objects) . ', ';
        }

        $l_sql .= 'isys_catg_share_access_list__isys_catg_shares_list__id = ' . $this->convert_sql_id($p_assigned_share) . ',
			isys_catg_share_access_list__mountpoint = ' . $this->convert_sql_text($p_mountpoint) . ',
			isys_catg_share_access_list__status = ' . $this->convert_sql_int($p_status) . ',
			isys_catg_share_access_list__description = ' . $this->convert_sql_text($p_description) . '
			WHERE isys_catg_share_access_list__id = ' . $this->convert_sql_id($p_cat_level_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation($p_cat_level_id, 'isys_catg_share_access_list', defined_or_default('C__RELATION_TYPE__SHARE_ACCESS'),
                    $l_cat_data['isys_catg_share_access_list__isys_catg_relation_list__id'], $p_assigned_objects, $l_cat_data['isys_catg_share_access_list__isys_obj__id']);

            return true;
        }

        return false;
    }

    /**
     * Method create
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     * @param   integer $p_assigned_objects
     * @param   integer $p_assigned_share
     * @param   string  $p_mountpoint
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL, $p_assigned_objects, $p_assigned_share, $p_mountpoint, $p_description)
    {
        $l_connection__id = isys_factory::get_instance('isys_cmdb_dao_connection', $this->get_database_component())
            ->add_connection($p_assigned_objects);

        $l_sql = 'INSERT INTO isys_catg_share_access_list SET
			isys_catg_share_access_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
			isys_catg_share_access_list__isys_catg_shares_list__id = ' . $this->convert_sql_id($p_assigned_share) . ',
			isys_catg_share_access_list__isys_connection__id = ' . $this->convert_sql_id($l_connection__id) . ',
			isys_catg_share_access_list__mountpoint = ' . $this->convert_sql_text($p_mountpoint) . ',
			isys_catg_share_access_list__status = ' . $this->convert_sql_int($p_status) . ',
			isys_catg_share_access_list__description = ' . $this->convert_sql_text($p_description);

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            isys_cmdb_dao_category_g_relation::instance($this->get_database_component())
                ->handle_relation($l_last_id, 'isys_catg_share_access_list', defined_or_default('C__RELATION_TYPE__SHARE_ACCESS'), null, $p_assigned_objects, $p_obj_id);

            return $l_last_id;
        }

        return false;
    }

    /**
     * Method to get all shares for the current object.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function get_shares($p_obj_id)
    {
        return isys_cmdb_dao_category_g_shares::instance($this->get_database_component())
            ->get_shares_by_obj_id_or_shares_id($p_obj_id);
    }
}