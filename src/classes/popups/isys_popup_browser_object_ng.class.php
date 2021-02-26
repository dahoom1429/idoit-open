<?php

use idoit\Component\Browser\Condition\ObjectTypeCondition;
use idoit\Module\Cmdb\Component\Browser\Filter\AuthFilter;

/**
 * i-doit
 *
 * Object browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_object_ng extends isys_component_popup
{
    /**
     * You can provide an array of Condition classes (as strings) to use your own condition logic.
     */
    const C__CUSTOM_CONDITIONS = 'customConditions';

    /**
     * You can provide an array of Filter classes (as strings) to use your own filter logic.
     * ['FilterClass' => 'Filter Parameter']
     */
    const C__CUSTOM_FILTERS = 'customFilters';

    /**
     * Category filter, for easy usage in the template. Example: "C__CATS__ROUTER;C__CATG__MODEL;C__CATG__PORT
     */
    const C__CAT_FILTER = 'catFilter';

    /**
     * Relation filter (type). Example: "C__RELATION_TYPE__SOFTWARE".
     */
    const C__RELATION_FILTER = 'relationFilter';

    /**
     * CMDB filter for objects. Example: "C__CMDB_STATUS__ORDERED;C__CMDB_STATUS__DELIVERED".
     */
    const C__CMDB_FILTER = 'cmdb_filter';

    /**
     * This should ONLY be used the user input! Use "C__CAT_FILTER" for internal filtering instead.
     */
    const C__TYPE_FILTER = 'typeFilter';

    /**
     * The next three parameters can be used to deactivate conditions.
     */
    const C__DISABLE_PRIMARY_CONDITIONS = 'disablePrimaryConditions';
    const C__DISABLE_SECONDARY_CONDITIONS = 'disableSecondaryConditions';
    const C__DISABLE_CUSTOM_CONDITIONS = 'disableCustomConditions';


    // Constants for the tabs.
    const C__OBJECT_BROWSER__TAB           = 'tabconfig';
    const C__OBJECT_BROWSER__TAB__LOCATION = 'location';

    // Constants for callback context.
    const C__CALL_CONTEXT__PREPARATION  = 1;
    const C__CALL_CONTEXT__REQUEST      = 2;
    const C__CALL_CONTEXT__PRESELECTION = 3;

    /**
     * Parameter to determine if the internal object browser calls should consider rights.
     */
    const C__USE_AUTH = 'use_auth';

    /**
     * Callback function for aborting/cancelling the object-browser.
     * Example: idoit.Notify.info('Abort Callback').
     */
    const C__CALLBACK__ABORT = 'callback_abort';

    /**
     * Callback for accepting.
     * Example: idoit.Notify.info('Accept Callback').
     */
    const C__CALLBACK__ACCEPT = 'callback_accept';
    /**
     * Callback function for detaching the object.
     * Example: idoit.Notify.info('Detach Callback').
     */
    const C__CALLBACK__DETACH = 'callback_detach';

    /**
     * Constant for automatic data retrieval.
     * Example: array(array("isys_cmdb_dao_category_s_group", "get_browser_selection"), $_GET[C__CMDB__GET__OBJECT]).
     */
    const C__DATARETRIEVAL = 'dataretrieval';

    /**
     * Disables the detach field.
     * Example: (boolean) true, false. Default: false.
     */
    const C__DISABLE_DETACH = 'p_bDisableDetach';

    /**
     * With this parameter you can tell the object browser to directly enter the edit mode. Used for the IP-list for example.
     * Example: (boolean) true, false.
     */
    const C__EDIT_MODE = 'edit_mode';

    /**
     * Do formsubmit after accept.
     * Example: (boolean) true, false.
     */
    const C__FORM_SUBMIT = 'formsubmit';

    /**
     * Constant for defining the use of multiselection.
     * Example: (boolean) true, false.
     */
    const C__MULTISELECTION = 'multiselection';

    /**
     * ID of the return element. Works only for multiselection!
     * Example: popupReceiver.
     */
    const C__RETURN_ELEMENT = 'returnElement';

    /**
     * Method for retrieving the JSON for the second list.
     * Example: 'isys_cmdb_dao_xyz::object_browser_list' or array("isys_cmdb_dao_xyz", "object_browser_list").
     */
    const C__SECOND_LIST = 'secondList';

    /**
     * Method for retrieving the correct format for displaying the input-contents (called in $this->format_selection())
     * Example: 'isys_cmdb_dao_xyz::format_selection' or array("isys_cmdb_dao_xyz", "format_selection").
     */
    const C__SECOND_LIST_FORMAT = 'secondListFormat';

    /**
     * Constant, if the "second selection"-view shall be activated.
     * Example: (boolean) true, false.
     */
    const C__SECOND_SELECTION = 'secondSelection';

    /**
     * Constant for object preselection.
     * Example: JSON array [1,2,3,4,5,6] or PHP array.
     */
    const C__SELECTION = 'selection';

    /**
     * Constant for defining the browser-title, through language constants.
     * Example: 'LC__BROWSER__TITLE__CONNECTION'.
     */
    const C__TITLE = 'title';

    /**
     * @desc Minimum right to create an object
     * Example: isys_auth::EDIT
     */
    const C__CHECK_RIGHT = 'checkRight';

    /**
     * The selection sorting can only be used with "multiselect". This is used in the nagios categories so far.
     * Example: (boolean) true, false.
     */
    const C__SORT_SELECTION = 'sortSelection';

    /**
     * In some case we don´t want to exclude some objecttypes if we use the category filter
     * Example: "C__OBJTYPE__SUPERNET;C__OBJTYPE__MIGRATION_OBJECT
     */
    const C__TYPE_BLACK_LIST = 'typeBlacklist';

    /**
     * This variable will be used to provide an object ID for custom conditions.
     * @var integer
     */
    protected $contextObjectId = 0;

    /**
     * The params, written in the smarty template.
     *
     * @var  array
     */
    protected $m_params = [];

    /**
     * Tab configuration.
     *
     * @var  array
     */
    protected $m_tabconfig = [
        self::C__OBJECT_BROWSER__TAB__LOCATION => ['disabled' => false],
    ];

    /**
     * Display quickinfo in format_selection
     *
     * @var bool
     */
    protected $m_format_quick_info = true;

    /**
     * Get object type ids which are matched by our multiple filter parameters.
     *
     * @param  array   $p_objecttype        array('C__OBJTYPE__SERVER', 'C__OBJTYPE__ROUTER', ...)
     * @param  array   $p_category          array('C__CATG__GLOBAL', 'C__CATS__ROUTER')
     * @param  array   $p_objtype_blacklist array('C__OBJTYPE__SERVER', 'C__OBJTYPE__ROUTER', ...)
     * @param  boolean $p_as_constant_array
     *
     * @return array
     * @throws Exception
     * @author Selcuk Kekec <skekec@i-doit.org>
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_objecttype_filter(array $p_objecttype = [], array $p_category = [], array $p_objtype_blacklist = [], $p_as_constant_array = false)
    {
        $l_arrObjectTypes = [];
        $db = isys_application::instance()->container->get('database');

        $l_dao = new isys_cmdb_dao_nexgen($db);
        $l_typeFilter = (is_array($p_objecttype) && count($p_objecttype)) ? array_flip($p_objecttype) : false;
        $l_catFilter = (is_array($p_category) && count($p_category)) ? $p_category : false;
        $l_typeBlacklist = (is_array($p_objtype_blacklist) && count($p_objtype_blacklist)) ? array_flip($p_objtype_blacklist) : false;

        if ($l_typeFilter) {
            foreach ($l_typeFilter as $l_objtype_const => $l_key) {
                $l_arr = $l_dao->get_objecttypes_using_cats($l_objtype_const);

                if ($l_arr) {
                    $l_typeFilter = array_merge($l_typeFilter, (array)array_flip($l_arr));
                }
            }
        }

        // Get objecttype groups.
        $l_objgroups = $l_dao->objgroup_get();

        $l_cmdb_dao = isys_cmdb_dao_object_type::instance($db);

        while ($l_row = $l_objgroups->get_row()) {
            // Get object types for current group.
            $l_objtypes = $l_dao->objtype_get_by_objgroup_id($l_row['isys_obj_type_group__id']);
            while ($l_otrow = $l_objtypes->get_row()) {
                // Check if we can skip this object type.
                if ($l_typeFilter && !isset($l_typeFilter[$l_otrow['isys_obj_type__const']])) {
                    continue;
                }

                if ($l_catFilter && !$l_cmdb_dao->has_cat($l_otrow['isys_obj_type__id'], $l_catFilter)) {
                    continue;
                }

                if ($l_typeBlacklist && isset($l_typeBlacklist[$l_otrow['isys_obj_type__const']])) {
                    continue;
                }

                $l_arrObjectTypes[] = $p_as_constant_array ? $l_otrow['isys_obj_type__const'] : $l_otrow['isys_obj_type__id'];
            }
        }

        return $l_arrObjectTypes;
    }

    /**
     * @param bool|true $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_quick_info($bool = true)
    {
        $this->m_format_quick_info = $bool;

        return $this;
    }

    /**
     * Handle specific ajax requests.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @throws  isys_exception_objectbrowser
     */
    public function &handle_ajax_request(isys_module_request $p_modreq)
    {
        // If we get an array, it's most likely something like "array('class::method', array('params'))".
        if (is_array($_GET['request'])) {
            // Write the callback-method in the callback variable.
            $l_callback = $_GET['request'][0];

            // Check if we got a second item in the array (We'll assume they are parameters).
            $l_callback_params = isset($_GET['request'][1]) ? (array)$_GET['request'][1] : [];
        } elseif (is_string($_GET['request'])) {
            // We will take the string, if given, as "class::method".
            $l_callback = $_GET['request'];

            // And assign an empty array as parameters.
            $l_callback_params = [];
        } else {
            throw new isys_exception_objectbrowser(
                'Wrong parameter.',
                'A wrong parameter has been assigned to isys_popup_browser_object_ng::C__SECOND_LIST. The syntax should be: array("class::method", array("params") or "class::method".'
            );
        }

        // Yeah, this can happen sometimes...
        if (is_array($l_callback) && count($l_callback) === 1) {
            $l_callback = $l_callback[0];
        }

        // Get an array from our "class::method" string.
        $l_callback = explode('::', $l_callback);

        if (empty($l_callback_params['modreq'])) {
            $l_callback_params['modreq'] = $p_modreq;
        }

        // Be sure to send JSON headers, so prototype can handle everything right.
        header('Content-type: application/json');

        // We won't blindly instancinate a class - We check first.
        if (class_exists($l_callback[0])) {
            $l_obj = new $l_callback[0]($this->database);

            if (method_exists($l_obj, $l_callback[1])) {
                // Call the method with the context-variable and module request as parameter.
                return $l_obj->{$l_callback[1]}(self::C__CALL_CONTEXT__REQUEST, $l_callback_params);
            }
        }

        return '[]';
    }

    /**
     * Handles the smarty including and displays selected objects and a link to open the popup.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        $imageDirectory = isys_application::instance()->www_path . 'images/';

        // Init.
        $l_strOut = '';
        $l_hiddenValue = '';
        $l_object_id = 0;

        $this->m_params = $p_params;

        $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] = (bool)$this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] = (bool)$this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS] = (bool)$this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS];

        // This is necessary to prevent multiple nested input-groups.
        $this->m_params['disableInputGroup'] = true;

        if (!isset($this->m_params[self::C__USE_AUTH])) {
            $this->m_params[self::C__USE_AUTH] = (bool)isys_tenantsettings::get('auth.use-in-object-browser', false);
        }

        $l_editmode = isys_glob_is_edit_mode();

        // We use this, if we don't use the edit mode for a view and want to display the object browser directly.
        if (isset($this->m_params[self::C__EDIT_MODE])) {
            $l_editmode = (bool)$this->m_params[self::C__EDIT_MODE];
        }

        // Check if theres a name given.
        if (empty($this->m_params['name'])) {
            return '';
        }

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (strpos($this->m_params['name'], '[') !== false && strpos($this->m_params['name'], ']') !== false) {
            $l_tmp = explode('[', $this->m_params['name']);
            $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            $configurationField = $l_tmp[0] . '__CONFIG[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $l_view = $this->m_params['name'] . '__VIEW';
            $l_hidden = $this->m_params['name'] . '__HIDDEN';
            $configurationField = $this->m_params['name'] . '__CONFIG';
        }

        // @see  ID-6927  Make sure to always use the same "raw" instance name.
        $objectBrowserName = self::uniformBrowserName($this->m_params['name']);

        $customObjectTypeFilter = isys_tenantsettings::get('cmdb.object-browser.' . $objectBrowserName . '.objectTypes');

        if (is_array($customObjectTypeFilter) && count($customObjectTypeFilter)) {
            $this->m_params[self::C__TYPE_FILTER] = $this->m_params[self::C__TYPE_FILTER] = implode(';', $customObjectTypeFilter);
        }

        // Extract object id from either p_strValue or p_strSelectedID.
        if ($this->m_params['p_strValue']) {
            $l_object_id = $this->m_params['p_strValue'];
        } elseif ($this->m_params['p_strSelectedID']) {
            $l_object_id = $this->m_params['p_strSelectedID'];
        }

        // Get object name and store id in p_strSelectedID.
        if ($l_object_id) {
            $this->m_params['p_strSelectedID'] = $l_object_id;

            // When in multiselection mode, use a different logic.
            if ($this->m_params[self::C__MULTISELECTION]) {
                // Have we got a array with objects?
                if (is_array($l_object_id)) {
                    // Just assign for later iteration.
                    $l_objects = $l_object_id;
                } elseif (is_string($l_object_id) && isys_format_json::is_json_array($l_object_id)) {
                    // We check if we got a valid JSON.
                    $l_objects = isys_format_json::decode($l_object_id, true);
                } else {
                    // The last option: A comma-separated list.
                    $l_objects = explode(',', $l_object_id);
                }

                // We need this to prevent JSON Arrays with quotes.
                $l_objects = array_map('intval', $l_objects);

                $l_object_array = [];

                $i = 1;
                // Iterate through each object-id.
                foreach ($l_objects as $l_id) {
                    if ($i++ == isys_tenantsettings::get('cmdb.limits.obj-browser.objects-in-viewmode', 8)) {
                        $l_object_array[] = '...';
                        break;
                    }

                    if ($l_id > 0) {
                        $l_object_array[] = $this->set_format_quick_info(!$l_editmode)
                            ->format_selection($l_id);
                    }
                }

                if (count($l_object_array)) {
                    // @see  ID-6310 Option for displaying objects comma separated or as HTML list.
                    if ($l_editmode || isys_tenantsettings::get('cmdb.limits.obj-browser.objects-rendering', 'comma') === 'comma') {
                        $this->m_params['p_strValue'] = implode(', ', $l_object_array);
                    } else {
                        $this->m_params['p_bInfoIconSpacer'] = 0;
                        $this->m_params['p_strValue'] = '<ul class="pl20 m0 list-style-none"><li>' . implode('</li><li>', $l_object_array) . '</li></ul>';
                    }
                }

                // Prepare value for hidden field.
                $l_hiddenValue = isys_format_json::encode($l_objects);
            } else {
                // Prepare value for hidden field.
                $l_hiddenValue = $l_object_id;

                // Prepare value for visible field.
                $this->m_params['p_strValue'] = $this->set_format_quick_info(!$l_editmode)
                    ->format_selection($l_object_id);
            }
        } else {
            // Sometimes we got an empty array, which will cause PHP errors...
            $this->m_params['p_strValue'] = '';
        }

        // This seems to happen sometimes..??
        if (is_array($this->m_params['p_strValue'])) {
            $this->m_params['p_strValue'] = implode(', ', $this->m_params['p_strValue']);
        }

        // Auto Suggesstion and read-only.
        $multiselection = isset($this->m_params[self::C__MULTISELECTION]) && $this->m_params[self::C__MULTISELECTION];
        $secondSelection = isset($this->m_params[self::C__SECOND_SELECTION]) && $this->m_params[self::C__SECOND_SELECTION];

        if ($multiselection || $secondSelection) {
            $this->m_params['p_bReadonly'] = 1;
        } else {
            $filters = [
                self::C__TYPE_FILTER     => $this->m_params[self::C__TYPE_FILTER],
                self::C__CAT_FILTER      => $this->m_params[self::C__CAT_FILTER],
                self::C__TYPE_BLACK_LIST => $this->m_params[self::C__TYPE_BLACK_LIST],
                self::C__CMDB_FILTER     => $this->m_params[self::C__CMDB_FILTER],
                self::C__CUSTOM_FILTERS  => $this->m_params[self::C__CUSTOM_FILTERS]
            ];

            $this->m_params['p_onClick'] = "if (!this.getValue().blank()) {this.writeAttribute('placeholder',this.readAttribute('data-last-value')).setValue('');}";
            $this->m_params['p_onBlur'] = "if (this.getValue().blank()) {this.setValue(this.readAttribute('data-last-value'));}";
            $this->m_params['p_strSuggest'] = 'object-browser';
            $this->m_params['p_strSuggestView'] = $l_view;
            $this->m_params['p_strSuggestHidden'] = $l_hidden;
            $this->m_params['p_strSuggestParameters'] = 'parameters: ' . isys_format_json::encode($filters) . ',' .
                "selectCallback: function(view, li) {view.writeAttribute('data-last-value', li.readAttribute('title'));" . $this->m_params[self::C__CALLBACK__ACCEPT] . '}';
        }

        if (!isset($this->m_params[self::C__CHECK_RIGHT])) {
            $this->m_params[self::C__CHECK_RIGHT] = 'isys_auth::EDIT';
        }

        if ($l_editmode) {
            $this->m_params['p_additional'] .= ' data-hidden-field="' . str_replace('"', "'", $l_hidden) . '" data-last-value="' . $this->m_params['p_strValue'] . '"';
            $this->m_params['id'] = $l_view;

            $l_strOut .= $l_objPlugin->navigation_edit($this->template, $this->m_params);

            if (!isset($this->m_params['p_bDisabled']) && !$this->m_params['p_bDisabled']) {
                $onClick = $this->process_overlay(C__CMDB__GET__OBJECT . '=' . isys_glob_get_param(C__CMDB__GET__OBJECT) . "&live_preselection=' + \$F('" . $l_hidden .
                    "') + '", '80%', '90%', null, null, 1000, 300, 1600, 800, $configurationField);

                $l_strOut .= '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_hiddenValue . '" />' . '<a href="javascript:" title="' .
                    $this->language->get('LC__UNIVERSAL__ATTACH') . '" class="' . $this->m_params['name'] .
                    ' attach input-group-addon input-group-addon-clickable" onclick="' . $onClick . '" >' . '<img src="' . $imageDirectory .
                    'icons/silk/zoom.png" alt=" " title="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" />' . '</a>';

                if (!isset($this->m_params[self::C__DISABLE_DETACH])) {
                    $l_detach_callback = isset($this->m_params[self::C__CALLBACK__DETACH]) ? $this->m_params[self::C__CALLBACK__DETACH] : "";

                    $l_onclick_detach = "var e_view = $('" . $l_view . "'), e_hidden = $('" . $l_hidden . "');" . "if(e_view && e_hidden) {" .
                        "e_view.writeAttribute('data-last-value', '" . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "').setValue('" .
                        $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "'); " . "e_hidden.setValue('');" . "}" . $l_detach_callback;

                    $l_strOut .= '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" class="' . $this->m_params['name'] .
                        ' detach input-group-addon input-group-addon-clickable" onclick="' . $l_onclick_detach . ';">' . '<img src="' . $imageDirectory .
                        'icons/silk/detach.png" alt=" " title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" />' . '</a>';
                }

                if (isset($this->m_params[self::C__SORT_SELECTION]) && $this->m_params[self::C__SORT_SELECTION] && isset($this->m_params[self::C__MULTISELECTION]) &&
                    $this->m_params[self::C__MULTISELECTION]) {
                    $l_onclick_sort = 'new BrowserSelectionSorter(\'' . $l_view . '\', {values:$F(\'' . $l_hidden . '\'),hidden:\'' . $l_hidden . '\'});';

                    $l_strOut .= '<a href="javascript:" title="' . $this->language->get('LC__REPORT__INFO__SORTING') .
                        '" class="input-group-addon input-group-addon-clickable" onclick="' . $l_onclick_sort . ';">' . '<img src="' . $imageDirectory .
                        'icons/silk/arrow_switch.png" alt=" " title="' . $this->language->get('LC__CMDB__OBJECT_BROWSER__SORT_SELECTION') . '" />' . '</a>';
                }
            }

            return $l_strOut . '<input id="' . $configurationField . '" name="' . $configurationField . '" value="' . isys_glob_htmlentities(isys_format_json::encode($this->m_params)) . '" type="hidden" />';
        }

        $this->m_params['p_bHtmlDecode'] = true;

        return $l_objPlugin->navigation_view($this->template, $this->m_params);
    }

    /**
     * Method for retrieving a pre-formatted text for the input-elements.
     *
     * @param  integer $p_objid
     * @param  boolean $plain
     *
     * @return string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_objid, $plain = false)
    {
        // Very important: We only want integers here!
        $p_objid = (int)$p_objid;

        // If the given object id is empty (0) we return an empty string.
        if ($p_objid === 0) {
            return '';
        }

        if ($this->m_params[self::C__USE_AUTH] && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_objid)) {
            return '[' . $this->language->get('LC__UNIVERSAL__HIDDEN') . ']';
        }

        // Init.
        $l_object_title = '';
        $l_return = '';

        if (isset($this->m_params[self::C__SECOND_LIST_FORMAT]) && !empty($this->m_params[self::C__SECOND_LIST_FORMAT])) {
            $l_callback = $this->m_params[self::C__SECOND_LIST_FORMAT];

            if (is_string($l_callback)) {
                // We check if we got a valid JSON - But we have to go sure it is an JSON array.
                if (isys_format_json::is_json_array($l_callback)) {
                    $l_callback = isys_format_json::decode($l_callback, true);
                } else {
                    $l_callback = explode('::', $this->m_params[self::C__SECOND_LIST_FORMAT]);
                }
            }

            if (class_exists($l_callback[0])) {
                $l_obj = new $l_callback[0]($this->database);

                if (method_exists($l_obj, $l_callback[1])) {
                    return $l_obj->{$l_callback[1]}($p_objid, !$this->m_format_quick_info);
                }
            }
        } else {
            if (empty($p_objid)) {
                return $this->language->get('LC__CMDB__BROWSER_OBJECT__NONE_SELECTED');
            }

            if (strpos($p_objid, ',') !== false) {
                $l_obj_ids = explode(',', $p_objid);
            } else {
                $l_obj_ids = [$p_objid];
            }

            // We need a DAO for the object name.
            $l_dao_cmdb = new isys_cmdb_dao($this->database);
            $l_quick_info = new isys_ajax_handler_quick_info($_GET, $_POST);

            foreach ($l_obj_ids as $l_obj_id) {
                if ($l_obj_id > 0) {
                    $l_object_title .= $this->language->get($l_dao_cmdb->get_objtype_name_by_id_as_string($l_dao_cmdb->get_objTypeID($l_obj_id))) . ' >> ' .
                        $l_dao_cmdb->get_obj_name_by_id_as_string($l_obj_id);

                    if ($this->m_format_quick_info) {
                        $l_return .= $l_quick_info->get_quick_info($l_obj_id, $l_object_title, C__LINK__OBJECT) . ', ';
                    } else {
                        $l_return .= str_replace('"', '', $l_object_title) . ', ';
                    }
                }
            }

            return substr($l_return, 0, -2);
        }

        return '';
    }

    /**
     * This is the default entrypoint of the object browser.
     * Every time the browser gui is loaded, this method gets called.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws Exception
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Parameter retrieval.
        $this->m_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        if (!is_bool($this->m_params[self::C__MULTISELECTION])) {
            if (is_string($this->m_params[self::C__MULTISELECTION])) {
                $this->m_params[self::C__MULTISELECTION] = in_array(strtolower($this->m_params[self::C__MULTISELECTION]), ['true', 'yes', 'on']);
            } else {
                // Try to cast numbers and any other types dynamically.
                $this->m_params[self::C__MULTISELECTION] = (bool) $this->m_params[self::C__MULTISELECTION];
            }
        }

        // Parameter validation.
        if (!is_array($this->m_params)) {
            new isys_exception_general('Parameter error');
        }

        if (!isset($this->m_params[self::C__USE_AUTH])) {
            // ID-2895 - Only append the auth-condition, if this feature is enabled.
            $this->m_params[self::C__USE_AUTH] = (bool) isys_tenantsettings::get('auth.use-in-object-browser', false);
        }

        if (strpos($this->m_params['name'], '[') !== false && strpos($this->m_params['name'], ']') !== false) {
            $l_tmp = explode('[', $this->m_params['name']);
            $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $l_view = $this->m_params['name'] . '__VIEW';
            $l_hidden = $this->m_params['name'] . '__HIDDEN';
        }

        // @see  ID-6927  Make sure to always use the same "raw" instance name.
        $objectBrowserName = self::uniformBrowserName($this->m_params['name']);

        $customObjectTypeFilter = isys_tenantsettings::get('cmdb.object-browser.' . $objectBrowserName . '.objectTypes');
        // @see ID-6060 Sort by the configured field.
        $defaultSortingField = isys_tenantsettings::get('cmdb.object-browser.' . $objectBrowserName . '.defaultSortingFieldIndex', null);
        $defaultSortingDirection = isys_tenantsettings::get('cmdb.object-browser.' . $objectBrowserName . '.defaultSortingDirection', 'asc');

        if (is_array($customObjectTypeFilter) && count($customObjectTypeFilter)) {
            $this->m_params[self::C__TYPE_FILTER] = implode(';', $customObjectTypeFilter);
        }

        if (isset($this->m_params[self::C__OBJECT_BROWSER__TAB]) && !empty($this->m_params[self::C__OBJECT_BROWSER__TAB])) {
            foreach ($this->m_params[self::C__OBJECT_BROWSER__TAB] as $l_tab_type => $l_tab_status) {
                $this->m_tabconfig[$l_tab_type]['disabled'] = !$l_tab_status;
            }
        }

        try {
            $globalCategoryFilter = array_filter(isys_string::split($this->m_params[self::C__CAT_FILTER]), function ($categoryConst) {
                return strpos($categoryConst, 'CATG') !== false;
            });

            $specificCategoryFilter = array_filter(isys_string::split($this->m_params[self::C__CAT_FILTER]), function ($categoryConst) {
                return strpos($categoryConst, 'CATS') !== false;
            });

            // Assign some smarty configuration variables.
            $this->template
                ->assign('objectBrowserName', $this->m_params['name'])
                ->assign(self::C__MULTISELECTION, (int)$this->m_params[self::C__MULTISELECTION])
                ->assign(self::C__CALLBACK__ACCEPT, $this->m_params[self::C__CALLBACK__ACCEPT])
                ->assign(self::C__CALLBACK__ABORT, $this->m_params[self::C__CALLBACK__ABORT])
                ->assign(self::C__FORM_SUBMIT, $this->m_params[self::C__FORM_SUBMIT])
                ->assign(self::C__TYPE_FILTER, $this->m_params[self::C__TYPE_FILTER])
                ->assign(self::C__TYPE_BLACK_LIST, $this->m_params[self::C__TYPE_BLACK_LIST])
                ->assign(self::C__CMDB_FILTER, $this->m_params[self::C__CMDB_FILTER])
                ->assign(self::C__CHECK_RIGHT, $this->m_params[self::C__CHECK_RIGHT])
                ->assign('specificCategoryFilter', implode(',', $specificCategoryFilter))
                ->assign('globalCategoryFilter', implode(',', $globalCategoryFilter))
                ->assign('customFilters', $this->m_params[self::C__CUSTOM_FILTERS])
                ->assign('defaultSortingField', isys_format_json::encode($defaultSortingField))
                ->assign('defaultSortingDirection', $defaultSortingDirection);

            // Look, if we set an own title for this browser instance.
            if (!isset($this->m_params[self::C__TITLE])) {
                $this->template->assign('browser_title', $this->language->get('LC__POPUP__BROWSER__OBJECT_BROWSER'));
            } else {
                $this->template->assign('browser_title', $this->language->get($this->m_params[self::C__TITLE]));
            }

            // Automatically set the return element.
            if (isset($this->m_params['name']) && (!isset($this->m_params[self::C__RETURN_ELEMENT]) || empty($this->m_params[self::C__RETURN_ELEMENT]))) {
                $this->template
                    ->assign('return_element', $l_hidden)
                    ->assign('return_view', $l_view);
            } else {
                $this->template->assign('return_element', $this->m_params[self::C__RETURN_ELEMENT]);
            }

            // Assign json encoded params.
            $this->template->assign('params', $this->m_params);

            // Call handlers.
            if (!$this->m_params[self::C__SELECTION] && $this->m_params['p_strSelectedID']) {
                // @todo Why do we pack this inside an array !?
                $this->m_params[self::C__SELECTION] = [$this->m_params['p_strSelectedID']];
            }

            // This code will preselect the objects, we selected since the last request (Open browser, select and close. Open browser again).
            if (isset($_GET['live_preselection'])) {
                if (!empty(isys_format_json::decode($_GET['live_preselection'], true))) {
                    $this->m_params[self::C__SELECTION] = [$_GET['live_preselection']];
                } else {
                    $this->m_params[self::C__SELECTION] = [];
                }
            }

            if (
                (strpos($this->m_params['name'], 'isys_cmdb_dao_category_s_person_assigned_groups__connected_object') !== false) &&
                is_array($this->m_params[self::C__DATARETRIEVAL]) &&
                empty($this->m_params[self::C__DATARETRIEVAL])
            ) {
                $this->m_params[self::C__DATARETRIEVAL] = null;
            }

            $this->handle_preselection($this->m_params[self::C__SELECTION], $this->m_params[self::C__DATARETRIEVAL]);

            $objectTypeFilter = [];
            $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]['disabled'] = true;

            if (isset($this->m_params[self::C__TYPE_FILTER]) && !empty($this->m_params[self::C__TYPE_FILTER])) {
                $objectTypeFilter = array_flip(explode(';', $this->m_params[self::C__TYPE_FILTER]));
            }

            if (is_array($objectTypeFilter) || !count($objectTypeFilter)) {
                $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]['disabled'] = false;
                $this->handle_location_tree();
            }

            // Preparations.
            $this->prepareConditionAssignments($objectTypeFilter);
        } catch (isys_exception_objectbrowser $e) {
            $this->template
                ->assign('error', $e->getMessage())
                ->assign('errorDetail', $e->getDetailMessage());
        } catch (Exception $e) {
            $this->template->assign('error', $e->getMessage());
        }

        // Javascript initialization.
        if (isset($this->m_params[self::C__SECOND_SELECTION]) && $this->m_params[self::C__SECOND_SELECTION]) {
            $l_gets = $p_modreq->get_gets();

            // Create the AJAX-string.
            $l_ajaxgets = [
                C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
                C__GET__MODULE_ID             => defined_or_default('C__MODULE__CMDB'),
                C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
                C__GET__AJAX_REQUEST          => 'handle_ajax_request',
                'request'                     => $this->m_params[self::C__SECOND_LIST],
            ];

            // Assign the Ajax URL for calling from the template.
            $this->template
                ->assign('ajax_url', isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets)))
                ->assign('js_init', 'popup/cable_connection_ng.js')
                ->assign(self::C__SECOND_SELECTION, true);
        } else {
            // Assign the object-browser JS.
            $this->template->assign('js_init', 'popup/object_ng.js');
        }

        $this->template
            ->assign('useAuth', (int)$this->m_params[self::C__USE_AUTH])
            ->display('popup/object_ng.tpl');
        die();
    }

    /**
     * Method for finding out which object types are allowed for the given parameters.
     *
     * @return array
     * @throws Exception
     */
    public function get_object_types_by_filter()
    {
        /*
         * The code below this comment is there for legacy reasons.
         */
        $l_dao = new isys_cmdb_dao_nexgen($this->database);

        $l_typeFilter = false;
        $objecTypes = [];

        // Check for type filtering.
        if (isset($this->m_params[self::C__TYPE_FILTER]) && !empty($this->m_params[self::C__TYPE_FILTER])) {
            $l_typeFilter = array_flip(explode(';', $this->m_params[self::C__TYPE_FILTER]));
        }

        if (is_array($l_typeFilter)) {
            foreach ($l_typeFilter as $l_objtype_const => $l_key) {
                $l_arr = $l_dao->get_objecttypes_using_cats($l_objtype_const);
                if ($l_arr) {
                    $l_typeFilter = array_merge($l_typeFilter, (array)array_flip($l_arr));
                }
            }
        }

        if (isset($this->m_params[self::C__CAT_FILTER]) && !empty($this->m_params[self::C__CAT_FILTER])) {
            $l_catFilter = explode(';', $this->m_params[self::C__CAT_FILTER]);
        } else {
            $l_catFilter = false;
        }

        $l_typeBlacklist = [];
        if (isset($this->m_params[self::C__TYPE_BLACK_LIST]) && !empty($this->m_params[self::C__TYPE_BLACK_LIST])) {
            $l_typeBlacklist = array_flip(explode(';', $this->m_params[self::C__TYPE_BLACK_LIST]));
        }

        // Defaults:
        $l_typeBlacklist['C__OBJTYPE__GENERIC_TEMPLATE'] = true;
        $l_typeBlacklist['C__OBJTYPE__NAGIOS_HOST_TPL'] = true;
        $l_typeBlacklist['C__OBJTYPE__NAGIOS_SERVICE_TPL'] = true;
        $l_typeBlacklist['C__OBJTYPE__LOCATION_GENERIC'] = true;
        $l_typeBlacklist['C__OBJTYPE__MIGRATION_OBJECT'] = true;

        // Get objecttype groups.
        $l_objgroups = $l_dao->objgroup_get();

        $displayCounter = (bool)isys_tenantsettings::get('cmdb.object-browser.display-object-type-counter', 1);
        $l_cmdb_dao = isys_cmdb_dao_object_type::instance($this->database);

        while ($l_row = $l_objgroups->get_row()) {
            // Get object types for current group.
            $l_objtypes = $l_dao->objtype_get_by_objgroup_id($l_row['isys_obj_type_group__id'], false, C__RECORD_STATUS__NORMAL, $displayCounter);
            while ($l_otrow = $l_objtypes->get_row()) {
                // Check if we can skip this object type.
                if (is_array($l_typeFilter) && !isset($l_typeFilter[$l_otrow['isys_obj_type__const']])) {
                    continue;
                }

                if ($l_catFilter && !$l_cmdb_dao->has_cat($l_otrow['isys_obj_type__id'], $l_catFilter)) {
                    continue;
                }

                if ($l_typeBlacklist && isset($l_typeBlacklist[$l_otrow['isys_obj_type__const']])) {
                    continue;
                }

                if ($displayCounter) {
                    $objecTypes[$this->language->get($l_row['isys_obj_type_group__title'])][$l_otrow['isys_obj_type__id']] = $this->language->get($l_otrow['isys_obj_type__title']) . ' (' . $l_otrow['objcount'] . ')';
                } else {
                    $objecTypes[$this->language->get($l_row['isys_obj_type_group__title'])][$l_otrow['isys_obj_type__id']] = $this->language->get($l_otrow['isys_obj_type__title']);
                }
            }
        }

        return $objecTypes;
    }

    /**
     * Method for getting the object retriever.
     *
     * @param string $condition
     *
     * @return \idoit\Component\Browser\ConditionInterface
     */
    protected function getObjectRetriever($condition = 'ObjectTypeCondition')
    {
        $dao = isys_cmdb_dao_object_type::instance($this->database);

        $conditionClass = $condition;

        if (!class_exists($conditionClass)) {
            $conditionClass = 'idoit\\Component\\Browser\\Condition\\' . $condition;
        }

        if (!class_exists($conditionClass)) {
            $conditionClass = ObjectTypeCondition::class;
        }

        /** @var \idoit\Component\Browser\ConditionInterface $conditionInstance */
        $conditionInstance = new $conditionClass($this->database);

        $filter = [];

        $cmdbFilter = array_filter(explode(';', $this->m_params[self::C__CMDB_FILTER]));
        $objectTypeFilter = array_filter(explode(';', $this->m_params[self::C__TYPE_FILTER]));
        $objectTypeExcludeFilter = array_filter(explode(';', $this->m_params[self::C__TYPE_BLACK_LIST]));
        $categoryFilter = array_filter(explode(';', $this->m_params[self::C__CAT_FILTER]));

        if (!empty($cmdbFilter)) {
            $filter['CmdbStatusFilter'] = $cmdbFilter;
        }

        if (!empty($objectTypeFilter)) {
            $filter['ObjectTypeFilter'] = $objectTypeFilter;
        }

        if (!empty($objectTypeExcludeFilter)) {
            $filter['ObjectTypeExcludeFilter'] = $objectTypeExcludeFilter;
        }

        if (!empty($categoryFilter)) {
            foreach ($categoryFilter as $categoryConstant) {
                $category = $dao->get_cat_by_const($categoryConstant);

                if ($category['type'] == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    if (!isset($filter['GlobalCategoryFilter'])) {
                        $filter['GlobalCategoryFilter'] = [];
                    }

                    $filter['GlobalCategoryFilter'][] = $category['id'];
                } elseif ($category['type'] == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                    if (!isset($filter['SpecificCategoryFilter'])) {
                        $filter['SpecificCategoryFilter'] = [];
                    }

                    $filter['SpecificCategoryFilter'][] = $category['id'];
                }
            }
        }

        // Blacklist some object types by default.
        if (!isset($filter['ObjectTypeExcludeFilter'])) {
            $filter['ObjectTypeExcludeFilter'] = [];
        }

        $filter['ObjectTypeExcludeFilter'][] = 'C__OBJTYPE__GENERIC_TEMPLATE';
        $filter['ObjectTypeExcludeFilter'][] = 'C__OBJTYPE__NAGIOS_HOST_TPL';
        $filter['ObjectTypeExcludeFilter'][] = 'C__OBJTYPE__NAGIOS_SERVICE_TPL';
        $filter['ObjectTypeExcludeFilter'][] = 'C__OBJTYPE__LOCATION_GENERIC';
        $filter['ObjectTypeExcludeFilter'][] = 'C__OBJTYPE__MIGRATION_OBJECT';

        $conditionInstance
            ->enableObjectCount((bool)isys_tenantsettings::get('cmdb.object-browser.display-object-type-counter', 1))
            ->registerFilterByArray($filter)
            ->setContextObjectId($this->contextObjectId);

        // @see  ID-#  We have to apply the auth filter separately here, because it works without the Browser controller.
        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $conditionInstance->registerFilter(new AuthFilter($this->database));
        }

        // Append custom filters (if available).
        if (is_array($this->m_params[self::C__CUSTOM_FILTERS]) && count($this->m_params[self::C__CUSTOM_FILTERS])) {
            foreach ($this->m_params[self::C__CUSTOM_FILTERS] as $customFilter => $customFilterParameter) {
                if (!class_exists($customFilter) || !is_a($customFilter, \idoit\Component\Browser\FilterInterface::class, true)) {
                    continue;
                }

                /** @var \idoit\Component\Browser\FilterInterface $customFilter */
                $conditionInstance->registerFilter((new $customFilter($this->database))->setParameter($customFilterParameter));
            }
        }

        return $conditionInstance;
    }

    /**
     * Method for adding a object type to the filter dropdown.
     *
     * @deprecated This is no longer supported
     * @return isys_popup_browser_object_ng
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_object_type_filter()
    {
        return $this;
    }

    /**
     * Handles the preselection and assigns a selection to smarty. A preselection is assigned in this format:
     *  [ object id, object title, object type, sys-id ]
     * Example:
     *  [ 1 , 'My Server', 'Server', 'SYSID1234567890' ]
     *
     * @param   array $p_preselection
     * @param   array|string $p_dataretrieval
     *
     * @throws  Exception
     * @throws  isys_exception_objectbrowser
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    protected function handle_preselection($p_preselection, $p_dataretrieval = null)
    {
        $preselection = $l_preselection = [];

        // If there is a certain callback defined via UI or template, use it.
        if (!empty($this->m_params[self::C__SECOND_LIST]) && $this->m_params[self::C__SECOND_SELECTION] && isset($this->m_params[self::C__SECOND_LIST], $this->m_params[self::C__SECOND_SELECTION])) {
            $l_preselection = ['first' => [], 'second' => [], 'category' => []];

            // If we get an array, it's most likely something like "array('class::method', array('params'))".
            if (is_array($this->m_params[self::C__SECOND_LIST])) {
                // Write the callback-method in the callback variable.
                $l_callback = $this->m_params[self::C__SECOND_LIST][0];

                // Check if we got a second item in the array (We'll assume they are parameters).
                $l_callback_params = isset($this->m_params[self::C__SECOND_LIST][1]) ? (array)$this->m_params[self::C__SECOND_LIST][1] : [];
            } elseif (is_string($this->m_params[self::C__SECOND_LIST])) {
                // When we only get a string, we can just write it inside the callback variable.
                $l_callback = $this->m_params[self::C__SECOND_LIST];

                // And assign an empty array as parameters.
                $l_callback_params = [];
            } else {
                throw new isys_exception_objectbrowser(
                    'Wrong parameter.',
                    'A wrong parameter has been assigned to isys_popup_browser_object_ng::C__SECOND_LIST. The syntax should be: array("class::method", array("params") or "class::method".'
                );
            }

            // Get an array with the class and method name separated.
            $l_callback = explode('::', $l_callback);

            if (is_array($p_preselection)) {
                $p_preselection = $p_preselection[0];
            }

            // Assign the preselection to our parameter-array.
            if (empty($l_callback_params['preselection'])) {
                $l_callback_params['preselection'] = $p_preselection;
            }

            // We won't blindly instancinate the class and call the method - we check.
            if (class_exists($l_callback[0])) {
                $l_obj = new $l_callback[0]($this->database);

                if (method_exists($l_obj, $l_callback[1])) {
                    // Call the callback-method with our parameters.
                    $l_preselection = $l_obj->{$l_callback[1]}(self::C__CALL_CONTEXT__PREPARATION, $l_callback_params);
                }
            }

            // @see ID-5686  As of right now we can use this "hacky" way of handling second selection data, but this needs to change.
            $preselection = array_map(function ($row) {
                // It is possible that the preselection only holds a single ID as integer instead of a array.
                return (is_array($row) ? $row[0] : $row);
            }, $l_preselection['second']);

            $parentPreselection = [];

            // @see  ID-6220  If we receive a 'first' selection, we'll process it accordingly.
            if (isset($l_preselection['first'])) {
                // Of course we'd like to only receive valid values, so we filter and make the array unique.
                $parentPreselection = array_unique(array_filter((array) $l_preselection['first']));
            }

            // Assign the preselection-variables.
            $this->template
                ->assign('preselectionCallback', implode('::', $l_callback))
                ->assign('parentPreselection', $parentPreselection)
                ->assign('preselection', array_values($preselection));
        } else {
            // Dirty hotfix for JSON inside an array (why?!) detection.
            if (is_array($p_preselection) && count($p_preselection) === 1) {
                if (is_string($p_preselection[0])) {
                    if (isys_format_json::is_json($p_preselection[0])) {
                        $p_preselection = isys_format_json::decode($p_preselection[0], true);
                    } elseif (strpos($p_preselection[0], ',') !== false) {
                        $p_preselection = explode(',', $p_preselection[0]);
                    }
                } elseif (is_array($p_preselection[0])) {
                    $p_preselection = $p_preselection[0];
                }
            }

            // If preselection should be retrieved via dataretrieval.
            if ($p_dataretrieval !== null) {
                // Feature - If we can't assign an array (because smarty can't handle arrays) we can use a JSON string instead.
                if (is_array($p_dataretrieval) && count($p_dataretrieval) > 1) {
                    // Try to retrieve the preselection via callback function.
                    list($l_callback, $l_parameter, $l_keys) = $p_dataretrieval;
                    list($className, $method) = $l_callback;
                } elseif (is_string($p_dataretrieval) && isys_format_json::is_json_array($p_dataretrieval)) {
                    list($l_callback, $l_parameter, $l_keys) = isys_format_json::decode($p_dataretrieval, true);

                    // We might already got an array or have string like "class::method".
                    list($className, $method) = is_array($l_callback) ? $l_callback : explode('::', $l_callback);
                } else {
                    throw new isys_exception_objectbrowser('Dataretrieval is empty', 'Dataretrieval variable needs information about a callback!');
                }

                if ($l_keys === null) {
                    $l_keys = [
                        'isys_obj__id',
                        'isys_obj__title',
                        'isys_obj_type__title',
                        'isys_obj__sysid'
                    ];
                }

                if (!class_exists($className)) {
                    throw new isys_exception_objectbrowser($className . ' does not exist.', 'The class "' . $className . '" could not be found. Maybe refresh the classmap?');
                }

                $class = new $className($this->database);

                if (!method_exists($class, $method)) {
                    throw new isys_exception_objectbrowser('Dataretrieval failed.', $method . ' does not exist in ' . $className);
                }

                $l_selection = $class->$method($l_parameter);

                if (!is_object($l_selection) || !is_a($l_selection, 'isys_component_dao_result')) {
                    throw new isys_exception_objectbrowser(
                        'Dataretrieval failed.',
                        $className . '->' . $method . ' does not return an object of type isys_component_dao_result.' . PHP_EOL . PHP_EOL .
                        'Return value is: ' . var_export($l_selection, true)
                    );
                }

                /** @var isys_component_dao_result $l_selection */
                while ($l_row = $l_selection->get_row(IDOIT_C__DAO_RESULT_TYPE_ALL)) {
                    if (isset($l_row[$l_keys[0]]) && $l_row[$l_keys[0]] > 0) {
                        $preselection[] = $l_row[$l_keys[0]];
                    } else {
                        $preselection[] = (int)$l_row[0];
                    }
                }
            } else {
                $preselection = $p_preselection;
            }

            // Ensure the preselection is a array.
            if (!is_array($preselection)) {
                $preselection = [$preselection];
            }

            // Clean the array.
            $preselection = array_unique(array_filter(array_map('intval', $preselection), function ($id) {
                return $id > 0;
            }));

            // Populate preselection in smarty object.
            $this->template
                ->assign('preselectionCallback', '')
                ->assign('preselection', array_values($preselection));
        }

        $defaultObjectTypeFilter = isys_tenantsettings::get('cmdb.object-browser.' . $this->m_params['name'] . '.defaultObjectType');

        if (!is_numeric($defaultObjectTypeFilter) && defined($defaultObjectTypeFilter)) {
            $defaultObjectTypeFilter = constant($defaultObjectTypeFilter);
        }

        $this->template
            ->assign('allowedToCreateGroups', isys_auth_cmdb_object_types::instance()->is_allowed_to(isys_auth::CREATE, 'OBJ_IN_TYPE/C__OBJECT_TYPE__GROUP'))
            ->assign('allObjectTypes', $this->getAllAllowedObjectTypes())
            ->assign('defaultObjectTypeFilter', $defaultObjectTypeFilter);
    }

    /**
     * Prepares all "Condition" assignments for the object browser.
     *
     * @param array $objectTypeFilter
     */
    protected function prepareConditionAssignments(array $objectTypeFilter)
    {
        $primaryConditions = [];
        $secondaryConditions = [];
        $customConditions = [];

        // Object type condition.
        if (!isset($this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] === false) {
            $objectTypeCondition = $this->getObjectRetriever();
            $primaryConditions[str_replace('\\', '.', get_class($objectTypeCondition))] = $objectTypeCondition;
        }

        if (!isset($this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] === false) {
            // Object group condition.
            $objectGroupCondition = $this->getObjectRetriever('ObjectGroupCondition');
            $primaryConditions[str_replace('\\', '.', get_class($objectGroupCondition))] = $objectGroupCondition;
        }

        if (!isset($this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] === false) {
            // Person group condition.
            $personGroupCondition = $this->getObjectRetriever('PersonGroupCondition');
            $primaryConditions[str_replace('\\', '.', get_class($personGroupCondition))] = $personGroupCondition;
        }

        // Report condition.
        if ((!isset($this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] === false) && class_exists('isys_module_report')) {
            $reportCondition = $this->getObjectRetriever('ReportCondition');
            $primaryConditions[str_replace('\\', '.', get_class($reportCondition))] = $reportCondition;
        }

        // Relation type condition.
        if (!$objectTypeFilter['C__OBJTYPE__RELATION'] && (!isset($this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] === false)) {
            $relationTypeCondition = $this->getObjectRetriever('RelationTypeCondition');
            $secondaryConditions[str_replace('\\', '.', get_class($relationTypeCondition))] = $relationTypeCondition;
        }

        // Location tab.
        if (empty($objectTypeFilter) && (!isset($this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] === false) && !$this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]['disabled']) {
            $secondaryConditions['locationView'] = 'LC__CMDB__OBJECT_BROWSER__LOCATION_VIEW';
        }

        // Date condition.
        if (!isset($this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] === false) {
            $dateCondition = $this->getObjectRetriever('DateCondition');
            $secondaryConditions[str_replace('\\', '.', get_class($dateCondition))] = $dateCondition;
        }

        // Search condition.
        if (!isset($this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS]) || $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] === false) {
            $dateCondition = $this->getObjectRetriever('SearchCondition');
            $secondaryConditions[str_replace('\\', '.', get_class($dateCondition))] = $dateCondition;
        }

        // Custom conditions.
        if (isset($this->m_params[self::C__CUSTOM_CONDITIONS]) && is_array($this->m_params[self::C__CUSTOM_CONDITIONS]) && (!isset($this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS]) || $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS] === false)) {
            foreach ($this->m_params[self::C__CUSTOM_CONDITIONS] as $conditionClassName) {
                if (! class_exists($conditionClassName) || !is_a($conditionClassName, \idoit\Component\Browser\ConditionInterface::class, true)) {
                    continue;
                }

                $customCondition = $this->getObjectRetriever($conditionClassName);
                $customConditions[str_replace('\\', '.', get_class($customCondition))] = $customCondition;
            }
        }

        $this->template
            ->assign('primaryConditions', $primaryConditions)
            ->assign('secondaryConditions', $secondaryConditions)
            ->assign('customConditions', $customConditions);
    }

    /**
     * Helper method for always receiving a "unified" object browser name.
     *
     * @param string $name
     *
     * @return string
     * @see    ID-6927
     */
    public static function uniformBrowserName($name)
    {
        // Convert special strings like "isys_cmdb_dao_category_g_assigned_workstation__parent__VIEW[123-321]" (used in multiedit).
        if (strpos($name, '[') !== false) {
            $name = strstr($name, '[', true);
        }

        // Convert custom category property identifier to their constant (used in multiedit).
        if (strpos($name, 'isys_cmdb_dao_category_g_custom_fields__f_popup_') === 0) {
            return str_replace('isys_cmdb_dao_category_g_custom_fields__f_popup_', 'C__CATG__CUSTOM__', $name);
        }

        // If we don't have a proper constant, we'll assume we have a string like "<dao-class-name>__<property-name>" (used in multiedit).
        if (strpos($name, 'C__') !== 0) {
            list($categoryClass, $propertyName) = explode('__', $name);

            if (class_exists($categoryClass) && is_a($categoryClass, isys_cmdb_dao_category::class, true)) {
                $properties = $categoryClass::instance(isys_application::instance()->container->get('database'))->get_properties();

                // If the property exists with a UI ID, we simply return that.
                if (isset($properties[$propertyName][C__PROPERTY__UI][C__PROPERTY__UI__ID])) {
                    return $properties[$propertyName][C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }
            }
        }

        return $name;
    }

    /**
     * Intializes the location tree and assigns it.
     */
    private function handle_location_tree()
    {
        $imageDirectory = isys_application::instance()->www_path . 'images/';

        // Prepare tree.
        $l_tree = new isys_component_ajaxtree(
            'locationBrowser',
            'index.php?call=tree_level&ajax=1&id={0}&selectCallback=browserPreselection.select&containersOnly=0',
            $imageDirectory . 'icons/silk/house.png',
            $this->language->get('LC__CMDB__TREE__LOCATION'),
            ''
        );

        // Assign the template variables.
        $this->template
            ->assign('ajaxUrl', 'index.php?call=tree_level&ajax=1&get_obj_name=1&id=')
            ->assign('locationBrowser', $l_tree->process());
    }

    /**
     * This method will return all object types, the user is allowed to create new objects in.
     *
     * @return array
     * @throws isys_exception_database
     */
    protected function getAllAllowedObjectTypes(): array
    {
        $objectTypes = [];

        // Get all object types the user is allowed to "create" in.
        $allowedObjecTypes = isys_auth_cmdb_object_types::instance()->get_allowed_objecttypes(isys_auth::CREATE);

        // Clean the array a little.
        $allowedObjecTypes = array_unique(array_values($allowedObjecTypes));

        $dao = isys_cmdb_dao::instance($this->database);

        $sql = 'SELECT isys_obj_type__id AS id, isys_obj_type__title AS title 
            FROM isys_obj_type 
            WHERE isys_obj_type__id ' . $dao->prepare_in_condition($allowedObjecTypes) . '
            AND isys_obj_type__const NOT IN (\'C__OBJTYPE__CONTAINER\', \'C__OBJTYPE__LOCATION_GENERIC\');';

        if (!isys_application::instance()->container->get('settingsTenant')->get('auth.active', 0)) {
            $sql = 'SELECT isys_obj_type__id AS id, isys_obj_type__title AS title 
            FROM isys_obj_type 
            WHERE isys_obj_type__const NOT IN (\'C__OBJTYPE__CONTAINER\', \'C__OBJTYPE__LOCATION_GENERIC\');';
        }

        $result = $dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $objectTypes[$row['id']] = $this->language->get($row['title']);
        }

        return $objectTypes;
    }

    /**
     * isys_popup_browser_object_ng constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->contextObjectId = isys_glob_get_param(C__CMDB__GET__OBJECT);
    }
}
