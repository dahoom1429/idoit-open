<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * CMDB UI: Port category for Network
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_network_port extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        return 'Port';
    }

    /**
     * Show the detail-template for port as a subcategory of network.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if (isset($_GET['loadLayer2Vlans'])) {
            isys_core::send_header('Content-Type', 'application/json');
            if (isset($_POST['ids']) && isys_format_json::is_json($_POST['ids'])) {
                $l_dao_layer2 = isys_cmdb_dao_category_s_layer2_net::instance($p_cat->get_database_component());
                echo isys_format_json::encode($l_dao_layer2->get_layer2_vlans(isys_format_json::decode($_POST['ids'])));
            } else {
                echo '[]';
            }

            die;
        }

        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_posts = isys_module_request::get_instance()->get_posts();
        $language = isys_application::instance()->container->get('language');

        $l_bNewPort = false;
        $l_bSave = $l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE;

        $l_id = (isset($l_gets[C__CMDB__GET__CATLEVEL]) ? $l_gets[C__CMDB__GET__CATLEVEL] : (isset($_POST[C__GET__ID]) ? $_POST[C__GET__ID] : -1));

        $l_gets[C__CMDB__GET__CATLEVEL] = $l_id;

        // Retrieve port data.
        $l_catdata = $p_cat->get_data($l_id)
            ->__to_array();

        // Check if this is a new port.
        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__NEW || $l_id == '-1') {
            global $g_port_id;
            $l_id = $g_port_id;
            $this->get_template_component()
                ->assign('nNewPort', 1);
            $l_bNewPort = true;
        }

        // Assign Port-ID to template.
        $this->get_template_component()
            ->assign('port_id', $l_id);

        // Go to list view after saving ports.
        if ($l_bSave && (!($_GET[C__CMDB__GET__CATLEVEL] > 0)) && $p_cat->validate_user_data()) {
            return $this->process_list($p_cat);
        }

        $l_rules = [
            'C__CATG__PORT__IP_ADDRESS' =>  $this->get_linklist($_GET[C__CMDB__GET__OBJECT], $l_id),
            'C__CATG__PORT__TYPE' => [
                'p_strTable' => 'isys_port_type'
            ],
            'C__CATG__PORT__MODE' => [
                'p_strTable' => 'isys_port_mode'
            ],
            'C__CATG__PORT__PLUG' => [
                'p_strTable' => 'isys_connection_type'
            ],
            'C__CATG__PORT__NEGOTIATION' => [
                'p_strTable' => 'isys_port_negotiation'
            ],
            'C__CATG__PORT__DUPLEX' => [
                'p_strTable' => 'isys_port_duplex'
            ],
            'C__CATG__PORT__SPEED' => [
                'p_strTable' => 'isys_port_speed'
            ],
            'C__CATG__PORT__STANDARD' => [
                'p_strTable' => 'isys_port_standard'
            ],
            'C__CATG__PORT__ACTIVE' => [
                'p_arData' => get_smarty_arr_YES_NO()
            ],
            'C__CATG__PORT__COUNT' => [
                'p_strValue' => 1
            ],
            'C__CATG__PORT__NET' => [
                'p_arTypeFilter' => defined_or_default('C__OBJTYPE__LAYER3_NET')
            ],
        ];

        // Get interfaces and assign them.
        $l_dao_iface = isys_cmdb_dao_category_g_network_interface::instance($this->m_database_component);
        $l_resInterface = $l_dao_iface->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

        $l_arInterface = [];

        if (count($l_resInterface)) {
            $interfaceTitle = $language->get('LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE');

            while ($l_row = $l_resInterface->get_row()) {
                $l_arInterface[$interfaceTitle][$l_row['isys_catg_netp_list__id'] . '_C__CATG__NETWORK_INTERFACE'] = $l_row['isys_catg_netp_list__title'];


                if (is_numeric($l_row['isys_catg_netp_list__slotnumber']) && $l_row['isys_catg_netp_list__slotnumber'] > 0) {
                    $l_arInterface[$interfaceTitle][$l_row['isys_catg_netp_list__id'] . '_C__CATG__NETWORK_INTERFACE'] .= ' (Slot: ' . $l_row['isys_catg_netp_list__slotnumber'] . ')';
                }
            }

            $l_rules['C__CATG__PORT__INTERFACE']['p_arData'] = $l_arInterface;
        }

        $l_dao_hba = new isys_cmdb_dao_category_g_hba($this->m_database_component);
        $l_resHBA = $l_dao_hba->get_data(
            null,
            $_GET[C__CMDB__GET__OBJECT],
            "AND isys_hba_type__const = " . $l_dao_hba->convert_sql_text('C__STOR_TYPE_ISCSI_CONTROLLER'),
            null,
            C__RECORD_STATUS__NORMAL
        );

        if (count($l_resHBA)) {
            while ($l_row = $l_resHBA->get_row()) {
                $l_arInterface[$language->get('LC__CMDB__CATG__HBA')][$l_row['isys_catg_hba_list__id'] . '_C__CATG__HBA'] = $l_row['isys_catg_hba_list__title'];
            }

            $l_rules['C__CATG__PORT__INTERFACE']['p_arData'] = $l_arInterface;
        }

        // Assign port data.
        if (!$l_bNewPort) {
            $l_speed = isys_convert::speed(
                $l_catdata["isys_catg_port_list__port_speed_value"],
                (int)$l_catdata["isys_catg_port_list__isys_port_speed__id"],
                C__CONVERT_DIRECTION__BACKWARD
            );

            $l_request = isys_request::factory()
                ->set_row($l_catdata);

            $l_vlans = $p_cat->get_attached_layer2_net($l_id);
            $l_vlanData = $l_selected_vlans = [];
            $l_default_vlan = null;
            while ($l_vrow = $l_vlans->get_row()) {
                $l_vlanData[$l_vrow['object_id']] = $l_vrow['title'];

                if ($l_vrow['default_vlan'] > 0) {
                    $l_default_vlan = $l_vrow['object_id'];
                }
            }
            $l_selected_vlans = array_keys($l_vlanData);

            // Get connection dao.
            $l_rules['C__CATG__PORT__DEST']['p_strValue'] = $p_cat->callback_property_assigned_connector($l_request);
            $l_rules['C__CATG__PORT__CABLE']['p_strValue'] = $p_cat->callback_property_cable($l_request);
            $l_rules['C__CATG__LAYER2__DEST']['p_strValue'] = empty($l_selected_vlans) ? '' : isys_format_json::encode($l_selected_vlans);
            $l_rules['C__CATG__PORT__DEFAULT_VLAN']['p_arData'] = $l_vlanData;
            $l_rules['C__CATG__PORT__DEFAULT_VLAN']['p_strSelectedID'] = $l_default_vlan;
            $l_rules['C__CATG__PORT__TITLE']['p_strValue'] = $l_catdata['isys_catg_port_list__title'];
            $l_rules['C__CATG__PORT__TYPE']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_type__id'];
            $l_rules['C__CATG__PORT__MODE']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_mode__id'];
            $l_rules['C__CATG__PORT__PLUG']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_plug_type__id'];
            $l_rules['C__CATG__PORT__NEGOTIATION']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_negotiation__id'];
            $l_rules['C__CATG__PORT__DUPLEX']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_duplex__id'];
            $l_rules['C__CATG__PORT__SPEED_VALUE']['p_strValue'] = $l_speed;
            $l_rules['C__CATG__PORT__SPEED']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_speed__id'];
            $l_rules['C__CATG__PORT__STANDARD']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__isys_port_standard__id'];
            $l_rules['C__CATG__PORT__MAC']['p_strValue'] = $l_catdata['isys_catg_port_list__mac'];
            $l_rules['C__CATG__PORT__MTU']['p_strValue'] = $l_catdata['isys_catg_port_list__mtu'];
            $l_rules['C__CATG__PORT__ACTIVE']['p_strSelectedID'] = $l_catdata['isys_catg_port_list__state_enabled'];
            $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_catg_port_list__description'];
            $l_rules['C__CATG__PORT__INTERFACE']['p_strSelectedID'] = ($l_catdata['isys_catg_port_list__isys_catg_netp_list__id'] > 0) ?
                $l_catdata['isys_catg_port_list__isys_catg_netp_list__id'] . '_C__CATG__NETWORK_INTERFACE' :
                $l_catdata['isys_catg_port_list__isys_catg_hba_list__id'] . '_C__CATG__HBA';
        } else {
            // @todo  Find out what these numbers mean.
            $l_rules['C__CATG__PORT__ACTIVE']['p_strSelectedID'] = 1;
            $l_rules['C__CATG__PORT__TYPE']['p_strSelectedID'] = 3;
            $l_rules['C__CATG__PORT__SPEED_VALUE']['p_strValue'] = 1;
            $l_rules['C__CATG__PORT__SPEED']['p_strSelectedID'] = defined_or_default('C__PORT_SPEED__GBIT_S');
            $l_rules['C__CATG__PORT__DUPLEX']['p_strSelectedID'] = defined_or_default('C__PORT_DUPLEX__FULL');
            $l_rules['C__CATG__PORT__MODE']['p_strSelectedID'] = defined_or_default('C__PORT_MODE__STANDARD');
            $l_rules['C__CATG__PORT__INTERFACE']['p_strSelectedID'] = $_GET['ifaceID'] . '_C__CATG__NETWORK_INTERFACE';

            $this->get_template_component()->assign('nNewPort', 1);
        }

        if (!$p_cat->validate_user_data() && $l_bSave) {
            $l_rules['C__CATG__PORT__TITLE']['p_strValue'] = $l_posts['C__CATG__PORT__TITLE'];
            $l_rules['C__CATG__PORT__TYPE']['p_strSelectedID'] = $l_posts['C__CATG__PORT__TYPE'];
            $l_rules['C__CATG__PORT__PLUG']['p_strSelectedID'] = $l_posts['C__CATG__PORT__PLUG'];
            $l_rules['C__CATG__PORT__NEGOTIATION']['p_strSelectedID'] = $l_posts['C__CATG__PORT__NEGOTIATION'];
            $l_rules['C__CATG__PORT__DUPLEX']['p_strSelectedID'] = $l_posts['C__CATG__PORT__DUPLEX'];
            $l_rules['C__CATG__PORT__SPEED_VALUE']['p_strValue'] = $l_posts['C__CATG__PORT__SPEED_VALUE'];
            $l_rules['C__CATG__PORT__SPEED']['p_strSelectedID'] = $l_posts['C__CATG__PORT__SPEED'];
            $l_rules['C__CATG__PORT__STANDARD']['p_strSelectedID'] = $l_posts['C__CATG__PORT__STANDARD'];
            $l_rules['C__CATG__PORT__MAC']['p_strValue'] = $l_posts['C__CATG__PORT__MAC'];
            $l_rules['C__CATG__PORT__MTU']['p_strValue'] = $l_posts['C__CATG__PORT__MTU'];
            $l_rules['C__CATG__PORT__ACTIVE']['p_strSelectedID'] = $l_posts['C__CATG__PORT__ACTIVE'];
            $l_rules['C__CATG__PORT__DEST']['p_strValue'] = $l_posts['C__CATG__PORT__DEST'];
            $l_rules['C__CATG__PORT__COUNT']['p_strValue'] = $l_posts['C__CATG__PORT__COUNT'];
            $l_rules['C__CATG__PORT__INTERFACE']['p_strSelectedID'] = $l_posts['C__CATG__PORT__INTERFACE'];
            $l_rules['C__CATG__PORT__START_WITH']['p_strValue'] = $l_posts['C__CATG__PORT__START_WITH'];
            $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_posts['C__CMDB__CAT__COMMENTARY'];

            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // @see  ID-6588  After creating new ports (one or many) we now jump to the list.
        if ($_GET[C__CMDB__GET__VIEWMODE] != C__CMDB__VIEW__LIST_OBJECT && $_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $tplNavbar = isys_component_template_navbar::getInstance();

            $link = isys_helper_link::create_url([
                C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__OBJECT   => $_GET[C__CMDB__GET__OBJECT],
                C__CMDB__GET__CATG     => $_GET[C__CMDB__GET__CATG]
            ]);

            $callback = "function() {window.location = '{$link}';}";
            $saveOnclick = "document.isys_form.navMode.value='" . C__NAVMODE__SAVE . "';";
            $saveOnclick .= "form_submit('', 'post', 'no_replacement', null, {$callback});";

            if ($tplNavbar->get_save_mode() === 'log') {
                $saveOnclick = "idoit.callbackManager.registerCallback('idoit.popup.C__CATG__NETWORK_PORT.accept', {$callback});" .
                    "get_commentary('idoit.popup.C__CATG__NETWORK_PORT.accept');";
            }

            $tplNavbar->set_js_onclick($saveOnclick, C__NAVBAR_BUTTON__SAVE);
        }

        // Assign the images-path and apply rules.
        $this->activate_commentary($p_cat)
            ->get_template_component()
            ->assign('port_ajax_url', "?" . http_build_query($_GET, null, "&") . "&call=category&" . C__CMDB__GET__CATLEVEL . "=" . $l_catdata["isys_catg_port_list__id"] . '&loadLayer2Vlans=1')
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /**
     * Show the list-template for port as a subcategory of network.
     *
     * @param  isys_cmdb_dao_category &$p_cat
     * @param  array                  $p_get_param_override
     * @param  string                 $p_strVarName
     * @param  string                 $p_strTemplateName
     * @param  bool                   $p_bCheckbox
     * @param  bool                   $p_bOrderLink
     * @param  string                 $p_db_field_name
     *
     * @return null
     * @throws Exception
     * @author Niclas Potthast <npotthast@i-doit.org>
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
        global $index_includes;
        $scoped = $p_strVarName != null;
        $this->get_template_component()
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");

        $l_arGetVariables = isys_module_request::get_instance()
            ->get_gets();
        $l_nObjID = $_GET[C__CMDB__GET__OBJECT];

        $l_listdao = isys_cmdb_dao_list_catg_network_port::build($p_cat->get_database_component(), $p_cat);
        $l_listdao->set_rec_status($_SESSION["cRecStatusListView"]);
        $l_listres = $l_listdao->get_result(null, $l_nObjID, null);
        $l_arTableHeader = $l_listdao->get_fields();

        $l_arTemp = $l_arGetVariables;
        $l_arTemp[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        $l_arTemp[C__CMDB__GET__CATG] = $_GET[C__CMDB__GET__CATG] ? $_GET[C__CMDB__GET__CATG] : defined_or_default('C__CATG__NETWORK_PORT');
        $l_arTemp[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
        $l_arTemp[C__GET__NAVMODE] = null;

        $l_arTemp[C__CMDB__GET__CATLEVEL] = "[{isys_catg_port_list__id}]";

        $l_strRowLink = isys_glob_build_url(urldecode(isys_glob_http_build_query($l_arTemp)));

        isys_component_template_navbar::getInstance()
            ->set_visible(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV)
            ->set_active(true, C__NAVBAR_BUTTON__EXPORT_AS_CSV);

        $l_objList = isys_component_list::factory(
            null,
            $l_listres,
            $l_listdao,
            $l_listdao->get_rec_status(),
            ($_POST[C__GET__NAVMODE] == C__NAVMODE__EXPORT_CSV ? 'csv' : 'html')
        );

        $l_objList->config($l_arTableHeader, $l_strRowLink, "[{isys_catg_port_list__id}]", true)
                  ->setEnableMultiselection(true);

        if ($l_objList->createTempTable()) {
            // @see  ID-7506  Because we don't call the parent 'process_list' method, we'll have to do this manually.
            $l_objList->setTableConfigUrl(isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                C__GET__SETTINGS_PAGE => 'cat_list',
                C__CMDB__GET__OBJECT  => $l_nObjID,
                C__CMDB__GET__CATG    => defined_or_default('C__CATG__NETWORK_PORT')
            ]));

            $l_strTempHtml = $l_objList->setScoped($scoped)->getTempTableHtml();

            // @see  ID-4438  Adding "scrollerVisible" in case the table grows to wide.
            $this->get_template_component()
                ->assign("objectTableList", $l_strTempHtml);
        }

        $index_includes['contentbottomcontent'] = "content/bottom/content/object_table_list.tpl";

        return null;
    }

    /**
     * @param  int $p_object_id
     * @param  int $p_port_id
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function get_linklist($p_object_id, $p_port_id)
    {
        // Assign ip addresses.
        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_database_component);
        $l_ips = $l_ip_dao->get_ips_by_obj_id($p_object_id, false, true);
        $l_ip_array = [];

        $emptyAddress = isys_application::instance()->container->get('language')->get('LC__IP__EMPTY_ADDRESS');

        while ($l_row = $l_ips->get_row()) {
            $l_address = $l_row['isys_cats_net_ip_addresses_list__title'] ? $l_row['isys_cats_net_ip_addresses_list__title'] : $l_row['isys_catg_ip_list__hostname'];

            if ($l_row['isys_catg_ip_list__status'] != C__RECORD_STATUS__NORMAL) {
                continue;
            }

            if ($l_row['isys_catg_ip_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
                $l_ip_array[] = [
                    'id'  => $l_row['isys_catg_ip_list__id'],
                    'val' => $l_address ? $l_address : $emptyAddress,
                    'sel' => $p_port_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && $p_port_id !== null
                ];
            } else {
                $l_ip_array[] = [
                    'id'  => $l_row['isys_catg_ip_list__id'],
                    'val' => $l_address ? Ip::validate_ipv6($l_address, true) : $emptyAddress,
                    'sel' => $p_port_id == $l_row['isys_catg_ip_list__isys_catg_port_list__id'] && $p_port_id !== null
                ];
            }
        }

        return [
            'p_bLinklist' => true,
            'p_arData'    => $l_ip_array
        ];
    }
}
