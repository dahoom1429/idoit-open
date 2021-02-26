<?php

/**
 * The new update procedure!
 *
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/*
 * #######################################################################
 * ###########           Update bootstrapping START!           ###########
 * #######################################################################
 */

// Defining some necessary constants.
define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");
define("C__CHANGELOG", "CHANGELOG");
define("C__DIR__FILES", "files/");
define("C__DIR__MIGRATION", "migration/");
define("C__URL__PRO", "?req=portal");
define("C__URL__OPEN", "http://www.i-doit.org");

// Setting some session variables.
$_SESSION["error"] = 0;

/* Increase session time while updating */
isys_component_session::instance()
    ->set_session_time(999999999);

$l_required_php_version = '5.6.0';
$l_php_version = phpversion();

if (version_compare($l_php_version, $l_required_php_version, '<') == -1) {
    throw new isys_exception_general('You have PHP ' . $l_php_version . '. For updating i-doit to a newer version you need at least PHP ' . $l_required_php_version . '.');
}

$g_modman = isys_module_manager::instance()
    ->init(isys_module_request::get_instance());

if (intval(ini_get('display_errors')) != 1) {
    ini_set("display_errors", "1");
}

if (intval(ini_get('memory_limit')) < 512) {
    ini_set("memory_limit", "512M");
}

set_time_limit(0);

$l_updatedir = $g_absdir . DS . 'updates' . DS;

include_once $l_updatedir . "classes/isys_update.class.php";

$l_fh = opendir($l_updatedir . "classes");

while ($l_file = readdir($l_fh)) {
    if (strpos($l_file, ".") !== 0 && !include_once($l_updatedir . "classes/" . $l_file)) {
        throw new isys_exception_filesystem("Could not load " . $l_updatedir . $l_file, 'In file "' . __FILE__ . '" on line ' . __LINE__);
    }
}

/*
 * #######################################################################
 * ###########            Update bootstrapping END!            ###########
 * #######################################################################
 */

// This will help us to determine the current step.
$l_current_step = $_GET['step'] ?: 1;
$l_update_gui = new isys_update_gui($l_current_step);

if (!is_array($_SESSION['update_data'])) {
    $_SESSION['update_data'] = [];
}

// Here we take the data from the frontend, format it and save it to the user session.
$l_formdata = isys_format_json::decode($_POST['current_formdata'] ?: 'null');

if (is_array($l_formdata)) {
    foreach ($l_formdata as $l_step => $l_data) {
        // Here we simply format the GET-string to an PHP array.
        parse_str($l_data, $l_data);

        // We always overwrite the session data with the "fresh" formdata.
        $_SESSION['update_data'][$l_step] = $l_data;
    }
}

$l_template = isys_component_template::instance();

$l_template->assign('steps', $l_update_gui->get_steps())
    ->assign('formdata', $_SESSION['update_data'])
    ->assign('formdata_json', isys_format_json::encode($_SESSION['update_data']))
    ->assign('current_formdata', $_SESSION['update_data']['step-' . $l_current_step] ?: [])
    ->assign('current_formdata_json', isys_format_json::encode($_SESSION['update_data']['step-' . $l_current_step] ?: []))
    ->assign('current_step', $l_update_gui->current_step());

// Check on AJAX request.
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');

    $return = [
        'success' => true,
        'data'    => null,
        'message' => null
    ];

    try {
        $return['data'] = $l_update_gui->process_current_step();
    } catch (Exception $e) {
        $return['success'] = false;
        $return['message'] = $e->getMessage();
    }

    echo isys_format_json::encode($return);
    die;
} else {
    /*
     * !! Attention !!
     * The method "set_step(1)" will force the user to do the update via Ajax.
     * If we remove this, you can navigate all the update-steps via URL (".../admin/?req=update&step=123") - this may be a feature, but it should not be done
     * because crucial data (like "which update?" or "which mandators?") should not be guessed.
     * - Leo
     */
    $l_template->assign('update_content', $l_update_gui->set_step(1)
        ->process_current_step());
}

/**
 * Class isys_update_gui which inherits the logic for every single update step.
 *
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_update_gui
{
    /**
     * This variable will hold the current update-step.
     *
     * @var  integer
     */
    protected $m_current_step = 1;

    /**
     * This variable will hold all necessary directory paths.
     *
     * @var  array
     */
    protected $m_dirs = [];

    /**
     * This variable can be used to return raw data instead of a rendered template.
     *
     * @var  boolean
     */
    protected $m_raw_response = false;

    /**
     * This array contains all update-steps.
     *
     * @var  array
     */
    protected $m_steps = [
        1 => [
            'title'  => 'i-doit update',
            'tpl'    => 'intro.tpl',
            'method' => 'init'
        ],
        2 => [
            'title'  => 'Available updates',
            'tpl'    => 'available_updates.tpl',
            'method' => 'available_updates'
        ],
        3 => [
            'title'  => 'Database(s)',
            'tpl'    => 'databases.tpl',
            'method' => 'databases'
        ],
        4 => [
            'title'  => 'File-update',
            'tpl'    => 'file_update.tpl',
            'method' => 'file_update'
        ],
        5 => [
            'title'  => 'Overview (Log)',
            'tpl'    => 'overview.tpl',
            'method' => 'overview'
        ],
        6 => [
            'title'  => 'Migration',
            'tpl'    => 'migration.tpl',
            'method' => 'migration'
        ],
        7 => [
            'title'  => 'Property migration',
            'tpl'    => 'property_migration.tpl',
            'method' => 'property_migration'
        ],
        8 => [
            'title'  => 'Completion',
            'tpl'    => 'completion.tpl',
            'method' => 'completion'
        ]
    ];

    /**
     * This variable will hold the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl = null;

    /**
     * This variable will hold an instance of isys_update.
     *
     * @var  isys_update
     */
    protected $m_update = null;

    /**
     * Returns all the update steps.
     *
     * @param   integer $p_step
     *
     * @return  isys_update_gui
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_step($p_step = 1)
    {
        $this->m_current_step = (int)$p_step;

        return $this;
    }

    /**
     * Returns all the update steps.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_steps()
    {
        return $this->m_steps;
    }

    /**
     * Returns the current step.
     *
     * @return  integer
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function current_step()
    {
        return $this->m_current_step;
    }

    /**
     * This method will switch to the next step and return boolean false, if the last step is reached.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function next_step()
    {
        $this->m_current_step++;

        if (!isset($this->m_steps[$this->m_current_step])) {
            return false;
        }

        return true;
    }

    /**
     * This method will call the current steps method and return its template or raw data.
     *
     * @return  string  The rendered template.
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process_current_step()
    {
        $l_step = $this->m_steps[$this->m_current_step];

        // Call the responsible function.
        if (isset($l_step['method']) && method_exists($this, $l_step['method'])) {
            $l_raw = call_user_func([
                $this,
                $l_step['method']
            ]);

            if ($this->m_raw_response) {
                return $l_raw;
            }
        }

        // Output the responsible template.
        return $this->m_tpl->fetch($l_step['tpl'], null, null, null, false);
    }

    /**
     * The "init" method is the first step - This will display the first screen and some information.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init()
    {
        // Retrieve the OS name and version.
        $l_os = [
            "name"    => php_uname("s"),
            "version" => php_uname("r") . " " . php_uname("v")
        ];

        $this->m_tpl->assign('os', $l_os)
            ->assign('info', $this->m_update->get_isys_info());
    }

    /**
     * The "available_updates" method is the second step - Here we will select the update package or download a current one.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function available_updates()
    {
        // Check for updates on i-doit.org.
        if ($_POST['check_update'] == 1) {
            $this->m_raw_response = true;

            return $this->check_for_update();
        }

        // Download and unzip an external file.
        if ($_POST['process_download'] == 1) {
            $this->m_raw_response = true;

            return $this->process_download($_POST['process_download_url']);
        }

        // Download and unzip an external file.
        if ($_POST['list_versions'] == 1) {
            $this->m_raw_response = true;

            return $this->process_download($_POST['process_download_url']);
        }

        // Assign curresponding url for update notices.
        if (C__ENABLE__LICENCE) {
            $l_site = C__URL__PRO;

            if (isset($_SESSION['licenced']) && $_SESSION['licenced'] === false) {
                $this->m_tpl->assign('licence_error', 'Error. Your licence does not allow any updates');
            }
        } else {
            $l_site = C__URL__OPEN;
        }

        // Assign the necessary variables.
        $this->m_tpl->assign('site_url', $l_site)
            ->assign('ajax_url', '?req=update&step=' . $this->current_step())
            ->assign('updates', isys_format_json::encode($this->m_update->get_available_updates($this->m_dirs['versions'])));
    }

    /**
     * The "available_updates" method is the third step - Here we will select the update package or download a current one.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function databases()
    {
        global $g_comp_database_system;

        $this->m_tpl->assign("databases", $this->m_update->get_databases())
            ->assign("g_system_database", $g_comp_database_system->get_db_name())
            ->assign("sql_mode", $g_comp_database_system->get_strictmode());
    }

    /**
     * The "file_update" method is the fourth step - Here we just display all files which will be overwritten.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function file_update()
    {
        $l_writeable = true;
        $l_files = 'No Files available for this update!';
        $l_files_count = 0;

        if (!is_writable($this->m_dirs['absolute'])) {
            $l_writeable = false;
        }

        if (is_dir($this->m_dirs['current_update_files'])) {
            $l_files = new isys_update_files($this->m_dirs['current_update_files']);

            /** @var $l_files_array RecursiveIteratorIterator */
            $l_files_array = $l_files->getdir();

            $l_files = '';
            $l_files_count = 0;
            $l_filepath_length = strlen($this->m_dirs['current_update_files']);

            if ($l_files_array instanceof RecursiveIteratorIterator) {
                foreach ($l_files_array as $l_current_file) {
                    // We remove the whole "/var/www/i-doit/updates/..." / "C:\xampp\htdocs\..." stuff.
                    $l_files .= '[+] ' . substr($l_current_file, $l_filepath_length) . PHP_EOL;
                    $l_files_count++;
                }
            }
        }

        $this->m_tpl->assign('writable', $l_writeable)
            ->assign('files', $l_files)
            ->assign('files_count', $l_files_count);
    }

    /**
     * The "overview" method is the fifth step - Here display the results of the update.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function overview()
    {
        $l_system_db = ($_SESSION['update_data']['step-3']['system_database'] ?: null);
        $l_tenant_dbs = [];

        if (is_array($_SESSION['update_data']['step-3'])) {
            foreach ($_SESSION['update_data']['step-3'] as $l_key => $l_tenant) {
                if (strpos($l_key, 'mandator_') !== false) {
                    $l_tenant_dbs[] = $l_tenant;
                }
            }
        }

        if (empty($l_system_db) && empty($l_tenant_dbs)) {
            throw new isys_exception_general('No databases were selected!');
        } else {
            $this->m_update->update($l_system_db, $l_tenant_dbs);
        }

        // Write debug log.
        $l_log = isys_update_log::get_instance();

        $this->m_tpl->assign('log', $l_log->get());

        $l_log->write_debug();
    }

    /**
     * The "migration" method is the sixth step - Here display the results of the migration.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function migration()
    {
        global $g_comp_database;

        // Once again, load the databases (I'm not sure but it seems as if they go missing from time to time).
        $this->m_update->get_databases();

        $l_migration = new isys_update_migration();
        $l_migration_log = [];
        $l_logged_data = false;

        try {
            if (is_array($_SESSION['update_data']['step-3'])) {
                $l_mig_log = isys_log_migration::get_instance()
                    ->set_log_file($this->m_dirs['temp'] . 'TODO GIVE ME A NAME')
                    ->set_log_level(isys_log::C__ALL);

                foreach ($_SESSION['update_data']['step-3'] as $l_key => $l_tenant) {
                    if (strpos($l_key, 'mandator_') !== false) {
                        $g_comp_database = $this->m_update->get_database($l_tenant);
                        $l_migration_log[$l_tenant] = $l_migration->migrate($this->m_dirs['current_update'] . DS . C__DIR__MIGRATION);

                        if (!empty($l_migration_log[$l_tenant])) {
                            $l_logged_data = true;
                        }
                    }
                }

                unset($l_mig_log);
            }
        } catch (Exception $e) {
            isys_update_log::get_instance()
                ->add($e->getMessage(), C__MESSAGE, "bold red indent");
        }

        if (!$l_logged_data) {
            $l_version = $this->m_update->get_isys_info();

            $l_migration_log = [$l_version['name'] => ['No migration code needed this time.']];
        }

        $this->m_tpl->assign("migration_log", $l_migration_log);
    }

    /**
     * The "property_migration" method is the seventh step - Here display the results of the property migration.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function property_migration()
    {
        // Once again, load the databases (I'm not sure but it seems as if they go missing from time to time).
        $this->m_update->get_databases();

        $l_migration = new isys_update_property_migration();
        $l_log = isys_update_log::get_instance();
        $l_result = [];

        try {
            isys_log_migration::get_instance()
                ->set_log_file($this->m_dirs['log'] . 'prop_' . date("Y-m-d_H-i-s") . '_idoit_migration.log')
                ->set_log_level(isys_log::C__ALL);

            foreach ($_SESSION['update_data']['step-3'] as $l_key => $l_tenant) {
                if (strpos($l_key, 'mandator_') !== false) {
                    $l_result[$l_tenant] = $l_migration->set_database($this->m_update->get_database($l_tenant))
                        ->reset_property_table()
                        ->collect_category_data()
                        ->prepare_sql_queries('g')
                        ->prepare_sql_queries('s')
                        ->execute_sql()
                        ->get_results();
                }
            }

            $this->m_tpl->assign('result', array_keys($l_result));
        } catch (Exception $e) {
            $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
        }
    }

    /**
     * The "completion" method is the eighth and last step - Here display the results of the update and wish a nice day.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function completion()
    {
        global $g_config;

        if ($_SESSION['error'] >= 1) {
            $l_message = '<h3 color="red">Error!</h3>' . '<p>There are <strong>' . $_SESSION['error'] .
                '</strong> errors occurred. Your i-doit could run unstable now.<br />Visit our support forum at <a href="http://www.i-doit.org/" target="_blank">http://www.i-doit.org/</a> for any help.</p>' .
                '<p>Detailed debug information can be found in the update log located at "' . $this->m_dirs['temp'] . '" on your i-doit web-server.</p>';
        } else {
            $l_message = '<h3>Congratulations!</h3>' . '<p>Your i-doit installation has been successfully updated to a newer version.</p>' .
                '<p>Detailed debug information can be found at "' . $this->m_dirs['temp'] . '" on your i-doit web-server.</p>';
        }

        $l_version = $this->m_update->get_isys_info();

        // Reset the session!
        $_SESSION['update_data'] = [];

        $this->m_tpl->assign("idoit_url", rtrim(rtrim($g_config['www_dir'], '/'), 'admin'))
            ->assign("idoit_title", $l_version['name'])
            ->assign("message", $l_message);
    }

    /**
     * This method will check online for the latest i-doit update.
     *
     * @return  array
     */
    protected function check_for_update()
    {
        $i = 0;
        $l_latest_update = [];
        $l_return = [
            'update'  => null,
            'message' => null
        ];

        // Retrieve the local versions.
        $l_local_updates = $this->m_update->get_available_updates($this->m_dirs['versions']);

        // @see  ID-6872  System settings can now provide a update-XML URL.
        if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
            $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
        } else {
            $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
        }

        // Now retrieve the latest "online" version.
        $l_updates = array_reverse($this->m_update->get_new_versions($this->m_update->fetch_file($updateXmlUrl)));

        // Get and assign system informations.
        $l_info = $this->m_update->get_isys_info();

        while ($l_info["revision"] < $l_updates[$i]["revision"]) {
            $l_latest_update = $l_updates[$i++];
        }

        if (is_array($l_local_updates) && count($l_local_updates) > 0) {
            foreach ($l_local_updates as $l_local_update) {
                if ($l_latest_update["version"] != $l_local_update["version"] && $l_latest_update != null) {
                    $l_return['update'] = $l_latest_update;
                    $l_return['message'] = 'There is a new i-doit update available! Version ' . $l_latest_update['version'] . ' (Revision: ' . $l_latest_update['revision'] .
                        '), released on ' . $l_latest_update['release'] . '. Simply press "Download and extract" to proceed!';
                } else {
                    $l_return['message'] = 'No updates available (for your version) or you have already downloaded the most recent one.';
                }
            }
        } else {
            $l_return['update'] = $l_latest_update;
        }

        return $l_return;
    }

    /**
     * This method will download the ZIP archive by the given URL and unzip it.
     *
     * @param   string $p_url
     *
     * @return  array
     */
    protected function process_download($p_url = null)
    {
        // Initialize the update file class.
        $l_files = new isys_update_files;

        $l_return = [
            'success' => false,
            'message' => null
        ];

        if (preg_match('/^http[s]?\:\/\/(login\.i-doit\.(com|de)|dev\.synetics\.de)\/.*?idoit-[\-\.0-9]+-update\.zip$/i', $p_url)) {
            // Dowload the update and store it in $this->m_dirs['temp_file'].
            if (isys_file_put_contents($this->m_dirs['temp_file'], $this->m_update->fetch_file($p_url)) > 0) {
                // Read and extract the zipfile to the i-doit directory.
                if ($l_files->read_zip($this->m_dirs['temp_file'], $this->m_dirs['absolute'], false, true)) {
                    $l_return['success'] = true;
                    $l_return['message'] = 'Finished downloading and unzipping!';

                    // Also we return the "fresh" download-list to update the frontend.
                    $l_return['updates'] = isys_format_json::encode($this->m_update->get_available_updates($this->m_dirs['versions']));

                    // Delete the temp file.
                    unlink($this->m_dirs['temp_file']);
                } else {
                    $l_return['message'] = 'The ZIP file was downloaded but could not be extracted!';
                }
            } else {
                $l_return['message'] = 'The download failed, please try again or check your connection!';
            }
        } else {
            $l_return['message'] = 'The download failed, file seems to be no i-doit update!';
        }

        return $l_return;
    }

    /**
     * The update UI constructor.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct($p_step = 1)
    {
        global $l_template, $g_absdir;

        $this->m_tpl = $l_template;
        $this->m_tpl->setTemplateDir(dirname(__DIR__) . DS . 'templates' . DS . 'update');

        $this->m_update = new isys_update;

        $this->m_current_step = $p_step;

        $this->m_dirs = [
            'absolute'             => $g_absdir,
            'log'                  => $g_absdir . DS . 'log' . DS,
            'temp'                 => $g_absdir . DS . 'temp' . DS,
            'temp_file'            => $g_absdir . DS . 'temp' . DS . 'tmp_update.zip',
            'updates'              => $g_absdir . DS . 'updates' . DS,
            'versions'             => $g_absdir . DS . 'updates' . DS . 'versions' . DS,
            'current_update'       => $g_absdir . DS . 'updates' . DS . 'versions' . DS . $_SESSION['update_data']['step-2']['update'] . DS ?: null,
            'current_update_files' => $g_absdir . DS . 'updates' . DS . 'versions' . DS . $_SESSION['update_data']['step-2']['update'] . DS . C__DIR__FILES . DS ?: null
        ];
    }
}
