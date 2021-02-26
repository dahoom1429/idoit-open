<?php

use idoit\Component\ContainerFacade;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use idoit\AddOn\ExtensionProviderInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * i-doit main application controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @property isys_component_template $template
 */
final class isys_application
{
    /**
     * @var isys_application
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    public $app_path = './';

    /**
     * Dependency injection container
     *
     * @var ContainerFacade
     */
    public $container = null;

    /**
     * Also known as $g_product_info
     *
     * @var isys_array
     */
    public $info = null;

    /**
     * @var string
     */
    public $language = 'en';

    /**
     * @var isys_module
     */
    public $module = null;

    /**
     * @var isys_tenant
     */
    public $tenant = null;

    /**
     * @var string
     */
    public $www_path = '/';

    /**
     * @return isys_application|null
     */
    final public static function instance()
    {
        if (!self::$m_instance) {
            self::$m_instance = new self();
        }

        return self::$m_instance;
    }

    /**
     * "The Run Loop"
     *
     * @param isys_request_controller $p_req
     *
     * @throws Exception
     */
    final public static function run(isys_request_controller $p_req)
    {
        /**
         * Parse routes
         */
        if (!$p_req->parse()) {
            // If request controller parsing fails, this means we're not using a path URI right now
            // So, fall back to the "old" request handling

            // If no module has been selected, select the CMDB.
            if (isset($_GET[C__GET__MODULE_ID]) && trim($_GET[C__GET__MODULE_ID]) !== '') {
                $l_mod_id = $_GET[C__GET__MODULE_ID];
            } else {
                $l_mod_id = defined_or_default('C__MODULE__CMDB');
            }

            // Boot load the legacy module
            \idoit\Legacy\ModuleLoader::factory(self::instance()->container)
                ->boot($l_mod_id, isys_register::factory('request'));
        }
    }

    /**
     * 404 handler
     *
     * @param isys_register $p_request
     */
    public static function error404(isys_register $p_request)
    {
        isys_application::instance()->container->notify->error('Error 404: Path not found.');
    }

    /**
     * Set custom warnings handler
     */
    public function overrideErrorHandler()
    {
        // Set custom warnings handler and deactivate assertions.
        set_error_handler([
            'isys_core',
            'warning_handler'
        ], E_WARNING & E_USER_WARNING);

        assert_options(ASSERT_ACTIVE, 0);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        if (property_exists($this, $id)) {
            return $this->$id;
        }
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        //@todo: must be thowed Exception for getting non existed parameter
        return null;
    }

    public function __set($id, $value)
    {
        if (!property_exists($this, $id)) {
            //@todo: must be thowed Exception for setting non existed parameter
            $this->container->set($id, $value);
        } else {
            $this->$id = $value;
        }
    }

    /**
     * The beginning of a structured bootstrapping
     *
     * @return  $this
     * @throws  Exception
     */
    final public function bootstrap()
    {
        try {
            global $g_comp_database_system, $g_modman, $g_page_limit;
            $useCache = defined('WEB_CONTEXT') && WEB_CONTEXT;

            // Initialize directories - this needs to be done, BEFORE the settings, template, and constant components are initialized, since they all use the temp directory.
            $this->init_config_directories();

            isys_component_session::instance(new isys_module_ldap())
                ->start_session();

            $useCache &= isset($_SESSION["user_mandator"]);

            $userMandatorId = (isset($_SESSION["user_mandator"]) ? $_SESSION["user_mandator"] : 'none');

            $diCacheFile = $this->app_path . '/temp/di_container_cache_mandator_' . $userMandatorId . '.php';

            $diCache = new ConfigCache($diCacheFile, true); //debug=true says that cache will be always check changing time for every component

            if ($useCache && $diCache->isFresh()) {
                require_once($diCacheFile);
                $this->container = new \idoit\Component\ContainerFacadeCompiled();
                $this->container->set('application', $this);
            } else {
                $loader = new YamlFileLoader($this->container, new FileLocator($this->app_path));
                $loader->load($this->app_path . '/src/di_services.yml');

                $this->container->setParameter('log.path', $this->app_path . '/log/system');
                $this->container->addCompilerPass(new RegisterListenersPass());
            }

            $g_comp_database_system = $this->container->database_system;
            $session = $this->container->get('session');
            global $g_db_system;
            if (is_array($g_db_system) && isset($g_db_system['type'],
                    $g_db_system['host'],
                    $g_db_system['port'],
                    $g_db_system['user'],
                    $g_db_system['pass'],
                    $g_db_system['name'])) {
                // initialize the system DB
                $systemDb = isys_component_database::factory(
                    $g_db_system['type'],
                    $g_db_system['host'],
                    $g_db_system['port'],
                    $g_db_system['user'],
                    $g_db_system['pass'],
                    $g_db_system['name']
                );
                // initialize the tenant with tenant DB
                $session->initMandatorSession($systemDb);
            }

            // Initialize directories which needs setting data
            $this->initConfigSettingDirectories();

            // Initialize system constants.
            $this->init_constant_manager();

            // Set default timezone.
            //@todo: check tenant settings after init_session
            date_default_timezone_set($this->container->settingsSystem->get('system.timezone', 'Europe/Berlin'));

            // Load module manager.
            $g_modman = $this->container->moduleManager;

            // Preserve backward compatibility
            \idoit\Legacy\BackwardCompatibility::factory($this->container)
                ->preserve();

            // Initialize session.
            $this->init_session_data();

            // Initialize some config variables.
            $this->init_config_variables();
            header('i-doit-Authorized: ' . $this->container->session->is_logged_in());

            // @see  ID-2855  Set some TCPDF settings for better performance
            define('K_TCPDF_EXTERNAL_CONFIG', true);

            $this->init_tcpdf_constants();

            // Obtain page limit from the settings. This can only work after initializing the session.
            $g_page_limit = isys_usersettings::get('gui.objectlist.rows-per-page', 50);

            //check if modules cache is actual
            $extendedModulesClasses = [];
            foreach ($this->container->moduleManager->modules() as $module) {
                /** @var isys_module_register $module */
                if ($module->get_object() instanceof ExtensionProviderInterface) {
                    $extendedModulesClasses[] = get_class($module->get_object());
                }
            }
            if ($useCache && $this->container->isCompiled() &&
                (!$this->container->hasParameter('modules') || !($this->container->getParameter('modules') === $extendedModulesClasses))) {
                unlink($diCacheFile);
                //@todo: here could be recursive call of itself for cleaning cache right now: return $this->bootstrap();
            }

            if (!$this->container->isCompiled()) {
                $this->container->setParameter('modules', $extendedModulesClasses);

                foreach ($this->container->moduleManager->modules() as $module) {
                    /** @var isys_module_register $module */
                    if ($module->get_object() instanceof ExtensionProviderInterface) {
                        /** @var \Symfony\Component\DependencyInjection\Extension\ExtensionInterface $moduleExtension */
                        $moduleExtension = $module->get_object()
                            ->getContainerExtension();
                        $moduleExtension->load([], $this->container);
                    }
                }

                if ($useCache) {
                    $this->container->compile();

                    $dumper = new PhpDumper($this->container);
                    $diCache->write(
                        $dumper->dump(['namespace' => 'idoit\Component', 'class' => 'ContainerFacadeCompiled', 'base_class' => 'ContainerFacade']),
                        $this->container->getResources()
                    );
                    chmod($diCache->getPath(), 0664);
                }
            }

            $this->container->get('database_system')->set_autocommit(true);
            $this->container->get('database')->set_autocommit(true);
        } catch (Exception $e) {
            throw $e;
        }

        if (isset($_SERVER['HTTP_X_I_DOIT_TENANT_ID']) && $_SERVER['HTTP_X_I_DOIT_TENANT_ID'] != $userMandatorId) {
            isys_notify::error('Changes were interrupted. You have logged in with a different tenant. Please, log in again!', ['sticky' => true]);
            exit;
        }

        return $this;
    }

    /**
     * Set application's language
     *
     * @param string $language
     *
     * @return isys_application
     */
    final public function language($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Destructor.
     *
     * @throws  Exception
     */
    final public function __destruct()
    {
        if ($this->container->signals) {
            $this->container->signals->emit('system.shutdown');
        }
    }

    /**
     * Initialize application's session
     *
     * @global $g_comp_database
     * @global $g_comp_template_language_manager
     * @global $g_mandator_info
     *
     * @throws Exception
     */
    private function init_session_data()
    {
        global $g_comp_database, $g_mandator_info, $g_comp_template_language_manager;

        $GLOBALS['g_comp_session'] = $this->container->session;
        //this inits basic mandator info in session service
        $g_mandator_info = $this->container->session->get_mandator_data();
        //this creates and inits user database
        $g_comp_database = $this->container->database;
        //this finally inits session data using database
        $this->container->session->get_session_data($this->container->session->get_session_id());

        /**
         * isys_tenantsettings are available from now on!
         */

        // Initialize template language manager if not already initialized by isys_component_session::post_init_session

        // Assign language manager from container to global for legacy calls
        $g_comp_template_language_manager = $this->container->get('language');

        // Leave global g_comp_template here for backward compatibility for legacy modules
        $GLOBALS['g_comp_template'] = $this->container->template;

        if ($g_mandator_info && is_object($this->container->database) && $this->container->database->is_connected()) {
            // Save Tenant Info.
            $this->tenant = new isys_tenant(
                $g_mandator_info['isys_mandator__title'],
                $g_mandator_info['isys_mandator__description'],
                $g_mandator_info['isys_mandator__id'],
                $g_mandator_info['isys_mandator__db_name'],
                $g_mandator_info['isys_mandator__dir_cache']
            );

            // ------------------------------------------------ OVERRIDE USER CONFIG ---
            isys_glob_override_user_settings();

            // Backward compatibility for older modules
            // @todo Should be removed in one of the next major versions
            $GLOBALS['g_active_modreq'] = $GLOBALS['g_modreq'] = isys_module_request::get_instance();
            // ---------------------------------------------------------------------------------------

            // Initialize module manager.
            $this->container->moduleManager->init(isys_module_request::get_instance());
        } else {
            // Initialize Pro module, if existent. This case happens when there is no login.
            if (file_exists($this->app_path . '/src/classes/modules/pro/init.php')) {
                include_once($this->app_path . '/src/classes/modules/pro/init.php');
            }
        }
    }

    /**
     * Create and include system constants (temp/const_cache.inc.php)
     *
     * @global $g_dcs
     */
    private function init_constant_manager()
    {
        global $g_dcs;

        // Include Global constant cache.
        $g_dcs = isys_component_constant_manager::instance();
        $g_dcs->include_dcs();
    }

    /**
     * Initialize some config variables
     *
     * @global $g_config
     */
    private function init_config_variables()
    {
        global $g_config;

        /**
         * Sysid prefix for isys_obj__sysid
         */
        define("C__CMDB__SYSID__PREFIX", $this->container->settingsTenant->get('cmdb.sysid.prefix', 'SYSID_'));

        /* Attaches LDAP users automatically to these group ids (comma-separated)
            - Only one group is also possible
            - Only group IDs will work, e.g. 15 for admin. Contacts->Groups for more */
        define("C__LDAP__GROUP_IDS", $this->container->settingsTenant->get('ldap.default-group', ''));

        // Activate LDAP Debugging into i-doit/log/ldap_debug.txt (boolean).
        define("C__LDAP__DEBUG", (bool)$this->container->settingsSystem->get('ldap.debug', true));

        // Maximum  amount of objects which are loaded into the tree of the object browser, the browser will not load at all if limit is reached.
        define("C__TREE_MAX_OBJECTS", $this->container->settingsTenant->get('cmdb.object-browser.max-objects', 1500)); // Numeric value

        $g_config["wiki_url"] = $this->container->settingsTenant->get('gui.wiki-url', '');
        $g_config["wysiwyg"] = $this->container->settingsTenant->get('gui.wysiwyg', '1');
        $g_config["use_auth"] = $this->container->settingsTenant->get('auth.active', '1');
        $g_config['devmode'] = $this->container->settingsTenant->get('system.devmode', false);

        // SYS-ID Readonly?
        define("C__SYSID__READONLY", !!$this->container->settingsTenant->get('cmdb.registry.sysid_readonly', 0));

        // Default date format (php-dateformat: http://php.net/date).
        define("C__INFOBOX__DATEFORMAT", $this->container->settingsTenant->get('gui.infobox.dateformat', 'd.m.Y H:i :'));

        // Enable locking of datasets (objects)?
        define("C__LOCK__DATASETS", !!$this->container->settingsTenant->get('cmdb.registry.lock_dataset', 0));

        // Timeout of locked datasets in seconds.
        define("C__LOCK__TIMEOUT", $this->container->settingsTenant->get('cmdb.registry.lock_timeout', 120));
        define("C__TEMPLATE__COLORS", $this->container->settingsTenant->get('cmdb.template.colors', 1));
        define("C__TEMPLATE__COLOR_VALUE", $this->container->settingsTenant->get('cmdb.template.color_value', '#CC0000'));
        define("C__TEMPLATE__STATUS", $this->container->settingsTenant->get('cmdb.template.status', 0));
        define("C__TEMPLATE__SHOW_ASSIGNMENTS", $this->container->settingsTenant->get('cmdb.template.show_assignments', 1));
    }

    /**
     * Set some TCPDF constants to increase performance.
     *
     * @see     ID-2855
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function init_tcpdf_constants()
    {
        if (!defined('K_PATH_FONTS')) {
            // Define the default TCPDF font directory. This is necessary, because we copy all TCPDF fonts in our own "<i-doit>/upload/fonts" dir.
            define('K_PATH_FONTS', rtrim($this->app_path, '/') . '/upload/fonts/');
        }

        // Generic name for a blank image.
        define('K_BLANK_IMAGE', '_blank.png');

        // Page format.
        define('PDF_PAGE_FORMAT', 'A4');

        // Page orientation (P=portrait, L=landscape).
        define('PDF_PAGE_ORIENTATION', 'P');

        // Document creator.
        define('PDF_CREATOR', 'TCPDF');

        // Document author.
        define('PDF_AUTHOR', 'i-doit');

        // Header title.
        define('PDF_HEADER_TITLE', 'i-doit PDF Dokument');

        // Header description string.
        define('PDF_HEADER_STRING', "For more information, visit www.i-doit.com");

        // Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
        define('PDF_UNIT', 'mm');

        // Header margin.
        define('PDF_MARGIN_HEADER', 5);

        // Footer margin.
        define('PDF_MARGIN_FOOTER', 10);

        // Top margin.
        define('PDF_MARGIN_TOP', 27);

        // Bottom margin.
        define('PDF_MARGIN_BOTTOM', 25);

        // Left margin.
        define('PDF_MARGIN_LEFT', 15);

        // Right margin.
        define('PDF_MARGIN_RIGHT', 15);

        // Default main font name.
        define('PDF_FONT_NAME_MAIN', 'helvetica');

        // Default main font size.
        define('PDF_FONT_SIZE_MAIN', 10);

        // Default data font name.
        define('PDF_FONT_NAME_DATA', 'helvetica');

        // Default data font size.
        define('PDF_FONT_SIZE_DATA', 8);

        // Default monospaced font name.
        define('PDF_FONT_MONOSPACED', 'courier');

        // Ratio used to adjust the conversion of pixels to user units.
        define('PDF_IMAGE_SCALE_RATIO', 1.25);

        // Magnification factor for titles.
        define('HEAD_MAGNIFICATION', 1.1);

        // Height of cell respect font height.
        define('K_CELL_HEIGHT_RATIO', 1.25);

        // Title magnification respect main font size.
        define('K_TITLE_MAGNIFICATION', 1.3);

        // Reduction factor for small font.
        define('K_SMALL_RATIO', 2 / 3);

        // Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
        define('K_THAI_TOPCHARS', false);

        // If true allows to call TCPDF methods using HTML syntax
        // IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
        define('K_TCPDF_CALLS_IN_HTML', false);

        // If true and PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
        define('K_TCPDF_THROW_EXCEPTION_ERROR', false);

        // Default timezone for datetime functions
        define('K_TIMEZONE', 'UTC');
    }

    /**
     * Small function to load directories which use isys_tenantsettings or isys_settings.
     * Should only be called AFTER initializing the settings.
     */
    private function initConfigSettingDirectories()
    {
        global $g_dirs;

        $g_dirs["fileman"] = [
            "target_dir" => $this->container->settingsSystem->get('system.dir.file-upload', $this->app_path . '/upload/files/'),
            "temp_dir"   => $g_dirs['temp'],
            "image_dir"  => $this->container->settingsSystem->get('system.dir.image-upload', $this->app_path . '/upload/images/'),
            "font_dir"   => $this->app_path . '/upload/fonts/',
        ];
    }

    /**
     * Return image dir with tenant
     *
     * @return string
     */
    public function getImageDir()
    {
        global $g_dirs;

        return $g_dirs['fileman']['image_dir'] . $this->tenant->id . DS;
    }

    /**
     * Return uploaded image path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getUploadImagePath(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        return $this->getOrCreateUploadImageDir($fileName) . $fileName;
    }

    /**
     * Return uploaded file path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getUploadFilePath(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        global $g_dirs;

        return $this->getOrCreateUploadFileDir($fileName) . $fileName;
    }

    /**
     * Return uploaded file's direcory path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getOrCreateUploadImageDir(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        $directoryName = $this->getImageDir() . substr(md5($fileName), 0, 2) . DS;

        if (!is_dir($directoryName) && !mkdir($directoryName, 0775, true)) {
            throw new isys_exception_filesystem("Can't create subdirectory '$directoryName' for uploading file. Please check rights");
        }

        return $directoryName;
    }

    /**
     * Return uploaded file's direcory path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getOrCreateUploadFileDir(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        global $g_dirs;

        $directoryName = $g_dirs['fileman']['target_dir'] . substr(md5($fileName), 0, 2) . DS;

        if (!is_dir($directoryName) && !mkdir($directoryName, 0775, true)) {
            throw new isys_exception_filesystem("Can't create subdirectory '$directoryName' for uploading file. Please check rights");
        }

        return $directoryName;
    }

    /**
     * Small method for specifically configuring the directories.
     */
    private function init_config_directories()
    {
        global $g_dirs;

        /**
         * @desc Directory configuration
         * -------------------------------------------------------------------------
         *       Array of required global directory structure, the rest is read
         *       and set by the system registry. NOTE: You should NOT modify this!
         *
         *       FILE MANAGER SETTINGS
         *
         *       Modify them in order to control the file manager, downloads and
         *       uploads. target_dir must be absolute and tailed by /, furthermore,
         *       your apache-user (normally www-data) needs full access rights (RWX)
         *       to this directory. temp_dir is /tmp/ on UNIX systems, otherwise
         *       configure it here manually for Win.
         *       The image_dir is used for the uploaded object images www-data needs also
         *       full access here
         */
        if (!isset($g_dirs['temp'])) {
            $g_dirs['temp'] = $this->app_path . '/temp/';
        }

        $g_dirs['class'] = $this->app_path . '/src/classes/';
        $g_dirs['handler'] = $this->app_path . '/src/handler/';
        $g_dirs['css_abs'] = $this->app_path . '/src/themes/default/css/';
        $g_dirs['js_abs'] = $this->app_path . '/src/tools/js/';
        $g_dirs['smarty'] = $this->app_path . '/src/themes/default/smarty/';
        $g_dirs['utils'] = $this->app_path . '/src/utils/';
        $g_dirs['import'] = $this->app_path . '/src/classes/import/';
        $g_dirs['log'] = $this->app_path . '/log/';
        $g_dirs['images'] = rtrim($this->www_path, '/') . '/images/';
        $g_dirs['theme'] = rtrim($this->www_path, '/') . '/src/themes/default/';
        $g_dirs['theme_images'] = rtrim($this->www_path, '/') . '/src/themes/default/images/';
        $g_dirs['tools'] = rtrim($this->www_path, '/') . '/src/tools/';
    }

    /**
     * Private wakeup method to ensure singleton.
     */
    final private function __wakeup()
    {
        ;
    }

    /**
     * Private clone method to ensure singleton.
     */
    final private function __clone()
    {
        ;
    }

    /**
     * Private constructor
     *
     * @global $g_absdir
     * @global $g_product_info
     */
    private function __construct()
    {
        global $g_absdir, $g_product_info, $g_config;

        $this->overrideErrorHandler();

        $this->container = new ContainerFacade();
        $this->container->set('application', $this);

        $this->app_path = $g_absdir;
        $this->www_path = $g_config['www_dir'];

        if (!isset($g_product_info) || !is_array($g_product_info)) {
            include_once($this->app_path . '/src/version.inc.php');
        }
        $this->info = ($g_product_info = new isys_array($g_product_info ?: []));

        /*
        // Also retrieving system database info
        // Not needed, yet.
        $l_update = new isys_update();
        $l_info = $l_update->get_isys_info();

        $this->db_info = ($g_product_info = new isys_array(
            array(
                'version' => $l_info['version'],
                'type'    => class_exists('isys_module_pro_autoload') ? 'PRO' : 'OPEN',
                'step'    => ''
            ))
        );
        */
    }
}
