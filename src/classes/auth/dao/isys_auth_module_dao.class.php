<?php

/**
 * i-doit
 *
 * Auth: abstract dao class
 *
 * @package     i-doit
 * @subpackage  dao
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_auth_module_dao extends isys_component_dao
{
    /**
     * Static instance cache.
     *
     * @var  array
     */
    private static $m_instances = [];

    /**
     * Module ID
     */
    protected $m_module_id = null;

    /**
     * Method for cleaning the table isys_auth
     */
    abstract protected function cleanup($p_method = null);

    /**
     * Factory method, for resource gentle class instantiation.
     *
     * @global        isys_component_database
     *
     * @param   mixed $p_module
     *
     * @return  isys_auth
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function factory($p_module)
    {
        global $g_comp_database;

        if (!is_numeric($p_module)) {
            if (!defined($p_module)) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $p_module));
            }

            $p_module = constant($p_module);
        }
        try {
            // Check for existing instance.
            if (array_key_exists($p_module, self::$m_instances)) {
                return self::$m_instances[$p_module];
            } else {
                // No instance was found, we need to create one.
                $l_auth_instance = isys_module_manager::instance()
                    ->get_module_auth($p_module);

                if ($l_auth_instance !== false) {
                    $l_module_auth = str_replace('isys_auth', 'isys_auth_dao', get_class($l_auth_instance));

                    if (class_exists($l_module_auth)) {
                        self::$m_instances[$p_module] = new $l_module_auth($g_comp_database);

                        return self::$m_instances[$p_module]->set_module_id($p_module);
                    } else {
                        $l_module_row = isys_module_manager::instance()
                            ->get_modules($p_module)
                            ->get_row();

                        throw new isys_exception_general($l_module_row['isys_module__title'] . ' has no dao Class.');
                    }
                }
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }
    }

    /**
     * This method goes through all module dao classes and calls the cleanup method
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function cleanup_all()
    {
        global $g_comp_database;;

        $l_modules_res = isys_module_manager::instance()
            ->get_modules();
        $l_system_module = false;

        try {
            while ($l_row = $l_modules_res->get_row()) {
                $l_auth_instance = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);

                if (!$l_auth_instance) {
                    continue;
                }

                $l_module_auth = get_class($l_auth_instance);

                if ($l_module_auth !== false) {
                    if (strpos($l_module_auth, 'isys_auth_') === 0) {
                        $l_module_auth = str_replace('isys_auth', 'isys_auth_dao', $l_module_auth);
                    } else {
                        $l_module_auth = str_replace('isys', 'isys_auth_dao', str_replace('_auth', '', $l_module_auth));
                    }

                    if (class_exists($l_module_auth) && strpos($l_module_auth, 'dao') !== false) {
                        if ($l_module_auth == 'isys_auth_dao_system') {
                            if ($l_system_module) {
                                continue;
                            } else {
                                $l_system_module = true;
                            }
                        }

                        $l_dao_obj = new $l_module_auth($g_comp_database);
                        $l_dao_obj->set_module_id($l_row['isys_module__id'])
                            ->cleanup();
                    }
                }
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }

        return true;
    }

    /**
     * Sets current module id
     *
     * @param $p_module
     *
     * @return $this
     */
    public function set_module_id($p_module)
    {
        $this->m_module_id = $p_module;

        return $this;
    }

    /**
     * Method which checks existing values from auth paths.
     *
     * @param   string $p_method
     * @param   string $p_table
     * @param   string $p_check_field
     *
     * @throws  isys_exception_general
     * @return  isys_auth_dao
     */
    protected function cleanup_default($p_method, $p_table, $p_check_field)
    {
        $l_query = 'SELECT * FROM ' . $p_table . ' WHERE ' . $p_check_field . ' = ';

        // Prepare delete query.
        $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id IN ';

        // Get paths
        $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth
			WHERE isys_auth__isys_module__id = ' . $this->convert_sql_id($this->m_module_id) . '
			AND isys_auth__path LIKE ' . $this->convert_sql_text(strtoupper($p_method) . '/%');

        $l_res = $this->retrieve($l_auth_query);
        try {
            if ($l_res->num_rows() > 0) {
                $l_delete_arr = [];
                while ($l_row = $l_res->get_row()) {
                    $l_auth_id = $l_row['isys_auth__id'];
                    $l_path_parts = explode('/', $l_row['isys_auth__path']);

                    if ($l_path_parts[1] == isys_auth::WILDCHAR) {
                        continue;
                    } else {
                        $l_check_value = $l_path_parts[1];
                    }

                    if (is_numeric($l_check_value)) {
                        $l_check_value = $this->convert_sql_id($l_check_value);
                    } else {
                        $l_check_value = $this->convert_sql_text($l_check_value);
                    }

                    $l_check_query = $l_query . $l_check_value;
                    $l_res_check = $this->retrieve($l_check_query);
                    if ($l_res_check->num_rows() == 0) {
                        $l_delete_arr[] = $l_auth_id;

                    }
                }
                if (is_countable($l_delete_arr) && count($l_delete_arr) > 0) {
                    $l_delete_query = $l_delete_query . '(' . implode(',', $l_delete_arr) . ')';
                    $this->update($l_delete_query);
                    $this->apply_update();
                }
            }
        } catch (isys_exception_general $e) {
            throw new isys_exception_general($e->getMessage());
        }

        return $this;
    }
}

?>
