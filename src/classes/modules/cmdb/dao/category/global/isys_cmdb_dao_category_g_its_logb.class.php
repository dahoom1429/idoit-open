<?php

/**
 * i-doit
 *
 * DAO: global category for IT service log books
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_its_logb extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'its_logb';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__ITS_LOGBOOK';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__ITS_LOGBOOK;

    /**
     * Category's table.
     *
     * @var string
     */
    protected $m_table = 'isys_catg_logb_list';

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_daoITSC = isys_cmdb_dao_category_g_it_service_components::instance($this->get_database_component());
        $l_daoRel = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $l_listdao = isys_component_dao_logbook::instance($this->get_database_component());

        $l_objects = $l_daoITSC->get_assigned_object($p_obj_id);
        if ($l_objects->num_rows() > 0) {
            $l_arObjects = [];
            while ($l_row = $l_objects->get_row()) {
                // If the object is already in the logbook, skip.
                if (!in_array($l_row["isys_connection__isys_obj__id"], $l_arObjects)) {
                    $l_objInfo = $l_daoITSC->get_type_by_object_id($l_row["isys_connection__isys_obj__id"]);
                    $l_rowInfo = $l_objInfo->get_row();

                    // If the object is a relation, we want the logbook of the corresponding objects, not the logbook of the relation itself.
                    if (intval($l_rowInfo["isys_obj_type__id"]) == defined_or_default('C__OBJTYPE__RELATION')) {
                        list($l_master, $l_slave) = $l_daoRel->get_relation_members_by_obj_id($l_row["isys_connection__isys_obj__id"]);
                        if (!empty($l_master)) {
                            $l_arObjects[$l_master] = $l_master;
                        }

                        if (!empty($l_slave)) {
                            $l_arObjects[$l_slave] = $l_slave;
                        }
                    } else {
                        if (!empty($l_row["isys_connection__isys_obj__id"]) && !in_array($l_row["isys_connection__isys_obj__id"], $l_arObjects)) {
                            $l_arObjects[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
                        }
                    }
                }
            }
        } else {
            $l_arObjects = -1;
        }

        $l_logbRes = $l_listdao->get_result(null, false, $l_arObjects, true);

        return $l_logbRes;
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
                $l_sql = ' AND (isys_catg_logb_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_logb_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * @param isys_component_dao_result $p_daores
     *
     * @return bool
     */
    public function init(isys_component_dao_result &$p_daores)
    {
        $this->m_daores = $p_daores;

        return true;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'date'             => array_replace_recursive(isys_cmdb_dao_category_pattern::datetime(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__date'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__LOGB__DATE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ]
            ]),
            'object'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__SOURCE__OBJECT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_logb_list__isys_obj__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'event'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__EVENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Event'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_logbook__isys_logbook_event__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_logbook_event',
                        'isys_logbook_event__id'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog'
                    ]
                ]
            ]),
            'source'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__SOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Source'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__isys_logbook_source__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ]
            ]),
            'user'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__SOURCE__USER',
                    C__PROPERTY__INFO__DESCRIPTION => 'User'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__isys_obj__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'object_type'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__obj_type_static'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
            ]),
            'category'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CATEGORY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Category'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__category_static'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
            ]),
            'alert_level'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__LEVEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Alarmlevel'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_logbook__isys_logbook_level__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_logbook_level',
                        'isys_logbook_level__id'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog'
                    ]
                ]
            ]),
            'user_name_static' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__USER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Username'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__user_name_static'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ]
            ]),
            'event_static'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOGB__EVENT_STATIC',
                    C__PROPERTY__INFO__DESCRIPTION => 'Event'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__event_static'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
            ]),
            'comment'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__CONTACT_COMMENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Comment'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__comment'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
            ]),
            'changes'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CHANGES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Changes'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_logbook__changes'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__LOGB__CHANGES'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'logbook_changes'
                    ]
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_logb_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LOGBOOK', 'C__CATG__LOGBOOK')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST      => false,
                    //C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                ],
            ])
        ];
    }

    /**
     * @param array $p_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool
     */
    public function sync($p_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        return true;

        /**
         * @todo Method transform_category_data_information does not exist anymore.
         */
        $l_map = isys_export_cmdb_object::transform_category_data_information($this->m_data_information);

        try {
            $l_dao = new isys_component_dao_logbook($this->m_db);

            foreach ($p_data as $l_data) {
                $l_data = (array)$l_data;

                $l_alert_level = null;

                if ($l_data["alert_level"]) {
                    $l_alert_level = constant($l_data["alert_level"]);
                }

                if (is_null($l_alert_level) && defined('C__LOGBOOK__ALERT_LEVEL__0')) {
                    $l_alert_level = C__LOGBOOK__ALERT_LEVEL__0;
                }

                if (is_null($l_data[$l_map["isys_logbook__event_static"]])) {
                    $l_data[$l_map["isys_logbook__event_static"]] = $l_data["title"];
                }

                if (is_null($l_data[$l_map["isys_logbook__description"]])) {
                    $l_data[$l_map["isys_logbook__description"]] = $l_data["description"];
                }

                if (isset($l_data["date"])) {
                    $l_date = date("Y-m-d H:i:s", strtotime($l_data["date"]));
                    if (strstr($l_date, "1970")) {
                        $l_date = null;
                    }
                } else {
                    $l_date = null;
                }

                $l_dao->set_entry($l_data[$l_map["isys_logbook__event_static"]], $l_data["log"], $l_date, $l_alert_level, $p_object_id,
                    $l_data[$l_map["isys_logbook__obj_name_static"]], $l_data[$l_map["isys_logbook__obj_type_static"]], $l_data[$l_map["isys_logbook__category_static"]],
                    defined_or_default('C__LOGBOOK_SOURCE__ALL'), "", $l_data[$l_map["isys_logbook__description"]]);
            }
        } catch (isys_exception_cmdb $e) {
            isys_glob_display_message($e->getMessage());
        }

        return true;
    }

    /**
     * @return int
     */
    public function save_element()
    {
        $l_dao = new isys_component_dao_logbook($this->m_db);

        $l_message = $_POST["C__CATG__LOGBOOK__MESSAGE"];
        $l_description = $_POST["C__CATG__LOGBOOK__DESCRIPTION"];

        if ($_POST["C__CATG__LOGBOOK__ALERTLEVEL"] > 0 && $_POST["C__CATG__LOGBOOK__ALERTLEVEL"] <= 4) {
            $l_alert_level = $_POST["C__CATG__LOGBOOK__ALERTLEVEL"];
        } else {
            $l_alert_level = defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1);
        }

        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];
        } else {
            $l_object_id = null;
        }

        $l_dao->set_entry($l_message, $l_description, null, $l_alert_level, $l_object_id, $this->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]),
            $this->get_objtype_name_by_id_as_string($_GET[C__CMDB__GET__OBJECTTYPE]), null, defined_or_default('C__LOGBOOK_SOURCE__USER'));

        return 2;
    }
}
