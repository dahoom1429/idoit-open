<?php

use idoit\Component\Helper\Purify;

/**
 * CMDB Category view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Wösten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_category extends isys_cmdb_view
{
    /**
     * Category DAO class.
     *
     * @var  isys_cmdb_dao_category_global
     */
    protected $m_cat_dao;

    /**
     * Category UI class.
     *
     * @var  isys_cmdb_ui_category_global
     */
    protected $m_cat_ui;

    /**
     * Category constant
     *
     * @var string
     */
    protected $m_categoryConst;

    /**
     * Category Data
     *
     * @var array
     */
    protected $m_categoryData = [];

    /**
     * Returns the ID for the category view.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__CATEGORY;
    }

    /**
     * Returns the mandatory parameters.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__OBJECT] = true;
    }

    /**
     * Returns the name of the view.
     *
     * @return  string
     */
    public function get_name()
    {
        return "Kategorieansicht";
    }

    /**
     * Method for setting the optional parameters.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__OBJECTTYPE] = true;
        $l_gets[C__CMDB__GET__CAT_MENU_SELECTION] = true;
        $l_gets[C__CMDB__GET__CAT_LIST_VIEW] = true;
        $l_gets[C__CMDB__GET__CATLEVEL] = true;
        $l_gets[C__CMDB__GET__CATLEVEL_1] = true;
        $l_gets[C__CMDB__GET__CATLEVEL_2] = true;
        $l_gets[C__CMDB__GET__CATLEVEL_3] = true;
        $l_gets[C__CMDB__GET__CATLEVEL_4] = true;
        $l_gets[C__CMDB__GET__CATLEVEL_5] = true;
        $l_gets[C__CMDB__GET__CATG] = true;
        $l_gets[C__CMDB__GET__CATS] = true;
    }

    /**
     * Returns the filepath of the "bottom" template.
     *
     * @return  null
     */
    public function get_template_bottom()
    {
        return null;
    }

    /**
     * Returns the filepath of the "top" template.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return "content/top/main_objectdetail.tpl";
    }

    /**
     * Method for handling the given nav-mode.
     *
     * @param   integer $p_navmode
     *
     * @throws  isys_exception_auth
     * @return  mixed
     */
    public function handle_navmode($p_navmode)
    {
        $l_auth_instance = isys_auth_cmdb::instance();
        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_navbar = $this->get_module_request()
            ->get_navbar();
        $l_tpl = $this->get_module_request()
            ->get_template();
        $l_actionproc = $this->get_action_processor();
        $l_token_manager = new \Symfony\Component\Security\Csrf\CsrfTokenManager();

        $l_navbar::set_locked(false);

        $l_result = null;
        $l_object_id = $l_gets[C__CMDB__GET__OBJECT];

        // Get responsible category data.
        $l_catmeta = $this->m_dao_cmdb->nav_get_current_category_data();

        // Get lock stuff and check if object id is logged.
        $l_dao_lock = new isys_component_dao_lock($this->get_module_request()
            ->get_database());

        if (($l_locked = $l_dao_lock->is_locked($l_object_id))) {
            $l_res = $l_dao_lock->get_lock_information($l_object_id);
            $l_row = $l_res->get_row();

            $l_tpl->assign("g_locked", "1")
                ->assign("lock_user", $this->m_dao_cmdb->get_obj_name_by_id_as_string($l_row["isys_user_session__isys_obj__id"]));
        }

        // Get primary multivalued information from database.
        $l_multi_gui = $this->m_dao_cmdb->gui_is_multivalued_by_category(
            $l_catmeta["type"],
            ($this->m_cat_dao->get_category_id() != defined_or_default('C__CATG__CUSTOM_FIELDS')) ? $this->m_cat_dao->get_category_id() : $l_catmeta["id"]
        );

        $l_csrf_token_id = 'i-doitCSRFToken_' . $l_object_id . '_' . $this->m_cat_dao->get_category_type() . '-' . $this->m_cat_dao->get_category_id();

        switch ($p_navmode) {
            case C__NAVMODE__JS_ACTION:
            case C__NAVMODE__NEW:
                isys_component_template::instance()
                    ->assign("csrf_value", $l_token_manager->getToken($l_csrf_token_id)
                        ->getValue());

                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::CREATE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::CREATE),
                            isys_application::instance()->container->get('language')
                                ->get($this->m_cat_dao->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_CREATE, [
                    &$this->m_cat_dao,
                    &$this->m_cat_ui
                ]);

                $l_actionproc->process();

                $l_saveret = $l_actionproc->result_pop();

                if (is_null($l_saveret)) {
                    // Do nothing and break up.
                    break;
                } elseif (is_numeric($l_saveret)) {
                    $l_result = $l_saveret;
                    $l_gets[C__CMDB__GET__CATLEVEL] = $l_result;
                } elseif (is_array($l_saveret)) {
                    /* New level->category ID assignment */
                    [$l_newlevel, $l_newid] = $l_saveret;
                    $l_gets[constant("C__CMDB__GET__CATLEVEL_" . $l_newlevel)] = $l_newid;
                }

                $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;

                if ($p_navmode == C__NAVMODE__JS_ACTION) {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__NEW);
                }

                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

                unset($l_gets[C__CMDB__GET__CAT_LIST_VIEW]);
                break;

            case C__NAVMODE__SAVE:
                if (isys_settings::get('system.security.csrf', false)) {
                    $l_token = new \Symfony\Component\Security\Csrf\CsrfToken($l_csrf_token_id, $_POST['_csrf_token']);

                    if (!$l_token_manager->isTokenValid($l_token)) {
                        throw new ErrorException(isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__CSRF_FAIL'));
                    }
                }

                // Check, if the user is allowed to edit the category entry.
                $requiredRight = $this->m_rights[isys_auth::CREATE] || $this->m_rights[isys_auth::EDIT];

                if (!$this->m_cat_dao->is_multivalued()) {
                    $requiredRight = $this->m_cat_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])->num_rows() > 0 ?
                        $this->m_rights[isys_auth::EDIT] : $this->m_rights[isys_auth::CREATE] || $this->m_rights[isys_auth::EDIT];
                }

                if (!$requiredRight) {
                    // Default error text
                    $errorText = isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY_FALLBACK', [isys_auth::get_right_name(isys_auth::EDIT)]);

                    try {
                        // Try to get category title instead
                        $errorText = isys_application::instance()->container->get('language')
                            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                                isys_auth::get_right_name(isys_auth::EDIT),
                                isys_application::instance()->container->get('language')
                                    ->get($this->m_cat_dao->getCategoryTitle())
                            ]);
                    } catch (Exception $e) {
                    }

                    throw new isys_exception_auth($errorText);
                }

                // Get last, most important multivalued information from UI category object.
                $l_multi_uic = $this->m_cat_dao->is_multivalued();
                if ($l_multi_uic !== null) {
                    // The user interface class of the category can decide, whether a category is handled multivalued or not.
                    $l_multi_gui = $l_multi_uic;
                }

                // In case we're single valued.
                if (!$l_multi_gui) {
                    // Ask CMDB for category entry count.
                    $l_catcount = $this->m_cat_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                        ->num_rows();

                    if ($l_catcount === 0) {
                        // If there is no list entry for this single-valued category, create it.
                        $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_CREATE, [
                            &$this->m_cat_dao,
                            &$this->m_cat_ui
                        ]);

                        /*
                         * Process action queue and directly clear action stack - we just
                         * want to create this entry. get_general_data of isys_cmdb_dao_category
                         * will read it automagically :-)
                         */
                        $l_actionproc->process();
                        $l_actionproc->result_clear();
                    }
                }

                // Okay, everything done - now we're going to update the category entry.
                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_UPDATE, [
                    &$this->m_cat_dao,
                    &$this->m_cat_ui
                ]);

                // Process category update.
                $l_result = $l_actionproc->process();

                // Check result of category update.
                $l_saveret = $l_actionproc->result_pop();

                if ($l_saveret == null) {
                    // Save was successful.
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;

                    if ($this->m_rights[isys_auth::EDIT] && !$l_locked) {
                        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                    }
                } else {
                    if (is_array($l_saveret)) {
                        [$l_newlevel, $l_newid] = $l_saveret;

                        if ($l_newlevel == 1) {
                            $l_gets[C__CMDB__GET__CATLEVEL] = $l_newid;
                        } else {
                            if (defined("C__CMDB__GET__CATLEVEL_" . $l_newlevel)) {
                                $l_gets[constant("C__CMDB__GET__CATLEVEL_" . $l_newlevel)] = $l_newid;
                            }
                        }

                        // Cancel navmodes.
                        $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
                        if ($this->m_rights[isys_auth::EDIT] && !$l_locked) {
                            $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);
                        }

                        unset($l_posts[C__GET__NAVMODE]);
                    } else {
                        // Save was bad, attach validation rules to TOM.
                        $l_tpl->smarty_tom_add_rules("tom.content.bottom.content", $this->m_cat_dao->get_additional_rules());

                        if ($this->m_rights[isys_auth::EDIT] && !$l_locked) {
                            $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                                ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                                ->set_active(true, C__NAVBAR_BUTTON__EDIT);

                            $l_dao_lock->add_lock($l_object_id);
                        }
                    }
                }
                break;

            case C__NAVMODE__CANCEL:
                $l_token_manager->removeToken($l_csrf_token_id);
                $l_dao_lock->delete_by_object_id($l_object_id);

                // ID-2385: If object with status birth was not saved, delete it and redirect to list
                $object = $this->m_cat_dao->get_object($l_object_id)
                    ->get_row();
                if (is_array($object) && $object['isys_obj__status'] == C__RECORD_STATUS__BIRTH) {
                    $this->m_cat_dao->delete_object_and_relations($l_object_id);

                    header("Status: 302 Found");
                    echo isys_helper_link::create_url([
                        C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_OBJECT,
                        C__CMDB__GET__OBJECTTYPE => $l_gets[C__CMDB__GET__OBJECTTYPE]
                    ]);
                    die();
                }

                $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;

                $l_navbar->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
            case C__NAVMODE__EDIT:
                isys_component_template::instance()
                    ->assign("csrf_value", $l_token_manager->getToken($l_csrf_token_id)
                        ->getValue());

                // Check, if the user is allowed to edit the category entry.
                if (!$this->m_rights[isys_auth::EDIT]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::EDIT),
                            isys_application::instance()->container->get('language')
                                ->get($this->m_cat_dao->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                // ID-6335: Redirect to object list
                $object = $this->m_cat_dao->get_object($l_object_id)
                    ->get_row();
                if (is_array($object) && $object['isys_obj__status'] == C__RECORD_STATUS__BIRTH) {
                    $cancelOnclick = 'document.isys_form.navMode.value=\'' . C__NAVMODE__CANCEL . ' \';';
                    $cancelOnclick .= 'form_submit(\'\', \'post\', \'no_replacement\', null, function(response) {window.location = response.responseText;});';

                    $l_navbar->set_js_onclick($cancelOnclick, C__NAVBAR_BUTTON__CANCEL);
                }

                if ($this->m_rights[isys_auth::EDIT] && !$l_locked) {
                    if (isset($l_posts['id']) && is_countable($l_posts['id']) && count($l_posts['id']) > 1) {
                        $triggerMultiEdit = true;
                        $message = '';
                        $method = 'warning';
                        unset($l_gets[C__CMDB__GET__CATLEVEL]);

                        if (!class_exists('isys_cmdb_dao_category_g_multiedit') || !defined('C__MODULE__PRO')) {
                            $triggerMultiEdit = false;
                            $message = 'LC__MODULE__MULTIEDIT__MULTIEDIT_IS_NOT_AVAILABLE';
                        }

                        if (!$this->m_rights['multiedit'] && defined('C__MODULE__PRO')) {
                            throw new isys_exception_auth(isys_application::instance()->container->get('language')
                                ->get('LC__MODULE__MULTIEDIT__AUTH_ERROR'));
                        }

                        if (!defined('C__MODULE__PRO')) {
                            $triggerMultiEdit = false;
                            $message = 'The "list edit" feature is not available in i-doit OPEN. Please select a single object to edit.';
                            $method = 'info';
                        }

                        if ($triggerMultiEdit) {
                            $retriever = null;
                            switch ($l_catmeta['type']) {
                                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                                    $retriever = new \idoit\Module\Multiedit\Model\SpecificCategories($this->get_module_request()
                                        ->get_database());
                                    break;
                                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                                    $retriever = new \idoit\Module\Multiedit\Model\GlobalCategories($this->get_module_request()
                                        ->get_database());
                                    break;
                            }

                            if ($retriever !== null && in_array($l_catmeta['id'], $retriever->getBlacklist())) {
                                // Category is in blacklist
                                $message = 'LC__MODULE__MULTIEDIT__CATEGORY_IS_NOT_AVAILABLE_FOR_LISTEDIT';
                            } else {
                                // load multiedit
                                $l_gets[C__CMDB__GET__CATG] = defined_or_default('C__CATG__MULTIEDIT');
                                $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
                            }
                        }

                        if ($message !== '') {
                            isys_notify::$method(isys_application::instance()->container->get('language')
                                ->get($message, [isys_application::instance()->container->get('language')
                                    ->get($this->m_cat_dao->getCategoryTitle())]), ['sticky' => true]);
                        }

                        $this->trigger_module_reload();
                        unset($l_posts[C__GET__NAVMODE]);
                        break;
                    }

                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

                    $l_dao_lock->add_lock($l_object_id);
                }
                break;

            case C__NAVMODE__UP:
                $l_token_manager->removeToken($l_csrf_token_id);

                // Set new GET parameters for category view and reload module.
                if (isset($l_gets[C__CMDB__GET__CATLEVEL])) {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_CATEGORY;
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
                    $l_gets[C__CMDB__GET__CATLEVEL] = null;
                    unset($l_gets[C__CMDB__GET__CATLEVEL]);
                } else {
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                }

                unset($l_posts[C__GET__NAVMODE]);
                break;
            case C__NAVMODE__PURGE:
                $l_token_manager->removeToken($l_csrf_token_id);

                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::DELETE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::DELETE),
                            isys_application::instance()->container->get('language')
                                ->get($this->m_cat_dao->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                $l_class_as_string = get_class($this->m_cat_dao);

                if ($l_class_as_string === 'isys_cmdb_dao_category_g_custom_fields') {
                    $this->m_cat_dao->set_catg_custom_id($_GET[C__CMDB__GET__CATG_CUSTOM]);
                }

                // Ask CMDB for category entry count
                $l_res = $this->m_cat_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                if ($l_res->num_rows() === 1 || $l_class_as_string === 'isys_cmdb_dao_category_g_custom_fields') {
                    $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);
                    $l_data = $l_res->get_row();

                    if (strpos($l_catdata["table_list"], "isys_cats") === 0) {
                        if (strripos($l_catdata["table_list"], "_list")) {
                            $l_catdata["table_list"] = substr($l_catdata["table_list"], 0, strripos($l_catdata["table_list"], "_list"));
                        }

                        $l_catdata["table_list"] = trim($l_catdata["table_list"]) . '_list';
                    }

                    if (strpos($l_catdata["table_list"], "isys_catg_custom") === 0) {
                        $l_data_field = $l_catdata["table_list"] . '__data__id';
                    } else {
                        $l_data_field = $l_catdata["table_list"] . '__id';
                    }

                    $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_RANK, [
                        C__CMDB__RANK__DIRECTION_DELETE,
                        $this->m_cat_dao,
                        $l_catdata["table_list"],
                        // source list
                        [$l_data[$l_data_field]],
                        C__NAVMODE__PURGE
                    ]);

                    $l_result = $l_actionproc->process();
                    $l_actionproc->result_clear();
                }
                break;

            default:
                // This usually happens, when we create a new object.
                isys_component_template::instance()
                    ->assign("csrf_value", $l_token_manager->getToken($l_csrf_token_id)
                        ->getValue());

                if ($l_object_id && isys_application::instance()->session->get_user_id() != $this->m_cat_dao->get_object($l_object_id)
                        ->get_row_value('isys_obj__owner_id')) {
                    // Check, if the user is allowed to view this category.
                    $l_auth_instance->check(isys_auth::VIEW, 'OBJ_ID/' . $l_object_id);
                }

                // Block detecting if the edit mode should be used. This is necessary for newborn entries.
                if ($l_catmeta["type"] != C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                    // Get entry ID.
                    $l_catdata = $this->m_cat_dao->get_general_data();

                    if ($this->m_cat_dao instanceof isys_cmdb_dao_category_g_custom_fields) {
                        $l_catdata = $this->m_cat_dao->get_data(null, $l_object_id)->get_row();
                    }

                    // Get source table for selected category.
                    $l_srctable = $this->m_dao_cmdb->gui_get_source_table_by_category($l_catmeta["type"], $this->m_cat_dao->get_category_id());

                    // Set entrystatus to normal in overview category, because there will never be data inside isys_catg_overview_list.
                    if ($l_gets[C__CMDB__GET__CATG] == defined_or_default('C__CATG__OVERVIEW') || $l_catmeta["table"] == "isys_catg_virtual") {
                        $l_entrystatus = C__RECORD_STATUS__NORMAL;
                    } else {
                        // Otherwize determine status of entry.
                        $l_entrystatus = $l_catdata[$l_srctable . "_list__status"];
                    }

                    // If there is no entry status (i.e. no entry) or the entry is a newborn, switch directly to editmode.
                    if (($l_entrystatus == null || $l_entrystatus == C__RECORD_STATUS__BIRTH) && !$l_multi_gui && ($this->m_rights[isys_auth::EDIT] || $this->m_rights[isys_auth::CREATE])) {
                        $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                        $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                            ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                            ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                            ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);
                    } else {
                        $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
                        if (!$l_locked) {
                            if (!$l_navbar->is_active(C__NAVBAR_BUTTON__SAVE)) {
                                $l_navbar->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)
                                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                            } else {
                                $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                                    ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
                            }
                        }
                    }
                } else {
                    /*
                     * On specific categories, we cannot handle this situation at the moment,
                     * since we're binding them directly to the distributor and sub-categories
                     * are handled manually.
                     */
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
                    if (!$l_locked) {
                        $l_navbar->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)
                            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                    }
                }

                // Inner-category list view active?
                if (!empty($l_gets[C__CMDB__GET__CAT_LIST_VIEW])) {
                    $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                        ->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__NEW)
                        ->set_visible(true, C__NAVBAR_BUTTON__NEW);
                }

                // ID-2385: If object with status birth prepare for cancel
                $object = $this->m_cat_dao->get_object($l_object_id)
                    ->get_row();
                if (is_array($object) && $object['isys_obj__status'] == C__RECORD_STATUS__BIRTH) {
                    $cancelOnclick = 'document.isys_form.navMode.value=\'' . C__NAVMODE__CANCEL . ' \';';
                    $cancelOnclick .= 'form_submit(\'\', \'post\', \'no_replacement\', null, function(response) {window.location = response.responseText;});';

                    $l_navbar->set_js_onclick($cancelOnclick, C__NAVBAR_BUTTON__CANCEL);
                }
        }

        if ($l_locked) {
            $l_navbar::set_locked(true);
            $l_navbar->hide_all_buttons()
                ->deactivate_all_buttons();
            $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__OFF;
        }

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__UP);

        $this->get_module_request()
            ->_internal_set_private("m_get", $l_gets);
        $this->get_module_request()
            ->_internal_set_private("m_post", $l_posts);
        $this->get_module_request()
            ->_internal_set_private("m_navbar", $l_navbar);
        $this->readapt_form_action();

        return $l_result;
    }

    /**
     * Process category view and handles the "authorization-fail" view.
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception|isys_exception_cmdb
     * @return  null
     */
    public function process()
    {
        // Prepare operation data
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_actionproc = $this->get_action_processor();

        try {
            if ($this->category_init()) {
                // Handle selected navigation mode and fill action processor.
                $l_result = $this->handle_navmode($l_posts[C__GET__NAVMODE]);

                // Process actions (if there are any).
                $l_actionproc->process();

                // Process object overview.
                $l_contentTop = new isys_cmdb_view_contenttop($this->m_modreq);
                $l_contentTop->process();

                // Process category view.
                $this->category_process($l_result);
            }
        } catch (isys_exception_auth $e) {
            global $index_includes;

            $index_includes["contentbottomcontent"] = 'exception-auth.tpl';
            $this->deactivate_view();
            $this->get_module_request()
                ->get_template()
                ->assign('exception', $e->write_log());
        } catch (isys_exception_cmdb $e) {
            throw $e;
        } catch (Exception $e) {
            throw new isys_exception_cmdb($e->getMessage());
        }

        return null;
    }

    /**
     * Method which is responsible for initializing the category-specific classes $this->m_cat_dao and $this->m_cat_ui.
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @throws  isys_exception_cmdb
     * @throws  Exception|isys_exception_cmdb
     * @return  boolean
     */
    public function category_init()
    {
        try {
            // Obtain necessary globals.
            $l_gets = $this->get_module_request()
                ->get_gets();

            $l_gets = Purify::castIntValues($l_gets);

            $l_database = $this->get_module_request()
                ->get_database();

            // Deactivate all navbar buttons - These will be activated in "handle_navmode()"
            $this->get_module_request()
                ->get_navbar()
                ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                ->set_active(false, C__NAVBAR_BUTTON__RECYCLE)
                ->set_active(false, C__NAVBAR_BUTTON__DUPLICATE)
                ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                ->set_active(false, C__NAVBAR_BUTTON__PRINT);

            /*
             * Warning!
             *
             * Only cateID is processed here by the distributor. There are situations where you can't use cateID,
             * because it's automatically initiating the list-fetching procedure in the category-factory.
             *
             * To skip this (thus you have to collect the data on your own)
             * use cat1ID -> cat6ID.
             */

            // Which category entry is chosen?
            $l_catentry = $_GET[C__CMDB__GET__CATLEVEL];
            $l_catmeta = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);
            $l_cat_const = $l_gets[$l_catmeta["get"]];

            /* Rights and category stuff */
            $this->m_categoryData = $this->m_dao_cmdb->gui_get_info_by_category($l_catmeta["type"], $l_cat_const);
            if ($this->m_categoryData->count()) {
                $this->m_categoryData = $this->m_categoryData->get_row();
            } else {
                $this->m_categoryData = [];
            }

            $this->m_categoryConst = $this->m_categoryData["isysgui_" . $l_catmeta["string"] . "__const"];
            $auth = isys_auth_cmdb::instance();
            $objectId = (int)$l_gets[C__CMDB__GET__OBJECT];

            $this->m_rights = [
                isys_auth::CREATE     => $auth->has_rights_in_obj_and_category(isys_auth::CREATE, $objectId, $this->m_categoryConst),
                isys_auth::VIEW       => $auth->has_rights_in_obj_and_category(isys_auth::VIEW, $objectId, $this->m_categoryConst),
                isys_auth::EDIT       => $auth->has_rights_in_obj_and_category(isys_auth::EDIT, $objectId, $this->m_categoryConst),
                isys_auth::DELETE     => $auth->has_rights_in_obj_and_category(isys_auth::DELETE, $objectId, $this->m_categoryConst),
                isys_auth::ARCHIVE    => $auth->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $objectId, $this->m_categoryConst),
                isys_auth::SUPERVISOR => $auth->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $objectId, $this->m_categoryConst),
                'multiedit' => isys_auth_multiedit::instance()->is_allowed_to(isys_auth::EXECUTE, 'MULTIEDIT')
            ];

            // Get all necessary data for the current category.
            $l_catdata = $this->m_categoryData;

            // Determine if it is multivalued.
            $l_is_multivalued = !empty($l_catdata['isysgui_' . $l_catmeta["string"] . '__list_multi_value']);

            // Initialize distributor.
            $l_object_id = $l_gets[C__CMDB__GET__OBJECT];
            $l_strError = '';

            // Emit signal.
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.beforeCategoryInit", $l_object_id, $l_catentry, $l_catmeta);

            if (!$l_object_id || $l_object_id == "") {
                throw new isys_exception_cmdb("Distributor couldn't determine object ID (id = '$l_object_id')", C__CMDB__ERROR__CATEGORY_PROCESSOR);
            }

            // Foreign key id.
            $l_nFKID = null;

            if ($l_is_multivalued) {
                $l_nFKID = $l_catentry;
            }

            // Check for object existence.
            if ($this->m_dao_cmdb->obj_exists($l_object_id)) {
                $l_dist = new isys_cmdb_dao_distributor(
                    $l_database,
                    $l_object_id,
                    $l_catmeta["type"],
                    $l_nFKID,
                    isys_cmdb_dao_distributor::make_category_filter($l_cat_const)
                );

                $l_nDCount = $l_dist->count();

                // Check distributor and count of fetched category access objects.
                if ($l_dist && $l_nDCount) {
                    $l_cat_dao = $l_dist->get_category($l_cat_const);

                    if (!$l_cat_dao) {
                        throw new isys_exception_cmdb("Could not get category DAO for selected category (in " . $l_strError . ")", C__CMDB__ERROR__CATEGORY_PROCESSOR);
                    }

                    if (isset($l_gets[C__CMDB__GET__CATG_CUSTOM]) && is_numeric($l_gets[C__CMDB__GET__CATG_CUSTOM]) && $l_cat_dao instanceof isys_cmdb_dao_category_g_custom_fields) {
                        $l_cat_dao->set_catg_custom_id($l_gets[C__CMDB__GET__CATG_CUSTOM]);
                    }

                    // Get UI class.
                    $l_cat_ui = $l_cat_dao->get_ui();

                    // Build cross-reference between DAO and UI (so that both reference each other in order to work together).
                    $l_cat_ui->init($l_cat_dao);

                    $this->m_cat_ui = $l_cat_ui;
                    $this->m_cat_dao = $l_cat_dao->set_object_id($l_object_id)
                        ->set_object_type_id($l_cat_dao->get_type_by_object_id($l_object_id)
                            ->get_row_value('isys_obj_type__id'));
                } else {
                    if (class_exists($l_catdata["isysgui_{$l_catmeta["string"]}__class_name"])) {
                        throw new isys_exception_cmdb(
                            "Your selected category '" . isys_application::instance()->container->get('language')
                                ->get($l_catdata["isysgui_{$l_catmeta["string"]}__title"]) .
                            "' with ID '{$l_cat_const}' does not work properly. The class exists, but your database table '{$l_catdata["isysgui_{$l_catmeta["string"]}__source_table"]}' may not.",
                            C__CMDB__ERROR__CATEGORY_PROCESSOR
                        );
                    } else {
                        throw new isys_exception_cmdb("The class {$l_catdata["isysgui_{$l_catmeta["string"]}__class_name"]} for your selected category '" .
                            isys_application::instance()->container->get('language')
                                ->get($l_catdata["isysgui_{$l_catmeta["string"]}__title"]) . "' with ID '{$l_cat_const}' does not exist.", C__CMDB__ERROR__CATEGORY_PROCESSOR);
                    }
                }
            } else {
                throw new isys_exception_cmdb(isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__OBJECT_DOES_NOT_EXIST', $l_object_id), 0, '', false);
            }

            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.afterCategoryInit", $l_object_id, $l_cat_dao, $l_dist);
        } catch (isys_exception_cmdb $e) {
            throw $e;
        } catch (Exception $e) {
            throw new isys_exception_cmdb($e->getMessage());
        }

        return true;
    }

    /**
     * @param null $p_result
     *
     * @throws isys_exception_cmdb
     */
    public function category_process($p_result = null)
    {
        global $index_includes;

        $l_gets = $this->get_module_request()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_navbar = $this->get_module_request()->get_navbar();

        // Rights-Check: Are we allowed to view this category.
        isys_auth_cmdb::instance()
            ->check_rights_obj_and_category(isys_auth::VIEW, $l_gets[C__CMDB__GET__OBJECT], $this->m_categoryConst);

        if ($this->m_cat_dao && $this->m_cat_ui) {
            $l_catmeta = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);
            $l_catdata = $this->m_categoryData;

            // Set customID
            if (method_exists($this->m_cat_dao, 'set_catg_custom_id') && isset($l_gets[C__CMDB__GET__CATG_CUSTOM])) {
                $this->m_cat_dao->set_catg_custom_id($l_gets[C__CMDB__GET__CATG_CUSTOM]);
            }

            $l_list_id = $this->m_cat_dao->get_list_id();

            $this->m_cat_ui->get_template_component()
                ->assign('category_entry_id', $this->m_cat_dao->get_list_id())
                ->assign(C__GET__ID, $l_list_id)
                ->assign('object_id', $this->m_cat_dao->get_object_id())
                ->assign('object_type_id', $this->m_cat_dao->get_object_type_id());

            $l_is_multi = !empty($l_catdata["isysgui_" . $l_catmeta["string"] . "__list_multi_value"]);
            $l_quickpurge = (isys_tenantsettings::get('cmdb.quickpurge') == '1') ? true : false;

            if (!$l_is_multi) {
                // Show print view button
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__PRINT)
                    ->set_visible(true, C__NAVBAR_BUTTON__PRINT);
            }

            $l_catlevel = 0;
            $l_catentry = $this->get_next_category_level($l_catlevel);

            // If navmode = 'save' then we want to see a single object-processor, no list.
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                $l_is_multi = false;
            }

            if (!$l_is_multi || ($l_is_multi && ($l_catentry !== null) && ($l_catlevel == 0))) {
                // UI Category Process.
                $this->m_cat_ui->activate_commentary($this->m_cat_dao);
                $index_includes["contentbottomcontent"] = $this->m_cat_ui->get_template();

                // Enables the ajax saving.
                if (isys_tenantsettings::get('cmdb.registry.quicksave', 1)) {
                    $this->m_cat_ui->enable_ajax_save();
                }

                // Emit signal.
                isys_component_signalcollection::get_instance()
                    ->emit("mod.cmdb.beforeProcess", $this->m_cat_dao, $index_includes["contentbottomcontent"]);

                // Set property's ui parameters
                $this->m_cat_ui->process_ui_rules($this->m_cat_dao->get_properties(C__PROPERTY__WITH__VALIDATION));

                // Process category.
                $this->m_cat_ui->process($this->m_cat_dao);

                if ($this->m_cat_dao->category_entries_purgable() && $_POST[C__GET__NAVMODE] != C__NAVMODE__EDIT && $l_list_id > 0) {
                    $l_navbar->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__PURGE)
                        ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
                }

                // Assign data-identifier attributes to smarty plugins.
                $this->assign_data_identifiers();

                // @see ID-3990 This works only if the category process calls the process_list which assigns the Object-Browser as the New Button
                if (!$l_navbar->is_js_function(C__NAVBAR_BUTTON__NEW) && !$l_navbar->is_js_onclick(C__NAVBAR_BUTTON__NEW)) {
                    $l_navbar->set_visible(false, C__NAVBAR_BUTTON__NEW)
                        ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                        ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE);
                }
            } else {
                /*
                 * JUMP "isys_cmdb_view_list_category"
                 * This category is multivalued, show its category list.
                 */
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_CATEGORY;

                $this->get_module_request()
                    ->_internal_set_private("m_get", $l_gets);
                $this->readapt_form_action();

                switch ($_SESSION["cRecStatusListView"]) {
                    default:
                    case C__RECORD_STATUS__NORMAL:
                        $l_rank_right = $this->m_rights[isys_auth::ARCHIVE];
                        $l_rank_button = C__NAVBAR_BUTTON__ARCHIVE;
                        break;

                    case C__RECORD_STATUS__ARCHIVED:
                        $l_rank_right = $this->m_rights[isys_auth::DELETE];
                        $l_rank_button = C__NAVBAR_BUTTON__DELETE;
                        break;

                    case C__RECORD_STATUS__DELETED:
                        $l_rank_right = $this->m_rights[isys_auth::SUPERVISOR];
                        $l_rank_button = C__NAVBAR_BUTTON__PURGE;
                }

                if (($this->m_rights[isys_auth::ARCHIVE] || $this->m_rights[isys_auth::DELETE]) && $_SESSION["cRecStatusListView"] != C__RECORD_STATUS__NORMAL) {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__RECYCLE)
                        ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE);
                }

                $l_navbar
                    ->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__NEW)
                    ->set_active($l_rank_right, $l_rank_button)
                    ->set_active(($l_quickpurge && $this->m_rights[isys_auth::SUPERVISOR]), C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(true, $l_rank_button)
                    ->set_visible(($l_quickpurge && $this->m_rights[isys_auth::SUPERVISOR]), C__NAVBAR_BUTTON__QUICK_PURGE);

                // Emit signal.
                isys_component_signalcollection::get_instance()
                    ->emit("mod.cmdb.beforeProcessList", $this->m_cat_dao, $index_includes["contentbottomcontent"]);

                // Process list.
                $this->m_cat_ui->process_list($this->m_cat_dao);
            }

            // We need the DAO-class name for the Javascript validation.
            $this->m_cat_ui->get_template_component()
                ->assign('cmdb_category_view_context', true)
                ->assign('dao_class_name', get_class($this->m_cat_dao));
        } else {
            throw new isys_exception_cmdb("Could not process category (DAO or UI not set)", C__CMDB__ERROR__CATEGORY_PROCESSOR);
        }
    }

    /**
     * Save process.
     *
     * @return  null
     */
    public function process_save()
    {
        // Prepare operation data.
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_actionproc = $this->get_action_processor();
        $l_tpl = $this->get_module_request()
            ->get_template();

        try {
            if ($this->category_init()) {
                // Handle selected navigation mode and fill action processor.
                $l_navModeResult = $this->handle_navmode($l_posts[C__GET__NAVMODE]);

                // Process actions (if there are any).
                $l_actionproc->process();

                // Show success message.
                isys_notify::success(isys_application::instance()->container->get('language')
                    ->get('LC__INFOBOX__DATA_WAS_SAVED'));

                if (isset($l_navModeResult[0][1]) && $l_navModeResult[0][1] !== true && $l_navModeResult[0][1] > 0) {
                    $l_tpl->assign('categoryID', $l_navModeResult[0][1]);
                }
            }
        } catch (isys_exception_auth $e) {
            $index_includes["contentbottomcontent"] = 'exception-auth.tpl';

            isys_notify::error(isys_application::instance()->container->get('language')
                    ->get('LC__INFOBOX__DATA_WAS_NOT_SAVED') . ' ' . $e->getMessage(), ['sticky' => true]);
            $e->write_log();
        } catch (isys_exception_cmdb $e) {
            isys_notify::error(isys_application::instance()->container->get('language')
                    ->get('LC__INFOBOX__DATA_WAS_NOT_SAVED') . ' ' . $e->getMessage(), ['sticky' => true]);
            $e->write_log();
        } catch (Exception $e) {
            isys_application::instance()->container->get('logger')->error($e->getMessage() . ' | ' . $e->getTraceAsString());
            isys_notify::error(isys_application::instance()->container->get('language')
                    ->get('LC__INFOBOX__DATA_WAS_NOT_SAVED') . ' ' . $e->getMessage(), ['sticky' => true]);
        }

        return null;
    }

    /**
     * Hides all buttons and the commentary field
     *
     * @author    Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function deactivate_view()
    {
        $this->get_module_request()
            ->get_navbar()
            ->hide_all_buttons();
        $this->get_module_request()
            ->get_navbar()
            ->deactivate_all_buttons();
        $this->get_module_request()
            ->get_template()
            ->assign('bShowCommentary', false);
    }

    /**
     * Dynamically add data identifiers to smarty plugins
     */
    protected function assign_data_identifiers()
    {
        if (is_object($this->m_cat_dao) && $this->m_cat_dao->get_category_id() != defined_or_default('C__CATG__OVERVIEW')) {
            $l_str_dao = get_class($this->m_cat_dao);
            $l_properties = $this->m_cat_dao->get_properties();
            $l_rules = [];

            if (is_array($l_properties)) {
                foreach ($l_properties as $l_key => $l_prop) {
                    if (isset($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID])) {
                        if ($l_str_dao === 'isys_cmdb_dao_category_g_custom_fields') {
                            $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID] = str_replace($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID][0] . '_', 'C__CATG__CUSTOM__', $l_key);
                        }
                        $l_rules[$l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]]['p_dataIdentifier'] = $l_str_dao . '::' . $l_key;
                    }
                }

                if (count($l_rules) > 0) {
                    $this->m_cat_ui->get_template_component()
                        ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
                }
            }
        }
    }

    /**
     * Public constructor overwrites the protected one from isys_cmdb_view.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}
