<?php

/**
 * i-doit
 * Hypergate
 * Responsible for login, logout and general tasks
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
global $g_template;

$session = isys_application::instance()->container->get('session');
$template = isys_application::instance()->container->get('template');

// Login procedure.
if (!$session->is_logged_in()) {
    if (isset($_POST['login_username'])) {
        include_once('login.inc.php');
    } elseif (isys_tenantsettings::get('session.sso.active', false) && isys_settings::get('session.sso.mandator-id', '1') > 0 &&
        ((isset($_SERVER['REDIRECT_REMOTE_USER']) && $_SERVER['REDIRECT_REMOTE_USER'] != '') || (isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] != ''))) {
        include_once('sso.inc.php');
    }
}

// Logout.
if (isset($_GET['logout']) && $session->is_logged_in()) {
    $session->logout();

    // @see ID-3202
    header('Location: ?');
}

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * SHOW LOGIN PAGE IF NOT LOGGED IN
 * --------------------------------------------------------------------------------------------------------------------------
 */

// If not logged in, show login dialog, otherwise forward to main include (i-doit.inc.php).
if (!$session->is_logged_in()) {
    if (!isys_tenantsettings::get('system.devmode', false)) {
        global $g_product_info;

        // Check for i-doit code / database version conflicts.
        $g_idoit = new isys_component_dao_idoit(isys_application::instance()->container->get('database_system'));
        $l_db_version = $g_idoit->get_version();

        if ($_GET['load'] !== 'update' && $l_db_version !== '' && $l_db_version != $g_product_info['version'] && !isset($_POST['login_submit'])) {
            global $g_config;

            isys_glob_display_error(
                "The version of your i-doit database does not match the version of your program code. " .
                "Please update your databases to <strong>i-doit " . $g_product_info["version"] . "</strong> using the <a href=\"" . $g_config['www_dir'] .
                "updates\">updater</a> or revert/update your i-doit source code to version " . $l_db_version . ".<br /><br />System Database Version: " . $l_db_version .
                "<br />Source Code Version: " . $g_product_info["version"]
            );
            die;
        }
    }

    // Check for session timeouts.
    if ($_SESSION['session_data']['isys_user_session__isys_obj__id'] > 0 && empty($l_error) || isset($_GET['timeout'])) {
        $l_login_header = 'i-doit session manager';
        $l_error = 'Your session timed out!<br />Login again, please.';
    }

    // User is not logged in.
    $template
        ->assign('csrf_value', (new \Symfony\Component\Security\Csrf\CsrfTokenManager())->getToken('i-doitCSRFTokenID')->getValue())
        ->assign('showAdminCenterLink', $GLOBALS['g_admin_auth']['admin']);

    if (isset($l_error)) {
        // Destroy session, because the login attempt failed, or session timed out.
        // isys_application::instance()->session->destroy();

        if ($l_login_header) {
            $template->assign('login_header', $l_login_header);
        }

        $template
            ->assign('login_error', str_replace("'", "\'", $l_error))
            ->display($g_template['start_page']); // Display error.

        die;
    }

    $g_template['start_page'] = 'main.tpl';

    $index_includes = ['contentarea' => 'content/login.tpl'];
} else {
    /**
     * --------------------------------------------------------------------------------------------------------------------------
     * USER IS LOGGED IN
     * --------------------------------------------------------------------------------------------------------------------------
     */

    if (isset($_GET[C__CMDB__GET__TENANT])) {
        try {
            $session = isys_application::instance()->container->get('session');
        } catch (Exception $e) {
            global $g_comp_session;
            $session = $g_comp_session;
        }
        $currentTenantId = (int)($session->get_mandator_id());
        $requiredTenantId = (int)$_GET[C__CMDB__GET__TENANT];
        if ($currentTenantId !== $requiredTenantId) {
            $session->change_mandator($requiredTenantId);
            header('Location: ' . isys_helper_link::create_url($_GET, true));
            die;
        }
    }

    /* Restore mandator id on failure */
    if (!isset($_SESSION['user_mandator'])) {
        /* If there is no user mandator saved in users session, it could be
           possible that it was unsetted. The
           mandator-ID is restored here. :-) */
        $l_mandator = $session->get_mandator_id();

        if ($l_mandator != null) {
            $_SESSION['user_mandator'] = $l_mandator;
        }
    } else {
        global $g_dirs, $g_absdir;

        /* User is not logged in. Do some directory checks: */
        $g_cache_dirs = [
            'temp'         => isys_glob_get_temp_dir(),
            'file upload'  => $g_dirs['fileman']['target_dir'],
            'font upload'  => $g_dirs['fileman']['font_dir'],
            'image upload' => $g_dirs['fileman']['image_dir']
        ];

        $g_not_writable = [];
        foreach ($g_cache_dirs as $l_dir) {
            if ($l_dir && file_exists($l_dir) && !is_writable($l_dir)) {
                $g_not_writable[] = $l_dir;
            }
        }

        if (count($g_not_writable) > 0) {
            isys_glob_display_error(
                'Temp/Cache Problem: The apache process is not able to write inside the following temporary i-doit directories: <br /><br />' .
                implode(',<br />', $g_not_writable) . '<br /><br />' .
                'Please provide the appropriate permissions (e.g. "chmod 775 path").<br /><br />' .
                '<button onclick="location.reload(true);">Refresh</button>'
            );
            die;
        }
    }

    /**
     * --------------------------------------------------------------------------------------------------------------------------
     * HANDLE SESSION BASED STUFF
     *  - Checks if a user is logged in and a mandator id is set in session
     * --------------------------------------------------------------------------------------------------------------------------
     */
    if (isset($_SESSION['user_mandator']) && $session->is_logged_in()) {
        // Assign current mandant name.
        $g_mandator_name = isys_glob_get_mandant_name_as_string($_SESSION['user_mandator']);

        $session->start_dbsession();

        // Load update engine.
        if (isset($_GET['load']) && $_GET['load'] === 'update') {
            global $g_absdir;
            include_once $g_absdir . '/src/template.inc.php';
            include_once $g_absdir . '/updates/update.inc.php';
            die;
        }
    }

    // Handle nag screen.
    if (class_exists('isys_module_licence')) {
        isys_module_licence::show_nag_screen();
    }

    if (!$session->get_session_id()) {
        $g_sessionid = $session->get_session_id();
    }

    // Read session data.
    $_SESSION['session_data'] = $session->get_session_data();

    if (is_array($_SESSION['session_data'])) {
        foreach ($_SESSION['session_data'] as $l_key => $l_val) {
            if (is_numeric($l_key)) {
                unset($_SESSION['session_data'][$l_key]);
            }
        }
    }

    // Load Event manager.
    $g_mod_event_manager = isys_event_manager::getInstance();

    // Assign navbar template.
    $index_includes['navbar'] = 'content/navbar/main.tpl';

    // User is logged in.
    include_once __DIR__ . '/i-doit.inc.php';

    // Show navbar.
    isys_component_template_navbar::getInstance()
        ->show_navbar();

    // Assign the collected data.
    global $g_mandator_name;

    $template
        ->assign('g_mandant_name', $g_mandator_name)
        ->assign('infobox', isys_component_template_infobox::instance())
        ->assign('menu_width', isys_usersettings::get('gui.leftcontent.width', isys_component_dao_user::C__CMDB__TREE_MENU_WIDTH));
}

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * INITIALIZE SOME TEMPLATE VARIABLES
 * --------------------------------------------------------------------------------------------------------------------------
 */
include_once __DIR__ . '/template.inc.php';

/**
 * --------------------------------------------------------------------------------------------------------------------------
 * PRINT OUT THE I-DOIT SITE
 * --------------------------------------------------------------------------------------------------------------------------
 */
if (!$g_output_done) {
    if (empty($g_dirs['smarty'])) {
        isys_glob_display_error('Error while displaying template: g_dirs[smarty] is empty. This could be a settings or cache problem');
    }

    if (empty($g_template['start_page'])) {
        isys_glob_display_error('Error while displaying template: g_template[start_page] is not set!');
    }

    if (!file_exists($g_dirs['smarty'] . 'templates/' . $g_template['start_page'])) {
        isys_glob_display_error('Error: Template ' . $g_dirs['smarty'] . 'templates/' . $g_template['start_page'] . ' does not exist.');
    }

    $template->display($g_dirs['smarty'] . 'templates/' . $g_template['start_page']);

    // Emit signal afterRender.
    isys_component_signalcollection::get_instance()->emit('system.gui.afterRender');
}
