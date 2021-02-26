<?php

use Carbon\Carbon;
use idoit\AddOn\AuthableInterface;
use idoit\Model\QuickInfo;
use idoit\Module\Cmdb\Search\Index\Data\CategoryCollector;
use idoit\Module\License\Entity\License;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;
use idoit\Module\Search\Index\Engine\Mysql;
use idoit\Module\Search\Index\Manager;
use idoit\Module\System\Cleanup\DeleteExportedCheckMkTags;
use idoit\Module\System\Cleanup\MigrateDbObjectsToCategory;
use idoit\Module\System\Cleanup\RemoveOrphanedCustomCategoryData;
use idoit\Module\System\Cleanup\TenantDatabaseEncryption;
use idoit\Module\System\Model\RelationType;
use idoit\Module\System\SettingPage\CustomizeObjectBrowser\CustomizeObjectBrowser;

/**
 * i-doit
 *
 * System settings
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_system extends isys_module implements AuthableInterface, isys_module_authable
{
    // Defines whether this module will be displayed in the extras-menu.
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines, if this module shall be displayed in the systme-menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var boolean
     */
    protected static $m_licenced = true;

    /**
     * Stores "additional options"...
     * @var array
     */
    private $m_additional_options = [];

    /**
     * The current module request instance.
     * @var isys_module_request
     */
    private $m_userrequest;

    /**
     * Installs a licences, if no mandator id is given, the id is retrieved from the session.
     *
     * @param   Integer $p_mandator_id
     *
     * @param null      $mandatorDatabase
     */
    public static function handle_licence_installation($p_mandator_id = null, $mandatorDatabase = null)
    {
        $language = isys_application::instance()->container->get('language');
        $template = isys_application::instance()->container->get('template');
        $session  = isys_application::instance()->container->get('session');

        if (!class_exists('isys_module_licence')) {
            return;
        }

        // Check only in i-doit not in admin-center
        if (defined('C__MODULE__SYSTEM') && class_exists('isys_auth_system_licence')) {
            isys_auth_system_licence::instance()
                ->installation(isys_auth::EXECUTE);
        }

        $l_mandator_id = $p_mandator_id;

        if (is_null($p_mandator_id)) {
            if (is_object($session)) {
                $l_mandator_id = $session->get_mandator_id();
            }
        }

        if (is_object($mandatorDatabase)) {
            $tenant_database = $mandatorDatabase->get_db_name();
        } elseif ($l_mandator_id > 0) {
            $mandatorDatabase = isys_application::instance()->container->database;
            $tenant_database = $mandatorDatabase->get_db_name();
        } else {
            $tenant_database = '';
        }

        $template->assign('tenant_database', $tenant_database)
            ->assign('encType', 'multipart/form-data');

        try {
            if (is_array($_POST) && count($_POST) > 0) {
                if (!empty($_FILES['licence_file']['tmp_name'])) {
                    if (class_exists('isys_module_licence')) {
                        global $g_license_token;

                        $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->database_system, $g_license_token);

                        // Validate uploaded licence.
                        $l_licence = new isys_module_licence();

                        // We try to catch a certain exception.
                        try {
                            $l_lic_parse = $licenseService->parseLicenseFile($_FILES["licence_file"]["tmp_name"]);
                        } catch (\idoit\Module\License\Exception\LegacyLicenseExpiredException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXPIRED'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseInvalidKeyException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseInvalidTypeException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseParseException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        }

                        if (is_array($l_lic_parse)) {
                            $template->assign("licence_info", $l_lic_parse);

                            try {
                                $licenseService->installLegacyLicense($l_lic_parse);

                                $template->assign("note", $language->get('LC__LICENCE__INSTALL__SUCCESSFULL'));
                            } catch (\idoit\Module\License\Exception\LegacyLicenseInstallException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                            } catch (\idoit\Module\License\Exception\LegacyLicenseExistingException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXISTS'));
                            } catch (\idoit\Module\License\Exception\LegacyLicenseExpiredException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXPIRED'));
                            }
                        }
                    }
                } else {
                    // No licence file uploaded.
                    $template->assign("error", $language->get('LC__LICENCE__NO_UPLOAD'));
                }
            }
        } catch (isys_exception_licence $e) {
            $template
                ->assign("error", $e->getMessage())
                ->assign("errorcode", $e->get_errorcode());
        }

        $template->include_template('contentbottomcontent', 'modules/system/licence_installation.tpl');
    }

    /**
     * Method for generating a string.
     *
     * @param   string $p_what
     * @param   string $p_treenode
     *
     * @return  string
     */
    private static function generate_link($p_what = null, $p_treenode = null)
    {
        return isys_helper_link::create_url([
            C__GET__MODULE_ID => defined_or_default('C__MODULE__SYSTEM'),
            'what'            => $p_what,
            C__GET__TREE_NODE => $p_treenode
        ]);
    }

    /**
     * Initializes the CMDB module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  Boolean
     */
    public function init(isys_module_request $p_req)
    {
        global $g_dirs, $g_config;

        $this->m_additional_options = [
            'updates'              => [
                'text' => 'i-doit Updates',
                'icon' => $g_dirs['images'] . '/icons/updates.gif',
                'link' => $g_config['www_dir'] . 'updates'
            ],
            'sysoverview'          => [
                'func' => 'handle_sysoverview',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__OVERVIEW'),
                'icon' => $g_dirs['images'] . '/icons/silk/cog.png'
            ],
            'lock'                 => [
                'func' => 'handle_lock',
                'text' => $this->language->get('LC__LOCKED__OBJECTS'),
                'icon' => null
            ],
            'logbook'              => [
                'text' => $this->language->get('LC__UNIVERSAL__TITLE_LOGBOOK'),
                'icon' => null,
                'link' => '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__LOGBOOK')
            ],
            'cache'                => [
                'func' => 'handle_cache',
                'text' => 'Cache / ' . $this->language->get('LC__UNIVERSAL__DATABASE'),
                'icon' => $g_dirs['images'] . '/icons/silk/database.png'
            ],
            'manager'              => [
                'text' => $this->language->get('LC__MODULE__MANAGER__TITLE'),
                'link' => '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__SYSTEM') . "&" . C__GET__MODULE_SUB_ID . "=" . defined_or_default('C__MODULE__MANAGER')
            ],
            'api'                  => [
                'func' => 'handle_api',
                'text' => 'JSON-RPC API',
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png'
            ],
            'apiCategoryConfiguration'                  => [
                'func' => 'apiCategoryConfiguration',
                'text' => $this->language->get('LC__SYSTEM_SETTINGS__API__CATEGORIES_AND_ATTRIBUTES'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png'
            ],
            'system_settings'      => [
                'func' => 'handle_settings',
                'text' => $this->language->get('LC__UNIVERSAL__GENERAL_CONFIGURATION'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png'
            ],
            'idoit_update'         => [
                'text' => 'i-doit Update',
                'icon' => $g_dirs['images'] . '/icons/silk/arrow_refresh.png',
                'link' => '?load=update'
            ],
            'relation_types'       => [
                'func' => 'handle_relation_types',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png',
            ],
            'roles_administration' => [
                'func' => 'handle_roles_administration',
                'text' => $this->language->get('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION'),
                'icon' => $g_dirs['images'] . '/icons/silk/server_database.png',
            ],
            'custom_properties'    => [
                'func' => 'handle_custom_properties',
                'text' => $this->language->get('LC__UNIVERSAL__CATEGORY_EXTENSION'),
                'icon' => $g_dirs['images'] . '/icons/silk/database.png'
            ],
            'object_matching'      => [
                'func' => 'handle_object_matching',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__OBJECT_MATCHING'),
                'icon' => $g_dirs['images'] . '/icons/silk/timeline_marker.png'
            ],
            'hinventory'           => [
                'func' => 'handle_hinventory',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY__CONFIGURATION'),
                'icon' => ''
            ],
            'customizeObjectBrowser'           => [
                'func' => 'handle_customizeObjectBrowser',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER'),
                'icon' => ''
            ]
        ];

        if (is_object($p_req)) {
            $this->m_userrequest = &$p_req;

            return true;
        }

        return false;
    }

    /**
     * Method for handling the licence overview.
     *
     */
    public function handle_licence_overview()
    {
        $template = isys_application::instance()->container->get('template');
        $session = isys_application::instance()->container->get('session');
        $systemDatabase = isys_application::instance()->container->get('database_system');

        if (!class_exists('isys_module_licence')) {
            return;
        }

        if (class_exists('isys_auth_system_licence')) {
            isys_auth_system_licence::instance()
                ->overview(isys_auth::EXECUTE);
        }

        // Get licence module.
        $l_licence = new isys_module_licence();

        // Check actiosns.
        if (isset($_GET["delete"]) && $_GET["id"] > 0) {
            if ($l_licence->delete_licence($systemDatabase, $_GET["id"])) {
                $template->assign("note", $this->language->get("LC__LICENCE__DELETED"));
            }
        }

        global $g_license_token;

        $licenseService = LicenseServiceFactory::createDefaultLicenseService($systemDatabase, $g_license_token);

        $l_exceeding["objects"] = $this->language->get("LC__UNIVERSAL__NO");

        $earliestExpiringLicense = $licenseService->getEarliestExpiringLicense();

        $requestedAt = null;
        $expiresAt = null;
        $remaining = null;

        $now = Carbon::now();

        $dateFormat = isys_locale::get_instance()->get_date_format();

        if ($earliestExpiringLicense instanceof License) {
            $requestedAt = $earliestExpiringLicense->getValidityFrom()->format($dateFormat);
            $expiresAt = $earliestExpiringLicense->getValidityTo()->format($dateFormat);

            $remaining = $earliestExpiringLicense->getValidityTo()->diff($now);
        } elseif (isset($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE])) {
            if (
                $earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] === LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE ||
                $earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] === LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING
            ) {
                $registrationDate = Carbon::createFromTimestamp($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);

                $requestedAt = $registrationDate->format($dateFormat);

                $expires = $registrationDate->copy()->modify("+99 years");

                $expiresAt = $expires->format($dateFormat);

                $remaining = $expires->diff($now);
            }

            if (isset($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME])) {
                $days = (int) round(abs((($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME] / 60 / 60 / 24))));

                $registrationDate = Carbon::createFromTimestamp($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);

                $requestedAt = $registrationDate->format($dateFormat);

                $expires = $registrationDate->copy()->modify("+{$days} days");

                $expiresAt = $expires->format($dateFormat);

                $remaining = $expires->diff($now);
            }
        }

        $expiresWithinSixMonths = Carbon::parse($expiresAt)
            ->between(
                $now,
                $now->copy()->addMonths(6)
            );

        $remainingTimePercentage = 0;

        if ($expiresWithinSixMonths) {
            $remainingTimePercentage = 100 / $now->diffInDays($now->copy()->addMonths(6)) * $now->diffInDays(Carbon::parse($expiresAt));
        }

        $stringTimeLimit = $this->language->get('LC__WIDGET__EVAL__TIME_LIMIT', [
            $requestedAt,
            $expiresAt,
            $remaining->y,
            $remaining->m,
            $remaining->d
        ]);

        if (!$licenseService->isTenantLicensed($session->get_mandator_id())) {
            $l_exceeding["objects"] = $this->language->get("LC__UNIVERSAL__YES");
            $l_exceeding["objects_class"] = "text-red text-bold";
            $template->assign("error", $this->language->get("LC__LICENCE__NO_LICENCE"));

            $stringTimeLimit = $this->language->get('LC__WIDGET__EVAL__TIME_LIMIT_EXCEEDED', [
                $requestedAt,
                $expiresAt,
                $remaining->y,
                $remaining->m,
                $remaining->d
            ]);
        }

        $tenant = $licenseService->getTenants(true, [$session->get_mandator_id()]);

        $l_free_objects = (int) $tenant[0]['isys_mandator__license_objects'];

        // Statistics.
        $l_mod_stat = new isys_module_statistics();
        $l_mod_stat->init_statistics();
        $l_mod_stat_counts = $l_mod_stat->get_counts();
        $l_mod_stat_stats = $l_mod_stat->get_stats();

        if (is_numeric($l_free_objects)) {
            $l_counts = $l_mod_stat->get_counts();
            $l_mod_stat_counts["free_objects"] = $l_free_objects - $l_counts["objects"];
            if ($l_mod_stat_counts["free_objects"] < 0) {
                $l_mod_stat_counts["free_objects"] = 0;
            }
        } else {
            $l_mod_stat_counts["free_objects"] = $l_free_objects;
        }

        $template
            ->assign("stats", $l_mod_stat)
            ->assign("stat_counts", $l_mod_stat_counts)
            ->assign("stat_stats", $l_mod_stat_stats)
            ->assign("exceeding", $l_exceeding)
            ->assign("licensedAddOns", $licenseService->getLicensedAddOns())
            ->assign("stringTimeLimit", $stringTimeLimit)
            ->assign("expiresWithinSixMonths", $expiresWithinSixMonths)
            ->assign("remainingTimePercentage", $remainingTimePercentage)
            ->include_template('contentbottomcontent', 'modules/system/licence_overview.tpl');
    }

    public function handle_settings()
    {
        global $index_includes;

        if (class_exists('isys_auth_system')) {
            isys_auth_system::instance()
                ->check(isys_auth::VIEW, 'GLOBALSETTINGS/SYSTEMSETTING');
        }

        $l_yes_no = get_smarty_arr_YES_NO();
        $l_comp_settings = new isys_component_dao_setting(isys_application::instance()->database);
        $l_list = '';

        $l_registry_keys = [
            [
                'title'        => $this->language->get("LC__SYSTEM__REGISTRY__MYTASK"),
                'post'         => 'reg_count_myTask_entries',
                'settings_key' => 'cmdb.registry.mytask_entries',
                'type'         => 'f_text',
                'params'       => [
                    'type' => 'f_text'
                ]
            ],
            [
                'title'        => $this->language->get("LC__CMDB__SYSTEM_SETTING__QUICK_SAVE_BUTTON"),
                'post'         => 'reg_quick_save_button',
                'settings_key' => 'cmdb.registry.quicksave',
                'type'         => 'f_dialog',
                'default'      => 1,
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => 'SYS-ID readonly',
                'post'         => 'reg_sysid_readonly',
                'settings_key' => 'cmdb.registry.sysid_readonly',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => $this->language->get('LC__SYSTEM__REGISTRY__DD_OBJECTS'),
                'post'         => 'reg_object_drag_n_drop',
                'settings_key' => 'cmdb.registry.object_dragndrop',
                'type'         => 'f_dialog',
                'params'       => [
                    'p_arData'     => $l_yes_no,
                    'type'         => 'f_dialog',
                    'p_bDbFieldNN' => 1
                ]
            ],
            [
                'title'        => $this->language->get('LC__CMDB__SETTINGS__CMDB__OBJECTTYPE_SORTING'),
                'post'         => 'reg_objtype_sorting',
                'settings_key' => 'cmdb.registry.object_type_sorting',
                'type'         => 'f_dialog',
                'default'      => C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL,
                'params'       => [
                    'p_arData'     => [
                        C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC => $this->language->get('LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__ALPHABETICALLY'),
                        C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL    => $this->language->get('LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__MANUAL')
                    ],
                    'p_bDbFieldNN' => 1,
                    'type'         => 'f_dialog'
                ]
            ],
            [
                'title'        => $this->language->get('LC__CMDB__SYSTEM_SETTING__SANITIZE_INPUT_DATA'),
                'post'         => 'data_sanitizing',
                'settings_key' => 'cmdb.registry.sanitize_input_data',
                'type'         => 'f_dialog',
                'default'      => 1,
                'params'       => [
                    'p_arData'      => $l_yes_no,
                    'type'          => 'f_dialog',
                    'p_bDbFieldNN'  => 1,
                    'p_strFootnote' => '*'
                ]
            ]
        ];

        $l_edit_right = class_exists('isys_auth_system') ? isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/SYSTEMSETTING') : true;

        $l_navbar = isys_component_template_navbar::getInstance();

        try {
            if (isset($_GET['delete_locks']) && isset($_POST['id'])) {
                if (is_array($_POST['id']) && count($_POST['id'])) {
                    $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);

                    foreach ($_POST['id'] as $l_lockID) {
                        $l_dao_lock->delete($l_lockID);
                    }
                }
            }

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            } else {
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                    // Save Registry-Values.
                    foreach ($l_registry_keys as $l_data) {
                        if (isset($_POST[$l_data['post']])) {
                            isys_tenantsettings::set($l_data['settings_key'], (int)$_POST[$l_data['post']]);
                        }
                    }

                    $l_comp_settings->set(null, defined_or_default('C__MANDATORY_SETTING__CURRENCY'), $_POST['C__CATG__OVERVIEW__MONETARY_FORMAT']);
                    $l_comp_settings->set(null, defined_or_default('C__MANDATORY_SETTING__IP_HANDLING'), (($_POST['C__MANDATOR_SETTINGS__IP_HANDLING'] <= 0) ? 0 : 1));

                    // Lock Handling.
                    isys_tenantsettings::set('cmdb.registry.lock_dataset', $_POST['lock']);
                    isys_tenantsettings::set('cmdb.registry.lock_timeout', $_POST['lock_timeout']);
                    isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                }
            }
        } catch (isys_exception $e) {
            isys_notify::error($e->getMessage(), ['sticky' => true]);
        }

        foreach ($l_registry_keys as $l_data) {
            $l_rules[$l_data['post']] = $l_data['params'];
            $type = ($l_data['type'] == "f_text") ? "p_strValue" : "p_strSelectedID";
            $default = isset($l_data['default']) ? $l_data['default'] : 0;
            $l_rules[$l_data['post']][$type] = isys_tenantsettings::get($l_data['settings_key'], $default);
        }

        $l_rules['C__CATG__OVERVIEW__MONETARY_FORMAT']['p_strSelectedID'] = $l_comp_settings->get(null, defined_or_default('C__MANDATORY_SETTING__CURRENCY'));
        $l_rules['C__MANDATOR_SETTINGS__IP_HANDLING'] = [
            'p_strSelectedID' => $l_comp_settings->get(null, defined_or_default('C__MANDATORY_SETTING__IP_HANDLING')),
            'p_arData'        => $l_yes_no,
            'p_bDbFieldNN'    => true
        ];

        $this->handle_templates();

        // Lock Vars.
        isys_application::instance()->template
            ->assign("C__LOCK__TIMEOUT", isys_tenantsettings::get('cmdb.registry.lock_timeout', 120))
            ->assign("C__LOCK__DATASETS", isys_tenantsettings::get('cmdb.registry.lock_dataset', 0));

        $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);
        $l_dao_lock->delete_expired_locks();
        $l_locks = $l_dao_lock->get_lock_information(null, null);

        if ($l_locks->num_rows() > 0) {
            $l_objList = new isys_component_list(null, $l_locks);

            $l_objList->set_row_modifier($this, 'modify_lock_row')
                ->setIdField('id')
                ->config([
                    'isys_cats_person_list__title' => $this->language->get('LC__CMDB__LOGBOOK__USER'),
                    'isys_user_session__ip'        => $this->language->get('LC__CATP__IP__ADDRESS'),
                    'isys_obj_type__title'         => $this->language->get('LC__CMDB__OBJTYPE'),
                    'isys_obj__title'              => $this->language->get('LC__UNIVERSAL__TITLE'),
                    'table_data'                   => $this->language->get('LC__UNIVERSAL__TOPIC'),
                    'isys_lock__datetime'          => $this->language->get('LC__LOCKED__AT'),
                    'expires'                      => $this->language->get('LC__EXPIRES_IN'),
                ], null, "[{isys_lock__id}]");

            $l_objList->createTempTable();
            $l_list = $l_objList->getTempTableHtml();
        }

        if (isys_glob_get_param(C__GET__NAVMODE) != C__NAVMODE__EDIT) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        isys_application::instance()->template->assign("g_list", $l_list)
            ->assign("registry_keys", $l_registry_keys)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes['contentbottomcontent'] = "modules/system/settings.tpl";
    }

    // Handles GlobalSettings -> System settings

    /**
     * Api category configuration
     *
     * @throws isys_exception_auth
     */
    public function apiCategoryConfiguration()
    {
        global $index_includes, $g_mandator_info;

        /**
         * Check right system
         */
        if (class_exists('isys_auth_system')) {
            isys_auth_system::instance()
                ->check(isys_auth::VIEW, 'JSONRPCAPI/OBJTYPE');
            $l_edit_right = isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'JSONRPCAPI');
        } else {
            $l_edit_right = true;
        }

        /**
         * Ajax call handling
         */
        if (!empty($_GET['ajax'])) {
            switch ($_GET['op']) {
                case 'getCategoryList':
                    header('Content-Type: application/json');

                    // Create category list and output it as json
                    echo isys_format_json::encode(self::getCategoryList());
                    break;
                case 'getCategoryDefinition':
                    header('Content-Type: application/json');

                    // Fetch and output category definition
                    echo isys_format_json::encode(self::getCategoryDefinition($_GET['categoryType'], $_GET['categoryId']));
                    break;
                case 'getCategoryReadRequest':
                    header('Content-Type: application/json');

                    // Parse params
                    $params = isys_format_json::decode($_GET['request'], true)['params'];

                    // Create category api model
                    $categoryModel = new isys_api_model_cmdb_category(isys_application::instance()->container->get('cmdb_dao'));

                    // Read category and fetch response
                    $response = $categoryModel->read($params);

                    // Output json
                    echo isys_format_json::encode($response);
                    break;
                case 'getCategoryWriteRequest':
                    header('Content-Type: application/json');

                    // Parse params
                    $params = isys_format_json::decode($_GET['request'], true)['params'];

                    // Create category api model
                    $categoryModel = new isys_api_model_cmdb_category(isys_application::instance()->container->get('cmdb_dao'));

                    // Save and fetch response
                    $response = $categoryModel->save($params);

                    // Output json
                    echo isys_format_json::encode($response);
                    break;
            }

            die;
        }

        // Create translations
        $translations = [
            'LC__SYSTEM_SETTINGS__API__CAA__LOADING_CATEGORIES',
            'LC__SYSTEM_SETTINGS__API__CAA__PLEASE_SELECT_A_CATEGORY',
            'LC__SYSTEM_SETTINGS__API__CAA__LOADING_CATEGORY_INFORMATION',
            'LC__SYSTEM_SETTINGS__API__CAA__CATEGORY_INFORMATION',
            'LC__SYSTEM_SETTINGS__API__CAA__ID',
            'LC__SYSTEM_SETTINGS__API__CAA__CONSTANT',
            'LC__SYSTEM_SETTINGS__API__CAA__MULTIVALUE',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__TITLE',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__KEY',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__TYPE',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__DATA_TYPE_READ',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__DATA_TYPE_WRITE',
            'LC__SYSTEM_SETTINGS__API__CAA__EXAMPLE',
            'LC__SYSTEM_SETTINGS__API__CAA__EXAMPLES',
            'LC__SYSTEM_SETTINGS__API__CAA__EXAMPLES__READ',
            'LC__SYSTEM_SETTINGS__API__CAA__EXAMPLES__WRITE',
            'LC__SYSTEM_SETTINGS__API__CAA__ATTRIBUTES__DESCRIPTION',
            'LC__UNIVERSAL__YES',
            'LC__UNIVERSAL__NO',
        ];

        foreach ($translations as $index => $languageConstant) {
            $translations[$languageConstant] = $this->language->get($languageConstant);

            unset($translations[$index]);
        }

        // Smarty variable assignments
        $this->m_userrequest->get_template()
            ->assign('translations', $translations)
            ->assign('apiKey', $g_mandator_info['isys_mandator__apikey']);

        // Set target template
        $index_includes['contentbottomcontent'] = "modules/system/apiCategoryConfiguration.tpl";
    }

    /**
     * Get category list
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getCategoryList()
    {
        $categoryStore = [];
        $cmdbDao = isys_application::instance()->container->get('cmdb_dao');

        $categoryTypeAbbrTitles = [
            'g'        => $this->language->get('LC__CMDB__GLOBAL_CATEGORIES'),
            's'        => $this->language->get('LC__CMDB__SPECIFIC_CATEGORIES'),
            'g_custom' => $this->language->get('LC__CMDB__CUSTOM_CATEGORIES'),
        ];

        foreach (['g', 's', 'g_custom'] as $categoryTypeAbbr) {
            $categoryResource = $cmdbDao->get_isysgui('isysgui_cat' . $categoryTypeAbbr);

            while ($categoryRow = $categoryResource->get_row()) {
                $parentTitle = '';
                $parentField = 'isysgui_cat' . $categoryTypeAbbr . '__parent';

                // @see  API-196 ID-7042  Categories with "parents" need to display their names in order to make sense.
                if (isset($categoryRow[$parentField]) && $categoryRow[$parentField]) {
                    if ($categoryTypeAbbr === 'g') {
                        $parentTitle = $this->language->get($cmdbDao->get_catg_name_by_id_as_string($categoryRow[$parentField])) . ' > ';
                    } elseif ($categoryTypeAbbr === 's') {
                        $parentTitle = $this->language->get($cmdbDao->get_cats_name_by_id_as_string($categoryRow[$parentField])) . ' > ';
                    }
                }

                $categoryStore[$categoryTypeAbbrTitles[$categoryTypeAbbr]][] = [
                    'value' => $categoryTypeAbbr . '.' . $categoryRow['isysgui_cat' . $categoryTypeAbbr . '__id'],
                    'title' => $parentTitle . $this->language->get($categoryRow['isysgui_cat' . $categoryTypeAbbr . '__title'])
                ];
            }

            usort($categoryStore[$categoryTypeAbbrTitles[$categoryTypeAbbr]], function ($a, $b) {
                return strnatcasecmp($a['title'], $b['title']);
            });
        }

        return $categoryStore;
    }

    private static function getCategoryDefinition($categoryType, $categoryId)
    {
        return \idoit\Module\Api\Category\Descriptor::byId($categoryType, $categoryId)->getDefinition();
    }

    /**
     * Handle api configuration
     */
    public function handle_api()
    {
        global $index_includes;

        if (class_exists('isys_auth_system')) {
            isys_auth_system::instance()
                ->check(isys_auth::VIEW, 'JSONRPCAPI/GLOBAL');
            $l_edit_right = isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'JSONRPCAPI');
        } else {
            $l_edit_right = true;
        }

        try {
            $l_navbar = isys_component_template_navbar::getInstance();

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            } else {
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                    // Check whether api key is used by another mandator already
                    $systemDatabase = isys_application::instance()->container->get('database_system');
                    $resource = $systemDatabase->query(
                        'SELECT * from isys_mandator WHERE 
                        isys_mandator__apikey = BINARY \'' . $systemDatabase->escape_string($_POST['C__SYSTEM_SETTINGS__APIKEY']). '\' AND 
                        isys_mandator__id != ' . (int)isys_application::instance()->session->get_mandator_id()
                    );

                    // Check whether api key is already used
                    if ($systemDatabase->num_rows($resource) !== 0) {
                        throw new isys_exception_general(isys_application::instance()->container->get('language')->get('LC__SYSTEM_SETTINGS__API__ERROR__DUPLICATE_APIKEY'));
                    }

                    // Save data.
                    isys_tenantsettings::set('api.status', (int)isset($_POST["C__SYSTEM_SETTINGS__API_STATUS"]));

                    if (isset($_POST['C__SYSTEM_SETTINGS__APIKEY'])) {
                        isys_application::instance()->database_system->query('UPDATE isys_mandator SET isys_mandator__apikey = \'' . $_POST['C__SYSTEM_SETTINGS__APIKEY'] .
                            '\' WHERE isys_mandator__id = \'' . (int)isys_application::instance()->session->get_mandator_id() . '\'');
                    }

                    if (isset($_POST['C__SYSTEM_SETTINGS__LOG_LEVEL'])) {
                        isys_settings::set('api.log-level', $_POST['C__SYSTEM_SETTINGS__LOG_LEVEL']);
                    }

                    if (isset($_POST['C__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY'])) {
                        isys_settings::set('api.authenticated-users-only', 1);
                    } else {
                        isys_settings::set('api.authenticated-users-only', 0);
                    }

                    if (isset($_POST['C__SYSTEM_SETTINGS__API__STRIP_TAGS'])) {
                        isys_settings::set('api.strip-tags', 1);
                    } else {
                        isys_settings::set('api.strip-tags', 0);
                    }

                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                } else {
                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                }
            }
        } catch (isys_exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        // Read data.
        $l_apikey_data = isys_application::instance()->database_system->fetch_row_assoc(isys_application::instance()->database_system->query('SELECT isys_mandator__apikey FROM isys_mandator WHERE isys_mandator__id = "' .
            (int)isys_application::instance()->session->get_mandator_id() . '";'));

        $rules = [
            'C__SYSTEM_SETTINGS__API_STATUS'                    => [
                'p_strTitle' => 'LC__SYSTEM_SETTINGS__API__ACTIVATE',
                'p_bChecked' => !!isys_tenantsettings::get('api.status', 0),
                'p_strValue' => 1
            ],
            'C__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY' => [
                'p_strTitle' => 'LC__SYSTEM_SETTINGS__API__AUTHENTICATED_USERS_ONLY',
                'p_bChecked' => !!isys_settings::get('api.authenticated-users-only', 1),
                'p_strValue' => 1
            ],
            'C__SYSTEM_SETTINGS__API__STRIP_TAGS' => [
                'p_strTitle' => 'LC__SYSTEM_SETTINGS__API__STRIP_TAGS',
                'p_bChecked' => !!isys_settings::get('api.strip-tags', 0),
                'p_strValue' => 1
            ],
            'C__SYSTEM_SETTINGS__LOG_LEVEL'               => [
                'p_strDbFieldNN' => 'LC__UNIVERSAL__DEACTIVATED',
                'p_strSelectedID' => isys_settings::get('api.log-level', \Monolog\Logger::WARNING),
                'p_arData' => [
                    \Monolog\Logger::ERROR   => 'ERROR',
                    \Monolog\Logger::WARNING => 'WARNING',
                    \Monolog\Logger::INFO    => 'INFO',
                    \Monolog\Logger::DEBUG   => 'DEBUG',
                ]
            ],
            'C__SYSTEM_SETTINGS__APIKEY'                        => [
                'p_strValue' => (isset($l_apikey_data['isys_mandator__apikey']) ? $l_apikey_data['isys_mandator__apikey'] : ''),
                'p_strClass' => 'input-mini'
            ]
        ];

        isys_application::instance()->template->smarty_tom_add_rules('tom.content.bottom.content', $rules);

        $index_includes['contentbottomcontent'] = "modules/system/api.tpl";
    }

    /**
     * Method for handling templates.
     *
     */
    public function handle_templates()
    {
        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
            isys_tenantsettings::set('cmdb.template.colors', (int)isset($_POST["colors"]));
            isys_tenantsettings::set('cmdb.template.color_value', '#' . trim($_POST["color_value"], "# \t\n\r\0\x0B"));
            isys_tenantsettings::set('cmdb.template.status', (int)isset($_POST["status"]));
            isys_tenantsettings::set('cmdb.template.show_assignments', (int)isset($_POST["assignments"]));

            isys_application::instance()->template->assign("C__TEMPLATE__STATUS", $_POST["status"])
                ->assign("C__TEMPLATE__SHOW_ASSIGNMENTS", $_POST["assignments"])
                ->assign("C__TEMPLATE__COLORS", $_POST["colors"])
                ->assign("C__TEMPLATE__COLOR", $_POST["color_value"]);
        } else {
            isys_application::instance()->template->assign("C__TEMPLATE__STATUS", C__TEMPLATE__STATUS)
                ->assign("C__TEMPLATE__SHOW_ASSIGNMENTS", C__TEMPLATE__SHOW_ASSIGNMENTS)
                ->assign("C__TEMPLATE__COLORS", C__TEMPLATE__COLORS)
                ->assign("C__TEMPLATE__COLOR", C__TEMPLATE__COLOR_VALUE);
        }

        if ($_GET['handle'] == 'templates') {
            isys_application::instance()->template->include_template('contentbottomcontent', 'modules/templates/settings__templates.tpl');
        }
    }

    /**
     * Method for handling the system overview.
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function handle_sysoverview()
    {
        global $index_includes, $g_absdir, $g_product_info, $g_dirs, $g_comp_database;

        isys_auth_system::instance()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW');

        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');
        $session = isys_application::instance()->container->get('session');

        // Find the operating system.
        $template
            ->assign('os', php_uname('s') . ' (' . PHP_OS . ')')
            ->assign('isWindows', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        $phpVersionStatus = 'ok';

        $phpVersion = getVersion(phpversion());

        if (version_compare($phpVersion, PHP_VERSION_MAXIMUM, '>')) {
            // above maximum.
            $phpVersionStatus = 'unsupported';
        } elseif (version_compare($phpVersion, PHP_VERSION_MINIMUM, '<')) {
            // Below minimum.
            $phpVersionStatus = 'failed';
        } elseif (version_compare($phpVersion, PHP_VERSION_DEPRECATED_BELOW, '<')) {
            // Deprecated.
            $phpVersionStatus = 'deprecated';
        } elseif (version_compare($phpVersion, PHP_VERSION_MINIMUM_RECOMMENDED, '<')) {
            // Not recommended.
            $phpVersionStatus = 'not_recommended';
        }

        $template
            ->assign("php_version", phpversion())
            ->assign("php_version_recommended", PHP_VERSION_MINIMUM_RECOMMENDED)
            ->assign("php_version_deprecated", PHP_VERSION_DEPRECATED_BELOW)
            ->assign("php_version_minimum", PHP_VERSION_MINIMUM)
            ->assign("idoit_version", $g_product_info)
            ->assign('php_version_status', $phpVersionStatus);

        /**
         * Database Version Check
         */
        try {
            // Get database version
            $result = $g_comp_database->query('SELECT VERSION() AS v;');
            $row = $g_comp_database->fetch_row_assoc($result);
            $rawDbVersion = $row['v'];

            $dbVersion = getVersion($rawDbVersion);

            // Detect MariaDB
            $isMariadb = stripos($rawDbVersion, 'maria') !== false;

            // Setting check related variables based on DBMS
            if ($isMariadb) {
                $dbTitle = 'MariaDB';
                $dbMinimumVersion = MARIADB_VERSION_MINIMUM;
                $dbMaximumVersion = MARIADB_VERSION_MAXIMUM;
                $dbRecommendedVersion = MARIADB_VERSION_MINIMUM_RECOMMENDED;
                $dbKbLink = '';
            } else {
                $dbTitle = 'MySQL';
                $dbMinimumVersion = MYSQL_VERSION_MINIMUM;
                $dbMaximumVersion = MYSQL_VERSION_MAXIMUM;
                $dbRecommendedVersion = MYSQL_VERSION_MINIMUM_RECOMMENDED;
                $dbKbLink = '';
            }

            $template
                ->assign('db_version', $dbVersion)
                ->assign('db_version_recommended', $dbRecommendedVersion)
                ->assign('db_title', $dbTitle);

            // Check version is supported
            if (!checkVersion($dbVersion, $dbMinimumVersion, $dbMaximumVersion)) {
                $template->assign('db_unsupported_version', true);
            } elseif ($isMariadb && version_compare($dbVersion, MARIADB_VERSION_DEPRECATED_BELOW, '<')) {
                $template->assign('db_deprecated_version', MARIADB_VERSION_DEPRECATED_BELOW);
            }
        } catch (Exception $exception) {
            // Version information was not detectable
            $template->assign(
                "sql_version_error",
                'Please notice that i-doit was not able to determine a valid MySQL/MariaDB version information. 
                     You can check your system to identify the problem or resume the installation process on your own risk. 
                     Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/display/en/System+Requirements">Knowledge Base</a>'
            );
        }

        if (file_exists($g_absdir . "/updates/classes/isys_update.class.php")) {
            include_once($g_absdir . "/updates/classes/isys_update.class.php");

            $l_upd = new isys_update();
            $l_info = $l_upd->get_isys_info();
            $l_new_update = $l_update_msg = '';

            try {
                try {
                    // @see  ID-6872  System settings can now provide a update-XML URL.
                    if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
                        $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
                    } else {
                        $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
                    }

                    $l_content = $l_upd->fetch_file($updateXmlUrl);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }

                $l_version = $l_upd->get_new_versions($l_content);

                if (is_array($l_version) && count($l_version) > 0) {
                    foreach ($l_version as $l_v) {
                        if ($l_info["revision"] < $l_v["revision"]) {
                            $l_new_update = $l_v;
                        }
                    }

                    if (!isset($l_new_update)) {
                        $l_update_msg = "You have got the latest version.";
                    }
                } else {
                    $l_update_msg = "Update check failed. Is the i-doit server not connected to the internet?";
                }

                $template
                    ->assign("update_msg", $l_update_msg)
                    ->assign("update", $l_new_update)
                    ->assign("idoit_info", $l_info);
            } catch (Exception $e) {
                $template
                    ->assign("update_error_msg", $e->getMessage())
                    ->assign("idoit_info", $l_info);
            }
        }

        // Directory Rights.
        $l_rights = [
            'source directory'             => [
                'chk'  => is_writable($g_absdir . '/src'),
                'dir'  => $g_absdir . '/src',
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'idoit directory'              => [
                'chk'  => is_writable($g_absdir),
                'dir'  => $g_absdir,
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'temp'                         => [
                'chk' => is_dir($g_absdir . '/temp') ? is_writable($g_absdir . '/temp') : mkdir($g_absdir . '/temp', 0775, true),
                'dir' => $g_absdir . '/temp',
                'msg' => 'WRITEABLE'
            ],
            'css'                          => [
                'chk' => is_dir($g_dirs['css_abs']),
                'dir' => $g_dirs['css_abs'],
                'msg' => 'VALID'
            ],
            'javascript'                   => [
                'chk' => is_dir($g_dirs['js_abs']),
                'dir' => $g_dirs['js_abs'],
                'msg' => 'VALID'
            ],
            'file upload'                  => [
                'chk' => is_dir($g_dirs['fileman']['target_dir']) ? is_writable($g_dirs['fileman']['target_dir']) : mkdir($g_dirs['fileman']['target_dir'], 0775, true),
                'dir' => $g_dirs['fileman']['target_dir'],
                'msg' => 'WRITEABLE'
            ],
            'image upload'                 => [
                'chk' => is_dir(isys_application::instance()->getImageDir()) ? is_writable(isys_application::instance()->getImageDir()) : mkdir(isys_application::instance()->getImageDir(), 0775, true),
                'dir' => isys_application::instance()->getImageDir(),
                'msg' => 'WRITEABLE'
            ],
            'default theme template cache' => [
                'chk' => is_dir($g_absdir . '/temp/smarty/compiled') ? is_writable($g_absdir . '/temp/smarty/compiled') : mkdir($g_absdir . '/temp/smarty/compiled', 0775, true),
                'dir' => $g_absdir . '/temp/smarty/compiled',
                'msg' => 'WRITEABLE'
            ],
            'default theme smarty cache'   => [
                'chk' => is_dir($g_absdir . '/temp/smarty/cache') ? is_writable($g_absdir . '/temp/smarty/cache') : mkdir($g_absdir . '/temp/smarty/cache', 0775, true),
                'dir' => $g_absdir . '/temp/smarty/cache',
                'msg' => 'WRITEABLE'
            ]
        ];

        // PHP checks.
        $l_php = [
            'max_execution_time'  => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'allow_url_fopen'     => ini_get('allow_url_fopen'),
            'max_input_vars'      => ini_get('max_input_vars'),
            'post_max_size'       => ini_get('post_max_size'),
            'file_uploads'        => ini_get('file_uploads'),
            'memory_limit'        => ini_get('memory_limit')
        ];

        // MySQL checks.
        $l_mysql = [
            'innodb_log_file_size'    => $database->get_config_value('innodb_log_file_size'),
            'tmp_table_size'          => $database->get_config_value('tmp_table_size'),
            'innodb_sort_buffer_size' => $database->get_config_value('innodb_sort_buffer_size'),
            'max_allowed_packet'      => $database->get_config_value('max_allowed_packet'),
            'join_buffer_size'        => $database->get_config_value('join_buffer_size'),
            'sort_buffer_size'        => $database->get_config_value('sort_buffer_size'),
            'max_heap_table_size'     => $database->get_config_value('max_heap_table_size'),
            'query_cache_limit'       => $database->get_config_value('query_cache_limit'),
            'query_cache_size'        => $database->get_config_value('query_cache_size'),
            'innodb_buffer_pool_size' => $database->get_config_value('innodb_buffer_pool_size'),
            'datadir'                 => $database->get_config_value('datadir')
        ];

        if (defined('C__MODULE__PRO') && class_exists('isys_module_licence')) {
            global $g_license_token;
            $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token);

            $objectCount = $licenseService->getTenants(true, [$session->get_mandator_id()])[0]['isys_mandator__license_objects'];
        } else {
            $dao = new isys_statistics_dao($database, new isys_cmdb_dao($database));
            $objectCount = $dao->count_objects();
        }

        $phpDependencies = isys_module_manager::instance()->getPackageDependencies('php');
        ksort($phpDependencies);
        $apacheDependencies = isys_module_manager::instance()->getPackageDependencies('apache');
        ksort($apacheDependencies);

        /* @see ID-7293 ZeitDifferenz */
        $result = $g_comp_database->query('SELECT CURRENT_TIMESTAMP() as `t`;');
        $row = $g_comp_database->fetch_row_assoc($result);
        $mySQLTime = $row['t'];
        $mySQLTimestamp = strtotime($mySQLTime);
        $phpTime = time();
        $timeDiffOut = [
            'php' => date('Y-m-d H:i:s', $phpTime),
            'mysql' => date('Y-m-d H:i:s', $mySQLTimestamp),
            'diff' => abs($phpTime - $mySQLTimestamp),
        ];


        $template
            ->assign('rights', $l_rights)
            ->assign('tenant', $session->get_mandator_name())
            ->assign('objectCount', $objectCount)
            ->assign('php', $l_php)
            ->assign('mysql', $l_mysql)
            ->assign('db_size', $this->get_database_size())
            ->assign('php_dependencies', $phpDependencies)
            ->assign('apache_dependencies', $apacheDependencies)
            ->assign('timeDiffOut', $timeDiffOut);

        $index_includes['contentbottomcontent'] = 'content/bottom/content/module__settings__overview.tpl';
    }

    /**
     * Retrieves the current database size
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_database_size()
    {
        $database = isys_application::instance()->container->get('database');

        $databaseSize = 0;
        $l_result = $database->query('SHOW TABLE STATUS');

        while ($l_row = $database->fetch_row_assoc($l_result)) {
            $databaseSize += $l_row['Data_length'] + $l_row['Index_length'];
        }

        $kiloByte = 1024;
        $gigaByte = $kiloByte ** 3;
        $megaByte = $kiloByte ** 2;

        if ($databaseSize >= $gigaByte) {
            return round(($databaseSize / $gigaByte), 2) . ' GB';
        }

        if ($databaseSize >= $megaByte) {
            return round(($databaseSize / $megaByte), 2) . ' MB';
        }

        if ($databaseSize >= $kiloByte) {
            return round(($databaseSize / $kiloByte), 2) . ' KB';
        }

        return $databaseSize . ' Byte';
    }

    /**
     * Method for handling the lock.
     */
    public function handle_lock()
    {
        global $index_includes;

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_list = null;

        $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__DELETE) {
            foreach ($_POST["id"] as $l_id) {
                $l_dao_lock->delete($l_id);
            }
        }

        if (count($_POST) > 0 && empty($_POST[C__GET__NAVMODE])) {
            if ($_POST['timeout'] > 0) {
                $l_timeout = (int) $_POST['timeout'];
            } else {
                $l_timeout = 120;
            }

            isys_tenantsettings::set('cmdb.registry.lock_dataset', isset($_POST['lock']) ? 1 : 0);
            isys_tenantsettings::set('cmdb.registry.lock_timeout', $l_timeout);

            isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));

            die();
        }

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__DELETE);

        $l_locks = $l_dao_lock->get_lock_information(null, null);

        if ($l_locks->num_rows() > 0) {
            $l_objList = new isys_component_list(null, $l_locks);

            $l_objList->set_row_modifier($this, 'modify_lock_row');

            $l_objList->config([
                'isys_cats_person_list__title' => $this->language->get('LC__CMDB__LOGBOOK__USER'),
                'isys_user_session__ip'        => $this->language->get('LC__CATP__IP__ADDRESS'),
                'isys_obj_type__title'         => $this->language->get('LC__CMDB__OBJTYPE'),
                'isys_obj__title'              => $this->language->get('LC__UNIVERSAL__TITLE'),
                'table_data'                   => $this->language->get('LC__UNIVERSAL__TOPIC'),
                'isys_lock__datetime'          => $this->language->get('LC__LOCKED__AT'),
                'expires'                      => $this->language->get('LC__EXPIRES_IN'),
            ], null, '[{isys_lock__id}]');

            $l_objList->createTempTable();
            $l_list = $l_objList->getTempTableHtml();
        }

        $l_dao_lock->delete_expired_locks();

        isys_application::instance()->template->assign('g_list', $l_list);

        $index_includes['contentbottomcontent'] = 'content/bottom/content/module__settings__lock.tpl';
    }

    /**
     * Modify the "lock" row.
     *
     * @param array $lockRow
     */
    public function modify_lock_row(&$lockRow)
    {
        $lockTimer = time() - strtotime($lockRow['isys_lock__datetime']);
        $remainingLockSeconds = (C__LOCK__TIMEOUT - $lockTimer);

        if ($remainingLockSeconds <= 0) {
            $lockRow['expires'] = 'already expired';
        } else {
            $lockRow['expires'] = $remainingLockSeconds . 's';
        }

        if ($lockRow['isys_lock__table_name'] && $lockRow['isys_lock__table_field'] && $lockRow['isys_lock__field_value']) {
            $value = is_numeric($lockRow['isys_lock__field_value'])
                ? $this->m_dao->convert_sql_id($lockRow['isys_lock__field_value'])
                : $this->m_dao->convert_sql_text($lockRow['isys_lock__field_value']);

            $sql = 'SELECT ' . $lockRow['isys_lock__table_name'] . '__title 
                FROM ' . $lockRow['isys_lock__table_name'] . ' 
                WHERE ' . $lockRow['isys_lock__table_field'] . ' = ' . $value . ' 
                LIMIT 1;';

            try {
                $title = $this->m_dao->retrieve($sql)->get_row_value($lockRow['isys_lock__table_name'] . '__title');

                if (!empty($title)) {
                    $lockRow['table_data'] = ($lockRow['isys_lock__table_label'] ? $this->language->get($lockRow['isys_lock__table_label']) . ': ' : '') . $title;
                }
            } catch (Exception $exception) {
                // Do nothing.
            }
        }
    }

    /**
     * Callback function for construction of breadcrumb navigation.
     *
     * @param   array &$p_gets
     *
     * @return  null
     */
    public function breadcrumb_get(&$p_gets)
    {
        return null;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Selcuk Kekec <skekec@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__SYSTEM_SETTINGS')) {
            return;
        }
        global $g_dirs;

        $i = 0;

        // Build tree.
        $l_root_node = $p_tree->add_node(
            $i,
            $p_parent,
            $this->language->get('LC__NAVIGATION__MAINMENU__TITLE_ADMINISTRATION'),
            '',
            '',
            'images/icons/silk/application_view_icons.png'
        );

        // Display: System settings
        $p_tree->add_node(++$i, $l_root_node, $this->language->get('LC__SETTINGS__SYSTEM__TITLE'), "?" . isys_glob_http_build_query([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                C__GET__SETTINGS_PAGE => C__SETTINGS_PAGE__SYSTEM,
                C__GET__TREE_NODE     => $i
            ]), '', 'images/icons/silk/cog.png');

        // Display: User settings.
        $l_usersettings_node = $p_tree->add_node(++$i, $l_root_node, $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS__USER'));
        $l_module_usersettings = new isys_module_user_settings();
        $l_module_usersettings->init(isys_module_request::get_instance());
        $l_module_usersettings->build_tree($p_tree, true, $l_usersettings_node);
        $l_system_auth = isys_auth_system::instance();

        // Display: Authorization module.
        if (defined('C__MODULE__AUTH')) {
            isys_module_auth::factory()
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_root_node);
        }

        // Display: CMDB Settings.
        $l_systemsettings_node = $p_tree->add_node(++$i, $l_root_node, $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS__CMDB'));

        // Global settings -> QCW
        if (class_exists('isys_module_quick_configuration_wizard') && defined('C__MODULE__QCW') && isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/QCW')) {
            $l_qcw_module = new isys_module_quick_configuration_wizard();
            $l_qcw_module->init(isys_module_request::get_instance());
            $l_qcw_module->build_tree($p_tree, true, $l_systemsettings_node);
        }

        // Global settings -> Groups (Software, Infrastructe, Others)
        $l_cmdb_dao = new isys_cmdb_dao(isys_application::instance()->database);
        $l_groups = $l_cmdb_dao->get_object_group_by_id();
        $l_tmpget[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECTTYPE;
        $l_allowed_objtype_groups = isys_auth_cmdb_object_types::instance()
            ->get_allowed_objecttype_group_configs();

        if (defined('C__MODULE__CMDB')) {
            if ($l_allowed_objtype_groups || is_array($l_allowed_objtype_groups)) {
                $l_systemsettings_groups = $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__CMDB__OBJTYPE__CONFIGURATION_MODUS'));
                while ($l_grp = $l_groups->get_row()) {
                    if (is_array($l_allowed_objtype_groups) && !in_array($l_grp['isys_obj_type_group__id'], $l_allowed_objtype_groups)) {
                        continue;
                    }

                    $l_tmpget[C__CMDB__GET__OBJECTGROUP] = $l_grp['isys_obj_type_group__id'];
                    $p_tree->add_node(++$i, $l_systemsettings_groups, $this->language->get($l_grp['isys_obj_type_group__title']), isys_helper_link::create_url([
                        C__GET__MODULE_ID         => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID     => C__MODULE__CMDB,
                        C__GET__SETTINGS_PAGE     => 'objectTypeListConfig',
                        C__CMDB__GET__OBJECTGROUP => $l_grp['isys_obj_type_group__id'],
                        C__CMDB__GET__VIEWMODE    => C__CMDB__VIEW__LIST_OBJECTTYPE,
                        C__GET__TREE_NODE         => $i,
                    ]), null, 'images/icons/silk/box.png');
                }
            }
        }

        // Global settings -> CMDB-Status
        if (defined('C__MODULE__PRO') && C__MODULE__PRO && $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/CMDBSTATUS')) {
            $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'), isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                C__GET__SETTINGS_PAGE => C__SETTINGS_PAGE__CMDB_STATUS,
                C__GET__TREE_NODE     => $i
            ]), '', 'images/icons/silk/color_swatch.png');
        }

        if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMPROPERTIES')) {
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                $this->m_additional_options['custom_properties']['text'],
                self::generate_link('custom_properties', $i),
                '',
                'images/icons/silk/database_gear.png'
            );
        }

        // @see  ID-6307 Adding auth check.
        if (isys_module_cmdb::getAuth()->is_allowed_to(isys_auth::VIEW, 'object_browser_configuration')) {
            // @see  ID-677 Customizable object browser.
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                $this->m_additional_options['customizeObjectBrowser']['text'],
                self::generate_link('customizeObjectBrowser', $i),
                '',
                'images/icons/silk/application_form_magnify.png'
            );
        }

        // Global settings -> Custom Categories
        if (defined('C__MODULE__CUSTOM_FIELDS') && class_exists('isys_module_custom_fields')) {
            if (class_exists('isys_module_custom_fields')) {
                if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/CUSTOMFIELDS')) {
                    $l_module_custom_cat = new isys_module_custom_fields();
                    $l_module_custom_cat->build_tree($p_tree, true, $l_systemsettings_node, $i);
                }
            }

            if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/RELATIONSHIPTYPES')) {
                $p_tree->add_node(
                    ++$i,
                    $l_systemsettings_node,
                    $this->m_additional_options['relation_types']['text'],
                    self::generate_link('relation_types', $i),
                    '',
                    'images/icons/silk/database_gear.png'
                );
            }

            if ($l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/ROLESADMINISTRATION')) {
                $p_tree->add_node(
                    ++$i,
                    $l_systemsettings_node,
                    $this->m_additional_options['roles_administration']['text'],
                    self::generate_link('roles_administration', $i),
                    '',
                    'images/icons/silk/database_gear.png'
                );
            }
        }

        // Global settings -> Logbook
        if (isys_auth_logbook::instance()
            ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK')) {
            $l_systemsettings_logbook_node = $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__LOGBOOK'));

            isys_factory::get_instance('isys_module_logbook')
                ->build_tree($p_tree, true, $l_systemsettings_logbook_node);
        }

        // Global settings -> Dialog-Admin
        if (isys_auth_dialog_admin::instance()
                ->is_allowed_to(isys_auth::VIEW, 'TABLE') || isys_auth_dialog_admin::instance()
                ->is_allowed_to(isys_auth::VIEW, 'CUSTOM')) {
            $l_dialogadmin_node = $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN'));

            isys_factory::get_instance('isys_module_dialog_admin')
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_dialogadmin_node);
        }

        // Global settings -> System settings
        if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/SYSTEMSETTING')) {
            $p_tree->add_node(
                ++$i,
                $l_systemsettings_node,
                $this->m_additional_options['system_settings']['text'],
                self::generate_link('system_settings', $i),
                '',
                'images/icons/silk/cog_edit.png'
            );
        }

        if (defined('C__MODULE__QRCODE') && class_exists('isys_module_qrcode') && (isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'qr_config/global') || isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'qr_config/objtype'))) {
            isys_factory::get_instance('isys_module_qrcode')
                ->init(isys_module_request::get_instance())
                ->build_tree($p_tree, true, $l_systemsettings_node);
        }

        // Initialize rights
        $l_jdisc_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'JDISC');
        $l_jsonrpcapi_rights = $l_system_auth->is_allowed_to(isys_auth::VIEW, 'JSONRPCAPI');
        $l_loginventory_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LOGINVENTORY');
        $l_ocs_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS');
        $l_hinventory_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'HINVENTORY');
        $l_ldap_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP');
        $l_tts_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'TTS');
        $l_systemtools_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS');
        $l_licence_rights = $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS');
        $l_swapci_rights = false;
        $l_nagios_rights = false;
        $l_check_mk_rights = false;
        $l_events_rights = false;

        if (defined('C__MODULE__SWAPCI') && isys_module_manager::instance()
                ->is_active('swapci')) {
            if (class_exists('isys_auth_swapci')) {
                $l_swapci_rights = isys_auth_swapci::instance()
                    ->has_any_rights_in_module();
            }
        }

        // Rights: Nagios
        if (defined('C__MODULE__NAGIOS')) {
            if (class_exists('isys_auth_nagios')) {
                $l_nagios_rights = isys_auth_nagios::instance()->has_any_rights_in_module();
            } else {
                $l_nagios_rights = true;
            }
        }

        // Rights: CheckMK
        if (defined('C__MODULE__CHECK_MK') && isys_module_manager::instance()->is_active('check_mk')) {
            if (class_exists('isys_auth_check_mk')) {
                $l_check_mk_rights = isys_auth_check_mk::instance()->has_any_rights_in_module();
            } else {
                $l_check_mk_rights = true;
            }
        }

        // Rights: Events
        if (defined('C__MODULE__EVENTS') && isys_module_manager::instance()
                ->is_active('events')) {
            if (class_exists('isys_auth_events')) {
                $l_events_rights = isys_auth_events::instance()
                    ->has_any_rights_in_module();
            } else {
                $l_events_rights = true;
            }
        }

        // Check for rights
        if ($l_jdisc_rights || $l_jsonrpcapi_rights || $l_loginventory_rights || $l_ocs_rights || $l_ldap_rights || $l_tts_rights || $l_systemtools_rights ||
            $l_licence_rights || $l_swapci_rights || $l_nagios_rights || $l_check_mk_rights || $l_events_rights || $l_hinventory_rights) {
            // Display: Interfaces / Externals.
            $l_iext_node = $p_tree->add_node(++$i, 0, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE'));
            $l_import_node = $p_tree->add_node(++$i, $l_iext_node, $this->language->get('LC__MODULE__IMPORT'), '', '', $g_dirs['images'] . 'icons/silk/database_copy.png');

            if (class_exists('isys_module_swapci')) {
                // Swap-CI module.
                if ($l_swapci_rights && isys_module_swapci::DISPLAY_IN_SYSTEM_MENU === true) {
                    $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__MODULE__SWAPCI'));

                    $l_swapci = isys_factory::get_instance('isys_module_swapci');
                    $l_swapci->init(isys_module_request::get_instance());
                    $l_swapci->build_tree($p_tree, true, $i);
                }
            }

            $l_monitoring = null;
            // Monitoring module.
            if (defined('C__MODULE__MONITORING') && class_exists('isys_module_monitoring') && isys_module_monitoring::DISPLAY_IN_SYSTEM_MENU === true) {
                $l_monitoring = $p_tree->add_node(++$i, $l_iext_node, $this->language->get('LC__MONITORING'), '', null, $g_dirs['images'] . 'icons/silk/monitor.png');

                isys_factory::get_instance('isys_module_monitoring')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            }

            // Check MK module.
            if ($l_check_mk_rights && class_exists('isys_module_check_mk') && isys_module_check_mk::DISPLAY_IN_SYSTEM_MENU === true) {
                // The Check_MK tree node is no longer used - The export is now located underneath the "Monitoring" module.
                isys_factory::get_instance('isys_module_check_mk')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            }

            // CMK2 module
            if (defined('C__MODULE__CMK2') && class_exists('isys_module_cmk2') && isys_module_manager::instance()->is_active('cmk2')) {
                (new isys_module_cmk2())
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            }

            // Interfaces / Externals -> JDISC
            if (defined('C__MODULE__JDISC') && class_exists('isys_module_jdisc') && isys_module_jdisc::DISPLAY_IN_SYSTEM_MENU === true && $l_jdisc_rights) {
                $p_tree->add_node(++$i, $l_import_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__JDISC'));

                isys_factory::get_instance('isys_module_jdisc')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $i);
            }

            // Interfaces / Externals -> JSON-RPC API
            if (isys_module_manager::instance()
                    ->is_active('api') && $l_jsonrpcapi_rights) {
                $apiParentNode = $p_tree->add_node(
                    ++$i,
                    $l_iext_node,
                    $this->m_additional_options['api']['text'],
                    null,
                    null,
                    $this->m_additional_options['api']['icon']
                );

                if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'JSONRPCAPI/GLOBAL')) {
                    $p_tree->add_node(
                        ++$i,
                        $apiParentNode,
                        $this->language->get('LC__MODULE__JSON_RPCAPI__COMMON_SETTINGS'),
                        self::generate_link('api', $i),
                        null,
                        $this->m_additional_options['api']['icon']
                    );
                }
                if ($l_system_auth->is_allowed_to(isys_auth::VIEW, 'JSONRPCAPI/OBJTYPE')) {
                    $p_tree->add_node(
                        ++$i,
                        $apiParentNode,
                        $this->m_additional_options['apiCategoryConfiguration']['text'],
                        self::generate_link('apiCategoryConfiguration', $i),
                        null,
                        $this->m_additional_options['apiCategoryConfiguration']['icon']
                    );
                }
            }

            // Events
            if (class_exists('isys_module_events') && isys_module_events::DISPLAY_IN_SYSTEM_MENU === true && $l_events_rights) {
                isys_factory::get_instance('isys_module_events')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_systemsettings_node);
            }

            // Interfaces / Externals -> LOGINventory
            if (class_exists('isys_module_loginventory') && isys_module_loginventory::DISPLAY_IN_SYSTEM_MENU === true && $l_loginventory_rights) {
                if (defined('C__MODULE__LOGINVENTORY')) {
                    isys_factory::get_instance('isys_module_loginventory')
                        ->init(isys_module_request::get_instance())
                        ->build_tree($p_tree, true, $l_import_node);
                }
            }

            // Interfaces / Externals -> OCS NG
            if ($l_ocs_rights && defined('C__MODULE__IMPORT')) {
                $l_iext_ocs_node = $p_tree->add_node(++$i, $l_import_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS'));

                // Interfaces / Externals -> OCS NG -> Configuration
                $l_qparams = [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__IMPORT,
                    'what'                => "ocsconfig",
                    C__GET__TREE_NODE     => $i + 1
                ];

                $p_tree->add_node(
                    ++$i,
                    $l_iext_ocs_node,
                    $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__CONFIGURATION'),
                    isys_helper_link::create_url($l_qparams),
                    '',
                    $g_dirs['images'] . 'icons/ocs-inventory.png',
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS/OCSCONFIG')
                );

                // Interfaces / Externals -> OCS NG -> Databases
                $l_qparams['what'] = 'ocsdb';
                $l_qparams[C__GET__TREE_NODE] = $i + 1;

                $p_tree->add_node(
                    ++$i,
                    $l_iext_ocs_node,
                    $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__DATABASE'),
                    isys_helper_link::create_url($l_qparams),
                    '',
                    $g_dirs['images'] . 'icons/ocs-inventory.png',
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'OCS/OCSDB')
                );
            }

            if ($l_hinventory_rights) {
                $l_iext_hinventory_node = $p_tree->add_node(++$i, $l_import_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY'));

                $l_qparams = [
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    'what'                => "hinventory",
                    C__GET__TREE_NODE     => $i + 1,
                    C__GET__SETTINGS_PAGE => 'config'
                ];

                // Interfaces / Externals -> H-Inventory -> Configuration
                $p_tree->add_node(
                    ++$i,
                    $l_iext_hinventory_node,
                    $this->m_additional_options['hinventory']['text'],
                    isys_helper_link::create_url($l_qparams),
                    '',
                    $this->m_additional_options['hinventory']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'HINVENTORY/CONFIG')
                );
            }

            // Interfaces / Externals -> Object Matching
            $p_tree->add_node(
                ++$i,
                $l_import_node,
                $this->m_additional_options['object_matching']['text'],
                self::generate_link('object_matching', $i),
                null,
                $this->m_additional_options['object_matching']['icon'],
                0,
                '',
                '',
                isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJECT_MATCHING')
            );

            // Interfaces / Externals -> LDAP
            if (defined('C__MODULE__LDAP') && class_exists('isys_module_ldap') && isys_module_ldap::DISPLAY_IN_SYSTEM_MENU === true && $l_ldap_rights) {
                $l_iext_ldap = $p_tree->add_node(++$i, $l_iext_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP'));

                isys_factory::get_instance('isys_module_ldap')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_iext_ldap);
            }

            // Interfaces / Externals -> Nagios
            if (defined('C__MODULE__NAGIOS') && class_exists('isys_module_nagios') && isys_module_nagios::DISPLAY_IN_SYSTEM_MENU === true && $l_nagios_rights) {
                isys_factory::get_instance('isys_module_nagios')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_monitoring ?: $l_iext_node);
            }

            // Interfaces / Externals -> RFC
            if (defined('C__MODULE__RFC') && class_exists('isys_module_rfc')) {
                if (isys_module_rfc::DISPLAY_IN_SYSTEM_MENU === true) {
                    $l_iext_rfc = $p_tree->add_node(++$i, $l_iext_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__RFC'), '', null, null, 0, '', '');

                    $l_rfc_module = new isys_module_rfc();
                    $l_rfc_module->init(isys_module_request::get_instance());
                    $l_rfc_module->build_tree($p_tree, true, $l_iext_rfc);
                }
            }

            // Interfaces / Externals -> TTS
            if (defined('C__MODULE__TTS') && class_exists('isys_module_tts') && isys_module_tts::DISPLAY_IN_SYSTEM_MENU === true && $l_tts_rights) {
                $l_iext_tts_node = $p_tree->add_node(++$i, $l_iext_node, $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__TTS'));

                isys_factory::get_instance('isys_module_tts')
                    ->init(isys_module_request::get_instance())
                    ->build_tree($p_tree, true, $l_iext_tts_node);
            }

            // Display: System-Tools.
            if ($l_systemtools_rights) {
                $l_systemtools_node = $p_tree->add_node(++$i, $l_root_node, $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS'));

                // System-Tools -> Cache
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['cache']['text'],
                    self::generate_link('cache', $i),
                    null,
                    $this->m_additional_options['cache']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE')
                );

                // System-Tools -> Validation
                if (defined('C__MODULE__PRO') && defined('C__MODULE__CMDB')) {
                    $p_tree->add_node(++$i, $l_systemsettings_node, $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION'), isys_helper_link::create_url([
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                        C__GET__SETTINGS_PAGE => 'validation',
                        C__GET__TREE_NODE     => $i
                    ]), '', 'images/icons/silk/page_red.png', 0, '', '', $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/VALIDATION'));
                }

                // System-Tools -> System-Overview
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['sysoverview']['text'],
                    self::generate_link('sysoverview', $i),
                    null,
                    $this->m_additional_options['sysoverview']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW')
                );

                // System-Tools -> i-doit Update
                $p_tree->add_node(
                    ++$i,
                    $l_systemtools_node,
                    $this->m_additional_options['idoit_update']['text'],
                    $this->m_additional_options['idoit_update']['link'],
                    null,
                    $this->m_additional_options['idoit_update']['icon'],
                    0,
                    '',
                    '',
                    $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/IDOITUPDATE')
                );
            }

            if (defined('C__ENABLE__LICENCE') && C__ENABLE__LICENCE) {
                // Display: Licence.
                if ($l_licence_rights) {
                    $l_lic = $p_tree->add_node(++$i, 0, $this->language->get('LC__WIDGET__EVAL_OVERVIEW'));

                    // Licence -> Overview
                    $p_tree->add_node(
                        ++$i,
                        $l_lic,
                        $this->language->get('LC__UNIVERSAL__LICENE_OVERVIEW'),
                        isys_helper_link::create_url([
                            C__GET__MODULE_ID => C__MODULE__SYSTEM,
                            C__GET__TREE_NODE => $i,
                            'handle'          => 'licence_overview'
                        ]),
                        null,
                        'images/icons/silk/page_white_stack.png',
                        ($_GET['handle'] == 'licence_overview') ? 1 : 0,
                        '',
                        '',
                        $l_system_auth->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS/OVERVIEW')
                    );
                }
            }
        }
    }

    /**
     * @param $p_request
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        global $index_includes;

        // Unpack request package.
        $l_gets = $this->m_userrequest->get_gets();
        $l_tplclass = $this->m_userrequest->get_template();
        $l_tree = $this->m_userrequest->get_menutree();

        $this->m_dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));

        $this->build_tree($l_tree, false, -1);
        $l_tplclass->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));

        try {
            if (isset($l_gets[C__GET__MODULE_SUB_ID]) && is_numeric($l_gets[C__GET__MODULE_SUB_ID])) {
                isys_module_request::get_instance()
                    ->get_module_manager()
                    ->get_by_id($l_gets[C__GET__MODULE_SUB_ID])
                    ->get_object()
                    ->start();
            } else {
                // If option is not set, set 'overview' as default.
                if (!isset($l_gets['what']) && !isset($l_gets['handle'])) {
                    $l_gets['what'] = 'overview';
                }

                // Call option-specific method.
                if (isset($l_gets['what']) && is_array($this->m_additional_options)) {
                    if (array_key_exists($l_gets['what'], $this->m_additional_options)) {
                        $l_dat = $this->m_additional_options[$l_gets['what']];
                        if (is_array($l_dat)) {
                            if (method_exists($this, $l_dat['func'])) {
                                call_user_func([
                                    $this,
                                    $l_dat['func']
                                ]);
                            }
                        }
                    }
                } else {
                    if (isset($l_gets['handle'])) {
                        if (method_exists($this, 'handle_' . $l_gets['handle'])) {
                            call_user_func([
                                $this,
                                'handle_' . $l_gets['handle']
                            ]);
                        }
                    }
                }
            }
        } catch (isys_exception_general $e) {
            throw $e;
        } catch (isys_exception_auth $e) {
            $l_tplclass->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    }

    /**
     * Deletes all objects with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL) {
            die('We will not purge all objects with status \'normal\'.');
        }

        $l_dao = new isys_cmdb_dao(isys_application::instance()->container->get('database'));

        $l_res = $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;');
        $l_count = $l_res->count();

        if ($l_count > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_dao->delete_object_and_relations($l_row['isys_obj__id']);
            }

            return $l_count;
        }

        return 0;
    }

    /**
     * Lists all objects with the given status (for system module -> cache).
     *
     * @global  array   $g_dirs
     *
     * @param   integer $p_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function list_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        global $g_dirs;

        $l_return = [];
        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);
        $l_quickinfo = new isys_ajax_handler_quick_info();

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj__sysid, isys_obj_type__title
			FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_quickinfo->get_quick_info(
                    $l_row['isys_obj__id'],
                    '<img src="' . $g_dirs['images'] . 'icons/silk/information.png" class="vam" /> <span class="vam">' .
                    $this->language
                        ->get($l_row['isys_obj_type__title']) . ' > ' . $l_row['isys_obj__title'] . '</span>',
                    C__LINK__OBJECT
                );
            }
        }

        return $l_return;
    }

    /**
     * @param string $type
     */
    public function cleanup_other($type)
    {
        $mapping = [
            'DeleteExportedCheckMkTags' => DeleteExportedCheckMkTags::class,
            'MigrateDbObjectsToCategory' => MigrateDbObjectsToCategory::class,
            'RemoveOrphanedCustomCategoryData' => RemoveOrphanedCustomCategoryData::class,
            'TenantDatabaseEncryption' => TenantDatabaseEncryption::class
        ];

        if (isset($mapping[$type])) {
            (new $mapping[$type])->process();
        }
    }

    /**
     * Deletes all categorie entries with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_categories($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL) {
            die('i-doit will not remove all category data with status "normal"!');
        }

        $l_log = isys_factory_log::get_instance('category_cleanup');

        $l_count_all = 0;
        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);
        $l_catg_row = [];
        $ignoreCategoryTypes = [isys_cmdb_dao_category::TYPE_VIEW, isys_cmdb_dao_category::TYPE_FOLDER];

        $l_arr_skip = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => filter_defined_constants([
                'C__CATG__LOGICAL_UNIT',
                'C__CATG__ASSIGNED_LOGICAL_UNIT',
                'C__CATG__OBJECT',
                'C__CATG__VIRTUAL_AUTH',
                'C__CATG__VIRTUAL_SUPERNET',
                'C__CATG__NET_CONNECTIONS_FOLDER',
                'C__CATG__WORKFLOW',
                'C__CATG__NAGIOS_SERVICE_REFS_TPL_BACKWARDS',
                'C__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS',
                'C__CATG__NAGIOS_REFS_SERVICES_BACKWARDS',
                'C__CATG__NAGIOS_REFS_SERVICES',
                'C__CATG__NAGIOS_APPLICATION_FOLDER',
                'C__CATG__NAGIOS_APPLICATION_REFS_NAGIOS_SERVICE',
            ]),
            C__CMDB__CATEGORY__TYPE_SPECIFIC => filter_defined_constants([
                'C__CATS__CONTRACT_ALLOCATION',
                'C__CMDB__SUBCAT__WS_ASSIGNMENT', // @todo  Remove in i-doit 1.12
                'C__CATS__WS_ASSIGNMENT',
                'C__CMDB__SUBCAT__FILE_VERSIONS', // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_VERSIONS',
                'C__CMDB__SUBCAT__FILE_OBJECTS',  // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_OBJECTS',
                'C__CMDB__SUBCAT__FILE_ACTUAL',   // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_ACTUAL'
            ])
        ];

        // Remove Custom category entries
        $l_res = $l_dao->get_all_catg_custom(null, ' AND isysgui_catg_custom__list_multi_value > 0');
        while ($l_row = $l_res->get_row()) {
            // Remove custom fields
            $l_res2 = $l_dao->retrieve('SELECT * FROM isys_catg_custom_fields_list WHERE
              isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $l_dao->convert_sql_id($l_row['isysgui_catg_custom__id']) . '
              AND (isys_catg_custom_fields_list__status = ' . $l_dao->convert_sql_int($p_type) . ' OR isys_catg_custom_fields_list__status IS NULL)');
            $l_data_ids = [];
            while ($l_row2 = $l_res2->get_row()) {
                if (!isset($l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']])) {
                    $l_log->info('Deleting custom fields data entry #' . $l_row2['isys_catg_custom_fields_list__data__id'] . ' in "isys_catg_custom_fields_list" ...');
                    $l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']] = true;
                }

                $l_dao->delete_entry($l_row2['isys_catg_custom_fields_list__id'], 'isys_catg_custom_fields_list');
            }
            $l_count_all += count($l_data_ids);
            unset($l_data_ids);
        }

        $l_res = $l_dao->get_all_catg(
            null,
            ' AND isysgui_catg__list_multi_value > 0 AND isysgui_catg__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_GLOBAL]) . ')'
        );

        while ($l_row = $l_res->get_row()) {
            $l_class = $l_row['isysgui_catg__class_name'];
            $l_table = (substr($l_row['isysgui_catg__source_table'], -5) == '_list') ? $l_row['isysgui_catg__source_table'] : $l_row['isysgui_catg__source_table'] . '_list';
            $categoryType = $l_row['isysgui_catg__type'];

            if (class_exists($l_class) && strpos($l_table, '_2_') == false && !in_array($categoryType, $ignoreCategoryTypes)) {
                $l_catg_res = call_user_func([
                    $l_class,
                    'instance'
                ], isys_application::instance()->database)->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_catg_res->num_rows();
                if ($l_count > 0) {
                    while ($l_catg_row = $l_catg_res->get_row()) {
                        $l_log->info('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_catg_row[$l_table . '__id'], $l_table);
                    }

                    $l_count_all += $l_count;
                }
            }
        }

        $l_res = $l_dao->get_all_cats(
            null,
            ' AND isysgui_cats__list_multi_value > 0 AND isysgui_cats__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_SPECIFIC]) . ')'
        );

        while ($l_row = $l_res->get_row()) {
            $l_table = $l_row['isysgui_cats__source_table'];
            $l_class = $l_row['isysgui_cats__class_name'];
            $categoryType = $l_row['isysgui_cats__type'];

            if (class_exists($l_class) && strpos($l_table, '_2_') == false && !in_array($categoryType, $ignoreCategoryTypes)) {
                $l_cats_res = call_user_func([
                    $l_class,
                    'instance'
                ], isys_application::instance()->database)->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_cats_res->num_rows();

                if ($l_count > 0) {
                    while ($l_cats_row = $l_cats_res->get_row()) {
                        $l_log->info('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_cats_row[$l_table . '__id'], $l_table);
                    }

                    $l_count_all += $l_count;
                }
            }
        }

        $l_log->flush_log();

        return $l_count_all;
    }

    /**
     * Method which removes duplicate "obj-type to category" assignments. This sometimes happens by accident.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_category_assignments()
    {
        echo 'Removing duplicate category assignments...<br />';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->get_object_types();

        while ($l_row = $l_res->get_row()) {
            $l_sql = 'SELECT isys_obj_type_2_isysgui_catg__id, isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id, count(isys_obj_type_2_isysgui_catg__isysgui_catg__id) AS cnt
				FROM isys_obj_type_2_isysgui_catg
				WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_row['isys_obj_type__id']) . '
				GROUP BY isys_obj_type_2_isysgui_catg__isysgui_catg__id
				HAVING cnt > 1
				ORDER BY cnt;';

            $l_duplicate_res = $l_dao->retrieve($l_sql);

            if ($l_duplicate_res->num_rows() > 0) {
                echo 'Removing duplicates from obj-type "' . $this->language
                        ->get($l_row['isys_obj_type__title']) . '" (' . $l_row['isys_obj_type__id'] . ')<br />';

                while ($l_duplicate_row = $l_duplicate_res->get_row()) {
                    // With this SQL we remove all duplicates but one.
                    $l_remove_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg
						WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isysgui_catg__id']) . '
						AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isys_obj_type__id']) . '
						AND isys_obj_type_2_isysgui_catg__id != ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__id']) . ';';

                    $l_dao->update($l_remove_sql);
                }

                $l_dao->apply_update();
            }
        }

        echo 'Done!';
    }

    /**
     * Method which removes duplicate single-value categorie entries. This sometimes happens by accident.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_duplicate_single_value_entries()
    {
        echo 'Deleting duplicate single-value categorie entries...<br />';

        // Here we define some categories, which are not allowed to be modified.
        $l_blacklist_catg = filter_array_by_keys_of_defined_constants([
            'C__CATG__NETWORK_PORT'          => true,
            'C__CATG__NETWORK_PORT_OVERVIEW' => true,
            'C__CATG__STORAGE'               => true,
            'C__CATG__CUSTOM_FIELDS'         => true,
            'C__CATG__OPERATING_SYSTEM'      => true,
        ]);
        $l_deleted_entries = 0;

        /**
         * @var isys_cmdb_dao $l_dao
         */
        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);
        $l_res = $l_dao->get_all_catg(null, ' AND isysgui_catg__list_multi_value = 0 AND isysgui_catg__type = \'' . isys_cmdb_dao_category::TYPE_EDIT . '\'');

        // Global categories
        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                // We use numeric values for better performance.
                if (isset($l_blacklist_catg[$l_row['isysgui_catg__id']])) {
                    continue;
                }
                $l_src = $l_row['isysgui_catg__source_table'];
                $l_className = $l_row['isysgui_catg__class_name'];
                if (class_exists($l_className)) {
                    $l_categoryDao = $l_className::instance(isys_application::instance()->database);
                    // Check if table really exists
                    if ($this->does_table_exists($l_src . '_list')) {
                        $l_cat_sql = 'SELECT ' . $l_src . '_list__id as id, ' . $l_src . '_list__isys_obj__id as obj_id, count(' . $l_src . '_list__isys_obj__id) as cnt
                            FROM ' . $l_src . '_list
                            GROUP BY ' . $l_src . '_list__isys_obj__id
                            HAVING cnt > 1';
                        $l_cat_res = $l_dao->retrieve($l_cat_sql);
                        $l_amount_deletion = $l_cat_res->num_rows();
                        if ($l_amount_deletion) {
                            echo 'Deleting duplicate entries in global category "' . $this->language
                                    ->get($l_row['isysgui_catg__title']) . '"... ';
                            while ($l_cat_row = $l_cat_res->get_row()) {
                                $rankingResult = $l_categoryDao->rank_record($l_cat_row['id'], C__CMDB__RANK__DIRECTION_DELETE, ($l_src . '_list'), null, true);
                                if ($rankingResult) {
                                    echo 'Done!<br />';
                                } else {
                                    echo 'Error!<br />';
                                }
                            }
                            $l_deleted_entries += $l_amount_deletion;
                        }
                    }
                }
            }
        }

        $l_blacklist_cats = filter_array_by_keys_of_defined_constants([
            'C__CATS__NET_IP_ADDRESSES' => true
        ]);

        // Specific categories
        $l_res = $l_dao->get_all_cats(null, ' AND isysgui_cats__list_multi_value = 0 AND isysgui_cats__type = \'' . isys_cmdb_dao_category::TYPE_EDIT . '\'');
        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                // We use numeric values for better performance.
                if (isset($l_blacklist_cats[$l_row['isysgui_cats__id']])) {
                    continue;
                }
                $l_src = $l_row['isysgui_cats__source_table'];
                $l_className = $l_row['isysgui_cats__class_name'];
                if (class_exists($l_className)) {
                    $l_categoryDao = $l_className::instance(isys_application::instance()->database);
                    // Check if table really exists
                    if ($this->does_table_exists($l_src)) {
                        $l_cat_sql = 'SELECT ' . $l_src . '__id as id, ' . $l_src . '__isys_obj__id as obj_id, count(' . $l_src . '__isys_obj__id) as cnt
                            FROM ' . $l_src . '
                            GROUP BY ' . $l_src . '__isys_obj__id
                            HAVING cnt > 1';

                        $l_cat_res = $l_dao->retrieve($l_cat_sql);
                        $l_amount_deletion = $l_cat_res->num_rows();
                        if ($l_amount_deletion) {
                            echo 'Deleting duplicate entries in specific category "' . $this->language
                                    ->get($l_row['isysgui_cats__title']) . '"... ';
                            while ($l_cat_row = $l_cat_res->get_row()) {
                                $rankingResult = $l_categoryDao->rank_record($l_cat_row['id'], C__CMDB__RANK__DIRECTION_DELETE, $l_src, null, true);
                                if ($rankingResult) {
                                    echo 'Done!<br />';
                                } else {
                                    echo 'Error!<br />';
                                }
                            }
                            $l_deleted_entries += $l_amount_deletion;
                        }
                    }
                }
            }
        }

        // Custom categories
        $l_res = $l_dao->get_all_catg_custom(null, ' AND isysgui_catg_custom__list_multi_value = 0');
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_custom_fields_id = $l_row['isysgui_catg_custom__id'];
                $l_sql = 'SELECT *, COUNT(isys_catg_custom_fields_list__isys_obj__id) AS cnt, GROUP_CONCAT(isys_catg_custom_fields_list__data__id) AS ids
                    FROM (SELECT *  FROM `isys_catg_custom_fields_list` WHERE `isys_catg_custom_fields_list__isysgui_catg_custom__id` = ' .
                    $l_dao->convert_sql_id($l_custom_fields_id) . '
                    GROUP BY isys_catg_custom_fields_list__data__id ORDER BY isys_catg_custom_fields_list__data__id ASC) AS customf GROUP BY isys_catg_custom_fields_list__isys_obj__id HAVING cnt > 1';

                $l_res2 = $l_dao->retrieve($l_sql);
                if ($l_res2->num_rows() > 0) {
                    echo 'Deleting duplicate entries in custom category "' . $this->language
                            ->get($l_row['isysgui_catg_custom__title']) . '"... <br />';
                    while ($l_row2 = $l_res2->get_row()) {
                        $l_data_ids = explode(',', $l_row2['ids']);
                        // remove last entry
                        array_pop($l_data_ids);
                        $l_amount_deletion = count($l_data_ids);

                        if ($l_amount_deletion > 0) {
                            // Delete all duplicate entries
                            $l_delete = 'DELETE FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__data__id IN (' . implode(',', $l_data_ids) . ')' .
                                ' AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $l_dao->convert_sql_id($l_custom_fields_id);
                            $l_dao->update($l_delete);
                        }
                        $l_deleted_entries += $l_amount_deletion;
                    }
                }
            }
            $l_dao->apply_update();
        }

        if ($l_deleted_entries) {
            echo '(' . $l_deleted_entries . ') Duplicate single value entries found and deleted. <br />';
        } else {
            echo 'No duplicate single value entries found.<br />';
        }
    }

    /**
     * Helper function which checks if a table really exists
     *
     * @param  string $p_table
     *
     * @return boolean
     */
    public function does_table_exists($p_table)
    {
        $database = isys_application::instance()->container->get('database');

        return $database->num_rows($database->query('SHOW TABLES LIKE "' . $database->escape_string($p_table) . '";')) > 0;
    }

    /**
     * This function deletes all unassigned relation entries and objects
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function cleanup_unassigned_relations()
    {
        $l_dao_rel = isys_cmdb_dao_relation::instance(isys_application::instance()->database);
        $l_stats = $l_dao_rel->delete_dead_relations();
        $l_dead_relation_objects = $l_stats[isys_cmdb_dao_relation::C__DEAD_RELATION_OBJECTS];

        if ($l_dead_relation_objects > 0) {
            //echo '(' . $l_amount . ') unassigned relation objects deleted.<br>';
            echo $this->language->get('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__OBJECTS_DELETED', [$l_dead_relation_objects]) . '<br />';
        } else {
            echo $this->language->get('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__NO_OBJECTS_DELETED');
        }
    }

    /**
     * This function renews the relation titles of all relation objects.
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function renew_relation_titles()
    {
        $l_sql = 'SELECT * FROM isys_catg_relation_list INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id';
        $l_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_relation', isys_application::instance()->database);
        $l_changes = false;

        $l_res = $l_dao->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_obj = $l_row['isys_catg_relation_list__isys_obj__id'];
            $l_master = $l_row['isys_catg_relation_list__isys_obj__id__master'];
            $l_slave = $l_row['isys_catg_relation_list__isys_obj__id__slave'];
            $l_relation_type = $l_row['isys_catg_relation_list__isys_relation_type__id'];
            $l_old_name = $l_row['isys_obj__title'];

            $l_dao->update_relation_object($l_obj, $l_master, $l_slave, $l_relation_type);

            $l_new_name = $l_dao->get_obj_name_by_id_as_string($l_row['isys_obj__id']);

            if ($l_old_name != $l_new_name) {
                $l_changes = true;
                echo "Relation title <strong>'" . $l_old_name . "'</strong> changed to <strong>'" . $l_new_name . "'</strong> (" . $l_obj . ").<br />";
            }
        }

        if (!$l_changes) {
            echo "No broken relation title found.";
        }
    }

    /**
     * Method for refilling empty SYS-IDs
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function refill_empty_sysids()
    {
        $l_sql = 'SELECT * FROM isys_obj
		    LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__sysid = ""
			OR isys_obj__sysid IS NULL;';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_new_sysid = $l_dao->generate_sysid($l_row['isys_obj_type__id'], $l_row['isys_obj__id']);

                echo 'The ' . $this->language
                        ->get($l_row['isys_obj_type__title']) . ' "' . $l_row['isys_obj__title'] . '" (#' . $l_row['isys_obj__id'] . ') has no SYS-ID. Filling it with: ' .
                    $l_new_sysid . '<br />';

                $l_sql = 'UPDATE isys_obj SET isys_obj__sysid = ' . $l_dao->convert_sql_text($l_new_sysid) . ' WHERE isys_obj__id = ' .
                    $l_dao->convert_sql_id($l_row['isys_obj__id']) . ';';

                if (!$l_dao->update($l_sql)) {
                    throw new isys_exception_cmdb("Updating the object with a new SYS-ID failed!");
                }
            }
        } else {
            echo 'No empty SYS-IDs found.';
        }
    }

    /**
     * Temporary solution in cleaning the auth table
     *
     * @return mixed
     */
    public function cleanup_auth()
    {
        $l_modules = isys_module_manager::instance()
            ->get_modules();
        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_system_module = false;

        while ($l_row = $l_modules->get_row()) {
            $l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_row['isys_module__id']);

            if ($l_auth_instance) {
                if (get_class($l_auth_instance) == 'isys_auth_system') {
                    if (!$l_system_module) {
                        $l_system_module = true;
                        $l_row['isys_module__id'] = defined_or_default('C__MODULE__SYSTEM');
                    } else {
                        continue;
                    }
                }

                $l_auth_module_obj = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);
            } else {
                continue;
            }

            // Get auth methods
            $l_auth_module_methods = $l_auth_module_obj->get_auth_methods();

            foreach ($l_auth_module_methods as $l_method => $l_content) {
                // Check if cleanup exists
                if (!array_key_exists('cleanup', $l_content)) {
                    continue;
                }
                $l_found = false;
                foreach ($l_content['cleanup'] as $l_table => $l_search_field) {
                    // Prepare search query
                    $l_query = 'SELECT * FROM ' . $l_table . ' WHERE ' . $l_search_field . ' = ';
                    // Prepare delete query
                    $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id = ';
                    // Get paths
                    $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' .
                        $l_dao->convert_sql_id($l_row['isys_module__id']) . ' ' . 'AND isys_auth__path LIKE ' . $l_dao->convert_sql_text(strtoupper($l_method) . '/%');

                    $l_res = $l_dao->retrieve($l_auth_query);
                    if (!$l_found && $l_res->num_rows() > 0) {
                        while ($l_row2 = $l_res->get_row()) {
                            $l_search_query = $l_query;
                            $l_delete_query2 = $l_delete_query;
                            $l_path_arr = explode('/', $l_row2['isys_auth__path']);
                            $l_field_value = $l_path_arr[1];

                            if ($l_field_value == isys_auth::WILDCHAR) {
                                continue;
                            }

                            $l_search_query .= (is_numeric($l_field_value)) ? $l_dao->convert_sql_id($l_field_value) : $l_dao->convert_sql_text($l_field_value);
                            $l_search_res = $l_dao->retrieve($l_search_query);
                            if ($l_search_res->num_rows() == 0) {
                                // Field value does not exist complete the delete query
                                $l_delete_query2 .= $l_dao->convert_sql_id($l_row2['isys_auth__id']);
                            } else {
                                // Field value found
                                unset($l_delete_query2);
                                $l_found = true;
                            }
                        }
                    }
                }
                if (isset($l_delete_query2)) {
                    $l_dao->update($l_delete_query2);
                }
            }
        }

        return $l_dao->apply_update();
    }

    /**
     * Handles relationship types
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function handle_relation_types()
    {
        isys_auth_system_globals::instance()
            ->relationshiptypes(isys_auth::VIEW);

        $database = isys_application::instance()->container->get('database');

        $relationTypeModel = RelationType::instance($database);
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($database);
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        switch ($l_navmode) {
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                try {
                    // At first we update all the "weighting" data.
                    $l_res = $l_dao_relation->retrieve('SELECT isys_relation_type__id FROM isys_relation_type;');

                    while ($l_row = $l_res->get_row()) {
                        $l_dao_relation->update_relation_type_weighting($l_row['isys_relation_type__id'], $l_posts['relation_weighting'][$l_row['isys_relation_type__id']]);
                    }

                    if ($l_posts['delRelTypes'] != '') {
                        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance($database);
                        $l_del_rel_types = explode(',', $l_posts['delRelTypes']);
                        $l_contacts_res = $l_dao_contact->get_contact_objects_by_tag(
                            null,
                            null,
                            'AND isys_contact_tag__isys_relation_type__id IN (' . $l_posts['delRelTypes'] . ')'
                        );
                        $l_contacts_role_already_updated = [];

                        while ($l_row = $l_contacts_res->get_row()) {
                            $l_relation_type = defined_or_default('C__RELATION_TYPE__USER');
                            $l_master = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave = $l_row['isys_obj__id'];
                            $l_catg_contact_id = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_contact_role = $l_row['isys_contact_tag__id'];

                            if (!in_array($l_contact_role, $l_contacts_role_already_updated)) {
                                // Set contact role with relation type contact user
                                $l_dao_contact->update_contact_tag($l_contact_role, null, $l_relation_type);
                                $l_contacts_role_already_updated[] = $l_contact_role;
                            }

                            // Update all contacts with the default relation type for contacts
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        }

                        $l_dao_relation->remove_relation_type($l_del_rel_types);
                    }

                    if (isset($l_posts['relation_title']) && is_array($l_posts['relation_title'])) {
                        foreach ($l_posts['relation_title'] as $relationTypeId => $title) {
                            $relationTypeModel->save($relationTypeId, [
                                'title'       => $title,
                                'titleMaster' => $l_posts['relation_title_master'][$relationTypeId],
                                'titleSlave'  => $l_posts['relation_title_slave'][$relationTypeId],
                                'type'        => $l_posts['relation_type'][$relationTypeId],
                                'default'     => $l_posts['relation_direction'][$relationTypeId]
                            ]);
                        }
                    }

                    if (isset($l_posts['new_relation_title'])) {
                        foreach ($l_posts['new_relation_title'] as $index => $title) {
                            $titleMaster = $l_posts['new_relation_title_master'][$index];
                            $titleSlave = $l_posts['new_relation_title_slave'][$index];

                            // Relation types need a title, master title and slave title.
                            if (empty($title) || empty($titleMaster) || empty($titleSlave)) {
                                continue;
                            }

                            $type = $l_posts['new_relation_type'][$index];
                            $direction = $l_posts['new_relation_direction'][$index];
                            $weighting = $l_posts['new_relation_weighting'][$index];

                            /*
                             * LF: Changed these lines since "$l_posts['new_relation_title']" can only contain NEW relation types.
                             * Otherwise it would have been possible to change core relation types.
                             */
                            $relationTypeModel->save(null, [
                                'title'       => $title,
                                'titleMaster' => $titleMaster,
                                'titleSlave'  => $titleSlave,
                                'type'        => $type,
                                'default'     => $direction,
                                'category'    => 'C__CATG__CUSTOM_FIELDS',
                                'editable'    => true,
                                'status'      => C__RECORD_STATUS__NORMAL,
                                'weightingId' => $weighting
                            ]);
                        }
                    }

                    isys_notify::success($this->language
                        ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                } catch (isys_exception_general $e) {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        $l_weighting_dao = isys_factory_cmdb_dialog_dao::get_instance('isys_weighting', $database);
        $l_weighting_dialog = isys_factory::get_instance('isys_smarty_plugin_f_dialog');
        $l_relation_types = $l_dao_relation->get_relation_types_as_array();
        $l_relation_type = [];

        isys_glob_sort_array_by_column($l_relation_types, 'title_lang');

        foreach ($l_relation_types as &$l_relation_type) {
            $l_weighting = $l_weighting_dao->get_data($l_relation_type['weighting']);

            $l_dialog_params = [
                'name'            => 'relation_weighting[' . $l_relation_type['id'] . ']',
                'p_strTable'      => 'isys_weighting',
                'p_strClass'      => 'input-mini',
                'p_strSelectedID' => $l_relation_type['weighting'],
                'order'           => 'isys_weighting__id',
                'p_bSort'         => false,
                'p_bDbFieldNN'    => true
            ];

            $l_relation_type['weighting'] = $l_weighting_dialog->navigation_edit(isys_module_request::get_instance()
                ->get_template(), $l_dialog_params);
            $l_relation_type['weighting_text'] = $this->language
                ->get($l_weighting['isys_weighting__title']);
            $l_relation_type['type'] = $l_relation_type["type"];
        }

        $l_dialog_params = [
            'name'            => 'new_relation_weighting[]',
            'p_strTable'      => 'isys_weighting',
            'p_strClass'      => 'input-mini',
            'p_strSelectedID' => $l_relation_type['weighting'],
            'order'           => 'isys_weighting__id',
            'p_bSort'         => false,
            'p_bDbFieldNN'    => true
        ];

        isys_module_request::get_instance()
            ->get_template()
            ->assign('weighting_tpl', $l_weighting_dialog->navigation_edit(isys_module_request::get_instance()
                ->get_template(), $l_dialog_params))
            ->assign('relation_types', $l_relation_types)
            ->include_template('contentbottomcontent', 'modules/system/relation_types.tpl');
    }

    /**
     *
     * @throws  isys_exception_cmdb
     */
    public function handle_roles_administration()
    {
        isys_auth_system_globals::instance()
            ->rolesadministration(isys_auth::VIEW);

        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);
        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->database);
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        $l_condition = " AND isys_relation_type__const IS NULL OR isys_relation_type__category = " . $l_dao_relation->convert_sql_text('C__CATG__CONTACT');
        $l_relation_types = $l_dao_relation->get_relation_types_as_array(null, C__RELATION__IMPLICIT, $l_condition);

        switch ($l_navmode) {
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            case C__NAVMODE__SAVE:

                $l_new_contact_role_titles = $l_posts['new_role_title'];
                $l_new_contact_role_relations = $l_posts['new_role_relation_type'];
                $l_delete_contact_roles = $l_posts['delRoles'];
                $l_update_contact_roles = $l_posts['updRoles'];
                try {
                    $l_contact_tag_res = $l_dao_contact->get_contact_tag_data();
                    $l_contact_tag_arr = [];
                    while ($l_contact_tag_row = $l_contact_tag_res->get_row()) {
                        $l_contact_tag_arr[$l_contact_tag_row['isys_contact_tag__id']] = $l_contact_tag_row['isys_contact_tag__title'];
                    }

                    if (count($l_new_contact_role_titles) > 0) {
                        foreach ($l_new_contact_role_titles as $l_key => $l_role_title) {
                            if (!in_array($l_role_title, $l_contact_tag_arr)) {
                                $l_dao_contact->add_contact_tag($l_role_title, $l_new_contact_role_relations[$l_key]);
                            } elseif (($l_contact_tag_id = array_search($l_role_title, $l_contact_tag_arr))) {
                                $l_update_contact_roles .= ',' . $l_contact_tag_id;
                                $l_posts['role_relation_type'][$l_contact_tag_id] = $l_new_contact_role_relations[$l_key];
                                $l_posts['role_title'][$l_contact_tag_id] = $l_role_title;
                            }
                        }
                    }

                    if ($l_delete_contact_roles != '') {
                        $l_delete_contact_roles_as_array = explode(',', $l_delete_contact_roles);
                        $l_contacts_res = $l_dao_contact->get_contact_objects_by_tag(null, $l_delete_contact_roles_as_array);
                        while ($l_row = $l_contacts_res->get_row()) {
                            $l_relation_type = defined_or_default('C__RELATION_TYPE__USER');
                            $l_master = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave = $l_row['isys_obj__id'];
                            $l_catg_contact_id = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        }
                        $l_dao_contact->delete_contact_tag($l_delete_contact_roles_as_array);
                    }

                    if ($l_update_contact_roles != '') {
                        $l_update_contact_roles = explode(',', $l_update_contact_roles);
                        $l_contact_update_relation_types = $l_posts['role_relation_type'];
                        $l_contact_update_title = $l_posts['role_title'];
                        foreach ($l_update_contact_roles as $l_contact_tag_id) {
                            $l_contact_tag_relation_type_id = $l_contact_update_relation_types[$l_contact_tag_id];
                            $l_contact_tag_title = (isset($l_contact_update_title[$l_contact_tag_id])) ? $l_contact_update_title[$l_contact_tag_id] : null;

                            $l_dao_contact->update_contact_tag($l_contact_tag_id, $l_contact_tag_title, $l_contact_tag_relation_type_id);
                        }
                    }
                    isys_notify::success($this->language
                        ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                } catch (isys_exception_general $e) {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        isys_module_request::get_instance()
            ->get_template()
            ->assign('contact_roles', $l_dao_contact->get_contact_tag_data())
            ->assign('relation_types', $l_relation_types)
            ->include_template('contentbottomcontent', 'modules/system/roles_administration.tpl');
    }

    /**
     * Handle custom properties
     *
     * @throws \isys_exception_auth
     * @throws \isys_exception_general
     */
    public function handle_custom_properties()
    {
        global $index_includes;

        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMPROPERTIES');

        /**
         * @var $l_dao_relation isys_cmdb_dao_category_g_relation
         * @var $l_dao_contact  isys_cmdb_dao_category_g_contact
         */
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        // Navmode-Handling
        switch ($l_navmode) {
            // EDIT
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
            // SAVE
            case C__NAVMODE__SAVE:
                if (isset($l_posts['data'])) {
                    $l_dao = new isys_cmdb_dao_custom_property(isys_application::instance()->database);

                    foreach ($l_posts['data'] as $l_category_const => $l_properties) {
                        foreach ($l_properties as $l_property_identifier => $l_property_value) {
                            $l_data_section = "";

                            if ($l_property_value || $l_property_value === '') {
                                $l_data_section = [
                                    C__PROPERTY__INFO => [
                                        C__PROPERTY__INFO__TITLE => $l_property_value
                                    ]
                                ];
                            }

                            $l_dao->create([
                                'cats'     => constant($l_category_const),
                                'property' => $l_property_identifier,
                                'data'     => $l_data_section,
                                'const'    => $l_category_const
                            ]);
                        }
                    }
                }
            // no break
            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        // Collect data
        $l_category_store = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => [
                'C__CATS__PERSON_MASTER'
            ],
        ];

        $l_data = [];

        foreach ($l_category_store as $l_category_type => $l_categories) {
            foreach ($l_categories as $l_category_const) {
                $l_category_dao = isys_factory_cmdb_category_dao::get_instance_by_id($l_category_type, constant($l_category_const), isys_application::instance()->database);
                // Check for method
                if (method_exists($l_category_dao, 'custom_properties')) {
                    $l_custom_properties = $l_category_dao->get_custom_properties();

                    // Are there any properties
                    if (is_array($l_custom_properties) && count($l_custom_properties)) {
                        $l_data[$l_category_const] = [
                            'title' => $l_category_dao->get_category_by_const_as_string($l_category_const),
                            'data'  => $l_custom_properties,
                        ];
                    }
                }
            }
        }

        // Assign data
        isys_module_request::get_instance()
            ->get_template()
            ->assign('data', $l_data);

        // Set template
        $index_includes['contentbottomcontent'] = "modules/system/custom_properties.tpl";
    }

    /**
     * Method for setting the relation weightings of all relations to the default setting.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function set_default_relation_priorities()
    {
        $l_dao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->database);

        $l_relation_types = $l_dao->get_relation_types_as_array();

        foreach ($l_relation_types as $l_relation_type_id => $l_relation_type) {
            echo 'Setting the priority of all relations (of type "' . $this->language
                    ->get($l_relation_type['title']) . '") to ' . $l_relation_type['weighting'] . '<br />';

            $l_sql = 'UPDATE isys_catg_relation_list
				SET isys_catg_relation_list__isys_weighting__id = ' . $l_dao->convert_sql_id($l_relation_type['weighting']) . '
				WHERE isys_catg_relation_list__isys_relation_type__id = ' . $l_dao->convert_sql_id($l_relation_type_id) . ';';

            if (!($l_dao->update($l_sql) && $l_dao->apply_update())) {
                isys_notify::error(isys_application::instance()->database->get_last_error_as_string(), ['sticky' => true]);
            }
        }
    }

    /**
     * Method for handline the cache actions.
     *
     * @global  array  $index_includes
     * @global  array  $g_db_system
     * @global  string $g_absdir
     */
    private function handle_cache()
    {
        global $index_includes, $g_db_system, $g_absdir;

        isys_auth_system_tools::instance()->cache(isys_auth::EXECUTE);

        $database = isys_application::instance()->container->get('database');
        $session = isys_application::instance()->container->get('session');
        $dao = isys_component_dao::instance($database);

        if ($_GET['ajax']) {
            switch ($_GET['do']) {
                case 'db_optimize':
                    echo '<table class="listing"><thead><tr><th>Table</th><th>Operation</th><th>Status</th></tr></thead><tbody>';

                    $result = $dao->retrieve('SHOW TABLES;');

                    while ($row = $result->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW)) {
                        $table = $row[0];

                        $optimizeResult = $dao->retrieve("OPTIMIZE TABLE {$table};")->get_row();

                        echo "<tr><td>{$optimizeResult['Table']}</td><td>{$optimizeResult['Op']}</td><td>{$optimizeResult['Msg_text']}</td></tr>";
                    }

                    echo '</tbody></table>';
                    break;

                case 'db_defrag':
                    echo '<table class="listing"><thead><tr><th>Table</th><th>Operation</th><th>Status</th></tr></thead><tbody>';

                    $result = $dao->retrieve('SHOW FULL TABLES;');

                    while ($row = $result->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW)) {
                        [$tableName, $tableType] = $row;

                        if ($tableType === 'VIEW') {
                            continue;
                        }

                        if ($dao->update("ALTER TABLE {$tableName} ENGINE = INNODB;") && $dao->apply_update()) {
                            $defragResult = 'OK';
                        } else {
                            $defragResult = 'DEFRAG NOT POSSIBLE';
                        }

                        echo "<tr><td>{$tableName}</td><td>defrag</td><td>{$defragResult}</td></tr>";
                    }

                    echo '</tbody></table>';
                    break;

                case 'export':
                    if (!$session->is_logged_in()) {
                        die('Youre not logged in! Please login first!');
                    }

                    $mysqlDumpExecutable = $_POST['mysqldump'];

                    if (file_exists($mysqlDumpExecutable)) {
                        $exportSystemDatabase = isset($_POST['system']) && $_POST['system'];
                        $exportTenantDatabase = isset($_POST['mandator']) && $_POST['mandator'];

                        if ($exportSystemDatabase) {
                            $exportName = isys_glob_get_temp_dir() . $g_db_system['name'] . '-' . time() . '.sql';

                            $arguments = [
                                '--add-drop-table',
                                '--quick',
                                '--dump-date',
                                '--complete-insert',
                                '--host=' . $g_db_system['host'],
                                '--user=' . $g_db_system['user'],
                                '--password=' . $g_db_system['pass'],
                                '--port=' . $g_db_system['port'],
                                ' ' . $g_db_system['name'],
                                '> ' . $exportName
                            ];

                            exec($mysqlDumpExecutable . ' ' . implode(' ', $arguments));

                            if (file_exists($exportName)) {
                                $exportFilesize = filesize($exportName);

                                if ($exportFilesize > 256) {
                                    $exportFilesizeMegabyte = round(isys_convert::memory($exportFilesize, C__MEMORY_UNIT__MB, C__CONVERT_DIRECTION__BACKWARD), 3);

                                    echo 'System dump saved to ' . $exportName . ', written <code>' . $exportFilesizeMegabyte . 'MB</code><br />';
                                } else {
                                    @unlink($exportName);
                                    echo '<p class="text-red">System database could not be dumped, please check permissions.</p>';
                                }
                            } else {
                                echo '<p class="text-red">System database could not be dumped, please check permissions.</p>';
                            }
                        }

                        if ($exportTenantDatabase) {
                            $exportName = isys_glob_get_temp_dir() . $database->get_db_name() . '-' . time() . '.sql';

                            $arguments = [
                                '--add-drop-table',
                                '--quick' ,
                                '--dump-date',
                                '--comments=false',
                                '--complete-insert',
                                '--host=' . $database->get_host(),
                                '--user=' . $database->get_user(),
                                '--password=' . $database->get_pass(),
                                '--port=' . $database->get_port(),
                                ' ' . $database->get_db_name(),
                                '> ' . $exportName
                            ];

                            exec($mysqlDumpExecutable . ' ' . implode(' ', $arguments));

                            if (file_exists($exportName)) {
                                $exportFilesize = filesize($exportName);

                                if ($exportFilesize > 256) {
                                    $exportFilesizeMegabyte = round(isys_convert::memory($exportFilesize, C__MEMORY_UNIT__MB, C__CONVERT_DIRECTION__BACKWARD), 3);

                                    echo 'Tenant dump saved to ' . $exportName . ', written <code>' . $exportFilesizeMegabyte . 'MB</code><br />';
                                } else {
                                    @unlink($exportName);
                                    echo '<p class="text-red">Tenant database could not be dumped, please check permissions.</p>';
                                }
                            } else {
                                echo '<p class="text-red">Tenant database could not be dumped, please check permissions.</p>';
                            }
                        }

                        echo '<p>Connect to your i-doit instance to copy the files. Direct downloads are not possible for security reasons.</p>';
                    } else {
                        echo 'Mysqldump binary not found under: ' . $mysqlDumpExecutable . '.<br /> This executable can normally be found inside the bin directory of your MySQL installation.';
                    }

                    break;

                case 'db_location':
                    $daoLocation = isys_cmdb_dao_location::instance($database);
                    $daoLocation->_location_fix();
                    $stats = $daoLocation->getLocationFixStats();

                    echo $this->language->get('LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS_DONE') . '<br />';

                    foreach ($stats as $key => $counter) {
                        if ($counter) {
                            echo $key . ': ' . $counter . '<br />';
                        }
                    }

                    break;

                case 'db_relation':
                    $l_dao = isys_cmdb_dao_relation::instance($database);

                    try {
                        // Delete dead relation objects
                        $l_dao->delete_dead_relations();

                        // Regenerate relation objects
                        $l_dao->regenerate_relations();
                        echo $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_SUCCESS');
                    } catch (Exception $e) {
                        echo $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_ERROR');
                    }

                    break;

                case 'db_set_lists_to_wildcardfilter':
                case 'db_set_lists_to_rowclick':
                    $l_sql = 'SELECT isys_obj_type_list__id, isys_obj_type_list__table_config, isys_obj__title, isys_obj_type__title
                        FROM isys_obj_type_list
                        INNER JOIN isys_obj ON isys_obj__id = isys_obj_type_list__isys_obj__id
                        INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_list__isys_obj_type__id;';
                    $l_dao = isys_cmdb_dao::instance($database);
                    $l_res = $l_dao->retrieve($l_sql);
                    $counter = count($l_res);

                    if ($_GET["do"] === 'db_set_lists_to_wildcardfilter') {
                        $l_method = 'setFilterWildcard';
                        $l_lc_response = 'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER_RESPONSE';
                    } else {
                        $l_method = 'setRowClickable';
                        $l_lc_response = 'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_RESPONSE';
                    }

                    if ($counter) {
                        while ($l_row = $l_res->get_row()) {
                            /**
                             * @var  $l_config  \idoit\Module\Cmdb\Model\Ci\Table\Config
                             */
                            $l_config = unserialize($l_row['isys_obj_type_list__table_config']);

                            if ($l_config === false || !is_object($l_config) || (is_object($l_config) && !is_a($l_config, '\idoit\Module\Cmdb\Model\Ci\Table\Config'))) {
                                $counter--;
                                echo $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_ERROR', [
                                        $this->language->get($l_row['isys_obj_type__title']),
                                        $l_row['isys_obj__title']
                                    ]) . '<br />';
                            } else {
                                $l_config->$l_method(true);

                                $l_sql = 'UPDATE isys_obj_type_list
                                    SET isys_obj_type_list__table_config = ' . $l_dao->convert_sql_text(serialize($l_config)) . '
                                    WHERE isys_obj_type_list__id = ' . $l_dao->convert_sql_id($l_row['isys_obj_type_list__id']) . ';';

                                $l_dao->update($l_sql);
                            }
                        }
                    }

                    echo $this->language->get($l_lc_response, $counter);

                    break;

                case "db_properties":
                    $l_dao = new isys_cmdb_dao_category_property($database);
                    $propertyResults = $l_dao->rebuild_properties();

                    foreach ($propertyResults as $key => $values) {
                        if ($key === 'missing_classes') {
                            continue;
                        }

                        echo '<strong>' . ucfirst($key) . '</strong><br /><br />';

                        $i = 0;
                        $results = [];

                        if (!is_array($values)) {
                            continue;
                        }

                        foreach ($values as $daoClassName => $propertyValues) {
                            /** @var isys_cmdb_dao_category $daoClassName */
                            try {
                                $title = $this->language->get($daoClassName::instance($database)->getCategoryTitle());
                            } catch (Exception $e) {
                                $title = '';
                            }

                            if ($daoClassName === 'isys_cmdb_dao_category_g_custom_fields') {
                                $title = $this->language->get('LC__CMDB__CATG__CUSTOM_CATEGORY');
                            }

                            $propretyCount = count($propertyValues);
                            $suffix = "{$key} {$propretyCount} properties";

                            if (empty($title)) {
                                echo "<strong><code>{$daoClassName}</code></strong> {$suffix}<br />";
                            } else {
                                $results[mb_strtolower($title) . '-' . $i] = "<strong>{$title}</strong> (<code>{$daoClassName}</code>) {$suffix}";
                            }

                            $i++;
                        }

                        ksort($results);

                        echo implode('<br />', $results) . '<br /><br />';
                    }

                    if (is_array($propertyResults['missing_classes']) && count($propertyResults['missing_classes'])) {
                        echo '<strong>Missing classes</strong><br /><br />' . implode('<br />', $propertyResults['missing_classes']);
                    }

                    break;

                case 'db_cleanup_objects':
                    $l_count = 0;

                    try {
                        switch ($_GET['param']) {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the objects.
                                $l_count = $this->cleanup_objects($_GET['param']);
                                break;

                            case 'all':
                                $l_count = $this->cleanup_objects(C__RECORD_STATUS__BIRTH) +
                                    $this->cleanup_objects(C__RECORD_STATUS__ARCHIVED) +
                                    $this->cleanup_objects(C__RECORD_STATUS__DELETED);
                        }
                    } catch (Exception $e) {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    }

                    echo sprintf($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DONE'), $l_count);

                    echo '<hr class="mb5 mt5" />';
                    break;

                case 'db_list_objects':
                    $l_objects = [];

                    try {
                        switch ($_GET['param']) {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the objects.
                                $l_objects = $this->list_objects($_GET['param']);
                                break;

                            case 'all':
                                $l_objects = array_merge(
                                    $this->list_objects(C__RECORD_STATUS__BIRTH),
                                    $this->list_objects(C__RECORD_STATUS__ARCHIVED),
                                    $this->list_objects(C__RECORD_STATUS__DELETED)
                                );
                        }
                    } catch (Exception $e) {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    }

                    if (count($l_objects) > 0) {
                        echo '<ul><li>' . implode('</li><li>', $l_objects) . '</li></ul>';
                    } else {
                        echo isys_tenantsettings::get('gui.empty_value', '-');
                    }

                    break;

                case 'db_cleanup_categories':
                    $l_count = 0;

                    try {
                        switch ($_GET['param']) {
                            default:
                            case C__RECORD_STATUS__BIRTH:
                            case C__RECORD_STATUS__ARCHIVED:
                            case C__RECORD_STATUS__DELETED:
                                // Method for cleaning up the category content.
                                $l_count = $this->cleanup_categories($_GET['param']);
                                break;

                            case 'all':
                                $l_count = $this->cleanup_categories(C__RECORD_STATUS__BIRTH) + $this->cleanup_categories(C__RECORD_STATUS__ARCHIVED) +
                                    $this->cleanup_categories(C__RECORD_STATUS__DELETED);
                        }
                    } catch (Exception $e) {
                        echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                    }

                    echo sprintf($this->language->get("LC__SYSTEM__CLEANUP_OBJECTS_DONE_CATEGORIES"), $l_count);

                    break;

                case 'cleanup_other':
                    $this->cleanup_other($_GET['param']);
                    break;

                case 'db_cleanup_cat_assignments':
                    $this->cleanup_category_assignments();
                    break;

                case 'db_cleanup_duplicate_sv_entries':
                    $this->cleanup_duplicate_single_value_entries();
                    break;

                case 'db_cleanup_unassigned_relations':
                    $this->cleanup_unassigned_relations();
                    break;

                case 'db_renew_relation_titles':
                    $this->renew_relation_titles();
                    break;

                case 'db_refill_empty_sysids':
                    $this->refill_empty_sysids();
                    break;

                case 'db_set_default_relation_priorities':
                    $this->set_default_relation_priorities();
                    break;

                case 'cache_system':
                    global $g_dirs;

                    // Removing isys_cache values
                    isys_cache::keyvalue()->flush();

                    $l_deleted = $l_undeleted = 0;

                    // Deleting contents of the temp directory.
                    isys_glob_delete_recursive($g_dirs["temp"], $l_deleted, $l_undeleted, (ENVIRONMENT === 'development'));
                    echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', count($l_deleted) . ' System');

                    isys_component_signalcollection::get_instance()->emit('system.afterFlushSystemCache');

                    break;
                case 'cache_auth':
                    // Invalidate auth cache
                    $affectedCacheFiles = isys_auth::invalidateCache();

                    echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', count($affectedCacheFiles));
                    break;

                case 'cache_quick_info':
                    try {
                        if (!QuickInfo::instance($database)->truncate()) {
                            throw new Exception();
                        }

                        echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE_MESSAGE');
                    } catch (Exception $e) {
                        echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE_MESSAGE_FAILURE') . ' ' . $e->getMessage();
                    }
                    break;

                case 'search_index':
                    try {
                        $searchEngine = new Mysql($database);

                        $collector = new CategoryCollector($database, [], []);

                        $dispatcher = isys_application::instance()->container->get('event_dispatcher');

                        // @see  ID-6535  Don't output anything because it will take double the time.
                        $manager = new Manager($searchEngine, $dispatcher);
                        $manager->addCollector($collector, 'categoryCollector');
                        $manager->clearIndex();
                        $manager->generateIndex();

                        echo 'Search index was created!';
                    } catch (Exception $e) {
                        echo 'An error occured: ' . $e->getMessage();
                    }
                    break;
            }

            die;
        }

        $l_mysqldump = system_which('mysqldump');

        if (!$l_mysqldump) {
            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                if (file_exists('c:\\programme\\mysql5\\bin\\mysqldump.exe')) {
                    $l_mysqldump = 'c:\\programme\\mysql5\\bin\\mysqldump.exe';
                } elseif (file_exists('c:\\programme\\mysql\\bin\\mysqldump.exe')) {
                    $l_mysqldump = 'c:\\programme\\mysql\\bin\\mysqldump.exe';
                } elseif (file_exists('c:\\programme\\mysql5.0\\bin\\mysqldump.exe')) {
                    $l_mysqldump = 'c:\\programme\\mysql5.0\\bin\\mysqldump.exe';
                } elseif (file_exists('c:\\windows\\system32\\mysqldump.exe')) {
                    $l_mysqldump = 'c:\\windows\\system32\\mysqldump.exe';
                } else {
                    $l_mysqldump = 'mysqldump.exe';
                }
            } else {
                $l_which = shell_exec('which mysqldump');

                if (strpos($l_which, 'mysqldump') !== false) {
                    $l_mysqldump = $l_which;
                } elseif (file_exists('/usr/bin/mysqldump')) {
                    $l_mysqldump = '/usr/bin/mysqldump';
                } elseif (file_exists('/usr/local/mysql/bin/mysqldump')) {
                    $l_mysqldump = '/usr/local/mysql/bin/mysqldump';
                } else {
                    $l_mysqldump = 'mysqldump';
                }
            }
        }

        // Cache buttons.
        $l_cache_buttons = [
            'LC__SETTINGS__SYSTEM__FLUSH_ALL_CACHE'  => [
                'onclick' => 'window.flush_cache(true);',
                'style'   => 'background-color:#eee;',
                'css'     => 'mb15'
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_SYS_CACHE'  => [
                'onclick' => "window.flush_database('cache_system', '', this);",
                'css'     => 'cache-button'
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_TPL_CACHE'  => [
                'onclick' => "window.flush_cache('IDOIT_DELETE_TEMPLATES_C', this);",
                'css'     => 'cache-button'
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_AUTH_CACHE' => [
                'onclick' => "window.flush_database('cache_auth', '', this);",
                'css'     => 'cache-button'
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE' => [
                'onclick' => "window.flush_database('cache_quick_info', '', this);",
                'css'     => 'cache-button'
            ]
        ];

        if (defined('C__MODULE__PRO')) {
            $l_cache_buttons['LC__SETTINGS__CMDB__VALIDATION__CACHE_REFRESH'] = [
                'onclick' => 'window.flush_validation_cache(this);',
                'css'     => 'cache-button'
            ];
        }

        // Database buttons.
        $l_database_buttons = [
            'LC__SYSTEM__CACHE_DB__TABLE_OPTIMIZATION'                     => [
                'onclick' => "window.flush_database('db_optimize', '" . $this->language->get('LC__SYSTEM__CACHE_DB__TABLE_OPTIMIZATION_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__TABLE_DEFRAG'                           => [
                'onclick' => "window.flush_database('db_defrag', '" . $this->language->get('LC__SYSTEM__CACHE_DB__TABLE_DEFRAG_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS'                    => [
                'onclick' => "window.flush_database('db_location', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_CATEGORY_ASSIGNMENTS'           => [
                'onclick' => "window.flush_database('db_cleanup_cat_assignments', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_CATEGORY_ASSIGNMENTSCONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__RENEW_PROPERTIES'                       => [
                'onclick' => "window.flush_database('db_properties', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RENEW_PROPERTIES_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_DUPLICATE_SINGLE_VALUE_ENTRIES' => [
                'onclick' => "window.flush_database('db_cleanup_duplicate_sv_entries', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_DUPLICATE_SINGLE_VALUE_ENTRIES_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_UNASSIGNED_RELATIONS'           => [
                'onclick' => "window.flush_database('db_cleanup_unassigned_relations', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_UNASSIGNED_RELATIONS_CONFIRMATION') . "', this);"
            ],
            'LC__SYSTEM__CACHE_DB__RENEW_RELATION_TITLES'                  => [
                'onclick' => "window.flush_database('db_renew_relation_titles', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RENEW_RELATION_TITLES_CONFIRMATION') . "', this)"
            ],
            'LC__SYSTEM__CACHE_DB__REFILL_EMPTY_SYSIDS'                    => [
                'onclick' => "window.flush_database('db_refill_empty_sysids', '" . $this->language->get('LC__SYSTEM__CACHE_DB__REFILL_EMPTY_SYSIDS_CONFIRMATION') . "', this)"
            ],
            'LC__SYSTEM__CACHE_DB__RESET_RELATION_PRIORITIES'              => [
                'onclick' => "window.flush_database('db_set_default_relation_priorities', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RESET_RELATION_PRIORITIES_CONFIRMATION') . "', this)"
            ],
            'LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS'                   => [
                'onclick' => "window.flush_database('db_relation', '" . $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_CONFIRMATION') . "', this)"
            ],
            'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK'             => [
                'onclick' => "window.flush_database('db_set_lists_to_rowclick', '" . $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_CONFIRMATION') . "', this)"
            ],
            'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER'        => [
                'onclick' => "window.flush_database('db_set_lists_to_wildcardfilter', '" . $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER_CONFIRMATION') . "', this)"
            ],
        ];

        $l_dao = isys_cmdb_dao::factory($database);

        $l_born = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ' AND isys_obj__undeletable = 0;')
            ->get_row();
        $l_archived = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ' AND isys_obj__undeletable = 0;')
            ->get_row();
        $l_deleted = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ' AND isys_obj__undeletable = 0;')
            ->get_row();

        // The aliases are used to display translated headings.
        $l_query = 'SELECT isys_obj__id AS \'ID\', isys_obj_type__title AS \'LC__REPORT__FORM__OBJECT_TYPE###1\', isys_obj__title AS \'LC__UNIVERSAL__TITLE###1\', ' .
            'isys_obj__created AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_created::isys_obj__created::LC__TASK__DETAIL__WORKORDER__CREATION_DATE\', ' .
            'isys_obj__updated AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_changed::isys_obj__updated::LC__CMDB__LAST_CHANGE\' ' . 'FROM isys_obj ' .
            'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE isys_obj__undeletable = 0 AND isys_obj__status = ';

        $l_object_buttons = [
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH'    => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH_CONFIRMATION', $l_born['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED' => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED_CONFIRMATION', $l_archived['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED'  => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED_CONFIRMATION', $l_deleted['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:90%;'
            ]
        ];

        if (!defined('C__MODULE__PRO')) {
            unset(
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['query'],
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['style'],
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['query'],
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['style'],
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['query'],
                $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['style']
            );

            $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['css'] = 'btn-block';
            $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['css'] = 'btn-block';
            $l_object_buttons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['css'] = 'btn-block';
        }

        $l_category_buttons = [
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_BIRTH'    => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_BIRTH_CONFIRMATION')) . "', this);",
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_ARCHIVED' => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_ARCHIVED_CONFIRMATION')) . "', this);",
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_DELETED'  => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_DELETED_CONFIRMATION')) . "', this);",
            ]
        ];

        $l_other_buttons = [
            'LC__MODULE__SEARCH__START_INDEXING'         => [
                'onclick' => 'window.search_index(this);'
            ],
            'LC__SYSTEM__CACHE_DB__MIGRATE_DATABASE_OBJECTS_TO_CATEGORY'         => [
                'onclick' => "window.flush_other('MigrateDbObjectsToCategory', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__MIGRATE_DATABASE_OBJECTS_TO_CATEGORY_CONFIRM')) . "', this);"
            ]
        ];

        // Create a DAO instance with the system database.
        $dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database_system'));
        $tenantId = $dao->convert_sql_id($session->get_mandator_id());

        $encryptedPassword = $dao
            ->retrieve("SELECT * FROM isys_mandator WHERE isys_mandator__id = {$tenantId} LIMIT 1;")
            ->get_row_value('isys_mandator__db_password');

        // Only display this button if the password has not been encrypted yet.
        if (empty($encryptedPassword)) {
            $l_other_buttons['LC__SYSTEM__CACHE_DB__TENANT_DATABASE_ENCRYPTION'] = [
                'onclick' => "window.processSecureAction('TenantDatabaseEncryption', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TENANT_DATABASE_ENCRYPTION__CONFIRMATION')) . "', this);"
            ];
        }

        if (isys_module_manager::instance()->is_active('check_mk')) {
            $l_other_buttons['LC__SYSTEM__CACHE_DB__TRUNCATE_EXPORTED_CHECK_MK_TAGS'] = [
                'onclick' => "window.flush_other('DeleteExportedCheckMkTags', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TRUNCATE_EXPORTED_CHECK_MK_TAGS_CONFIRM')) . "', this);"
            ];
        }

        if (isys_module_manager::instance()->is_active('custom_fields')) {
            $l_other_buttons['LC__SYSTEM__CACHE_DB__TRUNCATE_ORPHANED_CUSTOM_CATEGORY_DATA'] = [
                'onclick' => "window.flush_other('RemoveOrphanedCustomCategoryData', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TRUNCATE_ORPHANED_CUSTOM_CATEGORY_DATA_CONFIRM')) . "', this);"
            ];
        }

        $this->m_userrequest->get_template()
            ->assign('report_sql_path', isys_application::instance()->app_path . '/src/classes/modules/report/templates/report.js')
            ->assign('mysqldump', $l_mysqldump)
            ->assign('cache_buttons', $l_cache_buttons)
            ->assign('database_buttons', $l_database_buttons)
            ->assign('object_buttons', $l_object_buttons)
            ->assign('category_buttons', $l_category_buttons)
            ->assign('other_buttons', $l_other_buttons);

        $index_includes['contentbottomheader'] = '';
        $index_includes['contentbottomcontent'] = 'content/sys_cache.tpl';
    }

    /**
     * Handle object match profiles
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_object_matching()
    {
        $l_dbID = isys_glob_get_param("profileID");

        try {
            switch ($_POST[C__GET__NAVMODE]) {
                case C__NAVMODE__NEW:
                    $this->object_matching_profile(null);
                    $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                    break;

                case C__NAVMODE__SAVE:
                    $this->object_matching_profile($l_dbID);
                    $this->object_matching_profile_list();
                    break;

                case C__NAVMODE__PURGE:
                    idoit\Module\Cmdb\Model\Matcher\MatchDao::instance(isys_application::instance()->database)
                        ->deleteMatchProfile($_POST["id"]);
                    $this->object_matching_profile_list();
                    break;

                default:
                    if ($l_dbID != null) {
                        $this->object_matching_profile($l_dbID);
                    } else {
                        if ($_POST["id"] != null) {
                            $this->object_matching_profile($_POST["id"][0]);
                        } else {
                            $this->object_matching_profile_list();
                        }
                    }
                    break;
            }
        } catch (isys_exception_general $e) {
            isys_notify::warning($e->getMessage());
            $this->object_matching_profile_list();
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
            $this->object_matching_profile_list();
        }
    }

    /**
     * Show object match profile list
     *
     * @throws Exception
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function object_matching_profile_list()
    {
        isys_auth_system::instance()
            ->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');

        $template = isys_application::instance()->container->get('template');

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_list = new isys_component_list();

        $l_list_headers = [
            "isys_obj_match__title"     => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__TITLE"),
            "isys_obj_match__bits"      => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS"),
            "isys_obj_match__min_match" => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH")
        ];

        $l_list_data = [];

        $l_profiles = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance(isys_application::instance()->database)
            ->getMatchProfiles();
        $l_count = $l_profiles->num_rows();

        if ($l_count > 0) {
            while ($l_profile = $l_profiles->get_row()) {
                $l_profile['isys_obj_match__bits'] = implode(', ', $this->get_object_matching_bits($l_profile['isys_obj_match__bits'], true));
                $l_list_data[] = $l_profile;
            }

            $l_list->set_data($l_list_data);
            $l_list->config($l_list_headers, '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__SYSTEM') . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] .
                "&what=object_matching&profileID=[{isys_obj_match__id}]", "[{isys_obj_match__id}]");

            if ($l_list->createTempTable()) {
                $template->assign("objectTableList", $l_list->getTempTableHtml());
            }
        } else {
            $template->assign("objectTableList", '<div class="p10">' . $this->language->get('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>');
        }

        $l_navbar
            ->set_active(true, C__NAVBAR_BUTTON__NEW)
            ->set_active((($l_count > 0) ? true : false), C__NAVBAR_BUTTON__PURGE)
            ->set_active((($l_count > 0) ? true : false), C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        $template
            ->assign("content_title", $this->language->get('LC__MODULE__JDISC__PROFILES__OBJECT_MATCHING_PROFILES'))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');
    }

    /**
     * Show object match profile
     *
     * @param null $p_profileID
     *
     * @return bool
     * @throws isys_exception_auth
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function object_matching_profile($p_profileID = null)
    {
        isys_auth_system::instance()
            ->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');

        if ($p_profileID == 1) {
            isys_notify::warning($this->language->get('LC__MODULE__SYSTEM__OBJECT_MATCHING__DEFAULT_PROFILE_IS_NOT_EDITABLE', $p_profileID));
        }

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];
        $l_rules = $l_identifier_data = [];
        $l_selected_bit = null;

        // Navmode-Handling
        switch ($l_navmode) {
            case C__NAVMODE__SAVE:
                $l_matchDAO = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance(isys_application::instance()->database);
                if ($p_profileID) {
                    $l_matchDAO->saveMatchProfile(
                        $p_profileID,
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS__selected_box']),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']
                    );
                    $l_id = $p_profileID;
                } else {
                    $l_id = $l_matchDAO->createMatchProfile(
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS__selected_box']),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']
                    );
                }

                if ($l_id) {
                    isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));

                    return true;
                } else {
                    isys_notify::error($this->language->get('LC__INFOBOX__DATA_WAS_NOT_SAVED'), ['sticky' => true]);

                    return false;
                }
                break;
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                if ($p_profileID == 1) {
                    $l_navbar->set_active(false, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(false, C__NAVBAR_BUTTON__CANCEL);
                } else {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                }
                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);

                break;

            default:
                if ($p_profileID == 1) {
                    $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT);
                } else {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                }

                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        if ($p_profileID && $l_data = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance(isys_application::instance()->database)->getMatchProfileById($p_profileID)) {
            $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE']['p_strValue'] = $l_data['isys_obj_match__title'];
            $l_selected_bit = $l_data['isys_obj_match__bits'];
            $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']['p_strSelectedID'] = $l_data['isys_obj_match__min_match'];
        }

        $l_objectMatchingTypes = $this->get_object_matching_bits($l_selected_bit);
        $l_minMatch = [];
        $l_counter = 1;
        foreach ($l_objectMatchingTypes as $l_objectMatchType) {
            if ($l_objectMatchType['sel'] === true) {
                $l_minMatch[$l_counter] = $l_counter;
                $l_counter++;
            }
        }

        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']['p_arData'] = $l_minMatch;
        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS']['p_arData'] = $l_objectMatchingTypes;

        isys_application::instance()->container->get('template')
            ->assign('profileID', $p_profileID)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/system/object_matching.tpl');
    }

    /**
     * Helper Method which gets the Object matching bits for a dialog list
     *
     * @param int  $p_selected_bit
     * @param bool $p_only_selection
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_object_matching_bits($p_selected_bit = null, $p_only_selection = false)
    {
        $l_ciIdentifiers = new \idoit\Module\Cmdb\Model\Matcher\Ci\CiIdentifiers(null);
        $l_identifiers = $l_ciIdentifiers->getIdentifiers();
        $l_identifier_data = [];
        if (count($l_identifiers)) {
            foreach ($l_identifiers as $l_identifier) {
                $l_sel = false;
                $l_identifier_bit = $l_identifier::getBit();
                $l_identifier_title = $this->language->get($l_identifier->getTitle());

                if ($p_selected_bit & $l_identifier_bit) {
                    $l_sel = true;
                }

                if ($p_only_selection) {
                    if ($l_sel) {
                        $l_identifier_data[$l_identifier_bit] = $l_identifier_title;
                    }
                } else {
                    $l_identifier_data[] = [
                        'id'  => $l_identifier_bit,
                        'val' => $l_identifier_title . ' (' . implode(', ', $l_identifier->getUsableIn()) . ')',
                        'sel' => $l_sel
                    ];
                }
            }
        }

        return $l_identifier_data;
    }

    /**
     * Handles h-inventory
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_hinventory()
    {
        try {
            $l_type = $_GET[C__GET__SETTINGS_PAGE];

            if ($_GET[C__GET__SETTINGS_PAGE] === 'config') {
                $this->handle_hinventory_config();
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }
    }

    /**
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function handle_customizeObjectBrowser()
    {
        $setting = new CustomizeObjectBrowser(
            isys_application::instance()->container->get('template'),
            isys_application::instance()->container->get('database'),
            $this->language
        );

        // Initiate the "render page" action with the current navmode.
        $setting->renderPage((int) ($_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE]));
    }

    /**
     * Handles h-inventory config page
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    private function handle_hinventory_config()
    {
        $l_navbar = isys_component_template_navbar::getInstance();

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__EDIT:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                // Save config page
                isys_tenantsettings::set('import.hinventory.object-matching', $_POST['obj_match']);
                isys_tenantsettings::force_save();
                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            // no break

            default:
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        isys_application::instance()->container->get('template')
            ->assign('object_match_id', isys_tenantsettings::get('import.hinventory.object-matching', 1))
            ->include_template('contentbottomcontent', 'modules/system/hinventory_config.tpl');
    }

    /**
     * @deprecated Keep for backward compatibility until i-doit 1.17
     */
    public static function get_auth()
    {
        return self::getAuth();
    }

    /**
     * @return isys_auth_system
     */
    public static function getAuth()
    {
        return isys_auth_system::instance();
    }
}
