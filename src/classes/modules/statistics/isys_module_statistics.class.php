<?php

/**
 * i-doit.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
if (!class_exists('isys_statistics_dao')) {
    include_once(__DIR__ . '/dao/isys_statistics_dao.class.php');
}

class isys_module_statistics extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var  boolean
     */
    protected static $m_licenced = true;

    /**
     * @var  array
     */
    protected $m_counts = [
        'objects'         => 0,
        'objects_by_type' => 0,
        'contacts'        => [
            'persons'       => 0,
            'groups'        => 0,
            'organisations' => 0
        ],
        'cmdb_references' => 0,
        'mandators'       => 0
    ];

    /**
     * @var  boolean
     */
    protected $m_initialized = false;

    /**
     * @var  array
     */
    protected $m_stats = [
        'last_idoit_update' => null,
        'current_version'   => null,
        'current_revision'  => null
    ];

    /**
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Return instance of statistics dao.
     *
     * @param   $p_database
     *
     * @return  isys_statistics_dao
     */
    public static function get_statistics_dao($p_database)
    {
        if (!class_exists('isys_statistics_dao')) {
            include_once('init.php');
        }

        /* Since */
        if (!class_exists('isys_cmdb_dao')) {
            include_once(__DIR__ . '/../cmdb/init.php');
        }

        return new isys_statistics_dao($p_database, new isys_cmdb_dao($p_database));
    }

    /**
     * Gets counts.
     *
     * @return  array
     */
    public function get_counts()
    {
        return $this->m_counts;
    }

    /**
     * Gets stats.
     *
     * @return  array
     */
    public function get_stats()
    {
        return $this->m_stats;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@synetics.de>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
    }

    /**
     * Start method.
     */
    public function start()
    {
    }

    /**
     * Initializes the statistics.
     */
    public function init_statistics()
    {
        global $g_comp_database, $g_absdir, $g_product_info;

        $l_cmdb_dao = new isys_cmdb_dao($g_comp_database);
        $l_dao = new isys_statistics_dao($g_comp_database, $l_cmdb_dao);

        // Object counter.
        $this->m_counts['objects'] = $l_dao->count_objects();

        $l_otypes = $l_cmdb_dao->get_objtype();
        $l_otype_count = [];

        while ($l_row = $l_otypes->get_row()) {
            $l_otype_count[$l_row['isys_obj_type__id']] = [
                'type'  => $this->language->get($l_row['isys_obj_type__title']),
                'count' => $l_dao->count_objects($l_row['isys_obj_type__id'])
            ];
        }

        usort($l_otype_count, function ($a, $b) {
            return strcasecmp($a['type'], $b['type']);
        });

        $this->m_counts['objects_by_type'] = $l_otype_count;

        // Person counter.
        $this->m_counts['contacts']['persons'] = count(isys_cmdb_dao_category_s_person_master::instance($g_comp_database)
            ->get_data());

        // Group counter.
        $this->m_counts['contacts']['groups'] = count(isys_cmdb_dao_category_s_person_group_master::instance($g_comp_database)
            ->get_data());

        // Organisation counter.
        $this->m_counts['contacts']['organisations'] = count(isys_cmdb_dao_category_s_organization_master::instance($g_comp_database)
            ->get_data());

        // CMDB References.
        $this->m_counts['cmdb_references'] = $l_dao->count_cmdb_references();

        // Last i-doit update.
        if (file_exists($g_absdir . '/index.php')) {
            $this->m_stats['last_idoit_update'] = date('d.m.Y H:i:s', filemtime($g_absdir . '/index.php'));
        }

        // i-doit version and revision.
        $l_info = $l_dao->get_db_version();
        $this->m_stats['current_version'] = $g_product_info['version'];
        $this->m_stats['current_db_version'] = $l_info['version'];
        $this->m_stats['current_db_revision'] = $l_info['revision'];

        // Mandator counter.
        $l_dao_mandator = new isys_component_dao_mandator();
        $l_mandators = $l_dao_mandator->get_mandator(null, 0);

        $this->m_counts['mandators'] = $l_mandators->num_rows();

        $this->m_initialized = true;
    }

    /**
     * Initializes the module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req)) {
            $this->m_userrequest = &$p_req;

            return true;
        }

        return false;
    }
}
