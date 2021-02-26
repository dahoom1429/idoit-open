<?php

/**
 * CMDB List view for categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_list_category extends isys_cmdb_view_list
{
    /**
     * @var  string
     */
    private $m_cat_const;

    /**
     * Category DAO to use
     *
     * @var  isys_cmdb_dao_category
     */
    private $m_cat_dao;

    /**
     * @var  integer
     */
    private $m_cat_type;

    /**
     * Category UI to use.
     *
     * @var  isys_cmdb_ui_category
     */
    private $m_cat_ui;

    /**
     * @var array
     */
    protected $m_categoryData = [];

    /**
     * @var string
     */
    protected $m_categoryConst = '';

    /**
     * Method for returning the category ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__LIST_CATEGORY;
    }

    /**
     * Method for setting mandatory get-parameters via reference.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECT] = true;
    }

    /**
     * Method for returning the category name.
     *
     * @return  string
     */
    public function get_name()
    {
        return "Kategorieliste";
    }

    /**
     * Method for setting optional get-parameters via reference.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__CATG] = true;
        $l_gets[C__CMDB__GET__CATS] = true;
        $l_gets[C__CMDB__GET__CATLEVEL] = true;
        $l_gets[C__CMDB__GET__OBJECTTYPE] = true;
    }

    /**
     * @param   integer $p_navmode
     *
     * @throws  isys_exception_auth
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_general
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function handle_navmode($p_navmode)
    {
        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_posts = $this->get_module_request()
            ->get_posts();
        $l_navbar = $this->get_module_request()
            ->get_navbar();
        $l_tpl = $this->get_module_request()
            ->get_template();
        $l_actionproc = $this->get_action_processor();
        $l_dao_cmdb = $this->get_dao_cmdb();

        /* Get lock stuff and check if object id is logged */
        $l_object_id = $l_gets[C__CMDB__GET__OBJECT];
        $l_dao_lock = new isys_component_dao_lock(isys_application::instance()->database);

        if (($l_locked = $l_dao_lock->is_locked($l_object_id))) {
            $l_res = $l_dao_lock->get_lock_information($l_object_id);
            $l_row = $l_res->get_row();

            $l_tpl->assign("g_locked", "1")
                ->assign("lock_user", $l_dao_cmdb->get_obj_name_by_id_as_string($l_row["isys_user_session__isys_obj__id"]));
        }

        /* Rights-Check */
        if (isset($this->m_rights[isys_auth::VIEW])) {
            if (!$this->m_rights[isys_auth::VIEW]) {
                throw new isys_exception_auth(isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                        isys_auth::get_right_name(isys_auth::VIEW),
                        isys_application::instance()->container->get('language')
                            ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                    ]));
            }
        }

        switch ($p_navmode) {
            case C__NAVMODE__EXPORT_CSV:

                break;

            case C__NAVMODE__JS_ACTION:
            case C__NAVMODE__NEW:
                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::CREATE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::CREATE),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                $l_catid = null;

                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_CREATE, [
                    &$this->m_cat_dao,
                    &$this->m_cat_ui
                ]);

                // Process the action queue.
                $l_actionproc->process();

                // Check result of category update.
                $l_saveret = $l_actionproc->result_pop();

                if (is_null($l_saveret)) {
                    // Do nothing.
                    break;
                } else {
                    if (is_numeric($l_saveret)) {
                        // New category entry, assign it to cateID now.
                        $l_catid = $l_saveret;
                    } else {
                        // Save was bad, attach validation rules to TOM.
                        $l_tpl->smarty_tom_add_rules("tom.content.bottom.content", $this->m_cat_dao->get_additional_rules());
                    }
                }

                // Category view type.
                $l_catview = null;
                $l_cattree = null;

                $l_catview = C__CMDB__VIEW__CATEGORY;
                $l_cattree = C__CMDB__VIEW__TREE_OBJECT;

                // Set new GET parameters for category view.
                $l_gets[C__CMDB__GET__VIEWMODE] = $l_catview;
                $l_gets[C__CMDB__GET__TREEMODE] = $l_cattree;
                $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                $l_gets[C__CMDB__GET__CATLEVEL] = $l_catid;

                $l_posts[C__GET__NAVMODE] = C__NAVMODE__EDIT;

                // Set new request parameters.
                $this->get_module_request()
                    ->_internal_set_private("m_get", $l_gets);
                $this->get_module_request()
                    ->_internal_set_private("m_post", $l_posts);

                // Set formular action for view jump.
                $this->readapt_form_action();

                // Trigger a module reload now to reset the views.
                $this->trigger_module_reload();
                break;

            case C__NAVMODE__UP:
                $l_gets[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECTTYPE;
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__LIST_OBJECT;
                $l_gets[C__CMDB__GET__CATLEVEL] = null;
                $l_gets[C__CMDB__GET__CATLEVEL_1] = null;

                // Set new request parameters.
                $this->get_module_request()
                    ->_internal_set_private("m_get", $l_gets);
                $this->get_module_request()
                    ->_internal_set_private("m_post", $l_posts);

                // Set form action for view jump.
                $this->readapt_form_action();

                // Trigger a module reload now to reset the views.
                $this->trigger_module_reload();
                break;

            case C__NAVMODE__EDIT:
                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::EDIT]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::EDIT),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                $l_catid = null;

                if (is_array($l_posts["id"])) {
                    $l_catid = @$l_posts["id"][0];
                }

                if ($l_catid) {
                    /*
                     * Using a new method to get all category specific data instead of determine them everytime again and again.
                     * We should use this method everywhere.
                     */
                    $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);

                    // As you can see, this is very very easy now ...
                    $l_gets[C__CMDB__GET__VIEWMODE] = $l_catdata["view"];
                    $l_gets[C__CMDB__GET__TREEMODE] = $l_catdata["tree"];
                    $l_gets[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;
                    $l_gets[$l_catdata["get"]] = $l_catdata["id"];
                    $l_gets[C__CMDB__GET__CATLEVEL] = $l_catid;

                    // Set new request parameters.
                    $this->get_module_request()
                        ->_internal_set_private("m_get", $l_gets);

                    // Set formular action for view jump.
                    $this->readapt_form_action();

                    // Trigger a module reload now to reset the views.
                    $this->trigger_module_reload();
                }

                break;

            case C__NAVMODE__RECYCLE:
                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::EDIT] && !$this->m_rights[isys_auth::ARCHIVE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::EDIT),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                // Determine all detail information for the current category.
                $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);

                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_RANK, [
                    C__CMDB__RANK__DIRECTION_RECYCLE,
                    $this->m_cat_dao,
                    $l_catdata["table_list"],
                    // source list
                    &$l_posts["id"]
                ]);
                break;

            case C__NAVMODE__QUICK_PURGE:
                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::SUPERVISOR]) {
                    // See ID-885
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::SUPERVISOR),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                // Determine all detail information for the current category.
                $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);
                $l_last_status = C__RECORD_STATUS__DELETED;
                while ($_SESSION["cRecStatusListView"] <= $l_last_status) {
                    $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_RANK, [
                        C__CMDB__RANK__DIRECTION_DELETE,
                        $this->m_cat_dao,
                        $l_catdata["table_list"],
                        // source list
                        &$l_posts["id"]
                    ]);

                    $l_last_status--;
                }
                break;

            case C__NAVMODE__ARCHIVE:
                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::ARCHIVE] && !$this->m_rights[isys_auth::DELETE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::ARCHIVE),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                // Determine all detail information for the current category.
                $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);

                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_RANK, [
                    C__CMDB__RANK__DIRECTION_DELETE,
                    $this->m_cat_dao,
                    $l_catdata["table_list"],
                    // source list
                    &$l_posts["id"]
                ]);

                break;
            case C__NAVMODE__PURGE:
            case C__NAVMODE__DELETE:

                // Check, if the user is allowed to create a new category entry.
                if (!$this->m_rights[isys_auth::DELETE]) {
                    throw new isys_exception_auth(isys_application::instance()->container->get('language')
                        ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                            isys_auth::get_right_name(isys_auth::DELETE),
                            isys_application::instance()->container->get('language')
                                ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                        ]));
                }

                $l_check_id = $l_posts["id"];

                if (!is_array($l_check_id)) {
                    $l_check_id = [$l_check_id];
                }

                foreach ($l_check_id as $l_id) {
                    if (method_exists($this->m_cat_dao, 'get_table')) {
                        // We use this here, because a object can get purged when clicking "archive" quickly three times.
                        if ($l_dao_cmdb->cat_get_status_by_id($this->m_cat_dao->get_table(), $l_id) == C__RECORD_STATUS__DELETED) {
                            // This will prevent an object from getting purged, if the user is missing the SUPERVISOR right. See ID-885
                            if (!$this->m_rights[isys_auth::SUPERVISOR]) {
                                throw new isys_exception_auth(isys_application::instance()->container->get('language')
                                    ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                                        isys_auth::get_right_name(isys_auth::SUPERVISOR),
                                        isys_application::instance()->container->get('language')
                                            ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                                    ]));
                            }
                        }
                    }
                }

                // Determine all detail information for the current category.
                $l_catdata = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);

                $l_actionproc->insert(C__CMDB__ACTION__CATEGORY_RANK, [
                    C__CMDB__RANK__DIRECTION_DELETE,
                    $this->m_cat_dao,
                    $l_catdata["table_list"],
                    // source list
                    &$l_posts["id"]
                ]);
                break;
        }

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__PRINT);

        if (!$l_locked) {
            $l_navbar
                ->set_active($this->m_rights[isys_auth::EDIT] || ($this->m_rights[isys_auth::CREATE] && !$this->m_cat_dao instanceof \idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver), C__NAVBAR_BUTTON__NEW)
                ->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__EDIT)
                ->set_active(false, C__NAVBAR_BUTTON__DUPLICATE)
                ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(false, C__NAVBAR_BUTTON__DUPLICATE);

            if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__ARCHIVED || $_SESSION["cRecStatusListView"] == C__RECORD_STATUS__BIRTH) {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::DELETE], C__NAVBAR_BUTTON__DELETE)
                    ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
            }

            if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__NORMAL) {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__ARCHIVE);
            }

            if ($_SESSION["cRecStatusListView"] == C__RECORD_STATUS__DELETED) {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__PURGE)// See ID-885
                    ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
            }

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

            if ($_SESSION["cRecStatusListView"] != C__RECORD_STATUS__DELETED && isys_tenantsettings::get('cmdb.quickpurge') == '1') {
                $l_navbar
                    ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__QUICK_PURGE)// See ID-885
                    ->set_visible($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__QUICK_PURGE);
            }
        }
    }

    /**
     * Init method for the list.
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function list_init()
    {
        $l_database = $this->get_module_request()
            ->get_database();
        $l_gets = $this->get_module_request()
            ->get_gets();

        // Initialize needed variable for distributor resolution.
        $l_cattype = null;
        $l_catobj = $l_gets[C__CMDB__GET__OBJECT];
        $l_catconst = isys_glob_which_isset($l_gets[C__CMDB__GET__CATG], $l_gets[C__CMDB__GET__CATS], $l_gets[C__CMDB__GET__CATD]);

        // Evaluate category type.
        if (isset($l_gets[C__CMDB__GET__CATG])) {
            // For custom categories
            if ($l_gets[C__CMDB__GET__CATG] == defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                $l_cattype = C__CMDB__CATEGORY__TYPE_CUSTOM;
                $l_catconst = $l_gets[C__CMDB__GET__CATG_CUSTOM];
            } else {
                $l_cattype = C__CMDB__CATEGORY__TYPE_GLOBAL;
            }
        } else {
            if (isset($l_gets[C__CMDB__GET__CATS])) {
                $l_cattype = C__CMDB__CATEGORY__TYPE_SPECIFIC;
            }
        }

        // ATTENTION! Type comparison since global type = 0.
        if ($l_cattype !== null && $l_catconst !== null) {
            $l_dist = new isys_cmdb_dao_distributor($l_database, $l_catobj, $l_cattype, null, isys_cmdb_dao_distributor::make_category_filter($l_catconst));

            // Okay, distributor is initialized and some categories have been fetched?
            if ($l_dist && $l_dist->count()) {
                $l_cat_dao = $l_dist->get_category($l_catconst);

                if (!$l_cat_dao) {
                    throw new isys_exception_cmdb("Could not get category DAO for selected category", C__CMDB__ERROR__CATEGORY_PROCESSOR);
                }

                // Get UI class.
                $l_cat_ui = $l_cat_dao->get_ui();

                // Build cross-reference between DAO and UI (so that both reference each other in order to work together).
                $l_cat_ui->init($l_cat_dao);

                $this->m_cat_dao = $l_cat_dao;
                $this->m_cat_ui = $l_cat_ui;
                $this->m_cat_type = $l_cattype;
                $this->m_cat_const = $l_catconst;

                return true;
            } else {
                throw new isys_exception_cmdb("Could not resolve distributor for requested object.", C__CMDB__ERROR__DISTRIBUTOR);
            }
        }

        return false;
    }

    public function list_process()
    {
        global $index_includes;

        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_dao_cmdb = $this->get_dao_cmdb();
        $l_tpl = $this->get_module_request()
            ->get_template();

        // Process object overview.
        $l_contentTop = new isys_cmdb_view_contenttop($this->m_modreq);
        $l_contentTop->process();

        // ATTENTION! Type comparision since global type = 0.
        if ($this->m_cat_type !== null && $this->m_cat_const !== null) {
            $l_category_redirect = false;
            $l_catentry = $l_dao_cmdb->gui_get_info_by_category($this->m_cat_type, $this->m_cat_const)
                ->get_row();

            // Rights-Check.
            if (!$this->m_rights[isys_auth::VIEW]) {
                throw new isys_exception_auth(isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                        isys_auth::get_right_name(isys_auth::VIEW),
                        isys_application::instance()->container->get('language')
                            ->get($l_dao_cmdb->get_catg_name_by_id_as_string($this->m_cat_dao->get_category_id()))
                    ]));
            }

            list($l_keyname, ) = explode("__", key($l_catentry));

            $l_is_multivalued = !empty($l_catentry[$l_keyname . "__list_multi_value"]);

            if ($l_is_multivalued) {
                // @todo Remove category redirect !

                // Good it is multivalued - is there a category level selection set?
                $l_catlevel = null;
                $l_catret = $this->get_next_category_level($l_catlevel);

                if ($l_catret === null) {
                    // Emit signal.
                    isys_component_signalcollection::get_instance()
                        ->emit("mod.cmdb.beforeProcessList", $this->m_cat_dao, $index_includes["contentbottomcontent"]);

                    // If not, show the list.
                    $this->m_cat_ui->process_list($this->m_cat_dao);
                } else {
                    // Otherwise redirect to category view.
                    $l_category_redirect = true;
                }
            } else {
                // Not multivalued, redirect to category single view.
                $l_category_redirect = true;
            }

            if ($l_category_redirect == true) {
                // It is not a list, there may be some navigation error, which we're handling directly by redirecting to the view for the requested category.
                $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

                $this->get_module_request()
                    ->_internal_set_private("m_get", $l_gets);

                $this->trigger_module_reload();
            }
        } else {
            throw new isys_exception_cmdb("Could not resolve category type and constant (current: " . var_export($this->m_cat_type, true) . "/" .
                var_export($this->m_cat_const, true) . ")!", C__CMDB__ERROR__CATEGORY_PROCESSOR);
        }
    }

    /**
     * Constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        $l_gets = $this->get_module_request()->get_gets();
        $l_catmeta = $this->m_dao_cmdb->nav_get_current_category_data($l_gets);

        $auth = isys_auth_cmdb::instance();
        $objectId = (int)$l_gets[C__CMDB__GET__OBJECT];

        $this->m_categoryData = $this->m_dao_cmdb->gui_get_info_by_category($l_catmeta["type"], $l_gets[$l_catmeta["get"]])->get_row();
        $this->m_categoryConst = $this->m_categoryData["isysgui_" . $l_catmeta["string"] . "__const"];


        $this->m_rights = [
            isys_auth::CREATE     => $auth->has_rights_in_obj_and_category(isys_auth::CREATE, $objectId, $this->m_categoryConst),
            isys_auth::VIEW       => $auth->has_rights_in_obj_and_category(isys_auth::VIEW, $objectId, $this->m_categoryConst),
            isys_auth::ARCHIVE    => $auth->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $objectId, $this->m_categoryConst),
            isys_auth::DELETE     => $auth->has_rights_in_obj_and_category(isys_auth::DELETE, $objectId, $this->m_categoryConst),
            isys_auth::EDIT       => $auth->has_rights_in_obj_and_category(isys_auth::EDIT, $objectId, $this->m_categoryConst),
            isys_auth::SUPERVISOR => $auth->has_rights_in_obj_and_category(isys_auth::SUPERVISOR, $objectId, $this->m_categoryConst)
            // See ID-885
        ];
    }
}
