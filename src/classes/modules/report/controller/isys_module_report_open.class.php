<?php

/**
 * i-doit
 *
 * i-doit Report Manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Bluemer <dbluemer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report_open extends isys_module_report
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
     * @var string
     */
    private $m_report_browser_www = "https://reports-ng.i-doit.org/";

    /**
     * @var
     */
    protected $m_template;

    /**
     * Enhances the breadcrumb navigation.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function breadcrumb_get(&$p_gets)
    {
        if (!defined('C__MODULE__REPORT')) {
            return [];
        }
        $l_result = [];

        switch ($p_gets[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__STANDARD_REPORTS:
                $l_title = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__STANDARD_QUERIES');
                break;
            case C__REPORT_PAGE__REPORT_BROWSER:
                $l_title = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            default:
                return null;
                break;
        }

        $l_result[] = [
            $l_title => [
                C__GET__MODULE_ID   => C__MODULE__REPORT,
                C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
            ]
        ];

        return $l_result;
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
        if (!defined('C__MODULE__REPORT')) {
            return;
        }
        $l_parent = -1;
        $l_submodule = '';

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__REPORT;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__REPORT . '0', $l_parent, 'Report Manager');
        }

        $p_tree->add_node(
            C__MODULE__REPORT . '1',
            $l_root,
            isys_application::instance()->container->get('language')
            ->get('LC__REPORT__MAINNAV__STANDARD_QUERIES'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '1' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__STANDARD_REPORTS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID']
        );

        $p_tree->add_node(
            C__MODULE__REPORT . '2',
            $l_root,
            isys_application::instance()->container->get('language')
            ->get('LC__REPORT__MAINNAV__QUERY_BROWSER'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__REPORT_BROWSER . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID']
        );
    }

    /**
     * Start module Nagios.
     *
     * @author  Dennis Bluemer <dbluemer@synetics.de>
     */
    public function start()
    {
        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call")) {
            $this->processAjaxRequest();
            die;
        }

        $this->m_template = &isys_application::instance()->template;

        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        // Is the tree part of the system menu?
        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            // Handle the tree.
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();
            $this->build_tree($l_tree, false);
            $this->m_template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        }

        switch ($l_gets[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__REPORT_BROWSER:
                $this->processReportBrowser();
                break;

            default:
            case C__REPORT_PAGE__STANDARD_REPORTS:
                if (isset($l_gets['reportID'])) {
                    $this->showReport($l_gets['reportID']);
                } else {
                    $this->processStandardReportList();
                }
                break;
        }
    }

    /**
     * Method for preparing the report-data to view it properly with the TabOrder.
     * This is used by the ajax handler for the online reports and the normal reports.
     *
     * @param   string  $l_query
     * @param   null    $deprecated
     * @param   boolean $p_ajax_request
     *
     * @return  mixed  If this method is called by an ajax request, it returns an array. If not, null.
     * @since   0.9.9-9
     * @author  Leonard Fischer <lfischer@synetic.de>
     */
    private function process_show_report($l_query, $deprecated = null, $p_ajax_request = false)
    {
        global $g_comp_database;

        $l_dao = isys_report_dao::instance(isys_application::instance()->database_system);

        $l_result = $l_dao->query($l_query);

        $l_json = [];

        // This is necessary because of UTF8 and JSON complications.
        if ($l_result['grouped']) {
            foreach ($l_result['content'] as $l_groupname => $l_group) {
                $l_tmp = [];

                foreach ($l_group as $l_data) {
                    $l_tmp2 = [];

                    // With this code, we can set the ID at the first place of the table.
                    if (isset($l_data['__id__'])) {
                        $l_tmp2['__id__'] = $l_data['__id__'];
                    }

                    foreach ($l_data as $l_key => $l_value) {
                        if (in_array($l_key, $l_result['headers'])) {
                            // The whitespace at the end fixes #3667.
                            $l_tmp2[$l_key] = htmlspecialchars(isys_application::instance()->container->get('language')
                                    ->get($l_value), ENT_QUOTES) . '&nbsp;';
                        }
                    }

                    $l_tmp[] = $l_tmp2;
                }

                $l_json[isys_application::instance()->container->get('language')
                    ->get($l_groupname)] = isys_format_json::encode($l_tmp);
            }
        } else {
            foreach ($l_result['content'] as $l_data) {
                $l_tmp = [];

                // With this code, we can set the ID at the first place of the table.
                if (isset($l_data['__id__'])) {
                    $l_tmp['__id__'] = $l_data['__id__'];
                }

                foreach ($l_data as $l_key => $l_value) {
                    if (in_array($l_key, $l_result['headers'])) {
                        // The whitespace at the end fixes #3667.
                        $l_tmp[$l_key] = htmlspecialchars(isys_application::instance()->container->get('language')
                                ->get($l_value), ENT_QUOTES) . '&nbsp;';
                    }
                }

                $l_json[] = $l_tmp;
            }

            $l_json = isys_format_json::encode($l_json);
        }

        if ($p_ajax_request) {
            return [
                $l_result,
                $l_json
            ];
        }

        $this->m_template->assign("listing", $l_result)
            ->assign("result", $l_json);
    }

    /**
     * Method for processing the ajax-request.
     */
    private function processAjaxRequest()
    {
        global $g_comp_session;

        $g_comp_session->write_close();

        switch (isys_glob_get_param("request")) {
            case "executeReport":
                $l_query = stripslashes($_POST["query"]);

                try {
                    $this->process_show_report($l_query);

                    $this->m_template->assign("lm", isys_application::instance()->container->get('language'))
                        ->assign("report_id", $_POST["reportID"])
                        ->assign("reportTitle", $_POST["title"])
                        ->assign("reportDescription", $_POST["desc"])
                        ->display("modules/reports/report_result.tpl");
                } catch (Exception $e) {
                    isys_glob_display_error($e->getMessage());
                }

                break;
        }
    }

    /**
     * Method for displaying the online-report browser.
     */
    private function processReportBrowser()
    {
        global $index_includes;

        $this->m_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = "modules/reports/report_browser.tpl";
    }

    /**
     * Method for constructing and displaying the report-list.
     */
    private function processStandardReportList()
    {
        global $index_includes;

        $l_reports = isys_report_dao::instance(isys_application::instance()->database_system)
            ->get_reports(null, null, null, true, true);

        $l_header = [
            "isys_report__id"    => "ID",
            "isys_report__title" => "LC__UNIVERSAL__TITLE"
        ];
        $l_list = new isys_component_list(null, $l_reports, null, null);

        $l_rowLink = isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__REPORT_REPORT_ID . "=[{isys_report__id}]");
        $l_list->config($l_header, $l_rowLink, "[{isys_report__id}]");

        if ($l_list->createTempTable()) {
            $this->m_template->assign("objectTableList", $l_list->getTempTableHtml());
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__PURGE);
        $this->m_template->assign("content_title", isys_application::instance()->container->get('language')
            ->get("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";
    }

    /**
     * Method for displaying a report.
     *
     * @global  isys_component_database $g_comp_database_system
     * @global  isys_component_database $g_comp_database
     * @global  array                   $index_includes
     *
     * @param   integer                 $p_id
     */
    private function showReport($p_id)
    {
        global $index_includes;

        $l_report = isys_report_dao::instance(isys_application::instance()->database_system)
            ->get_report($p_id);

        $query = isys_cmdb_dao_category_property::instance(isys_application::instance()->container->get('database'))
                ->prepareEnvironmentForReportById($p_id)
                ->create_property_query_for_report() . '';

        try {
            $l_ajax_pager = 'false';

            // We use this DAO because here we defined how many pages we want to preload.
            $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);

            // First we modify the SQL to find out, with how many rows we are dealing...
            $l_rowcount_sql = 'SELECT COUNT(*) as count ' . substr($query, strpos($query, 'FROM'));

            try {
                $l_num_rows = $l_dao->retrieve($l_rowcount_sql)
                    ->num_rows();

                if ($l_num_rows == 1) {
                    $l_rowcount = $l_dao->retrieve($l_rowcount_sql)
                        ->get_row();
                } else {
                    $l_rowcount['count'] = $l_num_rows;
                }
            } catch (isys_exception_database $e) {
                // If our first try fails because we broke the SQL, we use this here...
                $l_rowcount = [
                    'count' => $l_dao->retrieve($query)
                        ->num_rows()
                ];
            }

            $l_preloadable_rows = isys_glob_get_pagelimit() * ((int)isys_usersettings::get('gui.lists.preload-pages', 30));

            // If we get more rows than our defined preloading allowes, we need the ajax pager.
            if ($l_preloadable_rows < $l_rowcount['count'] && !strpos($query, 'LIMIT')) {
                // First we append an offset to the report-query.
                $query = rtrim($query, ';') . ' LIMIT 0, ' . $l_preloadable_rows . ';';

                // Here we prepare the URL for the ajax pagination.
                $l_ajax_url = '?ajax=1&call=report&func=ajax_pager&report_id=' . $p_id;
                $l_ajax_pager = 'true';

                $this->m_template->assign('ajax_url', $l_ajax_url)
                    ->assign('preload_pages', ((int)isys_usersettings::get('gui.lists.preload-pages', 30)))
                    ->assign('max_pages', ceil($l_rowcount['count'] / isys_glob_get_pagelimit()));
            }

            $this->process_show_report($query);

            $this->m_template->assign("rowcount", $l_rowcount['count'])
                ->assign("ajax_pager", $l_ajax_pager)
                ->assign("report_id", $p_id)
                ->assign("reportTitle", $l_report["isys_report__title"])
                ->assign("reportDescription", $l_report["isys_report__description"]);

            $index_includes['contentbottomcontent'] = "modules/reports/report_execute.tpl";
        } catch (Exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());

            $this->processStandardReportList();
        }
    }

    /**
     * Constructor method to be sure, there's a DAO instance.
     */
    public function __construct()
    {
        if ($this->m_dao === null) {
            $this->m_dao = isys_report_dao::instance(isys_application::instance()->database_system);
        }
    }
}
