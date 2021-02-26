<?php

/**
 * i-doit
 *
 * DAO: global category for virtual hosts
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_host extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'virtual_host';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_virtual_host_list__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Add new graphic adapter.
     *
     * @param int    $p_object_id
     * @param int    $p_status
     * @param bool   $p_virtual_host
     * @param int   $p_license_server
     * @param int    $p_administration_service
     * @param string $p_description
     *
     * @return bool|int
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function create(
        $p_object_id,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_virtual_host = null,
        $p_license_server = null,
        $p_administration_service = null,
        $p_description = ''
    ) {
        $l_dao_con = new isys_cmdb_dao_connection(isys_application::instance()->database);

        $l_sql = 'INSERT IGNORE INTO isys_catg_virtual_host_list
            SET isys_catg_virtual_host_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_virtual_host_list__virtual_host = ' . $this->convert_sql_boolean($p_virtual_host) . ',
            isys_catg_virtual_host_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_virtual_host_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
            isys_catg_virtual_host_list__license_server = ' . $this->convert_sql_id($l_dao_con->add_connection($p_license_server)) . ',
            isys_catg_virtual_host_list__administration_service = ' . $this->convert_sql_id($l_dao_con->add_connection($p_administration_service)) . ';';

        if ($this->update($l_sql)) {
            $l_last_id = $this->get_last_insert_id();

            isys_cmdb_dao_category_g_relation::instance($this->m_db)
                ->handle_relation($l_last_id, "isys_catg_virtual_host_list", defined_or_default('C__RELATION_TYPE__VHOST_ADMIN_SERVICE'), null, $p_administration_service, $p_object_id);

            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                return $this->get_last_insert_id();
            }
        }

        return false;
    }

    /**
     * Updates an existing.
     *
     * @param int    $p_id
     * @param int    $p_status
     * @param int    $p_virtual_host
     * @param int    $p_license_server
     * @param int    $p_administration_service
     * @param string $p_description
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     */
    public function save($p_id, $p_status = C__RECORD_STATUS__NORMAL, $p_virtual_host = null, $p_license_server = null, $p_administration_service = null, $p_description = '')
    {
        $l_dao_connection = new isys_cmdb_dao_connection(isys_application::instance()->database);

        if (is_numeric($p_id)) {
            $l_data = $this->get_data($p_id)
                ->__to_array();

            if (!$l_data["isys_catg_virtual_host_list__license_server"]) {
                $l_licence_server = $l_dao_connection->add_connection($p_license_server);
            } else {
                $l_licence_server = $l_data["isys_catg_virtual_host_list__license_server"];
                $l_dao_connection->update_connection($l_licence_server, $p_license_server);
            }

            if (!$l_data["isys_catg_virtual_host_list__license_server"]) {
                $l_administration_service = $l_dao_connection->add_connection($p_administration_service);
            } else {
                $l_administration_service = $l_data["isys_catg_virtual_host_list__administration_service"];
                $l_dao_connection->update_connection($l_administration_service, $p_administration_service);
            }

            $l_sql = 'UPDATE isys_catg_virtual_host_list
                SET isys_catg_virtual_host_list__status = ' . $this->convert_sql_int($p_status) . ',
                isys_catg_virtual_host_list__virtual_host = ' . $this->convert_sql_boolean($p_virtual_host) . ',
                isys_catg_virtual_host_list__description = ' . $this->convert_sql_text($p_description) . ',
                isys_catg_virtual_host_list__license_server = ' . $this->convert_sql_id($l_licence_server) . ',
                isys_catg_virtual_host_list__administration_service = ' . $this->convert_sql_id($l_administration_service) . '
                WHERE isys_catg_virtual_host_list__id = ' . $this->convert_sql_id($p_id) . ';';

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL = $l_sql;

                isys_cmdb_dao_category_g_relation::instance($this->m_db)
                    ->handle_relation(
                        $p_id,
                        "isys_catg_virtual_host_list",
                        defined_or_default('C__RELATION_TYPE__VHOST_ADMIN_SERVICE'),
                        $l_data['isys_catg_virtual_host_list__isys_catg_relation_list__id'],
                        $p_administration_service,
                        $l_data['isys_catg_virtual_host_list__isys_obj__id']
                    );

                return $this->apply_update();
            }
        }

        return false;
    }

    /**
     * @param $p_cat_level
     *
     * @author Van Quyen Hoang <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        if ($p_create && empty($l_catdata)) {

            /**
             * Create a new virtual host entry
             */
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATG__VIRTUAL_HOST__YES_NO"],
                $_POST["C__CATG__VIRTUAL_HOST__LICENSE_SERVER__HIDDEN"],
                $_POST["C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id > 0) {
                $p_cat_level = 1;

                return $l_id;
            }
        } else {

            /**
             * Save the virtual host
             */
            if ($this->save(
                $l_catdata["isys_catg_virtual_host_list__id"],
                $l_catdata["isys_catg_virtual_host_list__status"],
                $_POST["C__CATG__VIRTUAL_HOST__YES_NO"],
                $_POST["C__CATG__VIRTUAL_HOST__LICENSE_SERVER__HIDDEN"],
                $_POST["C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            )) {
                $l_mod_event_manager = isys_event_manager::getInstance();

                if ($_GET[C__CMDB__GET__OBJECT] && (int)$_POST['C__CATG__VIRTUAL_HOST__YES_NO'] === 0) {
                    $l_selected = explode(",", $_POST["objects"]);
                    $l_dao_guests = new isys_cmdb_dao_category_g_guest_systems($this->get_database_component());
                    $l_guests = $l_dao_guests->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                    while ($l_row = $l_guests->get_row()) {
                        $l_guest = $l_row["isys_catg_virtual_machine_list__isys_obj__id"];

                        if ($l_guest > 0 && in_array($l_guest, $l_selected)) {
                            /**
                             * Devirtualize actions
                             */
                            if ($_POST["devirtualize_action"]) {
                                switch ($_POST["devirtualize_action"]) {
                                    case 1:
                                        $l_status = C__RECORD_STATUS__DELETED;
                                        $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_DELETED";
                                        break;
                                    case 2:
                                        $l_status = C__RECORD_STATUS__ARCHIVED;
                                        $l_strConstEvent = "C__LOGBOOK_EVENT__OBJECT_ARCHIVED";
                                        break;
                                }

                                /**
                                 * Change object status
                                 */
                                if ($this->set_object_status($l_guest, $l_status)) {
                                    /**
                                     * Trigger Logbook message
                                     */
                                    $l_mod_event_manager->triggerCMDBEvent($l_strConstEvent, $this->get_last_query(), $l_guest, $this->get_objTypeID($l_guest));
                                }
                            }
                        }

                        /**
                         * Detach guest system
                         *
                         * @todo implement a real detaching instead of overwriting everything with NULL..
                         */
                        $l_dao_guests->save($l_row["isys_catg_virtual_machine_list__id"], C__RECORD_STATUS__NORMAL, null, null, null);
                    }
                }

                return null;
            }
        }

        return false;
    }

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATG__VIRTUAL_HOST_ROOT')) {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_catg_virtual_host_list__id AS cnt FROM isys_catg_virtual_host_list
					WHERE isys_catg_virtual_host_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_virtual_switch_list__id AS cnt FROM  isys_catg_virtual_switch_list
					WHERE  isys_catg_virtual_switch_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_virtual_machine_list__id AS cnt FROM isys_catg_virtual_machine_list
					INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id
					WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return $this->retrieve($l_sql)
                ->get_row_value('cnt');
        } else {
            return parent::get_count($p_obj_id);
        }
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function properties()
    {
        return [
            'virtual_host'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VIRTUAL_HOST',
                    C__PROPERTY__INFO__DESCRIPTION => 'defines if object is a virtual host'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_virtual_host_list__virtual_host',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        '(CASE WHEN isys_catg_virtual_host_list__virtual_host = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	    WHEN isys_catg_virtual_host_list__virtual_host = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)',
                        'isys_catg_virtual_host_list'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_host_list',
                            'LEFT',
                            'isys_catg_virtual_host_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VIRTUAL_HOST__YES_NO',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'license_server'         => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__LICENSE_SERVER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Defines which object is the licence server'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_virtual_host_list__license_server',
                    C__PROPERTY__DATA__FIELD_ALIAS => 'licence_server_connection',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'connection_licence_server',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_virtual_host_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_host_list__license_server
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_virtual_host_list',
                        'isys_catg_virtual_host_list__id',
                        'isys_catg_virtual_host_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_host_list',
                            'LEFT',
                            'isys_catg_virtual_host_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_virtual_host_list__license_server',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VIRTUAL_HOST__LICENSE_SERVER',
                    C__PROPERTY__UI__PARAMS => []
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'administration_service' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER__ADMINISTRATION_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Defines which object is the administration service'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_virtual_host_list__administration_service',
                    C__PROPERTY__DATA__FIELD_ALIAS      => 'administration_service_connection',
                    C__PROPERTY__DATA__TABLE_ALIAS      => 'connection_administration_service',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__VHOST_ADMIN_SERVICE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_virtual_host',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_virtual_host']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj__title
                            FROM isys_catg_virtual_host_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_host_list__administration_service
                            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_virtual_host_list',
                        'isys_catg_virtual_host_list__id',
                        'isys_catg_virtual_host_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_host_list',
                            'LEFT',
                            'isys_catg_virtual_host_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_virtual_host_list__administration_service',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__INDEX            => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VIRTUAL_HOST__ADMINISTRATION_SERVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'browser_object_relation',
                        'relationFilter' => 'C__RELATION_TYPE__SOFTWARE'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'database_instance'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                ]
            ]),
            'description'            => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_host_list__description',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VIRTUAL_HOST', 'C__CATG__VIRTUAL_HOST'),
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
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('virtual_host'),
                        $this->get_property('license_server'),
                        $this->get_property('administration_service'),
                        $this->get_property('description')
                    ))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('virtual_host'),
                        $this->get_property('license_server'),
                        $this->get_property('administration_service'),
                        $this->get_property('description')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
