<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for virtual machines
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_machine extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'virtual_machine';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_virtual_machine_list__isys_obj__id';

    /**
     * Dynamic property callback for property primary.
     * This method is used for object lists and for reports
     *
     * @param $p_row
     *
     * @return null|string
     * @throws isys_exception_general
     */
    public function dynamic_property_primary($p_row)
    {
        global $g_comp_database;
        $l_obj_title = null;
        if ($p_row['isys_catg_virtual_machine_list__primary'] > 0) {
            $l_dao = isys_cmdb_dao::instance($g_comp_database);

            $l_obj_title = $l_dao->get_obj_name_by_id_as_string($p_row['isys_catg_virtual_machine_list__primary']);
            if (count($p_row) > 1) {
                $l_quickinfo = new isys_ajax_handler_quick_info();
                $l_obj_title = $l_quickinfo->get_quick_info($p_row['isys_catg_virtual_machine_list__primary'], $l_obj_title, C__LINK__OBJECT);
            }
        }

        return $l_obj_title;
    }

    /**
     * Callback method for the storage host device dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_primary(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();
        $l_return = [];

        if ($l_obj_id > 0) {
            $l_dao_vm = isys_cmdb_dao_category_g_virtual_machine::instance($this->m_db);

            $l_host_obj_id = $l_dao_vm->get_host_system($l_obj_id);

            if ($l_dao_vm->get_objTypeID($l_host_obj_id) == defined_or_default('C__OBJTYPE__CLUSTER')) {
                $l_res = isys_cmdb_dao_category_g_cluster_members::instance($this->m_db)
                    ->get_assigned_members($l_host_obj_id);
                if ($l_res->num_rows() > 0) {
                    while ($l_row = $l_res->get_row()) {
                        $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
                    }
                }
            } else {
                $l_return = [];
            }
        }

        return $l_return;
    }

    /**
     * Callback method for "virtual machine" property.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function callback_property_virtual_machine(isys_request $p_request)
    {
        return [
            C__VM__NO    => 'LC__UNIVERSAL__NO',
            C__VM__GUEST => 'LC__UNIVERSAL__YES'
        ];
    }

    /**
     * Import-Handler for this category
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        $l_ids = [];

        if (is_countable($p_data) && count($p_data) > 0) {
            $l_status = -1;
            $l_cat = -1;

            $_POST["C__CATG__VM__VM"] = C__VM__GUEST;
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()] = $p_data["type"];

            $l_ids[] = $this->save_element($l_cat, $l_status);
        }

        return $l_ids;
    }

    /**
     * Create new virtual machine.
     *
     * @param   integer $p_object_id
     * @param   integer $p_parent_obj
     * @param   string  $p_virtual_machine
     * @param   string  $p_vm_type
     * @param   string  $p_title
     * @param   string  $p_config_file
     * @param   integer $p_status
     * @param   string  $p_description
     * @param   integer $p_primary
     *
     * @return  mixed
     */
    public function create(
        $p_object_id,
        $p_parent_obj = null,
        $p_virtual_machine,
        $p_vm_type = null,
        $p_title = "",
        $p_config_file = "",
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_description = "",
        $p_primary = null
    ) {
        /**
         * @var $l_connection isys_cmdb_dao_connection
         */
        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);

        if (!empty($p_primary) && $p_primary != -1) {
            $l_primary = (int)$p_primary;
        } else {
            $l_primary = null;
        }

        $l_sql = "INSERT IGNORE INTO isys_catg_virtual_machine_list SET
			isys_catg_virtual_machine_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_virtual_machine_list__config_file = " . $this->convert_sql_text($p_config_file) . ",
			isys_catg_virtual_machine_list__vm = " . $this->convert_sql_text($p_virtual_machine) . ",
			isys_catg_virtual_machine_list__isys_vm_type__id = " . $this->convert_sql_id($p_vm_type) . ",
			isys_catg_virtual_machine_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_parent_obj)) . ",
			isys_catg_virtual_machine_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_virtual_machine_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_virtual_machine_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
			isys_catg_virtual_machine_list__primary = " . $this->convert_sql_id($l_primary) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL = $l_sql;
            $l_last_id = $this->get_last_insert_id();

            if (!empty($p_parent_obj) && $p_parent_obj > 0) {
                // Create implicit relation
                isys_cmdb_dao_category_g_relation::instance($this->m_db)
                    ->handle_relation($l_last_id, "isys_catg_virtual_machine_list", defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'), null, $p_parent_obj, $p_object_id);
            }

            return $l_last_id;
        }

        return false;
    }

    /**
     * Updates an existing
     *
     * @param string $p_id
     */
    public function save(
        $p_id,
        $p_parent_obj,
        $p_virtual_machine,
        $p_vm_type = null,
        $p_title = "",
        $p_config_file = "",
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_description = "",
        $p_primary = null
    ) {
        if (is_numeric($p_id)) {
            $l_sql = 'SELECT * FROM isys_catg_virtual_machine_list WHERE isys_catg_virtual_machine_list__id = ' . $this->convert_sql_id($p_id);
            $l_data = $this->retrieve($l_sql)
                ->__to_array();

            if (!empty($p_primary) && $p_primary != -1) {
                $l_primary = intval($p_primary);
            } else {
                $l_primary = null;
            }

            if (empty($l_data['isys_catg_virtual_machine_list__isys_connection__id'])) {
                $l_connection = isys_cmdb_dao_connection::instance($this->m_db);
                $l_connection->attach_connection('isys_catg_virtual_machine_list', $p_id, null);
            }

            $l_sql = "UPDATE isys_catg_virtual_machine_list
				SET
				isys_catg_virtual_machine_list__isys_connection__id = " . $this->convert_sql_id($this->handle_connection($p_id, $p_parent_obj)) . ",
				isys_catg_virtual_machine_list__title = " . $this->convert_sql_text($p_title) . ",
				isys_catg_virtual_machine_list__config_file = " . $this->convert_sql_text($p_config_file) . ",
				isys_catg_virtual_machine_list__vm = " . $this->convert_sql_text($p_virtual_machine) . ",
				isys_catg_virtual_machine_list__isys_vm_type__id = " . $this->convert_sql_id($p_vm_type) . ",
				isys_catg_virtual_machine_list__status = " . $this->convert_sql_int($p_status) . ",
				isys_catg_virtual_machine_list__description = " . $this->convert_sql_text($p_description) . ",
				isys_catg_virtual_machine_list__primary = " . $this->convert_sql_id($l_primary) . "
				WHERE isys_catg_virtual_machine_list__id = " . $this->convert_sql_id($p_id) . ";";

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL = $l_sql;

                if ($this->apply_update()) {

                    /**
                     * Create implicit relation
                     *
                     * @var $l_relation_dao isys_cmdb_dao_category_g_relation
                     */
                    $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

                    // Delete relation if object is no virtual machine
                    if ($p_virtual_machine == C__VM__NO) {
                        $p_parent_obj = null;
                    }

                    $l_relation_dao->handle_relation(
                        $p_id,
                        "isys_catg_virtual_machine_list",
                        defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'),
                        $l_data["isys_catg_virtual_machine_list__isys_catg_relation_list__id"],
                        $p_parent_obj,
                        $l_data["isys_catg_virtual_machine_list__isys_obj__id"]
                    );

                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param   integer $p_cat_level
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->get_row();
        $p_status = $l_catdata["isys_catg_virtual_machine_list__status"];
        $l_list_id = $l_catdata["isys_catg_virtual_machine_list__id"];

        $l_status = C__RECORD_STATUS__NORMAL;

        if (empty($l_list_id)) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST["C__CATG__VM__OBJECT__HIDDEN"],
                $_POST["C__CATG__VM__VM"],
                $_POST["C__CATG__VM__SYSTEM"],
                $_POST["C__CATG__VIRTUAL_MACHINE__TITLE"],
                $_POST["C__CATG__VM__CONFIG_FILE"],
                $l_status,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CMDB__CATG__VIRTUAL_MACHINE_HOST"]
            );

            if ($l_id > 0) {
                $p_cat_level = 1;

                return $l_id;
            }
        } else {
            $l_success = $this->save(
                $l_list_id,
                $_POST["C__CATG__VM__OBJECT__HIDDEN"],
                $_POST["C__CATG__VM__VM"],
                $_POST["C__CATG__VM__SYSTEM"],
                $_POST["C__CATG__VIRTUAL_MACHINE__TITLE"],
                $_POST["C__CATG__VM__CONFIG_FILE"],
                $l_status,
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CMDB__CATG__VIRTUAL_MACHINE_HOST"]
            );

            if ($l_success) {
                return null;
            }
        }

        return false;
    }

    /**
     * Return guest objects.
     *
     * @param   integer $p_host_object_id
     *
     * @return  isys_component_dao_result
     */
    public function get_guests($p_host_object_id)
    {
        $l_sql = 'SELECT ovmg.isys_obj__title, ovmg.isys_obj__id, oso.isys_obj__title AS os_name, isys_obj_type__title, isys_cats_net_ip_addresses_list__title, oso.isys_obj__isys_obj_type__id, isys_catg_virtual_machine_list__id
			FROM isys_catg_virtual_machine_list vmg
			INNER JOIN isys_obj ovmg ON ovmg.isys_obj__id = vmg.isys_catg_virtual_machine_list__isys_obj__id
			INNER JOIN isys_obj_type ON ovmg.isys_obj__isys_obj_type__id = isys_obj_type__id
			INNER JOIN isys_connection cvmg ON isys_catg_virtual_machine_list__isys_connection__id = cvmg.isys_connection__id
			LEFT OUTER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_catg_virtual_machine_list__isys_obj__id
			LEFT JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
			LEFT OUTER JOIN isys_catg_application_list ON isys_catg_virtual_machine_list__isys_obj__id = isys_catg_application_list__isys_obj__id
			LEFT OUTER JOIN isys_connection os ON os.isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT OUTER JOIN isys_obj oso ON os.isys_connection__isys_obj__id = oso.isys_obj__id
			WHERE cvmg.isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_host_object_id) . '
			AND isys_catg_virtual_machine_list__vm = ' . $this->convert_sql_int(C__VM__GUEST) . '
			AND (oso.isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__OPERATING_SYSTEM')) .
            ' OR ISNULL(oso.isys_obj__isys_obj_type__id) OR oso.isys_obj__isys_obj_type__id <> ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__OPERATING_SYSTEM')) . ')
			GROUP BY isys_catg_virtual_machine_list__id
			ORDER BY ovmg.isys_obj__title;';

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_parent_object_id
     *
     * @return  string
     */
    public function get_virtualization_system_as_string($p_parent_object_id)
    {
        $l_sql = 'SELECT isys_catg_virtual_machine_list__system
            FROM isys_catg_virtual_machine_list
            WHERE isys_catg_virtual_machine_list__isys_obj__id = ' . $this->convert_sql_id($p_parent_object_id) . '
            LIMIT 1;';

        return stripslashes($this->retrieve($l_sql)->get_row_value('isys_catg_virtual_machine_list__system'));
    }

    /**
     * Get virtual machine category by object id.
     *
     * @param   integer $p_guest_object_id
     *
     * @return  isys_component_dao_result
     */
    public function get_guest($p_guest_object_id)
    {
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_catg_virtual_machine_list
			ON isys_obj__id = isys_catg_virtual_machine_list__isys_obj__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_guest_object_id) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Detaches/Removes a virtual machine from list.
     *
     * @param   integer $p_list_id
     *
     * @return  boolean
     */
    public function detach_machine($p_list_id)
    {
        $l_sql = 'UPDATE isys_catg_virtual_machine_list
			INNER JOIN isys_connection ON isys_catg_virtual_machine_list__isys_connection__id = isys_connection__id
			SET isys_connection__isys_obj__id = NULL
			WHERE isys_catg_virtual_machine_list__id = ' . $this->convert_sql_id($p_list_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     *
     * @param   integer $p_parent_object_id
     *
     * @return  string
     */

    /**
     * Change vm status.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_vm_status
     *
     * @return  mixed
     */
    public function set_vm_status($p_obj_id, $p_vm_status)
    {
        if ($this->get_data(null, $p_obj_id)
                ->num_rows() == 0) {
            return $this->create($p_obj_id, null, $p_vm_status);
        } else {
            $l_sql = 'UPDATE isys_catg_virtual_machine_list SET
				isys_catg_virtual_machine_list__vm = ' . $this->convert_sql_id($p_vm_status) . '
				WHERE isys_catg_virtual_machine_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }
    }

    /**
     * Retrieve the object-id of the host.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function get_host_system($p_obj_id)
    {
        $condition = ' AND isys_catg_virtual_machine_list__vm = ' . $this->convert_sql_int(C__VM__GUEST);
        $l_data = $this->get_data(null, $p_obj_id, $condition)
            ->get_row();

        return $l_data["isys_connection__isys_obj__id"];
    }

    /**
     * @author Dennis Stuecken
     */
    public function attachObjects(array $p_post)
    {
        $p_new_id = -1;

        $l_intRetCode = 3;

        $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, 0);

        if ($l_id != false) {
            $this->m_strLogbookSQL = $this->get_last_query();
            $l_intRetCode = null;
            $p_new_id = $l_id;
        }

        return $l_intRetCode;
    }

    /**
     * Retrieve cluster members.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_cluster_members($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT obj2.isys_obj__id ,obj2.isys_obj__title, obj2.isys_obj__status FROM isys_catg_cluster_members_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_cluster_members_list__isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_cluster_members_list__isys_connection__id
			LEFT JOIN isys_obj obj2 ON isys_connection.isys_connection__isys_obj__id = obj2.isys_obj__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_cluster_members_list__id = ' . $this->convert_sql_id($p_catg_list_id) . ' ';
        }

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_catg_cluster_members_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ';
        }

        if ($p_status !== null) {
            $l_sql .= ' AND (isys_catg_cluster_members_list__status = ' . $this->convert_sql_int($p_status) . ') ';
        }

        $l_sql .= ' ORDER BY obj2.isys_obj__title;';

        return $this->retrieve($l_sql);
    }

    /**
     * Determine, whether the object identified by the given key is of type cluster.
     *
     * @param   integer $p_obj_id
     *
     * @return  bool
     */
    public function is_cluster($p_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj.isys_obj__isys_obj_type__id = isys_obj_type.isys_obj_type__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_obj_type__title = "LC__CMDB__OBJTYPE__CLUSTER";';

        return (bool)$this->retrieve($l_sql)
            ->num_rows();
    }

    /**
     * Abstract method for retrieving the dynamic properties.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_primary' => new DynamicProperty(
                'LC__CMDB__HOST_IN_CLUSTER',
                'isys_catg_virtual_machine_list__primary',
                'isys_catg_virtual_machine_list',
                [
                    $this,
                    'dynamic_property_primary'
                ]
            )
        ];
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
            'virtual_machine' => (new DialogDataProperty(
                'C__CATG__VM__VM',
                'LC__CMDB__CATG__VIRTUAL_MACHINE',
                'isys_catg_virtual_machine_list__vm',
                'isys_catg_virtual_machine_list',
                new isys_callback([
                    'isys_cmdb_dao_category_g_virtual_machine',
                    'callback_property_virtual_machine'
                ]),
                false,
                [],
                '(CASE WHEN isys_catg_virtual_machine_list__vm = \'' .
                C__VM__GUEST . '\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        	    ELSE ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)'
            ))->mergePropertyUiParams([
                'p_bDbFieldNN' => 1
            ]),
            'hosts'           => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VM__RUNNING_ON_HOST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Field on which host or cluster the virtual machine runs on'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_virtual_machine_list__isys_connection__id',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__VIRTUAL_MACHINE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_virtual_machine',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_virtual_machine',
                        true
                    ]),
                    C__PROPERTY__DATA__FIELD_ALIAS      => 'vm_obj',
                    C__PROPERTY__DATA__TABLE_ALIAS      => 'connection_vm',
                    C__PROPERTY__DATA__REFERENCES       => [
                        'isys_connection',
                        'isys_connection__id'
                    ],
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_virtual_machine_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id',
                        'isys_catg_virtual_machine_list',
                        'isys_catg_virtual_machine_list__id',
                        'isys_catg_virtual_machine_list__isys_obj__id',
                        '',
                        '',
                        null,
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_machine_list',
                            'LEFT',
                            'isys_catg_virtual_machine_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_connection',
                            'LEFT',
                            'isys_catg_virtual_machine_list__isys_connection__id',
                            'isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_connection__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CATG__VM__OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => 'C__CATG__VIRTUAL_HOST;C__CATG__VIRTUAL_HOST_ROOT'
                    ]
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'connection'
                    ]
                ]
            ]),
            'system' => (new DialogPlusProperty(
                'C__CATG__VM__SYSTEM',
                'LC__CMDB__CATG__VIRTUALIZATION_SYSTEM',
                'isys_catg_virtual_machine_list__isys_vm_type__id',
                'isys_catg_virtual_machine_list',
                'isys_vm_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'config_file'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VM__CONFIG_FILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'config file'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_machine_list__config_file',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__VM__CONFIG_FILE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'primary'         => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO             => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__HOST_IN_CLUSTER',
                    C__PROPERTY__INFO__DESCRIPTION => 'defines which host on the cluster the virtual machine runs on'
                ],
                C__PROPERTY__DATA             => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_virtual_machine_list__primary',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_virtual_machine_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_virtual_machine_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_virtual_machine_list__primary',
                        'isys_catg_virtual_machine_list',
                        'isys_catg_virtual_machine_list__id',
                        'isys_catg_virtual_machine_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                            ' AND EXISTS (SELECT 1 FROM isys_obj WHERE isys_obj__id = isys_connection__isys_obj__id 
                                AND isys_obj__isys_obj_type__id IN (
                                    SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                                    INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                                    WHERE isysgui_catg__const = \'C__CATG__CLUSTER_ROOT\'
                                ) 
                             )'
                        ])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_virtual_machine_list',
                            'LEFT',
                            'isys_catg_virtual_machine_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_virtual_machine_list__primary', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI               => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__VIRTUAL_MACHINE_HOST',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => new isys_callback([
                                'isys_cmdb_dao_category_g_virtual_machine',
                                'callback_property_primary'
                            ]),
                        'p_bDbFieldNN' => 0
                    ]
                ],
                C__PROPERTY__FORMAT           => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES         => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                ],
                C__PROPERTY__FORMAT__REQUIRES => 'hosts',
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'categories description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_virtual_machine_list__description',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VIRTUAL_MACHINE', 'C__CATG__VIRTUAL_MACHINE'),
                ],
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            $l_dao_cluster = isys_cmdb_dao_category_g_cluster_memberships::instance($this->m_db);

            $l_primary = isset($p_category_data['properties']['primary'][C__DATA__VALUE]) ? $p_category_data['properties']['primary'][C__DATA__VALUE] : null;

            // Replace the values for property virtual machine for 0 = C__VM__NO and 1 C__VM__GUEST
            if (isset($p_category_data['properties']['virtual_machine'])) {
                if ($p_category_data['properties']['virtual_machine'][C__DATA__VALUE] == 0) {
                    $p_category_data['properties']['virtual_machine'][C__DATA__VALUE] = C__VM__NO;
                } elseif ($p_category_data['properties']['virtual_machine'][C__DATA__VALUE] == 1) {
                    $p_category_data['properties']['virtual_machine'][C__DATA__VALUE] = C__VM__GUEST;
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $p_category_data['properties']['hosts'][C__DATA__VALUE],
                        $p_category_data['properties']['virtual_machine'][C__DATA__VALUE],
                        $p_category_data['properties']['system'][C__DATA__VALUE],
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['config_file'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $l_primary
                    );
                    if ($p_category_data['data_id']) {
                        if ($p_category_data['properties']['hosts'][C__DATA__VALUE] > 0 && $l_primary) {
                            if (!$l_dao_cluster->check_membership($p_category_data['properties']['hosts'][C__DATA__VALUE], $l_primary)) {
                                $l_dao_cluster->create($p_category_data['properties']['hosts'][C__DATA__VALUE], C__RECORD_STATUS__NORMAL, $l_primary);
                            }
                        }
                    }

                    return $p_category_data['data_id'];
                    break;
                case isys_import_handler_cmdb::C__UPDATE:

                    // If its not a virtual machine than set host to null otherwise the connection still exists
                    if ((int)$p_category_data['properties']['virtual_machine'][C__DATA__VALUE] === C__VM__NO) {
                        $p_category_data['properties']['hosts'][C__DATA__VALUE] = null;
                    }

                    $this->save(
                        $p_category_data['data_id'],
                        $p_category_data['properties']['hosts'][C__DATA__VALUE],
                        $p_category_data['properties']['virtual_machine'][C__DATA__VALUE],
                        $p_category_data['properties']['system'][C__DATA__VALUE],
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['config_file'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $l_primary
                    );
                    if ($p_category_data['properties']['hosts'][C__DATA__VALUE] > 0 && $l_primary) {
                        if (!$l_dao_cluster->check_membership($p_category_data['properties']['hosts'][C__DATA__VALUE], $l_primary)) {
                            $l_dao_cluster->create($p_category_data['properties']['hosts'][C__DATA__VALUE], C__RECORD_STATUS__NORMAL, $l_primary);
                        }
                    }

                    return $p_category_data['data_id'];
                    break;
            }
        }

        return false;
    }
}
