<?php

/**
 * CMDB List view for objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_list_object_old extends isys_cmdb_view_list
{
    /**
     * Holds the list DAO of the current object type.
     *
     * @var  isys_cmdb_dao_list_objects
     */
    private $m_dao_list;

    /**
     * Object type id for this view
     *
     * @var int
     */
    private $m_id;

    /**
     * Returns the view ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__LIST_OBJECT;
    }

    /**
     * Define the mandatory parameters for lists.
     *
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        // Object type not mandatory anymore, since we have the default type "C__OBJTYPE__SERVER"
        // The default type can be configured.
    }

    /**
     * Get the view name.
     *
     * @return  string
     */
    public function get_name()
    {
        return "Objektliste";
    }

    /**
     * Returns the filepath and name of the list template.
     *
     * @return  string
     */
    public function get_template_bottom()
    {
        return "content/bottom/content/object_table_list.tpl";
    }

    /**
     * Returns the filepath and name of the list template.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return "content/top/main_objecttype.tpl";
    }

    /**
     * Method for handling the current navmode.
     *
     * @param   integer $p_navmode
     *
     * @return  mixed
     */
    public function handle_navmode($p_navmode)
    {
        $l_gets = $this->get_module_request()->get_gets();
        $l_posts = $this->get_module_request()->get_posts();
        $l_navbar = $this->get_module_request()->get_navbar();
        $auth = isys_auth_cmdb::instance();
        $l_actionproc = $this->get_action_processor();
        $l_dao_cmdb = $this->get_dao_cmdb();

        // Find out the object type constant.
        $l_obj_type = $l_dao_cmdb->get_object_type($this->m_id);
        $l_obj_type_const = $l_obj_type['isys_obj_type__const'];

        switch ($p_navmode) {
            case C__NAVMODE__EXPORT_CSV:
                break;

            case C__NAVMODE__RECYCLE:

                if (is_array($l_posts) && isset($l_posts["id"]) && is_array($l_posts["id"])) {
                    foreach ($l_posts["id"] as $l_obj_id) {
                        $l_current_status = $l_dao_cmdb->get_object_by_id($l_obj_id)
                            ->get_row_value('isys_obj__status');

                        if ($l_current_status == C__RECORD_STATUS__ARCHIVED) {
                            try {
                                $auth->check(isys_auth::ARCHIVE, 'OBJ_IN_TYPE/' . $l_obj_type_const);
                            } catch (Exception $e) {
                                $auth->check(isys_auth::DELETE, 'OBJ_IN_TYPE/' . $l_obj_type_const);
                            }
                        } else {
                            $auth->check(isys_auth::DELETE, 'OBJ_IN_TYPE/' . $l_obj_type_const);
                        }
                    }

                    $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                        C__CMDB__RANK__DIRECTION_RECYCLE,
                        &$l_posts["id"]
                    ]);
                }
                break;
            case C__NAVMODE__QUICK_PURGE:
                $l_last_status = C__RECORD_STATUS__DELETED;
                while ($_SESSION["cRecStatusListView"] <= $l_last_status) {
                    if (isset($l_posts["id"])) {
                        $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                            C__CMDB__RANK__DIRECTION_DELETE,
                            &$l_posts["id"]
                        ]);
                        $l_last_status--;
                    }
                }
                $l_actionproc->process();
                break;
            case C__NAVMODE__ARCHIVE:
                $auth->check(isys_auth::ARCHIVE, 'OBJ_IN_TYPE/' . $l_obj_type_const);

                $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                    C__CMDB__RANK__DIRECTION_DELETE,
                    &$l_posts["id"]
                ]);
                break;

            case C__NAVMODE__PURGE:
            case C__NAVMODE__DELETE:
                $auth->check(isys_auth::DELETE, 'OBJ_IN_TYPE/' . $l_obj_type_const);

                $l_check_id = $l_posts["id"];

                if (!is_array($l_check_id)) {
                    $l_check_id = [$l_check_id];
                }

                foreach ($l_check_id as $l_id) {
                    // We use this here, because a object can get purged when clicking "archive" quickly three times.
                    if ($l_dao_cmdb->obj_get_status($l_id) == C__RECORD_STATUS__DELETED) {
                        // This will prevent an object from getting purged, if the user is missing the SUPERVISOR right. See ID-885
                        $auth->check(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/' . $l_obj_type_const);
                    }
                }

                $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                    C__CMDB__RANK__DIRECTION_DELETE,
                    &$l_posts["id"]
                ]);
                break;

            case C__NAVMODE__EDIT:
                $auth->check(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $l_obj_type_const);

                $l_objid = null;

                if (is_array($l_posts["id"])) {
                    $l_objid = @$l_posts["id"][0];
                }

                if ($l_objid) {
                    // Determine if overview should be shown.
                    $l_objtypeid = $l_dao_cmdb->get_objTypeID($l_objid);
                    $l_row = $l_dao_cmdb->get_type_by_id($l_objtypeid);
                    $l_overview = $l_row["isys_obj_type__overview"];
                    $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                    $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                    $l_gets[C__CMDB__GET__CATG] = ($l_overview == 1) ? defined_or_default('C__CATG__OVERVIEW') : defined_or_default('C__CATG__GLOBAL');
                    $l_gets[C__CMDB__GET__OBJECT] = $l_objid;

                    // Set new request parameters.
                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);

                    // Set formular action for view jump.
                    $this->readapt_form_action();

                    // Trigger a module reload now to reset the views.
                    $this->trigger_module_reload();
                }

                return;

            case C__NAVMODE__NEW:
                $auth->check(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $l_obj_type_const);

                $l_actionproc->insert(C__CMDB__ACTION__OBJECT_CREATE, [$this->m_id]);

                // Process the action queue.
                $l_actionproc->process();

                // Retrieve last result.
                $l_objid = $l_actionproc->result_pop();

                // Determine if overview should be shown.
                $l_objtypeid = $l_dao_cmdb->get_objTypeID($l_objid);
                $l_row = $l_dao_cmdb->get_type_by_id($l_objtypeid);
                $l_overview = $l_row["isys_obj_type__overview"];

                /*
                 * Set new GET parameters for category view after creation of an object we jump directly into the
                 * category view for the category "Global", so the user has to edit general parameters for the created object.
                 */
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
                $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                $l_gets[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                $l_gets[C__CMDB__GET__CATG] = ($l_overview == 1) ? defined_or_default('C__CATG__OVERVIEW') : defined_or_default('C__CATG__GLOBAL');
                $l_gets[C__CMDB__GET__OBJECT] = $l_objid;

                if (!empty($l_row['isys_obj_type__default_template'])) {
                    $l_posts['useTemplate'] = 1;
                }

                unset($l_posts[C__GET__NAVMODE]);

                // Set new request parameters
                $this->get_module_request()->_internal_set_private("m_get", $l_gets);
                $this->get_module_request()->_internal_set_private("m_post", $l_posts);

                // Set formular action for view jump.
                $this->readapt_form_action();

                $l_navbar
                    ->set_active($this->m_rights[isys_auth::CREATE], C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

                // Trigger a module reload now to reset the views.
                $this->trigger_module_reload();
                break;

            case C__NAVMODE__SAVE:
                $auth->check(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $l_obj_type_const);

                /*
                 * Usually, there should be no other point where we set the NAVMODE to 'save' in the objectlist.
                 * But let us check the duplicateparam additionally to prevent further unexpected handlings.
                 */
                if (isset($_POST['duplicate']) && $_POST['duplicate'] == '1') {
                    (new isys_popup_duplicate)->duplicate();
                }

                break;
            default:
        }

        $l_navbar
            ->set_active($this->m_rights[isys_auth::CREATE], C__NAVBAR_BUTTON__NEW)
            ->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)// Disable paging elements for object lists.
            ->set_visible(false, C__NAVBAR_BUTTON__UP)
            ->set_visible(false, C__NAVBAR_BUTTON__FORWARD)
            ->set_visible(false, C__NAVBAR_BUTTON__BACK)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

        // Delete.
        if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__ARCHIVED) {
            $l_navbar
                ->set_active($this->m_rights[isys_auth::DELETE], C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        }

        // Archive.
        if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__NORMAL) {
            $l_navbar
                ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__ARCHIVE)
                ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE);
        }

        // Recycle.
        if ($_SESSION["cRecStatusListView"] != C__RECORD_STATUS__NORMAL) {
            if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__ARCHIVED) {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE);
            } else {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__RECYCLE)
                    ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE);
            }
        }

        // Purge.
        if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__DELETED) {
            $l_navbar
                ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__PURGE)
                ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
        }

        if ($_SESSION["cRecStatusListView"] != C__RECORD_STATUS__DELETED && isys_tenantsettings::get('cmdb.quickpurge') == '1') {
            $l_navbar
                ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__QUICK_PURGE)
                ->set_visible(true, C__NAVBAR_BUTTON__QUICK_PURGE);
        }

        $this->get_module_request()
            ->get_template()
            ->smarty_tom_add_rule('tom.content.top.filter.p_bInvisible=1');
    }

    /**
     * Method for initializing the list.
     *
     * @return  boolean
     */
    public function list_init()
    {
        return true;
    }

    /**
     * This method returns the needed HTML to display the list.
     *
     * @global  array   $g_dirs
     * @global  integer $g_page_limit
     * @return  string
     */
    public function list_process()
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__CMDB')) {
            return '';
        }
        global $g_dirs;

        // Enable cache lifetime of 1 minute for lists.
        // isys_core::expire(60); // LF: This still has some issues!

        $l_navbar = isys_component_template_navbar::getInstance();
        $language = isys_application::instance()->container->get('language');
        $l_tpl = $this->get_module_request()
            ->get_template();
        $l_objtypeid = $this->m_id;

        /*
         * Add the additional onclick event "get_tree" to edit-button, so that a tree for the
         * corresponding object-type is also loaded when editing the object.?
         */
        $l_edit_onclick = "var checks = window.object_list.options.checkedBoxes;" . "if (checks.length > 1) { " . "document.location='?" . C__CMDB__GET__VIEWMODE . "=" .
            defined_or_default('C__CMDB__VIEW__MULTIEDIT') . "&" . C__CMDB__GET__CATG . "=" . defined_or_default('C__CATG__GLOBAL') . "&preselect=' + Object.toJSON(checks); " . "} else if (checks.length == 1) {" .
            "document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; " . "get_tree_by_object(checks[0], '" . C__CMDB__VIEW__TREE_OBJECT . "'); " .
            "form_submit('?viewMode=" . C__CMDB__VIEW__CATEGORY . "&objTypeID=" . $this->m_id . "&objID='+checks[0]+'" . "&call=category" . "&catgID=" . defined_or_default('C__CATG__OVERVIEW') .
            "'); }";

        $l_navbar->set_js_onclick($l_edit_onclick, C__NAVBAR_BUTTON__EDIT);

        $l_arData = $this->m_dao_list->get_rec_array();

        // The "list_display" variable is used to display the status switcher.
        $l_tpl->assign('list_display', true)
            ->assign("content_title", $language->get($this->m_dao_cmdb->get_objtype_name_by_id_as_string($l_objtypeid)))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=0")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $this->m_dao_list->get_rec_status())
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($l_arData))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=0")
            ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=1");

        try {
            // Maybe refactor "get_list_data" to execute the SQL in a seperate method.
            $l_jsonresult = $this->m_dao_list->get_list_data();
            $l_dao_result = $this->m_dao_list->get_dao_result();

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV) {
                $l_list_data = isys_format_json::decode($l_jsonresult);

                if (is_countable($l_list_data) && count($l_list_data)) {
                    $l_list = new isys_component_list_csv($l_list_data, null, $this->m_dao_list, $this->m_dao_list->get_rec_status());

                    // The configuration needs a header like this: "<5 characters>identifier" => "Header"
                    $l_list_header = array_flip(array_keys($l_list_data[0]));

                    foreach ($l_list_header as $l_key => &$l_value) {
                        $l_value = '.....' . $l_key;
                    }

                    $l_list_header = array_flip($l_list_header);

                    $l_list->config($l_list_header);

                    $l_list->createTempTable();
                    unset($l_list_data, $l_list_header);
                } else {
                    isys_notify::warning($language->get('LC__UNIVERSAL__CHOOSEN_EMPTY'));
                }
            }

            // Prepare navbar buttons.
            $this->prepare_navbar($_SESSION["cRecStatusListView"]);

            if ($l_dao_result->num_rows() == 0) {
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                    ->set_active(false, C__NAVBAR_BUTTON__RECYCLE)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_active(false, C__NAVBAR_BUTTON__DELETE)
                    ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__DUPLICATE)
                    ->set_active(false, C__NAVBAR_BUTTON__PRINT)
                    ->set_active(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_active(false, C__NAVBAR_BUTTON__EXPORT_AS_CSV);
            }

            // Special navbar and record status handling for relation objects.
            if ($l_objtypeid == defined_or_default('C__OBJTYPE__RELATION') || $l_objtypeid == defined_or_default('C__OBJTYPE__PARALLEL_RELATION')) {
                if (isset($_GET['view']) && $_GET['view'] === 'explicit') {
                    $l_navbar->set_visible(false, C__NAVBAR_BUTTON__PRINT)
                        ->set_visible(false, C__NAVBAR_BUTTON__DUPLICATE);
                } else {
                    $l_navbar->hide_all_buttons([C__NAVBAR_BUTTON__EDIT]);
                }

                $l_tpl->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=0");
            }

            // Deactivate archive button if we are showing templates.
            if ($this->m_dao_list->get_rec_status() == C__RECORD_STATUS__TEMPLATE) {
                $l_navbar->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_active(false, C__NAVBAR_BUTTON__RECYCLE);
            }

            // Emit signal.
            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.beforeCreateObjectList", $this->m_comp_list, $l_objtypeid);
        } catch (isys_exception_database $e) {
            return '<div class="exception p10">' . $e->getMessage() . '</div>';
        }

        // We calculate, if the ajax-pager has to be activated...
        $l_ajax_pager = false;
        $l_load_all = '';
        $l_obj_type_rows = $this->m_dao_list->get_object_count();
        $l_preload_objects = isys_glob_get_pagelimit() * ((int)isys_usersettings::get('gui.lists.preload-pages', 30));
        $l_default_sorting = $this->m_dao_list->get_default_sorting_title();
        $l_sorting_direction = $this->m_dao_list->get_sorting_direction();
        $l_auth_obj = isys_auth_cmdb_object_types::instance();

        // Check if user is allowed to view the selected objecttype
        if (method_exists($l_auth_obj, 'check_in_allowed_objecttypes')) {
            $l_auth_obj->check_in_allowed_objecttypes($l_objtypeid);
        }

        // After checking for rights, we can activate the "Export as CSV" button.
        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        $l_objtype_data = $this->m_dao_cmdb->get_objtype($l_objtypeid)
            ->get_row();
        $l_allow_dragging = $l_auth_obj->is_allowed_to(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $l_objtype_data['isys_obj_type__const']);

        // We only activate the ajax-pager if there are more objects in the list than the defined number of rows to preload.
        if ($l_obj_type_rows > $l_preload_objects && !$l_load_complete_list) {
            $l_ajax_pager = true;

            $l_load_all = '<div class="info m5 p5 bold">' .
                '<img src="' . $g_dirs['images'] . 'icons/infobox/blue.png" class="vam" /> <span class="vam">' .
                $language->get('LC__CMDB__LIST_MORE_ITEMS_NOTICE') .
                ' - <a href="javascript:" id="load_all_button">' .
                $language->get('LC__CMDB__LIST_LOAD_ALL') . ' (' . $l_obj_type_rows . ' ' . $language->get('LC__CMDB__CATG__OBJECT') . ')' .
                '</a></span></div>';
        }

        if ($l_jsonresult != '[]') {
            $l_list_config = '';

            if (isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'list_config')) {
                $l_list_config_url = isys_helper_link::create_url([
                    C__GET__MODULE_ID        => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID    => C__MODULE__CMDB,
                    C__GET__SETTINGS_PAGE    => 'list',
                    C__GET__TREE_NODE        => C__MODULE__CMDB . '01337',
                    // As defined in "isys_module_cmdb.class.php->build_tree()".
                    C__CMDB__GET__EDITMODE   => C__EDITMODE__ON,
                    C__CMDB__GET__OBJECTTYPE => $l_objtypeid
                ]);

                $l_list_config = '<a href="' . $l_list_config_url . '">' .
                    '<img width="15px" height="15px" title="' . $language->get('LC__CMDB__LIST_CONFIGURE') . '" alt="' . $language->get('LC__CMDB__LIST_CONFIGURE') . '" src="' . $g_dirs['images'] . 'icons/silk/table_edit.png" class="listConfigureIcon" />' .
                    '</a>';
            }

            $l_filter = $_SESSION['object-list-filter']['obj-type-' . $this->m_id];
            $l_memorize_filter = intval(isys_usersettings::get('gui.objectlist.remember-filter', 300)) > 0;

            $l_return = '<div style="overflow: auto;">
                    ' . $l_list_config . '
                    <div id="mainList"></div>
                        ' . $l_load_all . '
                    </div>

					<script type="text/javascript">
						// Set translations for the table view.
						idoit.Translate.set(\'LC__UNIVERSAL__TITLE_LINK\', \'' . $language->get('LC__UNIVERSAL__TITLE_LINK') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF') . '\');
						idoit.Translate.set(\'LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES\', \'' . $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES') . '\');

						// We set some variables for the list component.
						window.list_vars = {
							image_dir: \'' . $g_dirs['images'] . '\',
							tree_view: \'' . C__CMDB__VIEW__TREE_OBJECT . '\',
							view_mode: \'' . C__CMDB__VIEW__CATEGORY . '\'
						};

						// Creating a new ObjectTypeList instance for the list.
						window.object_list = new Lists.Objects(\'mainList\', {
							max_pages: ' . ceil($l_obj_type_rows / isys_glob_get_pagelimit()) . ',
							ajax_pager: ' . ($l_ajax_pager ? 'true' : 'false') . ',
							ajax_pager_url: "?ajax=1&call=object_list&func=load_objtype_list&dao=' . get_class($this->m_dao_list) . '&object_type=' .
                (int)isys_glob_get_param(C__CMDB__GET__OBJECTTYPE) . '",
							ajax_pager_preload: ' . ((int)isys_usersettings::get('gui.lists.preload-pages', 30)) . ',
							data: ' . $l_jsonresult . ',
							filter: "top",
							filter_save_url: "' . ($l_memorize_filter ? isys_helper_link::create_url([
                    C__GET__AJAX_CALL        => 'object_list',
                    C__GET__AJAX             => 1,
                    'func'                   => 'save_filter',
                    C__CMDB__GET__OBJECTTYPE => $this->m_id
                ]) : '') . '",
							paginate: "top",
							pageCount: ' . (int)isys_glob_get_pagelimit() . ',
							draggable: ' . (($l_allow_dragging) ? 'true' : 'false') . ',
							tr_click: ' . ($this->m_dao_list->activate_row_click() ? 'true' : 'false') . ',
							ndo_state_url: "?' . C__GET__AJAX_CALL . '=monitoring_ndo&' . C__GET__AJAX . '=1&func=load_ndo_states",
							ndo_state_field:"' . $language->get('LC__MONITORING__NDO__STATUS') . '",
							livestatus_state_url: "?' . C__GET__AJAX_CALL . '=monitoring_livestatus&' . C__GET__AJAX . '=1&func=load_livestatus_states",
							livestatus_state_field:"' . $language->get('LC__MODULE__CHECK_MK__STATUS') . '"
							' . ((!empty($l_default_sorting)) ? ',order_direction: "' . $l_sorting_direction . '", order_field:"' . $language->get($l_default_sorting) . '"' : '') . '
						});';

            if ($l_memorize_filter && isset($l_filter['timestamp'], $l_filter['value'], $l_filter['field'])) {
                $l_lifetime = ($l_filter['timestamp'] + isys_usersettings::get('gui.objectlist.remember-filter', 300) > time());

                if ($l_lifetime && is_string($l_filter['field']) && !empty($l_filter['field']) && is_string($l_filter['value']) && !empty($l_filter['value'])) {
                    $l_return .= 'if ($("data-grid-mainList-filter-data")) {$("data-grid-mainList-filter-data").setValue(' . isys_format_json::encode($l_filter['value']) .
                        ').highlight();}' . 'if ($("data-grid-mainList-filter-column")) {$("data-grid-mainList-filter-column").setValue(' .
                        isys_format_json::encode($l_filter['field']) . ').highlight();}' . '$("data-grid-mainList-filter-data").simulate("change");';
                }
            }

            if (!empty($l_default_sorting)) {
                $l_return .= "$('data-grid-mainList-" . $language->get($l_default_sorting) . "').className = '" . $l_sorting_direction . "';";
            }

            $l_return .= 'if ($("load_all_button")) {
							$("load_all_button").observe("click", function() {
								$("mainList").setOpacity(0.5);

								change_action_parameter("load_all", 1);
								change_action_parameter("call", "category");
								form_submit($("isys_form").action, "get");
							});
						}
					</script>';
        } else {
            // Output a message that no data was found with the selected status.
            $l_return = '<p class="p10"><img src="' . $g_dirs['images'] . 'icons/infobox/blue.png" class="vam" /> ' .
                str_replace(
                    '[{var1}]',
                    preg_replace('/ \([0-9]+\)/', '', $l_arData[$this->m_dao_list->get_rec_status()]),
                    $language->get('LC__CMDB__FILTER__NOTHING_FOUND')
                ) . '</p>';
        }

        return $l_return;
    }

    /**
     * Method for preparing the navbar for this request.
     *
     * @param  integer $p_recstatus
     */
    private function prepare_navbar($p_recstatus = C__RECORD_STATUS__NORMAL)
    {
        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_active($this->m_rights[isys_auth::VIEW], C__NAVBAR_BUTTON__PRINT)
            ->set_active($this->m_rights[isys_auth::CREATE], C__NAVBAR_BUTTON__NEW)
            ->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)
            ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__ARCHIVE)
            ->set_active(($this->m_rights[isys_auth::SUPERVISOR] && (isys_tenantsettings::get('cmdb.quickpurge') == '1')), C__NAVBAR_BUTTON__QUICK_PURGE); // See ID-885

        if (defined('C__MODULE__TEMPLATES') && defined('C__MODULE__PRO')) {
            $l_navbar->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__DUPLICATE);
        }

        switch ($p_recstatus) {
            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
                break;

            case C__RECORD_STATUS__DELETED:
                $l_navbar->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__PURGE); // See ID-885
                break;
        }
    }

    /**
     * Method which prepares the list view by getting the object type and an instance of its list-class.
     *
     * @param  isys_module_request $p_modreq
     */
    private function handle_request(isys_module_request $p_modreq)
    {
        $l_dao = new isys_cmdb_dao($p_modreq->get_database());
        $l_gets = $p_modreq->get_gets();

        // Retrieve id from _GET parameters
        if (isset($l_gets[C__CMDB__GET__OBJECTTYPE]) && $l_gets[C__CMDB__GET__OBJECTTYPE]) {
            $this->m_id = $l_gets[C__CMDB__GET__OBJECTTYPE];
        }

        // Find out the object type constant.
        $l_obj_type = $l_dao->get_object_type($this->m_id);

        $objectTypeConstant = $l_obj_type['isys_obj_type__const'];

        if ($objectTypeConstant) {
            $auth = isys_auth_cmdb::instance();

            $authCreate = $auth->is_allowed_to(isys_auth::CREATE, 'OBJ_IN_TYPE/' . $objectTypeConstant);

            if (!$authCreate) {
                $authCreate = isys_auth_cmdb_objects::instance()->is_object_type_allowed($l_obj_type['isys_obj_type__id'], isys_auth::CREATE);
            }

            $this->m_rights = [
                isys_auth::VIEW       => $auth->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/' . $objectTypeConstant),
                isys_auth::CREATE     => $authCreate,
                isys_auth::EDIT       => $auth->is_allowed_to(isys_auth::EDIT, 'OBJ_IN_TYPE/' . $objectTypeConstant),
                isys_auth::DELETE     => $auth->is_allowed_to(isys_auth::DELETE, 'OBJ_IN_TYPE/' . $objectTypeConstant),
                isys_auth::ARCHIVE    => $auth->is_allowed_to(isys_auth::ARCHIVE, 'OBJ_IN_TYPE/' . $objectTypeConstant),
                isys_auth::SUPERVISOR => $auth->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/' . $objectTypeConstant)
            ];
        }

        $l_dao_class = $l_obj_type["isys_obj_type__class_name"];

        if (class_exists($l_dao_class) && is_subclass_of($l_dao_class, 'isys_cmdb_dao_list_objects')) {
            $this->m_dao_list = new $l_dao_class($p_modreq->get_database());
        } else {
            $this->m_dao_list = new isys_cmdb_dao_list_objects($p_modreq->get_database());
        }

        $this->m_dao_list->set_object_type($l_obj_type);

        // Let's set specific object DAO when needed.
        if (method_exists($this->m_dao_list, "get_action_dao")) {
            $l_action_dao = $this->m_dao_list->get_action_dao();

            if ($l_action_dao instanceof isys_cmdb_dao) {
                $this->get_action_processor()
                    ->set_dao($l_action_dao);
            }
        }
    }

    /**
     * Constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        $this->m_id = isys_tenantsettings::get('defaults.cmdb.object-list.type', defined_or_default('C__OBJTYPE__SERVER'));

        parent::__construct($p_modreq);
        $this->handle_request($p_modreq);
    }
}
