<?php

use idoit\Component\Helper\Purify;
use idoit\Component\Table\Filter\Configuration;
use idoit\Component\Table\Table;
use idoit\AddOn\AuthableInterface;
use idoit\AddOn\ExtensionProviderInterface;
use idoit\Model\QuickInfo;
use idoit\Exception\JsonException;
use idoit\Module\Cmdb\CmdbExtension;
use idoit\Module\Cmdb\Component\Table\Config\Refresher;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * i-doit
 *
 *  CCCCC    MM  MM   DDDDD   BBBBB
 * CC   CC  MM MM MM  DD  DD  BB  BB
 * CC       MM MM MM  DD  DD  BBBBB
 * CC   CC  MM MM MM  DD  DD  BB  BB
 *  CCCCC   MM    MM  DDDDD   BBBBB
 * ------------------------------
 *     M   O   D   U   L   E
 * ------------------------------
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_cmdb extends isys_module implements AuthableInterface, ExtensionProviderInterface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * For breadcrumb navigation: General CMDB-Access DAO Low-Level API
     *
     * @var isys_cmdb_dao
     */
    private $m_dao_cmdb;

    /**
     * Module request
     *
     * @var isys_module_request
     */
    private $m_modreq;

    /**
     * View manager
     *
     * @var isys_cmdb_view_manager
     */
    private $m_view_manager;

    /**
     * @static
     * @return array
     */
    public static function get_additional_links()
    {
        $l_links = [
            'RELATION' => [
                'LC__CMDB__CATG__RELATION',
                isys_helper_link::create_url([
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_OBJECT,
                    C__CMDB__GET__TREEMODE   => C__CMDB__VIEW__TREE_RELATION,
                    C__CMDB__GET__OBJECTTYPE => defined_or_default('C__OBJTYPE__PARALLEL_RELATION')
                ]),
                defined_or_default('C__MODULE__CMDB'), // Parent module
                isys_application::instance()->www_path . 'images/icons/silk/arrow_out.png', // Sub module icon
            ]
        ];

        return $l_links;
    }

    /**
     * This method is used to build the validation-cache which is used inside the isys_cmdb_dao_category::get_properties_ng() method.
     *
     * @return isys_caching
     * @throws JsonException
     * @throws isys_exception_database
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function create_validation_cache()
    {
        // At first we get ourself a fresh instance with no data.
        $cache = isys_caching::factory('validation_config')->clear();

        $l_res = isys_cmdb_dao_validation::instance(isys_application::instance()->container->get('database'))->get_data();

        $jsonData = [];

        while ($row = $l_res->get_row()) {
            if ($row['isys_validation_config__isysgui_catg__id'] > 0) {
                $categoryType = 'g';
                $key = $row['isys_validation_config__category_class'];
            } elseif ($row['isys_validation_config__isysgui_cats__id'] > 0) {
                $categoryType = 's';
                $key = $row['isys_validation_config__category_class'];
            } else {
                $categoryType = 'g_custom';
                $key = 'isys_cmdb_dao_category_g_custom_fields:' . $row['isys_validation_config__isysgui_catg_custom__id'];
            }

            $jsonData[$categoryType][$key] = isys_format_json::decode($row['isys_validation_config__json'], true);
        }

        return $cache
            ->set('g', $jsonData['g'])
            ->set('s', $jsonData['s'])
            ->set('g_custom', $jsonData['g_custom'])
            ->save();
    }

    /**
     * @return isys_cmdb_view_manager|null
     */
    public function get_view_manager()
    {
        return $this->m_view_manager;
    }

    /**
     * Initializes the CMDB Module.
     *
     * @param   isys_module_request $p_req
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;

        $this->m_view_manager = new isys_cmdb_view_manager($p_req);
        $this->m_dao_cmdb = new isys_cmdb_dao(isys_application::instance()->database);

        try {
            // Register category views.
            $this->m_view_manager->register(new isys_cmdb_view_category($this->m_modreq));

            // Register config views.
            $this->m_view_manager->register("isys_cmdb_view_config_objecttype", C__CMDB__VIEW__CONFIG_OBJECTTYPE);
            $this->m_view_manager->register("isys_cmdb_view_config_systemdata", C__CMDB__VIEW__CONFIG_SYSTEMDATA);

            // Register list views
            $this->m_view_manager->register("isys_cmdb_view_list_category", C__CMDB__VIEW__LIST_CATEGORY);
            $this->m_view_manager->register("isys_cmdb_view_list_object", C__CMDB__VIEW__LIST_OBJECT);
            $this->m_view_manager->register("isys_cmdb_view_list_object_old", C__CMDB__VIEW__LIST_OBJECT_OLD);  // @todo remove in future
            $this->m_view_manager->register("isys_cmdb_view_list_objecttype", C__CMDB__VIEW__LIST_OBJECTTYPE);

            if (defined('C__CMDB__VIEW__EXPLORER')) {
                $this->m_view_manager->register("isys_cmdb_view_explorer", C__CMDB__VIEW__EXPLORER);
            }

            // Register misc. views.
            $this->m_view_manager->register("isys_cmdb_view_misc_blank", C__CMDB__VIEW__MISC_BLANK);

            // Register tree views.
            $this->m_view_manager->register("isys_cmdb_view_tree_location", C__CMDB__VIEW__TREE_LOCATION);
            $this->m_view_manager->register("isys_cmdb_view_tree_object", C__CMDB__VIEW__TREE_OBJECT);
            $this->m_view_manager->register("isys_cmdb_view_tree_objecttype", C__CMDB__VIEW__TREE_OBJECTTYPE);
            $this->m_view_manager->register("isys_cmdb_view_tree_relation", C__CMDB__VIEW__TREE_RELATION);

            return true;
        } catch (isys_exception_cmdb $l_e) {
            throw new isys_exception_cmdb("Could not initialize CMDB View (" . $l_e->getMessage() . ")");
        }
    }

    /**
     * Method for removing the IP-address from objects, which are getting archived or deleted. This is necessary for several reasons. Believe me. Also read #4082 in our TRAC.
     *
     * @global                isys_component_database
     *
     * @param   isys_cmdb_dao $p_cmdb_dao
     * @param   integer       $p_direction
     * @param   array         $p_objects
     *
     * @return  void
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function detach_ip_address($p_cmdb_dao, $p_direction, array $p_objects)
    {
        /**
         * @var  isys_cmdb_dao_category_g_ip $l_ip_dao
         */
        $l_ip_dao = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->database);

        switch ($p_direction) {
            case C__CMDB__RANK__DIRECTION_DELETE:
                $l_ip_dao->empty_ip_addresses_from_obj($p_objects);
                break;

            case C__CMDB__RANK__DIRECTION_RECYCLE:
                ; // Nothing to do here. Yet.
                break;
        }
    }

    /**
     * Build breadcrumb navifation
     *
     * @param &$p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        global $g_product_info;

        $l_gets = $this->m_modreq->get_gets();
        $l_tpl = $this->m_modreq->get_template();
        $l_cmdb = $this->m_dao_cmdb;
        $l_res = [];
        // Cast only C__CMDB__GET__XXX Variables to int
        $l_gets = Purify::castIntValues($l_gets);

        // Stage ONE : Object type.

        /* Retrive object type by object id if `$_GET[C__CMDB__GET__OBJECT]`is not setted */
        if (!isset($l_gets[C__CMDB__GET__OBJECTTYPE]) && isset($l_gets[C__CMDB__GET__OBJECT])) {
            $l_objectRes = $this->m_dao_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT])->get_row();
            $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objectRes["isys_obj_type__id"];
        }

        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
            $l_ot = $l_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
            if ($l_ot && $l_ot->num_rows() > 0) {
                $l_ot_data = $l_ot->get_row();
                if (isset($l_og_gets)) {
                    // If object group was processed, take its GET-Parameters.
                    $l_ot_gets = $l_og_gets;
                } else {
                    // Other $p_gets ...
                    $l_ot_gets = $p_gets;
                }

                if (isset($l_ot_data['isys_obj_type__isys_obj_type_group__id'])) {
                    //$l_ot_gets[C__CMDB__GET__OBJECTGROUP] = $l_ot_data['isys_obj_type__isys_obj_type_group__id'];
                }

                $l_ot_gets[C__CMDB__GET__OBJECTTYPE] = $l_gets[C__CMDB__GET__OBJECTTYPE];
                $l_ot_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;

                if (!is_value_in_constants($l_ot_data['isys_obj_type__id'], ['C__OBJTYPE__RELATION', 'C__OBJTYPE__PARALLEL_RELATION'])) {
                    $l_ot_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                } else {
                    $l_ot_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_RELATION;
                }

                $l_res[] = [
                    $this->language->get($l_ot_data["isys_obj_type__title"]) => $l_ot_gets
                ];
            }

            // Stage TWO : Object.
            if (isset($l_gets[C__CMDB__GET__OBJECT])) {
                $l_o = $l_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT]);

                if ($l_o && $l_o->num_rows() > 0) {
                    $l_o_data = $l_o->get_row();
                    $l_o_title = $l_cmdb->get_obj_name_by_id_as_string($l_o_data["isys_obj__id"]);

                    if (empty($l_o_title)) {
                        $l_o_title = (string)'ID: ' . $l_o_data["isys_obj__id"];
                    }

                    if (isset($l_ot_gets)) {
                        $l_o_gets = $l_ot_gets;
                        $l_o_gets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__GLOBAL');
                        $l_o_gets[C__CMDB__GET__OBJECT] = $l_gets[C__CMDB__GET__OBJECT];
                        $l_o_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY_GLOBAL;
                        $l_o_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                        $l_res[] = [
                            $this->language->get($l_o_title) => $l_o_gets
                        ];
                    }
                }

                // Stage THREE : Category.
                $l_cattype = null;
                $l_catview = null;
                $l_catget = null;
                $l_catprefix = null;
                $l_cattree = null;
                $l_catcget = null;

                if (isset($l_gets[C__CMDB__GET__CATG])) {
                    $l_cattype = C__CMDB__CATEGORY__TYPE_GLOBAL;
                    $l_catview = C__CMDB__VIEW__CATEGORY_GLOBAL;
                    $l_cattree = C__CMDB__VIEW__TREE_OBJECT;
                    $l_catget = C__CMDB__GET__CATG;
                    $l_catprefix = "catg";
                } elseif (isset($l_gets[C__CMDB__GET__CATS])) {
                    $l_cattype = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                    $l_catview = C__CMDB__VIEW__CATEGORY_SPECIFIC;
                    $l_cattree = C__CMDB__VIEW__TREE_OBJECT;
                    $l_catget = C__CMDB__GET__CATS;
                    $l_catprefix = "cats";
                }

                $l_catconst = isys_glob_which_isset($l_gets[C__CMDB__GET__CATG], $l_gets[C__CMDB__GET__CATS], $l_gets[C__CMDB__GET__CATD]);

                // Do not change this, necessary type comparison here!
                if ($l_cattype !== null) {
                    $l_catinfo = $l_cmdb->gui_get_info_by_category($l_cattype, $l_catconst);

                    if ($l_catinfo) {
                        $l_catinfo = $l_catinfo->get_row();
                        $l_cat_title = $this->language->get($l_catinfo["isysgui_" . $l_catprefix . "__title"]);

                        // @todo  Find out why $l_gets does not contain the key "C__CMDB__GET__CATG_CUSTOM".
                        if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL && $l_catconst == defined_or_default('C__CATG__CUSTOM_FIELDS') && $_GET[C__CMDB__GET__CATG_CUSTOM] > 0) {
                            $l_cat_title = isys_custom_fields_dao::instance(isys_application::instance()->database)
                                ->get_data($_GET[C__CMDB__GET__CATG_CUSTOM] ?: $_POST[C__CMDB__GET__CATG_CUSTOM])
                                ->get_row_value('isysgui_catg_custom__title');

                            // Just in case someone uses custom translations: ID-1649.
                            $l_cat_title = $this->language->get($l_cat_title);
                        }

                        $l_c_gets = [];

                        if (isset($l_o_gets)) {
                            $l_c_gets = $l_o_gets;
                            $l_c_gets[$l_catget] = $l_catconst;
                            $l_c_gets[C__CMDB__GET__VIEWMODE] = $l_catview;
                            $l_c_gets[C__CMDB__GET__TREEMODE] = $l_cattree;
                        }

                        $l_c_gets['navPageStart'] = $_GET['navPageStart'];

                        if ($_GET['navPageStart'] > 0) {
                            $l_cat_title .= ' (' . $this->language->get('LC__UNIVERSAL__PAGE') . ' ' . (ceil($_GET['navPageStart'] / isys_glob_get_pagelimit()) + 1) . ')';
                        }

                        if ($l_gets[C__CMDB__GET__CATLEVEL]) {
                            // This will prevent the "catgID=1" parameter, when we are in specific categories.
                            if (isset($l_gets[C__CMDB__GET__CATS])) {
                                unset($l_c_gets[C__CMDB__GET__CATG]);
                            }

                            $l_dist = new isys_cmdb_dao_distributor(
                                $this->m_dao_cmdb->get_database_component(),
                                $l_gets[C__CMDB__GET__OBJECT],
                                $l_cattype,
                                $l_gets[C__CMDB__GET__CATLEVEL],
                                [$l_catconst => true]
                            );

                            $l_category_dao = $l_dist->get_category($l_catconst);
                            if (method_exists($l_category_dao, 'get_general_data')) {
                                $l_general_data = $l_category_dao->get_general_data();
                                $l_category_entry_title = $l_category_dao->get_entry_identifier($l_general_data);

                                if (empty($l_category_entry_title)) {
                                    if ($l_gets[C__CMDB__GET__CATLEVEL] > 0) {
                                        $l_category_entry_title = '#' . $l_gets[C__CMDB__GET__CATLEVEL];
                                    } else {
                                        $l_category_entry_title = $this->language->get('LC__UNIVERSAL__NEW_ENTRY');
                                    }
                                }
                            }

                            $l_c_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_CATEGORY;

                            if ($l_cattype == C__CMDB__CATEGORY__TYPE_GLOBAL && $l_catconst == defined_or_default('C__CATG__CUSTOM_FIELDS') && $_GET[C__CMDB__GET__CATG_CUSTOM] > 0) {
                                $l_c_gets[C__CMDB__GET__CATG_CUSTOM] = $_GET[C__CMDB__GET__CATG_CUSTOM];
                            }
                        }

                        $l_res[] = [
                            $l_cat_title => $l_c_gets
                        ];

                        if (isset($l_category_entry_title)) {
                            $l_res[] = [
                                $l_category_entry_title => []
                            ];
                        }
                    }
                }
            }
        } elseif ($l_gets[C__CMDB__GET__VIEWMODE]) {
            switch ($l_gets[C__CMDB__GET__VIEWMODE]) {
                case C__CMDB__VIEW__EXPLORER:
                    $l_res[] = ['CMDB-Explorer' => null];
                    break;

                default:
                    $l_res[] = ['i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['step'] . ' ' . $g_product_info['type'] => null];
                    break;
            }
        } else {
            // We're in dashboard mode if theres no object type or viewmode set.
            $l_res[] = ['i-doit ' . $g_product_info['version'] . ' ' . $g_product_info['step'] . ' ' . $g_product_info['type'] => null];
        }

        return $l_res;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__CMDB')) {
            return false;
        }
        global $g_dirs;

        if (null !== $p_parent && is_int($p_parent)) {
            $l_mod_gets = $_GET;

            if ($p_system_module) {
                $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__CMDB;
            }

            unset($l_mod_gets[C__CMDB__GET__OBJECTTYPE], $l_mod_gets['what']);

            if (defined('C__MODULE__PRO')) {
                $l_mod_gets[C__GET__SETTINGS_PAGE] = 'validation';
                $l_mod_gets[C__GET__TREE_NODE] = C__MODULE__CMDB . '01338';
                $p_tree->add_node(
                    C__MODULE__CMDB . '01338',
                    $p_parent,
                    $this->language->get("LC__SETTINGS__CMDB__VALIDATION"),
                    isys_glob_build_url(isys_glob_http_build_query($l_mod_gets)),
                    null,
                    $g_dirs["images"] . "/icons/silk/page_white_edit.png"
                );
            }
        }
    }

    /**
     * Initialize module slots
     */
    public function initslots()
    {
        $signals = isys_application::instance()->container->get('signals');
        $database = isys_application::instance()->container->get('database');

        if ((new isys_component_dao_setting($database))->get(null, null, 'C__MANDATORY_SETTING__IP_HANDLING')) {
            $signals->connect('mod.cmdb.afterObjectRank', ['isys_module_cmdb', 'detach_ip_address']);
        }

        $signals
            // Unique-Handling for ips.
            ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_category_g_ip', 'unique_handling'])
            // @see  ID-5543
            ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_category_g_cluster_service', 'assignmentStatusTransition'])
            ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_connection', 'unidirectionalConnectionRanking'])
            // Handle authentication rules which are related to the ranked object.
            ->connect('mod.cmdb.afterObjectRank', ['isys_auth_dao_cmdb', 'objectRelatedAuthenticationRules'])
            // @see  ID-5599  Automatic inventory number creation.
            ->connect('mod.cmdb.objectCreated', [isys_cmdb_dao_category_g_accounting::instance($database), 'signal_auto_inventory_no'])
            ->connect('mod.cmdb.objectChanged', function ($objectIds = null, $changedBy = null) use ($database) {
                if ($objectIds !== null) {
                    // @see  ID-6913  Update "things" after a object has changed.
                    QuickInfo::instance($database)->deleteCache($objectIds);
                }

                // @todo  Should `isys_lock` also be cleared here?
            });
    }

    /**
     * Evalutes text and link for my-doit bookmark entry statically.
     * Returns true on success, false on failure.
     *
     * @param string $l_text
     * @param string $l_link
     *
     * @return boolean
     */
    public function mydoit_get(&$l_text, &$l_link)
    {
        if (!defined('C__MODULE__CMDB')) {
            return false;
        }
        $l_gets = $this->m_modreq->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_posts = $this->m_modreq->get_posts();
        $l_cmdb = $this->m_dao_cmdb;

        $l_textArray = [];

        $l_basegets = [];
        $l_basegets[C__GET__MODULE_ID] = C__MODULE__CMDB;

        // Stage ZERO : Object group.
        if (isset($l_gets[C__CMDB__GET__OBJECTGROUP])) {
            $l_og = $l_cmdb->objgroup_get_by_id($l_gets[C__CMDB__GET__OBJECTGROUP]);
            if ($l_og && $l_og->num_rows() > 0) {
                $l_basegets[C__CMDB__GET__OBJECTGROUP] = $l_gets[C__CMDB__GET__OBJECTGROUP];
            }
        }

        // Stage ONE : Object type.
        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
            $l_ot = $l_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
            if ($l_ot && $l_ot->num_rows() > 0) {
                $l_ot_data = $l_ot->get_row();
                $l_basegets[C__CMDB__GET__OBJECTTYPE] = $l_gets[C__CMDB__GET__OBJECTTYPE];
                $l_basegets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;
                $l_basegets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;

                $l_textArray[] = $this->language
                    ->get($l_ot_data["isys_obj_type__title"]);
            }

            /* Stage TWO : Object */
            if (isset($l_gets[C__CMDB__GET__OBJECT])) {
                $l_o = $l_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT]);
                if ($l_o && $l_o->num_rows() > 0) {
                    $l_o_data = $l_o->get_row();
                    $l_o_title = $l_cmdb->get_obj_name_by_id_as_string($l_o_data["isys_obj__id"]);
                    $l_basegets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__GLOBAL');
                    $l_basegets[C__CMDB__GET__OBJECT] = $l_gets[C__CMDB__GET__OBJECT];
                    $l_basegets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY_GLOBAL;
                    $l_basegets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;

                    $l_textArray[] = $l_o_title;
                }
            }
        }

        if (isset($l_gets[C__CMDB__GET__VIEWMODE]) && $l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__EXPLORER) {
            $l_textArray = [
                $this->language
                    ->get('LC__MODULE__CMDB__VISUALIZATION')
            ];

            // We use the "POST" array, because this might hold more reliable data.
            $l_basegets = [
                C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                C__CMDB__GET__OBJECT        => (int)$l_posts['C_VISUALIZATION_OBJ_SELECTION__HIDDEN'],
                C__CMDB__VISUALIZATION_VIEW => $l_gets[C__CMDB__VISUALIZATION_VIEW],
                C__CMDB__VISUALIZATION_TYPE => $l_gets[C__CMDB__VISUALIZATION_TYPE],
                'profile'                   => (int)$l_posts['C_VISUALIZATION_PROFILE'],
                'service'                   => (int)$l_posts['C_VISUALIZATION_SERVICE_FILTER']
            ];

            if ($l_basegets[C__CMDB__GET__OBJECT] > 0) {
                $l_textArray[] = $l_cmdb->get_obj_name_by_id_as_string($l_basegets[C__CMDB__GET__OBJECT]);
            }
        }

        $l_text = $l_textArray;
        $l_link = isys_glob_http_build_query($l_basegets);

        return true;
    }

    /**
     * Starts the CMDB Module
     *
     * @return bool
     */
    public function start()
    {
        global $index_includes;
        global $g_ajax;

        if ($_GET[C__GET__MODULE_SUB_ID]) {
            switch ($_GET[C__GET__SETTINGS_PAGE]) {
                case 'catlist':
                    return $this->multivalueCategoryList();
                case 'cat_list':
                    return $this->list_settings();
            }

            return $this->system_settings();
        }

        $database = isys_application::instance()->container->get('database');

        // If a add-on has been installed or updated the properties will be renewed
        if (isys_tenantsettings::get('cmdb.renew-properties', 1)) {
            // Deactivate renewal of properties so that it will not be triggered again
            isys_tenantsettings::set('cmdb.renew-properties', 0);

            // Trigger the property renewal
            isys_factory::get_instance('isys_update_property_migration')
                ->set_database($database)
                ->reset_property_table()
                ->collect_category_data()
                ->prepare_sql_queries('g')
                ->prepare_sql_queries('s')
                ->prepare_sql_queries('g_custom')
                ->execute_sql();

            // Notify that the properties have been renewed
            isys_notify::info($this->language->get('LC__SYSTEM__PROPERTIES_RENEWED'));
        }

        // @see  ID-6382  Refresh the table configurations - this will be triggered by several actions (search the code for the identifier!).
        if (isys_tenantsettings::get('cmdb.refresh-table-configurations', 1)) {
            isys_tenantsettings::set('cmdb.refresh-table-configurations', 0);

            try {
                (new Refresher($database))->processAll();

                // Notify that the table configurations have been refreshed.
                isys_notify::info($this->language->get('LC__SYSTEM__TABLE_CONFIGURATIONS_REFRESHED'));
            } catch (Exception $e) {
                // Notify the user that something went wrong.
                isys_notify::info($e->getMessage(), ['sticky' => true]);
            }
        }

        $l_viewdata = '';
        $l_gets = Purify::castIntValues($this->m_modreq->get_gets());
        $l_posts = $this->m_modreq->get_posts();
        $l_tpl = $this->m_modreq->get_template();

        /* Define Drag and Drop constant */
        define("C__OBJECT_DRAGNDROP", !!isys_tenantsettings::get('cmdb.registry.object_dragndrop', 1));

        /**
         * -------------------------------------------------------------------
         *  P A R A M E T E R   -   H A N D L I N G
         * -------------------------------------------------------------------
         */

        try {
            /**
             * Handle popup request
             * Popups are CMDB-specific.
             */
            if (isset($l_gets[C__CMDB__GET__POPUP])) {
                return $this->handle_popups($l_gets[C__CMDB__GET__POPUP]);
            }

            if (isset($l_gets[C__GET__FILE_MANAGER])) {
                $this->handle_file_manager($l_gets[C__GET__FILE_MANAGER]);
            }
        } catch (isys_exception_cmdb $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        /* ---------------------------------------------------------------- */
        /* BEGIN ##1## */
        {
            // Set default viewmode.
            if (!isset($l_gets[C__CMDB__GET__VIEWMODE])) {
                if (isset($l_gets[C__CMDB__GET__OBJECT])) {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                } else {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__MISC_BLANK;
                }
            }

            /* Retrieve object type id if needed! */
            if (!isset($l_gets[C__CMDB__GET__OBJECTTYPE]) && isset($l_gets[C__CMDB__GET__OBJECT])) {
                $l_objectRes = $this->m_dao_cmdb->get_object_by_id($l_gets[C__CMDB__GET__OBJECT])
                    ->get_row();
                $l_gets[C__CMDB__GET__OBJECTTYPE] = $l_objectRes['isys_obj_type__id'];
            }

            // Set default tree viewmode ##1##
            if (!isset($l_gets[C__CMDB__GET__TREEMODE]) || !$l_gets[C__CMDB__GET__TREEMODE]) {
                if (isset($l_gets[C__CMDB__GET__OBJECT])) {
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                } else {
                    $l_res = isys_component_dao_user::instance(isys_application::instance()->database)
                        ->get_user_settings();

                    if ($l_res && !isset($l_gets['treePath'])) {
                        $l_gets[C__CMDB__GET__TREEMODE] = $l_res['isys_user_locale__default_tree_view'];
                        $l_gets[C__CMDB__GET__TREETYPE] = $l_res['isys_user_locale__default_tree_type'];

                        // This is a bugfix, which prevents throwing an exception!
                        if ((!isset($l_gets[C__CMDB__GET__OBJECT]) && $l_gets[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_OBJECT) ||
                            empty($l_gets[C__CMDB__GET__TREEMODE])) {
                            $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                        }
                    } elseif (!isset($l_gets[C__CMDB__GET__OBJECT])) {
                        $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                    }
                }
            }

            /**
             * @var $g_menu isys_component_menu
             */
            global $g_menu;
            if (!isset($l_gets[C__CMDB__GET__OBJECTGROUP]) && !isset($l_gets[C__CMDB__GET__OBJECT]) && !isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
                // @todo  Should we make this configurable? Feature-Request pending...
                if (defined('C__MAINMENU__INFRASTRUCTURE') && defined('C__OBJTYPE_GROUP__INFRASTRUCTURE') && !$g_menu->get_active_menuobj()) {
                    $l_gets[C__CMDB__GET__OBJECTGROUP] = C__OBJTYPE_GROUP__INFRASTRUCTURE;
                    isys_component_menu::set_active_menu(C__OBJTYPE_GROUP__INFRASTRUCTURE);
                } elseif (isset($l_gets[C__CMDB__GET__VIEWMODE]) && $l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__MISC_BLANK) {
                    if (is_object($g_menu)) {
                        $l_active = $g_menu->get_active_menuobj();
                        $l_default = isys_component_menu::get_default_mainmenu();

                        if (defined($l_default)) {
                            $l_gets[C__CMDB__GET__OBJECTGROUP] = constant($l_default);
                        }

                        if (isset($l_active) && !empty($l_active)) {
                            $l_gets[C__CMDB__GET__OBJECTGROUP] = substr($l_active, 0, -1);
                        }

                        isys_component_menu::set_active_menu($l_gets[C__CMDB__GET__OBJECTGROUP]);

                        $l_tpl->assign('activeMainMenuItem', $l_active);
                    }
                }
            } else {
                if (is_object($g_menu)) {
                    $l_active = $g_menu->get_active_menuobj();
                    $l_default = isys_component_menu::get_default_mainmenu();

                    if (defined($l_default)) {
                        $l_gets[C__CMDB__GET__OBJECTGROUP] = constant($l_default);
                    }
                    if (isset($l_active) && !empty($l_active)) {
                        $l_gets[C__CMDB__GET__OBJECTGROUP] = substr($l_active, 0, -1);
                    }
                    isys_component_menu::set_active_menu($l_gets[C__CMDB__GET__OBJECTGROUP]);
                    if (($l_mainmenu = isys_component_menu::get_default_menu_as_constant())) {
                        $l_tpl->assign('activeMainMenuItem', $l_mainmenu);
                    }
                    unset($l_mainmenu);
                }
            }

            // This block is responsible to set absolutely necessary parameters to the GET-Parameterlist.
            $this->m_modreq->_internal_set_private("m_get", $l_gets);
        }
        /* END ##1## */
        /* ---------------------------------------------------------------- */
        /* BEGIN ##2## */
        {
            /* Exception 1: If showing the global category and no other
               category ID is set, set it to category "global"! */
            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__CATEGORY && !isset($l_gets[C__CMDB__GET__CATG]) && !isset($l_gets[C__CMDB__GET__CATS])) {

                // See what the object type settings state
                $l_typeres = $this->m_dao_cmdb->get_object_types($l_gets[C__CMDB__GET__OBJECTTYPE]);
                $l_arrRecord = $l_typeres->get_row();
                $l_bOverview = $l_arrRecord["isys_obj_type__overview"];

                if ($l_bOverview) {
                    $l_gets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__OVERVIEW');
                } else {
                    $l_gets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__GLOBAL');
                }
            }

            /* Exception 2: If showing the type list and objTypeID is not
                set, use misc view for welcome */
            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT && !isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__MISC_BLANK;
            }

            /* This block is responsible to set absolutely necessary parameters
               to the GET-Parameterlist */
            $this->m_modreq->_internal_set_private("m_get", $l_gets);
        }
        /* END ##2## */
        /* ---------------------------------------------------------------- */

        /* Require viewmode and treemode are mandatory parameters, they must
           be set ALL times. If they are not set, they will be set manually
           in ##1## */
        $l_parameters_mandatory = [
            C__CMDB__GET__VIEWMODE => true,
            C__CMDB__GET__TREEMODE => true
        ];

        /* Parameters which won't get deleted by the conformer */
        $l_parameters_optional = [
            C__GET__FILE_MANAGER             => true,
            C__CMDB__GET__POPUP              => true,
            C__CMDB__GET__OBJECTGROUP        => true,
            C__GET__MODULE_ID                => true,
            C__GET__MAIN_MENU__NAVIGATION_ID => true,
            C__CMDB__GET__EDITMODE           => true,
            C__GET__AJAX_CALL                => true,
            C__GET__AJAX_REQUEST             => true
        ];

        /*
         * -------------------------------------------------------------------
         *  R E Q U E S T - P R O C E S S O R
         * -------------------------------------------------------------------
         */

        /*
         * -------------------------------------------------------------------
         *  V I E W - H A N D L I N G
         * -------------------------------------------------------------------
         */

        if (empty($l_gets[C__CMDB__GET__VIEWMODE])) {
            $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        }

        // Do some GUI / Interaction handling.
        switch ($l_posts[C__GET__NAVMODE]) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case C__NAVMODE__EDIT:
                // If navmode = edit, set catlevel to first selected list element.
                if (is_array($l_posts[C__GET__ID]) && $l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                    if (is_numeric($l_posts[C__GET__ID][0]) && !isset($_GET[C__CMDB__GET__CATLEVEL])) {
                        $_GET[C__CMDB__GET__CATLEVEL] = $l_gets[C__CMDB__GET__CATLEVEL] = $l_posts[C__GET__ID][0];
                    }
                } elseif ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_CATEGORY) {
                    break; // break only if no entry has been selected and category list view is active @see ID-4207
                }
                // no break
            case C__NAVMODE__NEW:

                // When clicking new or edit, we should always land inside the detail category view.
                if (isset($l_gets[C__CMDB__GET__VIEWMODE]) && $l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_CATEGORY) {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                }

                $this->m_modreq->_internal_set_private("m_get", $l_gets);
                break;
        }

        // Skip view handling if its not needed.
        if (!$g_ajax || $g_ajax && !strstr($_GET[C__GET__AJAX_CALL], "tree")) {
            try {
                $l_content_top = null;
                $l_content_bottom = null;

                /**
                 * @var isys_cmdb_view
                 */
                $l_view = $this->m_view_manager->get_view($l_gets[C__CMDB__GET__VIEWMODE]);

                if (is_object($l_view)) {
                    // Ask view object for its navigation parameters.
                    $l_view->get_mandatory_parameters($l_parameters_mandatory);
                    $l_view->get_optional_parameters($l_parameters_optional);

                    $GLOBALS["g_cmdb_view"] = &$l_view;

                    if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE && $_GET[C__GET__AJAX] == '1') {
                        // Saving is regularly done via ajax so we don't need to process any views here.
                        if (method_exists($l_view, 'process_save')) {
                            $l_viewdata = $l_view->process_save();
                        }
                    } else {
                        $l_viewdata = $l_view->process();

                        // Does the view require a module request?
                        // @todo No view should require a reload of the cmdb module ! A reload is always a performance loss!
                        if ($l_view->requires_module_reload()) {
                            $l_view->trigger_module_reload();

                            // ... and restart the module.
                            return $this->start();
                        }
                    }

                    // Get filenames of templates associated with the view.
                    if (method_exists($l_view, 'get_template_top')) {
                        $l_content_top = $l_view->get_template_top();
                    }

                    if (method_exists($l_view, 'get_template_bottom')) {
                        $l_content_bottom = $l_view->get_template_bottom();
                    }

                    // Get name of template placeholder, where we write the data returned by process() in and assign it.
                    if (!empty($l_viewdata) && method_exists($l_view, 'get_template_destination')) {
                        $l_tpl->assign($l_view->get_template_destination(), $l_viewdata);
                    }
                }
            } catch (isys_exception_auth $e) {
                $l_tpl->assign('exception', $e->write_log());
                $l_content_bottom = 'exception-auth.tpl';
            } catch (isys_exception $e) {
                isys_application::instance()->container['notify']->error($e->getMessage());

                return $e->getMessage();
            } catch (Exception $e) {
                // Trigger system.exceptionTriggered, because this is not an isys_exception.
                isys_component_signalcollection::get_instance()
                    ->emit('system.exceptionTriggered', $e);

                isys_application::instance()->container['notify']->error($e->getMessage());

                return $e->getMessage();
            }

            // Set content templates.
            if (isset($l_content_top) && $l_content_top != null) {
                $index_includes["contenttop"] = $l_content_top;
            }

            if ($l_content_bottom != null) {
                $index_includes["contentbottomcontent"] = $l_content_bottom;
            }

            // Emit viewProcessed.
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.viewProcessed", isset($l_view) ? $l_view : null, $l_viewdata);
        }

        /*
         * -------------------------------------------------------------------
         *  T R E E - H A N D L I N G
         * -------------------------------------------------------------------
         */

        // Skip tree processing if it's not needed.
        if (!$g_ajax || $g_ajax && strstr($_GET[C__GET__AJAX_CALL], "tree")) {
            // Attach tree buttons
            $index_includes['lefttreetop'] = __DIR__ . '/templates/tree/buttons.tpl';

            $l_has_location_view_right = isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOCATION_VIEW');
            $l_tpl->assign("bShowMenuTreeButtons", true);

            if (is_value_in_constants($l_gets[C__CMDB__GET__OBJECTTYPE], ['C__OBJTYPE__PARALLEL_RELATION', 'C__OBJTYPE__RELATION']) && !$l_gets[C__CMDB__GET__OBJECT]) {
                $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_RELATION;
            }

            $l_tbloc_gets = $l_gets;
            // @see  ID-6614  We need to check if the variable is set to prevent writing zeroes
            if (isset($l_gets[C__CMDB__GET__OBJECT])) {
                $l_tbobj_gets[C__CMDB__GET__OBJECT] = (int)$l_gets[C__CMDB__GET__OBJECT];
            }

            // @see  ID-6614  We need to check if the variable is set to prevent writing zeroes
            if (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
                $l_tbobj_gets[C__CMDB__GET__OBJECTTYPE] = (int)$l_gets[C__CMDB__GET__OBJECTTYPE];
            }

            // @see  ID-6614  We need to check if the variable is set to prevent writing zeroes
            if (isset($l_gets[C__CMDB__GET__OBJECTGROUP])) {
                $l_tbobj_gets[C__CMDB__GET__OBJECTGROUP] = (int)$l_gets[C__CMDB__GET__OBJECTGROUP];
            }

            // @see  ID-6614  We need to check if the variable is set to prevent writing zeroes
            if (isset($l_gets[C__CMDB__GET__TREETYPE])) {
                $l_tbobj_gets[C__CMDB__GET__TREETYPE] = (int)$l_gets[C__CMDB__GET__TREETYPE];
            }

            if ($l_gets[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__CATEGORY_GLOBAL || is_numeric($l_gets[C__CMDB__GET__OBJECT])) {
                $l_tbobj_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;

                if (!isset($l_gets[C__CMDB__GET__TREEMODE])) {
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                }
            } else {
                $l_tbobj_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
            }

            $l_strMenuTreeButtonClass_location = "";
            $l_strMenuTreeButtonClass_object = "";

            if ($l_gets[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_LOCATION) {
                $l_strMenuTreeButtonClass_location = "active";
            } else {
                $l_strMenuTreeButtonClass_object = "active";
            }

            $l_tbloc_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_LOCATION;

            $l_arMenuTree = [
                "objectLink"    => isys_glob_build_ajax_url(C__FUNC__AJAX__TREE_LOCATION, $l_tbobj_gets),
                "locationLink"  => isys_glob_build_ajax_url(C__FUNC__AJAX__TREE_LOCATION, $l_tbloc_gets),
                "objectClass"   => $l_strMenuTreeButtonClass_object,
                "locationClass" => $l_strMenuTreeButtonClass_location,
            ];

            $l_treeMode = isys_glob_get_param(C__CMDB__GET__TREEMODE) ?: (isset($l_gets[C__CMDB__GET__TREEMODE]) ? $l_gets[C__CMDB__GET__TREEMODE] : C__CMDB__VIEW__TREE_OBJECT);
            $l_treeType = isys_glob_get_param(C__CMDB__GET__TREETYPE) ?: (isset($l_gets[C__CMDB__GET__TREETYPE]) ? $l_gets[C__CMDB__GET__TREETYPE] : C__CMDB__VIEW__TREE_LOCATION__LOCATION);

            $l_tpl->assign('has_location_view_right', $l_has_location_view_right)
                ->assign("menuTree", $l_arMenuTree)
                ->assign("treeMode", $l_treeMode)
                ->assign("treeType", $l_treeType);

            if (isset($l_gets[C__GET__NAVMODE]) && $l_gets[C__GET__NAVMODE]) {
                $l_posts[C__GET__NAVMODE] = $l_gets[C__GET__NAVMODE];
                $this->m_modreq->_internal_set_private("m_post", $l_posts);
            }

            // Get tree view.
            $l_tree = $this->m_view_manager->get_view($l_gets[C__CMDB__GET__TREEMODE]);

            if ($l_tree) {
                try {
                    $l_treedata = $l_tree->process();

                    // Tree data.
                    if (!empty($l_treedata)) {
                        // For the ajax based object tree, the tree data is a script initializing the tree, not the actual content.
                        if ($l_tree->get_id() == C__CMDB__VIEW__TREE_LOCATION) {
                            // Build and output ajax tree.
                            $l_tpl->assign("menu_tree_script", $l_treedata);
                        } else {
                            // Build and output tree.
                            $l_tpl->assign("menu_tree", $l_treedata);
                        }
                    }
                } catch (isys_exception $l_e) {
                    isys_application::instance()->container['notify']->error($l_e->getMessage());

                    return $l_e->getMessage();
                }
            }
        }

        /**
         * Load dashboard if viewmode = blank
         */
        if ($l_gets[C__CMDB__GET__VIEWMODE] === C__CMDB__VIEW__MISC_BLANK) {
            // Load Dashboard
            isys_module_dashboard::instance()
                ->init($this->m_modreq)
                ->load_user_dashboard()
                ->process_view();
        }

        /**
         * -------------------------------------------------------------------
         *  REQUEST CONFORMER
         * -------------------------------------------------------------------
         */
        try {
            if ($l_parameters_mandatory && $l_parameters_optional) {
                // Reset GET-Parameters to mandatory and optional parameters.
                $this->request_conformer($l_parameters_mandatory, $l_parameters_optional);
            }
        } catch (isys_exception_cmdb $l_e) {
            echo $l_e->getMessage();
        }

        /* Replace the GET and POST superglobals by the module request ones */
        isys_glob_merge_globals_by_modreq($this->m_modreq);

        return true;
    }

    /**
     * This navigation-point needs to be separated, see #3990.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function build_tree_listedit(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__CMDB')) {
            return;
        }
        global $g_dirs;

        if (null !== $p_parent && is_int($p_parent)) {
            $l_mod_gets = $_GET;

            if ($p_system_module) {
                $l_mod_gets[C__GET__MODULE_SUB_ID] = C__MODULE__CMDB;
            }

            unset($l_mod_gets[C__CMDB__GET__OBJECTTYPE], $l_mod_gets['what']);

            $l_mod_gets[C__GET__SETTINGS_PAGE] = 'list';
            $l_mod_gets[C__GET__TREE_NODE] = C__MODULE__CMDB . '01337';
            $p_tree->add_node(C__MODULE__CMDB . '01337', $p_parent, $this->language->get("LC__SETTINGS__CMDB__LISTS"), isys_glob_build_url(isys_glob_http_build_query($l_mod_gets)), null, $g_dirs["images"] . "/icons/silk/cog.png");
        }
    }

    /**
     * Method to create the object list in the user configuration
     *
     * @return bool|string
     * @throws isys_exception_auth
     */
    public function get_object_type_list()
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__CMDB')) {
            return;
        }

        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);

        $l_allowed_obj_types = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttypes();

        isys_application::instance()->template->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');

        if ($l_allowed_obj_types !== false) {
            $l_data = $l_dao->get_object_types($l_allowed_obj_types);
        } else {
            throw new isys_exception_auth($this->language->get('LC__AUTH__EXCEPTION__MISSING_RIGHTS_ON_OBJECT_TYPES'));
        }

        if ($l_data->num_rows() > 0) {
            $listDao = new isys_cmdb_dao_list_objecttype(isys_application::instance()->container->get('database'));
            $l_objList = new isys_component_list(null, $l_data, $listDao);
            $link = isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                C__GET__TREE_NODE     => $_GET[C__GET__TREE_NODE],
                C__GET__SETTINGS_PAGE => 'list',
                C__CMDB__GET__EDITMODE => C__EDITMODE__ON,
                C__CMDB__GET__OBJECTTYPE =>'[{isys_obj_type__id}]'
            ]);

            $l_objList->config($listDao->get_fields(), $link, "[{isys_obj_type__id}]", true);

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        } else {
            return false;
        }
    }

    /**
     * Multivalue category settings
     *
     * @return null
     * @throws Exception
     * @throws isys_exception_auth
     * @throws isys_exception_general
     */
    public function list_settings()
    {
        global $index_includes;

        isys_auth_cmdb::instance()
            ->multilist_config(isys_auth::EXECUTE);
        $db = $this->m_dao_cmdb->get_database_component();
        $dist = new isys_cmdb_dao($db);

        foreach ([C__CMDB__GET__CATG_CUSTOM => 'g_custom', C__CMDB__GET__CATG => 'g', C__CMDB__GET__CATS => 's', C__CMDB__GET__CATD => 'd'] as $get => $p) {
            if (isset($_GET[$get]) && $_GET[$get]) {
                $catId = $_GET[$get];
                $categoryParam = $p;
                $param = $get;
                break;
            }
        }

        if (!isset($catId, $categoryParam, $param) || !method_exists($dist, 'get_all_cat'.$categoryParam)) {
            throw new Exception('Cannot find category');
        }
        $category = $dist->{'get_all_cat'.$categoryParam}($catId)->get_row();
        if (!$category || !class_exists($category['isysgui_cat'. $categoryParam .'__class_name'])) {
            throw new Exception('Cannot find category');
        }
        $dao = $category['isysgui_cat'. $categoryParam .'__class_name']::instance($db);
        if ($param === C__CMDB__GET__CATG_CUSTOM) {
            $listDao = 'isys_cmdb_dao_list_catg_custom_fields';
            $dao->set_catg_custom_id($catId);
        } else {
            $listDao = $dao->get_category_list();
        }
        if (!$listDao) {
            throw new Exception('Cannot find category');
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_active(false, C__NAVBAR_BUTTON__CANCEL);

        /**
         * @var $listDao isys_cmdb_dao_list
         */
        $listDao = $listDao::instance($db);
        $listDao->set_dao_category($dao);
        if ($param === C__CMDB__GET__CATG_CUSTOM) {
            $listDao->set_properties($dao->get_properties());
        }

        $list = isys_component_list::factory(null, null, $listDao, $listDao->get_rec_status(), 'html');
        $tableConfig = $list->get_table_config();
        $defaultTableConfig = $list->getConfigWithAllProperties();

        if (isset($_POST['resetDefault']) && $_POST['resetDefault'] == 'default_values') {
            $tableConfig = $list->get_default_table_config();
        }

        // if save action - apply values from Form into TableConfig
        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE && isset($_POST['list__HIDDEN']) && $_POST['resetDefault'] !== 'default_values') {
            $properties = json_decode($_POST['list__HIDDEN'], true);
            $defaultProperties = [];
            foreach ($defaultTableConfig->getProperties() as $property) {
                $defaultProperties[$property->getPropertyKey()] = $property;
            }
            $tableConfig->setProperties([]);
            foreach ($properties as $property) {
                if ($defaultProperties[$property] && $defaultProperties[$property] instanceof Property) {
                    $tableConfig->addProperty($defaultProperties[$property]);
                }
            }
            $tableConfig->setRowClickable(isset($_POST['row_clickable']) && $_POST['row_clickable'] == 'on');
            $tableConfig->setFilterWildcard(isset($_POST['default_filter_wildcard']) && $_POST['default_filter_wildcard'] == 'on');
            $tableConfig->setShowEmailLinks(isset($_POST['show_email_links']) && $_POST['show_email_links'] == 'on');
            $tableConfig->setBroadsearch(isset($_POST['default_filter_broadsearch']) && $_POST['default_filter_broadsearch'] == 'on');
            if (isset($_POST['default_filter_field'], $defaultProperties[$_POST['default_filter_field']])) {
                $tableConfig->setFilterProperty($_POST['default_filter_field']);
            }
            if (isset($_POST['default_filter_value'])) {
                $tableConfig->setFilterValue($_POST['default_filter_value']);
            }
            if (isset($_POST['default_sorting'])) {
                $tableConfig->setSortingProperty($_POST['default_sorting']);
            } else {
                // @see  ID-5981  Reset the session, if default sorting was switched off.
                $tableConfig->setSortingProperty(null);
            }
            if (isset($_POST['sorting_direction'])) {
                $tableConfig->setSortingDirection($_POST['sorting_direction']);
            }
            if (isset($_POST['grouping_type'])) {
                $tableConfig->setGroupingType($_POST['grouping_type']);
            }
            if (isset($_POST['advanced_option_memory_unit'])) {
                $tableConfig->setAdvancedOptionMemoryUnit($_POST['advanced_option_memory_unit']);
            }
            if ($_POST['as_default'] == '1') {
                $list->save_default_table_config($tableConfig);
            } elseif ($_POST['for_users'] == '1' && isys_format_json::is_json_array($_POST['users'])) {
                foreach (isys_format_json::decode($_POST['users']) as $uid) {
                    $list->save_table_config($tableConfig, $uid);
                }
            } else {
                $list->save_table_config($tableConfig);
            }
            isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
        }
        // prepare selected properties in format, expected in property selector
        $l_selected_properties = [];
        foreach ($tableConfig->getProperties() as $property) {
            $l_selected_properties[$property->getPropertyKey()] = $property;
        }
        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT],
                C__CMDB__GET__CATG_CUSTOM => $_GET[C__CMDB__GET__CATG_CUSTOM],
                C__CMDB__GET__CATG => $_GET[C__CMDB__GET__CATG],
                C__CMDB__GET__CATD => $_GET[C__CMDB__GET__CATD],
                C__CMDB__GET__CATS => $_GET[C__CMDB__GET__CATS]
            ]);
        } else {
            $link = isys_helper_link::create_url([
                C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
                C__GET__MODULE_SUB_ID => defined_or_default('C__MODULE__CMDB'),
                C__GET__SETTINGS_PAGE => 'catlist'
            ]);
        }

        $this->displayTableConfig(
            $defaultTableConfig,
            $tableConfig,
            $this->language->get('LC__SETTINGS__CMDB__MULTIVALUE_LISTS') . ' » ' . $this->language->get($dao->getCategoryTitle()),
            $l_selected_properties,
            null,
            true,
            true,
            $link
        );

        $index_includes['contentbottomcontent'] = "modules/cmdb/multi_list_config.tpl";
    }

    /**
     * Renders the list with all multivalue categories
     */
    public function multivalueCategoryList()
    {
        isys_auth_cmdb::instance()
            ->multilist_config(isys_auth::EXECUTE);

        isys_application::instance()->template->assign('content_title', $this->language->get('LC__SETTINGS__CMDB__MULTIVALUE_LISTS'));
        $dao = $this->m_dao_cmdb;
        $db = $this->m_dao_cmdb->get_database_component();
        $filter = $_GET['tableFilter'] ?: [];
        $categories = [];

        foreach ($dao->get_all_categories() as $type => $allCategories) {
            switch ($type) {
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $param = C__CMDB__GET__CATS;
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    $param = C__CMDB__GET__CATG_CUSTOM;
                    break;
                default:
                    $param = C__CMDB__GET__CATG;
            }
            $typeCategories = array_filter($allCategories, function ($category) use ($db) {
                if ($category['list_multi_value'] != '1' || $category['status'] != '2' || !class_exists($category['class_name'])) {
                    return false;
                }
                if ($category['class_name'] === 'isys_cmdb_dao_category_g_custom_fields') {
                    return true;
                }
                $cat = $category['class_name']::instance($db);
                return $cat && $cat->get_category_list() && class_exists($cat->get_category_list());
            });
            foreach ($typeCategories as &$category) {
                $category['id'] = '&' . $param . '=' . $category['id'];
                $category['title'] = $this->language->get($category['title']);
                if ($category['parent'] && $allCategories[$category['parent']]) {
                    $category['title'] = $this->language->get($allCategories[$category['parent']]['title']) . ' » ' . $category['title'];
                }
                $categories[] = $category;
            }
        }
        $isAnd = !isset($filter['operation']) || $filter['operation'] !== '1';
        $categories = array_filter($categories, function ($cat) use ($isAnd, $filter) {
            foreach ($filter as $k => $v) {
                if ($k === 'operation') {
                    continue;
                }
                if ($v === '') {
                    continue;
                }
                $pass = stripos($cat[$k], $v) !== false;
                if (!$isAnd && $pass) {
                    return true;
                }
                if ($isAnd && !$pass) {
                    return false;
                }
            }
            return $isAnd;
        });

        if (isset($_GET['orderBy'])) {
            $field = $_GET['orderBy'];
            $cmp = $_GET['orderByDir'] == 'ASC' ? 1 : -1;
            usort($categories, function ($a, $b) use ($field, $cmp) {
                return $a[$field] > $b[$field]?$cmp:-$cmp;
            });
        }

        $config = new Config();
        $config->addProperty(new Property('title', '', '', $this->language->get('LC__UNIVERSAL__TITLE')));
        $config->addProperty(new Property('const', '', '', $this->language->get('LC__UNIVERSAL__CONSTANT')));
        $config->setPaging(isset($_GET['page']) ? $_GET['page'] : 1);
        $headers = array_map(function ($prop) {
            return $prop->getName();
        }, $config->getProperties());

        $routeParams = [];
        parse_str($_SERVER['QUERY_STRING'], $routeParams);
        if (isset($routeParams['tableFilter'])) {
            foreach ($routeParams['tableFilter'] as $k => $v) {
                $routeParams["tableFilter[$k]"] = $v;
            }
        }
        unset($routeParams['tableFilter'], $routeParams['only_content'], $routeParams['scope']);
        $options = [
            'idField'                  => 'id',
            'rowClick'                 => true,
            'rowClickURL'              => isys_helper_link::create_url([
                C__GET__MODULE_ID        => defined_or_default('C__MODULE__SYSTEM'),
                C__GET__MODULE_SUB_ID    => defined_or_default('C__MODULE__CMDB'),
                C__GET__SETTINGS_PAGE    => 'cat_list',
                '' => '%id%'
            ]),
            'scoped'                   => $_GET[C__GET__SCOPE],
            'columnSizes'              => ['60%', '40%'],
            'routeParams'              => $routeParams,
            'rowsPerPage'              => (int)$_GET['rowsPerPage'] ?: \isys_usersettings::get('gui.objectlist.rows-per-page', 50),
            'replacerOptions'          => isys_format_json::encode([]),
            'filter'                   => true,
            'filterColumns'            => [
                'title' => $this->language->get('LC__UNIVERSAL__TITLE'),
                'const' => $this->language->get('LC__UNIVERSAL__CONSTANT')
            ],
            'filterDefaultValues'      => $filter,
            'filterDefaultColumn'      => [
                'title' => $this->language->get('LC__UNIVERSAL__TITLE'),
                'field' => 'title',
            ],
            'order'                    => true,
            'orderColumns'             => ['title', 'const'],
            'orderDefaultColumn'       => $_GET['orderBy'],
            'orderDefaultDirection'    => $_GET['orderByDir'],
            'autocomplete'             => false
        ];
        $table = new Table(new ArrayAdapter($categories), $config, $headers, $options);
        $html = $table->render(true);
        if (isys_core::is_ajax_request() && isset($_GET['only_content']) && $_GET['only_content']) {
            echo $html;
            die();
        }
        // Assign evaluated list into template.
        isys_application::instance()->container->get('template')->assign('objectTableList', $html);
        global $index_includes;
        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    /**
     * System settings menu
     *
     * @return null
     * @throws Exception
     * @throws isys_exception_auth
     * @throws isys_exception_general
     */
    public function system_settings()
    {
        global $index_includes, $g_dirs;

        $database = isys_application::instance()->container->get('database');

        switch ($_GET[C__GET__SETTINGS_PAGE]) {
            case 'list':
                isys_auth_cmdb::instance()
                    ->list_config(isys_auth::EXECUTE);

                isys_component_template_navbar::getInstance()
                    ->set_active(false, C__NAVBAR_BUTTON__NEW)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE);

                $l_objectTypeId = $_GET[C__CMDB__GET__OBJECTTYPE];

                if ((isset($_GET[C__CMDB__GET__OBJECTTYPE]) && $_GET[C__CMDB__GET__OBJECTTYPE] > 0) || (isset($_POST[C__GET__ID]) && $_POST[C__GET__ID][0] > 0) || (isset($_POST['list_objtype_id']) && $_POST['list_objtype_id'] > 0)) {
                    if ($_POST[C__GET__ID][0] > 0) {
                        $_GET[C__CMDB__GET__OBJECTTYPE] = $_POST[C__GET__ID][0];
                    } elseif ($_POST['list_objtype_id'] > 0) {
                        $_GET[C__CMDB__GET__OBJECTTYPE] = $_POST['list_objtype_id'];
                    }

                    $l_dao = new isys_cmdb_dao_category_property_ng($database);
                    $l_g_distributor = new isys_cmdb_dao_distributor($database, 1, C__CMDB__CATEGORY__TYPE_GLOBAL);
                    $l_s_distributor = new isys_cmdb_dao_distributor($database, 1, C__CMDB__CATEGORY__TYPE_SPECIFIC);
                    $l_custom_dao = new isys_cmdb_dao_category_g_custom_fields($database);
                    $l_selected_properties = [];

                    /**
                     * @var $l_dao_objtype isys_cmdb_dao_object_type
                     */
                    $l_dao_objtype = isys_cmdb_dao_object_type::instance($database);
                    $l_object_type = $l_dao_objtype->get_object_types($_GET[C__CMDB__GET__OBJECTTYPE])
                        ->get_row();

                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(false, C__NAVBAR_BUTTON__CANCEL);

                    /**
                     * SAVE
                     */
                    if (isset($_POST['list__HIDDEN']) && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE && $_POST['resetDefault'] != 'default_values') {
                        /**
                         * Clearing filter caches
                         */
                        foreach ([
                                     $l_objectTypeId,
                                     'sort-' . $l_objectTypeId,
                                     'paging-' . $l_objectTypeId . '-2',
                                     'paging-' . $l_objectTypeId . '-3',
                                     'paging-' . $l_objectTypeId . '-4'
                                 ] as $key) {
                            $source = Configuration::initSessionSource($key);
                            $source->clear();
                        }
                        $l_grouping_type = $_POST['grouping_type'];

                        $l_dao->setGrouping($l_grouping_type);

                        $l_query = $l_dao->create_property_query_for_lists($_POST['list__HIDDEN_IDS'], $l_objectTypeId);
                        $l_props = isys_format_json::decode($_POST['list__HIDDEN'], true);
                        $l_default_sorting = (empty($_POST['default_sorting'])) ? false : $_POST['default_sorting'];
                        $l_sorting_direction = $_POST['sorting_direction'];
                        $l_default_filter_fields = [];
                        $l_default_filter_field = $_POST['default_filter_field'];
                        $l_default_filter_value = $_POST['default_filter_value'] === '-1' ? null : $_POST['default_filter_value'];
                        $l_keyName = 0;
                        $l_row_clickable = false;
                        $l_default_filter_wildcard = $_POST['default_filter_wildcard'];
                        $showEmailLinks = $_POST['show_email_links'];
                        $l_default_filter_broadsearch = $_POST['default_filter_broadsearch'];
                        $l_list_config = [];
                        $l_advanced_option_memory_unit = $_POST['advanced_option_memory_unit'];

                        $l_tresor = [];
                        if (is_array($l_props) && count($l_props) > 0) {
                            $tableConfig = new Config;

                            if ($l_default_sorting > 0) {
                                $l_default_sorting_property = isys_cmdb_dao_category_property::factory($database)
                                    ->retrieve_properties($l_default_sorting)
                                    ->get_row();

                                $tableConfig->setSortingProperty($l_default_sorting_property['class'] . '__' . $l_default_sorting_property['key'])
                                    ->setSortingDirection($l_sorting_direction);
                            }

                            if (!empty($l_default_filter_field)) {
                                $tableConfig->setFilterProperty($l_default_filter_field);
                            }

                            if (!empty($l_default_filter_value)) {
                                $tableConfig->setFilterValue($l_default_filter_value);
                            }

                            $tableConfig->setAdvancedOptionMemoryUnit($l_advanced_option_memory_unit)
                                ->setGroupingType($l_grouping_type)
                                ->setRowClickable(isset($_POST['row_clickable']) && $_POST['row_clickable'] === 'on')
                                ->setFilterWildcard(isset($_POST['default_filter_wildcard']) && $_POST['default_filter_wildcard'] === 'on')
                                ->setBroadsearch(isset($_POST['default_filter_broadsearch']) && $_POST['default_filter_broadsearch'] === 'on')
                                ->setShowEmailLinks(isset($_POST['show_email_links']) && $_POST['show_email_links'] === 'on');

                            foreach ($l_props as $l_category_info) {
                                foreach ($l_category_info as $l_cat_type => $l_category) {
                                    foreach ($l_category as $l_cat_const => $l_selected_property) {
                                        if ($l_cat_type === 'g') {
                                            $l_category = $l_g_distributor->get_category(constant($l_cat_const));
                                            $l_category_title = $l_category->get_catg_name_by_id_as_string(constant($l_cat_const));
                                        } elseif ($l_cat_type === 's') {
                                            $l_category = $l_s_distributor->get_category(constant($l_cat_const));
                                            $l_category_title = $l_category->get_cats_name_by_id_as_string(constant($l_cat_const));
                                        } else {
                                            $l_category = $l_custom_dao;
                                            $l_category->set_catg_custom_id(constant($l_cat_const));
                                            $l_category_title = $l_category->get_cat_custom_name_by_id_as_string(constant($l_cat_const));
                                        }

                                        if (is_object($l_category) && method_exists($l_category, 'get_properties')) {
                                            $l_custom_id = null;

                                            if (strpos($l_selected_property[0], '_') !== 0) {
                                                $l_properties = $l_category->get_properties();
                                                $l_method_info = get_class($l_category) . '::get_properties';
                                                $l_property_type = C__PROPERTY_TYPE__STATIC;

                                                if ($l_cat_type === 'g_custom') {
                                                    $l_custom_id = constant($l_cat_const);
                                                }

                                                if (isset($l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                                                } elseif (!isset($l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                                } else {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                                                }

                                                if ($l_category instanceof \isys_cmdb_dao_category_g_custom_fields) {
                                                    $l_field = $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] . '###' .
                                                        constant($l_cat_const);
                                                }

                                                $l_callback = false;
                                            } else {
                                                $l_properties = $l_category->get_dynamic_properties();
                                                $l_method_info = get_class($l_category) . '::get_dynamic_properties';
                                                $l_property_type = C__PROPERTY_TYPE__DYNAMIC;
                                                $l_field = false;
                                                $l_callback = [
                                                    get_class($l_properties[$l_selected_property[0]][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]),
                                                    $l_properties[$l_selected_property[0]][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                                ];
                                            }

                                            // Generate unique fieldname.
                                            ++$l_keyName;

                                            // Store translated title and keynames.
                                            $l_tresor[$this->language->get($l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE])][] = $l_keyName;

                                            $l_alias = get_class($l_category) . '__' . $l_selected_property[0];

                                            $l_list_config[$l_keyName] = [
                                                $l_property_type,
                                                $l_selected_property[0],
                                                $l_field,
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
                                                $l_method_info,
                                                $l_callback,
                                                $l_category_title,
                                                $l_custom_id,
                                                $l_alias
                                            ];

                                            if ($l_properties[$l_selected_property[0]][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST]) {
                                                $l_default_filter_fields[get_class($l_category) . '__' .
                                                $l_selected_property[0]] = $this->language->get($l_category_title) . ' > ' . $this->language->get($l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
                                            }

                                            $tableConfig->addProperty(new Property(
                                                get_class($l_category),
                                                $l_selected_property[0],
                                                $l_category_title,
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__DATA][C__PROPERTY__DATA__INDEX],
                                                $l_custom_id,
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                                                $l_properties[$l_selected_property[0]][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__FILTERABLE]
                                            ));
                                        }
                                    }
                                }
                            }

                            // Walk through the tresor.
                            foreach ($l_tresor as $l_tres) {
                                // Double title detected.
                                if (is_countable($l_tres) && count($l_tres) > 1) {
                                    // Add category title to the property title.
                                    if (!empty($l_list_config)) {
                                        foreach ($l_tres as $l_prop) {
                                            $l_list_config[$l_prop][3] = $this->language->get($l_list_config[$l_prop][3]) . " (" . $this->language->get($l_list_config[$l_prop][6]) . ")";
                                        }
                                    }
                                }
                            }

                            if (isset($_POST['row_clickable']) && $_POST['row_clickable'] === 'on') {
                                $l_row_clickable = true;
                            }

                            if (isset($_POST['default_filter_wildcard']) && $_POST['default_filter_wildcard'] === 'on') {
                                $l_default_filter_wildcard = true;
                            }

                            if (isset($_POST['default_filter_broadsearch']) && $_POST['default_filter_broadsearch'] === 'on') {
                                $l_default_filter_broadsearch = true;
                            }

                            if (isset($_POST['show_email_links']) && $_POST['show_email_links'] === 'on') {
                                $showEmailLinks = true;
                            }

                            // Check for given users (when overwriting configurations).
                            if ($_POST['as_default'] == '1') {
                                $l_obj_type_const = $_POST['object_type'];

                                if (defined($l_obj_type_const)) {
                                    if (is_array($l_list_config)) {
                                        isys_tenantsettings::set('cmdb.default-object-list.config.' . $l_obj_type_const, isys_format_json::encode($l_list_config));
                                    }

                                    if (is_object($tableConfig)) {
                                        isys_tenantsettings::set('cmdb.default-object-table.config.' . $l_obj_type_const, serialize($tableConfig));
                                    }

                                    isys_tenantsettings::set('cmdb.default-object-table.sql.' . $l_obj_type_const, $l_query);

                                    isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                                    die;
                                } else {
                                    isys_notify::error('The given constant "' . $l_obj_type_const . '" does not exist!', ['sticky' => true]);
                                    die;
                                }
                            }

                            $l_users = [null];

                            if ($_POST['for_users'] == '1' && isys_format_json::is_json_array($_POST['users'])) {
                                $l_users = isys_format_json::decode($_POST['users']);
                            }

                            foreach ($l_users as $l_user) {
                                $l_dao_objtype->save_list_config(
                                    $l_object_type['isys_obj_type__id'],
                                    isys_format_json::encode($l_list_config),
                                    $l_query,
                                    $l_row_clickable,
                                    $l_default_sorting,
                                    $l_sorting_direction,
                                    $l_user,
                                    $tableConfig,
                                    $l_default_filter_wildcard,
                                    $l_default_filter_broadsearch,
                                    $showEmailLinks
                                );
                            }

                            $l_selected_properties = $_POST['list__HIDDEN'];

                            try {
                                // Reset Cache
                                isys_cache::keyvalue()
                                    ->flush();

                                isys_notify::success(sprintf($this->language->get('LC__CMDB__OBJECT_LIST__SUCCESS'), count($l_dao->retrieve($l_query))));
                            } catch (isys_exception_database $e) {
                                isys_notify::error($e->getMessage(), ['sticky' => true]);
                            }
                        } else {
                            isys_notify::error($this->language->get('LC__REPORT__NO_ATTRIBUTES_ADDED'), ['sticky' => true]);
                        }
                    } else {
                        $l_class = $l_object_type['isys_obj_type__class_name'];

                        if (class_exists($l_class) && is_subclass_of($l_class, 'isys_cmdb_dao_list_objects')) {
                            $l_list_dao = isys_factory::get_instance($l_class, $database);
                        } else {
                            $l_list_dao = new isys_cmdb_dao_list_objects($database);
                        }

                        // We set the object type, so that we know what we have to look for in the isys_obj_type_list table.
                        $l_list_dao->set_object_type($l_object_type);

                        if ($_POST['resetDefault'] === 'default_values') {
                            $tableConfig = $l_list_dao->get_table_config(true);
                        } else {
                            $tableConfig = $l_list_dao->get_table_config();
                        }
                        $l_selection = $tableConfig->getProperties();

                        // Is the row clickable?
                        $l_row_clickable = $tableConfig->isRowClickable();
                        $l_default_sorting = $tableConfig->getSortingProperty();
                        $l_sorting_direction = $tableConfig->getSortingDirection();
                        $l_grouping_type = $tableConfig->getGroupingType();
                        $l_default_filter_field = $tableConfig->getFilterProperty();
                        $l_default_filter_value = $tableConfig->getFilterValue();
                        $l_default_filter_fields = [];
                        $l_default_filter_wildcard = $tableConfig->isFilterWildcard();
                        $l_default_filter_broadsearch = $tableConfig->isBroadsearch();
                        $l_advanced_option_memory_unit = $tableConfig->getAdvancedOptionMemoryUnit();
                        $showEmailLinks = $tableConfig->getShowEmailLinks();

                        /**
                         * @var $categoryDaos isys_cmdb_dao_category[]
                         */
                        $categoryDaos = [];

                        foreach ($tableConfig->getProperties() as $l_property) {
                            $categoryClass = $l_property->getClass();

                            if (!class_exists($categoryClass)) {
                                continue;
                            }

                            if (!isset($categoryDaos[$categoryClass])) {
                                $categoryDaos[$categoryClass] = new $categoryClass($database);
                            }

                            $property = $categoryDaos[$categoryClass]->get_property_by_key($l_property->getKey());

                            // ID-4396: If a property is available for object lists and filterable, it should be shown
                            if ($property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST]) {
                                $l_default_filter_fields[$categoryClass . '__' . $l_property->getKey()] = $this->language->get($l_property->getCategoryName()) . ' > ' . $this->language->get($l_property->getName());
                            }
                        }

                        unset($categoryDaos);

                        if (is_array($l_selection) && count($l_selection) > 0) {
                            foreach ($l_selection as $l_prop) {
                                $l_category_class = $l_prop->getClass();

                                if (class_exists($l_category_class)) {
                                    /**
                                     * @var $l_category_dao isys_cmdb_dao_category
                                     */
                                    $l_category_dao = isys_factory::get_instance($l_category_class, $database);

                                    $l_category_id = $l_category_dao->get_category_id();
                                    $l_cat_type = substr($l_category_dao->get_category_type_abbr(), 3);

                                    if (strpos($l_category_class, 'isys_cmdb_dao_category_g_custom_fields') === 0) {
                                        $l_cat_type = 'g_custom';
                                        $l_category_id = $l_prop->getCustomCatID();
                                    } else {
                                        // Fixing a problem where it was not possible to re-edit the person properties in list configuration
                                        if ($l_category_id == defined_or_default('C__CATS__PERSON')) {
                                            $l_category_id = defined_or_default('C__CATS__PERSON_MASTER');
                                        }

                                        // Fix a problem for person group. See ID-1217
                                        if ($l_category_id == defined_or_default('C__CATS__PERSON_GROUP_MASTER')) {
                                            $l_category_id = defined_or_default('C__CATS__PERSON_GROUP');
                                        }
                                    }

                                    if ($l_category_id > 0) {
                                        $l_selected_properties[] = [
                                            $l_cat_type => [
                                                $l_category_id => [
                                                    $l_prop->getKey()
                                                ]
                                            ]
                                        ];
                                    }
                                }
                            }
                        }

                        $l_selected_properties = isys_format_json::encode($l_selected_properties);
                    }

                    if (!empty($l_default_sorting)) {
                        if (strpos($l_default_sorting, '__') !== false) {
                            [$class, $propKey] = explode('__', $l_default_sorting);

                            if (class_exists($class) && method_exists($class, 'get_category_const')) {
                                /**
                                 * @var $l_category_dao isys_cmdb_dao_category
                                 */
                                $l_category_dao = isys_factory::get_instance($class, $database);

                                $l_default_sorting = $l_dao->retrieve('SELECT isys_property_2_cat__id
                                    FROM isys_property_2_cat
                                    WHERE isys_property_2_cat__cat_const = ' . $l_dao->convert_sql_text($l_category_dao->get_category_const()) . '
                                    AND isys_property_2_cat__prop_key = ' . $l_dao->convert_sql_text($propKey) . '
                                    LIMIT 1;')
                                    ->get_row_value('isys_property_2_cat__id');
                            }
                        }
                    }

                    $l_grouping_data = [
                        $l_dao::C__GROUPING__COMMA => $this->language->get('LC__MODULE__CMDB__GROUPING_TYPE_COMMA'),
                        $l_dao::C__GROUPING__LIST  => $this->language->get('LC__MODULE__CMDB__GROUPING_TYPE_LIST')
                    ];

                    isys_component_template_navbar::getInstance()
                        ->append_button(
                            $this->language->get('LC__MODULE__CMDB__LIST__JUMP_TO_LIST'),
                            'back',
                            [
                                "icon" => "icons/silk/table.png",
                                "url" => isys_helper_link::create_url([
                                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_OBJECT,
                                    C__CMDB__GET__OBJECTTYPE => $_GET[C__CMDB__GET__OBJECTTYPE]
                                ])
                            ]
                        )
                        ->append_button(
                            $this->language->get('LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG'),
                            'reset',
                            [
                                "icon" => "icons/silk/cross.png",
                                "js_onclick" => "confirm('{$this->language->get('LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG_CONFIRM')}') && $('resetDefault').setValue('default_values') && $('isys_form').submit()"
                            ]
                        );

                    // ID-2755 This should fix the bug.
                    isys_application::instance()->container->get('template')
                        ->activate_editmode()
                        ->assign('img_dir', $g_dirs['images'])
                        ->assign('selected_properties', $l_selected_properties)
                        ->assign('provides', C__PROPERTY__PROVIDES__LIST)
                        ->assign('objecttype', $l_object_type)
                        ->assign('list_obj_type_id', $_GET[C__CMDB__GET__OBJECTTYPE])
                        ->assign('row_clickable', $l_row_clickable)
                        ->assign('default_sorting', $l_default_sorting)
                        ->assign('sorting_data', [
                            'ASC'  => $this->language->get('LC__CMDB__SORTING__ASC'),
                            'DESC' => $this->language->get('LC__CMDB__SORTING__DESC')
                        ])
                        ->assign('defined_sorting', $l_sorting_direction)
                        ->assign('groupingSelection', $l_grouping_type)
                        ->assign('groupingData', $l_grouping_data)
                        ->assign('defaultFilterFields', $l_default_filter_fields)
                        ->assign('defaultFilterField', $l_default_filter_field)
                        ->assign('defaultFilterValue', $l_default_filter_value)
                        ->assign('ajax_url', isys_helper_link::create_url($_GET))
                        ->assign('has_right_to_overwrite', isys_auth_cmdb::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'OVERWRITE_USER_LIST_CONFIG'))
                        ->assign('has_right_to_define_standard', isys_auth_cmdb::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'DEFINE_STANDARD_LIST_CONFIG'))
                        ->assign('default_filter_wildcard', $l_default_filter_wildcard)
                        ->assign('show_email_links', $showEmailLinks)
                        ->assign('default_filter_broadsearch', $l_default_filter_broadsearch)
                        ->assign('defined_memory_unit', ($l_advanced_option_memory_unit ?: 'GB'))
                        ->assign('memory_unit_data', [
                            -1   => $this->language->get('LC__MODULE__CMDB__ADVANCED_OPTION__MEMORY_UNIT_DEFAULT_VALUE'),
                            'B'  => $this->language->get('LC__CMDB__MEMORY_UNIT__Bytes'),
                            'KB' => $this->language->get('LC__CMDB__MEMORY_UNIT__KB'),
                            'MB' => $this->language->get('LC__CMDB__MEMORY_UNIT__MB'),
                            'GB' => $this->language->get('LC__CMDB__MEMORY_UNIT__GB'),
                            'TB' => $this->language->get('LC__CMDB__MEMORY_UNIT__TB')
                        ]);
                } else {
                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__EDIT);

                    isys_application::instance()->template->assign('g_list', $this->get_object_type_list());
                }

                if (isset($l_object_type) && is_array($l_object_type) && !empty($l_object_type)) {
                    isys_application::instance()->template->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__OBJECT_LIST') . ' &raquo ' . $this->language->get($l_object_type['isys_obj_type__title']));
                } else {
                    isys_application::instance()->template->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__OBJECT_LIST'));
                }

                $index_includes['contentbottomcontent'] = "modules/cmdb/list_config.tpl";
                break;

            case 'validation':
                if (defined('C__MODULE__PRO')) {
                    isys_auth_system_tools::instance()
                        ->validation(isys_auth::EXECUTE);

                    $l_lc_global = $this->language->get('LC__UNIVERSAL__GLOBAL');
                    $l_lc_custom = $this->language->get('LC__CMDB__CATG__CUSTOM_CATEGORY');
                    $l_lc_specific = $this->language->get('LC__UNIVERSAL__SPECIFIC');

                    $l_category_dialog = [
                        $l_lc_global   => [],
                        $l_lc_specific => [],
                        $l_lc_custom   => []
                    ];

                    // @todo  This array SHOULD only be used for some very tricky categories.
                    $l_category_blacklist = [
                        C__CMDB__CATEGORY__TYPE_GLOBAL   => filter_defined_constants([
                            'C__CATG__IMAGES',
                            'C__CATG__LOGBOOK',
                            // C__CATG__OPERATING_SYSTEM, // ID-5826
                            'C__CATG__STORAGE',          // ID-3237  Removing categories with no properties to validate.
                            'C__CATG__SANPOOL',          // ID-3237  Removing categories with no properties to validate.
                            'C__CATG__CABLING',          // ID-3237  Removing categories with no properties to validate.
                            'C__CATG__VIRTUAL_AUTH'      // ID-3237  Removing categories with no properties to validate.
                        ]),
                        C__CMDB__CATEGORY__TYPE_SPECIFIC => filter_defined_constants([
                            'C__CATS__BASIC_AUTH'        // ID-3237  Removing categories with no properties to validate.
                        ])
                    ];

                    $appendix = [];
                    $l_prop_dao = isys_cmdb_dao_category_property::instance($database);

                    $l_categories = $l_prop_dao->get_all_categories([
                        isys_cmdb_dao_category_property::TYPE_EDIT,
                        isys_cmdb_dao_category_property::TYPE_FOLDER
                    ]);

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $id => $l_catg) {
                        if (!class_exists($l_catg['class_name']) || in_array($id, $l_category_blacklist[C__CMDB__CATEGORY__TYPE_GLOBAL])) {
                            continue;
                        }

                        $key = 'g:' . $l_catg['class_name'];

                        if (!isset($l_category_dialog[$l_lc_global][$key])) {
                            $l_category_dialog[$l_lc_global][$key] = [];
                        }

                        $l_category_dialog[$l_lc_global][$key][] = $this->language->get($l_catg['title']);
                    }

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] as $id => $l_cats) {
                        if (!class_exists($l_cats['class_name']) || in_array($id, $l_category_blacklist[C__CMDB__CATEGORY__TYPE_SPECIFIC])) {
                            continue;
                        }

                        $key = 's:' . $l_cats['class_name'];

                        $l_obj_types = [];

                        $l_obj_type_rows = $l_prop_dao->get_object_types_by_category($id, 's', false, true);

                        foreach ($l_obj_type_rows as $l_obj_type) {
                            $l_obj_types[] = $this->language->get($l_obj_type['isys_obj_type__title']);
                        }

                        if (count($l_obj_types) > 0) {
                            if (!isset($appendix[$key])) {
                                $appendix[$key] = [];
                            }

                            $appendix[$key][] = implode(', ', $l_obj_types);
                        }

                        if (!isset($l_category_dialog[$l_lc_specific][$key])) {
                            $l_category_dialog[$l_lc_specific][$key] = [];
                        }

                        $l_category_dialog[$l_lc_specific][$key][] = $this->language->get($l_cats['title']);
                    }

                    foreach ($l_categories[C__CMDB__CATEGORY__TYPE_CUSTOM] as $id => $l_catc) {
                        $key = 'c:' . $id;

                        if (!isset($l_category_dialog[$l_lc_custom][$key])) {
                            $l_category_dialog[$l_lc_custom][$key] = [];
                        }

                        $l_category_dialog[$l_lc_custom][$key][] = $this->language->get($l_catc['title']);
                    }

                    // Concatinate category names and sort the categories alphabetically.
                    foreach ($l_category_dialog as &$categories) {
                        foreach ($categories as $className => &$categoryNames) {
                            $categoryNames = implode(' / ', $categoryNames);

                            if (isset($appendix[$className])) {
                                $categoryNames .= ' (' . implode(', ', $appendix[$className]) . ')';
                            }
                        }

                        asort($categories);
                    }

                    // Set some rules for the form elements.
                    $l_rules = [
                        'cmdb-validation-category-selector' => [
                            'p_arData'          => $l_category_dialog,
                            'p_bInfoIconSpacer' => 0,
                            'p_bDbFieldNN'      => true,
                            'p_bSort'           => false,
                            'p_strSelectedID'   => 0
                        ],
                        'cmdb-validation-rules-template'    => [
                            'p_arData'          => [
                                'FILTER_VALIDATE_INT'    => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__TYPE_INT'),
                                'FILTER_VALIDATE_FLOAT'  => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__TYPE_FLOAT'),
                                'FILTER_VALIDATE_REGEXP' => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__TYPE_REGEX'),
                                'FILTER_VALIDATE_EMAIL'  => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__TYPE_EMAIL'),
                                'FILTER_VALIDATE_URL'    => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__TYPE_URL'),
                                'VALIDATE_BY_TEXTFIELD'  => $this->language->get('LC__SETTINGS__CMDB__VALIDATION__BY_TEXTFIELD')
                            ],
                            'p_bInfoIconSpacer' => 0,
                            'p_strClass'        => 'input input-small rule-selector ml5 mt5',
                            'p_bSort'           => false
                        ]
                    ];

                    // Load the defined configurations.
                    $l_configured_categories = [];
                    $l_validation_rows = isys_cmdb_dao_validation::instance($database)->get_data();

                    if (is_countable($l_validation_rows) && count($l_validation_rows)) {
                        while ($l_row = $l_validation_rows->get_row()) {
                            $l_catc_id = $l_row['isys_validation_config__isysgui_catg_custom__id'];
                            $categoryClass = $l_row['isys_validation_config__category_class'];

                            // We just need the configured category IDs.
                            if ($l_row['isys_validation_config__isysgui_catg__id'] > 0 && isset($l_category_dialog[$l_lc_global]['g:' . $categoryClass])) {
                                $l_configured_categories[] = 'g:' . $categoryClass;
                            }

                            if ($l_row['isys_validation_config__isysgui_cats__id'] > 0 && isset($l_category_dialog[$l_lc_specific]['s:' . $categoryClass])) {
                                $l_configured_categories[] = 's:' . $categoryClass;
                            }

                            if ($l_catc_id > 0 && isset($l_category_dialog[$l_lc_custom]['c:' . $l_catc_id])) {
                                $l_configured_categories[] = 'c:' . $l_catc_id;
                            }
                        }
                    }

                    isys_component_template_navbar::getInstance()
                        ->set_active(true, C__NAVBAR_BUTTON__SAVE);

                    isys_application::instance()->container->get('template')
                        ->activate_editmode()
                        ->assign('configured_categories', array_values($l_configured_categories))
                        ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

                    $index_includes['contentbottomcontent'] = $g_dirs['class'] . 'modules/pro/templates/modules/cmdb/validation_config.tpl';
                }
                break;
            case 'objectTypeListConfig':
                $l_posts = $this->m_modreq->get_posts();
                $l_gets = $this->m_modreq->get_gets();
                $l_gets = Purify::castIntValues($l_gets);

                /**
                 * @var isys_cmdb_view
                 */
                $l_view = $this->m_view_manager->get_view($l_gets[C__CMDB__GET__VIEWMODE]);

                if (is_object($l_view)) {
                    if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                        if (method_exists($l_view, 'handle_navmode')) {
                            $l_viewdata = $l_view->handle_navmode($l_posts[C__GET__NAVMODE]);
                            isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                        }

                        // Does the view require a module request?
                        if ($l_view->requires_module_reload()) {
                            $l_view->trigger_module_reload();

                            // ... and restart the module.
                            return $this->start();
                        }
                    } else {
                        if (empty($l_posts) && $l_gets[C__CMDB__GET__EDITMODE] || $l_posts[C__GET__NAVMODE] == C__NAVMODE__CANCEL) {
                            unset($l_gets[C__CMDB__GET__EDITMODE], $_POST[C__CMDB__GET__EDITMODE], $_GET[C__CMDB__GET__EDITMODE]);
                            $this->m_modreq->_internal_set_private('m_get', $l_gets);
                        }
                        $l_viewdata = $l_view->process();

                        // Does the view require a module request?
                        if ($l_view->requires_module_reload()) {
                            $l_view->trigger_module_reload();

                            // ... and restart the module.
                            return $this->start();
                        }
                    }

                    if (method_exists($l_view, 'get_template_bottom')) {
                        $l_content_bottom = $l_view->get_template_bottom();
                        $index_includes["contentbottomcontent"] = $l_content_bottom;
                    }

                    // Get name of template placeholder, where we write the data returned by process() in and assign it.
                    if (!empty($l_viewdata) && method_exists($l_view, 'get_template_destination')) {
                        $this->m_modreq->get_template()
                            ->assign($l_view->get_template_destination(), $l_viewdata);
                    }

                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.viewProcessed", isset($l_view) ? $l_view : null, $l_viewdata);
                }
                break;
        }

        return null;
    }

    /**
     * @param Config $defaultTableConfig
     * @param Config $tableConfig
     * @param        $l_dao
     * @param        $l_selected_properties
     * @param        $l_object_type
     * @param bool   $showOverwriteUserList
     * @param bool   $showOverwriteGlobalList
     * @param null   $backLink
     */
    protected function displayTableConfig(Config $defaultTableConfig, Config $tableConfig, $title, $l_selected_properties, $l_object_type, $showOverwriteUserList = true, $showOverwriteGlobalList = true, $backLink = null)
    {
        $l_default_filter_fields = [];
        foreach ($tableConfig->getProperties() as $property) {
            $l_default_filter_fields[$property->getPropertyKey()] = $this->language->get($property->getName());
        }

        $l_default_sorting = $tableConfig->getSortingProperty();

        $l_grouping_data = [
            isys_cmdb_dao_category_property_ng::C__GROUPING__COMMA => $this->language->get('LC__MODULE__CMDB__GROUPING_TYPE_COMMA'),
            isys_cmdb_dao_category_property_ng::C__GROUPING__LIST  => $this->language->get('LC__MODULE__CMDB__GROUPING_TYPE_LIST')
        ];

        global $g_dirs;
        isys_component_template_navbar::getInstance()
            ->append_button(
                $this->language->get('LC__MODULE__CMDB__LIST__JUMP_TO_LIST'),
                'back',
                [
                    "icon" => "icons/silk/table.png",
                    "url" => $backLink
                ]
            )
            ->append_button(
                $this->language->get('LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG'),
                'reset',
                [
                    "icon" => "icons/silk/cross.png",
                    "js_onclick" => "confirm('{$this->language->get('LC__MODULE__CMDB__RESTORE_DEFAULT_LIST_CONFIG_CONFIRM')}') && $('resetDefault').setValue('default_values') && $('isys_form').submit()"
                ]
            );

        // ID-2755 This should fix the bug.
        isys_application::instance()->template->activate_editmode()
            ->assign('img_dir', $g_dirs['images'])
            ->assign('content_title', $title)
            ->assign('properties', $defaultTableConfig->getProperties())
            ->assign('selected_properties', $l_selected_properties)
            ->assign('provides', C__PROPERTY__PROVIDES__LIST)
            ->assign('objecttype', $l_object_type)
            ->assign('list_obj_type_id', $_GET[C__CMDB__GET__OBJECTTYPE])
            ->assign('row_clickable', $tableConfig->isRowClickable())
            ->assign('default_sorting', $l_default_sorting)
            ->assign('sorting_data', ['ASC' => $this->language->get('LC__CMDB__SORTING__ASC'), 'DESC' => $this->language->get('LC__CMDB__SORTING__DESC')])
            ->assign('defined_sorting', $tableConfig->getSortingDirection())
            ->assign('groupingSelection', $tableConfig->getGroupingType())
            ->assign('groupingData', $l_grouping_data)
            ->assign('defaultFilterFields', $l_default_filter_fields)
            ->assign('defaultFilterField', $tableConfig->getFilterProperty())
            ->assign('defaultFilterValue', $tableConfig->getFilterValue())
            ->assign('ajax_url', isys_helper_link::create_url($_GET))
            ->assign(
                'has_right_to_overwrite',
                $showOverwriteUserList &&
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'OVERWRITE_USER_MULTILIST_CONFIG')
            )
            ->assign(
                'has_right_to_define_standard',
                $showOverwriteGlobalList &&
                isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'DEFINE_STANDARD_MULTILIST_CONFIG')
            )
            ->assign('default_filter_wildcard', $tableConfig->isFilterWildcard())
            ->assign('default_filter_broadsearch', $tableConfig->isBroadsearch())
            ->assign('defined_memory_unit', ($tableConfig->getAdvancedOptionMemoryUnit() ?: 'GB'))
            ->assign(
                'memory_unit_data',
                [
                    -1   => $this->language->get('LC__MODULE__CMDB__ADVANCED_OPTION__MEMORY_UNIT_DEFAULT_VALUE'),
                    'B'  => $this->language->get('LC__CMDB__MEMORY_UNIT__Bytes'),
                    'KB' => $this->language->get('LC__CMDB__MEMORY_UNIT__KB'),
                    'MB' => $this->language->get('LC__CMDB__MEMORY_UNIT__MB'),
                    'GB' => $this->language->get('LC__CMDB__MEMORY_UNIT__GB'),
                    'TB' => $this->language->get('LC__CMDB__MEMORY_UNIT__TB')
                ]
            );
    }

    /**
     * @param $p_popuptype
     *
     * @return bool
     */
    private function handle_popups($p_popuptype)
    {
        global $g_output_done;
        global $g_dirs;
        global $g_config;

        $l_popupcls = "isys_popup_" . $p_popuptype;

        if (class_exists($l_popupcls)) {
            /**
             * @var isys_component_popup
             */
            $l_popup = new $l_popupcls;
            $l_gets = $this->m_modreq->get_gets();

            if (isset($l_gets[C__GET__AJAX_REQUEST]) && method_exists($l_popup, "handle_ajax_request")) {
                try {
                    // Return the contents while dying.
                    die($l_popup->handle_ajax_request($this->m_modreq));
                } catch (Exception $e) {
                    die($e->getMessage());
                }
            }

            $l_popuptpl = $l_popup->handle_module_request($this->m_modreq)
                ->assign("dir_tools", $g_config["www_dir"] . "src/tools/")
                ->assign("dir_images", $g_dirs["images"]);

            if ($l_popuptpl != null) {
                $l_popuptpl->display("popup/main.tpl");
                $g_output_done = true;

                return true;
            }
        }

        return false;
    }

    /**
     * @param $p_reqtype
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     */
    private function handle_file_manager($p_reqtype)
    {
        global $g_dirs;

        try {
            $l_gets = $this->m_modreq->get_gets();
            $l_file_manager = new isys_component_filemanager();

            switch ($p_reqtype) {
                case "image":
                    $l_image_url = $l_gets["file"];
                    $l_file_manager->set_upload_path(isys_application::instance()->getOrCreateUploadImageDir($l_image_url));

                    /**
                     * send directly outputs the file to the client
                     */
                    if (!$l_file_manager->send($l_image_url, null, C_FILES__MODE_VIEW)) {
                        header("HTTP/1.0 404 Not Found");
                        die;
                    }
                    break;
                case "get":
                    if (isset($l_gets[C__GET__FILE__ID])) {
                        $l_physical_id = $l_gets[C__GET__FILE__ID];
                        $l_filename = null;
                        $l_files = null;

                        if (!empty($l_gets[C__GET__FILE_NAME])) {
                            $l_filename = $l_gets[C__GET__FILE_NAME];
                            $l_file_manager->set_upload_path(isys_application::instance()->getOrCreateUploadFileDir($l_filename));
                        } else {
                            $l_dao_file = new isys_cmdb_dao_category_s_file($this->m_dao_cmdb->get_database_component());
                            $l_files = $l_dao_file->get_filemanager_dao_by_physical_file_id($l_physical_id);
                            $filename = $l_files->get_row_value('isys_physical_filename');
                            $l_file_manager->set_upload_path(isys_application::instance()->getOrCreateUploadFileDir($filename));
                            $l_files = $l_dao_file->get_filemanager_dao_by_physical_file_id($l_physical_id);
                        }

                        /**
                         * send directly outputs the file to the client
                         */
                        if (!$l_file_manager->send($l_filename, $l_files, C_FILES__MODE_DOWNLOAD)) {
                            header("HTTP/1.0 404 Not Found");
                            die;
                        }
                    }
                    break;
            }
        } catch (isys_exception_cmdb $e) {
            throw $e;
        }
    }

    /**
     * Conforms the request to mandatory and optional GET-Parameters
     * to care for a "clean" navigation. Yes, we have too many. :-)
     *
     * @param array $mandatoryParameters
     * @param array $optionalParameters
     *
     * @return integer
     * @throws isys_exception_cmdb
     */
    private function request_conformer($mandatoryParameters, $optionalParameters)
    {
        $l_parameters_result = [];
        $l_result_count = 0;
        $l_gets = $this->m_modreq->get_gets();
        $l_gets = Purify::castIntValues($l_gets);

        // Process mandatory parameters.
        if (is_array($mandatoryParameters) && count($mandatoryParameters)) {
            // Filter out necessary parameters.
            foreach ($mandatoryParameters as $key => $value) {
                if (!isset($l_gets[$key])) {
                    throw new isys_exception_cmdb(
                        "Request or navigation error.\n" .
                        "Current GET-parameters are: \n" . var_export($l_gets, true) . "\n" .
                        "Mandatory parameters are:\n" . var_export($mandatoryParameters, true)
                    );
                }

                $l_parameters_result[$key] = $l_gets[$key];
                $l_result_count++;
            }
        }

        // Process optional parameters
        if (is_array($optionalParameters) && count($optionalParameters)) {
            foreach ($optionalParameters as $key => $value) {
                if (isset($l_gets[$key])) {
                    $l_parameters_result[$key] = $l_gets[$key];
                    $l_result_count++;
                }
            }
        }

        // Conform GET-Parameters result parameters.
        $this->m_modreq->_internal_set_private("m_get", $l_parameters_result);

        // And return count of new parameters (this is the same as count($this->m_modreq->get_gets()).
        return $l_result_count;
    }

    /**
     * @return string
     */
    public static function get_dir()
    {
        return __DIR__;
    }

    /**
     * Returns the module's container extension.
     *
     * @return ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new CmdbExtension();
    }

    /**
     * @deprecated Keep for backward compatibility until i-doit 1.17
     */
    public static function get_auth()
    {
        return self::getAuth();
    }

    /**
     * @return isys_auth_cmdb
     */
    public static function getAuth()
    {
        return isys_auth_cmdb::instance();
    }
}
