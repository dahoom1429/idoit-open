<?php

use idoit\Component\Helper\LdapUrlGenerator;
use idoit\Component\Property\Property;

define("C__LDAPPAGE__CONFIG", 1);
define("C__LDAPPAGE__SERVERTYPES", 2);
define("C__LDAPPAGE__CONINFO", 3);

define("C__LDAP_MAPPING__GROUP", 0);
define("C__LDAP_MAPPING__OBJECT_CLASS", 1);
define("C__LDAP_MAPPING__FIRSTNAME", 2);
define("C__LDAP_MAPPING__LASTNAME", 3);
define("C__LDAP_MAPPING__MAIL", 4);
define("C__LDAP_MAPPING__USERNAME", 5);
define("C__LDAP_MAPPING__DESCRIPTION", 6);

define("C__DEFAULT__TIMELIMIT", 30);

/**
 * i-doit
 *
 * LDAP-Module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_ldap extends isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @var \Monolog\Logger
     */
    private static $m_logger = null;

    /**
     * @var array
     */
    private $m_default_attributes = [
        "cn",
        "description"
    ];

    /**
     * @var isys_library_ldap
     */
    private $m_ldap = null;

    /**
     * @var isys_module_request
     */
    private $m_userrequest;

    /**
     * An array of login/authentications
     *
     * @var array
     */
    private $authentications = [];

    /**
     * Writes a debug message
     *
     * @param string $p_message
     */
    public static function debug($p_message)
    {
        if (C__LDAP__DEBUG) {
            self::get_logger()->debug($p_message);
        }
    }

    /**
     * @return \Monolog\Logger
     */
    public static function get_logger()
    {
        if (self::$m_logger === null) {
            self::$m_logger = new \Monolog\Logger('ldap');
            if (C__LDAP__DEBUG) {
                $monologHandler = new \Monolog\Handler\StreamHandler(isys_application::instance()->app_path . "/log/ldap", \Monolog\Logger::DEBUG);
            } else {
                $monologHandler = new \Monolog\Handler\NullHandler(\Monolog\Logger::DEBUG);
            }
            self::$m_logger->pushHandler($monologHandler);
        }

        return self::$m_logger;
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
        if (!defined('C__MODULE__LDAP')) {
            return;
        }
        global $g_dirs;
        $l_root = -1;
        $l_submodule = [];

        if ($p_system_module) {
            $l_root = $p_tree->find_id_by_title('Modules');
            $l_submodule = [C__GET__MODULE_SUB_ID => C__MODULE__LDAP];
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__LDAP . '0', $l_root, 'LDAP');
        }

        if (!isset($_GET[C__GET__SETTINGS_PAGE])) {
            $_GET[C__GET__SETTINGS_PAGE] = C__LDAPPAGE__CONFIG;
        }

        $p_tree->add_node(
            C__MODULE__LDAP . C__LDAPPAGE__CONFIG,
            $l_root,
            'Server',
            isys_helper_link::create_url([
                C__GET__MODULE_ID      => $_GET[C__GET__MODULE_ID],
                C__GET__TREE_NODE      => C__MODULE__LDAP . C__LDAPPAGE__CONFIG,
                C__GET__SETTINGS_PAGE  => C__LDAPPAGE__CONFIG,
                C__CMDB__GET__EDITMODE => C__EDITMODE__ON
            ] + $l_submodule),
            null,
            $g_dirs['images'] . "icons/tree/stammdaten.gif",
            (int) ($_GET[C__GET__SETTINGS_PAGE] == C__LDAPPAGE__CONFIG && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__LDAP),
            '',
            '',
            isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG)
        );

        $p_tree->add_node(
            C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES,
            $l_root,
            'Directories',
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => $_GET[C__GET__MODULE_ID],
                C__GET__TREE_NODE     => C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES,
                C__GET__SETTINGS_PAGE => C__LDAPPAGE__SERVERTYPES
            ] + $l_submodule),
            null,
            $g_dirs['images'] . "icons/tree/folderopen.gif",
            (int) ($_GET[C__GET__SETTINGS_PAGE] == C__LDAPPAGE__SERVERTYPES && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__LDAP),
            '',
            '',
            isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES)
        );
    }

    /**
     * Returns dao instance.
     *
     * @param isys_component_database $p_database
     *
     * @return isys_ldap_dao
     */
    public function get_dao(&$p_database = null)
    {
        global $g_comp_database;

        if (is_null($p_database)) {
            $l_database = $g_comp_database;
        } else {
            $l_database = $p_database;
        }

        if (!class_exists('isys_ldap_dao')) {
            include_once('init.php');
        }

        return new isys_ldap_dao($l_database);
    }

    /**
     * Starts module process
     *
     * @throws isys_exception_general
     */
    public function start()
    {
        // Unpack request package.
        $l_gets = $this->m_userrequest->get_gets();

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $l_template = $this->m_userrequest->get_template();
            $l_tree = $this->m_userrequest->get_menutree();

            $this->build_tree($l_tree, false);

            $l_template->assign("menu_tree", $l_tree->process(0));
        }

        $this->process($l_gets);
    }

    /**
     * Return key of serialized mapping
     *
     * @param   string $p_key
     * @param   string $p_mapping
     *
     * @return  string
     */
    public function get_mapping($p_key, $p_mapping)
    {
        $l_mapping = unserialize($p_mapping);

        if (isset($l_mapping[$p_key])) {
            return $l_mapping[$p_key];
        } else {
            return false;
        }
    }

    /**
     * Initializes the module
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_ldap
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;

        return $this;
    }

    /**
     * i-doit Session login. Returns a valid i-doit user id by giving ldap userlogin data.
     *
     * @param   string                                 $p_username
     * @param   string                                 $p_password
     * @param   null                                   $p_userdn
     * @param   isys_cmdb_dao_category_s_person_master $p_user_dao
     *
     * @throws  Exception
     * @return  boolean
     */
    public function session_login($p_username, $p_password, $p_userdn = null, isys_cmdb_dao_category_s_person_master $p_user_dao = null)
    {
        // User-DAO.
        if (is_null($p_user_dao)) {
            $l_user_dao = isys_cmdb_dao_category_s_person_master::instance(isys_application::instance()->database);
        } else {
            $l_user_dao = &$p_user_dao;
        }

        try {
            // Try the ldap-login (bind).
            $l_found_user = $this->ldap_login(
                $l_user_dao->get_database_component(),
                $p_username,
                $p_password,
                $p_userdn,
                null,
                $l_user_dao->get_person_id_by_username($p_username)
            );

            // User found and Ldap-Bind was OK.
            if (is_array($l_found_user)) {
                // Get UserDN.
                $l_dn = $l_found_user["dn"];

                // Checks if the user exists in i-doit.
                if (($l_user_id = $l_user_dao->exists($p_username))) {
                    /* Ldap bind was successfull and user was found in i-doit. Nice. */
                    // Reassign person group
                    $this->attach_groups_to_user($l_user_id, $this->ldap_get_groups($l_found_user), $l_user_dao);

                    return $l_user_id;
                } else /* User was not found in i-doit. So create it. */ {
                    $this->debug('Creating User: ' . $l_found_user[C__LDAP_MAPPING__FIRSTNAME] . ' ' . $l_found_user[C__LDAP_MAPPING__LASTNAME] . ' with username ' .
                        $p_username);

                    /**
                     *  Auth was OK, now we create the user as an internal contact
                     *  with an ldap dn reference
                     */
                    $l_user_id = $l_user_dao->create(
                        null,
                        $p_username,
                        $l_found_user[C__LDAP_MAPPING__FIRSTNAME],
                        $l_found_user[C__LDAP_MAPPING__LASTNAME],
                        $l_found_user[C__LDAP_MAPPING__MAIL],
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        $l_found_user[C__LDAP_MAPPING__DESCRIPTION],
                        $l_found_user["ldap_data"]["isys_ldap__id"],
                        $l_dn,
                        C__RECORD_STATUS__NORMAL,
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'system'
                    );

                    if (is_numeric($l_user_id) && $l_user_id > 0) {
                        $this->debug("User account created. User-ID: " . $l_user_id);

                        /* Now, also attach the user into ldap enabled i-doit groups.
                            Note: 	Attaching does only work, if the ldap group has got the same name
                                    like the i-doit GROUP LDAP MAPPING.
                                    This mapping is defined in the detail view of the corresponding group
                                    under contacts -> groups -> groupname */
                        $this->attach_groups_to_user($l_user_id, $this->ldap_get_groups($l_found_user), $l_user_dao, true);
                    } else {
                        $this->debug("Unknown error while creating the LDAP-User - Received an empty user-id.");
                    }

                    return $l_user_id;
                }
            } elseif ($l_found_user == true) {
                $this->debug("--- LDAP-Login succeeded. Granting access..");

                return true;
            } else {
                $this->debug("*** LDAP Auth failed. (" . var_export($l_found_user, true) . ")");
                $l_user_id = 0;
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $l_user_id;
    }

    /**
     *
     * Attach default groups configured in 'ldap.default-group'
     *
     * @param int                                    $p_user_id
     * @param isys_cmdb_dao_category_s_person_master $p_user_dao
     *
     * @return $this
     */
    public function attach_default_groups_to_user($p_user_id, isys_cmdb_dao_category_s_person_master $p_user_dao)
    {
        if (isys_tenantsettings::get('ldap.default-group', '') != '') {
            foreach (explode(',', isys_tenantsettings::get('ldap.default-group', '')) as $groupId) {
                // Attach user with ldap flag set to 0 since this some kind of a manual mapping
                $p_user_dao->attach_group($p_user_id, trim($groupId), '1');
            }
        }

        return $this;
    }

    /**
     * Attaches the user into found ldap groups
     *
     * @param int                                    $p_user_id
     * @param array                                  $p_groups
     * @param isys_cmdb_dao_category_s_person_master $p_user_dao
     *
     * @return $this
     */
    public function attach_groups_to_user($p_user_id, $p_groups, isys_cmdb_dao_category_s_person_master $p_user_dao, $p_user_created = false)
    {
        if (is_array($p_groups)) {
            $this->debug("Syncing groups..");

            /**
             * Detach all groups first
             */
            $p_user_dao->detach_groups($p_user_id, null, ' AND isys_person_2_group__ldap = 1', $p_user_created);

            if (count($p_groups)) {
                /**
                 * Then attach the ldap group pendents to user
                 */
                foreach ($p_groups as $l_group_data) {
                    if (is_numeric($l_group_data["isys_cats_person_group_list__isys_obj__id"])) {
                        //$this->debug(" Trying to attach user({$p_user_id}) to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                        if ($p_user_dao->attach_group($p_user_id, trim($l_group_data["isys_cats_person_group_list__isys_obj__id"]), '1')) {
                            $this->debug(" User ({$p_user_id}) successfully attached to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                        } else {
                            $this->debug(" Failed attaching user({$p_user_id}) to group: " . $l_group_data["isys_cats_person_group_list__title"]);
                        }
                    }
                }
            } else {
                // Only add default group if no ldap group has been attached to the user
                $this->attach_default_groups_to_user($p_user_id, $p_user_dao);
            }
        }

        return $this;
    }

    /**
     * Trys to login into all configured ldap-servers for $p_database.
     * If a login succeeds, the ldap attributes of the corresponding user are returned.
     *
     * @param isys_component_database $p_database
     * @param string                  $p_username
     * @param string                  $p_password
     * @param null                    $p_dn
     * @param null                    $p_ldap_server
     * @param null                    $p_user_id
     *
     * @throws Exception
     * @return array|boolean
     */
    public function ldap_login(&$p_database, $p_username, $p_password, $p_dn = null, $p_ldap_server = null, $p_user_id = null)
    {
        if (!is_object($p_database)) {
            return false;
        }

        // Get Servers and configured admin-authentication.
        $l_ldap_dao = $this->get_dao($p_database);
        $l_servers = $l_ldap_dao->get_active_servers($p_ldap_server);
        $i = 1;

        $this->debug('----------------------------------------------------------------------------------------------');
        $this->debug("LDAP Module launched for mandator: " . $p_database->get_db_name());

        if (!is_object($l_servers)) {
            throw new Exception("No active LDAP servers found.");
        }

        $this->debug("Found " . $l_servers->num_rows() . " configured LDAP Servers.");

        // Iterate through configured servers.
        while ($l_ldap = $l_servers->get_row()) {
            try {
                $this->debug($i++ . ": " . $l_ldap["isys_ldap__hostname"] . " (" . $l_ldap["isys_ldap__user_search"] . ")");
                $this->debug("----------------------------------------------------------------------------------------------");

                $authenticationHash = md5(implode(
                    '.',
                    [
                        $l_ldap["isys_ldap__hostname"],
                        $l_ldap["isys_ldap__dn"],
                        $l_ldap["isys_ldap__password"],
                        $l_ldap["isys_ldap__port"],
                        $l_ldap["isys_ldap__version"],
                        $l_ldap["isys_ldap__tls"],
                        $p_username,
                        $p_password
                    ]
                ));

                if (isset($this->authentications[$authenticationHash])) {
                    // @see  ID-7232  Implemented fix, thanks to prepared bugfix by VQH.
                    if ($this->authentications[$authenticationHash] !== false) {
                        return $this->authentications[$authenticationHash];
                    }

                    $this->debug('Already tried login in with same credentials and same ldap server (ID ' . $l_ldap['isys_ldap__id'] . ') configuration');
                    continue;
                }

                // Connect to LDAP-Server and get the internal ldap library.
                $this->m_ldap = $this->get_library(
                    $l_ldap["isys_ldap__hostname"],
                    $l_ldap["isys_ldap__dn"],
                    isys_helper_crypt::decrypt($l_ldap["isys_ldap__password"]),
                    $l_ldap["isys_ldap__port"],
                    $l_ldap["isys_ldap__version"],
                    $l_ldap["isys_ldap__tls"]
                );

                $this->authentications[$authenticationHash] = false;

                if (!$this->m_ldap->is_connected()) {
                    throw new Exception("LDAP-Connection Error");
                }

                // OpenLDAP Fix.
                if ($l_ldap["isys_ldap_directory__const"] == "C__LDAP__OPENLDAP") {
                    $l_user_mapping = $this->get_mapping(C__LDAP_MAPPING__USERNAME, $l_ldap["isys_ldap_directory__mapping"]);
                    if ($l_user_mapping) {
                        $this->m_ldap->set_idattribute($l_user_mapping);
                    } else {
                        $this->m_ldap->set_idattribute("uid");
                    }
                }

                if (empty($p_dn)) {
                    $this->debug("Searching for username: {$p_username}");

                    if (($l_found_user = $this->m_ldap->get_user($p_username, $l_ldap))) {
                        $this->debug("Found DN: " . $l_found_user["dn"] . ". Trying to login with it.");

                        $l_found_user["ldap_data"] = &$l_ldap;
                        $l_found_user["ldapi"] = &$this->m_ldap;

                        /* Try to authenticate with entered username and password */
                        if (!empty($l_found_user["dn"]) && $this->m_ldap->try_auth($l_found_user["dn"], $p_password)) {
                            $this->debug("Auth successfull (" . $l_found_user["dn"] . ").");

                            // Should read operations only be performed by admin user?
                            if (!empty($l_ldap['isys_ldap__use_admin_only']) && !!$l_ldap['isys_ldap__use_admin_only']) {
                                // Reconnect with configured user again
                                $this->m_ldap = $this->get_library(
                                    $l_ldap["isys_ldap__hostname"],
                                    $l_ldap["isys_ldap__dn"],
                                    isys_helper_crypt::decrypt($l_ldap["isys_ldap__password"]),
                                    $l_ldap["isys_ldap__port"],
                                    $l_ldap["isys_ldap__version"],
                                    $l_ldap["isys_ldap__tls"]
                                );
                            }

                            $this->authentications[$authenticationHash] = $l_found_user;

                            return $l_found_user;
                        } else {
                            if ($this->m_ldap->get_ldap_error() != "Success") {
                                $l_ldap_result = " LDAP-Result: " . $this->m_ldap->get_ldap_error();
                            } else {
                                $l_ldap_result = "";
                            }

                            $this->debug("** Auth failed." . $l_ldap_result);
                        }
                    } else {
                        $this->debug("User not found. Check if {$p_username} " . "exist in your configured search-path: " . $l_ldap["isys_ldap__user_search"]);
                    }
                } else {
                    $this->debug("Trying to auth with DN: " . $p_dn);
                    if ($this->m_ldap->try_auth($p_dn, $p_password)) {
                        $this->debug(" + " . $p_dn . " / " . $p_username . " authenticated.");
                        $_SESSION["username"] = $p_username;

                        // Sync groups
                        if ($p_user_id > 0) {
                            if (($l_found_user = $this->m_ldap->get_user($p_username, $l_ldap))) {
                                $l_found_user["ldap_data"] = &$l_ldap;
                                $l_found_user["ldapi"] = &$this->m_ldap;

                                // do not instantiate the object via factory. Because the object uses
                                // the wrong mandator database
                                $this->attach_groups_to_user($p_user_id, $this->ldap_get_groups($l_found_user), new isys_cmdb_dao_category_s_person_master($p_database));

                                $this->authentications[$authenticationHash] = $l_found_user;
                            }
                        }

                        /* AUTH SUCCEEDED */
                        $this->debug("----------------------------------------------------------------------------------------------");

                        return true;
                    } else {
                        if ($this->m_ldap->get_ldap_error() != "Success") {
                            $l_ldap_result = " LDAP-Result: " . $this->m_ldap->get_ldap_error();
                        } else {
                            $l_ldap_result = "";
                        }

                        $this->debug($p_dn . " / " . $p_username . " auth failed. " . $l_ldap_result);

                        /* AUTH FAILED */
                    }
                }

                $this->debug("----------------------------------------------------------------------------------------------");
            } catch (Exception $e) {
                $this->debug($e->getMessage());
            }
        }

        return false;
    }

    /**
     * Resolves groups from ldap memberof array
     *
     * @param array $p_found_user
     *
     * @return array
     */
    public function ldap_get_groups($p_found_user)
    {
        $l_return = [];

        $this->debug("Getting groups of {$p_found_user["dn"]} (Servertype: " . $p_found_user["ldap_data"]["isys_ldap_directory__title"] . ")");

        /** @var  $l_ldapi isys_library_ldap */
        $l_ldapi = $p_found_user["ldapi"];

        $l_mapping = unserialize($p_found_user["ldap_data"]["isys_ldap_directory__mapping"]);

        $l_group_attr = $l_mapping[C__LDAP_MAPPING__USERNAME];

        if (!is_object($l_ldapi)) {
            $this->debug("ERROR: LDAPi-Library not available :: " . __FILE__ . ":" . __LINE__);
        }

        switch ($p_found_user["ldap_data"]["isys_ldap_directory__const"]) {
            case "C__LDAP__OPENLDAP":

                $this->debug(" Attention: OpenLDAP connections are experimental!");

                // Determine search path for groups.
                $l_group_search = $p_found_user["ldap_data"]["isys_ldap__group_search"];
                if (!$l_group_search) {
                    $l_group_search = $l_ldapi->get_search_path();
                }

                $l_ldapi->set_search_path($l_group_search);

                if ($p_found_user[C__LDAP_MAPPING__USERNAME] || $p_found_user[$l_mapping[C__LDAP_MAPPING__USERNAME]]) {
                    $l_group_data = $l_ldapi->search(
                        $l_ldapi->get_search_path(),
                        "(" . $l_mapping[C__LDAP_MAPPING__GROUP] . "=" . $p_found_user["dn"] . ")",
                        array_merge([$l_group_attr, 'objectsid'], $this->m_default_attributes),
                        0,
                        null,
                        null,
                        C__LDAP_SCOPE__RECURSIVE
                    );

                    if (!$l_group_data) {
                        $this->debug("No group found with Filter: (" . $l_mapping[C__LDAP_MAPPING__GROUP] . "=" . $p_found_user["dn"] . ")");

                        return $l_return;
                    }

                    $this->debug(" search() " . $l_ldapi->get_search_path() . " (Filter: " . "(" . $l_mapping[C__LDAP_MAPPING__GROUP] . "=" . $p_found_user["dn"] . ")" .
                        "): " . $l_ldapi->count($l_group_data));

                    if ($l_ldapi->count($l_group_data) > 0) {
                        $l_group_entries = $l_ldapi->get_entries($l_group_data);

                        foreach ($l_group_entries as $l_single_group) {
                            $identifier = [];

                            $l_group_name = $l_single_group[$l_group_attr][0];

                            if (!empty($l_group_name)) {
                                if ($l_single_group["cn"][0]) {
                                    $l_group_name = $l_single_group["cn"][0];
                                } elseif ($l_single_group["cn"]) {
                                    $l_group_name = $l_single_group["cn"];
                                }
                            }

                            if (!empty($l_group_name)) {
                                $identifier[] = $l_group_name;
                            }

                            $objectSID = self::convertSID($l_single_group['objectsid'][0]);

                            if (!empty($objectSID)) {
                                $identifier[] = $objectSID;
                            }

                            $l_idoit_group = $this->get_idoit_group($identifier);

                            if ($l_idoit_group) {
                                $l_return[] = $l_idoit_group;
                            }
                        }
                    }
                } else {
                    $this->debug("No group found.");
                }

                break;

            default:
                $l_ldap_groups = (isset($p_found_user[$l_mapping[C__LDAP_MAPPING__GROUP]]) ? $p_found_user[$l_mapping[C__LDAP_MAPPING__GROUP]] : (isset($p_found_user[strtolower($l_mapping[C__LDAP_MAPPING__GROUP])]) ? $p_found_user[strtolower($l_mapping[C__LDAP_MAPPING__GROUP])] : $p_found_user[C__LDAP_MAPPING__GROUP]));

                if (is_array($l_ldap_groups)) {
                    unset($l_ldap_groups['count']);

                    $pagedResultEnabled = (bool) $l_ldapi->isPagedResultSupported();

                    foreach ($l_ldap_groups as $l_group) {
                        $cookie = '';
                        do {
                            if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                                if ($pagedResultEnabled) {
                                    $l_ldapi->ldapControlPagedResult(1, true, $cookie);
                                }
                                $l_group_data = $l_ldapi->search(
                                    $l_group,
                                    "(objectclass=*)",
                                    array_merge([$l_group_attr, 'objectsid'], $this->m_default_attributes),
                                    0,
                                    null,
                                    null,
                                    C__LDAP_SCOPE__RECURSIVE
                                );
                            } else {
                                // phpcs:disable
                                $l_group_data = $l_ldapi->search(
                                    $l_group,
                                    "(objectclass=*)",
                                    array_merge([$l_group_attr, 'objectsid'], $this->m_default_attributes),
                                    0,
                                    null,
                                    null,
                                    C__LDAP_SCOPE__RECURSIVE,
                                    [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 2, 'cookie' => $cookie]]]
                                );
                                // phpcs:enable
                            }

                            if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                                if ($pagedResultEnabled) {
                                    $l_ldapi->ldapControlPagedResultResponse($l_group_data, $cookie);
                                }
                            } else {
                                // phpcs:disable
                                ldap_parse_result($l_ldapi->get_connection(), $l_group_data, $errcode, $matcheddn, $errmsg, $referrals, $controls);
                                if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                                    // You need to pass the cookie from the last call to the next one
                                    $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
                                } else {
                                    $cookie = '';
                                }
                                // phpcs:enable
                            }

                            $this->debug(" Found " . $l_group . " (Filter: (objectclass=*)): " . $l_ldapi->count($l_group_data));

                            if ($l_ldapi->count($l_group_data) > 0) {
                                $l_group_entries = $l_ldapi->get_entries($l_group_data);

                                if ($l_group_entries) {
                                    $identifier = [];

                                    $l_group_name = (isset($l_group_entries[0][$l_group_attr][0]))
                                        ? $l_group_entries[0][$l_group_attr][0]
                                        : $l_group_entries[0][strtolower($l_group_attr)][0];

                                    if (empty($l_group_name)) {
                                        $l_group_name = $l_group_entries[0]['cn'][0];
                                    }

                                    if (!empty($l_group_name)) {
                                        $identifier[] = $l_group_name;
                                    }

                                    $objectSID = self::convertSID($l_group_entries[0]['objectsid'][0]);

                                    if (!empty($objectSID)) {
                                        $identifier[] = $objectSID;
                                    }

                                    // Search for i-doit pendant
                                    $l_idoit_group = $this->get_idoit_group($identifier);

                                    if ($l_idoit_group) {
                                        $l_return[] = $l_idoit_group;
                                    }
                                }
                            }
                        } while (!empty($cookie));
                    }
                }
                break;
        }

        return $l_return;
    }

    /**
     * Converts a binary SID into a valid string.
     *
     * @param string $objectSID
     *
     * @return string
     */
    public static function convertSID($objectSID)
    {
        $hexSID = bin2hex($objectSID);
        $rev = hexdec(substr($hexSID, 0, 2));
        $subCount = hexdec(substr($hexSID, 2, 2));
        $auth = hexdec(substr($hexSID, 4, 12));
        $result = "{$rev}-{$auth}";

        for ($i = 0; $i < $subCount; $i++) {
            $subauth[$i] = hexdec(self::convertLittleEndian(substr($hexSID, 16 + ($i * 8), 8)));
            $result .= '-' . $subauth[$i];
        }

        // Cheat by tacking on the S-
        return 'S-' . $result;
    }

    /**
     * Converts a little-endian hex-number to one, that 'hexdec' can convert.
     *
     * @param string $hex
     *
     * @return string
     */
    public static function convertLittleEndian($hex)
    {
        $result = '';

        for ($i = strlen($hex) - 2; $i >= 0; $i -= 2) {
            $result .= substr($hex, $i, 2);
        }

        return $result;
    }


    /**
     * Returns a html list of ldap server types or fales, if no type was found.
     *
     * @return string|boolean
     * @throws Exception
     */
    public function get_server_types()
    {
        if (!defined('C__MODULE__LDAP')) {
            return;
        }
        $l_ldap = new isys_ldap_dao(isys_application::instance()->container->get('database'));

        $l_types = $l_ldap->get_ldap_types();

        if ($l_types->num_rows() > 0) {
            $objectListLink = isys_helper_link::create_url([
                C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
                C__GET__MODULE_SUB_ID => C__MODULE__LDAP,
                C__GET__TREE_NODE     => $_GET[C__GET__TREE_NODE],
                C__GET__SETTINGS_PAGE => C__LDAPPAGE__SERVERTYPES,
                'id'                  => '[{isys_ldap_directory__id}]'
            ]);

            $l_objList = new isys_component_list(null, $l_types);
            $l_objList->config(
                [
                    'isys_ldap_directory__title' => 'LC__UNIVERSAL__TITLE',
                    'isys_ldap_directory__const' => 'LC__UNIVERSAL__CONSTANT'
                ],
                $objectListLink,
                '[{isys_ldap_directory__id}]'
            );

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }

        return false;
    }

    /**
     * Returns a html list of configured ldap servers, or false if nothing was configured, yet.
     *
     * @return string / false
     */
    public function get_server_list()
    {
        if (!defined('C__MODULE__LDAP') || !defined('C__MODULE__SYSTEM')) {
            return false;
        }
        $l_ldap = new isys_ldap_dao(isys_application::instance()->database);

        $l_data = $l_ldap->get_data();

        if ($l_data->num_rows() > 0) {
            $objectListLink = isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__LDAP,
                C__GET__TREE_NODE     => $_GET[C__GET__TREE_NODE],
                C__GET__SETTINGS_PAGE => C__LDAPPAGE__CONFIG,
                'id'                  => '[{isys_ldap__id}]'
            ]);

            $l_objList = new isys_component_list(null, $l_data);
            $l_objList->config(
                [
                    'isys_ldap__id'              => 'ID',
                    'isys_ldap_directory__title' => 'Directory',
                    'isys_ldap__hostname'        => 'Host',
                    'isys_ldap__port'            => 'Port',
                    'isys_ldap__dn'              => 'Login',
                    'isys_ldap__user_search'     => 'Search',
                    'isys_ldap__filter'          => 'Filter',
                    'isys_ldap__active'          => 'Active',
                    'isys_ldap__unique_attribute' => 'Unique Attribute'
                ],
                $objectListLink,
                '[{isys_ldap__id}]'
            );

            // @see  ID-6902  Disable the e-mail address replacer in the current configuration.
            $l_objList->setOptions([
                'replacerOptions' => [
                    'linkEmailAddresses' => false
                ]
            ]);

            $l_objList->createTempTable();

            return $l_objList->getTempTableHtml();
        }

        return false;
    }

    /**
     * Process ldap module
     *
     * @param array $p_get
     */
    public function process($p_get)
    {
        if (!defined('C__MODULE__LDAP')) {
            return;
        }
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $template = isys_application::instance()->container->get('template');

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_ldap = new isys_ldap_dao($database);

        // Enable save mode via AJAX:
        $l_navbar->set_save_mode('ajax')
            ->set_ajax_return('ajax_return');

        switch ($p_get[C__GET__SETTINGS_PAGE]) {
            case C__LDAPPAGE__SERVERTYPES:
                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES);
                $l_id = ($_GET['id'] ?: (isset($_POST['id']) ? (is_array($_POST['id']) ? array_pop($_POST['id']) : $_POST['id']) : null));

                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                    $l_mapping = [
                        C__LDAP_MAPPING__GROUP        => $_POST["LDAP_MAP__GROUP"],
                        C__LDAP_MAPPING__OBJECT_CLASS => $_POST["LDAP_MAP__OBJECTCLASS"],
                        C__LDAP_MAPPING__FIRSTNAME    => $_POST["LDAP_MAP__GIVENNAME"],
                        C__LDAP_MAPPING__LASTNAME     => $_POST["LDAP_MAP__SURNAME"],
                        C__LDAP_MAPPING__MAIL         => $_POST["LDAP_MAP__MAIL"],
                        C__LDAP_MAPPING__USERNAME     => $_POST["LDAP_MAP__USERNAME"]
                    ];

                    if (isset($l_id)) {
                        $l_ret = $l_ldap->save_ldap_directory($_POST["C__MODULE__LDAP_TYPE__TITLE"], $_POST["C__MODULE__LDAP_TYPE__CONST"], $l_mapping, $l_id);
                    } else {
                        $l_ret = $l_ldap->create_ldap_directory($_POST["C__MODULE__LDAP_TYPE__TITLE"], $_POST["C__MODULE__LDAP_TYPE__CONST"], $l_mapping);
                    }

                    if ($l_ret) {
                        isys_notify::success($language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                    } else {
                        isys_notify::error($language->get('LC__INFOBOX__DATA_WAS_NOT_SAVED') . ' : ' . $database->get_last_error_as_string());
                    }

                    // Die, because this is an ajax request.
                    die;
                }

                $l_edit_right = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__SERVERTYPES);

                if ($l_id || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
                    $l_rules = [];

                    // Display single-view, if id is set.
                    if ($l_id > 0) {
                        $l_types = $l_ldap->get_ldap_types($l_id)
                            ->__to_array();

                        $l_rules["C__MODULE__LDAP_TYPE__TITLE"]["p_strValue"] = $l_types["isys_ldap_directory__title"];

                        if ($l_types["isys_ldap_directory__const"] == "C__LDAP__AD" || $l_types["isys_ldap_directory__const"] == "C__LDAP__NDS" ||
                            $l_types["isys_ldap_directory__const"] == "C__LDAP__OPENLDAP") {
                            $l_rules["C__MODULE__LDAP_TYPE__CONST"]["p_bDisabled"] = true;
                        }

                        $l_rules["C__MODULE__LDAP_TYPE__CONST"]["p_strValue"] = $l_types["isys_ldap_directory__const"];

                        $l_mapping = unserialize($l_types["isys_ldap_directory__mapping"]);
                        $l_rules["LDAP_MAP__GROUP"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__GROUP];
                        $l_rules["LDAP_MAP__OBJECTCLASS"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__OBJECT_CLASS];
                        $l_rules["LDAP_MAP__GIVENNAME"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__FIRSTNAME];
                        $l_rules["LDAP_MAP__SURNAME"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__LASTNAME];
                        $l_rules["LDAP_MAP__MAIL"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__MAIL];
                        $l_rules["LDAP_MAP__USERNAME"]["p_strValue"] = $l_mapping[C__LDAP_MAPPING__USERNAME];
                    }

                    switch ($_POST[C__GET__NAVMODE]) {
                        case C__NAVMODE__NEW:
                        case C__NAVMODE__EDIT:
                            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                            break;
                        default:
                            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                            break;
                    }

                    $template->assign('dirID', $l_id)
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                } else {
                    $l_list = $this->get_server_types();

                    $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

                    $template->assign("g_list", $l_list)
                        ->assign('content_title', $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__DIRECTORIES'));
                }

                $template
                    ->include_template(
                        'contentbottomcontent',
                        isys_application::instance()->app_path . "/src/themes/default/smarty/templates/content/bottom/content/module__ldap_server_types.tpl"
                    );

                break;
            default:
            case C__LDAPPAGE__CONFIG:

                isys_auth_system::instance()
                    ->check(isys_auth::VIEW, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);

                /* Delete */
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__PURGE) {
                    foreach ($_POST["id"] as $l_server_id) {
                        if ($l_ldap->delete_server($l_server_id)) {
                            // Server $l_server_id deleted successfully
                        }
                    }
                    unset($_POST["id"]);
                }

                // Entry id
                $l_id = (isset($_GET["id"]) ? ($_GET["id"] >
                0 ? $_GET["id"] : null) : (isset($_POST["id"]) ? (is_array($_POST["id"]) ? array_pop($_POST["id"]) : ($_POST["id"] > 0 ? $_POST["id"] : null)) : null));

                /* Connection test */
                if ($_GET["connection_test"] == "1") {
                    $l_debug_level = 0;
                    if (isset($_GET['debug'])) {
                        if ($_GET['debug'] == 6 || $_GET['debug'] == 7) {
                            $l_debug_level = $_GET['debug'];
                        }
                    }

                    try {
                        $this->debug("Testing connection to " . $_POST["C__MODULE__LDAP__HOST"] . ":" . $_POST["C__MODULE__LDAP__PORT"] . " (" .
                            $_POST["C__MODULE__LDAP__DN"] . ")");

                        // If password has not been changed then we have to retrieve it from the database,
                        // because the post for the password field is '*******'.
                        if ($_POST["C__MODULE__LDAP__PASS__action"] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                            // retrieve password from database because it did not change
                            $_POST["C__MODULE__LDAP__PASS"] = isys_helper_crypt::decrypt($l_ldap->get_data($l_id)
                                ->get_row_value('isys_ldap__password'));
                        }

                        $l_ldapi = isys_library_ldap::factory(
                            $_POST["C__MODULE__LDAP__HOST"],
                            $_POST["C__MODULE__LDAP__DN"],
                            $_POST["C__MODULE__LDAP__PASS"],
                            $_POST["C__MODULE__LDAP__PORT"],
                            $_POST["C__MODULE__LDAP__VERSION"],
                            $_POST["C__MODULE__LDAP__TLS"],
                            $l_debug_level
                        )
                            ->set_logger(self::get_logger());

                        if (!$l_ldapi->connected()) {
                            $this->debug("Connection failed: " . $l_ldapi->get_last_error());

                            die("<p class=\"exception text-bold p5\">Error! Could not connect to " . "{$_POST["C__MODULE__LDAP__HOST"]}:{$_POST["C__MODULE__LDAP__PORT"]} using " .
                                "{$_POST["C__MODULE__LDAP__DN"]}!<br />" . "{$l_ldapi->get_last_error()}<br />" . "LDAP-Error: {$l_ldapi->get_ldap_error()}</p>");
                        } else {
                            $this->debug("Connection successfull.");

                            echo "<p class=\"box-green text-bold p5\">" . "Connection OK!<br />";

                            if ($_POST["C__MODULE__LDAP__RECURSIVE"] > 0) {
                                $l_scope = C__LDAP_SCOPE__RECURSIVE;
                            } else {
                                $l_scope = C__LDAP_SCOPE__SINGLE;
                            }

                            if (is_countable($_POST['field_value']) && count($_POST['field_value']) > 0) {
                                foreach ($_POST['field_value'] as $l_key => $l_val) {
                                    if ($l_val === '') {
                                        die("<p class=\"exception text-bold p5\">Error!<br />" . $language->get('LC__INFOBOX__LDAP__ERROR__VALUE_IN_FILTER_MUST_NOT_BE_EMPTY') . "</p>");
                                    }

                                    $l_arr[$l_key] = $l_val;
                                }
                            }

                            // New Filter
                            $l_filter_arr = [
                                "attributes"      => $_POST['field_title'],
                                "values"          => $_POST['field_value'],
                                "field_type"      => $_POST['field_type'],
                                "field_link_type" => $_POST['field_link_type'],
                                "field_operator"  => $_POST['field_operator']
                            ];

                            $l_filter = $this->create_filter_string($l_filter_arr);

                            if (!$l_filter) {
                                $l_filter = $_POST["C__MODULE__LDAP__FILTER"];
                            }

                            $l_res = $l_ldapi->search(
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $l_filter,
                                [],
                                0,
                                0,
                                (!empty($_POST["C__MODULE__LDAP__TIMELIMIT"]) ? intval($_POST["C__MODULE__LDAP__TIMELIMIT"]) : C__DEFAULT__TIMELIMIT),
                                null,
                                $l_scope
                            );

                            if ($l_res && ($l_count = $l_ldapi->count($l_res))) {
                                $this->debug("Found {$l_count} object(s) in {$_POST["C__MODULE__LDAP__SEARCH"]}.");
                                echo "<strong>{$l_count}</strong>  or more object(s) found in {$_POST["C__MODULE__LDAP__SEARCH"]}.";
                            } else {
                                $this->debug("No objects found in your configurated OU. No one will be able to login into i-doit. Check filter and search-dn.");
                                echo "No object found. That means that no one will be able to login with the current setup. " . "Check your filter and search-dn.";
                            }

                            echo "</p>";

                            die();
                        }
                    } catch (Exception $e) {
                        die("<p class=\"exception text-bold p5\">Error!<br /><br />" . $e->getMessage() . "</p>");
                    }
                }

                /**
                 * init
                 */
                $l_filter_arr = null;
                $l_filter = '';
                $l_arr = [];

                /* Save*/
                if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                    if (empty($_POST["C__MODULE__LDAP__ACTIVE"])) {
                        $_POST["C__MODULE__LDAP__ACTIVE"] = "0";
                    }

                    if (is_array($_POST['field_value']) && count($_POST['field_value']) > 0) {
                        $l_message = $language->get('LC__INFOBOX__LDAP__ERROR__VALUE_IN_FILTER_MUST_NOT_BE_EMPTY');

                        foreach ($_POST['field_value'] as $l_key => $l_val) {
                            if ($l_val === '') {
                                die("<p class=\"exception text-bold p5\">Error! " . $language->get('LC__INFOBOX__LDAP__ERROR__COULD_NOT_SAVE') . " <br />" . $l_message . "</p>");
                            }

                            $l_arr[$l_key] = $l_val;
                        }
                    }

                    header('Content-Type: application/json');

                    $l_filter_arr = [
                        "attributes"      => $_POST['field_title'],
                        "values"          => $l_arr,
                        "field_type"      => $_POST['field_type'],
                        "field_link_type" => $_POST['field_link_type'],
                        "field_operator"  => $_POST['field_operator']
                    ];

                    $l_filter = $this->create_filter_string($l_filter_arr);

                    if (!$l_filter) {
                        $l_filter = $_POST["C__MODULE__LDAP__FILTER"];
                    }

                    try {
                        if (isset($l_id)) {
                            $l_password = $_POST["C__MODULE__LDAP__PASS"];

                            if ($_POST["C__MODULE__LDAP__PASS__action"] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                                $l_password = null;
                            }

                            $l_ldap->save_server(
                                $_POST["C__MODULE__LDAP__DIRECTORY"],
                                $_POST["C__MODULE__LDAP__HOST"],
                                $_POST["C__MODULE__LDAP__PORT"],
                                $_POST["C__MODULE__LDAP__DN"],
                                $l_password,
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $_POST["C__MODULE__LDAP__SEARCH_GROUP"],
                                $l_filter,
                                $_POST["C__MODULE__LDAP__ACTIVE"],
                                $_POST["C__MODULE__LDAP__TIMELIMIT"],
                                $_POST["C__MODULE__LDAP__RECURSIVE"],
                                $_POST["C__MODULE__LDAP__TLS"],
                                $_POST["C__MODULE__LDAP__VERSION"],
                                $l_id,
                                $l_filter_arr,
                                !!$_POST['C__MODULE__LDAP__USE_ADMIN_ONLY'],
                                $_POST['C__MODULE__LDAP__ENABLE_PAGING'],
                                ($_POST['C__MODULE__LDAP__PAGE_LIMIT'] ?: 500),
                                ((int)$_POST['C__MODULE__LDAP__UNIQUE_ATTRIBUTE'] < 0 ? null: $_POST['C__MODULE__LDAP__UNIQUE_ATTRIBUTE'])
                            );
                        } else {
                            $l_password = $_POST["C__MODULE__LDAP__PASS"];

                            if ($_POST["C__MODULE__LDAP__PASS__action"] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                                $l_password = null;
                            }

                            $l_ldap->create_server(
                                $_POST["C__MODULE__LDAP__DIRECTORY"],
                                $_POST["C__MODULE__LDAP__HOST"],
                                $_POST["C__MODULE__LDAP__PORT"],
                                $_POST["C__MODULE__LDAP__DN"],
                                $l_password,
                                $_POST["C__MODULE__LDAP__SEARCH"],
                                $_POST["C__MODULE__LDAP__SEARCH_GROUP"],
                                $l_filter,
                                $_POST["C__MODULE__LDAP__ACTIVE"],
                                $_POST["C__MODULE__LDAP__TIMELIMIT"],
                                $_POST["C__MODULE__LDAP__RECURSIVE"],
                                $_POST["C__MODULE__LDAP__TLS"],
                                $_POST["C__MODULE__LDAP__VERSION"],
                                $l_filter_arr,
                                !!$_POST['C__MODULE__LDAP__USE_ADMIN_ONLY'],
                                $_POST['C__MODULE__LDAP__ENABLE_PAGING'],
                                $_POST['C__MODULE__LDAP__PAGE_LIMIT'],
                                ((int)$_POST['C__MODULE__LDAP__UNIQUE_ATTRIBUTE'] < 0 ? null: $_POST['C__MODULE__LDAP__UNIQUE_ATTRIBUTE'])
                            );
                        }

                        $response = ['success' => true, 'data' => null, 'message' => ''];
                        isys_notify::success($language->get('LC__INFOBOX__DATA_WAS_SAVED'));
                    } catch (Exception $e) {
                        $l_message = $e->getMessage();

                        $response = ['success' => false, 'data' => null, 'message' => $l_message];
                        isys_notify::error($l_message, ['sticky' => true]);
                    }

                    // Die, because this is an ajax request.
                    echo isys_format_json::encode($response);
                    die();
                }

                $l_edit_right = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);
                $l_delete_right = isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::DELETE, 'LDAP/' . C__MODULE__LDAP . C__LDAPPAGE__CONFIG);

                // New or Edit.
                if ((isset($l_id) || $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) && $_POST[C__GET__NAVMODE] != C__NAVMODE__PURGE) {
                    $l_data = $l_filter_data = [];

                    // Single-View Mode.

                    if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW || $_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                        $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                        $l_edit_mode = 1;
                    } else {
                        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                        $l_edit_mode = 0;
                    }

                    $l_versions = [
                        "1" => "1",
                        "2" => "2",
                        "3" => "3"
                    ];

                    $l_rules["C__MODULE__LDAP__ACTIVE"]["p_arData"] = get_smarty_arr_YES_NO();
                    $l_rules["C__MODULE__LDAP__PORT"]["p_strValue"] = "389";
                    $l_rules["C__MODULE__LDAP__TIMELIMIT"]["p_strValue"] = "30";
                    $l_rules["C__MODULE__LDAP__FILTER"]["p_strValue"] = "(objectClass=user)";
                    $l_rules["C__MODULE__LDAP__FILTER"]["p_bReadonly"] = true;
                    $l_rules["C__MODULE__LDAP__VERSION"]["p_arData"] = $l_versions;
                    $l_rules["C__MODULE__LDAP__VERSION"]["p_strSelectedID"] = "3";
                    $l_rules["C__MODULE__LDAP__ENABLE_PAGING"]["p_arData"] = get_smarty_arr_YES_NO();
                    $l_rules["C__MODULE__LDAP__PAGE_LIMIT"]["p_strValue"] = 500;

                    $l_rules["C__MODULE__LDAP__UNIQUE_ATTRIBUTE"]["p_arData"] = $this->getCustomProperties();

                    /**
                     * Configuring ldap encoding field
                     */
                    $l_rules["C__MODULE__LDAP__TLS"] = [
                        'p_arData' =>[
                            LdapUrlGenerator::LDAP_ENCODING_OFF      => isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__NO'),
                            LdapUrlGenerator::LDAP_ENCODING_STARTTLS => 'STARTTLS (Standard Port: 389)',
                            LdapUrlGenerator::LDAP_ENCODING_TLS      => 'LDAPS (Standard Port: 636)',
                        ],
                        'p_bDbFieldNN' => 1,
                        'p_strSelectedID' => LdapUrlGenerator::LDAP_ENCODING_OFF,
                        'p_bSort' => false,
                        'p_onChange' => 'setPortByEncoding()',
                    ];

                    if ($l_id > 0) {
                        $l_data = $l_ldap->get_data($l_id)
                            ->__to_array();
                        $l_filter_exists = false;
                        $l_filter_array_exists = false;
                        if (strlen($l_data["isys_ldap__filter_array"]) > 0) {
                            $l_filter_data = unserialize($l_data["isys_ldap__filter_array"]);

                            if (is_array($l_filter_data)) {
                                $l_filter_array_exists = true;
                                if (!empty($l_filter_data["attributes"][0]) && !empty($l_filter_data["values"][0])) {
                                    $l_filter = $this->create_filter_string(unserialize($l_data["isys_ldap__filter_array"]));
                                    $l_filter_exists = true;
                                }
                            }
                        }

                        if (!$l_filter_exists && !$l_filter_array_exists) {
                            $l_filter_data = [
                                'attributes'      => ['objectClass'],
                                'values'          => ['user'],
                                'field_type'      => ['3'],
                                'field_link_type' => ['&'],
                                'field_operator'  => ['=']
                            ];
                            $l_filter = "(objectClass=user)";
                        }

                        $template->assign("g_recursive", $l_data["isys_ldap__recursive"]);
                        $template->assign("g_use_admin_only", $l_data["isys_ldap__use_admin_only"]);

                        $l_rules["C__MODULE__LDAP__TLS"]["p_strSelectedID"] = $l_data["isys_ldap__tls"];
                        $l_rules["C__MODULE__LDAP__VERSION"]["p_strSelectedID"] = $l_data["isys_ldap__version"];
                        $l_rules["C__MODULE__LDAP__ACTIVE"]["p_strSelectedID"] = $l_data["isys_ldap__active"];
                        $l_rules["C__MODULE__LDAP__DIRECTORY"]["p_strSelectedID"] = $l_data["isys_ldap__isys_ldap_directory__id"];
                        $l_rules["C__MODULE__LDAP__TITLE"]["p_strValue"] = $l_data["isys_ldap__title"];
                        $l_rules["C__MODULE__LDAP__TIMELIMIT"]["p_strValue"] = $l_data["isys_ldap__timelimit"];
                        $l_rules["C__MODULE__LDAP__HOST"]["p_strValue"] = $l_data["isys_ldap__hostname"];
                        $l_rules["C__MODULE__LDAP__PORT"]["p_strValue"] = $l_data["isys_ldap__port"];
                        $l_rules["C__MODULE__LDAP__DN"]["p_strValue"] = $l_data["isys_ldap__dn"];
                        $l_rules["C__MODULE__LDAP__PASS"]["p_strValue"] = isys_helper_crypt::decrypt($l_data["isys_ldap__password"]);
                        $l_rules["C__MODULE__LDAP__SEARCH"]["p_strValue"] = $l_data["isys_ldap__user_search"];
                        $l_rules["C__MODULE__LDAP__SEARCH_GROUP"]["p_strValue"] = $l_data["isys_ldap__group_search"];
                        $l_rules["C__MODULE__LDAP__FILTER"]["p_strValue"] = $l_filter;
                        $l_rules["C__MODULE__LDAP__ENABLE_PAGING"]["p_strSelectedID"] = $l_data["isys_ldap__enable_paging"];
                        $l_rules["C__MODULE__LDAP__PAGE_LIMIT"]["p_strValue"] = $l_data["isys_ldap__page_limit"];
                        $l_rules["C__MODULE__LDAP__UNIQUE_ATTRIBUTE"]["p_strSelectedID"] = $l_data["isys_ldap__unique_attribute"];

                        if ($l_data['isys_ldap_directory__const'] == 'C__LDAP__OPENLDAP') {
                            $template->assign('simulate_openldap_search', true)
                                ->assign('ldap_directory_value', $l_data["isys_ldap__isys_ldap_directory__id"]);
                        }

                        if (!$l_filter_exists && !$l_filter_array_exists) {
                            $template->assign("filter_message", $language->get("LC__LDAP__FILTER__MESSAGE"));
                        }
                    } else {
                        $l_filter_data = [
                            'attributes'      => ['objectClass'],
                            'values'          => ['user'],
                            'field_type'      => ['3'],
                            'field_link_type' => ['&'],
                            'field_operator'  => ['=']
                        ];
                    }

                    // @see  ID-6588  After creating new ports (one or many) we now jump to the list.
                    if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
                        $getParameters = $_GET;

                        unset($getParameters[C__GET__ID]);

                        $link = isys_helper_link::create_url($getParameters);

                        $callback = "function(xhr) {if (xhr.responseJSON && xhr.responseJSON.success) {window.location = '{$link}';}}";
                        $saveOnclick = "document.isys_form.navMode.value='" . C__NAVMODE__SAVE . "';";
                        $saveOnclick .= "form_submit('', 'post', 'no_replacement', null, {$callback});";

                        $l_navbar->set_js_onclick($saveOnclick, C__NAVBAR_BUTTON__SAVE);
                    }

                    $template->assign("filter_arr", $l_filter_data)
                        ->assign("entryID", isset($l_data['isys_ldap__id']) ? $l_data['isys_ldap__id'] : null)
                        ->assign("isEditMode", $l_edit_mode)
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                } else {
                    // List-Mode.
                    $l_navbar->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                        ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                        ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW)
                        ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                        ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

                    $l_list = $this->get_server_list();

                    $template->assign("content_title", $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__SERVER'))
                        ->assign("g_list", $l_list);
                }

                $template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                    ->include_template('contentbottomcontent', 'content/bottom/content/module__ldap.tpl');

                break;
        }
    }

    /**
     * Get configured data from Settng "CMDB settings" -> "category extension"
     *
     * @return array
     * @throws Exception
     */
    public function getCustomProperties()
    {
        $dao = isys_cmdb_dao_category_s_person_master::instance(isys_application::instance()->container->get('database'));
        $customProperties = $dao->get_custom_properties();
        $data = [];
        foreach ($customProperties as $customProperty) {
            $title = $customProperty[Property::C__PROPERTY__INFO][Property::C__PROPERTY__INFO__TITLE];
            if (strpos($title, 'Custom') !== false) {
                continue;
            }
            $data[$title] = $title;
        }
        return $data;
    }

    /**
     * Get database field by custom attribute
     *
     * @param string $customAttribute
     *
     * @return string|null
     */
    public function getCustomPropertyDbField($customAttribute)
    {
        $dao = isys_cmdb_dao_category_s_person_master::instance(isys_application::instance()->container->get('database'));
        $customProperties = $dao->get_custom_properties();
        $data = [];
        foreach ($customProperties as $customProperty) {
            if ($customProperty[Property::C__PROPERTY__INFO][Property::C__PROPERTY__INFO__TITLE] === $customAttribute) {
                return $customProperty[Property::C__PROPERTY__DATA][Property::C__PROPERTY__DATA__FIELD];
            }
        }
        return null;
    }

    /**
     * Creates an ldap filter
     *
     * @param $p_filter_arr
     *
     * @return bool|null|string
     */
    public function create_filter_string($p_filter_arr)
    {
        if (isset($p_filter_arr["attributes"]) && is_array($p_filter_arr["attributes"])) {
            $l_attributes = $p_filter_arr["attributes"];
            $l_values = $p_filter_arr["values"];
            $l_types = $p_filter_arr["field_type"];
            $l_link_types = $p_filter_arr["field_link_type"];
            $l_operators = $p_filter_arr["field_operator"];

            $l_keys = array_keys($l_attributes);

            $l_arr = [];

            if (count($l_keys) > 0) {
                if (count($l_attributes) == 1) {
                    if ((empty($l_attributes[0]) && empty($l_values[0])) || (!empty($l_attributes[0]) && empty($l_values[0]))) {
                        return null;
                    }
                }

                foreach ($l_keys as $l_key) {
                    $l_string = "";

                    if ($l_operators[$l_key] == "!=") {
                        $l_prefix = "!";
                        $l_string = "(";
                    } else {
                        $l_prefix = "";
                    }

                    switch ($l_operators[$l_key]) {
                        case ">=":
                            $l_operator = ">=";
                            break;
                        case "<=":
                            $l_operator = "<=";
                            break;
                        default:
                            $l_operator = "=";
                            break;
                    }

                    if ($l_attributes[$l_key] && $l_values[$l_key]) {
                        $l_string .= $l_prefix . "(" . $l_attributes[$l_key] . $l_operator . ($l_values[$l_key]) . ")";

                        if (strlen($l_prefix) > 0) {
                            $l_string .= ")";
                        }

                        $l_arr[$l_key] = $l_string;
                    }
                }

                return $this->build_filter($l_arr, $l_types, $l_link_types);
            }
        }

        return false;
    }

    /**
     * Returns ldap library by isys_ldap__id.
     *
     * @param   integer $p_ldap_id
     * @param   null    &$p_connection_info This parameter will get overwritten anyways.
     *
     * @throws  Exception
     * @return isys_library_ldap
     */
    public function get_library_by_id($p_ldap_id, &$p_connection_info)
    {
        // get isys_ldap_dao.
        $l_dao = $this->get_dao();

        // retrieve server configuration data.
        $l_ldap = $l_dao->get_active_servers($p_ldap_id)
            ->__to_array();

        // fill reference with ldap connection information.
        $p_connection_info = $l_ldap;

        try {
            // return library.
            return $this->get_library(
                $l_ldap["isys_ldap__hostname"],
                $l_ldap["isys_ldap__dn"],
                isys_helper_crypt::decrypt($l_ldap["isys_ldap__password"]),
                $l_ldap["isys_ldap__port"],
                $l_ldap["isys_ldap__version"],
                $l_ldap["isys_ldap__tls"]
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns internal ldap library.
     *
     * @param   string  $p_host
     * @param   string  $p_user_dn
     * @param   string  $p_pass
     * @param   integer $p_port
     * @param   integer $p_protocol_version
     * @param   boolean $p_tls
     *
     * @return isys_library_ldap
     * @throws Exception
     */
    public function get_library($p_host, $p_user_dn, $p_pass, $p_port = 389, $p_protocol_version = 3, $p_tls = LdapUrlGenerator::LDAP_ENCODING_OFF)
    {
        if ($p_port < 0 || empty($p_port)) {
            $p_port = 389;
        }

        if ($p_protocol_version < 1 || empty($p_protocol_version)) {
            $p_protocol_version = 3;
        }

        if (empty($p_tls)) {
            $p_tls = LdapUrlGenerator::LDAP_ENCODING_OFF;
        }

        try {
            $this->debug("Creating new ldap-library connection to: " . $p_host . ":" . $p_port . ", user: " . $p_user_dn);

            return isys_library_ldap::factory($p_host, $p_user_dn, $p_pass, $p_port, $p_protocol_version, $p_tls)
                ->set_logger(self::get_logger());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the corresponding i-doit ldap group by the ldap name
     *
     * @param array $groupNames
     *
     * @return string
     */
    private function get_idoit_group(array $groupNames)
    {
        $l_return = '';

        if (count($groupNames)) {
            global $g_comp_database;

            //@todo ID-5812: Somehow database is only available here via global!
            $l_dao_groups = isys_cmdb_dao_category_s_person_group_master::instance($g_comp_database);

            //$this->debug("Querying LDAP group: ". $groupNames);

            $sanitizedGroupNames = array_map(function ($name) use ($l_dao_groups) {
                return $l_dao_groups->convert_sql_text($name);
            }, $groupNames);

            $l_idoit_group = $l_dao_groups->get_data(null, null, " AND (isys_cats_person_group_list__ldap_group IN (" . implode(',', $sanitizedGroupNames) . "))");

            if ($l_idoit_group->num_rows() > 0) {
                $l_idoit_group_data = $l_idoit_group->__to_array();
                $l_return = $l_idoit_group_data;

                $this->debug(' ** i-doit group pendant for "' . implode('" or "', $groupNames) . '" found: ' . $l_return["isys_cats_person_group_list__title"]);
            } else {
                $this->debug(' -- Group pendant for "' . implode('" or "', $groupNames) . '" not found. Set an LDAP-Mapping in your corresponding person group if you want to use this as a right group.');
            }
        } else {
            $this->debug("Group name empty in get_idoit_group()");

            return false;
        }

        return $l_return;
    }

    /**
     * Builds an LDAP-Filter as String
     *
     * @param array $p_arr
     * @param array $p_types
     * @param array $p_link_types
     *
     * @return string
     */
    private function build_filter($p_arr, $p_types, $p_link_types)
    {
        $l_counter = 0;

        $l_string = '';
        $l_last_key = -1;

        if (is_countable($p_arr) && count($p_arr) > 1) {
            foreach ($p_arr as $l_key => $l_val) {
                if (isset($p_arr[$l_last_key]) && $p_arr[$l_last_key]) {
                    switch ($p_types[$l_key]) {
                        case 1:
                            if ($l_counter == 0) {
                                $l_counter = 1;
                            }

                            $l_string = $l_string . $l_val;
                            break;
                        case 2:
                            if ($l_counter == 0) {
                                $l_counter = 2;
                            } else {
                                $l_counter++;
                            }

                            $l_string = $l_string . "(" . $p_link_types[$l_key] . $l_val;
                            break;
                        case 3:

                            if ($l_counter > 0) {
                                $l_string = substr($l_string, 0, strlen($l_string) - 1);
                            }
                            for ($i = 0;$i <= $l_counter;$i++) {
                                $l_string .= ")";
                            }

                            $l_counter = 1;
                            $l_string = "(" . $p_link_types[$l_key] . $l_string . $l_val;

                            break;
                        default:
                            break;
                    }
                } else {
                    $l_string = "(" . $p_link_types[$l_key] . $l_val;
                }
                $l_last_key = $l_key;
            }

            for ($i = 0;$i < $l_counter;$i++) {
                $l_string .= ")";
            }

            if ($l_counter == 0) {
                $l_string .= ")";
            }
        } else {
            return $p_arr[0];
        }

        return $l_string;
    }
}
