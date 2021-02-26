<?php

/**
 * i-doit
 *
 * Popup browser for SAN-Pools.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_sanpool extends isys_component_popup
{
    /**
     * Json Array of pools to use in format_selection
     *
     * @var string
     */
    private $m_format_pools = '[]';

    /**
     * Json array of raids to use in format_selection
     *
     * @var string
     */
    private $m_format_raids = '[]';

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_raids($json_string = '[]')
    {
        $this->m_format_raids = $json_string;

        return $this;
    }

    /**
     * @param string $json_string
     *
     * @inherit
     * @return $this
     */
    public function set_format_pools($json_string = '[]')
    {
        $this->m_format_pools = $json_string;

        return $this;
    }

    /**
     * Handles SMARTY request for SAN-Pool browser.
     *
     * @param   isys_component_template $template
     * @param   array                   $p_params
     *
     * @return  string
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     * @author Andre Woesten <awoesten@i-doit.org>
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$template, $p_params)
    {
        $p_params['currentObjID'] = $_GET[C__CMDB__GET__OBJECT];

        // Hidden field, in which the selected value is put.
        $l_hidden_devices = '<input id="' . $p_params['name'] . '__HIDDEN" name="' . $p_params['name'] . '__HIDDEN" type="hidden" value="' . $p_params['p_selectedDevices'] .
            '" />';
        $l_hidden_raids = '<input id="' . $p_params['name'] . '__HIDDEN2" name="' . $p_params['name'] . '__HIDDEN2" type="hidden" value="' . $p_params['p_selectedRaids'] .
            '" />';

        if ($p_params['id']) {
            $l_id = $p_params['id'];
        } else {
            $l_id = $p_params['name'];
            $p_params['id'] = $l_id;
        }

        // Set parameters for the f_text plug-in.
        $p_params['p_strValue'] = $this->set_format_pools($p_params['p_selectedDevices'])
            ->set_format_raids($p_params['p_selectedRaids'])
            ->format_selection($_GET[C__CMDB__GET__OBJECT]);

        $p_params['name'] .= '__VIEW';
        $p_params['p_bReadonly'] = '1';

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_is_edit_mode()) {
            $p_params['disableInputGroup'] = true;
            $l_detach_callback = isset($p_params['p_strDetachCallback']) ? $p_params['p_strDetachCallback'] : '';
            $l_onclick_detach = "var e_view = $('" . $l_id . "'), " . "e_hidden = $('" . $l_id . "__HIDDEN')," . "e_hidden2 = $('" . $l_id . "__HIDDEN2');" .
                "if(e_view && e_hidden && e_hidden2) {" . "e_view.value = '" . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "!'; " . "e_hidden.value = '';" .
                "e_hidden2.value = '';" . "}" . $l_detach_callback . ';';

            return $l_objPlugin->navigation_edit($template, $p_params) .
                '<a href="javascript:" title="' . $this->language->get('LC_SANPOOL_POPUP__SELECT_SAN') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $this->process_overlay('', 800, 360, $p_params) . '">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" />' .
                '</a>' .
                '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $l_onclick_detach . '" >' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/detach.png" alt="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" />' .
                '</a>' .
                $l_hidden_devices .
                $l_hidden_raids;
        }

        $p_params['p_bHtmlDecode'] = true;

        return $l_objPlugin->navigation_view($template, $p_params);
    }

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param   integer $p_objid
     * @param   boolean $plain
     *
     * @return  string
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    public function format_selection($p_objid, $plain = false)
    {
        $l_pools = isys_format_json::decode($this->m_format_pools);
        $l_raids = isys_format_json::decode($this->m_format_raids);

        $l_dao_ctrl = new isys_cmdb_dao_category_g_stor($this->database);
        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->database);

        if ((!is_countable($l_pools) || count($l_pools) === 0) && (!is_countable($l_raids) || count($l_raids) === 0)) {
            return $this->language->get('LC_UNIVERSAL__NONE_SELECTED') . '.';
        }

        if (empty($p_objid)) {
            $p_objid = (int) $_GET[C__CMDB__GET__OBJECT];
        }

        if (!$l_dao_ctrl->obj_exists($p_objid)) {
            return $this->language->get('LC_SANPOOL_POPUP__NO_OBJECT') . '.';
        }

        $l_res_dev = $l_dao_ctrl->get_device_subset($l_pools);
        $l_res_raid = $l_dao_raid->get_raid_subset($l_raids);

        if ((!is_countable($l_res_dev) || count($l_res_dev) === 0) && (!is_countable($l_res_raid) || count($l_res_raid) === 0)) {
            return $this->language->get('LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED') . '.';
        }

        $l_return = [];

        while ($l_row = $l_res_dev->get_row()) {
            $l_memory = isys_convert::memory($l_row['isys_catg_stor_list__capacity'], $l_row['isys_memory_unit__const'], C__CONVERT_DIRECTION__BACKWARD);
            $l_memory_type = $l_row['isys_memory_unit__title'];

            $l_return[] = isys_glob_str_stop($l_row['isys_catg_stor_list__title'], 50) . ' (' . $l_memory . ' ' . $l_memory_type . ')';
        }

        while ($l_row = $l_res_raid->get_row()) {
            list($l_memory, $l_memory_type_title, $l_num_disks) = $l_dao_ctrl->get_raid_memory_info($l_row['isys_catg_raid_list__id']);

            $l_memory_real = $l_dao_ctrl->raidcalc($l_num_disks, $l_memory, $l_dao_ctrl->get_raid_level($l_row['isys_catg_raid_list__isys_stor_raid_level__id']));

            $l_return[] = isys_glob_str_stop($l_row['isys_catg_raid_list__title'], 50) . ' (' . $l_memory_real . ' ' . $l_memory_type_title . ')';
        }

        return implode(",\n ", $l_return);
    }

    /**
     * This method gets called by the Ajax request to display the browser.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return void
     * @throws isys_exception_general
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_deviceList = [];
        $l_raidList = [];

        // Unpack module request.
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);
        $l_devs = isys_format_json::decode($l_params['p_selectedDevices'], true);
        $l_raids = isys_format_json::decode($l_params['p_selectedRaids'], true);

        // Prepare tree component.
        $l_tree = isys_component_tree::factory('ldev_browser');

        // Needing CMDB DAO.
        $l_dao_cmdb = new isys_cmdb_dao($this->database);

        // Create root node.
        $l_node_root = $l_tree->add_node(0, -1, $l_dao_cmdb->get_obj_name_by_id_as_string($l_params['currentObjID']));

        $l_dao_ctrl = new isys_cmdb_dao_category_g_stor($this->database);
        $l_shown_devices = [];

        $l_node_count = 2;
        $l_res_ctrl = $l_dao_ctrl->get_controller_by_object_id($l_params['currentObjID']);

        if ($l_res_ctrl && $l_res_ctrl->num_rows() > 0) {
            $l_node_controller = $l_tree->add_node(1, 0, $this->language->get('LC__CATG__STORAGE_CONTROLLER'));

            // Add controllers.
            while ($l_row_ctrl = $l_res_ctrl->get_row()) {
                $l_node_ctrl = $l_tree->add_node($l_node_count++, $l_node_controller, $l_row_ctrl['isys_catg_controller_list__title']);

                $l_res_dev = $l_dao_ctrl->get_devices($l_row_ctrl['isys_catg_controller_list__id']);
                $l_res_raid = $l_dao_ctrl->get_raids($l_row_ctrl['isys_catg_controller_list__id']);

                if ((is_countable($l_res_dev) && count($l_res_dev)) || (is_countable($l_res_raid) && count($l_res_raid))) {
                    // Add devices.
                    while ($l_row_dev = $l_res_dev->get_row()) {
                        $l_tree->add_node($l_node_count++, $l_node_ctrl, '<label>' .
                            $this->build_checkbox($l_row_dev['isys_catg_stor_list__id'], 'devicesInPool', in_array($l_row_dev['isys_catg_stor_list__id'], $l_devs)) . ' ' .
                            isys_glob_str_stop($l_row_dev['isys_catg_stor_list__title'], 50) . '</label>');

                        $l_shown_devices[] = $l_row_dev['isys_catg_stor_list__id'];
                    }

                    // Add raids.
                    while ($l_row_raid = $l_res_raid->get_row()) {
                        $l_tree->add_node($l_node_count++, $l_node_ctrl, '<label>' .
                            $this->build_checkbox($l_row_raid['isys_catg_raid_list__id'], 'raidsInPool', in_array($l_row_raid['isys_catg_raid_list__id'], $l_raids)) . ' ' .
                            isys_glob_str_stop($l_row_raid['isys_catg_raid_list__title'], 50) . '</label>');

                        $l_raidList[$l_row_raid['isys_catg_raid_list__id']] = $l_row_raid['isys_catg_raid_list__title'];
                    }
                } else {
                    $l_tree->add_node($l_node_count++, $l_node_ctrl, $this->language->get('LC_SANPOOL_POPUP__NO_DEVICES_FOUND') . '!');
                }
            }
        }

        // We want to display a message, if no devices are connected.
        // We need this node to show devices which are not assigned to any controller
        $l_node_unassigned = $l_tree->add_node(($l_node_count++), $l_node_root, $this->language->get('LC_SANPOOL_POPUP__NO_DEVICES_CONNECTED'));

        $l_res_dev = $l_dao_ctrl->get_devices(null, $l_params['currentObjID']);
        $l_res_raid = $l_dao_ctrl->get_unassigned_raids($l_params['currentObjID']);

        if ($l_res_dev->num_rows() > 0 || $l_res_raid->num_rows() > 0) {
            while ($l_row_dev = $l_res_dev->get_row()) {
                if (!empty($l_shown_devices)) {
                    if (in_array($l_row_dev['isys_catg_stor_list__id'], $l_shown_devices) === false) {
                        $l_tree->add_node(($l_node_count++), $l_node_unassigned, '<label>' .
                            $this->build_checkbox($l_row_dev['isys_catg_stor_list__id'], 'devicesInPool', in_array($l_row_dev['isys_catg_stor_list__id'], $l_devs)) . ' ' .
                            isys_glob_str_stop($l_row_dev['isys_catg_stor_list__title'], 50) . '</label>');
                    }
                } else {
                    $l_tree->add_node(
                        $l_node_count++,
                        $l_node_unassigned,
                        '<label>' . $this->build_checkbox($l_row_dev['isys_catg_stor_list__id'], 'devicesInPool', in_array($l_row_dev['isys_catg_stor_list__id'], $l_devs)) .
                        ' ' . isys_glob_str_stop($l_row_dev['isys_catg_stor_list__title'], 50) . '</label>'
                    );
                }

                $l_deviceList[$l_row_dev['isys_catg_stor_list__id']] = $l_row_dev['isys_catg_stor_list__title'];
            }

            while ($l_row_raid = $l_res_raid->get_row()) {
                $l_tree->add_node(
                    $l_node_count++,
                    $l_node_unassigned,
                    '<label>' . $this->build_checkbox($l_row_raid['isys_catg_raid_list__id'], 'raidsInPool', in_array($l_row_raid['isys_catg_raid_list__id'], $l_raids)) .
                    ' ' . isys_glob_str_stop($l_row_raid['isys_catg_raid_list__title'], 50) . '</label>'
                );

                $l_raidList[$l_row_raid['isys_catg_raid_list__id']] = $l_row_raid['isys_catg_raid_list__title'];
            }
        } else {
            $l_tree->add_node($l_node_count, $l_node_root, $this->language->get('LC_SANPOOL_POPUP__NO_DEVICES_FOUND'));
        }

        $this->template->assign('name', $l_params['id'])
            ->assign('deviceList', isys_format_json::encode($l_deviceList))
            ->assign('raidList', isys_format_json::encode($l_raidList))
            ->assign('browser', $l_tree->process(0))
            ->display('popup/sanzone.tpl');
        die();
    }

    /**
     * Method for displaying a checkbox inside the object browser.
     *
     * @param   integer $p_id
     * @param   string  $p_name
     * @param   boolean $p_checked
     *
     * @return  string
     */
    protected function build_checkbox($p_id, $p_name, $p_checked)
    {
        return '<input class="vam" type="checkbox" name="' . $p_name . '[]" value="' . $p_id . '"  ' . ($p_checked ? 'checked="checked"' : '') . ' />';
    }
}
