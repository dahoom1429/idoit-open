<?php

/**
 * i-doit
 *
 * Global category connector
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_connector extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for global category Version.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  null
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs;

        // Are we in create mode?
        if ($this->isCreate()) {
            // Prevent double save attempts
            $this->redirectAfterEntrySaved($_GET[C__CMDB__GET__OBJECT]);
        }

        $language = isys_application::instance()->container->get('language');
        $l_catdata = $p_cat->get_general_data();
        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Is this a new record?
        $l_new = (!isset($l_catdata['isys_catg_connector_list__id']) || $l_catdata['isys_catg_connector_list__id'] === null);

        $cateID = 0;

        if (isset($l_catdata['isys_catg_connector_list__id'])) {
            $cateID = (int)$l_catdata['isys_catg_connector_list__id'];
        }

        $this->get_template_component()
            ->assign('cateID', $cateID);

        // Assign ID values.
        $l_rules['C__CATG__CONNECTOR__CONNECTED_NET']['p_strSelectedID'] = $l_catdata['isys_connection__isys_obj__id'];
        $l_rules['C__CATG__CONNECTOR__CONNECTION_TYPE']['p_strSelectedID'] = $l_catdata['isys_connection_type__id'];
        $l_rules['C__CATG__CONNECTOR__CONNECTION_TYPE']['p_bInfoIconSpacer'] = 0;
        $l_rules['C__CATG__CONNECTOR__INTERFACE']['p_strSelectedID'] = $l_catdata['isys_interface__id'];
        $l_rules['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX']['p_strSelectedID'] = $l_catdata['isys_catg_connector_list__used_fiber_lead_rx'];
        $l_rules['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX']['p_strSelectedID'] = $l_catdata['isys_catg_connector_list__used_fiber_lead_tx'];
        $l_rules['C__CATP__CONNECTOR__FIBER_WAVE_LENGTHS']['p_strSelectedID'] = $l_catdata['isys_catg_connector_list__fiber_wave_lengths__id'];

        // Resolve corresponding category.
        $l_cat_name = $p_cat->get_assigned_category_title($l_catdata['isys_catg_connector_list__assigned_category']);

        $this->get_template_component()
            ->assign('new', $l_new)
            ->assign('C__CATG__CONNECTOR__CATEGORY_TYPE', $l_cat_name);

        // Assign connected connector data.
        $l_dao = new isys_cmdb_dao_cable_connection($p_cat->get_database_component());

        $l_connectorID = $l_dao->get_assigned_connector_id($l_catdata['isys_catg_connector_list__id']);
        $l_cableID = $l_dao->get_assigned_cable($l_catdata['isys_catg_connector_list__id']);

        // Assign the connection. Set "p_strSelectedID" to null because it gets set by the generic category handling.
        $l_rules['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR']['p_strSelectedID'] = null;
        $l_rules['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR']['p_objValue'] = $l_dao->get_assigned_object($l_catdata['isys_cable_connection__id'], $l_connectorID);
        $l_rules['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR']['p_strValue'] = $l_connectorID;
        $l_rules['C__CATG__CONNECTOR__ASSIGNED_CONNECTOR']['p_cableID'] = $l_cableID;

        // Cable id.
        $l_rules['C__CATG__CONNECTOR__CABLE']['p_strValue'] = $l_catdata['isys_cable_connection__isys_obj__id'];

        // Assign siblings.
        switch ($l_catdata['isys_catg_connector_list__type']) {
            case C__CONNECTOR__OUTPUT:
                $l_rules['C__CATG__CONNECTOR__SIBLING_IN']['p_strSelectedID'] = $l_catdata['isys_catg_connector_list__isys_catg_connector_list__id'];
                $this->get_template_component()
                    ->assign("out", true);
                break;

            case C__CONNECTOR__INPUT:
            default:
                $l_rules['C__CATG__CONNECTOR__SIBLING_OUT']['p_strSelectedID'] = $l_catdata['isys_catg_connector_list__isys_catg_connector_list__id'];
                $this->get_template_component()
                    ->assign("in", true);
                break;
        }

        $l_context[C__CONNECTOR__INPUT] = $language->get('LC__UNIVERSAL__ALREADY_CONNECTED');
        $l_context[C__CONNECTOR__OUTPUT] = $language->get('LC__CMDB__CATG__UI_ASSIGNED_UI') . ' %s';

        // Iterate through connector types to get possible sibling connectors
        foreach ([C__CONNECTOR__INPUT, C__CONNECTOR__OUTPUT] as $l_index) {
            $l_data = $p_cat->get_data(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                ' AND isys_catg_connector_list.isys_catg_connector_list__type = ' . $l_index,
                null,
                $_SESSION['cRecStatusListView']
            );

            while ($l_row = $l_data->get_row()) {
                $l_sibling[$l_index][$l_row['isys_catg_connector_list__id']] = $l_row['isys_catg_connector_list__title'];

                $l_connection = $language->get('LC__CMDB__CATG__UI_ASSIGNED_UI') . ' ' . $p_cat->get_connector_name_by_id($l_row['isys_catg_connector_list__isys_catg_connector_list__id']);

                if ($l_row['isys_catg_connector_list__isys_catg_connector_list__id'] > 0 && $l_row['isys_catg_connector_list__isys_catg_connector_list__id'] != $_GET[C__CMDB__GET__CATLEVEL]) {
                    $l_sibling[$l_index][$l_row['isys_catg_connector_list__id']] .= ' (' . sprintf($l_context[$l_index], $l_connection) . ')';
                }
            }
        }

        if (isset($l_sibling)) {
            $l_rules['C__CATG__CONNECTOR__SIBLING_IN']['p_arData'] = $l_sibling[C__CONNECTOR__INPUT];
            $l_rules['C__CATG__CONNECTOR__SIBLING_OUT']['p_arData'] = $l_sibling[C__CONNECTOR__OUTPUT];
        }

        // Static text.
        $l_rules['C__UNIVERSAL__TITLE']['p_strValue'] = $l_catdata['isys_catg_connector_list__title'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_catg_connector_list__description'];

        if (empty($l_catdata['isys_catg_connector_list__type'])) {
            $l_type = C__CONNECTOR__INPUT;
        } else {
            $l_type = $l_catdata['isys_catg_connector_list__type'];
        }

        $l_rules['C__CATG__CONNECTOR__INOUT']['p_strSelectedID'] = $l_type;
        $l_rules['C__CATG__CONNECTOR__INOUT']['p_arData'] = $p_cat->callback_property_type(isys_request::factory());

        if ($l_new) {
            // Name schema.
            $l_schema_config = isys_settings::get('cmdb.connector.suffix-schema', isys_format_json::encode([
                '##INPUT## - OUT',
                '- ##INPUT##',
                '(*) ##INPUT##',
            ]));

            // migration did an error value is 'null' as string
            if ($l_schema_config === 'null') {
                $l_schema_config = [
                    '##INPUT## - OUT',
                    '- ##INPUT##',
                    '(*) ##INPUT##',
                ];

                isys_settings::set('cmdb.connector.suffix-schema', isys_format_json::encode($l_schema_config));
            } elseif (is_string($l_schema_config)) {
                $l_schema_config = isys_format_json::decode($l_schema_config, true);
            }

            $l_schema_config['-1'] = $language->get('LC__UNIVERSAL__OWN') . ';';

            $l_rules['C__CATG__CONNECTOR__SUFFIX_SCHEMA']['p_arData'] = $l_schema_config;
            $l_rules['C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN']['p_strValue'] = min($l_schema_config);
        }

        if (!$p_cat->get_validation()) {
            $l_rules['C__CATG__CONNECTOR__CONNECTED_NET']['p_strSelectedID'] = $_POST['C__CATG__CONNECTOR__CONNETED'];
            $l_rules['C__CATG__CONNECTOR__CONNECTION_TYPE']['p_strSelectedID'] = $_POST['C__CATG__CONNECTOR__CONNECTION_TYPE'];
            $l_rules['C__CATG__CONNECTOR__INTERFACE']['p_strSelectedID'] = $_POST['C__CATG__CONNECTOR__INTERFACE'];
            $l_rules['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX']['p_strSelectedID'] = $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_RX'];
            $l_rules['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX']['p_strSelectedID'] = $_POST['C__CATG__CONNECTOR__USED_FIBER_LEAD_TX'];
            $l_rules['C__UNIVERSAL__TITLE']['p_strValue'] = $_POST['C__CATG__CONNECTOR__TITLE'];
            $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $_POST['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        if ($l_catdata['isys_catg_connector_list__assigned_category'] === 'C__CATG__UNIVERSAL_INTERFACE') {
            // Because we can't just "merge" all the dialog-tables, we dont display thie connection type but set it on our own.
            $l_rules['C__CATG__CONNECTOR__CONNECTION_TYPE']['p_bInvisible'] = 1;

            $l_ui_dao = new isys_cmdb_dao_category_g_ui($this->get_database_component());
            $l_ui_row = $l_ui_dao->get_data(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                'AND isys_catg_ui_list__isys_catg_connector_list__id = ' . $l_ui_dao->convert_sql_id($l_catdata['isys_catg_connector_list__id'])
            )
                ->get_row();

            $this->get_template_component()
                ->assign('connection_type', $language->get($l_ui_row['isys_ui_plugtype__title']));
        }

        // Handle fiber wave lengths:
        $l_assigned_fiber_wave_lengths = $p_cat->get_assigned_fiber_wave_lengths(null, $l_catdata['isys_catg_connector_list__id']);
        $l_wavelength = [];

        if ($l_assigned_fiber_wave_lengths) {
            while ($l_row_fiber_wave_lengths = $l_assigned_fiber_wave_lengths->get_row()) {
                $l_wavelength[] = (int)$l_row_fiber_wave_lengths['isys_fiber_wave_length__id'];
            }
        }

        $l_rules['C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS'] = [
            'p_strTable'      => 'isys_fiber_wave_length',
            'placeholder'     => $language->get('LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS'),
            'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-catg-connector-wavelength-update', selected);",
            'p_strSelectedID' => implode(',', $l_wavelength),
            'multiselect'     => true
        ];

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /**
     * Process the list.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param boolean                $p_bCheckbox
     * @param boolean                $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return null|void
     * @throws Exception
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $l_filter = isys_glob_get_param('filter');
        $l_object_id = $p_cat->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT];

        $l_get = $_GET;
        unset($l_get['ajax'], $l_get['call']);

        $l_inputs = $p_cat->get_data_by_type(
            $l_object_id,
            C__CONNECTOR__INPUT,
            $_SESSION['cRecStatusListView'],
            $l_filter,
            '',
            isys_glob_get_param('sort'),
            isys_glob_get_param('dir')
        );

        $l_outputs = $p_cat->get_data_by_type(
            $l_object_id,
            C__CONNECTOR__OUTPUT,
            $_SESSION['cRecStatusListView'],
            $l_filter,
            ' AND ISNULL(isys_catg_connector_list.isys_catg_connector_list__isys_catg_connector_list__id)',
            isys_glob_get_param('sort'),
            isys_glob_get_param('dir')
        );

        $l_dao_list = isys_cmdb_dao_list_catg_connector::build($p_cat->get_database_component(), $p_cat);

        $l_arData = [
            C__RECORD_STATUS__NORMAL   => 'LC__CMDB__RECORD_STATUS__NORMAL',
            C__RECORD_STATUS__ARCHIVED => 'LC__CMDB__RECORD_STATUS__ARCHIVED',
            C__RECORD_STATUS__DELETED  => 'LC__CMDB__RECORD_STATUS__DELETED'
        ];

        if (defined('C__TEMPLATE__STATUS') && C__TEMPLATE__STATUS === 1) {
            $l_arData[C__RECORD_STATUS__TEMPLATE] = 'Template';
        }

        if (defined('C__MODULE__PRO') && C__MODULE__PRO && class_exists('isys_popup_connection')) {
            $l_js_onclick = (new isys_popup_connection())->process_overlay('', '80%', '90%', [C__CMDB__GET__OBJECT => $l_object_id], 'popup_commentary', '770px', '350px');

            try {
                isys_auth_cmdb_categories::instance()->check_rights_obj_and_category(isys_auth::EDIT, $l_object_id, 'C__CATG__CONNECTOR');
                
                $connectionPopup = true;
            } catch (Exception $e) {
                $connectionPopup = false;
            }

            isys_component_template_navbar::getInstance()
                ->append_button('LC__CABLE_CONNECTION__POPUP_CONNECTION', 'ConnectionPopup', [
                    'active'        => $connectionPopup,
                    'tooltip'       => 'LC__CABLE_CONNECTION__POPUP_CONNECTION_TITLE',
                    'icon'          => 'icons/silk/disconnect.png',
                    'icon_inactive' => 'icons/silk/disconnect.png',
                    'js_onclick'    => $l_js_onclick,
                ]);
        }

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('conn_link', isys_helper_link::create_url($l_get))
            ->assign('inputs', $l_inputs)
            ->assign('outputs', $l_outputs)
            ->assign('dao_connector', $p_cat)
            ->assign('list_display', true)
            ->assignByRef('list_dao', $l_dao_list)
            ->smarty_tom_add_rule('tom.content.top.filter.p_strValue=' . $l_filter)
            ->smarty_tom_add_rule('tom.content.top.filter.p_bDisabled=0')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bDisabled=0')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_strSelectedID=' . $_SESSION['cRecStatusListView'])
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_arData=' . serialize($l_arData))
            ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
            ->include_template('contentbottomcontent', 'content/bottom/content/catg__connector_list.tpl');
    }
}
