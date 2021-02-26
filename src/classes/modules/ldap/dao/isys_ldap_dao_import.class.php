<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
abstract class isys_ldap_dao_import extends isys_ldap_dao
{
    /* LDAP Library */
    protected $m_data;

    /* Raw import data */
    protected $m_dn_data;

    /* Array with distinguished names (dn) */
    protected $m_library;

    /* Resource of the ldap search */
    protected $m_resource;

    /* Root DN String */
    protected $m_root_dn;

    abstract public function get_entries_from_resource();

    abstract public function prepare();

    /**
     * Setter for m_data
     *
     * @param $p_value
     *
     * @return isys_ldap_dao_import
    @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_m_data($p_value)
    {
        $this->m_data = $p_value;

        return $this;
    }

    /**
     * Setter for m_resource
     *
     * @param $p_value
     *
     * @return isys_ldap_dao_import
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_resource($p_value)
    {
        $this->m_resource = $p_value;

        return $this;
    }

    /**
     * Setter for m_root_dn
     *
     * @param   $p_value
     *
     * @return  isys_ldap_dao_import
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_root_dn($p_value)
    {
        $this->m_root_dn = $p_value;

        return $this;
    }

    /**
     * Setter for m_dn_data
     *
     * @param   $p_value
     *
     * @return  isys_ldap_dao_import
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_dn_data($p_value)
    {
        $this->m_dn_data = $p_value;

        return $this;
    }

    /**
     * Import method.
     *
     * @return  boolean
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function import()
    {
        global $g_comp_database;

        if (is_countable($this->m_data) && count($this->m_data) > 0) {
            isys_module_ldap::debug('Starting import of ' . count($this->m_data) . ' items.');
            $l_log = isys_factory_log::get_instance('import_ldap');
            $l_log->set_verbose_level($l_log::C__NOTICE);

            // Create an instance of the CMDB import
            $l_import = new isys_import_handler_cmdb($l_log, $g_comp_database);

            // Prepare and import the data.
            return $l_import->set_data($this->m_data)
                ->set_import_header('LDAP')
                ->set_mode(isys_import_handler_cmdb::C__MERGE)// @todo Check for the selected import-mode
                ->prepare()
                ->import();
        } else {
            isys_module_ldap::debug('No items to import.');

            return false;
        }
    }

    /**
     * Gets object id by ldap dn.
     *
     * @param   string $p_dn_string
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_object_by_ldap_dn($p_dn_string)
    {
        $l_dao = isys_cmdb_dao_category_g_ldap_dn::instance($this->m_db);
        $l_res = $l_dao->get_data(null, null, 'AND isys_catg_ldap_dn_list__title LIKE ' . $l_dao->convert_sql_text($p_dn_string));

        if ($l_res->num_rows() > 0) {
            return $l_res->get_row();
        } else {
            return false;
        }
    }

    /**
     * Builds an "i-doit comfortable" array for the import for the global category "global".
     *
     * @param   array $p_data
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_catg_global($p_data)
    {
        // No sys-id, this should be handled by the system.
        return [
            C__DATA__TITLE                                 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__GLOBAL'),
            'const'                                        => 'C__CATG__GLOBAL',
            'category_type'                                => C__CMDB__CATEGORY__TYPE_GLOBAL,
            isys_import_handler_cmdb::C__CATEGORY_ENTITIES => [
                [
                    'data_id'    => $p_data['data_id'],
                    'properties' => [
                        'title'       => [
                            'tag'   => 'title',
                            'value' => $p_data['name'],
                            'title' => 'LC__UNIVERSAL__TITLE'
                        ],
                        'cmdb_status' => [
                            'tag'        => 'cmdb_status',
                            'value'      => isys_application::instance()->container->get('language')
                                ->get('LC__CMDB_STATUS__IN_OPERATION'),
                            'id'         => defined_or_default('C__CMDB_STATUS__IN_OPERATION'),
                            'const'      => 'C__CMDB_STATUS__IN_OPERATION',
                            'title_lang' => 'LC__CMDB_STATUS__IN_OPERATION',
                            'title'      => 'LC__UNIVERSAL__CMDB_STATUS'
                        ],
                        'description' => [
                            'tag'   => 'description',
                            'value' => $p_data['description'],
                            'title' => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Builds an "i-doit conform" array for the import of the global category "application"
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_catg_application($p_data)
    {
        return [
            C__DATA__TITLE                                 => isys_application::instance()->container->get('language')
                ->get('LC__CATG__OPERATING_SYSTEM'),
            'const'                                        => 'C__CATG__OPERATING_SYSTEM',
            'category_type'                                => C__CMDB__CATEGORY__TYPE_GLOBAL,
            isys_import_handler_cmdb::C__CATEGORY_ENTITIES => [
                $p_data['data_id'] => [
                    'data_id'    => $p_data['data_id'],
                    'properties' => [
                        'application' => [
                            'tag'           => 'application',
                            'value'         => $p_data['application'],
                            'id'            => $p_data['obj_id'],
                            'connection_id' => null,
                            'type'          => $p_data['obj_type_const'],
                            'sysid'         => $p_data['sys_id'],
                            'lc_title'      => isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION'),
                            'title'         => 'LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Builds an "i-doit conform" array for the import of the global category "LDAP DN"
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_catg_ldap_dn($p_data)
    {
        return [
            C__DATA__TITLE                                 => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__LDAP_DN'),
            'const'                                        => 'C__CATG__LDAP_DN',
            'category_type'                                => C__CMDB__CATEGORY__TYPE_GLOBAL,
            isys_import_handler_cmdb::C__CATEGORY_ENTITIES => [
                [
                    'data_id'    => $p_data['data_id'],
                    'properties' => [
                        'title'       => [
                            'tag'   => 'title',
                            'value' => $p_data['title'],
                            'title' => 'LC__UNIVERSAL__TITLE'
                        ],
                        'description' => [
                            'tag'   => 'description',
                            'value' => $p_data['description'],
                            'title' => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                        ]
                    ]
                ]
            ]
        ];
    }
}
