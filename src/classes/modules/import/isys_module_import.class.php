<?php

use idoit\AddOn\AuthableInterface;
use idoit\Component\Logger;
use idoit\Module\Cmdb\Model\Matcher\Ci\CiIdentifiers;

/**
 * i-doit
 *
 * Import module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_import extends isys_module implements AuthableInterface, isys_module_authable
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var string
     */
    public static $m_path_to_category_map = "temp/cache_category_map.cache";

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @var
     */
    private $m_userrequest;

    /**
     * Method for retrieving the imported objects.
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_imports()
    {
        $l_sql = "SELECT * FROM isys_obj 
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj__scantime != '0000-00-00 00:00:00'
            AND isys_obj__hostname != '';";

        return isys_component_dao::instance(isys_application::instance()->container->get('database'))->retrieve($l_sql);
    }

    /**
     * @param string $p_hostname
     * @param string $p_obj_title
     *
     * @return array
     * @throws isys_exception_database
     * @throws Exception
     */
    public function check_status($p_hostname, $p_obj_title = null)
    {
        $condition = '';
        $dao = isys_component_dao::instance(isys_application::instance()->container->get('database'));

        if ($p_hostname !== null) {
            $condition .= ' AND isys_obj__hostname = ' . $dao->convert_sql_text($p_hostname);
        }

        if ($p_obj_title !== null) {
            $condition .= ' AND isys_obj__title = ' . $dao->convert_sql_text($p_obj_title);
        }

        return $dao->retrieve("SELECT * FROM isys_obj WHERE TRUE {$condition} LIMIT 1;")->get_row();
    }

    /**
     * @param isys_module_request & $p_req
     *
     * @desc Initializes the module
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;
    }

    /**
     * Return array of zipped scripts in "imports/scripts/" directory.
     *
     * @return array
     */
    public function get_scripts()
    {
        $l_scripts = [];

        $l_dirh = opendir(C__IMPORT__DIRECTORY . '/scripts/');
        while ($l_file = readdir($l_dirh)) {
            if (strstr($l_file, ".zip")) {
                $l_scripts[] = $l_file;
            }
        }

        return $l_scripts;
    }

    /**
     * Delete given import.
     *
     * @param string $p_filename
     *
     * @return bool
     */
    public function delete_import($p_filename)
    {
        if (file_exists(C__IMPORT__DIRECTORY . '/' . $p_filename)) {
            return unlink(C__IMPORT__DIRECTORY . '/' . $p_filename);
        }

        return false;
    }

    /**
     * Handler for ocs db list
     */
    public function handle_ocsdb()
    {
        $l_dbID = isys_glob_get_param("dbID");

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__NEW:
                $this->ocs_db(null);
                $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                break;

            case C__NAVMODE__SAVE:
                $this->ocs_db($l_dbID);
                break;

            case C__NAVMODE__PURGE:
                $this->delete_ocsdb($_POST["id"]);
                $this->ocs_list();
                break;

            default:
                if ($l_dbID != null) {
                    $this->ocs_db($l_dbID);
                } elseif ($_POST["id"] != null) {
                    $this->ocs_db($_POST["id"][0]);
                } else {
                    $this->ocs_list();
                }
                break;
        }
    }

    /**
     * Deletes an ocs db source from db
     *
     * @param $p_ids
     */
    public function delete_ocsdb($p_ids)
    {
        global $g_comp_database;

        $l_dao = new isys_component_dao_ocs($g_comp_database);

        if (is_array($p_ids)) {
            foreach ($p_ids as $l_id) {
                $l_dao->delete_ocsdb($l_id);
            }
        }
    }

    /**
     * Method which sets data for the ocs db page.
     *
     * @param   integer $p_id
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocs_db($p_id)
    {
        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OCS/OCSDB');
        $l_navbar = isys_component_template_navbar::getInstance();
        $l_dao = isys_component_dao_ocs::instance(isys_application::instance()->container->get('database'));

        switch (isys_glob_get_param(C__GET__NAVMODE)) {
            case C__NAVMODE__SAVE:
                $p_id = $l_dao->saveOCSDB($p_id);
                if ($p_id) {
                    isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                } else {
                    isys_notify::error($this->language->get('LC__INFOBOX__DATA_WAS_NOT_SAVED'), ['sticky' => true]);
                }
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                    ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL);
                break;
        }

        if (isys_glob_get_param(C__GET__MAIN_MENU__NAVIGATION_ID) == C__NAVMODE__EDIT && isys_glob_get_param(C__GET__NAVMODE) === false) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        if ($p_id) {
            $l_settings = $l_dao->getOCSDB($p_id);
        }

        if (empty($l_settings["isys_ocs_db__port"])) {
            $l_settings["isys_ocs_db__port"] = "3306";
        }

        $l_rules = [
            'C__MODULE__IMPORT__OCS_HOST'   => [
                'p_strValue' => $l_settings["isys_ocs_db__host"],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__IMPORT__OCS_PORT'   => [
                'p_strValue' => $l_settings["isys_ocs_db__port"],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__IMPORT__OCS_SCHEMA' => [
                'p_strValue' => $l_settings["isys_ocs_db__schema"],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__IMPORT__OCS_USER'   => [
                'p_strValue' => $l_settings["isys_ocs_db__user"],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__IMPORT__OCS_PASS'   => [
                'p_strValue' => isys_helper_crypt::decrypt($l_settings["isys_ocs_db__pass"]),
                'p_strClass' => 'input-small'
            ]
        ];

        isys_application::instance()->container->get('template')
            ->assign("dbID", $l_settings["isys_ocs_db__id"])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/import/ocs_db.tpl');
    }

    /**
     * Method for ocs db list
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocs_list()
    {
        $template = isys_application::instance()->container->get('template');

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_edit_right = isys_auth_system::instance()->is_allowed_to(isys_auth::EDIT, 'OCS/OCSDB');
        $l_delete_right = isys_auth_system::instance()->is_allowed_to(isys_auth::DELETE, 'OCS/OCSDB');
        $l_dao = new isys_component_dao_ocs(isys_application::instance()->container->get('database'));

        $l_dbs = $l_dao->getOCSDBs();

        $l_list = new isys_component_list();

        $l_list_headers = [
            'isys_ocs_db__id'     => 'ID',
            'isys_ocs_db__host'   => 'Host',
            'isys_ocs_db__schema' => 'Schema'
        ];

        $l_list_data = [];

        $l_count = $l_dbs->num_rows();

        if ($l_count > 0) {
            while ($l_row = $l_dbs->get_row()) {
                $l_list_data[] = $l_row;
            }

            $l_list->set_data($l_list_data);
            $l_list->config(
                $l_list_headers,
                '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__SYSTEM') .
                    '&' . C__GET__MODULE_SUB_ID . '=' . defined_or_default('C__MODULE__IMPORT') .
                    '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] .
                    '&what=ocsdb&dbID=[{isys_ocs_db__id}]',
                '[{isys_ocs_db__id}]'
            );

            if ($l_list->createTempTable()) {
                $template->assign("objectTableList", $l_list->getTempTableHtml());
            }
        } else {
            $template->assign("objectTableList", '<div class="p10">' . $this->language->get('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>');
        }

        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active((($l_count > 0) ? $l_delete_right : false), C__NAVBAR_BUTTON__PURGE)
            ->set_active((($l_count > 0) ? $l_edit_right : false), C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        $template
            ->assign('content_title', 'OCS ' . $this->language->get('LC__UNIVERSAL__DATABASE'))
            ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1');
    }

    /**
     * Handle CSV imports.
     *
     * @deprecated
     */
    public function csv_import_handler()
    {
    }

    /**
     * Fetch log files.
     *
     * @param  string $p_prefix File name prefix (optional)
     * @param  string $p_suffix File extension (optional). Default: '.log'
     *
     * @return array
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    public function get_log_files($p_prefix = '', $p_suffix = '.log')
    {
        $l_handle = opendir(C__IMPORT__LOG_DIRECTORY);
        $l_result = [];
        while ($l_file = readdir($l_handle)) {
            if (is_file(C__IMPORT__LOG_DIRECTORY . $l_file)) {
                if (preg_match("/^" . $p_prefix . "(.+)" . $p_suffix . "$/", $l_file) === 1) {
                    $l_result[$l_file]['name'] = $l_file;
                    $l_result[$l_file]['date'] = date('Y-m-d H:i:s', filectime(C__IMPORT__LOG_DIRECTORY . $l_file));
                }
            }
        }
        closedir($l_handle);

        return $l_result;
    }

    /**
     * Method for ocs obects list
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function ocsObjectsPage()
    {
        $l_dao = new isys_component_dao_ocs(isys_application::instance()->container->get('database'));
        $l_settings_res = $l_dao->getOCSDBs();
        $l_ocs_db_list = [];

        $l_default_ocs_db = isys_tenantsettings::get('ocs.default.db', null);

        if ($l_settings_res->num_rows() > 0) {
            if ($l_dao->getOCSDB($l_default_ocs_db) == null) {
                $l_default_ocs_db = null;
                isys_tenantsettings::set('ocs.default.db', '');
            }

            while ($l_settings = $l_settings_res->get_row()) {
                if ($l_default_ocs_db == null) {
                    $l_default_ocs_db = $l_settings['isys_ocs_db__id'];
                }

                $l_ocs_db_list[$l_settings['isys_ocs_db__id']] = $l_settings["isys_ocs_db__host"] . ' - ' . $l_settings["isys_ocs_db__schema"];
            }
        }

        $l_sql = "SELECT isys_obj_type__id, isys_obj_type__title, isys_obj_type_group__title 
            FROM isys_obj_type
            INNER JOIN isys_obj_type_group ON isys_obj_type_group__id = isys_obj_type__isys_obj_type_group__id
            WHERE isys_obj_type__show_in_tree = 1 
            AND isys_obj_type_group__const != 'C__OBJTYPE_GROUP__CONTACT' 
            AND isys_obj_type__status = " . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $l_res = $l_dao->retrieve($l_sql);
        $l_obj_type_groups = [];
        while ($l_row = $l_res->get_row()) {
            $l_obj_type_groups[$this->language->get($l_row['isys_obj_type_group__title'])][$l_row['isys_obj_type__id']] = $this->language->get($l_row['isys_obj_type__title']);
        }

        foreach ($l_obj_type_groups as $l_key => $l_obj_types) {
            asort($l_obj_type_groups[$l_key]);
        }

        $l_rules['templaet_objtype_arr']['p_arData'] = $l_obj_type_groups;
        $l_rules['all_objtypes']['p_arData'] = $l_obj_type_groups;
        $l_rules['selected_ocsdb']['p_arData'] = $l_ocs_db_list;
        $l_rules['ocs_overwrite_hostaddress_port']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['selected_ocsdb']['p_strSelectedID'] = $l_default_ocs_db;

        isys_application::instance()->container->get('template')
            ->assign('content_title', 'OCS Inventory')
            ->assign('js_script', 'show_ocs_objects(' . $l_default_ocs_db . ')')
            ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', 'modules/import/ocs_objects.tpl');
    }

    /**
     * Handles ocs configuration
     */
    public function handle_ocsconfig()
    {
        global $index_includes, $g_comp_database;

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'OCS/OCSCONFIG');

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_comp_dao_ocs = new isys_component_dao_ocs($g_comp_database);
        $l_res_ocs_dbs = $l_comp_dao_ocs->getOCSDBs();

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
            try {
                isys_tenantsettings::set('ocs.default.objtype', $_POST["C__OCS__OBJTYPE"]);
                isys_tenantsettings::set('ocs.prefix.server', $_POST["C__OCS__SERVER_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.client', $_POST["C__OCS__CLIENT_PREFIX"]);
                isys_tenantsettings::set('ocs.application', $_POST["C__OCS__APPLICATION"]);
                isys_tenantsettings::set('ocs.prefix.router', $_POST["C__OCS__ROUTER_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.switch', $_POST["C__OCS__SWITCH_PREFIX"]);
                isys_tenantsettings::set('ocs.prefix.printer', $_POST["C__OCS__PRINTER_PREFIX"]);
                isys_tenantsettings::set('ocs.application.assignment', $_POST["C__OCS__APPLICATION_ASSIGNMENT"]);
                isys_tenantsettings::set('ocs.logbook.active', $_POST["C__OCS__LOGBOOK"]);
                isys_tenantsettings::set('ocs.default.db', $_POST["C__OCS__DEFAULT_DB"]);
                isys_tenantsettings::set('ocs.object-matching', $_POST["C__OCS__OBJECT_MATCHING_PROFILE"]);
                isys_tenantsettings::force_save();

                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            } catch (Exception $e) {
                isys_notify::error($e->getMessage(), ['sticky' => true]);
            }
        }

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        } else {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        $l_dao = new isys_cmdb_dao($g_comp_database);
        $l_res = $l_dao->get_objtype();

        $l_objTypes = [];
        while ($l_row = $l_res->get_row()) {
            $l_objTypes[$l_row["isys_obj_type__id"]] = $this->language->get($l_row["isys_obj_type__title"]);
        }

        $l_ocs_dbs = [];
        $l_first_ocs_db = '';
        while ($l_row = $l_res_ocs_dbs->get_row()) {
            if ($l_first_ocs_db == '') {
                $l_first_ocs_db = $l_row['isys_ocs_db__id'];
            }

            $l_ocs_dbs[$l_row['isys_ocs_db__id']] = $l_row['isys_ocs_db__host'] . ' - ' . $l_row['isys_ocs_db__schema'];
        }

        $l_rules = [
            'C__OCS__OBJTYPE'                 => [
                'p_arData'        => $l_objTypes,
                'p_strSelectedID' => isys_tenantsettings::get('ocs.default.objtype'),
                'p_strClass'      => 'input-small'
            ],
            'C__OCS__DEFAULT_DB'              => [
                'p_arData'        => $l_ocs_dbs,
                'p_strSelectedID' => isys_tenantsettings::get('ocs.default.db', $l_first_ocs_db),
                'p_strClass'      => 'input-small'
            ],
            'C__OCS__SERVER_PREFIX'           => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.server'),
            ],
            'C__OCS__CLIENT_PREFIX'           => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.client'),
            ],
            'C__OCS__ROUTER_PREFIX'           => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.router'),
            ],
            'C__OCS__SWITCH_PREFIX'           => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.switch'),
            ],
            'C__OCS__PRINTER_PREFIX'          => [
                'p_strValue' => isys_tenantsettings::get('ocs.prefix.printer'),
            ],
            'C__OCS__APPLICATION'             => [
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.application'),
                'p_strClass'      => 'input-small'
            ],
            'C__OCS__APPLICATION_ASSIGNMENT'  => [
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.application.assignment'),
                'p_strClass'      => 'input-small'
            ],
            'C__OCS__LOGBOOK'                 => [
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => isys_tenantsettings::get('ocs.logbook.active'),
                'p_strClass'      => 'input-small'
            ],
            'C__OCS__OBJECT_MATCHING_PROFILE' => [
                'p_strSelectedID' => isys_tenantsettings::get('ocs.object-matching', 1),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => 1,
                'p_strTable'      => 'isys_obj_match'
            ]
        ];

        isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
        $index_includes['contentbottomcontent'] = "modules/import/ocs_config.tpl";
    }

    /**
     * Process AJAX requests.
     */
    public function processAjaxRequest()
    {
        $template = isys_application::instance()->container->get('template');

        switch ($_GET['request']) {
            case 'showOCSObject':
                global $g_comp_database;

                $l_dao = new isys_component_dao_ocs($g_comp_database);

                if (isset($_GET['selected_ocsdb'])) {
                    $l_db = $_GET['selected_ocsdb'];
                } else {
                    $l_db = null;
                }

                $l_settings = $l_dao->getOCSDB($l_db);

                $l_ocsdb = isys_component_database::get_database(
                    'mysqli',
                    $l_settings['isys_ocs_db__host'],
                    $l_settings['isys_ocs_db__port'],
                    $l_settings['isys_ocs_db__user'],
                    isys_helper_crypt::decrypt($l_settings['isys_ocs_db__pass']),
                    $l_settings['isys_ocs_db__schema']
                );

                $l_dao = new isys_component_dao_ocs($l_ocsdb);

                if ($_GET['snmp'] === 'undefined') {
                    $_GET['snmp'] = false;
                }
                $l_snmp = (bool)$_GET['snmp'];
                if ($l_snmp && $l_dao->does_snmp_exist()) {
                    // Scanned by SNMP?
                    $l_hw = $l_dao->getHardwareItemBySNMP($_GET['hwID']);

                    $template
                        ->assign('C__OCS__ID', $l_hw['ID'])
                        ->assign('C__OCS__NAME', $l_hw['NAME'])
                        ->assign('C__OCS__OS_NAME', $l_hw['OSNAME'])
                        ->assign('C__OCS__IP', $l_hw['IPADDR']);

                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    $l_row = $l_dao->retrieve('SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = ' .
                        $l_dao->convert_sql_text($l_hw['NAME']) . ';')
                        ->get_row();

                    if ($l_row['isys_obj__isys_obj_type__id'] != null) {
                        $template->assign('objTypeID', $l_row['isys_obj__isys_obj_type__id']);
                    } else {
                        $template->assign('objTypeID', isys_tenantsettings::get('ocs.default.objtype'));
                    }

                    $template->assign('imported', $l_row['isys_obj__imported']);

                    $l_objTypes = $l_dao->retrieve('SELECT isys_obj_type__id, isys_obj_type__title FROM isys_obj_type');
                    while ($l_row = $l_objTypes->get_row()) {
                        $l_object_types[$l_row['isys_obj_type__id']] = $this->language->get($l_row['isys_obj_type__title']);
                    }
                    asort($l_object_types);
                    $template
                        ->assign('object_types', $l_object_types)
                        ->assign('yes_no_selection', get_smarty_arr_YES_NO())
                        ->display('modules/import/ocs_snmp_object.tpl');
                } else {
                    $l_hw = $l_dao->getHardwareItem($_GET['hwID']);

                    $template
                        ->assign('C__OCS__ID', $l_hw['ID'])
                        ->assign('C__OCS__NAME', $l_hw['NAME'])
                        ->assign('C__OCS__OS_NAME', $l_hw['OSNAME'])
                        ->assign('C__OCS__PROCESSOR', $l_hw['PROCESSORT'])
                        ->assign('C__OCS__CPU_SPEED', $l_hw['PROCESSORS'])
                        ->assign('C__OCS__MEMORY', $l_hw['MEMORY'])
                        ->assign('C__OCS__IP', $l_hw['IPADDR']);

                    $l_dao = new isys_cmdb_dao($g_comp_database);

                    $l_row = $l_dao
                        ->retrieve('SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = ' . $l_dao->convert_sql_text($l_hw['NAME']) . ';')
                        ->get_row();

                    if ($l_row['isys_obj__isys_obj_type__id'] != null) {
                        $template->assign('objTypeID', $l_row['isys_obj__isys_obj_type__id']);
                    } else {
                        $template->assign('objTypeID', isys_tenantsettings::get('ocs.default.objtype'));
                    }

                    $template->assign('imported', $l_row['isys_obj__imported']);

                    $l_objTypes = $l_dao->retrieve('SELECT isys_obj_type__id, isys_obj_type__title FROM isys_obj_type');
                    while ($l_row = $l_objTypes->get_row()) {
                        $l_object_types[$l_row['isys_obj_type__id']] = $this->language->get($l_row['isys_obj_type__title']);
                    }

                    $template
                        ->assign('yes_no_selection', get_smarty_arr_YES_NO())
                        ->assign('object_types', $l_object_types)
                        ->display('modules/import/ocs_object.tpl');
                }
                break;
            case 'call_csv_handler':
                isys_module_import_csv::handle_ajax_request($_GET[C__CMDB__GET__CSV_AJAX]);
                break;

            default:
                if ($_GET[C__GET__PARAM] == C__IMPORT__GET__FINISHED_IMPORTS) {
                    $this->imports();
                }
                break;
        }
    }

    /**
     * @return mixed
     */
    public function get_module_id()
    {
        return $_GET[C__GET__MODULE_ID];
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        global $g_dirs;

        $l_parent = -1;
        $l_submodule = '';
        $l_root = null;
        $auth = self::getAuth();

        if (!defined('C__MODULE__IMPORT')) {
            return;
        }

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__IMPORT . '0', $l_parent, 'Import ' . $this->language->get('LC__UNIVERSAL__MODULE'));
        }

        if (!$p_system_module) {
            $p_tree->add_node(
                C__MODULE__IMPORT . C__IMPORT__GET__IMPORT,
                $l_root,
                $this->language->get('LC__MODULE__IMPORT__XML'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID                => C__MODULE__IMPORT,
                    C__GET__PARAM                    => C__IMPORT__GET__IMPORT,
                    C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__IMPORT,
                    C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                ]),
                '',
                $g_dirs['images'] . 'icons/silk/page_white_code.png',
                $_GET[C__GET__PARAM] == C__IMPORT__GET__IMPORT,
                '',
                '',
                $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)
            );

            if (class_exists('isys_module_import_csv')) {
                $p_tree->add_node(
                    C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                    $l_root,
                    $this->language->get('LC__MODULE__IMPORT__CSV'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__CSV,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]),
                    '',
                    $g_dirs['images'] . 'icons/silk/page_white_excel.png',
                    $_GET[C__GET__PARAM] == C__IMPORT__GET__CSV,
                    '',
                    '',
                    $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)
                );
            }

            $p_tree->add_node(
                C__MODULE__IMPORT . '2',
                $l_root,
                $this->language->get('LC__MODULE__IMPORT__OCS'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID                => C__MODULE__IMPORT,
                    C__GET__PARAM                    => C__IMPORT__GET__OCS_OBJECTS,
                    C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS,
                    C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                ]),
                '',
                $g_dirs['images'] . 'tree/ocs.png',
                $_GET['param'] == C__IMPORT__GET__OCS_OBJECTS,
                '',
                '',
                $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS)
            );

            if (defined('C__MODULE__JDISC') && class_exists('isys_module_jdisc')) {
                $p_tree->add_node(
                    C__MODULE__IMPORT . C__IMPORT__GET__JDISC,
                    $l_root,
                    $this->language->get('LC__MODULE__JDISC'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__JDISC,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__JDISC,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]),
                    '',
                    $g_dirs['images'] . 'icons/jdisc.png',
                    $_GET['param'] == C__IMPORT__GET__JDISC,
                    '',
                    '',
                    $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__JDISC)
                );
            }

            if (defined('C__MODULE__LDAP') && defined('C__MODULE__PRO')) {
                $p_tree->add_node(
                    C__IMPORT__GET__LDAP,
                    $l_root,
                    $this->language->get('LC__MODULE__IMPORT__LDAP'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__LDAP,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__LDAP,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]),
                    '',
                    $g_dirs['images'] . 'icons/silk/server_database.png',
                    $_GET['param'] == C__IMPORT__GET__LDAP,
                    '',
                    '',
                    $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP)
                );
            }

            if (defined('C__MODULE__SHAREPOINT') && defined('C__MODULE__PRO')) {
                $p_tree->add_node(
                    C__IMPORT__GET__SHAREPOINT,
                    $l_root,
                    $this->language->get('LC__MODULE__IMPORT__SHAREPOINT'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID                => C__MODULE__SHAREPOINT,
                        C__GET__PARAM                    => C__IMPORT__GET__SHAREPOINT,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]),
                    '',
                    $g_dirs['images'] . 'tree/sharepoint.png',
                    $_GET['param'] == C__IMPORT__GET__SHAREPOINT,
                    '',
                    '',
                    $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT)
                );
            }

            if (defined('C__MODULE__PRO')) {
                $p_tree->add_node(
                    C__IMPORT__GET__CABLING,
                    $l_root,
                    $this->language->get('LC__MODULE__IMPORT__CABLING'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID                => C__MODULE__IMPORT,
                        C__GET__PARAM                    => C__IMPORT__GET__CABLING,
                        C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CABLING,
                        C__GET__MAIN_MENU__NAVIGATION_ID => $_GET[C__GET__MAIN_MENU__NAVIGATION_ID],
                    ]),
                    '',
                    $g_dirs['images'] . 'icons/silk/chart_line.png',
                    $_GET['param'] == C__IMPORT__GET__CABLING,
                    '',
                    '',
                    $auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING)
                );
            }
        } else {
            $p_tree->add_node(
                C__MODULE__IMPORT . 7,
                $l_root,
                $this->language->get('LC__MODULE__IMPORT__OCS_DBS'),
                '?moduleID=' . $this->get_module_id() . '&what=ocsdb' . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '7',
                null,
                $g_dirs['images'] . '/tree/ocs.png',
                $_GET['what'] === 'ocsdb'
            );

            $p_tree->add_node(
                C__MODULE__IMPORT . 8,
                $l_root,
                $this->language->get('LC__MODULE__IMPORT__OCS_CONFIG'),
                '?moduleID=' . $this->get_module_id() . '&what=ocsconfig' . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '8',
                null,
                $g_dirs['images'] . 'tree/ocs.png',
                $_GET['what'] === 'ocsconfig'
            );

            if (defined('C__MODULE__JDISC') && class_exists("isys_module_jdisc")) {
                $l_module_jdisc = new isys_module_jdisc();
                $l_module_jdisc->build_tree($p_tree, true, $l_root);
            }

            if (defined('C__MODULE__SHAREPOINT')) {
                $p_tree->add_node(
                    C__MODULE__IMPORT . 11,
                    $l_root,
                    $this->language->get('LC__MODULE__SHAREPOINT__CONFIGURATION'),
                    '?moduleID=' . $this->get_module_id() . '&what=sharepoint_configuration&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '11',
                    null,
                    $g_dirs['images'] . 'tree/sharepoint.png',
                    $_GET['what'] === 'sharepoint_configuration'
                );
            }

            if (defined('C__MODULE__LOGINVENTORY')) {
                $p_tree->add_node(
                    C__MODULE__IMPORT . 12,
                    $l_root,
                    'LOGINventory-Konfiguration',
                    '?moduleID=' . $this->get_module_id() . '&what=loginventory_configuration&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__TREE_NODE . '=' . C__MODULE__IMPORT . '12',
                    null,
                    $g_dirs['images'] . 'icons/loginventory.png',
                    $_GET['what'] === 'loginventory_configuration'
                );
            }
        }
    }

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param array  $p_text
     * @param string $p_link
     *
     * @return bool
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

        $p_text[] = $this->language->get('LC__MODULE__IMPORT') . ' ' . $this->language->get('LC__UNIVERSAL__MODULE');

        if (isset($l_params[C__GET__PARAM])) {
            switch ($l_params[C__GET__PARAM]) {
                case C__IMPORT__GET__OCS_OBJECTS:
                    $p_text[] = $this->language->get('LC__MODULE__IMPORT__OCS');
                    break;
                case C__IMPORT__GET__JDISC:
                    $p_text[] = $this->language->get('LC__MODULE__JDISC');
                    break;
                case C__IMPORT__GET__LDAP:
                    $p_text[] = $this->language->get('LC__MODULE__IMPORT__LDAP');
                    break;
                case C__IMPORT__GET__CABLING:
                    $p_text[] = $this->language->get('LC__CMDB__CATG__CABLING');
                    break;
                case C__IMPORT__GET__SHAREPOINT:
                    $p_text[] = $this->language->get('LC__MODULE__SHAREPOINT');
                    break;
                case C__IMPORT__GET__IMPORT:
                default:
                    $p_text[] = $this->language->get('LC__UNIVERSAL__FILE_IMPORT');
                    break;
            }
        }

        $p_link = $l_url_parameters;

        return true;
    }

    /**
     * Starts module process
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        if (isset($_FILES['import_file'])) {
            $uploadedFilename = $_FILES['import_file']['name'];
            $extensionStartPosition = strrpos($uploadedFilename, '.');
            $uploadedFilename = substr($uploadedFilename, 0, $extensionStartPosition) . strtolower(substr($uploadedFilename, $extensionStartPosition));
            $_FILES['import_file']['name'] = $uploadedFilename;
        }

        // Path to import files.
        define('C__IMPORT__DIRECTORY', rtrim(isys_tenantsettings::get('system.dir.import-uploads', rtrim(BASE_DIR, '/') . '/imports/'), '/') . '/' . isys_application::instance()->tenant->id . '/');
        if (!file_exists(C__IMPORT__DIRECTORY)) {
            mkdir(C__IMPORT__DIRECTORY, 0775, true);
        }

        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call") && !isys_glob_get_param('mydoitAction')) {
            $this->processAjaxRequest();
            die;
        }

        if (!defined('C__MODULE__IMPORT')) {
            return;
        }

        $l_gets = $this->m_userrequest->get_gets();
        $auth = self::getAuth();

        global $index_includes, $g_absdir;

        if (!isset($_GET[C__GET__PARAM]) && !isset($_GET['what'])) {
            if ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__IMPORT;
            } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__OCS_OBJECTS;
            } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__JDISC)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__JDISC;
            } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__LDAP;
            } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__CABLING;
            } elseif ($auth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT)) {
                $_GET[C__GET__PARAM] = C__IMPORT__GET__SHAREPOINT;
            }
        }

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $this->build_system_menu();
        }

        try {
            if ($_POST['delete_import']) {
                header('Content-Type: application/json');

                if ($this->delete_import($_POST['delete_import'])) {
                    $l_success = true;
                    $l_message = $this->language->get('LC__MODULE__IMPORT__FILE_DELETION_SUCCEEDED', [$_POST['delete_import']]);
                } else {
                    $l_success = false;
                    $l_message = $this->language->get('LC__MODULE__IMPORT__FILE_DELETION_FAILED', [$_POST['delete_import']]);
                }

                $l_arr = [
                    'success' => $l_success,
                    'message' => $l_message
                ];

                echo isys_format_json::encode($l_arr);
                die;
            }

            if (isset($l_gets[C__GET__FILE_MANAGER])) {
                $this->handle_file_manager();
            }

            switch ($_GET[C__GET__PARAM]) {
                case C__IMPORT__GET__IMPORT:
                    $auth->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__IMPORT);
                    isys_application::instance()->template->assign("encType", "multipart/form-data");
                    $this->import_new();
                    $this->imports();
                    isys_application::instance()->template->assign("inventory_import", class_exists('isys_import_handler_inventory'))
                        ->assign("import_files", $this->get_files())
                        ->assign("tenant_id", isys_application::instance()->tenant->id)
                        ->assign("object_match_id", isys_tenantsettings::get('import.hinventory.object-matching', 1));
                    break;

                case C__IMPORT__GET__SCRIPTS:
                    isys_application::instance()->template->assign("import_path", str_replace($g_absdir . "/", "", C__IMPORT__DIRECTORY))
                        ->assign("scripts", $this->get_scripts());
                    break;

                case C__IMPORT__GET__OCS_OBJECTS:
                    $auth->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS);
                    $this->ocsObjectsPage();
                    break;

                case C__IMPORT__GET__CSV:
                    $this->process_csv_import_index();
                    break;

                case C__IMPORT__GET__FINISHED_IMPORTS:
                    $this->imports();
                    break;

                case C__IMPORT__GET__LDAP:
                    $auth->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__LDAP);

                    return $this->ldap_import_page();
                    break;
                case C__IMPORT__GET__CABLING:
                    $auth->check(isys_auth::EXECUTE, 'IMPORT/' . C__MODULE__IMPORT . C__IMPORT__GET__CABLING);
                    $this->cabling_import_page();
                    break;

                case C__IMPORT__GET__DOWNLOAD:

                    if (isset($_GET['file'])) {
                        switch ($_GET['file']) {
                            case 'hi':
                                global $g_absdir;
                                $l_filemanager = new isys_component_filemanager();
                                $l_filemanager->send($g_absdir . '/imports/scripts/inventory.zip');
                                break;
                        }
                    }

                    break;
                default:
                    ;
                    break;
            }

            if ($_GET[C__GET__PARAM] == C__IMPORT__GET__OCS_OBJECTS || $_GET[C__GET__PARAM] == C__IMPORT__GET__CSV) {
                return null;
            }

            if (isset($l_gets['what'])) {
                if ($l_gets['what'] === 'ocsconfig') {
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'OCS/OCSCONFIG');
                    $this->handle_ocsconfig();
                } elseif ($l_gets['what'] === 'ocsdb') {
                    isys_auth_system::instance()
                        ->check(isys_auth::VIEW, 'OCS/OCSDB');
                    $this->handle_ocsdb();
                } elseif (defined('C__MODULE__JDISC') && class_exists("isys_module_jdisc")) {
                    $l_jdisc = new isys_module_jdisc();
                    $l_jdisc->init($this->m_userrequest);
                    $l_jdisc->start();
                } elseif (defined('C__MODULE__SHAREPOINT') && $l_gets['what'] === 'sharepoint_configuration') {
                    $l_jdisc = new isys_module_sharepoint();
                    $l_jdisc->init($this->m_userrequest);
                    $l_jdisc->start();
                } elseif (defined('C__MODULE__LOGINVENTORY') && ($l_gets['what'] === 'loginventory_databases' || $l_gets['what'] === 'loginventory_configuration')) {
                    $l_loginvent = new isys_module_loginventory();
                    $l_loginvent->init($this->m_userrequest);
                    $l_loginvent->start();
                }
                // if
            } else {
                if (class_exists("isys_module_jdisc")) {
                    if ($_GET['param'] == C__IMPORT__GET__JDISC) {
                        $l_jdisc = new isys_module_jdisc();
                        $l_jdisc->init($this->m_userrequest);

                        return $l_jdisc->start();
                    }
                }

                $index_includes['contentbottomcontent'] = "modules/import/import_main.tpl";
                $index_includes['contenttop'] = false;
            }
        } catch (isys_exception_general $e) {
            //$this->build_system_menu();
            throw $e;
        } catch (isys_exception_auth $e) {
            isys_application::instance()->container->get('template')
                ->assign("exception", $e->write_log())
                ->include_template('contentbottomcontent', 'exception-auth.tpl');
        }
    }

    /**
     * Active directry computer import
     *
     * @return null
     * @throws Exception
     */
    public function ldap_import_page()
    {
        global $index_includes, $g_dirs, $g_comp_database;

        $l_rules = [];
        $l_objtypes = [];

        if (defined('C__MODULE__LDAP')) {
            $l_ldap = new isys_ldap_dao($g_comp_database);

            $l_res_ldap_serv = $l_ldap->get_data();
            $l_ldap_ad_exists = false;

            while ($l_row = $l_res_ldap_serv->get_row()) {
                if ($l_row['isys_ldap_directory__const'] === 'C__LDAP__AD') {
                    $l_ldap_ad_exists = true;
                    $l_ldap_serv[$l_row['isys_ldap__id']] = $l_row['isys_ldap__hostname'];
                    if ($l_row['isys_ldap__active'] > 0) {
                        $l_ldap_serv_selected = $l_row['isys_ldap__id'];
                    }
                }
            }

            if (!$l_ldap_ad_exists) {
                isys_application::instance()->container->get('template')->assign('error_message', 'No Active directory server defined.');
            } else {
                // rules
                $l_rules['C__LDAP_IMPORT__LDAP_SERVERS']['p_arData'] = $l_ldap_serv;
                $l_rules['C__LDAP_IMPORT__LDAP_SERVERS']['p_strSelectedID'] = $l_ldap_serv_selected;
                $l_rules['C__LDAP_IMPORT__OBJECTTYPE']['p_arData'] = $l_objtypes;

                isys_application::instance()->container->get('template')->activate_editmode()
                    ->assign("information_text", '')
                    ->assign("ldap_is_installed", true)
                    ->assign("content_title", "LDAP Objekt Import")
                    ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
                    ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
            }
        } else {
            isys_application::instance()->container->get('template')->assign('error_message', 'LDAP Module is not installed.');
        }

        $index_includes['contentbottomcontent'] = $g_dirs['class'] . 'modules/pro/templates/modules/import/ldap_import.tpl';

        return null;
    }

    /**
     *
     */
    public function cabling_import_page()
    {
        if (!defined('C__CATG__CABLING')) {
            return;
        }
        global $g_dirs;

        $database = isys_application::instance()->container->get('database');
        $template = isys_application::instance()->container->get('template');

        $l_dao = isys_cmdb_dao::instance($database);
        $l_log = isys_factory_log::get_instance('import_cabling')
            ->set_destruct_flush(isys_tenantsettings::get('logging.cmdb.import', false));

        $l_typefilter = $l_dao->get_object_types_by_category(C__CATG__CABLING, 'g', false, false);
        $l_typefilter_as_string = $l_dao->get_object_types_by_category(C__CATG__CABLING, 'g', true, false);
        $l_key = array_search('C__OBJTYPE__CABLE', $l_typefilter_as_string);
        unset($l_typefilter_as_string[$l_key]);

        $l_dialog_dao = isys_factory_cmdb_dialog_dao::get_instance($database, 'isys_connection_type');
        $l_dialog_data = $l_dialog_dao->get_data(null, 'RJ-45');
        $l_dialog_data_id = $l_dialog_data['isys_connection_type__id'];

        $template->activate_editmode()
            ->assign('content_title', $this->language->get('LC__CMDB__CATG__CABLING'))
            ->assign('encType', 'multipart/form-data')
            ->assign('lang_all_connectors', $this->language->get('LC__MODULE__IMPORT__CABLING__ALL_CONNECTORS'))
            ->assign('img_dir', $g_dirs['images'])
            ->assign('ajax_link', '?ajax=1&call=cabling_import&func=')
            ->assign('typefilter_as_string', implode(';', $l_typefilter_as_string));

        $l_objtype_group_arr = [];
        $l_objtypes = $l_dao->get_obj_type_by_catg([C__CATG__CABLING]);

        while ($l_row = $l_objtypes->get_row()) {
            if (!array_key_exists($l_row['isys_obj_type__isys_obj_type_group__id'], $l_objtype_group_arr)) {
                $l_objtype_group_arr[$l_row['isys_obj_type__isys_obj_type_group__id']] = $l_dao->objgroup_get_by_id($l_row['isys_obj_type__isys_obj_type_group__id'])
                    ->get_row();
            }

            $l_arr_objtypes[$this->language->get($l_objtype_group_arr[$l_row['isys_obj_type__isys_obj_type_group__id']]['isys_obj_type_group__title'])][$l_row['isys_obj_type__id']] = $l_row['isys_obj_type__title'];
        }

        $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_arData'] = $l_arr_objtypes;
        $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_strSelectedID'] = defined_or_default('C__OBJTYPE__PATCH_PANEL');
        $l_rules['C__MODULE__IMPORT__CABLING__CABLE_TYPE']['p_strSelectedID'] = $l_dialog_data_id;
        $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_strSelectedID'] = defined_or_default('C__CATG__CONNECTOR');
        $l_rules['C__MODULE__IMPORT__CABLING__CREATE_OUTPUT_CONNECTOR']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['C__MODULE__IMPORT__CABLING__CREATE_OUTPUT_CONNECTOR']['p_strSelectedID'] = 1;

        $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_arData'] = filter_array_by_keys_of_defined_constants([
            'C__CATG__CONNECTOR'           => $this->language->get('LC__CMDB__CATG__CONNECTORS'),
            'C__CATG__NETWORK_PORT'        => $this->language->get('LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS'),
            'C__CATG__CONTROLLER_FC_PORT'  => $this->language->get('LC__CMDB__CATS__CHASSIS_CABLING__FC_PORTS'),
            'C__CATG__UNIVERSAL_INTERFACE' => $this->language->get('LC__CMDB__CATG__UNIVERSAL_INTERFACE')
        ]);

        $l_default_arr = [
            [
                $this->language->get('LC_UNIVERSAL__OBJECT'),
                $this->language->get('LC__CATG__CONNECTOR__OUTPUT'),
                $this->language->get('LC__CMDB__OBJTYPE__CABLE'),
                $this->language->get('LC__CATG__CONNECTOR__INPUT'),
                $this->language->get('LC_UNIVERSAL__OBJECT'),
                $this->language->get('LC__CATG__CONNECTOR__OUTPUT')
            ]
        ];

        $l_show_default = true;

        if (isset($_FILES['import_file']) || isset($_POST['import_submitter'])) {
            $l_create_patch_panels = false;

            $l_rules['C__MODULE__IMPORT__CABLING__CABLING_TYPE']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__CABLE_TYPE']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__OBJTYPE']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'];
            $l_rules['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'];
            $l_rules['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE']['p_strSelectedID'] = $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'];

            if (!empty($_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN']) && !empty($_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'])) {
                $template->assign('advanced_options', true);
            }

            if (isset($_POST['C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST'])) {
                $l_rules['C__MODULE__IMPORT__CABLING__OBJECT_CHECK_EXIST']['p_bChecked'] = true;
                $l_create_patch_panels = true;
            }

            if ($_POST['import_submitter'] === 'load_csv') {
                // Reads file and generates the output
                if (!empty($_FILES['import_file']['name']) && strrchr($_FILES['import_file']['name'], ".") == '.csv') {
                    if (move_uploaded_file($_FILES['import_file']['tmp_name'], C__IMPORT__DIRECTORY . $_FILES['import_file']['name'])) {
                        chmod(C__IMPORT__DIRECTORY . $_FILES['import_file']['name'], 0664);
                        $l_import = new isys_import_handler_cabling($l_log, C__IMPORT__DIRECTORY . $_FILES['import_file']['name']);

                        $params = [
                            'cabling_type'        => $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'],
                            'connector_type'      => $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'],
                            'create_patch_panels' => $l_create_patch_panels,
                            'cabling_objects'     => $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'],
                            'wiring_system'       => $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'],
                            'cable_type'          => $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'],
                            'typefilter'          => $l_typefilter
                        ];

                        $l_list = $l_import->load_list()
                            ->set_options($params)
                            ->render_list()
                            ->get_output();

                        $template->assign('content', $l_list);
                        $l_show_default = false;
                    }
                }
            } elseif ($_POST['import_submitter'] === 'import') {
                // Imports the data
                $l_import = new isys_import_handler_cabling($l_log, null, $_POST['csv_row']);

                $params = [
                    'cabling_type'          => $_POST['C__MODULE__IMPORT__CABLING__CABLING_TYPE'],
                    'connector_type'        => $_POST['C__MODULE__IMPORT__CABLING__CABLE_TYPE'],
                    'create_patch_panels'   => $l_create_patch_panels,
                    'cabling_objects'       => $_POST['C__MODULE__IMPORT__CABLING__OBJTYPE'],
                    'wiring_system'         => $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_WIRING_SYSTEM__HIDDEN'],
                    'cable_type'            => $_POST['C__MODULE__IMPORT__CABLING__ADVANCED_OP_CABLE_TYPE'],
                    'createOutputConnector' => (bool)$_POST['C__MODULE__IMPORT__CABLING__CREATE_OUTPUT_CONNECTOR'],
                    'typefilter'            => $l_typefilter
                ];

                $l_import->set_options($params);

                $l_imported = $l_import->prepare()
                    ->import();
                $l_import_log = $l_import->get_import_log();

                $l_list = $l_import->render_list()
                    ->get_output();

                if ($l_imported) {
                    isys_notify::success($this->language->get('LC__MODULE__IMPORT__CABLING__SUCCEEDED'));
                } else {
                    isys_notify::error($this->language->get('LC__MODULE__IMPORT__CABLING__FAILED'));
                }

                $l_dlgets = $this->m_userrequest->get_gets();
                $l_dlgets[C__GET__FILE_MANAGER] = "get";
                $l_dlgets[C__GET__FILE_NAME] = 'cabling_import.csv';
                $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__IMPORT');
                $l_download_link = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

                $template->assign('content', $l_list)
                    ->assign('img_dir', $g_dirs['images'])
                    ->assign('import_log', ltrim($l_import_log))
                    ->assign('cabling_import_result', $l_imported)
                    ->assign('import_message_success', $this->language->get('LC__MODULE__IMPORT__CABLING__SUCCEEDED'))
                    ->assign('import_message_fail', $this->language->get('LC__MODULE__IMPORT__CABLING__FAILED'))
                    ->assign('download_link', $l_download_link);
                $l_show_default = false;
            }
        }

        if ($l_show_default) {
            $l_import = new isys_import_handler_cabling($l_log, null, $l_default_arr);

            $params = [
                'cabling_type'        => defined_or_default('C__CATG__CONNECTOR'),
                'connector_type'      => $l_dialog_data_id,
                'create_patch_panels' => false,
                'cabling_objects'     => defined_or_default('C__OBJTYPE__PATCH_PANEL'),
                'wiring_system'       => null,
                'cable_type'          => null,
                'typefilter'          => $l_typefilter
            ];

            $l_list = $l_import->load_list()
                ->set_options($params)
                ->render_list()
                ->get_output();

            $template->assign('content', $l_list);
        }

        $template
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/import/cabling_import.tpl');
    }

    /**
     * This method will process the CSV import specific actions.
     *
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_csv_import_index()
    {
        $l_tpl = isys_application::instance()->template;
        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        if (isset($_FILES['import_file']) && is_array($_FILES['import_file']) && $_FILES['import_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $this->import_new();
        } elseif (isset($l_posts['file']) || isset($l_gets['file'])) {
            $this->process_csv_import_assignment((isset($l_posts['file']) ? $l_posts['file'] : $l_gets['file']), $l_gets['profile']);

            return;
        } elseif (isset($l_posts['csv_filename']) && isset($l_posts['csv_separator'])) {
            header('Content-Type: application/json; charset=utf-8');

            echo isys_format_json::encode($this->process_csv_file($l_posts['csv_filename'], $l_posts['csv_separator'], $l_posts['object_type']));
            die;
        }
        if (!defined('C__MODULE__IMPORT')) {
            return;
        }

        // Display the list of files.
        $l_tpl->assign("encType", "multipart/form-data")
            ->assign("import_files", $this->get_files())
            ->assign("tenant_id", isys_application::instance()->tenant->id)
            ->assign("import_filter", $this->get_import_filter())
            ->assign("form_action_url", isys_helper_link::create_url([
                C__GET__MODULE_ID                => C__MODULE__IMPORT,
                C__GET__PARAM                    => C__IMPORT__GET__CSV,
                C__GET__TREE_NODE                => C__MODULE__IMPORT . C__IMPORT__GET__CSV,
                C__GET__MAIN_MENU__NAVIGATION_ID => 2
            ]))
            ->include_template('contentbottomcontent', 'modules/import/import_csv.tpl');
    }

    /**
     * This method will be used to display the assignment page, after you chose a CSV file to import.
     *
     * @param   string  $p_file
     * @param   integer $p_profile
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function process_csv_import_assignment($p_file, $p_profile = null)
    {
        if (!defined('C__MODULE__IMPORT')) {
            return;
        }

        $auth = self::getAuth();
        $l_tpl = isys_application::instance()->template;

        // A file has been selected. Display the matching options!
        if (file_exists(BASE_DIR . self::$m_path_to_category_map)) {
            // @todo  Check if this is necessary.
            unlink(BASE_DIR . self::$m_path_to_category_map);
        }

        $l_identificators = [];
        $l_identifiers = (new CiIdentifiers())->getIdentifiers();

        foreach ($l_identifiers as $l_key => $l_identifier) {
            $l_identificators[$l_key] = $this->language->get($l_identifier->getTitle());
        }

        $l_rules = [
            'object_type'     => ['p_arData' => isys_module_import_csv::get_objecttypes()],
            'csv_filename'    => ['p_strValue' => $p_file],
            'identificator[]' => ['p_arData' => $l_identificators]
        ];

        $l_matcher = [];

        $l_match_res = isys_cmdb_dao::instance(isys_application::instance()->database)
            ->retrieve('SELECT * FROM isys_obj_match;');

        if (is_countable($l_match_res) && count($l_match_res)) {
            while ($l_match_row = $l_match_res->get_row()) {
                $l_matcher[$l_match_row['isys_obj_match__bits'] . ';' . $l_match_row['isys_obj_match__min_match']] = $l_match_row['isys_obj_match__title'];
            }
        }

        $l_identifiers = (new CiIdentifiers(null))->getIdentifiers();
        $l_matcher_identifier = [];
        if (is_countable($l_identifiers) && count($l_identifiers)) {
            foreach ($l_identifiers as $l_key => $l_identifier) {
                $l_matcher_identifier[$l_identifier::getBit()] = $l_key;
            }
        }

        $l_tpl->activate_editmode()
            ->assign('isAllowedToViewProfiles', $auth->is_allowed_to($auth::VIEW, 'csv_import_profiles'))
            ->assign('isAllowedToEditProfiles', $auth->is_allowed_to($auth::EDIT, 'csv_import_profiles'))
            ->assign('isAllowedToDeleteProfiles', $auth->is_allowed_to($auth::DELETE, 'csv_import_profiles'))
            ->assign('isAllowedToCreateProfiles', $auth->is_allowed_to($auth::CREATE, 'csv_import_profiles'))
            ->assign('csv_filename', $p_file)
            ->assign('selected_profile', $p_profile)
            ->assign('log_icons', isys_format_json::encode(Logger::getLevelIcons()))
            ->assign('log_colors', isys_format_json::encode(Logger::getLevelColors()))
            ->assign('log_levels', isys_format_json::encode(Logger::getLevelNames()))
            ->assign('matcher', $l_matcher)
            ->assign('matcher_identifier', $l_matcher_identifier)
            ->assign('ajax_url_csvprofiles', isys_helper_link::create_url([
                C__GET__MODULE_ID      => C__MODULE__IMPORT,
                C__GET__PARAM          => C__IMPORT__GET__CSV,
                C__GET__AJAX           => 1,
                'request'              => 'call_csv_handler',
                C__CMDB__GET__CSV_AJAX => 'load_profiles'
            ]))
            ->assign('url_ajax_import', isys_helper_link::create_url([
                C__GET__MODULE_ID      => C__MODULE__IMPORT,
                C__GET__PARAM          => C__IMPORT__GET__CSV,
                C__GET__AJAX           => 1,
                'request'              => 'call_csv_handler',
                C__CMDB__GET__CSV_AJAX => 'import'
            ]))
            ->assign('csvmapping_ajax_url', isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'csv_import',
            ]))
            ->assign('multivalue_modes', [
                'untouched' => isys_module_import_csv::CL__MULTIVALUE_MODE__UNTOUCHED,
                'add'       => isys_module_import_csv::CL__MULTIVALUE_MODE__ADD,
                'overwrite' => isys_module_import_csv::CL__MULTIVALUE_MODE__OVERWRITE,
            ])
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/import/csv_import.tpl');
    }

    /**
     * Method for simply processing the CSV file for the frontend to start the matching.
     *
     * @param   string $p_csv_filename
     * @param   string $p_csv_separator
     * @param   mixed  $p_obj_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function process_csv_file($p_csv_filename, $p_csv_separator, $p_obj_type = null)
    {
        try {
            if ($p_obj_type !== null && defined($p_obj_type)) {
                $p_obj_type = constant($p_obj_type);
            }

            if (!($p_obj_type > 0)) {
                $p_obj_type = false;
            }

            return [
                'success' => true,
                'data'    => [
                    'csv_first_line'  => isys_module_import_csv::get_csv(
                        C__IMPORT__DIRECTORY . $p_csv_filename,
                        $p_csv_separator,
                        isys_module_import_csv::CL__GET__HEAD
                    ),
                    'csv_second_line' => isys_module_import_csv::get_csv(
                        C__IMPORT__DIRECTORY . $p_csv_filename,
                        $p_csv_separator,
                        isys_module_import_csv::CL__GET__CONTENT__FIRST_LINE
                    ),
                    'categories'      => isys_module_import_csv::get_importable_categories($_POST['multivalue'], $p_obj_type)
                ],
                'message' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     */
    private function get_import_filter()
    {
        global $g_dirs;
        $l_handler = [];
        $l_dir_import = $g_dirs["import"] . "handler/";
        $l_log = isys_factory_log::get_instance('import')
            ->set_destruct_flush((bool)isys_tenantsettings::get('logging.cmdb.import', false));


        $skipFiles = [
            'isys_import_handler.class.php',
            'isys_import_handler_csv.class.php',
            'isys_import_handler_cmdb.class.php',
            'isys_import_handler_inventory.class.php',
            'isys_import_handler_cabling.class.php'
        ];

        if (is_dir($l_dir_import)) {
            $l_import_fh = opendir($l_dir_import);
            while ($l_file = readdir($l_import_fh)) {
                if ($l_file !== '.' && $l_file !== '..' && $l_file !== '.DS_Store' && !in_array($l_file, $skipFiles, true) && is_file($l_dir_import . '/' . $l_file)) {
                    $l_class = preg_replace("/^(.*?).class.php$/", "\\1", $l_file);
                    $l_file = preg_replace("/^isys_import_handler_(.*?).class.php$/", "\\1", $l_file);

                    if (class_exists($l_class)) {
                        $l_class_obj = new $l_class($l_log);
                        if (method_exists($l_class_obj, 'get_name')) {
                            $l_import_name = $l_class_obj->get_name();
                        } else {
                            $l_import_name = str_replace(".php", "", str_replace("isys_import_handler_", "", $l_file));
                        }

                        $l_handler[$l_file] = $l_import_name;
                    }
                }
            }
        }

        return $l_handler;
    }

    /**
     * Method for a new import.
     *
     * @global  isys_component_database $g_comp_database
     * @global  string                  $g_absdir
     * @global  array                   $g_dirs
     */
    private function import_new()
    {
        global $g_comp_database, $g_absdir;

        $l_fileman = new isys_component_filemanager();
        $l_fileman->set_upload_path(C__IMPORT__DIRECTORY);
        $l_fileman->set_disallowed_filetypes(['exe', 'bin', 'bat', 'cmd', 'php', 'pl', 'cgi', 'py', 'rb', 'phtml']);

        // Object types for <select>.
        $l_dao = new isys_cmdb_dao($g_comp_database);
        $l_otypes = $l_dao->get_types();

        $templates = isys_application::instance()->container->get('template');

        $objectTypes = filter_defined_constants(['C__OBJTYPE__CONTAINER', 'C__OBJTYPE__GENERIC_TEMPLATE', 'C__OBJTYPE__LOCATION_GENERIC']);

        while ($l_row = $l_otypes->get_row()) {
            if (!in_array($l_row['isys_obj_type__id'], $objectTypes, false)) {
                $l_object_types[$l_row['isys_obj_type__id']] = $this->language->get($l_row['isys_obj_type__title']);
            }
        }

        if (is_array($_FILES) && count($_FILES) > 0) {
            $l_mimetypes = [
                'text/comma-separated-values',
                'text/csv',
                'application/csv',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'application/octet-stream'
            ];

            // If an error happens, there wont be a mime type, so further checks are not needed
            if ($_FILES['import_file']['error'] != UPLOAD_ERR_OK) {
                isys_notify::error($this->language->get('LC__MODULE__IMPORT__UPLOAD_ERROR_' . $_FILES['import_file']['error']));
            } else {
                if ($_FILES['import_file']['type'] === 'application/octet-stream') {
                    isys_notify::info($this->language->get('LC__MODULE__IMPORT__UPLOAD_MIMETYPE_MISMATCH'), ['life' => 10]);
                }

                if (!in_array($_FILES["import_file"]['type'], $l_mimetypes) && !strstr($_FILES["import_file"]['type'], 'text')) {
                    isys_notify::error($this->language->get('LC__MODULE__IMPORT__UPLOAD_PROHIBITED'));
                } else {
                    $l_fileman->receive($_FILES["import_file"]);
                    $l_errors = $l_fileman->get_errors();

                    if (is_countable($l_errors) && count($l_errors) > 0) {
                        $l_error = (!strstr($l_errors[0], "%s")) ? $l_errors[0] : null;
                        $templates
                            ->assign('class', 'msgbox_error')
                            ->assign('message', $this->language->get('LC__UNIVERSAL__FILE_UPLOAD__FAILED') . ': ' . $l_error . '(' . $_FILES['import_file']['name'] . ')');
                    } else {
                        $templates
                            ->assign('class', 'msgbox_info')
                            ->assign('message', $this->language->get('LC__UNIVERSAL__FILE_UPLOAD__SUCCESS') . ': (' . $_FILES['import_file']['name'] . ')');
                    }
                }
            }
        }

        $templates
            ->assign('object_types', $l_object_types)
            ->assign('import_path', str_replace($g_absdir . "/", "", C__IMPORT__DIRECTORY));
    }

    /**
     * Method for displaying the inventoried objects.
     */
    private function imports()
    {
        $l_imports = $this->get_imports();
        if (!defined('C__MODULE__IMPORT')) {
            return;
        }

        if ($l_imports->num_rows() > 0) {
            // Link for each table-row.
            $l_rowlink = '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__CMDB') .
                '&' . C__CMDB__GET__VIEWMODE . '=1100' .
                '&' . C__CMDB__GET__TREEMODE . '=1006' .
                '&' . C__CMDB__GET__OBJECTTYPE . '=[{isys_obj__isys_obj_type__id}]' .
                '&' . C__CMDB__GET__OBJECT . '=[{isys_obj__id}]';

            // Array with table header titles.
            $l_tableheader = [
                'isys_obj__id'         => 'LC__UNIVERSAL__ID',
                'isys_obj_type__title' => 'LC__CMDB__OBJTYPE',
                'isys_obj__title'      => 'LC__UNIVERSAL__TITLE',
                'isys_obj__hostname'   => 'LC__CATP__IP__HOSTNAME',
                'isys_obj__scantime'   => $this->language->get('LC_CALENDAR_POPUP__TIME_OF_DAY') . ' (Scan)'
            ];

            $l_objList = new isys_component_list(null, $l_imports);
            $l_objList->config($l_tableheader, $l_rowlink, '', true);
            $l_objList->createTempTable();

            $l_pagerlink = '?' . C__GET__MODULE_ID . '=' . C__MODULE__IMPORT . '&' . C__GET__PARAM . '=' . $_GET[C__GET__PARAM] . '&' . C__GET__TREE_NODE . '=' .
                $_GET[C__GET__TREE_NODE] . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET[C__GET__MAIN_MENU__NAVIGATION_ID];

            $l_navbar = isys_component_template_navbar::getInstance();
            $l_navbar->set_url($l_pagerlink, C__NAVMODE__FORWARD);
            $l_navbar->set_url($l_pagerlink, C__NAVMODE__BACK);

            isys_application::instance()->template->assign("g_list", $l_objList->getTempTableHtml());

            if ($_GET[C__GET__AJAX] == 1) {
                $l_navbar->show_navbar();
                echo isys_application::instance()->template->display('modules/import/import_main.tpl');
                die();
            }
        }
    }

    /**
     * Method for retrieving the files.
     *
     * @return  array
     */
    private function get_files()
    {
        $l_files = [];
        $l_filedata = '';
        $locales = isys_application::instance()->container->get('locales');

        try {
            if (is_writable(C__IMPORT__DIRECTORY)) {
                $l_fh = opendir(C__IMPORT__DIRECTORY);
                $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

                // Activate internal errors
                libxml_use_internal_errors(true);
                while ($l_file = readdir($l_fh)) {
                    if (strpos($l_file, ".") !== 0 && !is_dir(C__IMPORT__DIRECTORY . "/" . $l_file)) {
                        $l_data = null;
                        $l_imported_mktime = 0;
                        $l_scantime_mktime = 0;
                        $errorCount = 0;

                        $l_object_count = "?";
                        $l_type = $l_empty_value;
                        $l_stripped = str_replace(".xml", "", $l_file);

                        if (file_exists(C__IMPORT__DIRECTORY . $l_file)) {
                            $l_filedata = file_get_contents(C__IMPORT__DIRECTORY . $l_file);
                            try {
                                if (strpos(trim($l_filedata), "<") === 0) {
                                    $l_replace_array = [
                                        '<value></value>' => '',
                                    ];
                                    $l_xmlcontent = trim(isys_glob_replace_accent(strtr($l_filedata, $l_replace_array)));

                                    if (!empty($l_xmlcontent)) {
                                        try {
                                            $l_xmllib = new isys_library_xml($l_xmlcontent);
                                            $l_data = $l_xmllib->simple_xml_string($l_xmlcontent);
                                        } catch (Exception $e) {
                                            // xml file not readable
                                            $xmlErrors = libxml_get_errors();
                                            if (is_countable($xmlErrors) && count($xmlErrors)) {
                                                // Get last error element
                                                $xmlError = array_pop($xmlErrors);
                                                $xmlErrorMessage = trim($xmlError->message);
                                                throw new Exception('File error in "' . $l_file . '" with message: ' . $xmlErrorMessage);
                                            }
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                isys_notify::error($e->getMessage(), ['sticky' => true]);
                            }
                        }

                        if ($l_data) {
                            $l_hostname = (string)$l_data->hostname;
                            $l_scantime = (string)$l_data->datetime;
                            if ($l_data->hostname) {
                                $l_type = "inventory";
                                $l_object_count = 1;
                            }

                            if (!$l_scantime) {
                                $l_scantime = (string)$l_data->head->datetime;
                            }

                            if (!empty($l_hostname)) {
                                $l_status = $this->check_status($l_hostname);
                            } else {
                                $l_status = [];
                            }

                            if (strstr($l_scantime, "/")) {
                                $l_scantmp_1 = explode(" ", $l_scantime);
                                $l_date = $l_scantmp_1[0];
                                $l_time = $l_scantmp_1[1];

                                $l_scantmp_2 = explode("/", $l_date);

                                $l_scantime = $l_scantmp_2[0] . "." . $l_scantmp_2[1] . "." . $l_scantmp_2[2] . " " . $l_time;
                            }

                            if (!$l_hostname && isset($l_data->objects)) {
                                $l_object_count = (int)$l_data->objects->attributes()->count;

                                if (isset($l_data->head)) {
                                    if (isset($l_data->head->format)) {
                                        $l_type = (string)$l_data->head->format;
                                    } else {
                                        $l_type = (string)$l_data->head->type;
                                    }

                                    $l_hostname = (string)$l_data->objects->object->title;
                                    $l_status = $this->check_status(null, $l_hostname);
                                }
                            }

                            if ($l_status) {
                                $l_imported_mktime = strtotime($l_status["isys_obj__scantime"]);
                            }

                            if ($l_scantime) {
                                $l_scantime_mktime = strtotime($l_scantime);
                            }

                            if ($l_scantime_mktime <= $l_imported_mktime) {
                                $l_dupe = true;
                            } else {
                                $l_dupe = false;
                            }
                        } else {
                            $l_err = libxml_get_errors();

                            $l_status = false;
                            $l_dupe = false;
                            if (substr($l_file, -4) === '.csv') {
                                $l_type = 'csv';
                                $l_object_count = count(explode("\n", $l_filedata)) - 1;
                                if ($l_object_count <= 0) {
                                    $l_object_count = 1;
                                }
                            } elseif (substr($l_file, -4) === '.xml') {
                                $errorCount = is_countable($l_err) ? count($l_err) : 0;
                                if ($errorCount) {
                                    $l_type = 'isys_export_type_xml';
                                }
                            } else {
                                $l_type = 'not_supported';
                            }
                        }

                        $l_dlgets = $this->m_userrequest->get_gets();
                        $l_dlgets[C__GET__FILE_MANAGER] = "get";
                        $l_dlgets[C__GET__FILE_NAME] = urlencode($l_file);
                        $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__IMPORT');
                        $l_download_link = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_dlgets)));

                        $l_files[$l_stripped] = [
                            'filename'   => $l_file,
                            'stripped'   => $l_stripped,
                            'count'      => $l_object_count,
                            'type'       => $l_type,
                            'scantime'   => $locales->fmt_datetime($l_scantime_mktime),
                            'importtime' => ($l_imported_mktime ? $locales->fmt_datetime($l_imported_mktime) : false),
                            'dupe'       => $l_dupe,
                            'status'     => $l_status,
                            'download'   => $l_download_link,
                            'errorCount' => $errorCount
                        ];
                    }
                    sort($l_files);

                    unset($l_scantime, $l_scantime_mktime, $l_imported_mktime, $l_object_count);
                }
                // Clear libxml Errors.
                libxml_clear_errors();

                // Deactive internal errors.
                libxml_use_internal_errors(false);
            } else {
                throw new isys_exception_general(C__IMPORT__DIRECTORY . ' is not writable. Please create it and give Apache writing rights to it on unix systems.');
            }
        } catch (Exception $e) {
        }

        return $l_files;
    }

    /**
     * Build and assign system menu
     */
    private function build_system_menu()
    {
        $l_tree = $this->m_userrequest->get_menutree();

        $this->build_tree($l_tree, false);
        $this->m_userrequest->get_template()
            ->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));
    }

    /**
     * Handles the download of import files
     *
     * @throws Exception
     * @throws isys_exception_cmdb
     */
    private function handle_file_manager()
    {
        try {
            $l_gets = $this->m_userrequest->get_gets();
            $l_file_manager = new isys_component_filemanager();

            if (isset($l_gets[C__GET__FILE_NAME])) {
                $l_filename = $l_gets[C__GET__FILE_NAME];
                $l_files = null;

                // Send outputs directly to the file and to the client.
                if (!$l_file_manager->send(C__IMPORT__DIRECTORY . $l_filename, $l_files, C_FILES__MODE_DOWNLOAD)) {
                    header('HTTP/1.0 404 Not Found');
                    die;
                }
            }
        } catch (isys_exception_cmdb $e) {
            throw $e;
        }
    }

    /**
     * @deprecated Keep for backward compatibility until i-doit 1.17
     */
    public static function get_auth()
    {
        return self::getAuth();
    }

    /**
     * @return isys_auth_import
     */
    public static function getAuth()
    {
        return isys_auth_import::instance();
    }
}
