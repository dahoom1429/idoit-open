<?php

/**
 * i-doit
 *
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_system extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_system
     */
    private static $instance;

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_system
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            self::$m_dao = new isys_auth_dao(isys_application::instance()->container->get('database'));
        }

        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        $l_return = [
            'system'            => [ // This is used for the "?modules" page. Needs supervisor right.
                'title' => 'System',
                'type'  => 'boolean'
            ],
            'ocs'               => [
                'title' => 'LC__AUTH_GUI__OCS_CONDITION',
                'type'  => 'ocs'
            ],
            'jsonrpcapi'        => [
                'title' => 'LC__AUTH_GUI__JSONRPCAPI_CONDITION',
                'type'  => 'jsonrpcapi'
            ],
            'systemtools'       => [
                'title' => 'LC__AUTH_GUI__SYSTEMTOOLS_CONDITION',
                'type'  => 'systemtools'
            ],
            'globalsettings'    => [
                'title' => 'LC__AUTH_GUI__GLOBALSETTINGS_CONDITION',
                'type'  => 'globalsettings'
            ],
            'licencesettings'   => [
                'title' => 'LC__AUTH_GUI__LICENCESETTINGS_CONDITION',
                'type'  => 'licencesettings'
            ],
            'controllerhandler' => [
                'title'    => 'LC__AUTH_GUI__CONTROLLER_HANDLER',
                'type'     => 'controllerhandler',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ],
                'defaults' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'command'           => [
                'title'    => 'LC__AUTH_GUI__COMMANDS',
                'type'     => 'command',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ],
                'defaults' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'qr_config'         => [
                'title'    => 'LC__AUTH_GUI__QR_CODE_CONFIGURATION',
                'type'     => 'qr_config',
                'rights'   => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ],
                'defaults' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'object_matching'   => [
                'title'  => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OBJECT_MATCHING',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::SUPERVISOR
                ]
            ],
            'hinventory'        => [
                'title'  => 'LC__AUTH_GUI__HINVENTORY_CONDITION',
                'type'   => 'hinventory',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::SUPERVISOR
                ],
            ]
        ];

        if (defined('C__MODULE__JDISC')) {
            $l_return['jdisc'] = [
                'title' => 'LC__AUTH_GUI__JDISC_CONDITION',
                'type'  => 'jdisc'
            ];
        }

        if (defined('C__MODULE__LDAP')) {
            $l_return['ldap'] = [
                'title' => 'LC__AUTH_GUI__LDAP_CONDITION',
                'type'  => 'ldap'
            ];
        }

        if (defined('C__MODULE__TTS')) {
            $l_return['tts'] = [
                'title' => 'LC__AUTH_GUI__TTS_CONDITION',
                'type'  => 'tts'
            ];
        }

        return $l_return;
    }

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__SYSTEM');
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__SYSTEM__TITLE';
    }

    /**
     *
     * @param   integer $p_right
     *
     * @throws  isys_exception_auth
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function system($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        // Check for wildchars.
        if (isset($this->m_paths['system'])) {
            if (array_key_exists(isys_auth::EMPTY_ID_PARAM, $this->m_paths['system']) && in_array($p_right, $this->m_paths['system'][isys_auth::EMPTY_ID_PARAM])) {
                return true;
            }
        }
        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__AUTH_EXCEPTION__MISSING_RIGHT_FOR_SYSTEM'));
    }

    /**
     * Determines the rights for loginventory.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function loginventory($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'loginventory', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LOGINVENTORY')));
    }

    /**
     * Determines the rights for jdisc.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function jdisc($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'jdisc', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JDISC')));
    }

    /**
     * Determines the rights for ocs.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function ocs($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'ocs', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_OCS')));
    }

    /**
     * Determines the rights for h-inventory.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function hinventory($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'hinventory', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_HINVENTORY_CONFIG')));
    }

    /**
     * Determines the rights for ldap.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function ldap($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'ldap', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LDAP')));
    }

    /**
     * Determines the rights for TroubleTicket-System (tts).
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function tts($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'tts', $p_type, new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_TTS')));
    }

    /**
     * Determines the rights for JSON-RPC Api.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function jsonrpcapi($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        switch ($p_type) {
            case 'api':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JSONRPCAPI_API', $this->get_right_name(isys_auth::EXECUTE));
                break;
            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_JSONRPCAPI');
                break;
        }

        return $this->check_module_rights($p_right, 'jsonrpcapi', $p_type, new isys_exception_auth($l_exception));
    }

    /**
     * Determines the rights for Systemtools.
     *
     * @param   integer $p_right
     * @param   mixed   $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function systemtools($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        switch ($p_param) {
            case 'cache':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__SYSTEM__CACHE_DB')
                    ]);
                break;
            case 'modulemanager':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__TOOLS__MODULE_MANAGER')
                    ]);
                break;
            case 'validation':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__TOOLS__VALIDATION')
                    ]);
                break;
            case 'idoitupdate':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE')
                    ]);
                break;
            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_SYSTEMTOOLS');
                break;
        }

        return $this->check_module_rights($p_right, 'systemtools', $p_param, new isys_exception_auth($l_exception));
    }

    /**
     * Determines the rights for the global settings
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function globalsettings($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        switch ($p_param) {
            case 'systemsetting':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__SETTINGS__SYSTEM')
                    ]);
                break;
            case 'customfields':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES')
                    ]);
                break;
            case 'qcw':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__CMDB_CONFIGURATION__QOC')
                    ]);
                break;
            case 'cmdbstatus':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS')
                    ]);
                break;
            case 'relationshiptypes':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES')
                    ]);
                break;
            case 'rolesadministration':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION')
                    ]);
                break;
            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_GLOBALSETTINGS');
                break;
        }

        return $this->check_module_rights($p_right, 'globalsettings', $p_param, new isys_exception_auth($l_exception));
    }

    /**
     * Determines the rights for the licence administration.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function licencesettings($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        switch ($p_param) {
            case 'installation':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__LICENE_INSTALLATION')
                    ]);
                break;
            case 'overview':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__LICENE_OVERVIEW')
                    ]);
                break;
            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LICENCESETTINGS');
                break;
        }

        return $this->check_module_rights($p_right, 'licencesettings', $p_param, new isys_exception_auth($l_exception));
    }

    /**
     * Determines the rights for all controller handlers.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function controllerhandler($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'controllerhandler', $p_param, new isys_exception_auth('No rights to execute controller handler ' . $p_param . '.'));
    }

    /**
     * Determines the rights for all commands.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Kevin Mauel <kmauel@i-doit.com>
     */
    public function command($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->check_module_rights($p_right, 'command', $p_param, new isys_exception_auth('No rights to execute command ' . $p_param . '.'));
    }

    /**
     * Determines the rights for the QR code configuration.
     *
     * @param   integer $p_right
     * @param   string  $p_param
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function qr_config($p_right, $p_param)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        switch ($p_param) {
            default:
            case 'global':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_GLOBAL_QRCODE_CONFIG');
                break;
            case 'objtype':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__REPORT_EXCEPTION__MISSING_RIGHT_FOR_OBJTYPE_QRCODE_CONFIG');
                break;
        }

        return $this->generic_right($p_right, 'qr_config', $p_param, new isys_exception_auth($l_exception));
    }

    /**
     * Method for retrieving the "parameter" in the configuration GUI. Gets called generically by "ajax()" method.
     *
     * @see     isys_module_auth->ajax_retrieve_parameter();
     *
     * @param   string  $p_method
     * @param   string  $p_param
     * @param   integer $p_counter
     * @param   boolean $p_editmode
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function retrieve_parameter($p_method, $p_param, $p_counter, $p_editmode = false)
    {
        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        $l_dialog_data = null;

        switch ($p_method) {
            case 'controllerhandler':
                global $g_dirs;

                $l_dir = opendir($g_dirs["handler"]);
                $l_dialog_data = [];

                if (is_resource($l_dir)) {
                    while ($l_file = readdir($l_dir)) {
                        if (is_file($g_dirs["handler"] . DIRECTORY_SEPARATOR . $l_file) && preg_match("/^(isys_handler_(.*))\.class\.php$/i", $l_file, $l_register)) {
                            $l_dialog_data[strtolower($l_register[1])] = str_replace('isys_handler_', '', $l_register[1]);
                        }
                    }
                    closedir($l_dir);
                }

                // Module controllers
                $l_module_dir = $g_dirs["class"] . 'modules/';
                $l_module_res = opendir($l_module_dir);

                if (is_resource($l_module_res)) {
                    while ($l_dir = readdir($l_module_res)) {
                        if (strpos($l_dir, '.') === false) {
                            if (is_dir($l_module_dir . $l_dir . '/handler/controller')) {
                                $l_controller_dir = opendir($l_module_dir . $l_dir . '/handler/controller');

                                while ($l_file = readdir($l_controller_dir)) {
                                    if (is_file($l_module_dir . $l_dir . '/handler/controller/' . $l_file) &&
                                        preg_match("/^(isys_handler_(.*))\.class\.php$/i", $l_file, $l_register)) {
                                        $l_dialog_data[strtolower($l_register[1])] = str_replace('isys_handler_', '', $l_register[1]);
                                    }
                                }

                                closedir($l_controller_dir);
                            }
                        } else {
                            continue;
                        }
                    }

                    closedir($l_module_res);
                }
                break;

            case 'command':
                $classes = require __DIR__ . '/../../../../../src/classmap.inc.php';

                $l_dialog_data = [];
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src/idoit/Console/Command', FilesystemIterator::SKIP_DOTS));

                foreach ($iterator as $file) {
                    // Exclude dot, abstract classes, and interfaces
                    if ($file->isDir() || stripos($file->getBasename(), 'abstract') !== false || stripos($file->getBasename(), 'interface') !== false ||
                        stripos($file->getBasename(), 'command') === false) {
                        continue;
                    }

                    $l_dialog_data[strtolower($file->getBasename('.' . $file->getExtension()))] = $file->getBasename('.' . $file->getExtension());
                }

                foreach ($classes as $class => $file) {
                    if (stripos($class, '\\Command\\') !== false && stripos($file, 'src/classes/modules') !== false) {
                        $l_dialog_data[strtolower(substr($class, strrpos($class, '\\') + 1))] = substr($class, strrpos($class, '\\') + 1);
                    }
                }

                /**
                 * Collect commands for add-ons
                 */
                $iterator = new DirectoryIterator('src/classes/modules');

                /**
                 * @var $file SplFileInfo
                 */
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        continue;
                    }

                    if (!is_dir($file->getPathname() . '/src/Console/Command')) {
                        continue;
                    }

                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file->getPathname() . '/src/Console/Command', FilesystemIterator::SKIP_DOTS));

                    /**
                     * @var $addOn SplFileInfo
                     */
                    foreach ($iterator as $addOn) {
                        // Exclude dot, abstract classes, and interfaces
                        if ($addOn->isDir() || stripos($addOn->getBasename(), 'abstract') !== false || stripos($addOn->getBasename(), 'interface') !== false ||
                            stripos($addOn->getBasename(), 'command') === false) {
                            continue;
                        }

                        $l_dialog_data[strtolower($addOn->getBasename('.' . $addOn->getExtension()))] = $addOn->getBasename('.' . $addOn->getExtension());
                    }

                    unset($iterator);
                }

                break;

            case 'globalsettings':
                $l_dialog_data = isys_auth_system_globals::get_globalsettings_parameter();
                break;

            case 'jdisc':
                if (defined('C__MODULE__JDISC')) {
                    $l_dialog_data = [
                        C__MODULE__JDISC . 9  => 'LC__MODULE__JDISC__CONFIGURATION',
                        C__MODULE__JDISC . 10 => 'LC__MODULE__JDISC__PROFILES',
                    ];
                }
                break;

            case 'jsonrpcapi':
                $l_dialog_data = [
                    'global'  => 'LC__AUTH_GUI__QR_CODE_GLOBAL_CONFIGURATION',
                    'objtype' => 'LC__AUTH_GUI__QR_CODE_GLOBAL_OBJECT_TYPE'
                ];
                break;

            case 'ldap':
                if (defined('C__MODULE__LDAP')) {
                    $l_dialog_data = [
                        C__MODULE__LDAP . C__LDAPPAGE__CONFIG      => 'LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER',
                        C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES => 'LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__DIRECTORIES'
                    ];
                }
                break;

            case 'licencesettings':
                $l_dialog_data = isys_auth_system_licence::get_licencesettings_parameter();
                break;

            case 'loginventory':
                if (defined('C__MODULE__LOGINVENTORY')) {
                    $l_dialog_data = [
                        C__MODULE__LOGINVENTORY . 9  => 'LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_CONFIGURATION',
                        C__MODULE__LOGINVENTORY . 10 => 'LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES'
                    ];
                }
                break;

            case 'ocs':
                $l_dialog_data = [
                    'OCSCONFIG' => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__CONFIGURATION',
                    'OCSDB'     => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OCS__DATABASE'
                ];
                break;

            case 'qr_config':
                $l_dialog_data = isys_auth_system_qr::get_qr_config_parameter();
                break;

            case 'systemtools':
                $l_dialog_data = [
                    'CACHE'          => 'LC__SYSTEM__CACHE_DB',
                    'MODULEMANAGER'  => 'LC__CMDB__TREE__SYSTEM__TOOLS__MODULE_MANAGER',
                    'SYSTEMOVERVIEW' => 'LC__CMDB__TREE__SYSTEM__TOOLS__OVERVIEW',
                    'IDOITUPDATE'    => 'LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE'
                ];
                break;

            case 'tts':
                $l_dialog_data = [
                    'CONFIG' => 'LC__TTS__CONFIGURATION'
                ];
                break;
            case 'hinventory':
                $l_dialog_data = [
                    'CONFIG' => 'LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY__CONFIGURATION'
                ];
                break;
        }

        if ($l_dialog_data !== null && is_array($l_dialog_data)) {
            $l_dialog = new isys_smarty_plugin_f_dialog();

            if (is_string($p_param)) {
                $p_param = strtolower($p_param);
            }

            $l_params = [
                'name'              => 'auth_param_form_' . $p_counter,
                'p_arData'          => $l_dialog_data,
                'p_editMode'        => $p_editmode,
                'p_bDbFieldNN'      => 1,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'input-small',
                'p_strSelectedID'   => $p_param
            ];

            $l_return['html'] = $l_dialog->navigation_edit(isys_application::instance()->template, $l_params);

            return $l_return;
        }

        return false;
    }
}
