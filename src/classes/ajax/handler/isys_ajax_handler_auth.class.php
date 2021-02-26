<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 */
class isys_ajax_handler_auth extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'create_new_path_by_category':
                    $l_return['data'] = $this->create_new_path_by_category($_POST['person_id'], $_POST['module_id'], $_POST['method'], $_POST['parameter'], $_POST['rights']);
                    break;

                case 'load_all_module_paths':
                    $l_return['data'] = $this->load_all_module_paths($_POST['module_id']);
                    break;

                case 'load_all_object_paths':
                    $l_return['data'] = $this->load_all_object_paths($_POST['obj_id']);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Method for saving a new auth-path out of the "auth-category".
     *
     * @param   integer $p_person_id
     * @param   integer $p_module_id
     * @param   string  $p_method
     * @param   string  $p_parameter
     * @param   string  $p_rights May contain several rights, divided by ";".
     *
     * @throws  isys_exception_general
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function create_new_path_by_category($p_person_id, $p_module_id, $p_method, $p_parameter, $p_rights)
    {
        if (empty($p_person_id) || empty($p_module_id) || empty($p_method)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__AUTH_EXCEPTION_MISSING_PARAMETERS'));
        }

        $l_rights = explode(';', $p_rights);

        if (in_array(isys_auth::SUPERVISOR, $l_rights)) {
            // If the supervisor was selected, no other rights have to be assigned.
            $l_rights = [isys_auth::SUPERVISOR];
        }

        // Prepare the array syntax for isys_auth_dao->create_paths().
        $l_path_data = [$p_method => [$p_parameter => $l_rights]];

        isys_auth_dao::instance($this->m_database_component)
            ->create_paths($p_person_id, $p_module_id, $l_path_data);

        $l_object_paths = isys_cmdb_dao_category_g_virtual_auth::instance($this->m_database_component)
            ->get_object_paths($p_parameter);

        try {
            array_map(function (isys_caching $l_cache) {
                $l_cache->clear();
            }, isys_caching::find('auth-*'));
        } catch (Exception $e) {
            isys_notify::warning(sprintf('Could not clear cache files for %sauth-* with message: ' . $e->getMessage(), isys_glob_get_temp_dir()));
        }

        // Return the new paths for the given object.
        return isys_auth_dao::instance($this->m_database_component)
            ->build_paths_by_array($l_object_paths);
    }

    /**
     * Method for loading all paths by a given module.
     *
     * @param   integer $p_module_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_all_module_paths($p_module_id)
    {
        $l_paths = [];

        $l_dao = isys_auth_dao::instance($this->m_database_component);
        $l_cmdb_dao = isys_cmdb_dao::instance($this->m_database_component);
        $language = isys_application::instance()->container->get('language');

        $l_res = $l_dao->get_paths(null, $p_module_id);

        if ($l_res->count() > 0) {
            while ($l_row = $l_res->get_row()) {
                // This needs to be done, for the reference to work.
                if ($l_paths[$l_row['isys_auth__isys_obj__id']] === null) {
                    if ($l_row['isys_auth__isys_obj__id'] > 0) {
                        $l_person = $l_cmdb_dao->get_object_by_id($l_row['isys_auth__isys_obj__id'])
                            ->get_row();

                        $l_paths[$l_row['isys_auth__isys_obj__id']] = [
                            'paths'  => [],
                            'person' => $language->get($l_person['isys_obj_type__title']) . ' &raquo; ' . $l_person['isys_obj__title']
                        ];
                    }
                }

                $l_dao->build_path($l_paths[$l_row['isys_auth__isys_obj__id']]['paths'], $l_row);
            }
        }

        $l_methods = [];
        $l_auth_instance = isys_module_manager::instance()
            ->get_module_auth($p_module_id);

        if ($l_auth_instance) {
            $l_methods = $l_auth_instance->get_auth_methods();
        }

        $l_methods = array_map(function ($method) use ($language) {
            $method['title'] = $language->get($method['title']);

            return $method;
        }, $l_methods);

        // Retrieve the rights and make sure, the titles are UTF8.
        $rights = isys_auth::get_rights();

        $rights = array_map(function ($right) use ($language) {
            $right['title'] = $language->get($right['title']);

            return $right;
        }, $rights);

        return [
            'method'       => 'module-id',
            'auth_rights'  => $rights,
            'auth_methods' => $l_methods,
            'auth_paths'   => $l_paths
        ];
    }

    /**
     * Method for loading all paths by a given object (person / persongroup).
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_all_object_paths($p_obj_id)
    {
        $l_paths = [];

        $l_dao = isys_auth_dao::instance($this->m_database_component);
        $language = isys_application::instance()->container->get('language');

        $l_res = $l_dao->get_paths($p_obj_id);

        if (is_object($l_res) && $l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                // This needs to be done, for the reference to work.
                if ($l_paths[$l_row['isys_auth__isys_module__id']] === null) {
                    // Loading the module data.
                    $l_module = isys_module_manager::instance()
                        ->get_modules($l_row['isys_auth__isys_module__id'])
                        ->get_row();
                    $l_module['isys_module__title'] = isys_application::instance()->container->get('language')
                        ->get($l_module['isys_module__title']);

                    // Prepare the module specific methods.
                    $methods = [];
                    $l_auth_instance = isys_module_manager::instance()
                        ->get_module_auth($l_row['isys_auth__isys_module__id']);

                    if ($l_auth_instance) {
                        $methods = $l_auth_instance->get_auth_methods();
                    }

                    $methods = array_map(function ($method) use ($language) {
                        $method['title'] = $language->get($method['title']);

                        return $method;
                    }, $methods);

                    $l_paths[$l_row['isys_auth__isys_module__id']] = [
                        'paths'       => [],
                        'group_paths' => [],
                        'info'        => [
                            'data'    => $l_module,
                            'methods' => $methods
                        ]
                    ];
                }

                // Add the user specific paths.
                $l_dao->build_path($l_paths[$l_row['isys_auth__isys_module__id']]['paths'], $l_row);
            }
        }

        // Check, if the given obj-id is a person, so we can load the inherited rights.
        $l_obj_type = isys_cmdb_dao::instance($this->m_database_component)
            ->get_objTypeID($p_obj_id);

        if (defined('C__OBJTYPE__PERSON_GROUP') && $l_obj_type != C__OBJTYPE__PERSON_GROUP) {
            $l_res = $l_dao->get_group_paths_by_person($p_obj_id);

            if (is_object($l_res) && $l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    // This needs to be done, for the reference to work.
                    if ($l_paths[$l_row['isys_auth__isys_module__id']] === null) {
                        // Loading the module data.
                        $l_module = isys_module_manager::instance()
                            ->get_modules($l_row['isys_auth__isys_module__id'])
                            ->get_row();
                        $l_module['isys_module__title'] = isys_application::instance()->container->get('language')
                            ->get($l_module['isys_module__title']);

                        // Prepare the module specific methods.
                        $methods = [];
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($l_row['isys_auth__isys_module__id']);

                        if ($l_auth_instance) {
                            $methods = $l_auth_instance->get_auth_methods();
                        }

                        $methods = array_map(function ($method) use ($language) {
                            $method['title'] = $language->get($method['title']);

                            return $method;
                        }, $methods);

                        $l_paths[$l_row['isys_auth__isys_module__id']] = [
                            'paths'       => [],
                            'group_paths' => [],
                            'info'        => [
                                'data'    => $l_module,
                                'methods' => $methods
                            ]
                        ];
                    }

                    // Add the user specific paths.
                    $l_dao->build_path($l_paths[$l_row['isys_auth__isys_module__id']]['group_paths'], $l_row);
                }
            }
        }

        // Retrieve the rights and make sure, the titles are UTF8.
        $rights = isys_auth::get_rights();

        $rights = array_map(function ($right) use ($language) {
            $right['title'] = $language->get($right['title']);

            return $right;
        }, $rights);

        return [
            'method'      => 'obj-id',
            'auth_rights' => $rights,
            'modules'     => $l_paths
        ];
    }
}
