<?php

/**
 * i-doit
 *
 * Monitoring helper.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_monitoring_helper
{
    /**
     * Static instance of the database component.
     *
     * @var  isys_component_database
     */
    protected static $m_db = null;

    /**
     * This array holds various information about the different host states.
     *
     * @var  array
     */
    protected static $m_host_states = [
        C__MONITORING__STATE__UP          => [
            'state'    => 'UP',
            'state_id' => C__MONITORING__STATE__UP,
            'color'    => 'green',
            'icon'     => 'icons/silk/tick.png'
        ],
        C__MONITORING__STATE__DOWN        => [
            'state'    => 'DOWN',
            'state_id' => C__MONITORING__STATE__DOWN,
            'color'    => 'red',
            'icon'     => 'icons/silk/delete.png'
        ],
        C__MONITORING__STATE__UNREACHABLE => [
            'state'    => 'UNREACHABLE',
            'state_id' => C__MONITORING__STATE__UNREACHABLE,
            'color'    => 'blue',
            'icon'     => 'icons/silk/information.png'
        ]
    ];

    /**
     * Has the helper been initialized?
     *
     * @var  boolean
     */
    protected static $m_initialized = false;

    /**
     * This array holds various information about the different states.
     *
     * @var  array
     */
    protected static $m_states = [
        C__MONITORING__STATE__OK       => [
            'state'    => 'OK',
            'state_id' => C__MONITORING__STATE__OK,
            'color'    => 'green',
            'icon'     => 'icons/silk/tick.png'
        ],
        C__MONITORING__STATE__WARNING  => [
            'state'    => 'WARNING',
            'state_id' => C__MONITORING__STATE__WARNING,
            'color'    => 'yellow',
            'icon'     => 'icons/silk/error.png'
        ],
        C__MONITORING__STATE__CRITICAL => [
            'state'    => 'CRITICAL',
            'state_id' => C__MONITORING__STATE__CRITICAL,
            'color'    => 'red',
            'icon'     => 'icons/silk/delete.png'
        ],
        C__MONITORING__STATE__UNKNOWN  => [
            'state'    => 'UNKNOWN',
            'state_id' => C__MONITORING__STATE__UNKNOWN,
            'color'    => 'blue',
            'icon'     => 'icons/silk/information.png'
        ]
    ];

    /**
     * Static array for saving various information for several methods.
     *
     * @var  array
     */
    protected static $m_tmp = [
        'hostnames' => [],
    ];

    /**
     * Initialize method for setting some initial stuff.
     *
     * @static
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function init()
    {
        if (self::$m_db === null) {
            global $g_comp_database;

            self::$m_db = $g_comp_database;
        }

        self::$m_initialized = true;
    }

    /**
     * Static method to retrieve informations about the different states.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_state_info()
    {
        return self::$m_states;
    }

    /**
     * Static method to retrieve informations about the different host states.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_host_state_info()
    {
        return self::$m_host_states;
    }

    /**
     * Method for retrieving the hostname of the given object.
     *
     * @static
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function render_export_hostname($p_obj_id)
    {
        if (!self::$m_initialized) {
            self::init();
        }

        if (array_key_exists($p_obj_id, self::$m_tmp['hostnames'])) {
            return trim(self::$m_tmp['hostnames'][$p_obj_id]);
        }

        $l_row = isys_cmdb_dao_category_g_monitoring::instance(self::$m_db)
            ->get_data(null, $p_obj_id)
            ->get_row();

        if ($l_row !== false) {
            switch ($l_row['isys_catg_monitoring_list__host_name_selection']) {
                case C__MONITORING__NAME_SELECTION__INPUT:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim($l_row['isys_catg_monitoring_list__host_name']);

                case C__MONITORING__NAME_SELECTION__HOSTNAME_FQDN:
                    $l_row = isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                        ->get_primary_ip($p_obj_id)
                        ->get_row();

                    if (!empty($l_row['isys_catg_ip_list__domain'])) {
                        $l_row['isys_catg_ip_list__domain'] = '.' . trim($l_row['isys_catg_ip_list__domain']);
                    }

                    return self::$m_tmp['hostnames'][$p_obj_id] = trim($l_row['isys_catg_ip_list__hostname']) . $l_row['isys_catg_ip_list__domain'];

                case C__MONITORING__NAME_SELECTION__HOSTNAME:
                    return self::$m_tmp['hostnames'][$p_obj_id] = trim(isys_cmdb_dao_category_g_ip::instance(self::$m_db)
                        ->get_primary_ip($p_obj_id)
                        ->get_row_value('isys_catg_ip_list__hostname'));

                case C__MONITORING__NAME_SELECTION__OBJ_ID:
                    return self::$m_tmp['hostnames'][$p_obj_id] = self::prepare_valid_name($l_row['isys_obj__title']);
            }
        }

        return '';
    }

    /**
     * Method for converting invalid names like "Peter Griffin " to valid "peter_griffin".
     *
     * @param   string $p_value
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function prepare_valid_name($p_value)
    {
        return preg_replace('~[\s]+~', '_', isys_glob_strip_accent(trim($p_value)));
    }

    /**
     * @param   integer $p_host_id
     * @param   integer $p_hostname
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_objects_by_hostname($p_host_id, $p_hostname)
    {
        if (!self::$m_initialized) {
            self::init();
        }

        $l_dao = isys_cmdb_dao_category_g_monitoring::instance(self::$m_db);

        $l_host_res = $l_dao->get_data(null, null, ' AND isys_catg_monitoring_list__isys_monitoring_hosts__id = ' . $l_dao->convert_sql_id($p_host_id) . ' ', null,
            C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_host_res->get_row()) {
            if (self::render_export_hostname($l_row['isys_catg_monitoring_list__isys_obj__id']) == $p_hostname) {
                return $l_row;
            }
        }

        return [];
    }

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
        ;
    }

    /**
     * Private constructor - Singleton!
     */
    private function __construct()
    {
        ;
    }
}