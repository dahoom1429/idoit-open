<?php
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;

/**
 * i-doit
 *
 * DAO: global category for cluster servies
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cluster_service extends isys_cmdb_dao_category_global
{
    /**
     * Cluster service status Type enabled
     */
    const C__CLUSTER_SERVICE__STATUS_RUNNING = 1;

    /**
     * Cluster service status Type disabled
     */
    const C__CLUSTER_SERVICE__STATUS_DISABLED = 0;

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_service';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLUSTER_SERVICES';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'cluster_service';

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
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_cluster_service_list__isys_obj__id';

    /**
     * Handle connection to DBMS
     *
     * @param int $p_relation_id isys_catg_relation__id
     * @param int $p_dbms_obj    isys_obj__id
     *
     * @return bool|mixed
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function handle_dbms($p_relation_id, $p_dbms_obj)
    {
        global $g_comp_database;

        // Relation exists?
        if (is_numeric($p_relation_id)) {
            /** @var isys_cmdb_dao_category_g_relation $l_relation_dao */
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($g_comp_database);

            // Get relation data
            $l_rel_data = $l_relation_dao->get_data($p_relation_id)
                ->__to_array();

            /** @var isys_cmdb_dao_category_s_database_access $l_dao_dbms_access */
            $l_dao_dbms_access = isys_cmdb_dao_category_s_database_access::instance($g_comp_database);
            // Is DBS present?
            if ($p_dbms_obj > 0) {
                // Check for existing assignment
                $l_dbms_res = $l_dao_dbms_access->get_data(
                    null,
                    null,
                    "AND isys_connection__isys_obj__id = " . $l_dao_dbms_access->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                    null,
                    C__RECORD_STATUS__NORMAL
                );

                $l_create = true;

                // Remove connection
                if ($l_dbms_res->num_rows()) {
                    $l_create = $l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                }

                if ($l_create) {
                    // Create link to DBS
                    return $l_dao_dbms_access->create($p_dbms_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                }
            } else {
                // Remove connection
                return $l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
            }
        }

        return false;
    }

    /**
     * Get DBMS related data.
     *
     * @param   integer $p_relation_id
     *
     * @return  array
     * @throws isys_exception_database
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public static function get_dbms($p_relation_id)
    {
        if (is_numeric($p_relation_id)) {
            $l_dbms_dao = isys_cmdb_dao_category_s_database_access::instance(isys_application::instance()->database);

            // Retrieve relation object
            $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id
                FROM isys_catg_relation_list
                WHERE isys_catg_relation_list__id = ' . $l_dbms_dao->convert_sql_id($p_relation_id) . ';';

            $l_rel_res = $l_dbms_dao->retrieve($l_sql);

            if (is_countable($l_rel_res) && count($l_rel_res)) {
                // Get DBMS data.
                $l_dbms_res = $l_dbms_dao->get_data(
                    null,
                    null,
                    "AND isys_connection__isys_obj__id = " . $l_dbms_dao->convert_sql_id($l_rel_res->get_row_value('isys_catg_relation_list__isys_obj__id')),
                    null,
                    C__RECORD_STATUS__NORMAL
                );

                if ($l_dbms_res->num_rows()) {
                    return $l_dbms_res->get_row();
                }
            }
        }

        return [];
    }

    /**
     * Callback method for the notification option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_runs_on(isys_request $p_request)
    {
        $l_return = [];

        $l_dao_cluster_members = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
        $l_cluster_members = $l_dao_cluster_members->get_data(null, $p_request->get_object_id(), '', null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_cluster_members->get_row()) {
            $l_selected = false;

            if ($p_request->get_category_data_id() > 0) {
                $l_selected = ($this->get_cluster_members($p_request->get_category_data_id(), $l_row["isys_catg_cluster_members_list__id"])
                        ->num_rows() > 0);
            }

            $l_return[] = [
                "val" => $this->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]),
                "hid" => 0,
                "sel" => $l_selected,
                "id"  => $l_row["isys_catg_cluster_members_list__id"]
            ];
        }

        return $l_return;
    }

    /**
     * Callback method for property assigned_database_schema
     *
     * @param isys_request $p_request
     *
     * @return null|int
     * @throws isys_exception_database
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function callback_property_assigned_database_schema(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return = null;
        $l_dbs_dao = isys_cmdb_dao_category_s_database_access::instance($g_comp_database);
        $l_data = $p_request->get_row();
        $l_sql = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list ' . 'WHERE isys_catg_relation_list__id = ' .
            $l_dbs_dao->convert_sql_id($l_data['isys_catg_cluster_service_list__isys_catg_relation_list__id']);

        $l_rel_res = $l_dbs_dao->retrieve($l_sql);

        if ($l_rel_res->num_rows() > 0) {
            $l_rel_data = $l_rel_res->get_row_value('isys_catg_relation_list__isys_obj__id');
            $l_dbs_data = $l_dbs_dao->get_data(null, null, "AND isys_connection__isys_obj__id = " . $l_dbs_dao->convert_sql_id($l_rel_data), null, C__RECORD_STATUS__NORMAL)
                ->get_row();

            $l_return = $l_dbs_data['isys_obj__id'];
        }

        return $l_return;
    }

    /**
     * Callback method for the notification option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_default_server(isys_request $p_request)
    {
        $l_return = [];

        $l_dao_cluster_members = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
        $l_cluster_members = $l_dao_cluster_members->get_data(null, $p_request->get_object_id(), '', null, C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_cluster_members->get_row()) {
            $l_selected = false;

            if ($p_request->get_category_data_id() > 0) {
                $l_selected = ($this->get_cluster_members($p_request->get_category_data_id(), $l_row["isys_catg_cluster_members_list__id"])
                        ->num_rows() > 0);
            }

            if ($l_selected) {
                $l_return[$l_row["isys_catg_cluster_members_list__id"]] = $this->get_obj_name_by_id_as_string($l_row["isys_connection__isys_obj__id"]);
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
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_service_list
			INNER JOIN isys_obj ON isys_catg_cluster_service_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_cluster_type ON isys_cluster_type__id = isys_catg_cluster_service_list__isys_cluster_type__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id
			LEFT JOIN isys_catg_cluster_members_list ON isys_catg_cluster_members_list__id = isys_catg_cluster_service_list__cluster_members_list__id
			 LEFT JOIN isys_cats_relpool_list ON isys_cats_relpool_list__id = isys_catg_cluster_service_list__isys_cats_relpool_list__id
			 WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_cluster_service_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @param string  $p_alias
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if ($p_obj_id !== null) {
            if (is_array($p_obj_id)) {
                return ' AND (isys_catg_cluster_service_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_catg_cluster_service_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return '';
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws Exception
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        return [
            'type'                     => (new DialogProperty(
                'C__CMDB__CATG__CLUSTER_SERVICE__TYPE',
                'LC__CMDB__CATG__CLUSTER_SERVICE__TYPE',
                'isys_catg_cluster_service_list__isys_cluster_type__id',
                'isys_catg_cluster_service_list',
                'isys_cluster_type'
            )),
            'cluster_service'          => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__OBJTYPE__CLUSTER_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cluster services'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_cluster_service_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__CLUSTER_SERVICE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_cluster_service',
                        'callback_property_relation_handler'
                    ], ['isys_cmdb_dao_category_g_cluster_service']),
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                              INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                              INNER JOIN isys_catg_cluster_service_list ON isys_catg_cluster_service_list__isys_connection__id = isys_connection__id',
                        'isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list__id',
                        'isys_catg_cluster_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cluster_service_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cluster_service_list',
                            'LEFT',
                            'isys_catg_cluster_service_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_cluster_service_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL => false
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION',
                    isys_popup_browser_object_ng::C__CAT_FILTER => "C__CATS__APPLICATION;C__CATS__CLUSTER_SERVICE;C__CATS__DBMS;C__CATS__MIDDLEWARE;C__CATS__SERVICE",
                    isys_popup_browser_object_ng::C__CALLBACK__ACCEPT => "idoit.callbackManager.triggerCallback('clusterservice__cluster_service_selected');",
                    isys_popup_browser_object_ng::C__CALLBACK__DETACH => "new Effect.SlideUp('cluster_options', {duration:0.2});"
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'hostaddresses'            => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Host addresses'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_cluster_service_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_catg_ip_list_2_isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT ipa.isys_cats_net_ip_addresses_list__title FROM isys_cats_net_ip_addresses_list ipa
                              INNER JOIN isys_catg_ip_list ipl ON ipl.isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = ipa.isys_cats_net_ip_addresses_list__id
                              INNER JOIN isys_catg_ip_list_2_isys_catg_cluster_service_list ip2s ON ip2s.isys_catg_ip_list__id = ipl.isys_catg_ip_list__id
                              INNER JOIN isys_catg_cluster_service_list s ON s.isys_catg_cluster_service_list__id = ip2s.isys_catg_cluster_service_list__id',
                        'isys_catg_cluster_service_list',
                        's.isys_catg_cluster_service_list__id',
                        's.isys_catg_cluster_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['s.isys_catg_cluster_service_list__id'])
                    ),
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CLUSTER_SERVICE__HOSTADDRESSES',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection' => true,
                        // @todo Property Callback for multiedit (in future).
                        'p_strPopupType' => 'browser_cat_data',
                        'dataretrieval'  => 'isys_cmdb_dao_category_g_ip::catdata_browser'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'cluster_hostaddress'
                    ],
                    C__PROPERTY__FORMAT__REQUIRES => 'cluster_service'
                ]
            ]),
            'drives'                   => (new ObjectBrowserMultiselectProperty(
                'C__CMDB__CATG__CLUSTER_SERVICE__DRIVES',
                'LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES',
                'isys_catg_cluster_service_list__id',
                'isys_catg_cluster_service_list',
                'isys_catg_drive_list_2_isys_catg_cluster_service_list',
                '',
                'isys_catg_drive_list',
                [
                    'isys_export_helper',
                    'cluster_drives'
                ],
                '',
                'CONCAT(ref.isys_catg_drive_list__title, \' (\', ref.isys_catg_drive_list__driveletter, \') \')'
            ))->mergePropertyUiParams(
                [
                    'p_strPopupType' => 'browser_cat_data',
                    isys_popup_browser_object_ng::C__DATARETRIEVAL  => 'isys_cmdb_dao_category_g_drive::catdata_browser'
                ]
            )->setPropertyFormatOffset(
                \idoit\Component\Property\Property::C__PROPERTY__FORMAT__REQUIRES,
                'cluster_service'
            )->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT  => false,
                C__PROPERTY__PROVIDES__VIRTUAL => true
            ]),
            'shares'                   => (new ObjectBrowserMultiselectProperty(
                'C__CMDB__CATG__CLUSTER_SERVICE__SHARES',
                'LC__CMDB__CATG__CLUSTER_SERVICE__SHARES',
                'isys_catg_cluster_service_list__isys_obj__id',
                'isys_catg_cluster_service_list',
                'isys_catg_shares_list_2_isys_catg_cluster_service_list',
                '',
                'isys_catg_shares_list',
                [
                    'isys_export_helper',
                    'cluster_shares'
                ],
                '',
                'CONCAT(ref.isys_catg_shares_list__title, \' (\', ref.isys_catg_shares_list__unc_path, \') \')'
            ))->mergePropertyUiParams(
                [
                    'p_strPopupType' => 'browser_cat_data',
                    isys_popup_browser_object_ng::C__DATARETRIEVAL  => 'isys_cmdb_dao_category_g_shares::catdata_browser'
                ]
            )->setPropertyFormatOffset(
                \idoit\Component\Property\Property::C__PROPERTY__FORMAT__REQUIRES,
                'cluster_service'
            )->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT  => false,
                C__PROPERTY__PROVIDES__VIRTUAL => true
            ]),
            'runs_on'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_list(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
                    C__PROPERTY__INFO__DESCRIPTION => 'Runs on'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_service_list__id',
                    C__PROPERTY__DATA__SELECT => \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(cs.isys_obj__title, \' {\', cs.isys_obj__id, \'}\', \' >> \', o.isys_obj__title, \' {\', o.isys_obj__id, \'}\')
                            FROM isys_catg_cluster_service_list AS c
                              INNER JOIN isys_connection AS csc ON csc.isys_connection__id = c.isys_catg_cluster_service_list__isys_connection__id
                              INNER JOIN isys_obj AS cs ON cs.isys_obj__id = csc.isys_connection__isys_obj__id
                              INNER JOIN isys_catg_cluster_members_list_2_isys_catg_cluster_service_list AS cm ON cm.isys_catg_cluster_service_list__id = c.isys_catg_cluster_service_list__id
                              INNER JOIN isys_catg_cluster_members_list AS m ON m.isys_catg_cluster_members_list__id = cm.isys_catg_cluster_members_list__id
                              INNER JOIN isys_connection AS con ON con.isys_connection__id = m.isys_catg_cluster_members_list__isys_connection__id
                              INNER JOIN isys_obj AS o ON o.isys_obj__id = con.isys_connection__isys_obj__id',
                        'isys_catg_cluster_service_list',
                        'c.isys_catg_cluster_service_list__id',
                        'c.isys_catg_cluster_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['c.isys_catg_cluster_service_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID           => 'C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
                    C__PROPERTY__UI__EMPTYMESSAGE => 'LC__CMDB__CATG__CLUSTER_SERVICE__NO_MEMBERS',
                    C__PROPERTY__UI__PARAMS       => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_cluster_service',
                            'callback_property_runs_on'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'cluster_runs_on'
                    ]
                ]
            ]),
            'default_server'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Default server'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_service_list__cluster_members_list__id',
                    C__PROPERTY__DATA__SELECT => \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT o.isys_obj__title
                            FROM isys_catg_cluster_service_list AS c
                              INNER JOIN isys_catg_cluster_members_list AS m ON m.isys_catg_cluster_members_list__id = c.isys_catg_cluster_service_list__cluster_members_list__id
                              INNER JOIN isys_connection AS con ON con.isys_connection__id = m.isys_catg_cluster_members_list__isys_connection__id
                              INNER JOIN isys_obj AS o ON o.isys_obj__id = con.isys_connection__isys_obj__id',
                        'isys_catg_cluster_service_list',
                        'c.isys_catg_cluster_service_list__id',
                        'c.isys_catg_cluster_service_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__INDEX  => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => new isys_callback([
                            'isys_cmdb_dao_category_g_cluster_service',
                            'callback_property_default_server'
                        ]),
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'cluster_service_connection'
                    ]
                ]
            ]),
            'assigned_database_schema' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__DATABASE_SCHEMA',
                    C__PROPERTY__INFO__DESCRIPTION => 'The assigned database schema for the application'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_cluster_service_list__isys_catg_relation_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_database_access_list',
                        'isys_cats_database_access_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_cluster_service_list
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_cluster_service_list__isys_catg_relation_list__id
                                INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_catg_relation_list__isys_obj__id
                                INNER JOIN isys_cats_database_access_list ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_access_list__isys_obj__id',
                        'isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list__id',
                        'isys_catg_cluster_service_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        \idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cluster_service_list',
                            'LEFT',
                            'isys_catg_cluster_service_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        \idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_relation_list',
                            'LEFT',
                            'isys_catg_cluster_service_list__isys_catg_relation_list__id',
                            'isys_catg_relation_list__id'
                        ),
                        \idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_relation_list__isys_obj__id',
                            'isys_connection__isys_obj__id'
                        ),
                        \idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_database_access_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_cats_database_access_list__isys_connection__id'
                        ),
                        \idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_database_access_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CLUSTER_SERVICE_DATABASE_SCHEMATA',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATS__DATABASE_SCHEMA',
                        'p_strPopupType'                            => 'browser_object_ng',
                        'p_strValue'                                => new isys_callback([
                            'isys_cmdb_dao_category_g_cluster_service',
                            'callback_property_assigned_database_schema'
                        ]),
                        'p_strSelectedID'                           => ''
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                    C__PROPERTY__PROVIDES__VIRTUAL   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_application_export_helper',
                        'clusterServiceDatabaseSchema'
                    ]
                ]
            ]),
            'service_status'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Status of the cluster service assignment'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_service_list__service_status',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE 
                                WHEN isys_catg_cluster_service_list__service_status = ' . $this->convert_sql_int(self::C__CLUSTER_SERVICE__STATUS_RUNNING) . ' 
                                THEN ' . $this->convert_sql_text('LC__CATS__CLUSERT_SERVICE__STATUS_ENABLED') . '
                                ELSE ' . $this->convert_sql_text('LC__CATS__CLUSERT_SERVICE__STATUS_DISABLED') . ' 
                            END) AS cluster_service_status
                            FROM isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list__id',
                        'isys_catg_cluster_service_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_cluster_service_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_cluster_service_list__isys_connection__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__CLUSTER_SERVICE__SERVICE_STATUS',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData' => self::getServiceStatusLabels()
                    ],
                    C__PROPERTY__UI__DEFAULT => self::C__CLUSTER_SERVICE__STATUS_RUNNING
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_cluster_service_export_helper',
                        'clusterServiceStatus'
                    ]
                ]
            ]),
            'description'              => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cluster_service_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_cluster_service_list__description FROM isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list',
                        'isys_catg_cluster_service_list__id',
                        'isys_catg_cluster_service_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cluster_service_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CLUSTER_SERVICE', 'C__CATG__CLUSTER_SERVICE')
                ]
            ])
        ];
    }

    /**
     * Get Cluster Service Status dialog content
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function getServiceStatusLabels()
    {
        return [
            isys_cmdb_dao_category_g_cluster_service::C__CLUSTER_SERVICE__STATUS_DISABLED => 'LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS__DISABLED',
            isys_cmdb_dao_category_g_cluster_service::C__CLUSTER_SERVICE__STATUS_RUNNING  => 'LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS__RUNNING'
        ];
    }

    /**
     * Get specific service status
     *
     * @param $serviceStatus
     *
     * @return mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function getServiceStatus($serviceStatus)
    {
        $serviceStatusData = isys_cmdb_dao_category_g_cluster_service::getServiceStatusLabels();

        return $serviceStatusData[$serviceStatus];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['cluster_service'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['runs_on'][C__DATA__VALUE],
                            $p_category_data['properties']['default_server'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['hostaddresses'][C__DATA__VALUE],
                            $p_category_data['properties']['drives'][C__DATA__VALUE],
                            $p_category_data['properties']['shares'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                            $p_category_data['properties']['service_status'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['cluster_service'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['runs_on'][C__DATA__VALUE],
                            $p_category_data['properties']['default_server'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['hostaddresses'][C__DATA__VALUE],
                            $p_category_data['properties']['drives'][C__DATA__VALUE],
                            $p_category_data['properties']['shares'][C__DATA__VALUE],
                            $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                            $p_category_data['properties']['service_status'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * @param $p_cluster_member_id
     *
     * @return null
     */
    public function get_member_object_by_cluster_member_id($p_cluster_member_id)
    {
        $l_dao = new isys_cmdb_dao_category_g_cluster_members($this->m_db);
        $l_data = $l_dao->get_data($p_cluster_member_id)
            ->__to_array();

        if ($l_data) {
            return $l_data["isys_connection__isys_obj__id"];
        }

        return null;
    }

    /**
     * Save global category backup element.
     *
     * @param   integer $p_cat_level
     * @param           $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @throws Exception
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_ret = null;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_cluster_service_list__status"];

        $l_id = $l_catdata['isys_catg_cluster_service_list__id'];

        $l_addresses = null;
        $l_shares = null;
        $l_drives = null;

        if (!empty($_POST['C__CMDB__CATG__CLUSTER_SERVICE__DRIVES__HIDDEN'])) {
            $l_drives = isys_format_json::decode($_POST['C__CMDB__CATG__CLUSTER_SERVICE__DRIVES__HIDDEN']);
        }

        if (!empty($_POST['C__CMDB__CATG__CLUSTER_SERVICE__SHARES__HIDDEN'])) {
            $l_shares = isys_format_json::decode($_POST['C__CMDB__CATG__CLUSTER_SERVICE__SHARES__HIDDEN']);
        }

        if (!empty($_POST['C__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES__HIDDEN'])) {
            $l_addresses = isys_format_json::decode($_POST['C__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES__HIDDEN']);
        }

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION__HIDDEN'],
                $_POST['C__CMDB__CATG__CLUSTER_SERVICE__TYPE'],
                $_POST['C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON__selected_values'],
                $_POST['C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                $l_addresses,
                $l_drives,
                $l_shares,
                $_POST["C__CATG__CLUSTER_SERVICE_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS"]
            );

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }

            $p_cat_level = null;

            return $l_id;
        } else {
            if ($l_catdata['isys_catg_cluster_service_list__id'] != "") {
                $l_ret = $this->save(
                    $l_id,
                    $_POST['C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION__HIDDEN'],
                    $_POST['C__CMDB__CATG__CLUSTER_SERVICE__TYPE'],
                    $_POST['C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON__selected_values'],
                    $_POST['C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $p_intOldRecStatus,
                    $l_addresses,
                    $l_drives,
                    $l_shares,
                    $_POST["C__CATG__CLUSTER_SERVICE_DATABASE_SCHEMATA__HIDDEN"],
                    $_POST["C__CATG__CLUSTER_SERVICE__SERVICE_STATUS"]
                );

                $this->m_strLogbookSQL = $this->get_last_query();
            }
        }

        return $l_ret;
    }

    /**
     * Build host address
     *
     * @param $p_id
     * @param $p_hostaddress_id
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function bind_hostaddress($p_id, $p_hostaddress_id)
    {
        if ($p_id > 0 && (is_numeric($p_hostaddress_id) && $p_hostaddress_id > 0)) {
            if ($this->update("DELETE FROM isys_catg_ip_list_2_isys_catg_cluster_service_list " . "WHERE " . "isys_catg_cluster_service_list__id = " .
                    $this->convert_sql_id($p_id) . " AND " . "isys_catg_ip_list__id = " . $this->convert_sql_id($p_hostaddress_id) . ";") && $this->apply_update()) {
                $l_sql = "INSERT INTO isys_catg_ip_list_2_isys_catg_cluster_service_list SET " . "isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_id) .
                    ", " . "isys_catg_ip_list__id = " . $this->convert_sql_id($p_hostaddress_id) . ";";

                if ($this->update($l_sql) && $this->apply_update()) {
                    return $this->get_last_insert_id();
                }
            }
        }

        return false;
    }

    /**
     * Detach all members
     *
     * @param   integer $p_cluster_service_id
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function detach_members($p_cluster_service_id)
    {
        $l_delete = 'DELETE FROM isys_catg_cluster_members_list_2_isys_catg_cluster_service_list WHERE isys_catg_cluster_service_list__id = ' .
            $this->convert_sql_id($p_cluster_service_id) . ';';

        return ($this->update($l_delete) && $this->apply_update());
    }

    /**
     * Detachh all addresses
     *
     * @param   integer $p_cluster_service_id
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function detach_addresses($p_cluster_service_id)
    {
        $l_delete = 'DELETE FROM isys_catg_ip_list_2_isys_catg_cluster_service_list WHERE isys_catg_cluster_service_list__id = ' .
            $this->convert_sql_id($p_cluster_service_id) . ';';

        return ($this->update($l_delete) && $this->apply_update());
    }

    /**
     * Detach all drives
     *
     * @param   integer $p_cluster_service_id
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function detach_drives($p_cluster_service_id)
    {
        $l_delete = 'DELETE FROM isys_catg_drive_list_2_isys_catg_cluster_service_list WHERE isys_catg_cluster_service_list__id = ' .
            $this->convert_sql_id($p_cluster_service_id) . ';';

        return ($this->update($l_delete) && $this->apply_update());
    }

    /**
     * Detach all shares
     *
     * @param   integer $p_cluster_service_id
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function detach_shares($p_cluster_service_id)
    {
        $l_delete = 'DELETE FROM isys_catg_shares_list_2_isys_catg_cluster_service_list WHERE isys_catg_cluster_service_list__id = ' .
            $this->convert_sql_id($p_cluster_service_id) . ';';

        return ($this->update($l_delete) && $this->apply_update());
    }

    /**
     * Bind share
     *
     * @param $p_id
     * @param $p_shares_id
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function bind_share($p_id, $p_shares_id)
    {
        if ($p_id > 0 && $p_shares_id > 0) {
            $l_sql = 'DELETE FROM isys_catg_shares_list_2_isys_catg_cluster_service_list
				WHERE isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($p_id) . '
				AND isys_catg_shares_list__id = ' . $this->convert_sql_id($p_shares_id) . ';';

            if ($this->update($l_sql) && $this->apply_update()) {
                $l_sql = "INSERT INTO isys_catg_shares_list_2_isys_catg_cluster_service_list
					SET isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_id) . ",
					isys_catg_shares_list__id = " . $this->convert_sql_id($p_shares_id) . ";";

                if ($this->update($l_sql) && $this->apply_update()) {
                    return $this->get_last_insert_id();
                }
            }
        }

        return false;
    }

    /**
     * Specify cluster that runs cluster service
     *
     * @param $p_cluster_service_list_id
     * @param $p_cluster_member_id
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function cluster_runs_on($p_cluster_service_list_id, $p_cluster_member_id)
    {
        if ($p_cluster_member_id > 0 && $p_cluster_service_list_id > 0) {
            // Check if entry already exists
            $l_sql = "SELECT * FROM isys_catg_cluster_members_list_2_isys_catg_cluster_service_list WHERE " . "isys_catg_cluster_members_list__id = " .
                $this->convert_sql_id($p_cluster_member_id) . " " . "AND isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_list_id) . ";";
            $res = $this->retrieve($l_sql);
            if (!is_countable($res) || count($res) === 0) {
                $l_sql = "INSERT INTO isys_catg_cluster_members_list_2_isys_catg_cluster_service_list " . "SET " . "isys_catg_cluster_members_list__id = " .
                    $this->convert_sql_id($p_cluster_member_id) . ", " . "isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_list_id) . ";";

                return ($this->update($l_sql) && $this->apply_update());
            }

            return true;
        }

        return false;
    }

    /**
     * Get cluster members
     *
     * @param      $p_cluster_service_list_id
     * @param null $p_cluster_members_list_id
     * @param null $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_cluster_members($p_cluster_service_list_id, $p_cluster_members_list_id = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_members_list_2_isys_catg_cluster_service_list " . "INNER JOIN isys_catg_cluster_members_list ON " .
            "isys_catg_cluster_members_list.isys_catg_cluster_members_list__id = " .
            "isys_catg_cluster_members_list_2_isys_catg_cluster_service_list.isys_catg_cluster_members_list__id " . "INNER JOIN isys_connection ON " .
            "isys_catg_cluster_members_list__isys_connection__id = isys_connection__id " . "INNER JOIN isys_obj ON " .
            "isys_connection__isys_obj__id = isys_obj.isys_obj__id " . "INNER JOIN isys_obj_type ON " . "isys_obj_type__id = isys_obj.isys_obj__isys_obj_type__id " .
            "WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_list_id);

        if (!is_null($p_cluster_members_list_id)) {
            $l_sql .= " AND isys_catg_cluster_members_list.isys_catg_cluster_members_list__id = " . $this->convert_sql_id($p_cluster_members_list_id);
        }

        if ($p_status) {
            $l_sql .= " AND isys_obj__status = " . $this->convert_sql_id($p_status);
        }

        $l_sql .= " AND isys_catg_cluster_members_list__status = '" . C__RECORD_STATUS__NORMAL . "'";

        return $this->retrieve($l_sql);
    }

    /**
     * Get cluster addresses
     *
     * @param      $p_cluster_service_list_id
     * @param null $p_catg_ip_list_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_cluster_addresses($p_cluster_service_list_id, $p_catg_ip_list_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_ip_list_2_isys_catg_cluster_service_list " . "INNER JOIN isys_catg_ip_list ON " .
            "isys_catg_ip_list_2_isys_catg_cluster_service_list.isys_catg_ip_list__id = isys_catg_ip_list.isys_catg_ip_list__id " .
            "INNER JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id " .
            "WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_list_id);

        if (!is_null($p_catg_ip_list_id)) {
            $l_sql .= " AND isys_catg_ip_list.isys_catg_ip_list__id = " . $this->convert_sql_id($p_catg_ip_list_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Get cluster drives
     *
     * @param      $p_cluster_service_list_id
     * @param null $p_catg_drive_list_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_cluster_drives($p_cluster_service_list_id, $p_catg_drive_list_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_drive_list_2_isys_catg_cluster_service_list " . "INNER JOIN isys_catg_drive_list ON " .
            "isys_catg_drive_list_2_isys_catg_cluster_service_list.isys_catg_drive_list__id = isys_catg_drive_list.isys_catg_drive_list__id " .
            "WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_list_id);

        if (!is_null($p_catg_drive_list_id)) {
            $l_sql .= " AND isys_catg_drive_list.isys_catg_drive_list__id = " . $this->convert_sql_id($p_catg_drive_list_id);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Get cluster shares
     *
     * @param      $p_cluster_service_list_id
     * @param null $p_catg_shares_list_id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_cluster_shares($p_cluster_service_list_id, $p_catg_shares_list_id = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_shares_list_2_isys_catg_cluster_service_list
			INNER JOIN isys_catg_shares_list ON isys_catg_shares_list_2_isys_catg_cluster_service_list.isys_catg_shares_list__id = isys_catg_shares_list.isys_catg_shares_list__id
			WHERE isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($p_cluster_service_list_id);

        if (!is_null($p_catg_shares_list_id)) {
            $l_sql .= ' AND isys_catg_shares_list.isys_catg_shares_list__id = ' . $this->convert_sql_id($p_catg_shares_list_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Bind drive
     *
     * @param $p_id
     * @param $p_drive_id
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function bind_drive($p_id, $p_drive_id)
    {
        if ($p_id > 0 && (is_numeric($p_drive_id) && $p_drive_id > 0)) {
            if ($this->update("DELETE FROM isys_catg_drive_list_2_isys_catg_cluster_service_list " . "WHERE " . "isys_catg_cluster_service_list__id = " .
                    $this->convert_sql_id($p_id) . " AND " . "isys_catg_drive_list__id = " . $this->convert_sql_id($p_drive_id) . ";") && $this->apply_update()) {
                $l_sql = "INSERT INTO isys_catg_drive_list_2_isys_catg_cluster_service_list SET " . "isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_id) .
                    ", " . "isys_catg_drive_list__id = " . $this->convert_sql_id($p_drive_id) . ";";

                if ($this->update($l_sql) && $this->apply_update()) {
                    return $this->get_last_insert_id();
                }
            }
        }

        return false;
    }

    /**
     * Prepares an object title for the automatically generated parallel relation
     *
     * @param     $p_cluster_service_title
     * @param int $p_cluster_type
     *
     * @return string
     * @throws isys_exception_database
     * @throws Exception
     */
    public function prepare_relpool_title($p_cluster_service_title, $p_cluster_type)
    {
        $l_cluster_type = $this->get_dialog("isys_cluster_type", $p_cluster_type)
            ->__to_array();

        return $p_cluster_service_title . " (" . isys_application::instance()->container->get('language')
                ->get($l_cluster_type["isys_cluster_type__title"]) . ")";
    }

    /**
     * Save category entry
     *
     * @param   integer $p_id
     * @param   int|array   $p_application_id
     * @param   integer $p_type
     * @param   string  $p_runs_on Comma separated IDs
     * @param   integer $p_default_server
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   mixed   $p_addresses
     * @param   mixed   $p_drives
     * @param   mixed   $p_shares
     * @param   integer $p_database_schemata_obj
     *
     * @param null      $serviceStatus
     *
     * @return  boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save(
        $p_id,
        $p_application_id,
        $p_type,
        $p_runs_on,
        $p_default_server,
        $p_description,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_addresses = null,
        $p_drives = null,
        $p_shares = null,
        $p_database_schemata_obj = null,
        $serviceStatus = null
    ) {
        if (!is_null($p_runs_on)) {
            $this->detach_members($p_id);

            if (!is_array($p_runs_on)) {
                $p_runs_on = explode(",", $p_runs_on);
            }
            foreach ($p_runs_on as $l_server) {
                $this->cluster_runs_on($p_id, $l_server);
            }

            /**
             * Create or update the parallel relation
             */
            $l_title = $this->prepare_relpool_title($this->get_obj_name_by_id_as_string($p_application_id), $p_type);

            $this->handle_relationpool($p_runs_on, $p_id, $l_title);
        }

        $l_strSql = "UPDATE isys_catg_cluster_service_list " . "INNER JOIN isys_connection " . "ON " . "isys_catg_cluster_service_list__isys_connection__id = " .
            "isys_connection__id " . "SET " . "isys_connection__isys_obj__id = " . $this->convert_sql_id($p_application_id) . ", " .
            "isys_catg_cluster_service_list__isys_cluster_type__id = " . $this->convert_sql_id($p_type) . ", " .
            "isys_catg_cluster_service_list__cluster_members_list__id = " . $this->convert_sql_id($p_default_server) . ", " .
            "isys_catg_cluster_service_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_catg_cluster_service_list__status = " .
            $this->convert_sql_int($p_status) . ", " . "isys_catg_cluster_service_list__service_status = " . $this->convert_sql_int($serviceStatus) . " " .
            "WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_id);

        if (!$this->update($l_strSql) || !$this->apply_update()) {
            return false;
        }

        $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_data = $this->get_data($p_id)
            ->__to_array();

        $l_relation_dao->handle_relation(
            $p_id,
            'isys_catg_cluster_service_list',
            defined_or_default('C__RELATION_TYPE__CLUSTER_SERVICE'),
            $l_data['isys_catg_cluster_service_list__isys_catg_relation_list__id'],
            $l_data['isys_catg_cluster_service_list__isys_obj__id'],
            $p_application_id
        );

        if ($p_application_id > 0) {
            // Link DBMS
            self::handle_dbms($l_data['isys_catg_cluster_service_list__isys_catg_relation_list__id'], $p_database_schemata_obj);
        }

        // Host addresses:
        if (isset($p_addresses)) {
            if (is_array($p_addresses)) {
                $l_addresses = $p_addresses;
            } elseif (is_string($p_addresses)) {
                $l_addresses = explode(",", $p_addresses);
            } else {
                $l_addresses = false;
            }

            $this->detach_addresses($p_id);

            if ($l_addresses) {
                foreach ($l_addresses as $l_value) {
                    $this->bind_hostaddress($p_id, $l_value);
                }
            }
        }

        // Drives:
        if (isset($p_drives)) {
            if (is_array($p_drives)) {
                $l_drives = $p_drives;
            } elseif (is_string($p_drives)) {
                $l_drives = explode(",", $p_drives);
            } else {
                $l_drives = false;
            }

            $this->detach_drives($p_id);

            if ($l_drives) {
                foreach ($l_drives as $l_value) {
                    $this->bind_drive($p_id, $l_value);
                }
            }
        }

        // Shares:
        if (isset($p_shares)) {
            if (is_array($p_shares)) {
                $l_shares = $p_shares;
            } elseif (is_string($p_shares)) {
                $l_shares = explode(",", $p_shares);
            } else {
                $l_shares = false;
            }

            $this->detach_shares($p_id);

            if ($l_shares) {
                foreach ($l_shares as $l_value) {
                    $this->bind_share($p_id, $l_value);
                }
            }
        }

        return true;
    }

    /**
     * Create.
     *
     * @param   integer $p_objID
     * @param   integer $p_application_id
     * @param   integer $p_type
     * @param   string  $p_runs_on
     * @param   integer $p_default_server
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   mixed   $p_addresses
     * @param   mixed   $p_drives
     * @param   mixed   $p_shares
     * @param   integer $p_database_schemata_obj
     *
     * @param null      $serviceStatus
     *
     * @return  mixed
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function create(
        $p_objID,
        $p_application_id,
        $p_type,
        $p_runs_on,
        $p_default_server,
        $p_description,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_addresses = null,
        $p_drives = null,
        $p_shares = null,
        $p_database_schemata_obj = null,
        $serviceStatus = null
    ) {
        if (empty($p_status)) {
            $p_status = C__RECORD_STATUS__NORMAL;
        }

        $l_connection = isys_cmdb_dao_connection::instance($this->m_db)
            ->add_connection($p_application_id);

        $l_strSql = "INSERT INTO isys_catg_cluster_service_list SET
            isys_catg_cluster_service_list__isys_connection__id = " . $this->convert_sql_id($l_connection) . ",
            isys_catg_cluster_service_list__isys_cluster_type__id = " . $this->convert_sql_id($p_type) . ",
            isys_catg_cluster_service_list__cluster_members_list__id = " . $this->convert_sql_id($p_default_server) . ",
            isys_catg_cluster_service_list__description = " . $this->convert_sql_text($p_description) . ",
            isys_catg_cluster_service_list__status = " . $this->convert_sql_int($p_status) . ",
            isys_catg_cluster_service_list__service_status = " . $this->convert_sql_int($serviceStatus) . ",
            isys_catg_cluster_service_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if (!$this->update($l_strSql) || !$this->apply_update()) {
            return false;
        }

        $l_id = $this->get_last_insert_id();

        if (!is_null($p_runs_on)) {
            if (!is_array($p_runs_on)) {
                $p_runs_on = explode(",", $p_runs_on);
            }

            foreach ($p_runs_on as $l_server) {
                $this->cluster_runs_on($l_id, $l_server);
            }

            // Create or update the parallel relation.
            $l_title = $this->prepare_relpool_title($this->get_obj_name_by_id_as_string($p_application_id), $p_type);

            $this->handle_relationpool($p_runs_on, $l_id, $l_title);
        }

        isys_cmdb_dao_category_g_relation::instance($this->m_db)
            ->handle_relation($l_id, "isys_catg_cluster_service_list", defined_or_default('C__RELATION_TYPE__CLUSTER_SERVICE'), null, $p_objID, $p_application_id);

        // Handle database schemata
        if ($p_application_id > 0) {
            // Link DBMS
            self::handle_dbms($this->get_data($l_id)
                ->get_row_value('isys_catg_cluster_service_list__isys_catg_relation_list__id'), $p_database_schemata_obj);
        }

        // Host addresses:

        if (isset($p_addresses)) {
            if (is_array($p_addresses)) {
                $l_addresses = $p_addresses;
            } elseif (is_string($p_addresses)) {
                $l_addresses = explode(",", $p_addresses);
            } else {
                $l_addresses = false;
            }

            if ($l_addresses) {
                foreach ($l_addresses as $l_value) {
                    $this->bind_hostaddress($l_id, $l_value);
                }
            }
        }

        // Drives:
        if (isset($p_drives)) {
            if (is_array($p_drives)) {
                $l_drives = $p_drives;
            } elseif (is_string($p_drives)) {
                $l_drives = explode(",", $p_drives);
            } else {
                $l_drives = false;
            }

            if ($l_drives) {
                foreach ($l_drives as $l_value) {
                    $this->bind_drive($l_id, $l_value);
                }
            }
        }

        // Shares:
        if (isset($p_shares)) {
            if (is_array($p_shares)) {
                $l_shares = $p_shares;
            } elseif (is_string($p_shares)) {
                $l_shares = explode(",", $p_shares);
            } else {
                $l_shares = false;
            }

            if ($l_shares) {
                foreach ($l_shares as $l_value) {
                    $this->bind_share($l_id, $l_value);
                }
            }
        }

        return $l_id;
    }

    /**
     * Handles the creation of a relation pool (and a parallel relation object)
     *
     * @param array $p_poolobjects
     * @param int   $p_cluster_service_id
     * @param       $p_relation_title
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function handle_relationpool($p_poolobjects, $p_cluster_service_id, $p_relation_title)
    {
        if ($p_cluster_service_id > 0) {
            $l_dao_member = new isys_cmdb_dao_category_g_cluster_members($this->get_database_component());
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_dao_relpool = new isys_cmdb_dao_category_s_parallel_relation($this->get_database_component());

            $l_relationpool = $this->get_relation_pool_by_id($p_cluster_service_id, $p_relation_title);

            if ($l_relationpool > 0) {
                if ($l_dao_relpool->clear($l_relationpool)) {
                    if (is_array($p_poolobjects)) {
                        foreach ($p_poolobjects as $l_member) {
                            if ($l_member > 0) {
                                $l_memberdata = $l_dao_member->get_data($l_member)
                                    ->__to_array();

                                if ($l_memberdata["isys_catg_cluster_members_list__isys_catg_relation_list__id"] > 0) {
                                    $l_relationtmp = $l_dao_relation->get_data_by_id($l_memberdata["isys_catg_cluster_members_list__isys_catg_relation_list__id"])
                                        ->__to_array();

                                    $l_dao_relpool->attach_relation($l_relationpool, $l_relationtmp["isys_obj__id"]);
                                }
                            }
                        }
                    }
                }

                // Saves relpool ID.
                $this->update("UPDATE isys_catg_cluster_service_list
                    SET isys_catg_cluster_service_list__isys_cats_relpool_list__id = " . $this->convert_sql_id($l_relationpool) . "
                    WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cluster_service_id) . ";");
            }

            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Deletes connection between cluster and cluster service.
     *
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function delete($p_cat_level)
    {
        $this->detach_addresses($p_cat_level);
        $this->detach_drives($p_cat_level);
        $this->detach_members($p_cat_level);
        $this->detach_shares($p_cat_level);

        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        $l_sql = "DELETE FROM isys_catg_cluster_service_list WHERE isys_catg_cluster_service_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        $l_dao_relation->delete_relation($this->get_data($p_cat_level)
            ->get_row_value('isys_catg_cluster_service_list__isys_catg_relation_list__id'));

        if ($this->update($l_sql) && $this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not delete id '{$p_cat_level}' in table isys_catg_cluster_service_list.");
        }
    }

    /**
     * Sets status.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     *
     * @return  boolean
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_status($p_cat_level, $p_status)
    {
        $l_sql = 'UPDATE isys_catg_cluster_service_list
			SET isys_catg_cluster_service_list__status = ' . $this->convert_sql_int($p_status) . '
			WHERE isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not change status for id '{$p_cat_level}' in table isys_catg_cluster_service_list.");
        }
    }

    /**
     * Get assigned objects and relations
     *
     * @param   integer $p_cat_id
     * @param   integer $p_assigned_obj__id
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_assigned_objects_and_relations($p_cat_id = null, $p_assigned_obj__id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'SELECT isys_catg_cluster_service_list__id, main.isys_obj__id AS main_obj_id, main.isys_obj__title AS main_obj_title, rel_object.isys_obj__id AS rel_obj_id, rel_object.isys_obj__title AS rel_obj_title
			FROM isys_catg_cluster_service_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_service_list__isys_connection__id
			INNER JOIN isys_obj AS main ON isys_catg_cluster_service_list__isys_obj__id = main.isys_obj__id
			INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_cluster_service_list__isys_catg_relation_list__id
			INNER JOIN isys_obj AS rel_object ON isys_catg_relation_list__isys_obj__id = rel_object.isys_obj__id
			WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_assigned_obj__id);

        if ($p_status) {
            $l_sql .= ' AND isys_catg_cluster_service_list__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_cat_id !== null) {
            $l_sql .= ' AND isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($p_cat_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Checks if a relation pool exists. Creates one if not.
     *
     * @param   integer $p_cluster_service_id
     * @param   string  $p_relation_title
     * @param   boolean $p_create_if_not_exists
     *
     * @return  integer
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function get_relation_pool_by_id($p_cluster_service_id, $p_relation_title, $p_create_if_not_exists = true)
    {
        $l_sql = 'SELECT isys_catg_cluster_service_list__isys_cats_relpool_list__id, isys_cats_relpool_list__threshold, isys_cats_relpool_list__description
			FROM isys_catg_cluster_service_list
			INNER JOIN isys_cats_relpool_list ON isys_cats_relpool_list__id = isys_catg_cluster_service_list__isys_cats_relpool_list__id
			WHERE isys_catg_cluster_service_list__id = ' . $this->convert_sql_id($p_cluster_service_id) . ';';

        $l_ret = $this->retrieve($l_sql)
            ->get_row();

        $l_dao_relpool = isys_cmdb_dao_category_s_parallel_relation::instance($this->m_db);

        if ($l_ret["isys_catg_cluster_service_list__isys_cats_relpool_list__id"] > 0) {
            $l_dao_relpool->save(
                $l_ret["isys_catg_cluster_service_list__isys_cats_relpool_list__id"],
                $p_relation_title,
                $l_ret["isys_cats_relpool_list__threshold"],
                $l_ret["isys_cats_relpool_list__description"]
            );

            return $l_ret["isys_catg_cluster_service_list__isys_cats_relpool_list__id"];
        } else {
            if ($p_create_if_not_exists && defined('C__OBJTYPE__PARALLEL_RELATION')) {
                return $l_dao_relpool->create($this->create_object($p_relation_title, C__OBJTYPE__PARALLEL_RELATION), $p_relation_title, "0", "");
            }
        }

        return false;
    }

    /**
     * Set category entries status based
     * on object status.
     *
     * This method is listening on `mod.cmdb.afterObjectRank` and
     * registered in isys_module_cmdb.
     *
     * @see ID-5543
     *
     * @param isys_cmdb_dao $cmdbDao
     * @param               $direction
     * @param array         $objectIds
     *
     * @return bool
     */
    public static function assignmentStatusTransition(isys_cmdb_dao $cmdbDao, $direction, array $objectIds)
    {
        try {
            // Check whether objects were provided
            if (!empty($objectIds)) {
                // Get status of an object from delivered set
                $targetStatus = $cmdbDao->get_object_status_by_id(reset($objectIds));

                if (!empty($targetStatus)) {
                    // Sanitize object ids
                    $objectIds = array_filter(array_map(function ($objectId) use ($cmdbDao) {
                        return is_numeric($objectId) ? $cmdbDao->convert_sql_id($objectId) : null;
                    }, $objectIds), function ($objectId) {
                        return !empty($objectId);
                    });

                    // Check whether valid object ids exists after clean up
                    if (!empty($objectIds)) {
                        // Build sql for transition target entries
                        $sql = '
                          UPDATE isys_catg_cluster_service_list SET
                                   isys_catg_cluster_service_list__status = ' . $cmdbDao->convert_sql_id($targetStatus) . '
                          WHERE  isys_catg_cluster_service_list__isys_obj__id IN(' . implode(',', $objectIds) . ');
                        ';

                        // Update it!
                        return $cmdbDao->update($sql) && $cmdbDao->apply_update();
                    }
                }
            }
        } catch (isys_exception_dao $e) {
            isys_notify::error('Unable to transition corresponding cluster service entries to the right status.');
        }

        return false;
    }
}
