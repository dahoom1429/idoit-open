<?php

/**
 * i-doit
 *
 * Template module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\AddOn\AuthableInterface;
use idoit\Context\Context;

define('TPL_PID__EXISTING', 1);
define('TPL_PID__NEW', 2);
define('TPL_PID__NEW_OBJET', 3);
define('TPL_PID__SETTINGS', 4);
define('TPL_PID__MASS_CHANGE', 5);

/**
 * Class isys_module_templates
 */
class isys_module_templates extends isys_module implements AuthableInterface, isys_module_authable
{
    /**
     *
     */
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    /**
     *
     */
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * Skip these categories for export.
     *
     * @var  array
     */
    protected static $m_cat_skips = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [
            'C__CATG__LOGBOOK',
            'C__CATG__OVERVIEW',
            'C__CATG__NETWORK_PORT',
            'C__CATG__NETWORK_PORT_OVERVIEW',
            'C__CATG__STORAGE',
            'C__CATG__SANPOOL',
            'C__CATG__ITS_LOGBOOK',
            'C__CATG__CABLING',
            'C__CATG__CLUSTER_ROOT',
            'C__CATG__CLUSTER_VITALITY',
            'C__CATG__CLUSTER_SHARED_VIRTUAL_SWITCH',
            'C__CATG__CLUSTER_SHARED_STORAGE',
            'C__CATG__RELATION',
            'C__CATG__COMPUTING_RESOURCES',
            'C__CATG__IT_SERVICE_RELATIONS',
            'C__CATG__OBJECT_VITALITY',
            'C__CATG__RACK_VIEW'
        ],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => [
            'C__CATS__RELATION_DETAILS',
            'C__CATS__CHASSIS_VIEW',
            'C__CATS__PDU_OVERVIEW',
            'C__CATS__LICENCE_OVERVIEW'
        ]
    ];

    /**
     * @var  boolean
     */
    protected static $m_licenced = true;

    /**
     * @var
     */
    protected $m_db;

    /**
     *
     */
    private $m_rec_status = C__RECORD_STATUS__TEMPLATE;

    /**
     * The isys_module_request instance.
     *
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Method for retrieving any module specific additional links.
     *
     * @static
     * @return  array
     */
    public static function get_additional_links()
    {
        global $g_dirs;

        $l_return = [];

        if (defined('C__MODULE__PRO') && defined('C__MODULE__TEMPLATES') && defined('C__MODULE__CMDB')) {
            $l_return['MASS_CHANGES'] = [
                'LC__MASS_CHANGE',
                isys_helper_link::create_url([
                    C__GET__MODULE_ID => C__MODULE__TEMPLATES,
                    C__GET__MODULE    => C__MODULE__CMDB
                ]),
                C__MODULE__CMDB,
                // Module parent
                $g_dirs['images'] . 'icons/silk/table_row_insert.png',
                // Sub module icon
            ];
        }

        return $l_return;
    }

    /**
     * @param null $p_mod
     *
     * @return string
     */
    public static function get_module_title($p_mod = null)
    {
        if ($p_mod == defined_or_default('C__MODULE__CMDB')) {
            return 'LC__MASS_CHANGE';
        }

        return 'LC__MODULE__TEMPLATES';
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @throws Exception
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__TEMPLATES') || !defined('C__MODULE__CMDB')) {
            return;
        }
        $l_root = 0;
        $l_parent = -1;
        $l_submodule = '';
        $p_tree->set_tree_sort(false);

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__TEMPLATES;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } elseif ($_GET[C__GET__MODULE] == C__MODULE__CMDB) {
            if (defined('C__MODULE__PRO')) {
                $l_root = $p_tree->add_node(C__MODULE__TEMPLATES . '0', $l_parent, $this->language->get('LC__MASS_CHANGE'));
            }
        } else {
            $l_root = $p_tree->add_node(C__MODULE__TEMPLATES . '0', $l_parent, 'Templates');
        }

        if (!$p_system_module) {
            if ($_GET[C__GET__MODULE] == C__MODULE__CMDB) {
                if (defined('C__MODULE__PRO')) {
                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE,
                        $l_root,
                        $this->language->get('LC__MASS_CHANGE'),
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' .
                        C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__MASS_CHANGE,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE)
                    );

                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__NEW,
                        $l_root,
                        $this->language->get('LC__MASS_CHANGE__CREATE_NEW_TEMPLATE'),
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' .
                        C__MODULE__TEMPLATES . TPL_PID__NEW . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                    );

                    $p_tree->add_node(
                        C__MODULE__TEMPLATES . TPL_PID__EXISTING,
                        $l_root,
                        $this->language->get('LC__MASS_CHANGE__EXISTING_TEMPLATES'),
                        '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . '&' . C__GET__MODULE . '=' . C__MODULE__CMDB . '&' . C__GET__TREE_NODE . '=' .
                        C__MODULE__TEMPLATES . TPL_PID__EXISTING . '&' . C__GET__SETTINGS_PAGE . '=' . TPL_PID__EXISTING,
                        '',
                        '',
                        (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                        '',
                        '',
                        isys_auth_templates::instance()
                            ->is_allowed_to(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                    );
                }
            } else {
                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET,
                    $l_root,
                    $this->language->get('LC__TEMPLATES__CREATE_OBJECTS'),
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET . '&' .
                    C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW_OBJET,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET)
                );

                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__NEW,
                    $l_root,
                    $this->language->get('LC__TEMPLATES__NEW_TEMPLATE'),
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__NEW . '&' .
                    C__GET__SETTINGS_PAGE . '=' . TPL_PID__NEW,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)
                );

                $p_tree->add_node(
                    C__MODULE__TEMPLATES . TPL_PID__EXISTING,
                    $l_root,
                    $this->language->get('LC__TEMPLATES__EXISTING_TEMPLATES'),
                    '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . TPL_PID__EXISTING . '&' .
                    C__GET__SETTINGS_PAGE . '=' . TPL_PID__EXISTING,
                    '',
                    '',
                    (($_GET[C__GET__SETTINGS_PAGE] == '1') ? 1 : 0),
                    '',
                    '',
                    isys_auth_templates::instance()
                        ->is_allowed_to(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)
                );
            }
        }

        if ($p_system_module) {
            $p_tree->add_node(
                C__MODULE__TEMPLATES . '4',
                $l_root,
                $this->language->get('LC__CATS__AC_SETTINGS'),
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TEMPLATES . '4' . '&' .
                C__GET__SETTINGS_PAGE . '=4'
            );
        }
    }

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param    string $p_text
     * @param    string $p_link
     *
     * @return   boolean
     * @author   Van Quyen Hoang <qhoang@i-doit.org>
     * @throws Exception
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_url_exploded = explode('?', $_SERVER['HTTP_REFERER']);
        $l_url_parameters = $l_url_exploded[1];
        $l_parameters_exploded = explode('&', $l_url_parameters);

        $l_params = array_pop(array_map(function ($p_arg) {
            $l_return = [];
            foreach ($p_arg as $l_content) {
                [$l_key, $l_value] = explode('=', $l_content);
                $l_return[$l_key] = $l_value;
            }

            return $l_return;
        }, [$l_parameters_exploded]));

        if (isset($l_params[C__GET__MODULE])) {
            // Mass changes
            $p_text[] = $this->language->get('LC__MASS_CHANGE');

            switch ($l_params[C__GET__SETTINGS_PAGE]) {
                case TPL_PID__MASS_CHANGE:
                    // Do nothing.
                    break;

                case TPL_PID__NEW:
                    $p_text[] = $this->language->get('LC__MASS_CHANGE__CREATE_NEW_TEMPLATE');
                    break;

                case TPL_PID__EXISTING:
                default:
                    $p_text[] = $this->language->get('LC__MASS_CHANGE__EXISTING_TEMPLATES');
                    break;
            }
        } else {
            // Templates.
            $p_text[] = $this->language->get('LC__MODULE__TEMPLATES');
            switch ($l_params[C__GET__SETTINGS_PAGE]) {
                case TPL_PID__NEW:
                    $p_text[] = $this->language->get('LC__TEMPLATES__NEW_TEMPLATE');
                    break;

                case TPL_PID__NEW_OBJET:
                    $p_text[] = $this->language->get('LC__TEMPLATES__CREATE_OBJECTS');
                    break;

                case TPL_PID__EXISTING:
                default:
                    $p_text[] = $this->language->get('LC__TEMPLATES__EXISTING_TEMPLATES');
                    break;
            }
        }

        $p_link = $l_url_parameters;

        return true;
    }

    /**
     * Starts module process
     *
     * @return $this|void
     * @throws isys_exception_general
     * @throws Exception
     */
    public function start()
    {
        global $index_includes;

        // Unpack request package.
        $l_gets = $this->m_userrequest->get_gets();
        $l_posts = $this->m_userrequest->get_posts();
        $l_template = $this->m_userrequest->get_template();

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $l_tree = $this->m_userrequest->get_menutree();

            // Process tree.
            $this->build_tree($l_tree, false);

            // Assign tree.
            $l_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        }

        try {
            $this->process($l_gets, $l_posts);
        } catch (isys_exception_general $e) {
            throw $e;
        } catch (isys_exception_auth $e) {
            $l_template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    }

    /**
     * Initializes the module
     *
     * @param   isys_module_request & $p_req
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

    /**
     * Method for retrieving the module-ID.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__TEMPLATES');
    }

    /**
     * Method for replacing all placeholders.
     *
     * @param string  $p_xml
     * @param string  $p_title
     * @param string  $p_type
     * @param integer $p_id
     * @param string  $p_sysid
     * @param string  $p_created
     * @param string  $p_created_by
     * @param string  $p_updated
     * @param string  $p_updated_by
     * @param null    $p_description
     * @param null    $p_cmdb_status
     * @param int     $p_status
     *
     * @return array|string
     * @throws isys_exception_database
     * @throws Exception
     */
    public function modify_xml_header(
        $p_xml,
        $p_title,
        $p_type,
        $p_id = null,
        $p_sysid = null,
        $p_created = null,
        $p_created_by = null,
        $p_updated = null,
        $p_updated_by = null,
        $p_description = null,
        $p_cmdb_status = null,
        $p_status = C__RECORD_STATUS__NORMAL
    ) {
        $l_modified = $p_xml;

        if (!empty($p_type) && !empty($p_xml)) {
            $l_dao = isys_cmdb_dao::instance($this->m_db);
            $l_res = $l_dao->get_objtype($p_type);
            $l_row = $l_res->get_row();
            unset($l_res);
            $l_res = $l_dao->get_object_group_by_id($l_row['isys_obj_type__isys_obj_type_group__id']);
            $l_row2 = $l_res->get_row();

            /* DEFAULTS */
            if (!empty($p_id)) {
                $l_res3 = $l_dao->get_object_by_id($p_id);
                if ($l_res3->num_rows() > 0) {
                    $l_row3 = $l_res3->get_row();
                    $p_created = $l_row3['isys_obj__created'];
                    $p_created_by = $l_row3['isys_obj__created_by'];
                    $p_updated = $l_row3['isys_obj__updated'];
                    $p_updated_by = $l_row3['isys_obj__updated_by'];
                    $p_sysid = $l_row3['isys_obj__sysid'];
                    $p_cmdb_status = $l_row3['isys_obj__isys_cmdb_status__id'];

                    if (empty($p_cmdb_status)) {
                        $p_cmdb_status = $l_row3["isys_obj__isys_cmdb_status__id"];
                    }

                    if (empty($p_status)) {
                        $p_status = $l_row3["isys_obj__status"];
                    }
                }
            }

            if (empty($p_id)) {
                $p_id = 42424242;
            }
            if (empty($p_created)) {
                $p_created = date('Y-m-d H:i:s');
            }
            if (empty($p_created_by)) {
                $p_created_by = $_SESSION['username'];
            }
            if (empty($p_updated)) {
                $p_updated = date('Y-m-d H:i:s');
            }
            if (empty($p_updated_by)) {
                $p_updated_by = $_SESSION['username'];
            }

            if (empty($p_sysid)) {
                $l_sysid_prefix = (!empty($l_row['isys_obj_type__sysid_prefix'])) ? $l_row['isys_obj_type__sysid_prefix'] : C__CMDB__SYSID__PREFIX;
                $l_sysid_suffix = ($l_sysid_prefix == C__CMDB__SYSID__PREFIX) ? time() : ($l_dao->get_last_obj_id_from_type() + 1);

                $p_sysid = $l_sysid_prefix . $l_sysid_suffix;

                if (strlen($p_sysid) < 13) {
                    $l_zeros = '';
                    for ($i = 0;$i < (13 - strlen($p_sysid));$i++) {
                        $l_zeros .= '0';
                    }
                    $l_sysid_suffix = $l_zeros . $l_sysid_suffix;
                    $p_sysid = $l_sysid_prefix . $l_sysid_suffix;
                }

                // generate sysid till its unique
                if ($l_sysid_prefix == C__CMDB__SYSID__PREFIX) {
                    $l_counter = 1;
                    while ($l_dao->get_obj_id_by_sysid($p_sysid)) {
                        $p_sysid = $l_sysid_prefix . ($l_sysid_suffix + $l_counter);
                        $l_counter++;
                    }
                }
            }

            $keys = [
                '%TITLE%',
                '%OBJTYPEID%',
                '%ID%',
                '%SYSID%',
                '%CREATED%',
                '%CREATEDBY%',
                '%UPDATED%',
                '%DESCRIPTION%',
                '%UPDATEDBY%',
                '%OBJTYPECONST%',
                '%OBJTYPETITLE%',
                '%OBJTYPELANG%',
                '%OBJTYPEGROUP%',
                '%STATUS%',
                '%CMDB_STATUS%'
            ];

            $values = [
                $p_title,
                $p_type,
                $p_id,
                $p_sysid,
                $p_created,
                $p_created_by,
                $p_updated,
                $p_description,
                $p_updated_by,
                $l_row['isys_obj_type__const'],
                $this->language->get($l_row['isys_obj_type__title']),
                $l_row['isys_obj_type__title'],
                $l_row2['isys_obj_type_group__const'],
                $p_status,
                $p_cmdb_status
            ];

            if (is_array($l_modified)) {
                foreach ($l_modified as $l_key => $l_value) {
                    if (!is_array($l_value)) {
                        $l_modified[$l_key] = str_replace($keys, $values, $l_value);
                    }
                }
            } else {
                $l_modified = str_replace($keys, $values, $l_modified);
            }
        }

        return $l_modified;
    }

    /**
     * Creates object by template.
     *
     * @param   array   $p_templates       Array of templates to use; format: array(1,2,3,4).
     * @param   integer $p_object_type     Object type id to create.
     * @param   string  $p_title           Object title.
     * @param   integer $p_obj_id          Use template to the given Object ID if exists.
     * @param   boolean $p_html_output     Output debug messages as html, if false, no output is made.
     * @param   integer $p_count           Amount of objects to create.
     * @param   string  $p_suffix_type
     * @param   string  $p_category
     * @param   string  $p_purpose
     * @param   string  $p_append_to_title Appends this string to the object title if count > 1; Placeholder "##COUNT##" stands for the current iterator.
     * @param   integer $p_start_at
     * @param   integer $p_zero_point_calc
     * @param   integer $p_zero_points
     *
     * @return  bool|mixed|null
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     * @throws isys_exception_template
     * @throws Exception
     * @throws Exception
     */
    public function create_from_template(
        $p_templates,
        $p_object_type,
        $p_title,
        $p_obj_id = null,
        $p_html_output = true,
        $p_count = 1,
        $p_suffix_type = '',
        $p_category = null,
        $p_purpose = null,
        $p_append_to_title = '##COUNT##',
        $p_start_at = 0,
        $p_zero_point_calc = 0,
        $p_zero_points = 0
    ) {
        global $g_comp_session;

        $l_mod_event_manager = isys_event_manager::getInstance();

        if (isys_tenantsettings::get('cmdb.registry.sanitize_input_data', 1)) {
            $p_title = isys_helper::sanitize_text($p_title);
        }

        /** @var isys_component_session $g_comp_session */
        $g_comp_session->write_close();
        set_time_limit(60 * 60 * 24);

        // Start logging.
        $l_log = isys_factory_log::get_instance('template');
        $l_log->set_verbose_level(isys_log::C__NONE);

        $l_object_id = null;
        $l_template_names = null;

        if ($p_html_output) {
            // Stop output buffering.
            ob_end_clean();
            ob_flush();

            echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"de\" lang=\"de\">";
            isys_application::instance()->template->display("head.tpl");
            echo "<div class=\"p10\">";
        }

        $l_dao = new isys_cmdb_dao($this->m_db);

        // Check if the user chose a template.
        if (empty($p_templates)) {
            throw new isys_exception_template($this->language->get('LC__TEMPLATES__NO_TEMPLATE_CHOSEN'));
        }

        // Check if he chose an object-type.
        if (empty($p_object_type)) {
            throw new isys_exception_template($this->language->get('LC__TEMPLATES__NO_OBJECT_TYPE_CHOSEN'));
        }

        // Export module check.
        if (defined("C__MODULE__EXPORT") && class_exists("isys_module_export")) {
            Context::instance()
                ->setImmutable(true)
                ->setContextTechnical(Context::CONTEXT_EXPORT_XML)
                ->setGroup(Context::CONTEXT_GROUP_TEMPLATE)
                ->setContextCustomer(Context::CONTEXT_TEMPLATE);

            // Export type check.
            $l_export_type = "isys_export_type_xml";
            if (class_exists($l_export_type)) {

                // Import module check.
                if (class_exists("isys_import_handler_cmdb")) {
                    // Init.
                    $l_export = new isys_export_cmdb_object($l_export_type, $this->m_db);
                    $l_import = new isys_import_handler_cmdb($l_log, $this->m_db);

                    $l_import->disconnectSignals();
                    $l_import_dao = new isys_module_dao_import_log($this->m_db);
                    $l_overwrite = false;

                    if ($p_obj_id > 0) {
                        $l_import->set_mode($l_import::C__OVERWRITE);
                        $l_overwrite = true;
                    } else {
                        $l_import->set_mode(isys_import_handler_cmdb::C__APPEND);
                    }

                    // Skip the following categories.
                    foreach (self::$m_cat_skips as $l_skip_type => $l_skip_cat) {
                        $l_export->add_skip($l_skip_cat, $l_skip_type);
                    }

                    if (!is_numeric($p_count) || $p_count < 1) {
                        $p_count = 1;
                    }

                    $l_xml_template = $l_export->export($p_templates, $this->get_export_cats(), C__RECORD_STATUS__TEMPLATE, false, true)
                        ->parse()
                        ->get_export();

                    $l_start = ($p_start_at > 0) ? $p_start_at : 0;

                    isys_event_manager::getInstance()
                        ->set_import_id($l_import_dao->add_import_entry(
                            $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__TEMPLATE'),
                            $p_count . ' object(s) created by the template module'
                        ));

                    // Fixes ID-2087
                    $p_title = htmlspecialchars($p_title);
                    $l_dao = isys_cmdb_dao_category_g_global::instance(isys_application::instance()->container->get('database'));
                    $objectIds = [];
                    for ($i = $l_start;$i < $p_count + $l_start;$i++) {
                        $l_import->set_prepared_data([]);
                        // Title formatting.
                        $l_counter = "";
                        $l_title = $p_title;

                        if ($p_suffix_type != '' && $p_count > 1) {
                            if ($p_zero_point_calc == "1" && $p_zero_points > 0) {
                                for ($n = strlen(strval($i));$n <= $p_zero_points;$n++) {
                                    $l_counter .= "0";
                                }
                            }

                            $l_counter .= $i;

                            if ($p_suffix_type == '-1') {
                                $l_title .= str_replace('##COUNT##', $l_counter, $p_append_to_title);
                            } else {
                                $l_title .= $l_counter;
                            }
                        }

                        // Validate on Object-Title
                        $validationData = [
                            'title' => $l_title,
                        ];

                        // ID-6192: Don't care about validation when using template while coming from object lists and new action
                        $validation = (C__CMDB__VIEW__LIST_OBJECT != isys_glob_get_param(C__CMDB__GET__VIEWMODE) ? $l_dao->validate($validationData): null);

                        if (
                            is_array($validation) &&
                            isset($validation['title'])
                        ) {
                            $this->println($validation['title']);
                            continue;
                        }

                        if (!$l_overwrite) {
                            $p_obj_id = $l_dao->get_last_id_from_table('isys_obj');
                            $p_obj_id++;
                        }

                        // If object type is null or does not exist, use object type SERVER instead.
                        if (!is_numeric($p_object_type) || $p_object_type < 0 || $l_dao->get_objtype($p_object_type)
                                ->num_rows() <= 0) {
                            $p_object_type = defined_or_default('C__OBJTYPE__SERVER');
                        }

                        $this->println($this->language->get("LC__TEMPLATES__APPLYING") . "&hellip;", $p_html_output);
                        $l_modified_template = $this->modify_xml_header(
                            $l_xml_template,
                            $l_title,
                            $p_object_type,
                            $p_obj_id,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            C__RECORD_STATUS__NORMAL
                        );
                        $l_import->load_xml_data($l_modified_template);

                        if (!$l_import->parse()) {
                            return false;
                        }

                        $l_import->prepare();

                        Context::instance()
                            ->setImmutable(false)
                            ->setContextTechnical(Context::CONTEXT_IMPORT_XML)
                            ->setGroup(Context::CONTEXT_GROUP_TEMPLATE)
                            ->setContextCustomer(Context::CONTEXT_TEMPLATE)
                            ->setImmutable(true);

                        if (!$l_import->import()) {
                            return false;
                        }

                        $l_tmp = $l_import->get_object_ids();

                        while ($l_tmpID = array_shift($l_tmp)) {
                            $l_object_id = $l_tmpID;
                        }


                        $l_data = $l_dao->get_object_by_id($p_templates[count($p_templates) - 1])
                            ->__to_array();
                        $l_data_new_object = $l_dao->get_object_by_id($l_object_id)
                            ->__to_array();

                        if (!isys_cmdb_dao_category_g_accounting::has_placeholders($l_title)) {
                            $l_dao->save_title($l_object_id, $l_title);
                        }

                        // Saving some properties to our new object.
                        $l_dao->save_description($l_object_id, $l_data["isys_obj__description"]);
                        $l_dao->set_object_cmdb_status($l_object_id, $l_data["isys_obj__isys_cmdb_status__id"]);

                        // Category:
                        if (is_numeric($p_category) && $p_category > 0) {
                            $l_log->debug('Overwrite category...');
                            if ($l_dao->save_category($l_object_id, $p_category) === false) {
                                $l_log->warning('Cannot overwrite category.');
                            }
                        }

                        // Purpose:
                        if (is_numeric($p_purpose) && $p_purpose > 0) {
                            $l_log->debug('Overwrite purpose...');
                            if ($l_dao->save_purpose($l_object_id, $p_purpose) === false) {
                                $l_log->warning('Cannot overwrite pupose.');
                            }
                        }

                        unset($l_data);

                        $l_title = $l_data_new_object['isys_obj__title'];

                        $this->println($this->language->get("LC__CATG__ODEP_OBJ") . " " . $i . " (" . $l_title . ") ..", $p_html_output);
                        $this->println($this->language->get("LC_POPUP_WIZARD_FILE__CASE2A", ["var1" => $l_title]), $p_html_output);

                        if ($p_html_output) {
                            $l_object_link = $this->prepareLink([
                                'isys_obj_type__id' => $p_object_type,
                                'isys_obj__id'      => $l_object_id
                            ]);

                            $this->println('<a class="" target="_blank" href="' . $l_object_link . '">' . $this->language->get($l_dao->get_objtype_name_by_id_as_string($p_object_type)) . ': ' . $l_title . '</a>');

                            $this->println('', $p_html_output);
                        }

                        $objectIds[] = $l_object_id;

                        if (is_object($l_mod_event_manager)) {
                            $l_template_names = [];

                            foreach ($p_templates as $l_templ_obj_id) {
                                $l_template_names[] = '"' . $l_dao->get_obj_name_by_id_as_string($l_templ_obj_id) . '"';
                            }

                            $l_template_names = isys_helper_textformat::this_this_and_that(array_filter(array_unique($l_template_names)));

                            $l_mod_event_manager->triggerCMDBEvent(
                                'C__LOGBOOK_ENTRY__TEMPLATE_APPLIED',
                                null,
                                $l_object_id,
                                $p_object_type,
                                $l_template_names,
                                null,
                                "Template successfully applied."
                            );
                        }
                    }

                    $l_import->fireDisconnectedSignals();

                    if ($p_html_output) {
                        $objectIdsAsString = '[' . implode(',', $objectIds) . ']';
                        echo "<script type=\"text/javascript\">parent.tpl_loader_hide();parent.$('edit-objects-multiedit').removeClassName('disabled');parent.$('edit-objects-multiedit-object-ids').setValue('{$objectIdsAsString}')</script></div></body></html>";
                    }

                    isys_component_signalcollection::get_instance()
                        ->emit('mod.cmdb.templatesApplied', $p_templates, $p_object_type, $p_title, $l_object_id, $l_import);

                    return $l_object_id;
                } else {
                    throw new isys_exception_template("Import environment not installed. Template creation aborted.");
                }
            } else {
                throw new isys_exception_template("Export type: XML is not installed. Template creation aborted.");
            }
        } else {
            throw new isys_exception_template("Required export module is not installed. Template creation aborted.");
        }
    }

    /**
     * Applies mass change to one or more objects based on a template.
     *
     * @param array  $p_objects               Object identifiers (int)
     * @param int    $p_template              Template object identifier
     * @param string $p_empty_fields          Mode for empty fields
     * @param string $p_multivalue_categories Mode for multi-valued categories
     * @param bool   $p_html_output           Prints HTML if enabled.
     *
     * @return void
     * @throws isys_exception_cmdb
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @throws Exception
     */
    public function apply_mass_change($p_objects, $p_template, $p_empty_fields, $p_multivalue_categories, $p_html_output = true, $overwriteCmdbStatus = true)
    {
        assert(is_array($p_objects));
        assert(is_int($p_template) && $p_template > 0);
        assert(is_int($p_empty_fields));
        assert(is_int($p_multivalue_categories));
        assert(is_bool($p_html_output));

        $l_object_arr = [];

        $l_string = null;

        // Start logging.
        $l_log = isys_factory_log::get_instance('mass_change');
        $l_log->set_verbose_level(constant('isys_log::' . isys_application::instance()->container->request->get('log-level')));

        Context::instance()
            ->setContextTechnical(Context::CONTEXT_EXPORT_XML)
            ->setGroup(Context::CONTEXT_GROUP_TEMPLATE)
            ->setContextCustomer(Context::CONTEXT_MASS_CHANGE)
            ->setImmutable(true);

        // Initiate export:
        $l_export = new isys_export_cmdb_object('isys_export_type_xml', $this->m_db);

        // Initiate import:
        $l_import = new isys_import_handler_cmdb($l_log, $this->m_db);
        $l_import->disconnectSignals();
        $l_import_dao = new isys_module_dao_import_log($this->m_db);

        isys_event_manager::getInstance()
            ->set_import_id($l_import_dao->add_import_entry(
                $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__MASS_CHANGES'),
                count($p_objects) . ' object(s) modified by mass channges.'
            ));

        // Set mode:
        $l_import->set_mode(isys_import_handler_cmdb::C__MERGE);
        $l_import->set_empty_fields_mode($p_empty_fields);
        $l_import->set_multivalue_categories_mode($p_multivalue_categories);
        $l_import->set_logbook_event('C__LOGBOOK_ENTRY__MASS_CHANGE_APPLIED');

        $l_xml_template = $l_export->setOverwriteCmdbStatusOnMassChange($overwriteCmdbStatus)
            ->export($p_template, $this->get_export_cats(), C__RECORD_STATUS__TEMPLATE, false, true)
            ->parse()
            ->get_export();

        if ($p_html_output) {
            // Stop output buffering:
            ob_end_clean();
            ob_flush();

            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">';
            isys_application::instance()->template->display('head.tpl');
            echo '<div class="p10">';
        }

        $l_dao = isys_cmdb_dao_category_g_accounting::instance(isys_application::instance()->container->get('database'));

        $l_template_object = $l_dao->get_object_by_id($p_template)
            ->__to_array();

        $titleWithPlaceholders = null;
        if (isys_cmdb_dao_category_g_accounting::has_placeholders($l_template_object['isys_obj__title'])) {
            $titleWithPlaceholders = $l_template_object['isys_obj__title'];
        }

        // @todo Save some properties that are not handled by modifying the XML header:
        //$l_dao->set_object_cmdb_status($l_object_id, $l_template_object["isys_obj__isys_cmdb_status__id"]);

        //unset($l_template_object);

        // Iterate through each target object:
        foreach ($p_objects as $l_object_id) {
            // Fetch object information:
            $l_object = $l_dao->get_object_by_id($l_object_id)
                ->__to_array();
            $oldTitle = $l_object['isys_obj__title'];

            if ($titleWithPlaceholders !== null) {
                $l_object['isys_obj__title'] = $l_dao->replace_placeholders(
                    $titleWithPlaceholders,
                    $l_object_id,
                    $l_object['isys_obj__isys_obj_type__id'],
                    null,
                    $l_object['isys_obj__sysid'],
                    'isys_obj'
                );
            }

            $l_object_arr[$l_object_id] = $l_object;

            // Keep some object information untouched:
            $l_string .= $this->language->get("LC__TEMPLATES__APPLYING") . "&hellip;<br>";

            $l_modified_template = $this->modify_xml_header(
                $l_xml_template,
                htmlspecialchars($l_object['isys_obj__title']),  // Problems with special characters
                $l_object['isys_obj_type__id'],
                $l_object['isys_obj__id'],
                $l_object['isys_obj__sysid'],
                $l_object['isys_obj__created'],
                $l_object['isys_obj__created_by'],
                $l_object['isys_obj__updated'],
                $l_object['isys_obj__updated_by'],
                $l_template_object['isys_obj__description'],
                $l_object['isys_obj__isys_cmdb_status__id']
            );

            $l_import->load_xml_data($l_modified_template);

            if (!$l_import->parse()) {
                throw new isys_exception_general('Parsing failed.');
            }

            $l_import->prepare();

            $l_string .= $this->language->get("LC__MASS_CHANGE__APPLIED", ["var1" => $oldTitle]) . "<br>";

            $l_object_link = $this->prepareLink($l_object);

            $l_string .= '<a class="" target="_blank" href="' . $l_object_link . '">' . $this->language->get($l_object['isys_obj_type__title']) . ': ' . $l_object['isys_obj__title'] . '</a><br>';
        }

        Context::instance()
            ->setImmutable(false)
            ->setContextTechnical(Context::CONTEXT_IMPORT_XML)
            ->setGroup(Context::CONTEXT_GROUP_TEMPLATE)
            ->setContextCustomer(Context::CONTEXT_MASS_CHANGE)
            ->setImmutable(true);

        // Wrapping output in pre tags for better formating
        echo '<pre>';

        if (!$l_import->import()) {
            throw new isys_exception_general('Importing failed.');
        } else {
            $this->println($l_string, true);
        }

        echo '</pre>';

        $l_import->fireDisconnectedSignals();

        if ($p_html_output) {
            echo '<script type="text/javascript">parent.loader_hide();</script>';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        }

        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.massChangeApplied', $l_object_arr, $p_template, $l_import);
    }

    /**
     * Get table as HTML
     *
     * @return bool|string
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @throws Exception
     */
    public function get_template_list()
    {
        // Retrieve results dependent from record status
        $dao = new isys_templates_dao(isys_application::instance()->container->get('database'));
        $templatesResource = ($this->isMassChange()) ? $dao->get_mass_change_templates() : $dao->get_templates();

        // Check for results
        if ($templatesResource->num_rows() > 0) {
            /**
             * Create and configure the table
             */
            // @see  ID-4982  Changed the code a bit to make the templates sortable :)
            $tableComponent = new isys_component_list(null, $templatesResource);

            // Table header configuration
            $columnConfiguration = [
                "title"               => "Template",
                "created"             => $this->language->get("LC__TASK__DETAIL__WORKORDER__CREATION_DATE"),
                "affected_categories" => $this->language->get("LC__CMDB__AFFECTED_CATEGORIES"),
                "export"              => "Download"
            ];

            // Create row pattern for row
            $rowLinkPattern = isys_helper_link::create_url([
                C__GET__MODULE_ID        => defined_or_default('C__MODULE__CMDB'),
                C__CMDB__GET__OBJECTTYPE => '[{isys_obj_type__id}]',
                C__CMDB__GET__VIEWMODE   => 1100,
                C__CMDB__GET__TREEMODE   => C__CMDB__VIEW__TREE_OBJECT,
                C__CMDB__GET__OBJECT     => '[{isys_id}]'
            ]);

            // Setup configuration
            $tableComponent->config($columnConfiguration, $rowLinkPattern, '[{isys_id}]', true, true, [
                    "10",
                    "20",
                    "950",
                    "20"
                ]);
            $tableComponent->set_row_modifier($this, "row_templates");

            // Generate table html and return to caller
            if ($tableComponent->createTempTable()) {
                return $tableComponent->getTempTableHtml();
            }
        } else {
            // Message indicating that no templates are available
            return "<p class=\"p10\">" . $this->language->get('LC__MODULE__TEMPLATES__NO_TEMPLATES') . "</p>";
        }
    }

    /**
     * Get row template
     *
     * @param $p_ar_data
     *
     * @throws Exception
     */
    public function row_templates(&$p_ar_data)
    {
        $p_ar_data["affected_categories"] = "<div style='white-space:normal;'>" .
            implode(", ", $this->get_affected_categories($p_ar_data["isys_obj__id"], $p_ar_data["isys_obj__isys_obj_type__id"])) . "</div>";
        $p_ar_data["title"] = "<strong>" . $p_ar_data["isys_obj__title"] . "</strong> <br />" . $this->language->get($p_ar_data["isys_obj_type__title"]);
        $p_ar_data["created"] = $p_ar_data["isys_obj__created"] . " (" . $p_ar_data["isys_obj__created_by"] . ")";
        $p_ar_data["export"] = "<a href=\"?" . C__GET__MODULE_ID . "=" . $this->get_module_id() . "&export=" . $p_ar_data["isys_obj__id"] .
            "\"><img src=\"images/icons/silk/page_white_code.png\" /></a>";
    }

    /**
     * @param      $p_object_id
     * @param null $p_object_type
     * @param bool $p_as_array
     *
     * @return array
     * @throws Exception
     */
    public function get_affected_categories($p_object_id, $p_object_type = null, $p_as_array = false)
    {
        /* Categories */
        $l_template_cats = $l_return_as_array = [];
        $l_cat = new isys_cmdb_dao_category_g_overview($this->m_db);

        if (is_null($p_object_type)) {
            $p_object_type = $l_cat->get_objTypeID($p_object_id);
        }

        $categoryTypes = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => $l_cat->get_categories_as_array($p_object_type, $p_object_id, C__CMDB__CATEGORY__TYPE_GLOBAL, C__RECORD_STATUS__NORMAL, false),
            C__CMDB__CATEGORY__TYPE_SPECIFIC => $l_cat->get_categories_as_array($p_object_type, $p_object_id, C__CMDB__CATEGORY__TYPE_SPECIFIC, C__RECORD_STATUS__NORMAL, false)
        ];

        foreach ($categoryTypes as $categoryType => $categories) {
            if (!is_array($categories) || count($categories) === 0) {
                continue;
            }

            foreach ($categories as $categoryId => $categoryData) {
                if (!in_array($categoryData['const'], self::$m_cat_skips[$categoryType], true)) {
                    if (!is_object($categoryData['dao'])) {
                        continue;
                    }

                    // @see  ID-5555  Skip categories with type "view" (and not "edit").
                    if ($categoryData['type'] & isys_cmdb_dao_category::TYPE_VIEW && !($categoryData['type'] & isys_cmdb_dao_category::TYPE_EDIT)) {
                        continue;
                    }

                    // @see  ID-5555  Skip "virtual" categories.
                    if ($categoryData['dao'] instanceof isys_cmdb_dao_category_g_virtual || $categoryData['dao'] instanceof isys_cmdb_dao_category_s_virtual) {
                        continue;
                    }

                    if (!method_exists($categoryData['dao'], 'get_count')) {
                        continue;
                    }

                    $l_count = $categoryData['dao']->get_count($p_object_id);

                    if ($l_count > 0) {
                        $l_return_as_array[$categoryType][] = $categoryId;
                        $l_template_cats[] = $categoryData['title'] . ' (' . $l_count . ')';
                    }
                }
            }
        }

        return ($p_as_array) ? $l_return_as_array : $l_template_cats;
    }

    /**
     * Gets type
     *
     * @return int
     */
    public function get_m_rec_status()
    {
        return $this->m_rec_status;
    }

    /**
     * Sets type.
     * Possible options:
     *    - C__RECORD_STATUS__TEMPLATE
     *    - C__RECORD_STATUS__MASS_CHANGES_TEMPLATE
     *
     * @param int $p_type
     */
    public function set_m_rec_status($p_type)
    {
        $this->m_rec_status = $p_type;
    }

    /**
     * Method for displaying a string.
     *
     * @param  string  $p_message
     * @param  boolean $p_html_output
     */
    private function println($p_message, $p_html_output = true)
    {
        if ($p_html_output) {
            echo $p_message . '<br />';
        }
    }

    /**
     * Method for retrieving specific export categories.
     *
     * @return  array
     * @throws isys_exception_general
     */
    private function get_export_cats()
    {
        $l_export = new isys_export_cmdb_object();
        $l_tmp = $l_export->fetch_exportable_categories();
        $l_transformed = [];

        foreach ($l_tmp as $l_category_type => $l_categories) {
            foreach ($l_categories as $l_categoryID => $l_crap) {
                $l_transformed[$l_category_type][] = $l_categoryID;
            }
        }

        return $l_transformed;
    }

    /**
     * Processes user requests.
     *
     * @param   array $p_get
     * @param   array $p_post
     *
     * @throws isys_exception_auth
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     * @throws Exception
     */
    private function process($p_get, $p_post)
    {
        if (!defined('C__MODULE__TEMPLATES') || !defined('C__MODULE__CMDB')) {
            return;
        }
        global $index_includes;

        $auth = isys_auth_templates::instance();
        $template = isys_application::instance()->container->get('template');

        $l_navbar = isys_component_template_navbar::getInstance();

        if (isset($p_get["export"]) && $p_get["export"] > 0) {
            header("Content-Type: text/xml; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"template-" . $p_get["export"] . "-" . date("Y-m-d") . ".xml\"");

            $l_affected_categories = $this->get_affected_categories($p_get["export"], isys_cmdb_dao::instance($this->m_db)
                ->get_objTypeID($p_get["export"]), true);

            $l_export = new isys_export_cmdb_object();
            echo $l_export->export($p_get["export"], $l_affected_categories, C__RECORD_STATUS__NORMAL)
                ->parse()
                ->get_export();
            die;
        }

        if (isset($p_post["create_template"]) && $p_post["create_template"]) {
            try {
                // Create object by templates.
                $this->create_from_template(
                    $p_post["templates"],
                    $p_post["object_type"],
                    $p_post["object_title"],
                    null,
                    true,
                    $p_post["C__TEMPLATE__SUFFIX_COUNT"],
                    $p_post["C__TEMPLATE__SUFFIX_SUFFIX_TYPE"],
                    $p_post["category"],
                    $p_post["purpose"],
                    $p_post["C__TEMPLATE__SUFFIX_SUFFIX_TYPE_OWN"],
                    $p_post["C__TEMPLATE__SUFFIX_COUNT_STARTING_AT"],
                    $p_post["C__TEMPLATE__SUFFIX_ZERO_POINT_CALC"],
                    $p_post["C__TEMPLATE__SUFFIX_ZERO_POINTS"]
                );

                die();
            } catch (Exception $e) {
                isys_glob_display_error($e->getMessage());
                die;
            }
        }

        // Mass change:
        if ($p_post["apply_mass_change"]) {
            // Parse arguments and call method for mass changes:
            try {
                // Selected objects:
                if (!isset($p_post['selected_objects__HIDDEN'])) {
                    throw new isys_exception_general('No objects selected.');
                }

                $l_objects = json_decode($p_post['selected_objects__HIDDEN']);
                $l_object_list = [];
                if (is_array($l_objects)) {
                    foreach ($l_objects as $l_object) {
                        if (!is_numeric($l_object) || $l_object <= 0) {
                            throw new isys_exception_general('Object list is invalid.');
                        }
                        $l_object_list[] = intval($l_object);
                    }
                    unset($l_objects);
                }

                // Selected template:
                if (!isset($p_post['templates'][0]) || !is_numeric($p_post['templates'][0])) {
                    throw new isys_exception_general('No template selected.');
                }
                $l_template = intval($p_post['templates'][0]);

                $extraActions = $this->getExtraActions();
                if (isset($extraActions[$l_template], $extraActions[$l_template]['handler']) && is_callable($extraActions[$l_template]['handler'])) {
                    return $extraActions[$l_template]['handler']($l_object_list);
                }
                if ($l_template < 0) {
                    throw new isys_exception_general('No template selected.');
                }
                // Handle empty fields:
                if (!isset($p_post['empty_fields']) || !is_numeric($p_post['empty_fields']) ||
                    ($p_post['empty_fields'] != isys_import_handler_cmdb::C__KEEP && $p_post['empty_fields'] != isys_import_handler_cmdb::C__CLEAR)) {
                    throw new isys_exception_general('Handling empty fields is unclear.');
                }
                $l_empty_fields = intval($p_post['empty_fields']);

                // Handle multi-valued categories:
                if (!isset($p_post['multivalue_categories']) || !is_numeric($p_post['multivalue_categories']) ||
                    ($p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UNTOUCHED && $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__APPEND &&
                        $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__OVERWRITE &&
                        $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UPDATE &&
                        $p_post['multivalue_categories'] != isys_import_handler_cmdb::C__UPDATE_ADD)) {
                    throw new isys_exception_general('Handling multi-valued categories is unclear.');
                }
                $l_multivalue_categories = intval($p_post['multivalue_categories']);

                $this->apply_mass_change($l_object_list, $l_template, $l_empty_fields, $l_multivalue_categories, true, (bool)$p_post['overwrite-cmdb-status']);
            } catch (Exception $e) {
                echo($e->getMessage());
            } //try/catch

            die;
        } // if mass change

        $template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        if (!isset($p_get[C__GET__SETTINGS_PAGE])) {
            if ($p_get[C__GET__MODULE] == C__MODULE__CMDB) {
                if ($auth->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__MASS_CHANGE;
                } elseif ($auth->is_allowed_to(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__EXISTING;
                } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW;
                }
            } else {
                if ($auth->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW_OBJET;
                } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__NEW;
                } elseif ($auth->is_allowed_to(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__EXISTING)) {
                    $p_get[C__GET__SETTINGS_PAGE] = TPL_PID__EXISTING;
                }
            }
        }

        switch ($p_get[C__GET__SETTINGS_PAGE]) {
            case TPL_PID__NEW:
                if ($p_get[C__GET__MODULE] == C__MODULE__CMDB) {
                    $auth->check(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__NEW);
                    $rules = [
                        'object_type' => [
                            'p_strSelectedID' => defined_or_default('C__OBJTYPE__GENERIC_TEMPLATE')
                        ]
                    ];

                    $template
                        ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
                        ->include_template('contentbottomcontent', 'modules/templates/new_mass_changes_template.tpl');
                } else {
                    $auth->check(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW);
                    $rules = [
                        'object_type' => [
                            'chosen'          => true,
                            'p_bDbFieldNN'    => 0,
                            'status'          => 0,
                            'exclude'         => 'C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC;C__OBJTYPE__RELATION',
                            'p_strSelectedID' => $_GET[C__CMDB__GET__OBJECTTYPE],
                            'p_strTable'      => 'isys_obj_type',
                            'p_strClass'      => 'input-small',
                            'sort'            => true
                        ]
                    ];

                    $template
                        ->activate_editmode()
                        ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
                        ->include_template('contentbottomcontent', 'modules/templates/new_template.tpl');
                }
                break;

            case TPL_PID__NEW_OBJET:
                $auth->check(isys_auth::EXECUTE, 'TEMPLATES/' . C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET);
                $l_dao = new isys_templates_dao($this->m_db);
                $l_templates = $l_dao->get_templates();

                $l_template_array = null;

                while ($l_row = $l_templates->get_row()) {
                    $l_template_array[$this->language->get($l_row["isys_obj_type__title"])][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
                }

                ksort($l_template_array);

                $rules = [
                    'object_title' => [
                        'p_bInfoIconSpacer' => 0,
                        'disableInputGroup' => true,
                        'p_bEditMode' => true,
                        'p_strValue' => ''
                    ],
                    'object_type'  => [
                        'chosen'          => true,
                        'p_bDbFieldNN'    => 0,
                        'status'          => 0,
                        'exclude'         => 'C__OBJTYPE__CONTAINER;C__OBJTYPE__LOCATION_GENERIC;C__OBJTYPE__GENERIC_TEMPLATE;C__OBJTYPE__RELATION',
                        'p_strSelectedID' => $_GET[C__CMDB__GET__OBJECTTYPE],
                        'p_strTable'      => 'isys_obj_type',
                        'p_strClass'      => 'input-small',
                        'sort'            => true
                    ],
                    'purpose'      => [
                        'p_strTable' => 'isys_purpose',
                        'p_strClass' => 'input-small'
                    ],
                    'category'     => [
                        'p_strTable' => 'isys_catg_global_category',
                        'p_strClass' => 'input-small'
                    ],
                    'templates_id' => [
                        'p_strClass'        => 'input-small',
                        'p_arData'          => $l_template_array,
                        'p_strSelectedID'   => $_GET['template_id'],
                        'p_bSort'           => false,
                        'p_bDbFieldNN'      => true,
                        'p_multiple'        => true,
                        'chosen'            => true,
                        'p_bInfoIconSpacer' => 0
                    ]
                ];

                // @see  ID-5580  Adding placeholders to the template GUI
                $sql = 'SELECT isys_obj__id AS id, isys_obj__isys_obj_type__id AS typeId, isys_obj__title as title, isys_obj__sysid AS sysid 
                    FROM isys_obj 
                    WHERE isys_obj__isys_obj_type__id ' . $l_dao->prepare_in_condition(filter_defined_constants(['C__OBJTYPE__CABLE', 'C__OBJTYPE__RELATION']), true) . ' 
                    ORDER BY RAND() 
                    LIMIT 1;';

                $objectData = $l_dao->retrieve($sql)->get_row();

                $placeholderData = isys_cmdb_dao_category_g_accounting::get_placeholders_info_with_data(true, $objectData['id'], $objectData['typeId'], $objectData['title'], $objectData['sysid']);

                unset($placeholderData['%OBJTITLE%'], $placeholderData['%COUNTER%'], $placeholderData['%COUNTER#N%'], $placeholderData['%COUNTER:N#N%']);

                $template->activate_editmode()
                    ->assign('templates', $l_template_array)
                    ->assign('placeholderData', $placeholderData)
                    ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
                    ->include_template('contentbottomcontent', 'modules/templates/create_object.tpl');
                break;

            case TPL_PID__SETTINGS:
                if (class_exists("isys_module_system")) {
                    $l_settings = new isys_module_system();
                    $l_settings->handle_templates();
                } else {
                    throw new isys_exception_general("Module 'isys_module_system' does not exist.");
                }
                break;

            case TPL_PID__MASS_CHANGE:
                $auth->check(isys_auth::EXECUTE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE);
                // Retrieve available object templates for mass changes:
                $l_dao = new isys_templates_dao($this->m_db);
                $l_templates = $l_dao->get_mass_change_templates();
                $l_template_array = [];

                while ($l_row = $l_templates->get_row()) {
                    $l_template_array[$this->language->get($l_row["isys_obj_type__title"])][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
                }

                ksort($l_template_array);

                if (count($l_template_array) === 0) {
                    $l_rules['selected_objects']['p_bDisabled'] = true;
                    $l_rules['templates']['p_bDisabled'] = true;
                    $l_rules['apply_mass_change']['p_bDisabled'] = true;
                    isys_application::instance()->template->assign('field_disabled', 'disabled="disabled"');
                } else {
                    $l_rules['templates']['p_arData'] = $this->getExtraMenuItems() + $l_template_array;
                }

                // Options:
                $template->activate_editmode()
                    ->assign('keep', isys_import_handler_cmdb::C__KEEP)
                    ->assign('clear', isys_import_handler_cmdb::C__CLEAR)
                    ->assign('untouched', isys_import_handler_cmdb::C__UNTOUCHED)
                    ->assign('add', isys_import_handler_cmdb::C__APPEND)
                    ->assign('delete_add', isys_import_handler_cmdb::C__OVERWRITE)
                    ->assign('update', isys_import_handler_cmdb::C__UPDATE)
                    ->assign('update_add', isys_import_handler_cmdb::C__UPDATE_ADD)
                    ->assign('hasTemplates', !empty($l_template_array))
                    ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
                    ->include_template('contentbottomcontent', 'modules/templates/mass_change.tpl');
                break;

            default:
                /**
                 * Ajax-Procedure: Purging templates
                 */
                $rankingErrors = [];

                // Validate request - Are we responsible?
                if (
                    is_array($p_post[C__GET__ID]) && !empty($p_post[C__GET__ID]) &&     // Check whether entries are selected
                    $p_post[C__GET__NAVMODE] == C__NAVMODE__PURGE &&                    // Check whether navmode is `purge`
                    !empty($p_get[C__GET__AJAX])                                        // Check whether request is AJAX
                ) {
                    // Initialize DAO
                    $cmdbDao = new isys_cmdb_dao(isys_application::instance()->container->get('database'));

                    /**
                     * Attention:   This structure is for error collecting
                     *              while ranking and can look silly at first sight.
                     *
                     *              !!! Do not change a function like rank_records without caution !!!
                     */

                    // Iterate over selected templates in table
                    foreach ($p_post[C__GET__ID] as $objectId) {
                        try {
                            // Delete template
                            $cmdbDao->rank_records([$objectId], C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true);
                        } catch (\Exception $e) {
                            // Collect ranking error for notification purpose
                            $rankingErrors[] = $e->getMessage();
                        }
                    }

                    // Notify user about success of purge operation
                    if (empty($rankingErrors)) {
                        isys_notify::success($this->language->get('LC__UNIVERSAL__OPERATION_SUCCESSFUL'));
                    }
                }

                /**
                 * Check rights based on mode sssss
                 */
                if ($this->isMassChange()) {
                    $auth->check(isys_auth::VIEW, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . '1');
                    $l_check_delete_rights = $auth->is_allowed_to(isys_auth::DELETE, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . '1');
                    $l_check_edit_rights = $auth->is_allowed_to(isys_auth::EDIT, 'MASS_CHANGES/' . C__MODULE__TEMPLATES . '1');
                    $template->assign('rec_status', C__RECORD_STATUS__MASS_CHANGES_TEMPLATE);
                } else {
                    $auth->check(isys_auth::VIEW, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1');
                    $l_check_delete_rights = $auth->is_allowed_to(isys_auth::DELETE, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1');
                    $l_check_edit_rights = $auth->is_allowed_to(isys_auth::EDIT, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1');
                    $template->assign('rec_status', C__RECORD_STATUS__TEMPLATE);
                }

                // Configure navigation bar
                $l_navbar->set_active($l_check_delete_rights, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__PURGE)
                    ->set_active($l_check_edit_rights, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(false, C__NAVBAR_BUTTON__UP)
                    ->set_visible(false, C__NAVBAR_BUTTON__FORWARD)
                    ->set_visible(false, C__NAVBAR_BUTTON__BACK);

                if ($l_check_edit_rights) {
                    $url = isys_helper_link::create_url([
                        C__GET__MODULE_ID      => C__MODULE__CMDB,
                        C__CMDB__GET__VIEWMODE => 1100,
                        C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
                        C__CMDB__GET__OBJECT   => '%id%'
                    ]);
                    $editOnClick = implode(' && ', array_map(function ($str) {
                        return "({$str})";
                    }, [
                        "checked = $$('[name=\'id[]\']:checked').invoke('getValue').invoke('toInt')[0]",
                        "$('contentBottomContent').addClassName('is-loading')",
                        "document.isys_form.navMode.value = '" . C__NAVBAR_BUTTON__EDIT . "'",
                        "$('isys_form').action = '$url'.replace('%id%', checked)",
                        "$('isys_form').submit()"
                    ]));
                    $l_navbar->set_js_onclick($editOnClick, C__NAVBAR_BUTTON__EDIT);
                }
                try {
                    // Setup template related variables
                    isys_application::instance()->template
                        ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                        ->assign("content_title", 'Templates')
                        ->assign("rankingErrors", $rankingErrors)
                        ->assign("tableList", $this->get_template_list());
                } catch (Exception $exception) {
                    // @todo What should happen now?
                }

                // Set template section
                $index_includes['contentbottomcontent'] = "modules/templates/main.tpl";

                // Prevent showing object related header in this context
                unset($index_includes['contenttop']);

                break;
        }
    }

    /**
     * Determine current mode
     *
     * Decision made by `mod` in $_GET
     * Possible modes:
     *
     * EMPTY -> Mode is `template`
     * 2     -> Mode is `masschange`
     *
     * @return bool
     */
    protected function isMassChange()
    {
        return $_GET[C__GET__MODULE] == defined_or_default('C__MODULE__CMDB');
    }

    /**
     * Get extra menu items
     *
     * @return array
     * @throws Exception
     */
    protected function getExtraMenuItems()
    {
        $items = [];

        foreach ($this->getExtraActions() as $k => $v) {
            $items[$k] = $v['name'];
        }

        return [
            $this->language->get('LC__MASS_CHANGE__ACTIONS') => $items,
            $this->language->get('LC__MASS_CHANGE__DIVIDER') => [],
        ];
    }

    /**
     * Get extra actions for mass change
     *
     * @return array
     * @throws Exception
     */
    protected function getExtraActions()
    {
        $extraActions = [
            -2 => [
                'name'    => $this->language->get('LC__MASS_CHANGE__ACTIONS__ARCHIVE'),
                'handler' => function ($o) {
                    return $this->processObjectsAction($o, 'LC__MASS_CHANGE__ACTIONS__ARCHIVE', function ($i) {
                        return $this->changeRank($i, C__RECORD_STATUS__ARCHIVED);
                    });
                },
            ],
            -3 => [
                'name'    => $this->language->get('LC__MASS_CHANGE__ACTIONS__DELETE'),
                'handler' => function ($o) {
                    return $this->processObjectsAction($o, 'LC__MASS_CHANGE__ACTIONS__DELETE', function ($i) {
                        return $this->changeRank($i, C__RECORD_STATUS__DELETED);
                    });
                },
            ]
        ];

        if (defined('C__MODULE__TEMPLATES') && isys_auth_templates::instance()->is_allowed_to(isys_auth::DELETE, 'TEMPLATES/' . C__MODULE__TEMPLATES . '1')) {
            $extraActions[-4] = [
                'name'    => $this->language->get('LC__MASS_CHANGE__ACTIONS__PURGE'),
                'handler' => function ($o) {
                    return $this->processObjectsAction($o, 'LC__MASS_CHANGE__ACTIONS__PURGE', function ($i) {
                        return $this->purgeObjects($i);
                    });
                },
            ];
        }

        return $extraActions;
    }

    /**
     * Changes the rank of object to $rank
     *
     * @param $object
     *
     * @param $rank
     *
     * @return bool
     */
    protected function changeRank($object, $rank)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_db);
        $statusDiff = $rank - $object['isys_obj__status'];
        $id = $object['isys_obj__id'];
        while ($statusDiff !== 0) {
            $direction = $statusDiff > 0 ? C__CMDB__RANK__DIRECTION_DELETE : C__CMDB__RANK__DIRECTION_RECYCLE;
            $success = $l_dao->rank_records([$id], $direction, 'isys_obj');
            if (!$success) {
                return false;
            }
            $statusDiff -= $statusDiff > 0 ? 1 : -1;
        }

        return true;
    }

    /**
     * Purge objects
     *
     * @param $object
     *
     * @return bool
     */
    protected function purgeObjects($object)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_db);

        return $l_dao->rank_records([$object['isys_obj__id']], C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true);
    }

    /**
     * Processes selected $action for every object in objects, renders the output and close the request
     *
     * @param array    $objects
     * @param          $type
     * @param callable $action
     *
     * @throws Exception
     */
    protected function processObjectsAction(array $objects, $type, callable $action)
    {
        ob_end_clean();
        ob_flush();
        echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">';
        isys_application::instance()->template->display('head.tpl');
        echo '<div class="p10">';

        $output = [
            $this->language->get($type . '__START')
        ];

        $l_dao = isys_cmdb_dao::instance($this->m_db);

        foreach ($objects as $objectId) {
            $object = $l_dao->get_object_by_id($objectId)->__to_array();
            $result = $action($object);
            $message = $type . '__' . ($result ? 'OK' : 'FAIL');
            $objectTitle = $object['isys_obj__title'];

            if (!$result) {
                $objectTitle = '<a href="' . $this->prepareLink($object, $objectTitle) . '" target="_blank">' . $objectTitle . '</a>';
            }

            $output[] = $this->language->get($message, ["var1" => $objectTitle]);
        }

        $output[] = $this->language->get($type . '__DONE');

        echo implode('<br/>', $output);
        echo '<script type="text/javascript">parent.loader_hide();</script>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        die;
    }

    /**
     * Prepare link to object view page
     *
     * @param $object
     *
     * @return string
     */
    protected function prepareLink($object)
    {
        return isys_helper_link::create_url([
            C__GET__MODULE_ID        => defined_or_default('C__MODULE__CMDB'),
            C__CMDB__GET__OBJECTTYPE => $object['isys_obj_type__id'],
            C__CMDB__GET__VIEWMODE   => 1100,
            C__CMDB__GET__TREEMODE   => 1006,
            C__CMDB__GET__OBJECT     => $object['isys_obj__id']
        ]);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_db = $g_comp_database;

        parent::__construct();
    }

    /**
     * @deprecated Keep for backward compatibility until i-doit 1.17
     */
    public static function get_auth()
    {
        return self::getAuth();
    }

    /**
     * @return isys_auth_templates
     */
    public static function getAuth()
    {
        return isys_auth_templates::instance();
    }
}
