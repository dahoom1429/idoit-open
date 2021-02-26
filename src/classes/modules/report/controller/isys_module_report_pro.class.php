<?php

/**
 * i-doit
 *
 * i-doit Report Manager PRO Version.
 *
 * @package       i-doit
 * @subpackage    Modules
 * @author        Dennis Bluemer <dbluemer@synetics.de>
 * @author        Van Quyen Hoang    <qhoang@synetics.de>
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report_pro extends isys_module_report
{
    // Define, if this module shall be displayed in the named menus.
    /**
     *
     */
    const DISPLAY_IN_MAIN_MENU = true;
    /**
     *
     */
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * URL of the report-repository.
     *
     * @var         string
     * @deprecated  ??
     */
    private $m_report_browser_www = "https://reports-ng.i-doit.org/";

    /**
     * Header of the current report
     *
     * @var array
     */
    private $m_report_headers = [];

    /**
     * @param $p_report_class_path
     */
    public static function add_external_view($p_report_class_path)
    {
        isys_register::factory('additional-report-views')
            ->set($p_report_class_path);
    }

    /**
     * @return string[]
     */
    public static function getReportListHeaders()
    {
        return [
            'isys_report__id'              => 'ID',
            'isys_report__title'           => 'LC__UNIVERSAL__TITLE',
            'category_title'               => 'LC_UNIVERSAL__CATEGORY',
            'with_qb'                      => 'LC__REPORT__LIST__VIA_QUERY_BUILDER_CREATED',
            'isys_report__category_report' => 'LC__REPORT__FORM__CATEGORY_REPORT',
            'isys_report__description'     => 'LC__UNIVERSAL__DESCRIPTION'
        ];
    }

    /**
     * Method for assigning the object types to the dropdown.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws Exception
     * @global  isys_component_database $g_comp_database
     */
    public function build_object_types()
    {
        global $g_comp_database;

        $l_result = $g_comp_database->query('SELECT isys_obj_type__id AS id, isys_obj_type__title AS type
            FROM isys_obj_type
            WHERE isys_obj_type__show_in_tree = 1
            OR isys_obj_type__const = "C__OBJTYPE__RELATION";');

        while ($l_row = $g_comp_database->fetch_array($l_result)) {
            $l_objects[$l_row['id']] = $this->language->get($l_row['type']);
        }

        asort($l_objects);

        isys_application::instance()->container->get('template')->assign('object_types', $l_objects);
    }

    /**
     * Method for preparing the report-data to view it properly with the TabOrder.
     * This is used by the ajax handler for the online reports and the normal reports.
     *
     * @param   string  $l_query
     * @param   null    $deprecated
     * @param   boolean $p_ajax_request
     * @param   boolean $p_limit
     * @param   boolean $p_raw_data
     * @param   boolean $p_title_chaining
     * @param   boolean $compressedMultivalueResults
     *
     * @return  mixed  If this method is called by an ajax request, it returns an array. If not, null.
     * @throws Exception
     * @since   0.9.9-9
     * @author  Leonard Fischer <lfischer@synetic.de>
     */
    public function process_show_report(
        $l_query,
        $deprecated = null,
        $p_ajax_request = false,
        $p_limit = false,
        $p_raw_data = false,
        $p_title_chaining = true,
        $compressedMultivalueResults = false,
        $showHtml = false
    ) {
        try {
            $l_result = isys_report_dao::instance(isys_application::instance()->container->get('database_system'))
                ->query($l_query, null, $p_raw_data, $p_title_chaining);
            $l_json = $l_return = [];
            $l_counter = 0;

            $l_return = isys_report::reformatResult($l_result, $showHtml, $compressedMultivalueResults, $p_limit);

            if (isset($l_result['headers'])) {
                $this->set_report_headers($l_result['headers']);
            }

            if ($p_ajax_request) {
                return $l_return;
            }

            $l_json = isys_format_json::encode($l_return);

            isys_application::instance()->container->get('template')
                ->assign('rowcount', $l_result['num'])
                ->assign("listing", $l_result)
                ->assign("result", $l_json);
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')
                ->error($e->getMessage());
        }

        return '';
    }

    /**
     * @param      $p_viewdir
     * @param bool $p_as_dialog
     * @param bool $p_check_right
     *
     * @return array
     */
    public function getViews($p_viewdir, $p_as_dialog = false, $p_check_right = false)
    {
        $l_views = [];

        try {
            if (is_readable($p_viewdir)) {
                $l_dirhndl = dir($p_viewdir);

                while ($l_f = $l_dirhndl->read()) {
                    if (strpos($l_f, ".") !== 0 && $l_f != "isys_report_view.class.php" && $l_f != "isys_report_view_sla.class.php") {
                        $l_class = str_replace(".class.php", "", $l_f);

                        /** @var  isys_report_view $l_class_object */
                        $l_class_object = new $l_class();

                        if (method_exists($l_class_object, 'name')) {
                            if ($p_check_right) {
                                if (!isys_auth_report::instance()
                                    ->is_allowed_to(isys_auth::VIEW, 'VIEWS/' . strtoupper($l_class))) {
                                    continue;
                                }
                            }

                            if (!$p_as_dialog) {
                                $l_views[] = [
                                    "filename"    => $l_f,
                                    "view"        => str_replace([
                                        "isys_report_view_",
                                        ".class.php"
                                    ], "", $l_f),
                                    "class"       => $l_class,
                                    "name"        => $this->language->get($l_class_object->name()),
                                    "description" => $this->language->get($l_class_object->description()),
                                    "viewtype"    => $l_class_object->viewtype()
                                ];
                            } else {
                                $l_views[$l_class] = $this->language->get($l_class_object->name());
                            }
                        }
                    }
                }
            } else {
                throw new Exception('Report view directory ' . $p_viewdir . ' is not readable.');
            }
            $l_dirhndl->close();

            $l_external_views = isys_register::factory('additional-report-views')
                ->get();

            foreach ($l_external_views as $l_external_view_class => $l_tmp) {
                if (is_file($l_external_view_class)) {
                    include_once($l_external_view_class);

                    $l_classname = strstr(basename($l_external_view_class), '.class.php', true);

                    /** @var isys_report_view $l_class */
                    $l_class = new $l_classname;

                    if (!$p_as_dialog) {
                        $l_views[] = [
                            "filename"    => basename($l_external_view_class),
                            "view"        => $l_classname . '.tpl',
                            "class"       => $l_classname,
                            "name"        => $this->language->get($l_class->name()),
                            "description" => $this->language->get($l_class->description()),
                            "viewtype"    => $l_class->viewtype()
                        ];
                    } else {
                        $l_views[$l_classname] = $this->language->get($l_class->name());
                    }
                }
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }

        // Adding some further report-views, which got added by modules.
        return $l_views;
    }

    /**
     * Checks if the report can only be edited via sql editor or not
     *
     * @param $p_id
     *
     * @return bool
     * @throws Exception
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function is_sql_only_report($p_id)
    {
        $l_report = $this->m_dao->get_report($p_id);

        return (empty($l_report['isys_report__querybuilder_data']) ? true : false);
    }

    /**
     * Enhances the breadcrumb navigation.
     *
     * @param $p_gets
     *
     * @return array
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@synetics.de>
     * @throws Exception
     */
    public function breadcrumb_get(&$p_gets)
    {
        if (!defined('C__MODULE__REPORT')) {
            return [];
        }
        $l_result = [];

        switch ($p_gets[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                $l_title = $this->language->get('LC__REPORT__MAINNAV__STANDARD_QUERIES');

                if (isset($p_gets['report_category'])) {
                    $l_report_category = current($this->m_dao->get_report_categories($p_gets['report_category']));
                    $l_report_category_title = $l_report_category['isys_report_category__title'];
                }

                if (isset($p_gets[C__GET__REPORT_REPORT_ID])) {
                    $l_report_title = $this->m_dao->get_report_title_by_id($p_gets[C__GET__REPORT_REPORT_ID]);
                }

                break;
            case C__REPORT_PAGE__REPORT_BROWSER:
                $l_title = $this->language->get('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $l_title = 'Views';

                if (isset($p_gets[C__GET__REPORT_REPORT_ID]) && class_exists($p_gets[C__GET__REPORT_REPORT_ID])) {
                    $l_report_title = $this->language->get($p_gets[C__GET__REPORT_REPORT_ID]::name());
                }
                break;
            default:
                return null;
                break;
        }

        if (isset($l_report_category_title)) {
            $l_result[] = [
                $l_report_category_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                    'report_category'   => $p_gets['report_category']
                ]
            ];
        } else {
            $l_result[] = [
                $l_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                ]
            ];
        }

        if (isset($l_report_title)) {
            $l_result[] = [
                $l_report_title => []
            ];
        }

        return $l_result;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @param   integer             $expandReports
     *
     * @throws isys_exception_database
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     * @since     0.9.9-7
     * @see       isys_module::build_tree()
     * @throws Exception
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null, $expandReports = 0)
    {
        if (!defined('C__MODULE__REPORT')) {
            return;
        }
        $l_parent = -1;
        $l_submodule = '';

        $p_tree->set_tree_sort(false);

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__REPORT;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__REPORT . '0', $l_parent, 'Report Manager');
        }

        $l_report_root = $p_tree->add_node(
            C__MODULE__REPORT . '2',
            $l_root,
            $this->language->get('LC__REPORT__MAINNAV__STANDARD_QUERIES'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            '',
            ((($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__CUSTOM_REPORTS || !isset($_GET[C__GET__REPORT_PAGE])) && !isset($_GET['report_category'])) ? 1 : 0),
            '',
            '',
            (isys_auth_report::instance()
                    ->has('custom_report') || isys_auth_report::instance()
                    ->has('reports_in_category')),
            '',
            $expandReports
        );

        $l_res = $this->m_dao->get_report_categories(null, false);
        while ($l_row = $l_res->get_row()) {
            $p_tree->add_node(
                C__MODULE__REPORT . '2' . $l_row['isys_report_category__id'],
                $l_report_root,
                $this->language->get($l_row['isys_report_category__title']),
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' .
                $l_row['isys_report_category__id'] . '&' . C__GET__REPORT_PAGE . '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&report_category=' .
                $l_row['isys_report_category__id'] . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
                '',
                'images/icons/silk/page_portrait.png',
                ((isset($_GET['report_category']) && $_GET['report_category'] == $l_row['isys_report_category__id']) ? 1 : 0),
                '',
                '',
                (isys_auth_report::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'REPORTS_IN_CATEGORY/' . $l_row['isys_report_category__id']))
            );
        }

        $p_tree->add_node(
            C__MODULE__REPORT . '3',
            $l_root,
            $this->language->get('LC__REPORT__MAINNAV__QUERY_BROWSER'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '3' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__REPORT_BROWSER . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/icons/silk/report_picture.png',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__REPORT_BROWSER) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("online_reports")
        );

        $p_tree->add_node(
            C__MODULE__REPORT . '5',
            $l_root,
            'Views',
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '5' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__VIEWS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/icons/silk/report_magnify.png',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__VIEWS) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("views")
        );
    }

    /**
     * Start module Report Manager.
     *
     * @author    Dennis Blümer <dbluemer@synetics.de>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     * @throws Exception
     */
    public function start()
    {
        $l_id = 0;

        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call")) {
            $this->processAjaxRequest();
            die;
        }

        if (isset($_GET["export"])) {
            $this->exportReport($_GET["report_id"], $_GET["type"]);
            die;
        }

        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        isys_application::instance()->container->get('template')->assign('allowedObjectGroup', isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJECT_TYPE__GROUP'));

        try {
            switch ($l_gets[C__GET__REPORT_PAGE]) {
                case C__REPORT_PAGE__REPORT_BROWSER:
                    isys_auth_report::instance()
                        ->check(isys_auth::VIEW, 'ONLINE_REPORTS');
                    $this->processReportBrowser();
                    break;
                case C__REPORT_PAGE__VIEWS:
                    isys_auth_report::instance()
                        ->check(isys_auth::VIEW, 'VIEWS');
                    $this->processViews();
                    break;
                case C__REPORT_PAGE__CUSTOM_REPORTS:
                default:
//					isys_auth_report::instance()->check(isys_auth::VIEW, 'CUSTOM_REPORT');
                    switch ($_POST[C__GET__NAVMODE]) {
                        case C__NAVMODE__DUPLICATE:
                            try {
                                $this->duplicate_report($_POST['report_id']);
                                unset($_POST['savedCheckboxes']);
                                $this->processReportList();
                                isys_notify::success($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__SUCCESS'));
                            } catch (Exception $e) {
                                isys_notify::error($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__ERROR'));
                            }
                            break;
                        case C__NAVMODE__EDIT:
                            if (is_array($_POST["id"])) {
                                isys_auth_report::instance()
                                    ->check_report_right(isys_auth::EDIT, $_POST["id"][0]);
                                if (isset($_POST["querybuilder"]) && $_POST["querybuilder"] != '') {
                                    if ((bool)$_POST["querybuilder"]) {
                                        if ($this->is_sql_only_report($_POST["id"][0])) {
                                            $this->processReportList();
                                            isys_notify::error($this->language->get('LC__REPORT__LIST__EDIT__ERROR'));
                                        } else {
                                            $this->processQueryBuilder($_POST["id"][0], $_GET['report_category']);
                                        }
                                    } else {
                                        $this->editReport($_POST["id"][0]);
                                    }
                                } else {
                                    if ($this->is_sql_only_report($_POST["id"][0])) {
                                        $this->editReport($_POST["id"][0]);
                                    } else {
                                        $this->processQueryBuilder($_POST["id"][0], $_GET['report_category']);
                                    }
                                }
                            } else {
                                $this->processReportList();
                            }

                            break;

                        case C__NAVMODE__SAVE:
                            try {
                                if (!empty($_POST['report_mode'])) {
                                    isys_auth_report::instance()
                                        ->check(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');
                                    switch ($_POST['report_mode']) {
                                        case 'category':
                                            if ($_POST['category_selection'] > 0) {
                                                // update
                                                $this->update_category(
                                                    $_POST['category_selection'],
                                                    $_POST['category_title'],
                                                    $_POST['category_description'],
                                                    $_POST['category_sort']
                                                );
                                            } else {
                                                // create
                                                $this->create_category($_POST['category_title'], $_POST['category_description'], $_POST['category_sort']);
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                    $this->processReportList();
                                } else {
                                    try {
                                        // isys_auth_report::instance()->check(isys_auth::EXECUTE, 'EDITOR');

                                        if (!empty($_POST['report_id'])) {
                                            // Update
                                            if (!empty($_POST["queryBuilder"])) {
                                                $this->saveReport($_POST['report_id'], true);
                                            } else {
                                                // SQL-Editor
                                                $this->saveReport($_POST['report_id']);
                                            }
                                        } else {
                                            // Create
                                            if (isset($_POST["queryBuilder"]) && $_POST["queryBuilder"] == 1) {
                                                // Query Builder
                                                $l_id = $this->createReport(true);
                                            } else {
                                                // SQL Editor
                                                $l_id = $this->createReport(false);
                                            }
                                        }
                                        isys_notify::success($this->language->get('LC__REPORT__FORM__SUCCESS'));

                                        if ($l_id > 0) {
                                            header('Content-Type: application/json');
                                            die(isys_format_json::encode([
                                                'success' => true,
                                                'id'      => $l_id
                                            ]));
                                        }
                                    } catch (Exception $e) {
                                        isys_notify::error($this->language->get('LC__REPORT__FORM__ERROR'));
                                    }
                                }
                            } catch (Exception $e) {
                                isys_notify::error($e->getMessage());
                            }
                            break;

                        case C__NAVMODE__NEW:
                            // @see  ID-4035  Replace the "editor" right with "create: reports in category".
                            if (is_numeric($_GET['report_category'])) {
                                self::getAuth()->check(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/' . $_GET['report_category']);
                            } else {
                                self::getAuth()->check(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/*');
                            }

                            if (isset($_POST['querybuilder']) && $_POST['querybuilder'] != '') {
                                switch ($_POST['querybuilder']) {
                                    case '0':
                                        $this->editReport();
                                        break;
                                    case '1':
                                    default:
                                        $this->processQueryBuilder(null, $_GET['report_category']);
                                        break;
                                }
                            } else {
                                $this->processQueryBuilder(null, $_GET['report_category']);
                            }
                            break;

                        case C__NAVMODE__PURGE:
                            if (is_array($_POST["id"])) {
                                $deletionFailed = [];

                                foreach ($_POST["id"] as $l_id) {
                                    try {
                                        $this->deleteReport($l_id);
                                    } catch (Exception $e) {
                                        $deletionFailed[] = '#' . $l_id;
                                    }
                                }

                                // @see  ID-3813  Inform the user why certain reports have not been purged.
                                if (count($deletionFailed)) {
                                    $message = $this->language->get('LC__REPORT__PURGE_FAILURE') . isys_helper_textformat::this_this_and_that($deletionFailed);

                                    isys_notify::warning($message, ['sticky' => true]);
                                }
                            }

                            $this->processReportList();
                            break;

                        default:
                            if (isset($_GET[C__GET__REPORT_REPORT_ID])) {
                                isys_auth_report::instance()
                                    ->check_report_right(isys_auth::EXECUTE, $_GET[C__GET__REPORT_REPORT_ID]);
                                $this->showReport($_GET[C__GET__REPORT_REPORT_ID]);
                            } else {
                                $this->processReportList();
                            }
                            break;
                    }
                    break;
            }
        } catch (isys_exception_auth $e) {
            isys_application::instance()->container->get('template')->assign("exception", $e->write_log())
                ->include_template('contentbottomcontent', 'exception-auth.tpl');
        }

        // Is the tree part of the system menu?
        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            // Handle the tree.
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();

            $this->build_tree($l_tree, false);

            isys_application::instance()->container->get('template')->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        }
    }

    /**
     * Set header of the current report
     *
     * @param $p_headers
     */
    public function set_report_headers($p_headers)
    {
        $this->m_report_headers = $p_headers;
    }

    /**
     * Gets the header of the current report
     *
     * @return array
     */
    public function get_report_headers()
    {
        return $this->m_report_headers;
    }

    /**
     * Method for building the report SQL.
     *
     * @return  array
     * @author    Leonard Fischer <lfischer@i-doit.org>
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     * @throws Exception
     */
    protected function buildReport()
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

        // Returning the SQL-query and the other dara (title, description, ...).
        $l_return = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'type'              => 'c',
            'userspecific'      => $_POST['chk_user_specific'],
            'query'             => $l_dao
                    ->prepareEnvironmentForReportByPost()
                    ->create_property_query_for_report(),
            'report_category'   => $_POST['report_category'],
            'empty_values'      => $_POST['empty_values'],
            'compressed_multivalue_results' => $_POST['compressed_multivalue_results'],
            'show_html' => $_POST['show_html'],
            'display_relations' => $_POST['display_relations'],
            'keep_description_format' => $_POST['keep_description_format'],
            'category_report'   => $_POST['category_report']
        ];
        if (!is_array($_POST['lvls_raw'])) {
            $l_lvls = isys_format_json::decode($_POST['lvls_raw']);
        } else {
            $l_lvls = $_POST['lvls_raw'];
        }

        if ($l_lvls !== null) {
            foreach ($l_lvls as $l_key => $l_lvl_content) {
                foreach ($l_lvl_content as $l_key2 => $l_content) {
                    $l_lvls[$l_key][$l_key2] = isys_format_json::decode($l_content);
                }
            }
        }

        if (!is_array($_POST['querycondition'])) {
            $l_condition = isys_format_json::decode($_POST['querycondition']);
        } else {
            $l_condition = $_POST['querycondition'];
        }

        $l_arr = [
            'main_object'       => isys_format_json::decode($_POST['report__HIDDEN']),
            'lvls'              => $l_lvls,
            'conditions'        => $l_condition,
            'default_sorting'   => $_POST['default_sorting'],
            'sorting_direction' => $_POST['sorting_direction'],
            'statusFilter'      => $_POST['statusFilter']
        ];

        $l_return['querybuilder_data'] = isys_format_json::encode($l_arr);

        if (!empty($_GET[C__GET__REPORT_REPORT_ID]) || !empty($_POST['report_id'])) {
            $l_return['report_id'] = (!empty($_POST['report_id']) ? $_POST['report_id'] : $_GET[C__GET__REPORT_REPORT_ID]);
        }

        return $l_return;
    }

    /**
     * Updates reports
     *
     * @param      $p_id
     * @param bool $p_querybuilder
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws Exception
     */
    private function saveReport($p_id, $p_querybuilder = false)
    {
        if ($p_querybuilder) {
            $l_report = new isys_report($this->buildReport());
            $l_report->update();
        } else {
            return $this->m_dao->saveReport(
                $p_id,
                $_POST["title"],
                $_POST["description"],
                $_POST["query"],
                null,
                (($_POST['chk_user_specific'] == 'on' ? true : false)),
                $_POST['report_category'],
                $_POST['compressed_multivalue_results'],
                $_POST['show_html'],
                $_POST['keep_description_format']
            );
        }

        return false;
    }

    /**
     * Duplicates Report
     *
     * @param $p_report_id
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     * @throws Exception
     */
    private function duplicate_report($p_report_id)
    {
        $l_report = $this->m_dao->get_report($p_report_id);
        $l_param = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'query'             => $l_report['isys_report__query'],
            'userspecific'      => ($_POST['chk_user_specific'] == 'on') ? 1 : 0,
            'querybuilder_data' => $l_report['isys_report__querybuilder_data'],
            'report_category'   => $_POST['category_selection'],
            'type'              => $l_report['isys_report__type'],
            'empty_values'      => $l_report['isys_report__empty_values'],
            'category_report'   => $l_report['isys_report__category_report'],
            'compressed_multivalue_results' => $_POST['compressed_multivalue_results'] ?: $l_report['isys_report__compressed_multivalue_results'],
            'show_html' => $_POST['show_html'] ?: $l_report['isys_report__show_html'],
            'display_relations' => $l_report['isys_report__display_relations'],
            'keep_description_format' => $l_report['isys_report__keep_description_format'],
        ];
        $l_report_instance = new isys_report($l_param);
        $l_report_id = $l_report_instance->store();

        // Add right
        $this->add_right($l_report_id, 'custom_report');
    }

    /**
     * This Method is a helper method which adds the rights for custom_report or reports_in_category
     *
     * @param $p_id
     * @param $p_method
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function add_right($p_id, $p_method)
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session, $g_comp_database;

        // Check if user has wildcard rights for CUSTOM_REPORTS and REPORTS
        switch ($p_method) {
            case 'custom_report':
                if (isys_auth_report::instance()
                        ->get_allowed_reports() === true) {
                    return;
                }
                break;
            case 'reports_in_category':
                if (isys_auth_report::instance()
                        ->get_allowed_report_categories() === true) {
                    return;
                }
                break;
        }

        $l_user_id = (int)$g_comp_session->get_user_id();
        $l_path_data = [$p_method => [$p_id => [isys_auth::SUPERVISOR]]];
        // Add right for the report
        isys_auth_dao::instance($g_comp_database)
            ->create_paths($l_user_id, defined_or_default('C__MODULE__REPORT'), $l_path_data);
        isys_caching::factory('auth-' . $l_user_id)
            ->clear();
    }

    /**
     * @param  integer $p_id
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_auth
     */
    private function deleteReport($p_id)
    {
        $l_report = $this->m_dao->get_report($p_id);

        if (isys_application::instance()->container->get('session')->get_user_id() != $l_report['isys_report__user']) {
            isys_auth_report::instance()->check(isys_auth::DELETE, 'CUSTOM_REPORT/' . $p_id);
        }

        return $this->m_dao->deleteReport($p_id);
    }

    /**
     * @param $p_id
     *
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @throws Exception
     */
    private function editReport($p_id = null)
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session;

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__EDIT:
            case C__NAVMODE__NEW:
                $l_navbar = isys_component_template_navbar::getInstance();
                $l_navbar->set_save_mode('ajax')
                    ->set_ajax_return('ajaxReturnNote')
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
        }

        $l_title = null;
        $l_selected_category = null;
        $l_report_description = null;
        $l_query = null;
        $l_user_specific = null;
        $l_my_report = false;
        $l_report = null;

        if ($p_id !== null) {
            $l_report = $this->m_dao->get_reports(null, [$p_id], null, true, false)[0];

            $l_title = $l_report["isys_report__title"];
            $l_selected_category = $l_report['isys_report__isys_report_category__id'];
            $l_report_description = $l_report["isys_report__description"];
            $l_query = isys_glob_htmlentities($l_report["isys_report__query"]);
            $l_user_specific = $l_report["isys_report__user_specific"];

            if ($g_comp_session->get_user_id() == $l_report['isys_report__user']) {
                $l_my_report = true;
            } else {
                isys_auth_report::instance()
                    ->check(isys_auth::EDIT, 'CUSTOM_REPORT/' . $p_id);
            }
        }

        $l_allowed_report_categories = isys_auth_report::instance()->get_allowed_report_categories();

        if ($l_allowed_report_categories === false) {
            $l_report_category_data = $this->m_dao->get_report_categories('Global', false)->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } else {
            $l_report_categories = $this->m_dao->get_report_categories($l_allowed_report_categories);
            $l_data = [];
            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    try {
                        // @see  ID-5548  Check if the user is allowed to see this category.
                        isys_auth_report::instance()->reports_in_category(isys_auth::CREATE, $l_category['isys_report_category__id']);

                        $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                    } catch (Exception $e) {
                        // Do nothing.
                    }
                }
            }
        }

        $l_rules = [
            'title'             => ['p_strValue' => $l_title],
            'report_category'   => [
                'p_strSelectedID' => $l_selected_category,
                'p_arData'        => $l_data
            ],
            'description'       => ['p_strValue' => $l_report_description],
            'query'             => ['p_strValue' => $l_query],
            'chk_user_specific' => [
                'p_bChecked'    => $l_user_specific,
                'p_strDisabled' => (!$l_my_report)
            ],
            'compressed_multivalue_results' => [
                'p_strSelectedID' => ($l_report['isys_report__compressed_multivalue_results'] ?: 0)
            ],
            'show_html' => [
                'p_strSelectedID' => ($l_report['isys_report__show_html'] ?: 0)
            ]
        ];

        if (!empty($l_report["isys_report__querybuilder_data"])) {
            isys_application::instance()->container->get('template')
                ->assign("querybuilder_warning", $this->language->get('LC__REPORT__EDIT__WARNING_TEXT'));
        }

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__REPORT__LIST__SQL_EDITOR'))
            ->assign("report_id", $l_report["isys_report__id"])
            ->activate_editmode()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_edit.tpl');
    }

    /**
     * @param $p_id
     * @param $p_type
     *
     * @throws Exception
     */
    private function exportReport($p_id, $p_type)
    {
        $l_row = $this->m_dao->get_report($p_id);

        $l_report = [
            'report_id'   => $l_row['isys_report__id'],
            'type'        => $l_row['isys_report__type'],
            'title'       => $l_row['isys_report__title'],
            'description' => $l_row['isys_report__description'],
            'query'       => $l_row['isys_report__query'],
            'mandator'    => $l_row['isys_report__mandator'],
            'datetime'    => $l_row['isys_report__datetime'],
            'last_edited' => $l_row['isys_report__last_edited'],
            'show_html'   => $l_row['isys_report__show_html'],
            'compressed_multivalue_results' => $l_row['isys_report__compressed_multivalue_results'],
            'keep_description_format' => $l_row['isys_report__keep_description_format'],
        ];

        try {
            isys_application::instance()->container->get('session')->write_close();

            $report = (new \idoit\Module\Report\Report(
                new isys_component_dao(isys_application::instance()->container->get('database')),
                $l_row['isys_report__query'],
                $l_row['isys_report__title'],
                $l_row['isys_report__id'],
                $l_row['isys_report__type'],
                $l_row['isys_report__compressed_multivalue_results']
            ))->setCompressedMultivalueResults($l_row['isys_report__compressed_multivalue_results']);

            switch ($p_type) {
                case 'xml':
                    $l_report = new isys_report_xml($l_report);
                    break;

                case 'csv':
                    \idoit\Module\Report\Export\CsvExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;
                case 'txt':
                    \idoit\Module\Report\Export\TxtExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;

                case 'pdf':
                    $l_report['title'] = utf8_decode($l_report['title']); // Bugfix for ID-2182
                    $l_report = new isys_report_pdf($l_report);
                    break;

                default:
                    throw new Exception('Missing or unknown export type');
            }

            $l_report->export();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (\idoit\Exception\OutOfMemoryException $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());

            throw $e;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Method for processing ajax requests.
     *
     * @throws isys_exception_database
     * @throws isys_exception_dao
     * @throws Exception
     */
    private function processAjaxRequest()
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session;

        $g_comp_session->write_close();

        switch (isys_glob_get_param("request")) {
            case "executeReport":

                $l_query = stripslashes($_POST["query"]);

                try {
                    $this->process_show_report($l_query);

                    isys_application::instance()->container->get('template')->assign("report_id", $_POST["reportID"])
                        ->assign("reportTitle", $_POST["title"])
                        ->assign("reportDescription", $_POST["desc"])
                        ->display($this->get_tpl_dir() . "report_result.tpl");
                } catch (Exception $e) {
                    isys_glob_display_error($e->getMessage());
                }

                break;

            case "downloadReport":
                if (isys_auth_report::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'ONLINE_REPORTS')) {
                    /** @var isys_report_dao $l_dao */
                    $l_dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));

                    if ($l_dao->reportExists($_POST["title"], $_POST["query"])) {
                        isys_notify::warning($this->language->get('LC__REPORT__EXISTS'));
                    } else {
                        $l_global_id = $l_dao->get_report_categories('Global', false)
                            ->get_row_value('isys_report_category__id');

                        if ($l_dao->createReport($_POST["title"], $_POST["desc"], $_POST["query"], null, false, false, $l_global_id) !== false) {
                            isys_notify::success($this->language->get('LC__REPORT__DOWNLOAD_SUCCESSFUL'));
                        } else {
                            isys_notify::error($this->language->get('LC__REPORT__ERROR_SAVING'));
                        }
                    }
                } else {
                    isys_notify::error($this->language->get('LC__UNIVERSAL__NO_ACCESS_RIGHTS'));
                }
                break;

            default:
                if (isset($_GET["reportID"])) {
                    $l_class = $_GET["reportID"];

                    if (class_exists($l_class)) {
                        $l_obj = new $l_class();

                        if (method_exists($l_obj, "ajax_request")) {
                            $l_obj->ajax_request();
                        }

                        unset($l_obj);
                    }
                }
                break;
        }
    }

    /**
     * Method for displaying a report.
     *
     * @param   integer                 $p_id
     *
     * @throws Exception
     * @global  isys_component_database $g_comp_database_system
     * @global  isys_component_database $g_comp_database
     *
     */
    private function showReport($p_id)
    {
        $l_report = isys_report_dao::instance(isys_application::instance()->container->get('database_system'))
            ->get_report($p_id);

        if ($l_report['isys_report__type'] !== 'c') {
            $query = isys_cmdb_dao_category_property::instance(isys_application::instance()->container->get('database'))
                    ->prepareEnvironmentForReportById($p_id)
                    ->create_property_query_for_report() . '';
        } else {
            $query = $l_report['isys_report__query'];
        }

        try {
            $l_ajax_pager = false;

            // We use this DAO because here we defined how many pages we want to preload.
            $l_dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));

            $l_rowcount = [
                'count' => 0
            ];

            try {
                if (\idoit\Module\Report\Validate\Query::validate($query)) {
                    /**
                     * Count rows based on the complete statement (as a fallback)
                     *
                     * @param $l_q
                     *
                     * @return array
                     */
                    $l_checkfallback = function ($l_q) use ($l_dao) {
                        // If our first try fails because we broke the SQL, we use this here...
                        return [
                            'count' => $l_dao->retrieve($l_q)
                                ->num_rows()
                        ];
                    };

                    /**
                     * Check for inner selects
                     *
                     * @see https://i-doit.atlassian.net/browse/ID-3099
                     */
                    if (preg_match('/SELECT.*?^[SELECT].*?FROM/is', $query)) {
                        // First we modify the SQL to find out, with how many rows we are dealing...
                        $l_rowcount_sql = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) as count FROM', $query, 1);

                        try {
                            $l_num_rows = $l_dao->retrieve($l_rowcount_sql)
                                ->num_rows();

                            if ($l_num_rows == 1) {
                                $l_rowcount = $l_dao->retrieve($l_rowcount_sql)
                                    ->get_row();
                            } else {
                                $l_rowcount['count'] = $l_num_rows;
                            }
                        } catch (Exception $e) {
                            // If our first try fails because we broke the SQL, we use this here...
                            $l_rowcount = $l_checkfallback($query);
                        }
                    } else {
                        $l_rowcount = $l_checkfallback($query);
                    }
                }
            } catch (Exception $e) {
                // query validation failed.
            }

            $l_preloadable_rows = isys_glob_get_pagelimit() * ((int)isys_usersettings::get('gui.lists.preload-pages', 30));

            // If we get more rows than our defined preloading allowes, we need the ajax pager.
            if ($l_preloadable_rows < $l_rowcount['count'] && !strpos($query, 'LIMIT')) {
                // First we append an offset to the report-query.
                $query = rtrim(trim($query), ';') . ' LIMIT 0, ' . $l_preloadable_rows . ';';

                // Here we prepare the URL for the ajax pagination.
                $l_ajax_url = '?ajax=1&call=report&func=ajax_pager&report_id=' . $p_id;
                $l_ajax_pager = true;

                isys_application::instance()->container->get('template')->assign('ajax_url', $l_ajax_url)
                    ->assign('preload_pages', ((int)isys_usersettings::get('gui.lists.preload-pages', 30)))
                    ->assign('max_pages', ceil($l_rowcount['count'] / isys_glob_get_pagelimit()));
            }

            $this->process_show_report($query, null, false, false, false, true, !!$l_report["isys_report__compressed_multivalue_results"], !!$l_report["isys_report__show_html"]);

            // Should we compress multivalue categorie results
            if ($l_report['isys_report__compressed_multivalue_results']) {
                // Reparse results -  actually it is unnecessary
                $results = isys_format_json::decode(isys_application::instance()->container->get('template')->get_template_vars('result'), true);
                $unsortedColumns = [];

                if (is_array($results) && !empty($results)) {
                    // Extract column names from first result row
                    $unsortedColumns = isys_format_json::encode(array_keys(reset($results)));
                }

                // Send it to template
                isys_application::instance()->container->get('template')->assign('columnNames', $unsortedColumns);
            }

            // Should we show html
            if ($l_report['isys_report__show_html']) {
                // todo implement
            }

            isys_application::instance()->container->get('template')->assign("rowcount", $l_rowcount['count'])
                ->assign("ajax_pager", $l_ajax_pager)
                ->assign("report_id", $p_id)
                ->assign("reportTitle", $l_report["isys_report__title"])
                ->assign("reportDescription", $l_report["isys_report__description"])
                ->assign("compressedMultivalueCategories", $l_report["isys_report__compressed_multivalue_results"])
                ->assign("showHtml", $l_report["isys_report__show_html"])
                ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_execute.tpl');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());

            $this->processReportList();
        }
    }

    /**
     * Process views
     *
     * @throws isys_exception_auth
     * @throws Exception
     */
    private function processViews()
    {
        global $g_absdir;

        if (!isset($_GET["reportID"]) || !class_exists($_GET["reportID"])) {
            $l_header = [
                "viewtype"    => $this->language->get("LC__CMDB__CATG__TYPE"),
                "name"        => $this->language->get("LC__UNIVERSAL__TITLE"),
                "description" => $this->language->get("LC__LANGUAGEEDIT__TABLEHEADER_DESCRIPTION")
            ];

            $l_viewdir = $g_absdir . "/src/classes/modules/report/views/";
            if (is_dir($l_viewdir) && is_readable($l_viewdir)) {
                $l_views = $this->getViews($l_viewdir);

                $l_list = new isys_component_list($l_views, null, null, null);
                $l_list->disable_dragndrop();

                $l_rowLink = isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__REPORT_REPORT_ID . "=[{class}]");
                $l_list->config($l_header, $l_rowLink);

                if ($l_list->createTempTable()) {
                    isys_application::instance()->container->get('template')->assign("objectTableList", $l_list->getTempTableHtml());
                }

                isys_application::instance()->container->get('template')->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__STANDARD_QUERIES"));
            } else {
                isys_application::instance()->container->get('notify')->error("Could not read dir: " . $l_viewdir);
            }

            isys_application::instance()->container->get('template')->assign("content_title", "Report-Views")
                ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');
        } else {
            $l_class = strtoupper($_GET['reportID']);

            isys_auth_report::instance()
                ->check(isys_auth::VIEW, 'VIEWS/' . $l_class);

            if (class_exists($l_class)) {
                /** @var isys_report_view $l_view */
                $l_view = new $l_class();

                isys_application::instance()->container->get('template')
                    ->assign('content_title', $this->language->get($l_view::name()))
                    ->assign('viewTemplate', $l_view->template())
                    ->include_template('contentbottomcontent', self::get_tpl_dir() . 'view.tpl');

                // ID-6399 Only call "init" if available.
                if (method_exists($l_view, 'init')) {
                    $l_view->init();
                }

                $l_view->start();
            } else {
                throw new Exception('Error: Report does not exist.');
            }
        }
    }

    /**
     * Shows the report list
     *
     * @throws Exception
     */
    private function processReportList()
    {
        $l_allowed_reports = isys_auth_report::instance()
            ->get_allowed_reports();

        $l_dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));
        $l_report_category = (isset($_GET['report_category'])) ? $_GET['report_category'] : null;

        if ($l_report_category !== null && !$l_dao->check_report_category($l_report_category)) {
            $l_report_category = null;
        }

        if ($l_report_category > 0) {
            isys_auth_report::instance()->reports_in_category(isys_auth::VIEW, $l_report_category);
        }

        $l_reports = $l_dao->get_reports(null, $l_allowed_reports, $l_report_category, true, true);

        $l_header = self::getReportListHeaders();

        if (isset($_GET['call']) || isset($_GET['ajax'])) {
            $l_gets = $_GET;
            unset($l_gets['call']);
            unset($l_gets['ajax']);
        } else {
            $l_gets = $_GET;
        }

        $l_rowLink = isys_glob_build_url(http_build_query($l_gets) . "&" . C__GET__REPORT_REPORT_ID . "=[{isys_report__id}]");

        $l_list = (new isys_component_list(null, $l_reports, null, null))->config($l_header, $l_dao->buildRowLinkFunction($l_rowLink), "[{isys_report__id}]")
            ->set_row_modifier($l_dao, 'modify_row');

        $tableConfig = $l_list->get_table_config();
        $tableConfig->setFilterProperty((isys_tenantsettings::get('report.list.filter') ?: 'isys_report__id'));
        $l_list->set_table_config($tableConfig);

        if ($l_list->createTempTable()) {
            isys_application::instance()->container->get('template')->assign("objectTableList", $l_list->getTempTableHtml());
        }

        isys_application::instance()->container->get('template')->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

        $l_new_overlay = [
            [
                "title"   => $this->language->get('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "icons/silk/brick_add.png",
                "href"    => "javascript:;",
                "onclick" => "document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ],
            [
                "title"   => $this->language->get("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "icons/silk/application_form_add.png",
                "href"    => "javascript:;",
                "onclick" => "$('querybuilder').value=0;document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ]
        ];

        $l_edit_overlay = [
            [
                "title"   => $this->language->get('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "icons/silk/brick_edit.png",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=1;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ],
            [
                "title"   => $this->language->get("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "icons/silk/application_form_edit.png",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=0;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ]
        ];

        // @see  ID-4035  Replace the "editor" right with "create: reports in category".
        if (is_numeric($_GET['report_category'])) {
            $l_rights_create = self::getAuth()->is_allowed_to(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/' . $_GET['report_category']);
        } else {
            $l_rights_create = self::getAuth()->is_allowed_to(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/*');
        }

        $l_rights_report_category = isys_auth_report::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');

        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote')
            ->set_overlay($l_new_overlay, C__NAVBAR_BUTTON__NEW)
            ->set_overlay($l_edit_overlay, 'edit')
            ->set_active($l_rights_create, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_rights_create, C__NAVBAR_BUTTON__NEW);

        if ($l_allowed_reports) {
            $l_navbar->append_button($this->language->get('LC__AUTH__RIGHT_EDIT'), 'edit', [
                    "icon"                => "icons/silk/page_edit.png",
                    "navmode"             => C__NAVMODE__EDIT,
                    "add_onclick_prepend" => "$('querybuilder').value='';",
                    "accesskey"           => "e"
                ]);
        }

        if ($l_rights_report_category) {
            $l_navbar->append_button($this->language->get('LC_UNIVERSAL__CATEGORIES'), 'report_category', [
                    "icon"       => "icons/silk/application_form_add.png",
                    "navmode"    => C__NAVMODE__DUPLICATE,
                    "js_onclick" => " onclick=\"get_popup('report', null, '480', '270', {'func':'show_category'});\""
                ]);
        }

        if ($l_allowed_reports) {
            $l_navbar->append_button($this->language->get('LC__NAVIGATION__NAVBAR__DUPLICATE'), 'duplicate_report', [
                    "icon"       => "icons/silk/page_copy.png",
                    "navmode"    => C__NAVMODE__DUPLICATE,
                    "js_onclick" => " onclick=\"if ($$('.mainTableHover')[0].down('input:checked')) {get_popup('report', null, '480', '260', {'func':'show_duplicate'});} else {idoit.Notify.error('" .
                        $this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED') . "', {life:10}); }\""
                ])
                ->append_button($this->language->get('LC__NAVIGATION__NAVBAR__PURGE'), 'purge', [
                    "navmode"    => C__NAVMODE__PURGE,
                    "icon"       => "icons/silk/page_delete.png",
                    "accesskey"  => "d",
                    'js_onclick' => "if (confirm('" . $this->language->get('LC__REPORT__CONFIRM_PURGE') .
                        "')) {\$('navMode').setValue(6); form_submit(null, null, null, null, null, get_listSelection4Submit());}",
                    'active'     => true,
                    'visible'    => true
                ]);
        }

        isys_application::instance()->container->get('template')
            ->assign("querybuilder", 1)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_list.tpl');
    }

    /**
     *
     */
    private function processReportBrowser()
    {
        isys_application::instance()->container->get('template')->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'report_browser.tpl');
    }

    /**
     * Shows the querybuilder
     *
     * @param int $p_reportID
     * @param int $p_report_category_id
     *
     * @throws isys_exception_database
     * @throws Exception
     */
    private function processQueryBuilder($p_reportID = null, $p_report_category_id = null)
    {
        $template = isys_application::instance()->container->get('template');

        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote')
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        $l_rules = [];

        // Add the object types to the select-box.
        $this->build_object_types();

        // States array
        $statusFilter = [
            $this->language->get('LC__CMDB__RECORD_STATUS__ALL') . ' (0)',
            $this->language->get('LC__CMDB__RECORD_STATUS__BIRTH') . ' (' . C__RECORD_STATUS__BIRTH . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__NORMAL') . ' (' . C__RECORD_STATUS__NORMAL . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__ARCHIVED') . ' (' . C__RECORD_STATUS__ARCHIVED . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__DELETED') . ' (' . C__RECORD_STATUS__DELETED . ')'
        ];

        $l_report_category_data = $this->m_dao->get_report_categories();
        $l_allowed_report_categories = isys_auth_report::instance()
            ->get_allowed_report_categories();
        $l_global_report_category_id = null;

        if ($l_allowed_report_categories === false) {
            $l_report_category_data = $this->m_dao->get_report_categories('Global', false)->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } else {
            $l_report_categories = $this->m_dao->get_report_categories($l_allowed_report_categories);
            $l_data = [];
            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    try {
                        // @see  ID-5548  Check if the user is allowed to see this category.
                        isys_auth_report::instance()->reports_in_category(isys_auth::CREATE, $l_category['isys_report_category__id']);

                        $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                    } catch (Exception $e) {
                        // Do nothing.
                    }
                }
            }
        }

        if ($p_report_category_id !== null) {
            $template->assign('category_selected', $p_report_category_id);
        } else {
            $template->assign('category_selected', $l_global_report_category_id);
        }

        $l_display_relation = 0;

        if ($p_reportID !== null) {
            $l_report = $this->m_dao->get_report($p_reportID);
            $l_querybuilder_data = isys_format_json::decode($l_report['isys_report__querybuilder_data']);
            $l_conditions = array_slice($l_querybuilder_data, 2, 1);

            $l_display_relation = $l_report['isys_report__display_relations'] ?: 0;
            $l_keep_description_format = $l_report['isys_report__keep_description_format'] ?: 0;

            $template->assign('category_selected', $l_report['isys_report__isys_report_category__id'])
                ->assign('empty_values_selected', $l_report['isys_report__empty_values'])
                ->assign('report_id', $p_reportID)
                ->assign('chk_user_specific', $l_report["isys_report__user_specific"])
                ->assign('preselection_data', isys_format_json::encode($l_querybuilder_data['main_object']))
                ->assign('preselection_lvls', isys_format_json::encode($l_querybuilder_data['lvls']))
                ->assign('default_sorting', $l_querybuilder_data['default_sorting'])
                ->assign('sorting_direction', $l_querybuilder_data['sorting_direction'])
                ->assign('report_title', $l_report['isys_report__title'])
                ->assign('report_description', $l_report['isys_report__description'])
                ->assign('querybuilder_conditions', ((count($l_conditions['conditions']) > 0) ? isys_format_json::encode($l_conditions) : null))
                ->assign('statusFilterValue', ($l_querybuilder_data['statusFilter'] ?: 0))
                ->assign("isCategoryReport", $l_report['isys_report__category_report']);

            $compressedMultivalueResults = $l_report['isys_report__compressed_multivalue_results'];
            $showHtml = $l_report['isys_report__show_html'];
        } else {
            $l_rules['report']['preselection'] = '[{"g":{"C__CATG__GLOBAL":["title"]}}]';
            $compressedMultivalueResults = 0;
            $showHtml = 0;
        }

        // Assign the title and make the save/cancel buttons invisible.
        $template->assign('category_data', $l_data)
            ->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__QUERY_BUILDER"))
            ->assign("yes_or_no", get_smarty_arr_YES_NO())
            ->assign('keep_description_format', $l_keep_description_format)
            ->assign('display_relations_selected', $l_display_relation)
            ->assign('sorting_data', [
                'ASC'  => $this->language->get('LC__CMDB__SORTING__ASC'),
                'DESC' => $this->language->get('LC__CMDB__SORTING__DESC')
            ])
            ->assign("statusFilter", $statusFilter)
            ->assign('compressed_multivalue_results', $compressedMultivalueResults)
            ->assign('show_html', $showHtml)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', $this->get_tpl_dir() . 'querybuilder.tpl');
    }

    /**
     * Method for inserting an report into the database.
     *
     * @param   boolean $p_querybuilder
     *
     * @return  mixed  boolean, null or integer
     */
    private function createReport($p_querybuilder)
    {
        $l_id = null;

        try {
            if ($p_querybuilder) {
                $l_report = new isys_report($this->buildReport());
                $l_id = $l_report->store();
            } else {
                $l_id = $this->m_dao->createReport(
                    $_POST["title"],
                    $_POST["description"],
                    $_POST["query"],
                    null,
                    false,
                    $_POST['chk_user_specific'],
                    $_POST['report_category'],
                    $_POST['compressed_multivalue_results'],
                    $_POST['show_html'],
                    $_POST['keep_description_format']
                );
            }

            // Add right
            $this->add_right($l_id, 'custom_report');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());
        }

        return $l_id;
    }

    /**
     * Method to create a new report category
     *
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @throws isys_exception_dao
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function create_category($p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title))) {
            $l_last_id = $this->m_dao->create_category(trim($p_title), $p_description, $p_sorting);

            if ($l_last_id) {
                // Add auth right.
                $this->add_right($l_last_id, 'reports_in_category');
            }

            return $l_last_id;
        }

        return false;
    }

    /**
     * Method to update an existing report category
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @throws isys_exception_dao
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function update_category($p_id, $p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title))) {
            return $this->m_dao->update_category($p_id, trim($p_title), $p_description, $p_sorting);
        }

        return false;
    }

    /**
     * Constructor method to be sure, there's a DAO instance.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->m_dao === null) {
            $this->m_dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));
        }
    }
}
