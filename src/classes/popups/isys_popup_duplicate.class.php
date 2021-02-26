<?php

use idoit\Context\Context;

/**
 * i-doit
 *
 * Popup for object duplication
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_duplicate extends isys_component_popup
{
    /**
     * This array will hold the IDs of all newly created objects.
     *
     * @var  array
     */
    protected $m_imported_objects = [];

    /**
     * This array contains categories which are blacklisted only for the duplication
     *
     * @var array
     */
    protected $m_duplicate_blacklist = [];

    /**
     * Handles Smarty inclusion.
     *
     * @global  array                   $g_config
     *
     * @param   isys_component_template $p_tplclass (unused)
     * @param   mixed                   $p_params   (unused)
     *
     * @return  string
     */
    public function handle_smarty_include(&$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * Handles module request.
     *
     * @param   isys_module_request $p_modreq (unused)
     *
     * @return void
     * @throws isys_exception_database
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $template = isys_application::instance()->container->get('template');

        $l_cmdb_dao = isys_cmdb_dao_object_type::instance($this->database);

        $l_custom_name = !in_array($_GET[C__CMDB__GET__OBJECTTYPE], filter_defined_constants(['C__OBJTYPE__PERSON', 'C__OBJTYPE__PERSON_GROUP', 'C__OBJTYPE__ORGANIZATION']));

        if (isset($_POST['table_ids']) && is_array($_POST['table_ids']) && count($_POST['table_ids']) === 1) {
            $template->assign('object_title', html_entity_decode($l_cmdb_dao->get_obj_name_by_id_as_string($_POST['table_ids'][0]), null, ''));
        } elseif (isset($_POST['id']) && is_array($_POST['id']) && count($_POST['id']) === 1) {
            $template->assign('object_title', html_entity_decode($l_cmdb_dao->get_obj_name_by_id_as_string($_POST['id'][0]), null, ''));
        } else {
            $template->assign('object_title', '');
        }

        $globalCategories = [];
        $specificCategories = [];
        $customCategories = [];

        // Assign durable global categories:
        $l_cat = $l_cmdb_dao->get_durable_catg();
        $l_skipped_categories = isys_export_cmdb_object::get_skipped_categories(C__CMDB__CATEGORY__TYPE_GLOBAL);
        while ($l_row = $l_cat->get_row()) {
            if (isset($this->m_duplicate_blacklist[$l_row['isysgui_catg__id']])) {
                continue;
            }

            if (!class_exists($l_row['isysgui_catg__class_name'])) {
                continue;
            }

            $properties = (new $l_row['isysgui_catg__class_name']($this->database))->get_properties();

            if (is_countable($properties) && count($properties) > 0 && isys_export_cmdb_object::isCategoryExportable($properties)) {
                $globalCategories[$l_row['isysgui_catg__id']] = [
                    C__GET__ID => $l_row['isysgui_catg__id'],
                    'title'    => $this->language->get($l_row['isysgui_catg__title'])
                ];
            }
        }

        // Assign global categories:
        $l_cat = $l_cmdb_dao->get_catg_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);
        while ($l_row = $l_cat->get_row()) {
            // Don´t show skipped categories in GUI.
            if (isset($this->m_duplicate_blacklist[$l_row['isysgui_catg__id']])) {
                continue;
            }

            if (!class_exists($l_row['isysgui_catg__class_name'])) {
                continue;
            }

            if (array_key_exists($l_row['isysgui_catg__id'], $l_skipped_categories) || in_array($l_row['isysgui_catg__const'], ['C__CATG__VIRTUAL', 'C__CATG__GLOBAL'], true)) {
                // @todo C__CATG__GLOBAL is already set in durable catg above.
                continue;
            }

            $properties = (new $l_row['isysgui_catg__class_name']($this->database))->get_properties();

            if (is_countable($properties) && count($properties) > 0 && isys_export_cmdb_object::isCategoryExportable($properties)) {
                $globalCategories[$l_row['isysgui_catg__id']] = [
                    C__GET__ID => $l_row['isysgui_catg__id'],
                    'title'    => $this->language->get($l_row['isysgui_catg__title'])
                ];
            }
        }

        // Assign custom categories:
        $l_cat_custom = $l_cmdb_dao->get_catg_custom_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);

        while ($l_row = $l_cat_custom->get_row()) {
            $customCategories[$l_row['isysgui_catg_custom__id']] = [
                C__GET__ID => $l_row['isysgui_catg_custom__id'],
                'title'    => $this->language->get($l_row['isysgui_catg_custom__title'])
            ];
        }

        // Assign specific categories:
        $specificCategoryList = $l_cmdb_dao->get_specific_category($_GET[C__CMDB__GET__OBJECTTYPE], C__RECORD_STATUS__NORMAL, null, true)->__as_array();

        foreach ($specificCategoryList as $specificCategory) {
            if (!class_exists($specificCategory['isysgui_cats__class_name'])) {
                continue;
            }

            if ($specificCategory['isysgui_cats__type'] == isys_cmdb_dao_category::TYPE_FOLDER) {
                continue;
            }

            $properties = (new $specificCategory['isysgui_cats__class_name']($this->database))->get_properties();

            if (is_countable($properties) && count($properties) > 0 && isys_export_cmdb_object::isCategoryExportable($properties)) {
                $specificCategories[$specificCategory['isysgui_cats__id']] = [
                    C__GET__ID => $specificCategory['isysgui_cats__id'],
                    'title'    => $this->language->get($specificCategory['isysgui_cats__title'])
                ];
            }
        }

        // Sort categories alphabetically.
        usort($globalCategories, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        usort($specificCategories, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        usort($customCategories, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        $template
            ->assign('customName', $l_custom_name)
            ->assign('custom_categories', array_values($customCategories))
            ->assign('categories', array_values($globalCategories))
            ->assign('specificCategories', array_values($specificCategories))
            ->display('popup/duplicate.tpl');
        die;
    }

    /**
     * Duplicates object.
     *
     * @todo Use a shorter way to duplicate: Instead of making a complete import
     * after making a complete export, just transform data to the new data
     * structure.
     *
     * @return  boolean
     */
    public function duplicate()
    {
        $l_return = [
            'success' => true,
            'data'    => [],
            'message' => null,
        ];

        try {
            isys_application::instance()->container->session->write_close();

            // Start logging:
            $l_log = isys_factory_log::get_instance('duplicate');
            $l_log->set_verbose_level(isys_log::C__NONE);

            // Retrieve objects:
            $l_objects = [];

            // Check whether several object ids are posted by table
            if (isset($_POST[C__GET__ID]) && is_array($_POST[C__GET__ID]) && !empty($_POST[C__GET__ID])) {
                $l_objects = $_POST[C__GET__ID];
            } elseif (isset($_POST['objects'])) {
                $l_objects = explode(',', $_POST['objects']);
            }

            /**
             * @var $l_cmdb_dao isys_cmdb_dao_object_type
             */
            $l_cmdb_dao = isys_cmdb_dao_object_type::instance($this->database);

            // Iterate though objects:
            $l_object_type = null;
            foreach ($l_objects as $l_object_id) {
                // Determine object type identifier:
                if (!isset($l_object_type)) {
                    $l_object_type = $l_cmdb_dao->get_objTypeID($l_object_id);
                }
            } //foreach object

            // Retrieve categories:
            $l_categories = [];
            // Global categories:
            if (isset($_POST['globalCategory']) && !empty($_POST['globalCategory'])) {
                $l_catg = $_POST['globalCategory'];
                $l_categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = $l_catg;
            }
            // Specific categories:
            if (isset($_POST['specificCategory']) && !empty($_POST['specificCategory'])) {
                $specificCategoies = $_POST['specificCategory'];
                $l_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $specificCategoies;
            }

            // Custom categories:
            if (isset($_POST['custom_category'])) {
                $l_catc = $_POST['custom_category'];
                $l_categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = $l_catc;
            }

            Context::instance()
                ->setImmutable(true)
                ->setContextTechnical(Context::CONTEXT_EXPORT_XML)
                ->setGroup(Context::CONTEXT_GROUP_DUPLICATE)
                ->setContextCustomer(Context::CONTEXT_DUPLICATE);

            // Export data...
            $l_export = new isys_export_cmdb_object('isys_export_type_xml', $this->database);

            $l_parser = $l_export->export($l_objects, $l_categories, C__RECORD_STATUS__NORMAL, true)
                ->parse();

            $l_data = $l_parser->get_export();

            unset($l_export, $l_parser);

            Context::instance()
                ->setImmutable(false)
                ->setContextTechnical(Context::CONTEXT_IMPORT_XML)
                ->setGroup(Context::CONTEXT_GROUP_DUPLICATE)
                ->setContextCustomer(Context::CONTEXT_DUPLICATE)
                ->setImmutable(true);

            // ...and import it:
            $l_import = new isys_import_handler_cmdb($l_log, $this->database);
            $l_import->set_option('update-object-changed', false)
                ->set_mode(isys_import_handler_cmdb::C__APPEND)
                ->set_multivalue_categories_mode(isys_import_handler_cmdb::C__APPEND);

            if (isset($_POST['update_globals'])) {
                $l_import->set_update_globals();
            }
            $l_import->load_xml_data($l_data);

            if ($l_import->parse() === false) {
                return false;
            }

            $l_import->prepare();
            $objectsCount = count($l_objects);

            // Set title inside import method
            foreach ($l_objects as $l_object_id) {
                $l_title = null;
                if (count($l_objects) > 1) {
                    // There are more than one objects to be duplicated, so the need their names:
                    if (isset($_POST['object_title']) && $_POST['object_title'] != '') {
                        $l_title = $_POST['object_title'];
                    } else {
                        $l_title = $l_cmdb_dao->get_obj_name_by_id_as_string($l_object_id);
                    }
                } elseif (isset($_POST['object_title'])) {
                    // Only one object:
                    assert(isset($_POST["object_title"]) && is_string($_POST["object_title"]));
                    $l_title = $_POST['object_title'];
                }

                // Set title:
                if ($l_title !== null) {
                    $l_import->set_replaced_title($l_title, $l_object_id);
                }
            }

            if ($l_import->import() === false) {
                return false;
            }

            $l_object_ids = $l_import->get_object_ids();
            unset($l_import);

            $this->m_imported_objects = [];

            $l_auth_dao = new isys_auth_dao($this->database);
            foreach ($l_objects as $l_object_id) {
                // Skip objects that could not be duplicated:
                if (!isset($l_object_ids[$l_object_id])) {
                    continue;
                }

                // Call custom duplication methods:
                if ($l_cmdb_dao->has_cat($l_object_type, [
                    'C__CATS__PERSON_GROUP',
                    'C__CATS__PERSON'
                ])) {
                    $l_auth_dao->duplicate($l_object_id, $l_object_ids[$l_object_id]);
                }

                // Handle options:
                $this->handle_options($l_object_ids[$l_object_id]);

                $this->m_imported_objects[] = $l_object_ids[$l_object_id];
            }

            $l_return['data']['imported'] = array_unique($this->m_imported_objects);

            unset($l_cmdb_dao);
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        if (isset($_POST['open_new_created_object']) && $_POST['open_new_created_object'] == 'on') {
            // Redirect directly inside the newly created object. See Jira ticket: ID-1429.
            $l_return['data']['redirect'] = isys_helper_link::create_url([C__CMDB__GET__OBJECT => end($this->get_imported_objects())]);
            $l_return['data']['url'] = isys_helper_link::get_base() . 'cmdb/object/' . end($this->get_imported_objects());
        }

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);

        Context::instance()->setImmutable(false);

        // End the request.
        die;
    }

    /**
     * Method for retrieving the newly created object IDs.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_imported_objects()
    {
        return $this->m_imported_objects;
    }

    /**
     * Handles options given by the duplicate dialog.
     *
     * @param   integer $p_object_id
     */
    private function handle_options($p_object_id)
    {
        switch ($_POST['duplicate_options']) {
            case 'virtualize':
                $l_dao = new isys_cmdb_dao_category_g_virtual_machine($this->database);
                $l_dao->set_vm_status($p_object_id, C__VM__GUEST);
                break;

            case 'devirtualize':
                $l_dao = new isys_cmdb_dao_category_g_virtual_machine($this->database);
                $l_dao->set_vm_status($p_object_id, C__VM__NO);
                break;
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        if (defined('C__CATG__IDENTIFIER')) {
            $this->m_duplicate_blacklist[constant('C__CATG__IDENTIFIER')] = true;
        }

        if (defined('C__CATG__OBJECT')) {
            $this->m_duplicate_blacklist[constant('C__CATG__OBJECT')] = true;
        }

        parent::__construct();

        if (!defined('C__MODULE__EXPORT') || !class_exists('isys_module_export')) {
            throw new isys_exception_general('Export module is not installed.');
        }

        set_time_limit(isys_convert::DAY);
    }
}
