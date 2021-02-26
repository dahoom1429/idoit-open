<?php
/**
 * i-doit
 *
 * i-doit Starter
 *
 * @package     i-doit
 * @subpackage  General
 * @author      i-doit-team
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Helper\Purify;

try {
    global $g_dirs, $g_comp_database, $g_config, $g_comp_session;

    // Set default form-action.
    $_SERVER['QUERY_STRING'] = isys_helper_link::remove_params_from_url($_SERVER['QUERY_STRING'], C__GET__AJAX_CALL);

    /**
     * Show default tooltips in the navigation bar.
     *
     * @var $g_bDefaultTooltips bool
     */
    $g_bDefaultTooltips = true;

    // Initialize navbar.
    $g_comp_template_navbar = isys_component_template_navbar::getInstance()
        ->set_save_mode(isys_tenantsettings::get('cmdb.registry.quicksave', 1) ? 'quick' : 'log');

    // Global array for URL parameters which mustn't get deleted.
    $g_arSaveURLParameters = [
        "mNavID",
        "SMARTY_DEBUG",
        "bvMode"
    ];

    $getParams = Purify::purifyParams($_GET);

    /**
     * Include main navigation.
     *
     * @todo  isys_mainnavi need a place in the i-doit structure.
     * @todo  isys_mainnavi is plain and not dynamic.
     */
    if (!$g_ajax) {
        include_once $g_dirs['utils'] . 'isys_mainnavi.inc.php';
    }

    // Store status array.
    if ($_POST['cmdb_status']) {
        $_SESSION['cmdb_status'] = $_POST['cmdb_status'];

        // @see ID-6409 Save CMDB-Status filter for a user.
        if (isys_tenantsettings::get('cmdb.gui.remember-cmdb-status', false)) {
            isys_usersettings::set('cmdb.gui.mydoit-cmdb-status-0', $_POST['cmdb_status'][0] ?: 0);
            isys_usersettings::set('cmdb.gui.mydoit-cmdb-status-1', $_POST['cmdb_status'][1] ?: 0);
            isys_usersettings::set('cmdb.gui.mydoit-cmdb-status-2', $_POST['cmdb_status'][2] ?: 0);
        }
    }

    if (!isset($_SESSION['cmdb_status']) || !is_array($_SESSION['cmdb_status'])) {
        // Set default status.
        $_SESSION['cmdb_status'] = [
            0,
            0,
            0
        ];

        // @see ID-6409 Save CMDB-Status filter for a user.
        if (isys_tenantsettings::get('cmdb.gui.remember-cmdb-status', false)) {
            $_SESSION['cmdb_status'][0] = isys_usersettings::get('cmdb.gui.mydoit-cmdb-status-0', 0);
            $_SESSION['cmdb_status'][1] = isys_usersettings::get('cmdb.gui.mydoit-cmdb-status-1', 0);
            $_SESSION['cmdb_status'][2] = isys_usersettings::get('cmdb.gui.mydoit-cmdb-status-2', 0);
        }
    }

    // Write cRecStatusListView to session.
    if (isys_glob_get_param("cRecStatus")) {
        $_SESSION['cRecStatusListView'] = isys_glob_get_param("cRecStatus");
    } else {
        // Is there a value in the session?
        if (!isset($_SESSION['cRecStatusListView'])) {
            // Set default value
            $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
        } elseif ($_SESSION['cRecStatusListView'] > C__RECORD_STATUS__DELETED) {
            if ($getParams[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_CATEGORY) {
                $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
            } elseif ($getParams[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT && $_SESSION['cRecStatusListView'] != C__RECORD_STATUS__TEMPLATE) {
                $_SESSION['cRecStatusListView'] = C__RECORD_STATUS__NORMAL;
            }
        }
    }

    // CMDB-SPECIFIC - Set object-type id, if not existent in _GET parameters.
    if (empty($getParams[C__CMDB__GET__OBJECTTYPE]) && isset($getParams[C__CMDB__GET__OBJECT])) {
        if (class_exists("isys_cmdb_dao")) {
            $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

            $_GET[C__CMDB__GET__OBJECTTYPE] = $l_dao_cmdb->get_objTypeID($getParams[C__CMDB__GET__OBJECT]);
            unset($l_dao_cmdb);
        }
    }

    /**
     * Include Applicaiton controllerp
     */
    include_once('application.inc.php');
} catch (isys_exception_general $e) {
    isys_glob_display_error($e->getMessage());
    die();
}
