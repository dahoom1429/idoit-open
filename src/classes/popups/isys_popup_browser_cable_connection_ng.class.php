<?php

/**
 * i-doit
 *
 * Cable-connection Browser
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Leonard Fischer <lfischer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_cable_connection_ng extends isys_popup_browser_object_ng
{
    /**
     * Name of the parameter, which inherits the second-selection ID.
     */
    const C__SECOND_SELECTION_ID = 'second_selection_id';

    const C__ONLY_LOGICAL_PORTS = 'only_log_ports';

    private $m_only_logical_ports = false;

    /**
     * Returns a formatted string for the selected connection.
     *
     * @param   integer $p_connector_list_id
     * @param   boolean $p_plain
     *
     * @return  string
     */
    public function format_selection($p_connector_list_id, $p_plain = false)
    {
        if (empty($p_connector_list_id)) {
            return '-';
        }

        $l_quick_info = new isys_ajax_handler_quick_info();

        // If we connect logical ports, we don't need the rest.
        if ($this->m_only_logical_ports === true) {
            $l_row = isys_cmdb_dao_category_g_network_ifacel::instance($this->database)
                ->get_data($p_connector_list_id)
                ->get_row();

            $l_title = $l_row['isys_obj__title'] . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_row['isys_catg_log_port_list__title'];

            if ($p_plain || !$this->m_format_quick_info) {
                return $l_title;
            }

            return $l_quick_info->get_quick_info($l_row['isys_obj__id'], $l_title, C__LINK__CATG, false, [
                C__CMDB__GET__CATG     => defined_or_default('C__CATG__NETWORK_LOG_PORT'),
                C__CMDB__GET__CATLEVEL => $p_connector_list_id,
            ]);
        }

        $l_dao = new isys_cmdb_dao_category_g_connector($this->database);

        if ($p_connector_list_id === null) {
            $p_connector_list_id = -1;
        }

        $l_data = $l_dao->get_data($p_connector_list_id)
            ->__to_array();

        $l_title = $l_data['isys_obj__title'] . isys_tenantsettings::get('gui.separator.connector', ' > ') . $l_data['isys_catg_connector_list__title'];

        if ($p_plain || !$this->m_format_quick_info) {
            return $l_title;
        }

        return $l_quick_info->get_quick_info($l_data['isys_obj__id'], $l_title, C__LINK__CATG, false, [
            C__CMDB__GET__CATG     => defined_or_default('C__CATG__CONNECTOR'),
            C__CMDB__GET__CATLEVEL => $p_connector_list_id,
        ]);
    }

    /**
     * Handle ajax request.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return string
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_ajax_request(isys_module_request $p_modreq)
    {
        $l_dao_distributor = new isys_cmdb_dao_distributor($this->database, $_GET[C__CMDB__GET__OBJECT], C__CMDB__CATEGORY__TYPE_GLOBAL, null, [defined_or_default('C__CATG__CONNECTOR') => true]);

        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $l_guidata = $l_dao_distributor->get_guidata(defined_or_default('C__CATG__CONNECTOR'));
        $l_cat = $l_dao_distributor->get_category(defined_or_default('C__CATG__CONNECTOR'));

        if (is_object($l_cat)) {
            if ($_GET[self::C__ONLY_LOGICAL_PORTS]) {
                $l_logport_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->database);
                $l_logport_res = $l_logport_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, '', C__RECORD_STATUS__NORMAL);

                $l_json = [];

                if ($l_logport_res->num_rows() > 0) {
                    while ($l_logport_row = $l_logport_res->get_row()) {
                        $l_connected_to = $emptyValue;

                        if ($l_logport_row['isys_catg_log_port_list__isys_catg_log_port_list__id'] !== null) {
                            $l_row = $l_logport_dao->get_data($l_logport_row['isys_catg_log_port_list__isys_catg_log_port_list__id'])->get_row();
                            $l_connected_to = $l_row['isys_obj__title'] . isys_tenantsettings::get('gui.separator.connector', ' > ') .
                                $l_row['isys_catg_log_port_list__title'];
                        }

                        $l_json[] = [
                            '__checkbox__'                                            => $l_logport_row['isys_catg_log_port_list__id'],
                            $this->language->get('LC__CMDB__CATG__CONNECTORS')        => $l_logport_row['isys_catg_log_port_list__title'],
                            $this->language->get('LC__CMDB__CATG__UI_ASSIGNED_UI')    => $l_connected_to,
                            $this->language->get('LC__CMDB__CATG__INTERFACE_L__TYPE') => $l_logport_row['isys_netx_ifacel_type__title']
                        ];
                    }

                    // Set header-information.
                    header('Content-type: application/json');

                    return isys_format_json::encode($l_json);
                }
            } else {
                $l_json = [];
                $l_data = $l_cat->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, '', C__RECORD_STATUS__NORMAL);
                $l_dao_connection = new isys_cmdb_dao_cable_connection($this->database);

                if ($l_data->num_rows() > 0) {
                    while ($l_row = $l_data->get_row()) {
                        $l_connected_to = $emptyValue;
                        $l_category = $l_cat->get_assigned_category_title($l_row['isys_catg_connector_list__assigned_category']);
                        $l_connector_data = $l_dao_connection
                            ->get_assigned_connector($l_row['isys_catg_connector_list__id'])
                            ->get_row();

                        if ($l_connector_data['isys_catg_connector_list__title'] !== null && $l_connector_data['isys_catg_connector_list__isys_obj__id'] !== null) {
                            $l_connected_to = $l_dao_connection->get_obj_name_by_id_as_string($l_connector_data['isys_catg_connector_list__isys_obj__id']) .
                                isys_tenantsettings::get('gui.separator.connector', ' > ') .
                                $l_connector_data['isys_catg_connector_list__title'];
                        }

                        // Set in- or output.
                        $l_inout = $this->language->get('LC__CATG__CONNECTOR__OUTPUT');
                        if ($l_row['isys_catg_connector_list__type'] == C__CONNECTOR__INPUT) {
                            $l_inout = $this->language->get('LC__CATG__CONNECTOR__INPUT');
                        }

                        $l_json[] = [
                            '__checkbox__'                                         => $l_row[$l_guidata['isysgui_catg__source_table'] . '_list__id'],
                            $this->language->get('LC__CMDB__CATG__CONNECTORS')     => $l_row[$l_guidata['isysgui_catg__source_table'] . '_list__title'],
                            $this->language->get('LC__CMDB__CATG__UI_ASSIGNED_UI') => $l_connected_to,
                            $this->language->get('LC__CMDB__CATG__CATEGORY')       => $l_category,
                            $this->language->get('LC__CMDB__CATS__PRT_TYPE')       => $l_inout
                        ];
                    }

                    // Set header-information.
                    header('Content-type: application/json');

                    return isys_format_json::encode($l_json);
                }
            }
        } else {
            $l_json = [];
        }

        header('Content-type: application/json');

        return isys_format_json::encode($l_json);
    }

    /**
     * Handle the popup request.
     *
     * @param   isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws  Exception
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $this->m_params = isys_format_json::decode(base64_decode($_POST['params']));

        if (!is_array($this->m_params)) {
            throw new Exception('Parameter error.');
        }

        // Unpack module request.
        $l_gets = $p_modreq->get_gets();

        $this->m_tabconfig[self::C__OBJECT_BROWSER__TAB__LOCATION]['disabled'] = $this->m_params['secondSelection'];

        // Create Ajax URL.
        $l_ajaxgets = [
            C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
            C__GET__MODULE_ID             => defined_or_default('C__MODULE__CMDB'),
            C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
            C__GET__AJAX_REQUEST          => 'handle_ajax_request',
            self::C__ONLY_LOGICAL_PORTS   => $this->m_params[self::C__ONLY_LOGICAL_PORTS]
        ];

        $this->m_only_logical_ports = $this->m_params[self::C__ONLY_LOGICAL_PORTS];

        $globalCategoryFilter = array_filter(isys_string::split($this->m_params[self::C__CAT_FILTER]), function ($categoryConst) {
            return strpos($categoryConst, 'CATG') !== false;
        });

        $specificCategoryFilter = array_filter(isys_string::split($this->m_params[self::C__CAT_FILTER]), function ($categoryConst) {
            return strpos($categoryConst, 'CATS') !== false;
        });

        // @see ID-6060 Sort by the configured field.
        $defaultSortingField = isys_tenantsettings::get('cmdb.object-browser.' . substr($this->m_params['hidden'], 0, -8) . '.defaultSortingFieldIndex', null);
        $defaultSortingDirection = isys_tenantsettings::get('cmdb.object-browser.' . substr($this->m_params['hidden'], 0, -8) . '.defaultSortingDirection', 'asc');

        // Assign the Ajax URL for calling from the template.
        $this->template
            ->assign('objectBrowserName', $this->m_params['name'])
            ->assign('ajax_url', isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets)))// Assign our object-types to the template.
            ->assign('return_element', $this->m_params['hidden'])
            ->assign('return_view', $this->m_params['view'])
            ->assign('return_cable_name', $this->m_params['cable_name'])
            ->assign(self::C__TYPE_FILTER, $this->m_params[self::C__TYPE_FILTER])
            ->assign(self::C__CMDB_FILTER, $this->m_params[self::C__CMDB_FILTER])
            ->assign(self::C__CALLBACK__ACCEPT, $this->m_params[self::C__CALLBACK__ACCEPT])
            ->assign(self::C__CALLBACK__ABORT, $this->m_params[self::C__CALLBACK__ABORT])
            ->assign(self::C__CALLBACK__DETACH, $this->m_params[self::C__CALLBACK__DETACH])
            ->assign('usageWarning', $this->m_params['usageWarning'])
            ->assign('allowedToCreateGroups', isys_auth_cmdb_object_types::instance()->is_allowed_to(isys_auth::CREATE, 'OBJ_IN_TYPE/C__OBJECT_TYPE__GROUP'))
            ->assign('allObjectTypes', $this->getAllAllowedObjectTypes())
            ->assign('js_init', 'popup/cable_connection_ng.js')
            ->assign('specificCategoryFilter', implode(',', $specificCategoryFilter))
            ->assign('globalCategoryFilter', implode(',', $globalCategoryFilter))
            ->assign('defaultSortingField', isys_format_json::encode($defaultSortingField))
            ->assign('defaultSortingDirection', $defaultSortingDirection);

        // Set a nice browser-name
        if (!isset($this->m_params[self::C__TITLE])) {
            $this->template->assign('browser_title', $this->language->get('LC__POPUP__BROWSER__OBJECT_BROWSER'));
        } else {
            $this->template->assign('browser_title', $this->language->get($this->m_params[self::C__TITLE]));
        }

        // This code will preselect the objects, we selected since the last request (Open browser, select and close. Open browser again).
        if (isset($_GET['live_preselection'])) {
            if ($_GET['live_preselection'] > 0) {
                $this->m_params[self::C__SECOND_SELECTION_ID] = $_GET['live_preselection'];
            } else {
                $this->m_params[self::C__SECOND_SELECTION_ID] = null;
            }
        }

        // Handle the preselection.
        $this->handle_preselection($this->m_params[self::C__SECOND_SELECTION_ID]);

        $objectTypeFilter = [];

        if (isset($this->m_params[self::C__TYPE_FILTER]) && !empty($this->m_params[self::C__TYPE_FILTER])) {
            $objectTypeFilter = array_flip(explode(';', $this->m_params[self::C__TYPE_FILTER]));
        }

        $this->prepareConditionAssignments($objectTypeFilter);

        // Enable second selection.
        $this->template->assign('secondSelection', (isset($this->m_params[self::C__SECOND_SELECTION]) && $this->m_params[self::C__SECOND_SELECTION] == true));

        // Show popup content and die.
        $this->template->display('popup/object_ng.tpl');

        die();
    }

    /**
     * Handles the preselection and assigns these to smarty.
     *
     * @param mixed $preselection "$l_port_preselection"
     * @param null  $unused
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function handle_preselection($preselection, $unused = null)
    {
        if (!is_array($preselection)) {
            if (is_numeric($preselection)) {
                $preselection = [$preselection];
            } else {
                $preselection = [];
            }
        }

        // @see  ID-7114  Instead of calling `parent::handle_preselection()` we do the logic here, since there'll surely be a reason why we don't call the parent method.
        $defaultObjectTypeFilter = isys_tenantsettings::get('cmdb.object-browser.' . $this->m_params['name'] . '.defaultObjectType');

        if (!is_numeric($defaultObjectTypeFilter) && defined($defaultObjectTypeFilter)) {
            $defaultObjectTypeFilter = constant($defaultObjectTypeFilter);
        }

        $this->template
            ->assign('preselection', array_values(array_filter($preselection)))
            ->assign('preselectionCallback', $this->m_params[self::C__SECOND_LIST])
            ->assign('defaultObjectTypeFilter', $defaultObjectTypeFilter);
    }

    /**
     * Handle the smarty include for displaying the form-element.
     *
     * @param  isys_component_template $template
     * @param  array                   $parameters
     *
     * @return string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$template, $parameters)
    {
        $this->m_params = $parameters;

        $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS];

        if (empty($this->m_params['name'])) {
            return '';
        }

        $l_strOut = '';

        if (strpos($this->m_params['name'], '[') !== false && strpos($this->m_params['name'], ']') !== false) {
            $l_tmp = explode('[', $this->m_params['name']);
            $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));

            $l_attr = [
                'hidden'     => $l_hidden,
                'cable_name' => $this->m_params['name'] . '__CABLE_NAME',
                'view'       => $l_view,
            ];

            unset($l_tmp);
        } else {
            $l_attr = [
                'hidden'     => $this->m_params['name'] . '__HIDDEN',
                'cable_name' => $this->m_params['name'] . '__CABLE_NAME',
                'view'       => $this->m_params['name'] . '__VIEW',
            ];
        }

        // f_text parameters + parameters.
        $l_objPlugin = new isys_smarty_plugin_f_text();
        $this->m_params['id'] = $l_attr['view'];
        $this->m_params[C__CMDB__GET__OBJECT] = $_GET[C__CMDB__GET__OBJECT];
        $this->m_params['hidden'] = $l_attr['hidden'];
        $this->m_params['view'] = $l_attr['view'];
        $this->m_params['cable_name'] = $l_attr['cable_name'];
        $this->m_params['p_bReadonly'] = true;

        if ($this->m_params['p_strValue']) {
            $l_port_id = $this->m_params['p_strValue'];
        } elseif ($this->m_params['p_strSelectedID']) {
            $l_port_id = $this->m_params['p_strSelectedID'];
        } else {
            $l_port_id = null;
        }

        if (strpos($l_port_id, '"') !== false) {
            $l_port_id = (int)isys_format_json::decode($l_port_id);
        }

        if ($l_port_id == 'null') {
            $l_port_id = 0;
        }

        $l_obj_id = 0;
        if (isset($this->m_params['p_objValue']) && is_numeric($this->m_params['p_objValue']) && $this->m_params['p_objValue'] > 0) {
            $l_obj_id = $this->m_params['p_objValue'];
        } elseif (is_numeric($l_port_id) && $l_port_id > 0) {
            $l_dao_connector = new isys_cmdb_dao_category_g_connector($this->database);
            $l_data = $l_dao_connector->get_data($l_port_id)
                ->get_row();
            $l_obj_id = $l_data['isys_catg_connector_list__isys_obj__id'];
        }

        $this->m_params[self::C__SELECTION] = $l_obj_id;
        $this->m_params[self::C__SECOND_SELECTION_ID] = $l_port_id;

        $l_strHiddenField = '<input id="' . $l_attr['hidden'] . '" name="' . $l_attr['hidden'] . '" class="' . $this->m_params['hidden_class'] . '" type="hidden" value="' .
            $l_port_id . '" />' . '<input id="' . $l_attr['cable_name'] . '" name="' . $l_attr['cable_name'] . '" type="hidden" value="" />';

        $l_detach_callback = isset($this->m_params[self::C__CALLBACK__DETACH]) ? $this->m_params[self::C__CALLBACK__DETACH] : "";

        $l_onclick_detach = "var e_view = $('" . $l_attr['view'] . "'), " . "e_hidden = $('" . $l_attr['hidden'] . "');" .
            "if(e_view && e_hidden) {e_view.value = '" . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "!'; e_hidden.value = '';}" .
            $l_detach_callback;

        $this->m_only_logical_ports = $this->m_params[self::C__ONLY_LOGICAL_PORTS];

        if (isys_glob_is_edit_mode() || $this->m_params[self::C__EDIT_MODE]) {
            $this->m_params["p_strValue"] = $this->format_selection($l_port_id, true);
            $this->m_params['disableInputGroup'] = true;

            // Textfield.
            $l_strOut .= $l_objPlugin->navigation_edit($this->template, $this->m_params);

            // Opener.
            $l_strOut .= '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' .
                $this->process_overlay("live_preselection=' + \$F('" . $l_attr['hidden'] . "') + '", '80%', '90%', $this->m_params, null, 1000, 300, 1600, 800) . ';" >' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="Open the browser" />' . '</a>';

            // Detacher.
            return $l_strOut . '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' .
                $l_onclick_detach . ';" >' . '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/detach.png" alt="Detach" />' . '</a>' . $l_strHiddenField;
        }

        $this->m_params['p_bHtmlDecode'] = true;
        $this->m_params['p_strValue'] = $this->format_selection($l_port_id);

        return $l_strOut . $l_objPlugin->navigation_view($this->template, $this->m_params) . $l_strHiddenField;
    }
}
