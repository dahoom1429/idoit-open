<?php

use idoit\Component\Table\Filter\Configuration;
use idoit\Component\Table\Pagerfanta\Adapter\DaoAdapter;
use idoit\Component\Table\Table;

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
class isys_cmdb_view_list_object extends isys_cmdb_view_list
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
        $l_actionproc = $this->get_action_processor();
        $l_dao_cmdb = $this->get_dao_cmdb();

        // Find out the object type constant.
        $l_obj_type = $l_dao_cmdb->get_object_type($this->m_id);
        $l_obj_type_const = $l_obj_type['isys_obj_type__const'];

        // Check for edit and delete rights.
        $auth = isys_auth_cmdb::instance();

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
            $l_navbar
                ->set_active($this->m_rights[isys_auth::ARCHIVE], C__NAVBAR_BUTTON__RECYCLE)
                ->set_visible(true, C__NAVBAR_BUTTON__RECYCLE);
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
                                $auth->obj_id(isys_auth::DELETE, $l_obj_id);
                            } catch (isys_exception_auth $e) {
                                $auth->obj_id(isys_auth::ARCHIVE, $l_obj_id);
                            }
                        } else {
                            $auth->obj_id(isys_auth::DELETE, $l_obj_id);
                        }
                    }

                    $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                        C__CMDB__RANK__DIRECTION_RECYCLE,
                        &$l_posts["id"]
                    ]);
                }
                break;
            case C__NAVMODE__QUICK_PURGE:
                if (is_array($l_posts) && isset($l_posts["id"]) && is_array($l_posts["id"])) {
                    foreach ($l_posts["id"] as $l_obj_id) {
                        $auth->obj_id(isys_auth::SUPERVISOR, $l_obj_id);
                    }
                }

                $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                    C__CMDB__RANK__PURGE,
                    &$l_posts["id"]
                ]);
                $l_actionproc->process();
                break;
            case C__NAVMODE__ARCHIVE:
                if (is_array($l_posts) && isset($l_posts["id"]) && is_array($l_posts["id"])) {
                    foreach ($l_posts["id"] as $l_obj_id) {
                        try {
                            $auth->obj_id(isys_auth::DELETE, $l_obj_id);
                        } catch (isys_exception_auth $e) {
                            $auth->obj_id(isys_auth::ARCHIVE, $l_obj_id);
                        }
                    }
                }

                $l_actionproc->insert(C__CMDB__ACTION__OBJECT_RANK, [
                    C__CMDB__RANK__DIRECTION_DELETE,
                    &$l_posts["id"]
                ]);
                break;

            case C__NAVMODE__PURGE:
            case C__NAVMODE__DELETE:
                if (is_array($l_posts) && isset($l_posts["id"]) && is_array($l_posts["id"])) {
                    foreach ($l_posts["id"] as $l_obj_id) {
                        $auth->obj_id(isys_auth::DELETE, $l_obj_id);
                    }
                }

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
                $this->get_module_request()
                    ->_internal_set_private("m_get", $l_gets);
                $this->get_module_request()
                    ->_internal_set_private("m_post", $l_posts);

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
        if (!defined('C__MODULE__CMDB') || !defined('C__MODULE__SYSTEM')) {
            return;
        }
        // Enable cache lifetime of 1 minute for lists.
        // isys_core::expire(60); // LF: This still has some issues!

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV) {
            if ($this->m_dao_list->get_object_count()) {
                try {
                    $this->process_csv_export($this->m_dao_list);
                } catch (Exception $e) {
                    try {
                        // @see  ID-4966  Set a link to the list configuration.
                        $configUrl = isys_helper_link::create_url([
                            C__GET__MODULE_ID        => C__MODULE__SYSTEM,
                            C__GET__MODULE_SUB_ID    => C__MODULE__CMDB,
                            C__GET__SETTINGS_PAGE    => 'list',
                            C__CMDB__GET__OBJECTTYPE => $this->m_id
                        ]);

                        isys_notify::warning(isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__LIST_PLEASE_REFRESH', $configUrl), ['sticky' => true]);

                        $this->process_csv_export($this->m_dao_list->set_defaults(true));
                    } catch (Exception $e) {
                        isys_notify::error($e->getMessage(), ['sticky' => true]);
                    }
                }
            } else {
                isys_notify::warning(isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__CHOOSEN_EMPTY'));
            }
        }

        // The "list_display" variable is used to display the status switcher.
        isys_application::instance()->template->assign('list_display', true)
            ->assign("content_title", isys_application::instance()->container->get('language')
                ->get($this->m_dao_cmdb->get_objtype_name_by_id_as_string($this->m_id)))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=0")
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $this->m_dao_list->get_rec_status())
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($this->m_dao_list->get_rec_array()))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=0")
            ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=1");

        $this->prepare_navbar($_SESSION["cRecStatusListView"]);

        try {
            $result = $this->process_table_component($this->m_dao_list);
        } catch (Exception $e) {
            try {
                // @see  ID-4966  Set a link to the list configuration.
                $configUrl = isys_helper_link::create_url([
                    C__GET__MODULE_ID        => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID    => C__MODULE__CMDB,
                    C__GET__SETTINGS_PAGE    => 'list',
                    C__CMDB__GET__OBJECTTYPE => $this->m_id
                ]);

                isys_notify::warning(isys_application::instance()->container->get('language')
                    ->get('LC__CMDB__LIST_PLEASE_REFRESH', $configUrl), ['sticky' => true]);
                $result = '<div class="exception p10">' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                $result = '<div class="exception p10">' . $e->getMessage() . '</div>';
            }
        }
        // if we request the page with parameter only_content - we render only the content of the table
        if (isys_core::is_ajax_request() && isset($_GET['only_content']) && $_GET['only_content']) {
            echo $result;
            die();
        }
        return $result;
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
            ->set_active($this->m_rights[isys_auth::DELETE], C__NAVBAR_BUTTON__DELETE)
            ->set_active($this->m_rights[isys_auth::SUPERVISOR] && isys_tenantsettings::get('cmdb.quickpurge') == '1', C__NAVBAR_BUTTON__QUICK_PURGE); // See ID-885

        if (defined('C__MODULE__TEMPLATES') && defined('C__MODULE__PRO')) {
            $l_navbar->set_active($this->m_rights[isys_auth::EDIT], C__NAVBAR_BUTTON__DUPLICATE);
        }

        switch ($p_recstatus) {
            case C__RECORD_STATUS__ARCHIVED:
                $l_navbar
                    ->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
                break;

            case C__RECORD_STATUS__DELETED:
                $l_navbar
                    ->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
                    ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE)
                    ->set_active($this->m_rights[isys_auth::SUPERVISOR], C__NAVBAR_BUTTON__PURGE); // See ID-885
                break;
        }
    }

    /**
     * Method for processing the table component.
     *
     * @param isys_cmdb_dao_list_objects $p_list_dao
     */
    protected function process_csv_export(isys_cmdb_dao_list_objects $p_list_dao)
    {
        $tableConfiguration = $p_list_dao
            ->get_table_config()
            ->setGroupingType(isys_cmdb_dao_category_property_ng::C__GROUPING__COMMA);

        // @see  ID-6004  Always use commas to separate multiple values, when exporting CSV.
        $p_list_dao->set_table_config($tableConfiguration);

        $header = $this->get_list_headers($p_list_dao);

        Configuration::filter($p_list_dao, $this->m_id);
        Configuration::sort($p_list_dao, $this->m_id);

        $l_query = $p_list_dao->get_table_query(false);

        $objectRows = [];
        $result = $p_list_dao->retrieve($l_query);

        while ($row = $result->get_row()) {
            // @see  ID-7244  Filter away all "non-selected" objects.
            if (isset($_POST['table_ids']) && is_array($_POST['table_ids']) && !in_array($row['__id__'], $_POST['table_ids'], false)) {
                continue;
            }

            $objectRows[] = $row;
        }

        $csvTable = new isys_component_table_csv(
            $objectRows,
            null,
            $p_list_dao,
            $p_list_dao->get_rec_status()
        );

        // @see  ID-6672  Pass the configuration to the CSV table instance.
        $csvTable
            ->set_table_config($tableConfiguration)
            ->config($header)
            ->createTempTable();
    }

    /**
     * Retrieve the headers for the list
     *
     * @param isys_cmdb_dao_list_objects $p_list_dao
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_list_headers(isys_cmdb_dao_list_objects $p_list_dao)
    {
        $header = [];
        if (($tableConfig = $p_list_dao->get_table_config()) !== false) {
            foreach ($tableConfig->getProperties() as $i => $property) {
                $aliases[$property->getClass() . '__' . $property->getKey()] = $property->getType();

                $header[] = isys_application::instance()->container->get('language')
                    ->get($property->getName());
            }
        }

        return $header;
    }

    /**
     * Method for processing the table component.
     *
     * @param isys_cmdb_dao_list_objects $p_list_dao
     *
     * @return string
     */
    protected function process_table_component(isys_cmdb_dao_list_objects $p_list_dao)
    {
        if (!defined('C__MODULE__CMDB') || !defined('C__MODULE__SYSTEM')) {
            return;
        }
        $adapter = new DaoAdapter($p_list_dao);

        // Increase GROUP_CONCAT max length.
        $p_list_dao->update('SET SESSION group_concat_max_len = ' . $p_list_dao->convert_sql_int(\isys_tenantsettings::get('mysql.group_concat_max_len', 2048)) . ';');

        $header = $filterColumns = $orderColumns = $aliases = [];
        $orderThreshhold = isys_tenantsettings::get('cmdb.limits.table-order-threshhold', 20000);
        $tableConfig = $p_list_dao->get_table_config();
        $objectTypeID = $this->m_id;
        $listViewStatus = ($_SESSION['cRecStatusListView'] ?: C__RECORD_STATUS__NORMAL);

        $sessionSource = Configuration::initSessionSource($objectTypeID);
        $sortSessionSource = Configuration::initSessionSource('sort-' . $objectTypeID);
        $pagingSessionSource = Configuration::initSessionSource('paging-' . $objectTypeID . '-' . $listViewStatus);

        // if tableFilter or filtered comes - clear the session's stored values
        if ((isset($_GET['tableFilter']) && is_array($_GET['tableFilter'])) || isset($_GET['filtered'])) {
            $sessionSource->clear();
            $sortSessionSource->clear();
            $pagingSessionSource->clear();
        }

        $filterValues = Configuration::filter($p_list_dao, $objectTypeID);
        $sortValues = Configuration::sort($p_list_dao, $objectTypeID);
        $pagingValues = Configuration::paging($p_list_dao, $objectTypeID . '-' . $listViewStatus);

        // if not filtering for suggestion - save the filters
        if (!isset($_GET['suggestion'])) {
            $sessionSource->set($filterValues);
            $sortSessionSource->clear();
            $sortSessionSource->set($sortValues);
            $pagingSessionSource->clear();
            $pagingSessionSource->set($pagingValues);
        }

        if ($tableConfig && is_countable($sortValues) && count($sortValues) > 0) {
            $tableConfig->setSortingProperty(key($sortValues));
            $tableConfig->setSortingDirection($sortValues[key($sortValues)]);
        }

        // Check for paging values and set it into table configuration
        if ($tableConfig && !empty($pagingValues)) {
            // Set page number
            if ($pagingValues['page']) {
                $tableConfig->setPaging($pagingValues['page']);
            }

            // Set number of rows per page
            if ($pagingValues['rowsPerPage']) {
                $tableConfig->setRowsPerPage($pagingValues['rowsPerPage']);
            }
        }

        $results = $adapter->getNbResults();

        if ($tableConfig !== false) {
            foreach ($tableConfig->getProperties() as $i => $property) {
                $header[isys_application::instance()->container->get('language')
                    ->get($property->getCategoryName()) . ' > ' . isys_application::instance()->container->get('language')
                    ->get($property->getName())] = isys_application::instance()->container->get('language')
                    ->get($property->getName());

                if (($property->isIndexed() || $results < $orderThreshhold)) {
                    $filterColumns[$property->getPropertyKey()] = isys_application::instance()->container->get('language')
                        ->get($property->getName());
                }
                if ($property->isFilterable()) {
                    $orderColumns[$i] = $property->getPropertyKey();
                }
            }

            $l_dao_cmdb = new isys_cmdb_dao_status(isys_application::instance()->database);

            $l_status_dao = $l_dao_cmdb->get_cmdb_status();
            $statusMapping = [];

            // Language mapping for cmdb status objects.
            while ($row = $l_status_dao->get_row()) {
                // Explicitly skip these two CMDB status instead of relying on "isys_cmdb_status__editable" being 0.
                if ($row['isys_cmdb_status__const'] === 'C__CMDB_STATUS__IDOIT_STATUS' || $row['isys_cmdb_status__const'] === 'C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') {
                    continue;
                }

                $statusMapping[isys_application::instance()->container->get('language')
                    ->get($row["isys_cmdb_status__title"])] = $row["isys_cmdb_status__id"];
            }

            // append status filter with mapping for select
            $filterColumns['isys_cmdb_dao_category_g_global__cmdb_status'] = $statusMapping;

            $isRowClickable = $tableConfig->isRowClickable();
            $orderDefaultColumn = $tableConfig->getSortingProperty();
            $orderDefaultDirection = $tableConfig->getSortingDirection();
            $viewMemoryUnit = $tableConfig->getAdvancedOptionMemoryUnit();
        } else {
            // Try to reproduce the headers by looking at the "old" JSON format.
            $header = array_map('_L', (new isys_array($p_list_dao->get_list_config()))->pluck(3)
                ->toArray());

            $isRowClickable = $p_list_dao->get_list_row_clickable();
            $orderDefaultColumn = null;
            $orderDefaultDirection = null;
            $viewMemoryUnit = -1;

            // @see  ID-4966  Set a link to the list configuration.
            $configUrl = isys_helper_link::create_url([
                C__GET__MODULE_ID        => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID    => C__MODULE__CMDB,
                C__GET__SETTINGS_PAGE    => 'list',
                C__CMDB__GET__OBJECTTYPE => $this->m_id
            ]);

            isys_notify::warning(isys_application::instance()->container->get('language')
                ->get('LC__CMDB__LIST_PLEASE_REFRESH', $configUrl), ['sticky' => true]);
        }

        // It fixes the issue of the incorrect saved table config filter property: ID-4956
        $filterDefaultColumn = [];
        $fields = [$tableConfig->getFilterProperty(), Table::DEFAULT_FILTER_FIELD, key($filterColumns)];
        foreach ($fields as $field) {
            if ($field && isset($filterColumns[$field])) {
                $filterDefaultColumn = [
                    'title' => $filterColumns[$field],
                    'field' => $field
                ];
                break;
            }
        }

        // ID-3717: Suggesting results for filters
        if (\isys_core::is_ajax_request() && $_GET['suggestion']) {
            $data = [];

            $l_query = $p_list_dao->get_table_query(0, 25);

            foreach ($p_list_dao->retrieve($l_query)
                         ->__as_array() as $item) {
                $data[] = [
                    'source' => '',
                    'value'  => $item[$filterDefaultColumn['field']],
                    'link'   => isys_application::instance()->www_path . '?' . C__CMDB__GET__OBJECT . '=' . $item['__id__'],
                    'score'  => 100
                ];
            }

            \isys_core::send_header('Content-Type', 'application/json');
            echo \isys_format_json::encode($data);
            die;
        }

        $currencyConfig = isys_application::instance()->container->locales->get_user_settings(LC_NUMERIC);

        $url = [];
        parse_str($_SERVER['QUERY_STRING'], $url);
        if (isset($url['tableFilter'])) {
            foreach ($url['tableFilter'] as $k => $v) {
                $url["tableFilter[$k]"] = $v;
            }
        }
        unset($url['tableFilter']);

        $replacerOptions = [
            'memoryUnit'               => $viewMemoryUnit,
            'currencyUnit'             => isys_application::instance()->container->locales->get_currency(),
            'currencySeparator'        => $currencyConfig['thousand_sep'],
            'currencyDecimalSeparator' => $currencyConfig['decimal_point'],
            'linkEmailAddresses'       => $tableConfig->getShowEmailLinks()
        ];

        $options = [
            'enableCheckboxes'         => true,
            'rowClick'                 => $isRowClickable,
            'rowClickURL'              => isys_helper_link::create_url([C__CMDB__GET__OBJECT => '%id%']),
            'keyboardCommands'         => true,
            'tableConfigURL'           => isys_helper_link::create_url([
                C__GET__MODULE_ID        => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID    => C__MODULE__CMDB,
                C__GET__SETTINGS_PAGE    => 'list',
                C__GET__TREE_NODE        => 92,
                C__CMDB__GET__OBJECTTYPE => $objectTypeID
            ]),
            'dragDrop'                 => !!isys_tenantsettings::get('cmdb.registry.object_dragndrop', 1),
            'order'                    => true,
            'orderColumns'             => $orderColumns,
            'orderDefaultColumn'       => $orderDefaultColumn,
            'orderDefaultDirection'    => $orderDefaultDirection,
            'routeParams'              => $url,
            'filter'                   => (isset($filterDefaultColumn['field']) && !empty($filterDefaultColumn['field'])),
            'filterColumns'            => $filterColumns,
            'filterDefaultColumn'      => $filterDefaultColumn,
            'filterDefaultValues'      => $filterValues,
            'resizeColumns'            => true,
            'resizeColumnAjaxURL'      => isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'table',
                'func'            => 'saveColumnWidths',
                'identifier'      => 'cmdb.objtype-' . $objectTypeID . '.table-columns'
            ]),
            'resizeTableAjaxURL'       => isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'table',
                'func'            => 'saveTableWidth',
                'identifier'      => 'cmdb.objtype-' . $objectTypeID . '.table-width'
            ]),
            'columnSizes'              => isys_usersettings::get('cmdb.objtype-' . $objectTypeID . '.table-columns', []),
            'tableSize'                => isys_usersettings::get('cmdb.objtype-' . $objectTypeID . '.table-width', 0),
            'rowsPerPage'              => (int)$_GET['rowsPerPage'] ?: \isys_usersettings::get('gui.objectlist.rows-per-page', 50),
            'replacerOptions'          => isys_format_json::encode($replacerOptions),
            'fuzzySuggestion'          => isys_tenantsettings::get('cmdb.table.fuzzy-suggestion', false),
            'fuzzySuggestionThreshold' => isys_tenantsettings::get('cmdb.table.fuzzy-threshold', 0.2),
            'fuzzySuggestionDistance'  => isys_tenantsettings::get('cmdb.table.fuzzy-distance', 50),
            'status'                   => ($this->m_dao_list->get_rec_status() ?: C__RECORD_STATUS__NORMAL),
            'directEditMode'        => isys_tenantsettings::get('cmdb.gui.objectlist.direct-edit-mode', 0),
            'enableMultiSelection'  => true
        ];

        // Deactivate list config link if user has no execute right for the list config @see ID-3538
        if (!isys_auth_cmdb_object_types::instance()
            ->is_allowed_to(isys_auth::EXECUTE, 'LIST_CONFIG')) {
            $options['tableConfigURL'] = null;
        }

        $l_table = new Table($adapter, $tableConfig, $header, $options);

        /*
         * Add the additional onclick event "get_tree" to edit-button, so that a tree for the
         * corresponding object-type is also loaded when editing the object.?
         */
        $l_edit_onclick = "var checks = list_selection().invoke('toInt');" . "if (checks.length > 1) { " . "document.location='multiedit?" . C__CMDB__GET__CATG . "=" . defined_or_default('C__CATG__GLOBAL') . "&objTypeID=" . $objectTypeID . "&preselect=' + Object.toJSON(checks); " .
            "} else if (checks.length == 1) {" . "document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; " . "get_tree_by_object(checks[0], '" .
            C__CMDB__VIEW__TREE_OBJECT . "'); " . "form_submit('?viewMode=" . C__CMDB__VIEW__CATEGORY . "&objTypeID=" . $objectTypeID . "&objID='+checks[0]+'" .
            "&call=category" . "&catgID=" . defined_or_default('C__CATG__OVERVIEW') . "'); }";

        isys_component_template_navbar::getInstance()
            ->set_js_onclick($l_edit_onclick, C__NAVBAR_BUTTON__EDIT);

        // After checking for rights, we can activate the "Export as CSV" button.
        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        return $l_table->render(true);
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
    }

    public function get_optional_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__OBJECTTYPE] = true;
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
