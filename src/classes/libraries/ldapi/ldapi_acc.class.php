<?php
/**
 * LDAP Library Wrapper - Accounting
 *
 * Please also check the php documentation:
 * http://www.php.net/manual/en/ref.ldap.php
 *
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @copyright Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0 - 10.06.2008
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 *
 */

if (!class_exists("ldapi")) {
    trigger_error("Class ldapi is required for the ldapi accounting extension.\n", E_USER_ERROR);
}

class ldapi_acc extends ldapi
{

    private $m_directory_type = ldapi::DIR__OTHER;

    private $m_idattribute = "cn";

    private $m_search_path = "";

    /**
     * @param string $p_attribute
     */
    public function set_idattribute($p_attribute)
    {
        $this->m_idattribute = $p_attribute;
    }

    /**
     * @param string $p_sp
     */
    public function set_search_path($p_sp)
    {
        $this->m_search_path = $p_sp;
    }

    /**
     * @return string
     */
    public function get_search_path()
    {
        return $this->m_search_path;
    }

    /**
     * Set directory type
     *
     * @param int $p_dt
     */
    public function set_directory_type($p_dt)
    {
        $this->m_directory_type = $p_dt;
    }

    /**
     * Get configured directory type
     *
     * @return int
     */
    public function get_directory_type()
    {
        return $this->m_directory_type;
    }

    /**
     * Returns corresponding dn by username using the specified search path.
     * (->set_search_path("OU=y,DC=x"))
     *
     * @param string $p_username
     *
     * @return boolean
     */
    public function get_dn_by_username($p_username)
    {
        if (($l_res = $this->search($this->m_search_path, "(" . $this->m_idattribute . "=" . $p_username . ")", ["cn"]))) {
            $l_ar = $this->get_entries($l_res);

            return (isset($l_ar[0]["dn"])) ? $l_ar[0]["dn"] : false;
        }

        return false;
    }

    /**
     * Try authentication with username and password
     *
     * @param string $p_username
     * @param string $p_password
     *
     * @return boolean
     */
    public function try_auth($p_username, $p_password)
    {
        if (str_word_count($p_username, null, '=') === 0) { // @See ID-5205 method get_dn_by_username cannot filter with a DN String
            $l_username = $this->get_dn_by_username($p_username);
        } else {
            $l_username = $p_username;
        }

        if (!empty($l_username)) {
            return $this->bind($l_username, $p_password);
        }
    }

    public function __construct($p_hostname = null)
    {
        parent::__construct($p_hostname);
    }
}

?>
