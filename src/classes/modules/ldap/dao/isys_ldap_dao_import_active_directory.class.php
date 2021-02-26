<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_ldap_dao_import_active_directory extends isys_ldap_dao_import
{

    /**
     * Prepares category ldap dn
     *
     * @param      $p_ldap_dn
     * @param null $p_obj_id
     *
     * @return array
     */
    public function prepare_catg_ldap_dn($p_ldap_dn, $p_obj_id = null)
    {
        $l_ldap_dn = $p_ldap_dn;
        $l_dao = isys_cmdb_dao_category_g_ldap_dn::instance($this->m_db);
        if ($p_obj_id != null) {
            $l_res = $l_dao->get_data(null, $p_obj_id);

            if ($l_res->num_rows() > 0) {
                $l_data = $l_res->__to_array();
                $l_ldap_dn['data_id'] = $l_data['isys_catg_ldap_dn_list__id'];
            }
        }

        return $this->parse_catg_ldap_dn($l_ldap_dn);
    }

    /**
     * Prepares global category
     *
     * @param      $p_global
     * @param null $p_obj_id
     *
     * @return array
     */
    public function prepare_catg_global($p_global, $p_obj_id = null)
    {
        $l_global = $p_global;
        $l_dao = isys_cmdb_dao_category_g_global::instance($this->m_db);
        if ($p_obj_id !== null) {
            $l_res = $l_dao->get_data(null, $p_obj_id);
            if ($l_res->num_rows() > 0) {
                $l_data = $l_res->__to_array();
                $l_global['data_id'] = $l_data['isys_catg_global_list__id'];
            }
        }

        return $this->parse_catg_global($l_global);
    }

    /**
     * Prepares category application for import.
     *
     * @param   integer $p_application
     * @param   integer $p_obj_type_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_catg_application($p_application, $p_obj_type_id = null)
    {
        // We get an instance of isys_cmdb_dao for two small calls.
        $l_dao = isys_cmdb_dao::instance($this->m_db);
        $l_app_array = $p_application;

        $l_app_id = $l_dao->get_obj_id_by_title($l_app_array['application'], $p_obj_type_id);
        if (!$l_app_id) {
            $l_app_id = $l_dao->insert_new_obj($p_obj_type_id, false, $l_app_array['application'], null, C__RECORD_STATUS__NORMAL);
        }

        $l_data = $l_dao->get_object_by_id($l_app_id)
            ->__to_array();
        $l_last_id = $l_dao->retrieve('SELECT MAX(isys_catg_application_list__id) AS id FROM isys_catg_application_list LIMIT 0,1')
            ->__to_array();
        $l_last_id = $l_last_id['id'];
        $l_last_id = ($l_last_id !== null) ? $l_last_id + 1 : 1;

        $l_app_array['data_id'] = $l_last_id;
        $l_app_array['obj_id'] = $l_data['isys_obj__id'];
        $l_app_array['obj_type_const'] = $l_data['isys_obj_type__const'];
        $l_app_array['sys_id'] = $l_data['isys_obj__sysid'];

        return $this->parse_catg_application($l_app_array);
    }

    /**
     * Method which gets data from the ldap resource
     *
     * @return    array
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_entries_from_resource()
    {
        $l_return = [];
        try {
            if ($this->m_resource) {
                $l_arr = $this->m_library->get_entries($this->m_resource);

                if (is_array($l_arr)) {
                    foreach ($l_arr as $l_key => $l_value) {
                        if (is_numeric($l_key)) {
                            if (strtolower($l_value['distinguishedname'][0]) == trim(strtolower($this->m_root_dn))) {
                                continue;
                            }

                            $l_object_title = $l_value['name'][0];
                            $l_dn = $l_value['distinguishedname'][0];
                            $l_return[] = [
                                'id'    => $l_dn,
                                'title' => $l_object_title
                            ];
                        }
                    }
                }
            }
        } catch (isys_exception_ldap $e) {
        }

        return $l_return;
    }

    /**
     * Prepare function which builds an import conformed array
     * @param array $p_entries
     * @return    isys_ldap_dao_import_active_directory
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare($p_entries = [])
    {
        isys_module_ldap::debug('Starting data preparation...');
        global $g_comp_session;

        if (!empty($p_entries)) {
            $l_arr = $p_entries;
        } else {
            $l_arr = $this->m_library->get_entries($this->m_resource);
        }
        if (!is_countable($l_arr)) {
            return $this;
        }

        isys_module_ldap::debug('Found ' . count($l_arr) . ' entries!');

        $l_dao = new isys_cmdb_dao($this->get_database_component());

        $l_new_obj_id = ((int)$l_dao->get_last_obj_id_from_type()) + 1;

        if (!defined('C__OBJTYPE__SERVER') || !defined('C__OBJTYPE__CLIENT')) {
            return $this;
        }
        $l_objtypes[C__OBJTYPE__SERVER] = $l_dao->get_object_types(C__OBJTYPE__SERVER)
            ->get_row();
        $l_objtypes[C__OBJTYPE__CLIENT] = $l_dao->get_object_types(C__OBJTYPE__CLIENT)
            ->get_row();

        foreach ($l_arr as $l_key => $l_value) {
            if (is_numeric($l_key)) {
                if (strtolower($l_value['distinguishedname'][0]) == trim(strtolower($this->m_root_dn)) ||
                    (is_array($this->m_dn_data) && !in_array($l_value['distinguishedname'][0], $this->m_dn_data))) {
                    continue;
                }

                $l_obj_info = $this->get_object_by_ldap_dn($l_value['distinguishedname'][0]);
                if ($l_obj_info) {
                    $l_obj_id = $l_obj_info['isys_obj__id'];
                    $l_created = strtotime($l_obj_info['isys_obj__created']);
                    $l_sysid = $l_obj_info['isys_obj__sysid'];
                    $l_description = $l_obj_info['isys_obj__description'];
                } else {
                    $l_obj_id = null;
                    $l_created = time();
                    $l_sysid = null;
                    $l_description = '';
                }

                if (strpos($l_value['operatingsystem'][0], 'Server') || !isset($l_value['operatingsystem'][0])) {
                    $l_obj_group = $l_dao->get_object_group_by_id($l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__isys_obj_type_group__id'])
                        ->get_row();
                    $l_obj_type = [
                        'value'        => isys_application::instance()->container->get('language')
                            ->get($l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__title']),
                        'id'           => $l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__id'],
                        'const'        => $l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__const'],
                        'title_lang'   => $l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__title'],
                        'group'        => $l_obj_group['isys_obj_type_group__const'],
                        'sysid_prefix' => $l_objtypes[C__OBJTYPE__SERVER]['isys_obj_type__sysid_prefix']
                    ];
                } else {
                    $l_obj_group = $l_dao->get_object_group_by_id($l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__isys_obj_type_group__id'])
                        ->get_row();
                    $l_obj_type = [
                        'value'        => isys_application::instance()->container->get('language')
                            ->get($l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__title']),
                        'id'           => $l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__id'],
                        'const'        => $l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__const'],
                        'title_lang'   => $l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__title'],
                        'group'        => $l_obj_group['isys_obj_type_group__const'],
                        'sysid_prefix' => $l_objtypes[C__OBJTYPE__CLIENT]['isys_obj_type__sysid_prefix']
                    ];
                }

                // @todo check if object already exists by distinguished name
                if ($l_obj_id === null) {
                    $l_obj_id = $l_new_obj_id++;
                }

                if (isset($l_value['operatingsystem'][0]) && defined('C__OBJTYPE__OPERATING_SYSTEM')) {
                    $l_operating_system = $this->prepare_catg_application(['application' => $l_value['operatingsystem'][0]], C__OBJTYPE__OPERATING_SYSTEM, $l_obj_id);
                } else {
                    $l_operating_system = null;
                }
                $globalCategories = [];
                if (defined('C__CATG__GLOBAL')) {
                    $globalCategories[C__CATG__GLOBAL] = $this->prepare_catg_global([
                        'name'        => $l_value['name'][0],
                        'description' => $l_value['description'][0]
                    ], $l_obj_id);
                }
                if (defined('C__CATG__OPERATING_SYSTEM') && $l_operating_system !== null) {
                    $globalCategories[C__CATG__OPERATING_SYSTEM] = $l_operating_system;
                }
                if (defined('C__CATG__LDAP_DN')) {
                    $globalCategories[C__CATG__LDAP_DN] = $this->prepare_catg_ldap_dn([
                        'title' => $l_value['distinguishedname'][0]
                    ], $l_obj_id);
                }

                $this->m_data[$l_obj_id] = [
                    C__DATA__TITLE   => $l_value['name'][0],
                    'id'             => $l_obj_id,
                    'created'        => $l_created,
                    'created_by'     => $g_comp_session->get_current_username(),
                    'updated'        => time(),
                    'updated_by'     => $g_comp_session->get_current_username(),
                    'type'           => $l_obj_type,
                    'sysid'          => $l_sysid,
                    'status'         => C__RECORD_STATUS__NORMAL,
                    'cmdb_status'    => defined_or_default('C__CMDB_STATUS__IN_OPERATION'),
                    'description'    => $l_description,
                    'category_types' => [
                        C__CMDB__CATEGORY__TYPE_GLOBAL   => $globalCategories,
                        C__CMDB__CATEGORY__TYPE_SPECIFIC => []
                    ]
                ];
            }
        }

        return $this;
    }

    /**
     * Constructor
     *
     * @global isys_component_database $g_comp_database Database component
     *
     * @param isys_log                 $p_log           Logger
     */
    public function __construct(isys_component_database &$p_db, $p_library)
    {
        $this->m_library = $p_library;
        parent::__construct($p_db);
    }
}
