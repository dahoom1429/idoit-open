<?php

use idoit\Component\Logger;
use idoit\Context\Context;

/**
 * i-doit
 * Session manager. Providers basic session management
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_session extends isys_component
{
    const MIN_SESSION_TIME = 60;
    const MAX_SESSION_TIME = 99999;

    /**
     * @var  isys_component_session
     */
    private static $m_instance = null;

    /**
     * @var  array
     */
    protected $m_mandator_data = [];

    /**
     * @var  integer
     */
    protected $m_mandator_id;

    /**
     * @var  string
     */
    protected $m_mandator_name;

    /**
     * Session specific error messages.
     *
     * @var  array
     */
    private $m_err;

    /**
     * @var  string
     */
    private $m_language;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var  boolean
     */
    private $m_logged_in = false;

    /**
     * @var  isys_module_ldap
     */
    private $m_mod_ldap = null;

    /**
     * @var  array
     */
    private $m_session_data;

    /**
     * @var  string
     */
    private $m_session_id;

    /**
     * @var  integer
     *
     * Set initial value to a very high value, since sessions get cleaned on a login with this default value.
     * The original session time for each mandator is only available AFTER logging in.
     */
    private $m_session_time = 99999999;

    /**
     * @var  integer
     */
    private $m_user_id;

    /**
     * @var  array
     */
    private $m_userdata = [];

    /**
     * @var  string
     */
    private $m_username = null;

    /**
     * Get singleton instance.
     *
     * @param   isys_module_ldap $p_ldap_module
     *
     * @return  \isys_component_session
     */
    final public static function instance(isys_module_ldap $p_ldap_module = null)
    {
        if (!self::$m_instance) {
            self::$m_instance = new self($p_ldap_module);
        }

        return self::$m_instance;
    }

    /**
     * @param  integer $p_session_time
     */
    public function set_session_time($p_session_time)
    {
        if ($p_session_time > 0) {
            $this->m_session_time = $p_session_time;
        }
    }

    /**
     * @return  integer
     */
    public function get_session_time()
    {
        return $this->m_session_time;
    }

    /**
     * Checks if given session time is a valid value
     *
     * @param $sessionTime
     *
     * @return bool
     * @author Kevin Mauel <kmauel@i-doit.com>
     */
    public static function isValidSessionTime($sessionTime)
    {
        return $sessionTime >= self::MIN_SESSION_TIME && $sessionTime <= self::MAX_SESSION_TIME;
    }

    /**
     * @return  string
     */
    public function get_language()
    {
        return $this->m_language;
    }

    /**
     * Return _SESSION key
     *
     * @param $p_key
     *
     * @return null
     */
    public function get($p_key)
    {
        return isset($_SESSION[$p_key]) ? $_SESSION[$p_key] : null;
    }

    /**
     * @param   string $p_language
     *
     * @return  $this
     */
    public function set_language($p_language)
    {
        global $g_idoit_language_short;

        if (!$p_language) {
            // Initialize a default language (since $g_idoit_language_short is used by the init.php scripts to include the language file).
            $p_language = 'en';
        }

        $g_idoit_language_short = $_SESSION["lang"] = $this->m_language = $p_language;

        isys_application::instance()
            ->language($p_language);

        return $this;
    }

    /**
     * @return array
     */
    public function get_userdata()
    {
        return $this->m_userdata;
    }

    /**
     * @return mixed
     */
    public function get_current_username()
    {
        return $this->m_username;
    }

    /**
     * @return array
     */
    public function get_mandator_data()
    {
        return $this->m_mandator_data;
    }

    /**
     * @return isys_module_ldap
     */
    public function get_ldap_module()
    {
        return $this->m_mod_ldap;
    }

    /**
     * @return mixed
     */
    public function get_user_id()
    {
        if (!$this->m_user_id) {
            $l_sessdata = $this->get_session_data();

            $this->m_user_id = $l_sessdata["isys_user_session__isys_obj__id"];
        }

        return $this->m_user_id;
    }

    /**
     * @param      $p_msg
     * @param null $p_key
     */
    public function add_error($p_msg, $p_key = null)
    {
        if (!is_null($p_key)) {
            $this->m_err[$p_key] = $p_msg;
        } else {
            $this->m_err[] = $p_msg;
        }
    }

    /**
     * @return array
     */
    public function get_errors()
    {
        return $this->m_err;
    }

    /**
     * @param $p_mname
     *
     * @return isys_component_session
     */
    public function set_mandator_name($p_mname)
    {
        $this->m_mandator_name = $p_mname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function get_mandator_name()
    {
        return $this->m_mandator_name;
    }

    /**
     * @return mixed
     */
    public function get_mandator_id()
    {
        return $this->m_mandator_id;
    }

    /**
     * @return mixed
     */
    public function get_user_session_id()
    {
        $l_sessdata = $this->get_session_data();

        return $l_sessdata["isys_user_session__id"];
    }

    /**
     * Is the user logged in? TRUE, if yes, FALSE, if error or no.
     *
     * @return  boolean
     */
    public function is_logged_in()
    {
        if (is_array($this->m_session_data) && isset($this->m_session_data["isys_user_session__isys_obj__id"])) {
            // Returns true, if session is _not_ binded to a guest user.
            return true;
        } else {
            if (!count($_SESSION)) {
                return false;
            }

            if ($this->m_session_data) {
                return true;
            }
        }

        return false;
    }

    /**
     * Change current mandator.
     * Works only if username and password are same for the new mandator. Uses current user to double check.
     *
     * @param   integer $p_mandator_id
     *
     * @return  boolean
     * @throws  Exception
     */
    public function change_mandator($p_mandator_id)
    {
        $db = isys_application::instance()->container->get('database');

        if (is_object($db) && $db->is_connected() && $p_mandator_id > 0) {
            $l_person_dao = new isys_cmdb_dao_category_s_person_master($db);
            $l_data = $l_person_dao->get_person_by_username($this->get_current_username())
                ->__to_array();

            if ($l_data) {
                try {
                    $this->connect_mandator($p_mandator_id);
                    $this->delete_current_session();
                    $this->start_dbsession();

                    if ($this->login($db, $l_data['isys_cats_person_list__title'], $l_data['isys_cats_person_list__user_pass'], true, true)) {
                        if (defined('C__MODULE__PRO') && C__MODULE__PRO && defined('C__ENABLE__LICENCE') && C__ENABLE__LICENCE === true &&
                            class_exists('isys_module_licence')) {
                            // Overwrite Licence related keys in session from previous mandator
                            // todo licensing 2.0
                            $l_licence = new isys_module_licence();
                            $l_licence->verify();
                        }

                        return true;
                    }
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }

        return false;
    }

    /**
     * Returns dao for mandator.
     *
     * @param    integer $p_mandator_id
     *
     * @return   resource
     * @version  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function get_mandator_dao($p_mandator_id)
    {
        return (new isys_component_dao_mandator)->get_mandator_query($p_mandator_id);
    }

    /**
     * Does a complete weblogin, including session storage,
     * session initialization and mandator selection
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function weblogin($p_user, $p_pass, $p_mandator, $p_md5 = false)
    {
        $db = isys_application::instance()->container->get('database');

        if (!$this->is_logged_in() && $p_user && $p_pass) {
            if (is_numeric($p_mandator)) {
                if ($this->connect_mandator($p_mandator) !== null) {
                    if (is_object($db) && $db->is_connected()) {
                        $this->renewSessionId();
                        $this->delete_current_session();
                        $this->start_dbsession();

                        return $this->login($db, $p_user, $p_pass, true, $p_md5);
                    }
                } else {
                    throw new Exception('Could not connect to tenant with id ' . $p_mandator);
                }
            } else {
                throw new Exception("Login failed. Either username (" . $p_user . "), password or tenant (" . $p_mandator . ") information is not correct.");
            }
        }

        return $this->is_logged_in();
    }

    /**
     * Does a mandator login based on its apikey
     *
     * @param       $p_apikey
     * @param array $p_userdata
     * @param int   $p_session_id
     *
     * @throws \Exception
     * @throws \isys_exception_api
     * @return bool
     */
    public function apikey_login($p_apikey, $p_userdata = null, $p_session_id = null)
    {
        $db = isys_application::instance()->container->get('database');

        if (!$this->is_logged_in() && !empty($p_apikey)) {
            foreach ($this->fetchTenants($p_apikey) as $mandator) {
                $this->connect_mandator($mandator['isys_mandator__id']);

                if ($db->is_connected()) {
                    // Check for an existing session
                    if ($p_session_id && strlen($p_session_id) > 2) {
                        // Try to connect to an existing session
                        $l_session_data = $this->get_session_data($p_session_id);

                        if ($this->is_logged_in()) {
                            // Post init session and write session data to $_SESSION
                            $this->post_init_session($l_session_data);

                            return true;
                        }
                    }

                    $this->delete_current_session();
                    $this->start_dbsession();

                    if ($p_userdata) {
                        return $this->login($db, $p_userdata['username'], $p_userdata['password'], true);
                    } else {
                        // API option 'api.authenticated-users-only' is activated and no user is specified
                        if (isys_settings::get('api.authenticated-users-only', 1)) {
                            throw new isys_exception_api('Please specify a user by RPC Session header or HTTP Basic Authentication.', isys_api_controller_jsonrpc::ERR_Auth);
                        }

                        $l_object = $db->query('SELECT isys_obj__id, isys_cats_person_list__isys_obj__id, isys_cats_person_list__title, isys_cats_person_list__first_name, isys_cats_person_list__last_name, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address ' .
                            'FROM isys_obj INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id ' .
                            'LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1 ' .
                            'WHERE isys_obj__const = \'C__OBJ__PERSON_API_SYSTEM\';');

                        if ($l_object) {
                            $l_object_data = $db->fetch_row_assoc($l_object);

                            if ($l_object_data && isset($l_object_data['isys_cats_person_list__isys_obj__id'])) {
                                $this->post_init_session($l_object_data);

                                return true;
                            }
                        }
                    }
                }
                throw new Exception('Could not connect tenant database.');
            }

            return false;
        }

        return $this->is_logged_in();
    }

    /**
     * @param array $p_cats_person_data
     *
     * @return void
     */
    public function write_userdata($p_cats_person_data)
    {
        if (isset($p_cats_person_data["isys_cats_person_list__isys_obj__id"])) {
            $this->m_logged_in = true;

            $this->write_userid($p_cats_person_data["isys_cats_person_list__isys_obj__id"]);

            $_SESSION["username"] = $this->m_username = $p_cats_person_data['isys_cats_person_list__title'];
            $this->m_userdata = [
                'name'  => $p_cats_person_data["isys_cats_person_list__first_name"] . ' ' . $p_cats_person_data["isys_cats_person_list__last_name"],
                'email' => isset($p_cats_person_data["isys_cats_person_list__mail_address"]) ? $p_cats_person_data["isys_cats_person_list__mail_address"] : '',
                'id'    => $p_cats_person_data["isys_cats_person_list__isys_obj__id"]
            ];
        }
    }

    /**
     * @return array|NULL
     *
     * @param integer $p_mandator_id
     *
     * @desc Connects to a mandator specified by $p_mandator_id, writes session info
     *       and saves the database access object on container's database
     */
    public function connect_mandator($p_mandator_id)
    {
        global $g_mandator_info;

        $database_system = isys_application::instance()->container->get('database_system');
        $db = isys_application::instance()->container->get('database');

        $l_res = $this->get_mandator_dao($p_mandator_id);

        if ($database_system->num_rows($l_res)) {
            $l_dbdata = $database_system->fetch_row_assoc($l_res);

            if ($l_dbdata) {
                try {
                    /**
                     * Create tenant database
                     *
                     * @return isys_component_database
                     * @throws Exception
                     */
                    $db->setDatabase(isys_component_database::get_database(
                        $this->getSystemDbType(),
                        $l_dbdata["isys_mandator__db_host"],
                        $l_dbdata["isys_mandator__db_port"],
                        $l_dbdata["isys_mandator__db_user"],
                        isys_component_dao_mandator::getPassword($l_dbdata),
                        $l_dbdata["isys_mandator__db_name"]
                    ));

                    // Create connection to mandator DB
                    $g_mandator_info = $l_dbdata;

                    $this->set_mandator_name($l_dbdata["isys_mandator__title"]);

                    $this->m_mandator_data = $l_dbdata;
                    $this->m_mandator_id = (int)$p_mandator_id;
                    $_SESSION["user_mandator"] = (int)$p_mandator_id;
                    $this->m_logged_in = $this->is_logged_in();

                    return $l_dbdata;
                } catch (isys_exception_database $e) {
                    isys_application::instance()->container->notify->error($e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * @return array
     *
     * @param string $p_username
     * @param string $p_password
     * @param bool   $p_md5_pass
     *
     * @desc On login, we need to know the mandators, to which
     *       a user is allowed to connect. This function returns
     *       an array with an option array for Smarty in this
     *       format:
     *
     *       <code>
     *        array(
     *         idoit_system.isys_mandator.isys_mandator__id =>
     *          idoit_system.isys_mandator.isys_mandator__title
     *        );
     *       </code>
     *
     *       There can't be real failure, but if there is one,
     *       the array length is also 0. Take a look at the debugger
     *       to see occured errors/warnings.
     */
    public function fetch_mandators($p_username, $p_password, $p_md5_pass = false)
    {
        $l_mandants = [];

        if (!$p_md5_pass) {
            $l_md5_pass = md5($p_password);
        } else {
            $l_md5_pass = $p_password;
        }

        $mandators = $this->fetchTenants();

        if (!empty($mandators)) {
            foreach ($mandators as $mandator) {
                // Create connection to mandator DB
                $db = isys_component_database::get_database(
                    $this->getSystemDbType(),
                    $mandator["isys_mandator__db_host"],
                    $mandator["isys_mandator__db_port"],
                    $mandator["isys_mandator__db_user"],
                    isys_component_dao_mandator::getPassword($mandator),
                    $mandator["isys_mandator__db_name"]
                );

                // Setting database component in proxy
                isys_application::instance()->container->get('database')->setDatabase($db);

                if ($db->is_connected()) {
                    /* Get user dao and user data */
                    $l_user_dao = new isys_cmdb_dao_category_s_person_master($db);
                    $l_userdata = $l_user_dao->get_person_by_username($p_username, C__RECORD_STATUS__NORMAL)
                        ->get_row();
                    $l_user_id = -1;

                    /* Try other auths, if user does not exist in database. */
                    if ($l_userdata) {
                        // Set user id if password was accepted.
                        if ($l_userdata['isys_cats_person_list__user_pass'] === $l_md5_pass) {
                            $l_user_id = $l_userdata["isys_obj__id"];
                        }
                    }

                    if ($l_user_id === -1) {
                        if (!isset($l_ldap_done[$mandator["isys_mandator__db_name"]])) {
                            $l_user_id = $this->ldap_login($p_username, $p_password, null, $l_user_dao);
                        }
                    }

                    // Check if user_id is allowed to login.
                    if ($l_user_id > 0) {
                        // Removed: isys_rs_system, check if the given user owns at least one group / role.
                        $l_mandants[$mandator["isys_mandator__id"]] = [
                            'id'      => $mandator["isys_mandator__id"],
                            'user_id' => $l_user_id,
                            'title'   => $mandator["isys_mandator__title"]
                        ];

                        /*
                        if ($p_with_language)
                        {
                            $l_locale             = isys_locale::get($db, $l_user_id);
                            $l_language_constant  = $l_locale->get_setting(LC_LANG);
                            $l_preferred_language = $l_locale->resolve_language_by_constant($l_language_constant);
                            $l_locale->reset_cache();
                            $l_mandants[$mandator["isys_mandator__id"]]['preferred_language'] = $l_preferred_language;
                        }
                        */
                    }

                    $l_ldap_done[$mandator["isys_mandator__db_name"]] = true;
                }
            }

            if (count($l_mandants) === 0) {
                // Log failed login:
                $this->logger->warning('Login failed for user ' . $p_username, [
                    'username'   => $p_username,
                    'ip-address' => $_SERVER['REMOTE_ADDR'],
                    'user-agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
            }

            return $l_mandants;
        }

        return null;
    }

    /**
     * @param string                                 $p_username
     * @param string                                 $p_password
     * @param string                                 $p_userdn
     * @param isys_cmdb_dao_category_s_person_master $p_user_dao
     */
    public function ldap_login($p_username, $p_password, $p_userdn, $p_user_dao)
    {
        /* Check LDAP Auth, if module is installed and an LDAP-Server is registered for the current mandator */
        if (is_object($this->m_mod_ldap)) {
            try {
                /* Call session login in ldap module */
                if ($this->m_mod_ldap->session_login($p_username, $p_password, $p_userdn, $p_user_dao)) {
                    $l_obj_id = $p_user_dao->get_person_id_by_username($p_username, C__RECORD_STATUS__NORMAL);
                    if ($l_obj_id) {

                        // @see  ID-7673  Remove the auth cache of the logged in user and immediately renew it.
                        isys_auth::invalidateUserCache((int) $l_obj_id);

                        return $l_obj_id;
                    } else {
                        throw new Exception('Error: User was improperly created.');
                    }
                }
            } catch (Exception $e) {
                $this->m_mod_ldap->debug($e->getMessage());
            }
        }

        return false;
    }

    /**
     * Logs a user with $p_username and $p_password in to the database specified by $p_db.
     *
     *
     * @param   isys_component_database $p_db
     * @param   string                  $p_username
     * @param   string                  $p_password
     * @param   boolean                 $p_retbool
     * @param   boolean                 $p_md5pass
     * @param   bool                    $directLogin - defines if the user can login without more checks
     *
     * @return  boolean
     * @throws Exception
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function login(isys_component_database &$p_db, $p_username, $p_password, $p_retbool = false, $p_md5pass = false, $directLogin = false)
    {
        $l_password = ($p_md5pass == false) ? md5($p_password) : $p_password;

        // Search the user.
        $l_res = $this->query_session($p_db, $p_username);

        if ($p_db->num_rows($l_res) >= 1) {
            $this->m_logged_in = $directLogin;

            $l_row = $p_db->fetch_row_assoc($l_res);
            if (!$this->m_logged_in) {
                if ($l_row["isys_cats_person_list__user_pass"] == $l_password) {
                    $this->m_logged_in = true;
                } else {
                    if (is_object($this->m_mod_ldap)) {
                        if ($this->m_mod_ldap->ldap_login($p_db, $p_username, $p_password, null, null, $l_row["isys_obj__id"])) {
                            $this->m_logged_in = true;
                        }
                    }
                }
            }

            if ($this->m_logged_in) {
                /**
                 * User is logged in, so post init session
                 */
                $this->post_init_session($l_row);

                $p_db->query('UPDATE isys_cats_person_list SET isys_cats_person_list__last_login = NOW() WHERE isys_cats_person_list__isys_obj__id = ' .
                    (int)$l_row["isys_obj__id"] . ';');

                return ($p_retbool) ? true : $l_row;
            }
        }
        // Log failed login:
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (Context::instance()->getOrigin() == Context::ORIGIN_CONSOLE) {
            $user_agent = Context::ORIGIN_CONSOLE;
        }
        $this->logger->warning('Login failed for user ' . $p_username, [
            'username'   => $p_username,
            'ip-address' => $_SERVER['REMOTE_ADDR'],
            'user-agent' => $user_agent
        ]);

        return false;
    }

    /**
     * Renew the session id to prevent session fixation attack
     */
    public function renewSessionId()
    {
        $db = isys_application::instance()->container->get('database');

        $oldId = session_id();
        if (defined('WEB_CONTEXT') && WEB_CONTEXT) {
            session_regenerate_id(true);
        }
        $newId = session_id();

        // replace old session key in database to the new one
        if (is_object($db) && $db->is_connected()) {
            $db->begin();
            $db->query('UPDATE isys_user_session SET isys_user_session__php_sid="' . $newId . '" WHERE (isys_user_session__php_sid = "' . $db->escape_string($oldId) . '");');
            $db->commit();
        }
    }

    /**
     * Destroys the current session.
     *
     * @return boolean
     */
    public function destroy()
    {
        try {
            if (session_id()) {
                $params = session_get_cookie_params();

                session_unset();
                // Generate a new session id and delte the old session file
                // Session can only be regenerated if session is active
                if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                    session_regenerate_id(true); // Prevent's session fixation | see ID-3433
                }
                if (@session_destroy()) {
                    if (!headers_sent()) {
                        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                        // session_id(sha1(uniqid(microtime()))); // Sets a random ID for the session
                    }

                    return true;
                }
            }

            return false;
        } catch (ErrorException $e) {
            // session already closed
        }
    }

    /**
     * session_write_close wrapper
     */
    public function write_close()
    {
        @session_write_close();

        return $this;
    }

    /**
     * @return string
     */
    public function get_tenant_cache_dir()
    {
        if ($this->m_mandator_data && isset($this->m_mandator_data["isys_mandator__dir_cache"])) {
            return $this->m_mandator_data["isys_mandator__dir_cache"];
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function include_mandator_cache()
    {
        if ($this->m_mandator_data["isys_mandator__dir_cache"]) {
            return isys_component_constant_manager::instance()
                ->include_dcm($this->m_mandator_data["isys_mandator__dir_cache"]);
        } else {
            throw new Exception('Error: Tenant cache directory in system database (dir_cache) is not set');
        }
    }

    /**
     * @return boolean
     * @desc Perform user logout, returns a boolean result:
     *       true for success and false for failure.
     */
    public function logout()
    {
        $db = isys_application::instance()->container->get('database');
        /* Cache Session ID before calling session_destroy() */
        $l_SesID = $this->get_session_id();

        /* Drop current session from database */
        $this->delete_current_session();

        if ($this->destroy()) {
            $this->m_username = null;
            $this->m_logged_in = false;
            $this->m_mandator_id = null;
            $this->m_mandator_name = null;
            $this->m_mandator_data = null;
            $this->m_session_id = null;
            $this->m_user_id = null;
            unset($this->m_session_data);
        }

        //delete temporary tables which are not used currently
        if (is_object($db) && $db->is_connected()) {
            $l_objDAOTable = new isys_component_dao_table($db);
            $l_objDAOTable->clean_temp_tables_at_logout($l_SesID);
        }

        return true;
    }

    /**
     * Starts a session.
     *
     * @return  boolean
     */
    public function start_session()
    {
        if (isset($this->session_started) && $this->session_started) {
            return true;
        }

        if (!headers_sent()) {
            $l_session_name = ini_get('session.name');

            if (isset($_COOKIE[$l_session_name]) && $_COOKIE[$l_session_name] == '') {
                unset($_COOKIE[$l_session_name]);
            }

            $l_session_params = session_get_cookie_params();

            // Check if the ini settings have been set - if not, try to set force it.
            if (!$l_session_params['httponly'] || !$l_session_params['secure']) {
                session_set_cookie_params(
                    $l_session_params['lifetime'] ?: 0,
                    $l_session_params['path'] ?: '/',
                    $l_session_params['domain'] ?: '',
                    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'),
                    true
                );
            }
            // special handling of session id for API call
            if (isset($_SERVER['HTTP_X_RPC_AUTH_SESSION'])) {
                session_id($_SERVER['HTTP_X_RPC_AUTH_SESSION']);
            }
            $l_ret = session_start();
            $this->m_username = $_SESSION["username"];
            if (isset($_SESSION['ip'], $_SESSION['user_agent'])) {
                if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                    // Then it destroys the session.
                    $this->destroy();
                }
            }
            $this->remember_user();
        } else {
            $l_ret = false;
        }

        $this->session_started = $l_ret;

        return $l_ret;
    }

    /**
     * Load Mandator Info from db and save into session
     *
     * @param isys_component_database $databaseSystem
     *
     * @throws Exception
     */
    public function initMandatorSession(isys_component_database $databaseSystem)
    {
        if ($this->start_session()) {
            // Override session language - At this point, $this->language is only set when isys_application::instance()->set_language('xyz') was called beforehand.
            if (isys_application::instance()->language) {
                $this->set_language(isys_application::instance()->language);
            }

            isys_application::instance()->container->get('database_system')
                ->setDatabase($databaseSystem);

            // Check if mandator is set yet and connects database for current mandator.
            if ($userMandatorId = $this->get("user_mandator")) {
                //connect_mandator function without creating database service
                $l_res = $this->get_mandator_dao($userMandatorId);

                if ($databaseSystem->num_rows($l_res)) {
                    $l_dbdata = $databaseSystem->fetch_row_assoc($l_res);

                    if ($l_dbdata) {
                        $this->set_mandator_name($l_dbdata["isys_mandator__title"]);

                        $this->m_mandator_data = $l_dbdata;
                        $this->m_mandator_id = $userMandatorId;
                        $_SESSION["user_mandator"] = $userMandatorId;
                        $this->m_logged_in = $this->is_logged_in();
                        $this->connect_mandator($this->m_mandator_id);
                    }
                }
            }
        } else {
            $l_err = "Unable to start session!";
            if (headers_sent()) {
                $l_err .= "\nHeaders already sent. There should not be any output before the session starts!";
            }

            throw new Exception($l_err);
        }
    }

    /**
     * Remember client's ip address and user agent
     *
     * Used to prevent session hijacking attacks
     *
     * @return $this
     */
    public function remember_user()
    {
        // Remember ip address and user agent.
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR']; // Saves the user's IP
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT']; // Saves the user's navigator

        return $this;
    }

    /**
     * Initialize a session in the database.
     *
     * @return  integer
     */
    public function start_dbsession()
    {
        global $_SERVER;
        $db = isys_application::instance()->container->get('database');

        if (is_object($db) && $db->is_connected()) {
            $l_res = $this->query_session($db, null, $this->get_session_id());

            // Is a user session existent ..?
            if ($db->num_rows($l_res) == 0) {
                // NO - so add the session.
                $l_query = "INSERT INTO isys_user_session SET
				    isys_user_session__isys_obj__id = 1,
				    isys_user_session__php_sid = '" . $db->escape_string($this->get_session_id()) . "',
				    isys_user_session__time_login = CURRENT_TIMESTAMP,
				    isys_user_session__time_last_action = CURRENT_TIMESTAMP,
				    isys_user_session__description = '" . $db->escape_string($_SERVER['REQUEST_URI']) . "',
				    isys_user_session__ip = '" . $db->escape_string($_SERVER['REMOTE_ADDR']) . "';";

                if ($db->query($l_query)) {
                    return $db->get_last_insert_id();
                }
            } else {
                // YES - update and use existing session.
                $l_query = "UPDATE isys_user_session SET
					isys_user_session__time_last_action = CURRENT_TIMESTAMP,
					isys_user_session__description = '" . $db->escape_string($_SERVER['REQUEST_URI']) . "'
					WHERE isys_user_session__php_sid = '" . $db->escape_string($this->get_session_id()) . "'";

                $l_query = $db->limit_update($l_query, 1);

                if ($db->query($l_query)) {
                    return $this->get_session_id();
                }
            }
        }

        return null;
    }

    /**
     * Returns the session record as associative array.
     *
     * @param   string $p_sessionid
     *
     * @return  array
     */
    public function get_session_data($p_sessionid = null)
    {
        if (!$this->m_session_data) {
            $db = isys_application::instance()->container->get('database');

            if ($p_sessionid === null) {
                $p_sessionid = $this->get_session_id();
            }

            if (strlen($p_sessionid) > 0) {
                if ($db && $db->is_connected()) {
                    $l_res = $this->query_session($db, null, $p_sessionid);

                    if ($db->num_rows($l_res)) {
                        // User is logged in, so post initialize session.
                        $this->post_init_session($db->fetch_array($l_res));
                    }
                }
            }
        }

        return $this->m_session_data;
    }

    /**
     * @param $p_session_id
     *
     * @return $this
     */
    public function set_session_id($p_session_id)
    {
        $this->m_session_id = session_id($p_session_id);

        return $this;
    }

    /**
     * Returns the current session id.
     *
     * @return  string
     */
    public function get_session_id()
    {
        if (!$this->m_session_id) {
            $this->m_session_id = session_id();
        }

        return $this->m_session_id;
    }

    /**
     * Delete the expired sessions.
     *
     * @return  $this
     */
    public function delete_expired_sessions()
    {
        $db = isys_application::instance()->container->get('database');
        $sessionTimeoutTime = $db->date_sub('SECOND', $this->m_session_time, 'NOW()');

        if (is_object($db) && $db->is_connected()) {
            $query = <<<SQL
DELETE FROM isys_user_session
  WHERE
    isys_user_session__time_last_action < {$sessionTimeoutTime}
SQL;
            $db->begin();
            $db->query($query);
            $db->commit();

            if ($this->is_logged_in()) {
                $query = <<<SQL
SELECT isys_user_session__id FROM isys_user_session
  WHERE
    isys_user_session__time_last_action > {$sessionTimeoutTime}
    AND isys_user_session__isys_obj__id = {$this->get_user_id()}
SQL;
                // Check if user still has a valid session after possibly outdated sessions were deleted
                if ($db->num_rows($db->query($query)) === 0) {
                    // .. and do a logout if not.
                    $this->logout();  // @see ID-3670

                    if (isset($_GET["ajax"])) {
                        // Relocate via javascript if this is an ajax request
                        echo "<script type=\"text/javascript\">document.location='?timeout';</script>";
                        die();
                    } else {
                        // Relocate via Apache if not.
                        header('Location: ?timeout');
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Delete the current session from database.
     *
     * @return  void
     */
    public function delete_current_session()
    {
        $db = isys_application::instance()->container->get('database');

        $_SESSION['groups'] = null;

        if (is_object($db) && $db->is_connected()) {
            $db->begin();
            $db->query('DELETE FROM isys_user_session WHERE (isys_user_session__php_sid = "' . $db->escape_string($this->get_session_id()) . '");');
            $db->commit();
        }
    }

    /**
     * Initialization, which is only possible after user has logged in
     *
     * @param $p_session_data
     */
    protected function post_init_session($p_session_data)
    {
        // Store session data (very important to do this here)
        $this->m_session_data = $p_session_data;
        $this->write_userdata($p_session_data);

        // Include and write mandator cache
        $this->include_mandator_cache();

        /**
         * Initialize user settings
         */
        try {
            //@todo: settings inits can be removed soon because it's already inited in DI
            isys_usersettings::initialize(isys_application::instance()->container->database);
            isys_tenantsettings::initialize(isys_application::instance()->container->database_system, $this->get_mandator_id());

            // Now that the settings are initialized, we can set the session time for this tenant
            $this->set_session_time(isys_tenantsettings::get('session.time', 300));

            // Delete expired sessions
            $this->delete_expired_sessions();
        } catch (Exception $e) {
            isys_glob_display_error($e->getMessage());
            die();
        }

        // Re-set the language (if necessary).
        $l_lang = $this->get_language();

        if (!$l_lang) {
            if (isset($_SESSION['lang']) && $_SESSION['lang']) {
                $l_lang = $_SESSION['lang'];
            } else {
                if (isys_application::instance()->container->locales) {
                    $l_lang = isys_application::instance()->container->locales->resolve_language_by_constant(isys_locale::get_instance()
                        ->get_setting(LC_LANG)) ?: isys_tenantsettings::get('system.default-language', 'en');
                } else {
                    // Reinit user locales
                    isys_locale::get_instance()
                        ->init($this->get_user_id());
                    $l_lang = isys_locale::get_instance()
                        ->resolve_language_by_constant(isys_locale::get_instance()
                            ->get_setting(LC_LANG)) ?: isys_tenantsettings::get('system.default-language', 'en');
                }
            }
        }

        global $g_comp_template_language_manager;
        isys_application::instance()->container->get('language')
            ->load($l_lang);
        // Assign language manager from container to global for legacy calls
        $g_comp_template_language_manager = isys_application::instance()->container->get('language');
        $this->set_language($l_lang);
        isys_module_manager::instance()
            ->init(isys_module_request::get_instance());
    }

    /**
     * @return boolean
     *
     * @param integer $p_userid
     *
     * @desc Write a User-ID to a session. It is private and used by the
     *       method 'login'.
     */
    private function write_userid($p_userid)
    {
        $db = isys_application::instance()->container->get('database');

        $this->m_user_id = $p_userid;

        if ($p_userid > 0) {
            $l_query = "UPDATE isys_user_session SET isys_user_session__isys_obj__id ='" . $db->escape_string($p_userid) . "' " . "WHERE isys_user_session__php_sid='" .
                $this->get_session_id() . "';";

            return (bool)$db->query($l_query);
        }

        throw new Exception('There was a problem writing your user id to session.');
    }

    /**
     * Query isys_mandator in system database.
     *
     * @param   string  $p_apikey
     * @param   boolean $p_onlyactive
     *
     * @return  mixed
     */
    private function fetchTenants($p_apikey = null, $p_onlyactive = true)
    {
        $systemDb = isys_application::instance()->container->get('database_system');

        $l_condition = '';

        if ($p_apikey) {
            $l_condition .= ' AND (isys_mandator__apikey = \'' . $systemDb->escape_string($p_apikey) . '\')';
        }

        if ($p_onlyactive) {
            $l_condition .= ' AND (isys_mandator__active = 1)';
        }

        if (!($l_sortby = isys_tenantsettings::get('login.tenantlist.sortby'))) {
            $l_sortby = 'isys_mandator__title';
        }

        $res = $systemDb->query('SELECT * FROM isys_mandator WHERE TRUE ' . $l_condition . ' ORDER BY ' . $l_sortby . ' ASC');
        $result = [];
        while ($row = $systemDb->fetch_row_assoc($res)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Query isys_user_session in mandator database
     *
     * @param $p_session_id
     *
     * @return mixed
     */
    private function query_session(isys_component_database &$p_db, $p_username = null, $p_session_id = null, $p_condition = '')
    {
        $l_condition = $p_condition;

        if ($p_session_id) {
            $l_condition .= ' AND isys_user_session__php_sid = "' . $p_db->escape_string($p_session_id) . '"';
        }

        if ($p_username) {
            $l_condition .= ' AND ( BINARY LOWER(isys_cats_person_list__title) = LOWER(\'' . $p_db->escape_string($p_username) . '\'))';
        }

        $joinCondition = 'isys_obj__status = \'' . C__RECORD_STATUS__NORMAL . '\'';

        if ($p_db->is_field_existent('isys_cats_person_list', 'isys_cats_person_list__disabled_login')) {
            $joinCondition .= ' AND isys_cats_person_list__disabled_login = 0';
        }

        $query = 'SELECT isys_obj__id, isys_obj__title, isys_cats_person_list__isys_obj__id, isys_cats_person_list__title, isys_cats_person_list__first_name, ' .
            'isys_cats_person_list__user_pass, isys_cats_person_list__last_name, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, isys_user_session.* ' .
            'FROM isys_cats_person_list ' . 'INNER JOIN isys_obj ON isys_cats_person_list__isys_obj__id = isys_obj__id ' .
            'LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1 ' .
            'LEFT JOIN isys_user_session ON isys_user_session__isys_obj__id = isys_obj__id ' . 'WHERE (' . $joinCondition . ')' .
            $l_condition;

        return $p_db->query($query);
    }

    /**
     * Private wakeup method to prevent multiple instances via unserialization.
     */
    final private function __wakeup()
    {
        ;
    }

    /**
     * Private clone method to prevent multiple instances.
     */
    final private function __clone()
    {
        ;
    }

    /**
     * Constructing session management.
     *
     * @param isys_module_ldap|null $p_ldap_module
     */
    private function __construct(isys_module_ldap $p_ldap_module = null)
    {
        // LDAP Module dependency injection.
        $this->m_mod_ldap = $p_ldap_module;

        $logPath = isys_tenantsettings::get('system.log.path', BASE_DIR . 'log') . DS . 'failed-logins_' . date('Y-m-d') . '.log';
        $this->logger = Logger::factory('login', $logPath);
    }

    /**
     * Get the type of database
     *
     * @return string
     */
    private function getSystemDbType()
    {
        global $g_db_system;

        return $g_db_system["type"] ? $g_db_system["type"] : 'mysql';
    }
}
