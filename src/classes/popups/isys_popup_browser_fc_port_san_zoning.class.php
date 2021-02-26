<?php

/**
 * i-doit
 *
 * Popup browser for FC-Ports.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_fc_port_san_zoning extends isys_component_popup
{
    /**
     * Json string of selected fc ports to use in format_selection
     *
     * @var string
     */
    private $m_format_selected_fc_ports = '[]';

    /**
     * Selected wwn for format_selection
     *
     * @var string
     */
    private $m_format_selected_wwn = '';

    /**
     * Json string of san zones for format_selection
     *
     * @var string
     */
    private $m_format_zones = '[]';

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_zones($json_string = '')
    {
        $this->m_format_zones = $json_string;

        return $this;
    }

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_selected_fc_ports($json_string = '')
    {
        $this->m_format_selected_fc_ports = $json_string;

        return $this;
    }

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_selected_wwn($json_string = '')
    {
        $this->m_format_selected_wwn = $json_string;

        return $this;
    }

    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     *
     * @return string
     * @throws \idoit\Exception\JsonException
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // Hidden field, in which the selected value is put.
        $l_hidden = '<input id="' . $p_params['name'] . '__HIDDEN" name="' . $p_params['name'] . '__HIDDEN" type="hidden" value="' . $p_params['p_strValue'] . '" />';
        $l_hidden_fc = '<input id="' . $p_params['name'] . '__SELECTED_FCPORT" name="' . $p_params['name'] . '__SELECTED_FCPORT" type="hidden" value="' . $p_params['p_strSelFCPort'] . '" />';
        $l_hidden_wwn = '<input id="' . $p_params['name'] . '__SELECTED_WWN" name="' . $p_params['name'] . '__SELECTED_WWN" type="hidden" value="' . $p_params['p_strSelWWN'] . '" />';

        if ($p_params['id']) {
            $l_id = $p_params['id'];
        } else {
            $l_id = $p_params['name'];
            $p_params['id'] = $l_id;
        }

        // Set parameters for the f_text plug-in.
        $p_params['name'] .= '__VIEW';
        $p_params['p_bReadonly'] = 1;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_is_edit_mode()) {
            $p_params['disableInputGroup'] = true;

            $p_params['p_strValue'] = $this->set_format_zones($p_params['p_strValue'])
                ->set_format_selected_fc_ports($p_params['p_strPrim'])
                ->set_format_selected_wwn($p_params['p_strSelWWN'])
                ->format_selection($_GET[C__CMDB__GET__OBJECT], true);

            $l_detach_callback = isset($p_params['p_strDetachCallback']) ? $p_params['p_strDetachCallback'] : "";
            $l_onclick_detach = "var e_view = $('" . $l_id . "'), " . "e_hidden = $('" . $l_id . "__HIDDEN')," . "e_hidden2 = $('" . $l_id . "__SELECTED_FCPORT')," .
                "e_hidden3 = $('" . $l_id . "__SELECTED_WWN');" . "if(e_view && e_hidden && e_hidden2 && e_hidden3) {" . "e_view.value = '" .
                $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "!';" . "e_hidden.value = '';" . "e_hidden2.value = '';" . "e_hidden3.value = '';" . "} " .
                $l_detach_callback;

            $l_return = $l_objPlugin->navigation_edit($this->template, $p_params) .
                '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $this->process_overlay('', 800, 360, $p_params) . ';">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="" />' .
                '</a>' .
                '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $l_onclick_detach . ';">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/detach.png" alt="" />' .
                '</a>';

            return $l_return . $l_hidden . $l_hidden_fc . $l_hidden_wwn;
        }
        if (empty($p_params['p_strValue']) || $p_params['p_strValue'] === '[]') {
            $p_params['p_bInfoIconSpacer'] = 1;
        } else {
            $p_params['p_bInfoIconSpacer'] = 0;
        }

        $p_params['p_bHtmlDecode'] = true;
        $p_params['p_strValue'] = $this->set_format_zones($p_params['p_strValue'])
            ->set_format_selected_fc_ports($p_params['p_strSelFCPort'])
            ->set_format_selected_wwn($p_params['p_strSelWWN'])
            ->format_selection($_GET[C__CMDB__GET__OBJECT], false);

        return $l_objPlugin->navigation_view($this->template, $p_params);
    }

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param integer $p_id
     * @param bool    $p_plain
     *
     * @return array|string
     * @throws \idoit\Exception\JsonException
     */
    public function format_selection($p_id, $p_plain = false)
    {
        $l_zones = isys_format_json::decode($this->m_format_zones, true);
        $l_selFCPort = isys_format_json::decode($this->m_format_selected_fc_ports, true);
        $l_selWWN = isys_format_json::decode($this->m_format_selected_wwn, true);

        if (!is_array($l_zones) || !count($l_zones)) {
            return $this->language->get('LC_UNIVERSAL__NONE_SELECTED') . '.';
        }

        $l_daoZones = new isys_cmdb_dao_category_s_san_zoning($this->database);

        $l_res = $l_daoZones->get_data(null, null, 'AND isys_cats_san_zoning_list__id ' . $l_daoZones->prepare_in_condition($l_zones), null, C__RECORD_STATUS__NORMAL);

        if (!$l_res->num_rows()) {
            return $this->language->get('LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED') . '.';
        }

        $l_return = [];

        if ($p_plain) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = isys_glob_str_stop($l_row['isys_obj__title'], 50);
            }

            $l_return = implode(', ', $l_return);
        } else {
            while ($l_row = $l_res->get_row()) {
                $l_tmp = '<a href="' . isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_row['isys_obj__id']]) . '">' .
                    isys_glob_str_stop($l_row['isys_obj__title'], 50) . '</a> ' . $this->language->get('LC__UNIVERSAL__CHOSEN') . ': ';

                if (in_array($l_row['isys_cats_san_zoning_list__id'], $l_selFCPort)) {
                    $l_tmp .= 'FC-Port, ';
                }

                if (in_array($l_row['isys_cats_san_zoning_list__id'], $l_selWWN)) {
                    $l_tmp .= 'WWN';
                } else {
                    // For removing the ", " behind "FC-Port".
                    $l_tmp = substr($l_tmp, 0, -2);
                }

                $l_return[] = '<li>' . $l_tmp . '</li>';
            }

            $l_return = '<ul style="margin:0 0 0 20px; list-style:none;">' . implode("\n", $l_return) . '</ul>';
        }

        return $l_return;
    }

    /**
     * Method for handling the module request.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_general
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        // Prepare tree.
        $l_tree = isys_component_tree::factory('g_browser');

        // Needing CMDB DAO.
        $l_dao = new isys_cmdb_dao_category_s_san_zoning($this->database);

        // Create root node.
        $l_node_root = $l_tree->add_node(0, -1, $this->language->get($l_dao->get_objtype_name_by_id_as_string(defined_or_default('C__OBJTYPE__SAN_ZONING'))));

        $l_n = 1;

        $l_deviceList = [];
        $l_res = $l_dao->get_data();

        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                if (empty($l_row['isys_cats_san_zoning_list__id'])) {
                    $l_dao->create($l_row['isys_obj__id'], C__RECORD_STATUS__NORMAL, $l_row['isys_obj__title'], null);
                }
            }

            $l_res = $l_dao->get_data();

            $l_counter = 0;
            // Remove sorting otherwise there will be a problem in the selection
            $l_tree->set_tree_sort(false);

            while ($l_row = $l_res->get_row()) {
                $l_node = $l_tree->add_node(
                    $l_n++,
                    $l_node_root,
                    '<label>' . $this->build_hiddenfield($l_row['isys_cats_san_zoning_list__id'], $l_counter) . ' ' . isys_glob_str_stop($l_row['isys_obj__title'], 50) .
                    '</label>'
                );

                $l_tree->add_node(
                    99999 + $l_n,
                    $l_node,
                    '<label>' . $this->build_checkbox_fcport_selection($l_params['p_strSelFCPort'], $l_row['isys_cats_san_zoning_list__id']) . ' FC-Port</label>'
                );

                $l_tree->add_node(
                    999999 + $l_n,
                    $l_node,
                    '<label>' . $this->build_checkbox_wwn_selection($l_params['p_strSelWWN'], $l_row['isys_cats_san_zoning_list__id']) . ' WWN</label>'
                );

                $l_counter++;
                $l_deviceList[$l_row['isys_cats_san_zoning_list__id']] = $l_row['isys_obj__title'];
            }
        } else {
            $l_tree->add_node($l_n++, $l_node_root, $this->language->get('LC_FC_PORT_POPUP__NO_PORTS'));
        }

        $this->template->assign('deviceList', $l_deviceList)
            ->assign('extraField', $l_params['p_strExtraField'])
            ->assign('name', $l_params["id"])
            ->assign('browser', $l_tree->process(0))
            ->display('popup/fc_port_san_zoning.tpl');
        die;
    }

    /**
     * Method for rendering the FC port checkbox.
     *
     * @param   string  $p_ports
     * @param   integer $p_id
     *
     * @return  string
     * @throws \idoit\Exception\JsonException
     */
    public function build_checkbox_fcport_selection($p_ports, $p_id)
    {
        $l_show = in_array($p_id, isys_format_json::decode($p_ports, true));

        return '<input class="vam" type="checkbox" name="fcport_selection[]" value="' . $p_id . '" onChange="window.refresh_selected();" ' .
            ($l_show ? 'checked="checked"' : '') . ' />';
    }

    /**
     * Method for rendering the WWN checkbox.
     *
     * @param   string  $p_wwn
     * @param   integer $p_id
     *
     * @return  string
     * @throws \idoit\Exception\JsonException
     */
    protected function build_checkbox_wwn_selection($p_wwn, $p_id)
    {
        $l_show = in_array($p_id, isys_format_json::decode($p_wwn, true));

        return '<input class="vam" type="checkbox" name="wwn_selection[]" value="' . $p_id . '" onChange="window.refresh_selected();" ' .
            ($l_show ? 'checked="checked"' : '') . ' />';
    }

    /**
     * Method for building the hidden fields (used as directories in the tree).
     *
     * @param   integer $p_id
     * @param   integer $p_counter
     *
     * @return  string
     */
    private function build_hiddenfield($p_id, $p_counter)
    {
        return '<input class="vam" type="hidden" name="zonesInFCPort[]" id="zone_' . $p_counter . '" value="' . $p_id . '" />';
    }
}
