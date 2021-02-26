<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_ajax_handler_validate_field extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try {
            switch ($_GET['func']) {
                case 'validate':
                    $l_return['data'] = $this->validate(
                        $_POST['identifier'],
                        trim($_POST['element_value']),
                        $_POST['obj_type_id'],
                        $_POST['obj_id'],
                        $_POST['catg_custom_id'],
                        $_POST['category_entry_id']
                    );
                    break;

                case 'validate_all':
                    $l_return['data'] = $this->validate_all(isys_format_json::decode($_POST['data']), $_POST['obj_type_id'], $_POST['obj_id']);
                    break;

                case 'get_mandatory_fields':
                    $l_return['data'] = $this->get_mandatory_fields(explode(',', $_POST['elements']), $_POST['catg_custom_id']);
                    break;

                case 'get_validation_by_category':
                    $l_return['data'] = $this->get_validation_by_category($_POST['cat_type'], $_POST['cat_id']);
                    break;

                case 'save_validation_configuration':
                    $l_return['data'] = $this->save_validation_configuration(isys_format_json::decode($_POST['configuration']));
                    break;

                case 'reset_validation_cache':
                    $l_return['data'] = $this->reset_validation_cache();
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
     * Method for validating a value by a given property identifier and some other stuff.
     *
     * @param   string  $p_identifier
     * @param   mixed   $p_value
     * @param   integer $p_obj_type_id
     * @param   integer $p_obj_id
     * @param   integer $p_custom_category_id
     * @param   integer $p_category_entry_id
     *
     * @throws  isys_exception_api_validation
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function validate($p_identifier, $p_value, $p_obj_type_id = null, $p_obj_id = null, $p_custom_category_id = 0, $p_category_entry_id = 0)
    {
        $l_return = '';

        list($l_dao_name, $l_prop) = explode('::', $p_identifier);

        if (class_exists($l_dao_name)) {
            /* @var  $l_dao isys_cmdb_dao_category */
            $l_dao = isys_factory::get_instance($l_dao_name, $this->m_database_component);

            if (method_exists($l_dao, 'validate')) {
                if (method_exists($l_dao, 'set_catg_custom_id') && $p_custom_category_id > 0) {
                    $l_dao->set_catg_custom_id($p_custom_category_id);
                }

                // This might happen on the overview-page.
                if (substr($l_prop, 0, 2) === 'C_') {
                    $l_properties = $l_dao->get_properties();

                    foreach ($l_properties as $l_key => $l_property) {
                        if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] == $l_prop) {
                            $l_prop = $l_key;

                            break;
                        }
                    }
                }

                $l_validated = $l_dao->set_object_id($p_obj_id)
                    ->set_list_id($p_category_entry_id)
                    ->set_object_type_id($p_obj_type_id)
                    ->validate([$l_prop => $p_value]);

                if (is_array($l_validated)) {
                    throw new isys_exception_api_validation($l_validated[$l_prop], $l_validated);
                }
            }
        } else {
            $l_return = 'DAO class ("' . $l_dao_name . '") could not be found';
        }

        return $l_return;
    }

    /**
     * Method for validating multiple values by a given property identifier and some other stuff.
     *
     * @param   array   $p_data
     * @param   integer $p_obj_type_id
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function validate_all(array $p_data = [], $p_obj_type_id = null, $p_obj_id = null)
    {
        $l_return = [];

        foreach ($p_data as $l_property) {
            $l_return[$l_property['identifier']] = $this->validate(
                $l_property['identifier'],
                $l_property['value'],
                $p_obj_type_id,
                $p_obj_id,
                $l_property['catg_custom_id'],
                $l_property['category_entry_id']
            );

            // We only want failed validations.
            if (!$l_return[$l_property['identifier']]['error']) {
                unset($l_return[$l_property['identifier']]);
            }
        }

        return $l_return;
    }

    /**
     * This method returns an array of the mandatory fields from the given elements.
     * The element needs a certain format: 'dao_class_name::property_key'.
     *
     * @param   array   $p_elements
     * @param   integer $p_custom_category_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function get_mandatory_fields($p_elements, $p_custom_category_id = 0)
    {
        $l_return = [];
        $l_already_called_dao = [];

        foreach ($p_elements as $l_element) {
            $l_dao_name = current(explode('::', $l_element));

            if (!in_array($l_dao_name, $l_already_called_dao) && class_exists($l_dao_name)) {
                $l_already_called_dao[] = $l_dao_name;

                // We use the singleton pattern, so we don't always create new instances of the same class (inside the foreach).
                $l_dao = call_user_func([
                    $l_dao_name,
                    'instance'
                ], $this->m_database_component);

                if ($l_dao_name === 'isys_cmdb_dao_category_g_custom_fields' && method_exists($l_dao, 'set_catg_custom_id')) {
                    $l_dao->set_catg_custom_id($p_custom_category_id);
                }

                $l_properties = $l_dao->get_properties(C__PROPERTY__WITH__VALIDATION);

                foreach ($l_properties as $l_property) {
                    if ($l_property[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) {
                        // The ["id"]["id"] is needed for custom categories.
                        if (is_array($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]) && isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID])) {
                            $l_ui_id = 'C__CATG__CUSTOM__' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                        } else {
                            $l_ui_id = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                        }

                        if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP &&
                            ($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER ||
                                $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M)) {
                            $l_ui_id .= '__HIDDEN';
                        } elseif ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG_LIST) {
                            $l_ui_id .= '__selected_values';
                        }

                        $l_return[] = $l_ui_id;
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Retrieves the properties with validation rules.
     *
     * @param   integer $p_cat_type
     * @param   integer $p_cat_id
     *
     * @throws  Exception
     * @throws  isys_exception_missing_function
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function get_validation_by_category($p_cat_type, $p_cat_id)
    {
        $l_virtual_properties = $l_property_names = $l_locked_properties = [];

        if (!is_numeric($p_cat_id) && class_exists($p_cat_id) && is_a($p_cat_id, 'isys_cmdb_dao_category', true)) {
            $l_property_dao = $p_cat_id::instance($this->m_database_component);
        } else {
            $l_property_dao = isys_factory_cmdb_category_dao::get_instance_by_id($p_cat_type, $p_cat_id, $this->m_database_component);
        }

        if ($p_cat_type == C__CMDB__CATEGORY__TYPE_CUSTOM) {
            if (!method_exists($l_property_dao, 'set_catg_custom_id')) {
                throw new isys_exception_missing_function('The DAO class "' . get_class($l_property_dao) . '" is missing the method "set_catg_custom_id".');
            }

            $l_property_dao->set_catg_custom_id($p_cat_id);
        }

        $l_properties = $l_property_dao->get_properties();

        foreach ($l_properties as $l_property => $l_property_data) {
            if (!$l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__READONLY]) {
                $l_property_names[$l_property] = isys_application::instance()->container->get('language')->get($l_property_data[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
            }

            // Also we want to display the attributes, that can not have user specific validation.
            if (!$l_property_data[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION]) {
                $l_locked_properties[] = $l_property;
            }

            if ($l_property_data[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL] === true) {
                $l_virtual_properties[] = $l_property;
            }
        }

        if ($p_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL) {
            $p_cat_id = $l_property_dao->get_category_id();
            $l_cattype = 'g';
        } elseif ($p_cat_type == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
            $p_cat_id = $l_property_dao->get_category_id();
            $l_cattype = 's';
        } else {
            $l_cattype = 'g_custom';
        }

        $l_validation_row = isys_cmdb_dao_validation::instance($this->m_database_component)
            ->get_data(null, $p_cat_id, $l_cattype)
            ->get_row();

        // The "stdClass" is necessary so that we get a JSON "{}", if there is no configuration.
        $l_configuration = isys_format_json::decode($l_validation_row['isys_validation_config__json']) ?: [];

        // This is (especially) necessary for custom categories, in case properties change.
        if (is_array($l_configuration)) {
            foreach ($l_configuration as $l_property => $l_config) {
                // We want to unset the validation rules for properties, that already own internal validation rules.
                if (in_array($l_property, $l_locked_properties)) {
                    $l_configuration[$l_property]['check']['validation'] = [];
                }

                if (!array_key_exists($l_property, $l_property_names)) {
                    unset($l_configuration[$l_property]);
                }
            }
        }

        // Remove all properties which are virtual
        if (is_countable($l_virtual_properties) && count($l_virtual_properties) > 0) {
            foreach ($l_virtual_properties as $l_virtual_property) {
                if (isset($l_configuration[$l_virtual_property])) {
                    unset($l_configuration[$l_virtual_property]);
                }

                if (isset($l_property_names[$l_virtual_property])) {
                    unset($l_property_names[$l_virtual_property]);
                }

                if (($l_key = array_search($l_virtual_property, $l_locked_properties)) !== false) {
                    unset($l_locked_properties[$l_key]);
                    $l_locked_properties = array_values($l_locked_properties);
                }
            }
        }

        return [
            'rules'             => [
                'id'     => $l_validation_row['isys_validation_config__id'],
                'config' => $l_configuration
            ],
            'properties'        => $l_property_names,
            'locked_properties' => $l_locked_properties,
            'multivalue'        => $l_property_dao->is_multivalued()
        ];
    }

    /**
     * @param   array $p_config
     *
     * @return  null
     * @throws  isys_exception_database
     */
    private function save_validation_configuration(array $p_config)
    {
        /* @var  isys_cmdb_dao_validation $l_dao */
        $l_dao = isys_cmdb_dao_validation::instance($this->m_database_component);

        // Our first action is to truncate the validation configuration.
        if (!$l_dao->truncate()) {
            throw new isys_exception_database('While cleaning the validation configuration, an error occured: ' . $this->m_database_component->get_last_error_as_string());
        }

        foreach ($p_config as $l_category => $l_data) {
            $l_create_data = [
                'catg'   => 0,
                'cats'   => 0,
                'catc'   => 0,
                'config' => $l_data['rules']['config']
            ];

            // May be "g", "s" or "c" (custom).
            switch (substr($l_category, 0, 1)) {
                case 'g':
                    $className = substr($l_category, 2);
                    if (class_exists($className) && is_a($className, isys_cmdb_dao_category::class, true)) {
                        $l_create_data['catg'] = $className::instance($this->m_database_component)->get_category_id();
                    }
                    break;
                case 's':
                    $className = substr($l_category, 2);
                    if (class_exists($className) && is_a($className, isys_cmdb_dao_category::class, true)) {
                        $l_create_data['cats'] = $className::instance($this->m_database_component)->get_category_id();
                    }
                    break;
                case 'c':
                    $l_create_data['catc'] = substr($l_category, 2);
                    break;
            }

            $l_dao->create($l_create_data);
        }

        isys_module_cmdb::create_validation_cache();

        return null;
    }

    /**
     * Method for resetting the validation cache.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function reset_validation_cache()
    {
        isys_module_cmdb::create_validation_cache();

        return 'OK!';
    }
}
