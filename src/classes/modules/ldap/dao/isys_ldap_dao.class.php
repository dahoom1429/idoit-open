<?php

/**
 * i-doit
 *
 * LDAP Module Dao
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_ldap_dao extends isys_module_dao
{
    /**
     * Deletes a ldap server.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete_server($p_id)
    {
        return $this->m_db->query('DELETE FROM isys_ldap WHERE isys_ldap__id = ' . $this->convert_sql_id($p_id) . ';');
    }

    /**
     * Validates given arguments, returns false if one argument is empty.
     *
     * @return  boolean
     */
    public function validate()
    {
        $l_argv = func_get_args();

        foreach ($l_argv as $l_arg) {
            if ($l_arg == "-1" || $l_arg == "" || is_null($l_arg)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build Changes for update or insert
     *
     * @param      $p_directory_type
     * @param      $p_host
     * @param      $p_port
     * @param      $p_user_dn
     * @param      $p_pass
     * @param      $p_user_search
     * @param      $p_group_search
     * @param      $p_filter
     * @param      $p_active
     * @param int  $p_timelimit
     * @param int  $p_recursive
     * @param int  $p_tls
     * @param int  $p_version
     * @param null $p_filter_arr
     * @param bool $useAdminOnly
     * @param bool $enableLdapPaging
     * @param int  $pageLimit
     * @param string|null $uniqueAttribute
     *
     * @return array
     */
    public function getChanges(
        $p_directory_type,
        $p_host,
        $p_port,
        $p_user_dn,
        $p_pass,
        $p_user_search,
        $p_group_search,
        $p_filter,
        $p_active,
        $p_timelimit = 30,
        $p_recursive = 0,
        $p_tls = 0,
        $p_version = 3,
        $p_filter_arr = null,
        $useAdminOnly = false,
        $enableLdapPaging = false,
        $pageLimit = 500,
        $uniqueAttribute = null
    ) {
        $changes = [
            'isys_ldap__isys_ldap_directory__id' => $this->convert_sql_id($p_directory_type),
            'isys_ldap__hostname' => $this->convert_sql_text($p_host),
            'isys_ldap__port' => $this->convert_sql_int($p_port),
            'isys_ldap__dn' => $this->convert_sql_text($p_user_dn),
            'isys_ldap__user_search' => $this->convert_sql_text($p_user_search),
            'isys_ldap__group_search' => $this->convert_sql_text($p_group_search),
            'isys_ldap__filter' => $this->convert_sql_text($p_filter),
            'isys_ldap__active' => $this->convert_sql_boolean($p_active),
            'isys_ldap__recursive' => $this->convert_sql_boolean($p_recursive),
            'isys_ldap__timelimit' => $this->convert_sql_int($p_timelimit),
            'isys_ldap__tls' => $this->convert_sql_int($p_tls),
            'isys_ldap__version' => $this->convert_sql_int($p_version),
            'isys_ldap__enable_paging' => $this->convert_sql_boolean($enableLdapPaging),
            'isys_ldap__unique_attribute' => $this->convert_sql_text($uniqueAttribute)
        ];

        if ($pageLimit !== null) {
            $changes['isys_ldap__page_limit'] = $this->convert_sql_int($pageLimit);
        }

        if ($p_pass !== null) {
            $changes['isys_ldap__password'] = $this->convert_sql_text(isys_helper_crypt::encrypt($p_pass));
        }

        if ($useAdminOnly !== null) {
            $changes['isys_ldap__use_admin_only'] = $this->convert_sql_boolean($useAdminOnly);
        }

        if (is_array($p_filter_arr)) {
            $changes['isys_ldap__filter_array'] = $this->convert_sql_text(serialize($p_filter_arr));
        }

        return array_map(function ($k, $v) {
            return $k . ' = ' . $v;
        }, array_keys($changes), $changes);
    }

    /**
     * Saves a registered ldap server.
     *
     * @param integer $p_directory_type
     * @param string  $p_host
     * @param integer $p_port
     * @param string  $p_user_dn
     * @param string  $p_pass
     * @param string  $p_user_search
     * @param string  $p_group_search
     * @param string  $p_filter
     * @param integer $p_active
     * @param integer $p_timelimit
     * @param integer $p_recursive
     * @param integer $p_tls
     * @param integer $p_version
     * @param integer $p_id
     * @param array   $p_filter_arr
     * @param bool    $useAdminOnly
     * @param bool    $enableLdapPaging
     * @param integer $pageLimit
     * @param string|null    $uniqueAttribute
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    public function save_server(
        $p_directory_type,
        $p_host,
        $p_port,
        $p_user_dn,
        $p_pass,
        $p_user_search,
        $p_group_search,
        $p_filter,
        $p_active,
        $p_timelimit = 30,
        $p_recursive = 0,
        $p_tls = 0,
        $p_version = 3,
        $p_id,
        $p_filter_arr = null,
        $useAdminOnly = false,
        $enableLdapPaging = false,
        $pageLimit = 500,
        $uniqueAttribute = null
    ) {
        if ($this->validate($p_directory_type, $p_host, $p_port, $p_user_dn, $p_user_search)) {
            $changes = $this->getChanges(
                $p_directory_type,
                $p_host,
                $p_port,
                $p_user_dn,
                $p_pass,
                $p_user_search,
                $p_group_search,
                $p_filter,
                $p_active,
                $p_timelimit,
                $p_recursive,
                $p_tls,
                $p_version,
                $p_filter_arr,
                $useAdminOnly,
                $enableLdapPaging,
                $pageLimit,
                $uniqueAttribute
            );

            $l_sql = "UPDATE isys_ldap SET " . implode(', ', $changes);
            $l_sql .= " WHERE isys_ldap__id = " . $this->convert_sql_id($p_id) . ";";

            if ($this->update($l_sql) && $this->apply_update()) {
                return $this->get_last_insert_id();
            } else {
                return false;
            }
        } else {
            throw new Exception("Not all required fields are filled.");
        }
    }

    /**
     * Creates a new ldap server
     *
     * @param integer $p_directory_type
     * @param string  $p_host
     * @param integer $p_port
     * @param string  $p_user_dn
     * @param string  $p_pass
     * @param string  $p_user_search
     * @param string  $p_group_search
     * @param string  $p_filter
     * @param integer $p_active
     * @param integer $p_timelimit
     * @param integer $p_recursive
     * @param integer $p_tls
     * @param integer $p_version
     * @param array   $p_filter_arr
     * @param bool    $useAdminOnly
     * @param bool    $enableLdapPaging
     * @param integer $pageLimit
     * @param string|null    $uniqueAttribute
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function create_server(
        $p_directory_type,
        $p_host,
        $p_port,
        $p_user_dn,
        $p_pass,
        $p_user_search,
        $p_group_search,
        $p_filter,
        $p_active,
        $p_timelimit = 30,
        $p_recursive = 0,
        $p_tls = 0,
        $p_version = 3,
        $p_filter_arr = null,
        $useAdminOnly = false,
        $enableLdapPaging = false,
        $pageLimit = 500,
        $uniqueAttribute = null
    ) {
        if ($this->validate($p_directory_type, $p_host, $p_port, $p_user_dn, $p_pass, $p_user_search)) {
            if (!$this->exists($p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_filter)) {
                $changes = $this->getChanges(
                    $p_directory_type,
                    $p_host,
                    $p_port,
                    $p_user_dn,
                    $p_pass,
                    $p_user_search,
                    $p_group_search,
                    $p_filter,
                    $p_active,
                    $p_timelimit,
                    $p_recursive,
                    $p_tls,
                    $p_version,
                    $p_filter_arr,
                    $useAdminOnly,
                    $enableLdapPaging,
                    $pageLimit,
                    $uniqueAttribute
                );

                $l_sql = "INSERT INTO isys_ldap SET " . implode(', ', $changes);

                if ($this->update($l_sql) && $this->apply_update()) {
                    return $this->get_last_insert_id();
                }

                return false;
            } else {
                throw new Exception("Server already exists.");
            }
        } else {
            throw new Exception("Not all required fields are filled.");
        }
    }

    /**
     * Checks if a ldap server is already registered
     *
     * @param string $p_host
     * @param int    $p_port
     * @param string $p_user_dn
     * @param string $p_pass
     * @param        $p_user_search
     * @param        $p_filter
     *
     * @return boolean
     */
    public function exists($p_host, $p_port, $p_user_dn, $p_pass, $p_user_search, $p_filter)
    {
        $l_sql = "SELECT * FROM isys_ldap
			WHERE isys_ldap__hostname = '{$p_host}'
			AND isys_ldap__port = '{$p_port}'
			AND isys_ldap__dn = '{$p_user_dn}'
			AND isys_ldap__password = '" . isys_helper_crypt::encrypt($p_pass) . "'
			AND isys_ldap__user_search = '{$p_user_search}'
			AND isys_ldap__filter = '{$p_filter}';";
        $result = $this->retrieve($l_sql);

        return is_countable($result) && count($result) > 0;
    }

    /**
     * Saves an existing ldap type
     *
     * @param string $p_title
     * @param string $p_const
     * @param array  $p_mapping
     */
    public function save_ldap_directory($p_title, $p_const, $p_mapping, $p_id)
    {
        if ($p_id > 0) {
            $l_mapping = $this->format_mapping($p_mapping);

            $l_sql = "UPDATE isys_ldap_directory SET " . "isys_ldap_directory__title = '{$this->m_db->escape_string($p_title)}', ";

            if (!empty($p_const)) {
                $l_sql .= "isys_ldap_directory__const = '{$this->m_db->escape_string($p_const)}', ";
            }

            $l_sql .= "isys_ldap_directory__mapping = '{$l_mapping}', " . "isys_ldap_directory__status = '" . C__RECORD_STATUS__NORMAL . "' " .
                "WHERE (isys_ldap_directory__id = '{$p_id}');";

            return ($this->update($l_sql) && $this->apply_update());
        }

        return false;
    }

    /**
     * Creates a new ldap type.
     *
     * @param   string $p_title
     * @param   string $p_const
     * @param   array  $p_mapping
     *
     * @return  boolean
     */
    public function create_ldap_directory($p_title, $p_const, $p_mapping)
    {
        $l_sql = 'INSERT INTO isys_ldap_directory SET isys_ldap_directory__title = ' . $this->convert_sql_text($p_title) . ', ';

        if (!empty($p_const)) {
            $l_sql .= 'isys_ldap_directory__const = ' . $this->convert_sql_text($p_const) . ', ';
        }

        $l_sql .= 'isys_ldap_directory__mapping = ' . $this->convert_sql_text($this->format_mapping($p_mapping)) . ',
			isys_ldap_directory__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Returns mapping as array.
     *
     * @param   integer $p_id
     *
     * @return  array
     */
    public function get_mapping($p_id)
    {
        return unserialize($this->get_ldap_types($p_id)
            ->get_row_value('isys_ldap_directory__mapping'));
    }

    /**
     * Returns all registered ldap types.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_ldap_types($p_id = null)
    {
        $l_sql = 'SELECT * FROM isys_ldap_directory WHERE TRUE';

        if ($p_id !== null) {
            $l_sql .= ' AND isys_ldap_directory__id = ' . $this->convert_sql_id($p_id);
        }

        return $this->retrieve($l_sql . '  ORDER BY isys_ldap_directory__title ASC;');
    }

    /**
     * Returns only active and registered servers.
     *
     * @param   integer $p_id
     *
     * @return  isys_component_dao_result
     */
    public function get_active_servers($p_id = null)
    {
        return $this->get_data($p_id, 1);
    }

    /**
     * Returns ldap configurations.
     *
     * @param   integer $p_id
     * @param   integer $p_active
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_id = null, $p_active = null)
    {
        $l_sql = 'SELECT * FROM isys_ldap
			 LEFT JOIN isys_ldap_directory ON isys_ldap__isys_ldap_directory__id = isys_ldap_directory__id
			 WHERE TRUE';

        if ($p_id !== null) {
            $l_sql .= ' AND isys_ldap__id = ' . $this->convert_sql_id($p_id);
        }

        if ($p_active !== null) {
            $l_sql .= ' AND isys_ldap__active = ' . $this->convert_sql_id($p_active);
        }

        return $this->retrieve($l_sql . ' ORDER BY isys_ldap_directory__title, isys_ldap__hostname ASC;');
    }

    /**
     * Formats a mappign
     *
     * @param   mixed $p_mapping
     *
     * @return  string
     */
    private function format_mapping($p_mapping)
    {
        return serialize($p_mapping);
    }
}
