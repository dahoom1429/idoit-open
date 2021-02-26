<?php

/**
 * i-doit
 *
 * DAO: specific category for the basic auth-system implementation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 * @author      Leonard Fischer <lfischer@i-doit.com>
 */
class isys_cmdb_dao_category_s_basic_auth extends isys_cmdb_dao_category_s_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'basic_auth';

    /**
     * Entry-Counter.
     *
     * @return  integer
     */
    public function get_count($p_objid = null)
    {
        return 1;
    }

    /**
     * Return Category Data - This works a bit different than other "get_data" methods.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT *
			FROM isys_auth
			LEFT JOIN isys_module ON isys_module__id = isys_auth__isys_module__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_auth__isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE TRUE ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_cats_list_id !== null) {
            $l_sql .= 'AND isys_auth__id = ' . $this->convert_sql_id($p_cats_list_id) . ' ';
        }

        if ($p_obj_id !== null) {
            if (is_array($p_obj_id)) {
                $l_sql .= 'AND isys_auth__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ' ';
            } else {
                $l_sql .= 'AND isys_auth__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ';
            }
        }

        if ($p_status !== null) {
            $l_sql .= 'AND isys_auth__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        $l_sql .= $p_condition . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function properties()
    {
        return [];
    }

    /**
     * Creates the distrubtion connector entry and returns its id.
     * If obj_id is null, the method takes it from $_GET parameter.
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Method for saving the element.
     *
     * @param   boolean $p_create
     *
     * @return  integer  The error code or null on success.
     */
    public function save_user_data($p_create)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_dao = isys_auth_dao::instance($this->m_db);

        // Remove all old paths of the given person / persongroup.
        $l_dao->remove_all_paths($l_obj_id);

        // Create new paths.
        foreach ($_POST as $l_key => $l_value) {
            if (strpos($l_key, 'module_') === 0) {
                $l_path_nr = substr($l_key, 7);

                if (!isset($_POST['right_' . $l_path_nr])) {
                    continue;
                }

                $l_rights = $_POST['right_' . $l_path_nr];

                if (in_array(isys_auth::SUPERVISOR, $l_rights)) {
                    $l_rights = isys_helper::split_bitwise((isys_auth::SUPERVISOR * 2) - 1);
                }

                $data = [];
                $authInstance = isys_module_manager::instance()
                    ->get_module_auth($l_value);

                if ($authInstance) {
                    $authMethods = $authInstance->get_auth_methods();

                    foreach ($authMethods as $method => $options) {
                        if (!isset($options['rights']) || empty($options['rights'])) {
                            $options['rights'] = isys_helper::split_bitwise((isys_auth::SUPERVISOR * 2) - 1);
                        }

                        // Only write rights, that are allowed by the method.
                        $data[$method] = ['*' => array_intersect((array)$options['rights'], (array)$l_rights)];
                    }
                }

                if (defined($l_value)) {
                    $l_dao->create_paths($l_obj_id, constant($l_value), $data);
                }
            }
        }
    }
}