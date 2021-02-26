<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * i-doit Report Manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report extends isys_module implements isys_module_authable, AuthableInterface
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @var isys_module_report_pro
     */
    private static $m_instance = null;

    /**
     * @var isys_report_dao
     */
    protected $m_dao;

    /**
     * Template location
     *
     * @var null
     */
    protected $m_tpl = null;

    /**
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_tpl_dir()
    {
        $l_dir = __DIR__ . '/templates';

        if (!is_dir($l_dir)) {
            return false;
        }

        return $l_dir . DS;
    }

    /**
     * Static get instance method.
     *
     * @return  mixed  Either isys_module_report_pro or isys_module_report_open.
     */
    public static function get_instance()
    {
        if (!is_object(self::$m_instance)) {
            if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE) {
                self::$m_instance = new isys_module_report_pro();
            } else {
                self::$m_instance = new isys_module_report_open();
            }
        }

        return self::$m_instance;
    }

    /**
     * @param   isys_module_request & $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    }

    /**
     * Enhances the breadcrumb navigation.
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function breadcrumb_get(&$p_gets)
    {
        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE) {
            $l_report = new isys_module_report_pro();
        } else {
            $l_report = new isys_module_report_open();
        }

        if (method_exists($l_report, 'breadcrumb_get')) {
            return $l_report->breadcrumb_get($_GET);
        } else {
            return null;
        }
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree & $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE) {
            $l_report = new isys_module_report_pro();
        } else {
            $l_report = new isys_module_report_open();
        }

        $l_report->build_tree($p_tree, $p_system_module, $p_parent);
    }

    /**
     * Method for retrieving a bookmark string (mydoit).
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @version Van Quyen Hoang <qhoang@i-doit.org>
     * @since   0.9.9-9
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_link_options = [
            C__GET__MODULE_ID        => defined_or_default('C__MODULE__REPORT'),
            C__GET__REPORT_PAGE      => $_GET[C__GET__REPORT_PAGE],
            C__GET__REPORT_REPORT_ID => $_GET[C__GET__REPORT_REPORT_ID],
            C__GET__TREE_NODE        => $_GET[C__GET__TREE_NODE],
            'report_category'        => $_GET['report_category']
        ];

        $p_text[] = 'Report Manager';
        switch ($_GET[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__REPORT_BROWSER:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                if ($_GET['report_category'] > 0) {
                    $p_text[] = $this->m_dao->get_report_categories($_GET['report_category'], false)
                        ->get_row_value('isys_report_category__title');
                } else {
                    $p_text[] = isys_application::instance()->container->get('language')
                        ->get('LC__REPORT__MAINNAV__CUSTOM_QUERIES');
                }
                break;
            case C__REPORT_PAGE__QUERY_BUILDER:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__QUERY_BUILDER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $p_text[] = 'Views';
                break;
            case C__REPORT_PAGE__STANDARD_REPORTS:
            default:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__STANDARD_QUERIES');
                break;
        }

        if (isset($_GET[C__GET__REPORT_REPORT_ID])) {
            $l_row = $this->m_dao->get_report($_GET[C__GET__REPORT_REPORT_ID]);
            $p_text[] = $l_row['isys_report__title'];
        }

        // Define the favorite-link.
        $p_link = isys_glob_http_build_query($l_link_options);

        return true;
    }

    /**
     * Start-method.
     *
     * @return  isys_module_report_open
     */
    public function start()
    {
        if (defined("C__ENABLE__LICENCE") && C__ENABLE__LICENCE) {
            $l_report = new isys_module_report_pro();
        } else {
            $l_report = new isys_module_report_open();
        }

        $this->m_dao = isys_report_dao::instance(isys_application::instance()->database_system);

        $l_report->start();

        return $l_report;
    }

    /**
     * @deprecated
     */
    public static function get_auth()
    {
        return self::getAuth();
    }

    /**
     * @return isys_auth_report
     */
    public static function getAuth()
    {
        return isys_auth_report::instance();
    }
}
