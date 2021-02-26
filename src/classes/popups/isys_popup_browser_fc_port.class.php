<?php

/**
 * i-doit
 *
 * Popup browser for FC-Ports
 *
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_fc_port extends isys_component_popup
{
    /**
     * Json Array of pools to use in format_selection
     *
     * @var string
     */
    private $m_format_pools = '[]';

    /**
     * Id of primary port to use in format_selection
     *
     * @var string
     */
    private $m_format_primary_port = '';

    /**
     * @param int $int
     *
     * @inherit
     * @return $this
     */
    public function set_format_primary_port($int)
    {
        $this->m_format_primary_port = $int;

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
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // Calculate names for different fields.
        if (strstr($p_params["name"], '[') && strstr($p_params["name"], ']')) {
            $l_tmp = explode('[', $p_params["name"]);
            $viewFieldName = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $hiddenFieldName = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            $primaryFieldName = $l_tmp[0] . '__PRIM[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $viewFieldName = $p_params["name"] . '__VIEW';
            $hiddenFieldName = $p_params["name"] . '__HIDDEN';
            $primaryFieldName = $p_params["name"] . '__PRIM';
        }

        // Hidden field, in which the selected value is stored.
        $l_strHiddenField = '<input name="' . $hiddenFieldName .'" id="' . $hiddenFieldName . '" type="hidden" value="' . $p_params["p_strValue"] . '" data-sync-fields="PRIM" />';

        // Set parameters for the f_text plug-in.
        $p_params['p_bReadonly'] = "1";

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (isys_glob_get_param('editMode') == C__EDITMODE__ON) {
            // Here we pass some data, that we'll need later on.
            $p_params[C__CMDB__GET__OBJECT] = $_GET[C__CMDB__GET__OBJECT];
            $p_params[C__CMDB__GET__CATLEVEL] = $_GET[C__CMDB__GET__CATLEVEL];
            $p_params['selected_ports'] = $p_params['p_strValue'];
            $p_params['disableInputGroup'] = true;

            $l_url = $this->process_overlay('', 400, 460, $p_params);

            $p_params["p_strValue"] = $this->set_format_pools($p_params["p_strValue"])
                ->set_format_primary_port($p_params["p_strPrim"])
                ->format_selection($_GET[C__CMDB__GET__OBJECT], false);

            $p_params['name'] = $viewFieldName;

            if ($p_params['id']) {
                $l_id = $p_params['id'];
            } else {
                $l_id = $p_params['name'];
                $p_params['id'] = $l_id;
            }

            $l_onclick_detach = "var e_view = $('" . $viewFieldName . "'), e_hidden = $('" . $hiddenFieldName . "');
				if(e_view && e_hidden) {
					e_view.value = '" . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . "!';
					e_hidden.value = '';
				}" . (isset($p_params['callback_detach']) ? $p_params['callback_detach'] : "");

            return $l_objPlugin->navigation_edit($this->template, $p_params) .
                '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $l_url . ';">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="' . $this->language->get('LC__UNIVERSAL__ATTACH') . '" />' .
                '</a>' .
                '<a href="javascript:" title="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" class="input-group-addon input-group-addon-clickable" onClick="' . $l_onclick_detach . ';">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/detach.png" alt="' . $this->language->get('LC__UNIVERSAL__DETACH') . '" />' .
                '</a>' . $l_strHiddenField .
                '<input name="' . $primaryFieldName . '" id="' . $primaryFieldName . '" type="hidden" value="' . $p_params['p_strPrim'] . '" />';
        }

        $p_params['p_bHtmlDecode'] = true;
        $p_params['p_strValue'] = $this->set_format_pools($p_params['p_strValue'])
            ->set_format_primary_port($p_params['p_strPrim'])
            ->format_selection($_GET[C__CMDB__GET__OBJECT], true);

        return $l_objPlugin->navigation_view($this->template, $p_params) . $l_strHiddenField;
    }

    /**
     * Returns a formatted string for the selected SAN-Pool.
     *
     * @param  integer $p_objid
     * @param  bool    $plain
     *
     * @return string
     */
    public function format_selection($p_objid, $plain = false)
    {
        if (empty($this->m_format_pools)) {
            return $this->language->get('LC_UNIVERSAL__NONE_SELECTED') . '.';
        }

        $l_pools = explode(',', $this->m_format_pools);

        if (!$p_objid) {
            $p_objid = (int) $_GET[C__CMDB__GET__OBJECT];
        }

        $l_daoFC = new isys_cmdb_dao_category_g_controller_fcport($this->database);
        $l_res = $l_daoFC->get_data(null, $p_objid, '', null, C__RECORD_STATUS__NORMAL);

        if (!is_countable($l_res) || count($l_res) === 0) {
            return $this->language->get('LC_SANPOOL_POPUP__NO_PATHS_CONNECTED') . '.';
        }

        $l_str_out = [];

        while ($l_row = $l_res->get_row()) {
            if (in_array($l_row['isys_catg_fc_port_list__id'], $l_pools)) {
                $l_path = isys_glob_str_stop($l_row['isys_catg_fc_port_list__title'], 50);
                if ($this->m_format_primary_port == $l_row['isys_catg_fc_port_list__id']) {
                    $l_path .= ' (' . $this->language->get('LC__UNIVERSAL__PRIMARY') . ')';
                }

                $l_str_out[] = $l_path;
            }
        }

        return implode(', ', $l_str_out);
    }

    /**
     *
     * @param  isys_module_request $p_modreq
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_fc_ports = [];
        $l_fc_ports_selected = [];

        // Unpack module request.
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        /**
         * Calculate names for different fields
         */
        if (strpos($l_params['name'], '[') !== false && strpos($l_params['name'], ']') !== false) {
            $l_tmp = explode('[', $l_params['name']);
            $viewFieldName = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $hiddenFieldName = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            $primaryFieldName = $l_tmp[0] . '__PRIM[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $viewFieldName = $l_params['name'] . '__VIEW';
            $hiddenFieldName = $l_params['name'] . '__HIDDEN';
            $primaryFieldName = $l_params['name'] . '__PRIM';
        }

        /**
         * Creating an instance of "isys_cmdb_dao_category_g_controller_fcport".
         *
         * @var  isys_cmdb_dao_category_g_controller_fcport $l_dao
         */
        $l_dao = isys_cmdb_dao_category_g_controller_fcport::instance($this->database);

        $l_res = $l_dao->get_data(null, $l_params[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);

        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_row['isys_catg_fc_port_list__id'] = (int)$l_row['isys_catg_fc_port_list__id'];

                $l_fc_ports[$l_row['isys_catg_fc_port_list__id']] = [
                    'id'    => $l_row['isys_catg_fc_port_list__id'],
                    'title' => $l_row['isys_catg_fc_port_list__title'],
                ];
            }

            $l_fc_ports_selected = explode(',', $l_params['selected_ports']);
            $l_fc_ports_selected = array_map('intval', $l_fc_ports_selected);
        }

        if (empty($l_params['selected_ports'])) {
            $l_fc_ports_selected = [];
        }

        // Write primary path.
        $this->template->assign('returnfield', $l_params['name'])
            ->assign('viewField', $viewFieldName)
            ->assign('hiddenField', $hiddenFieldName)
            ->assign('primField', $primaryFieldName)
            ->assign('callback_accept', $l_params['callback_accept'])
            ->assign('fc_ports', isys_format_json::encode($l_fc_ports))
            ->assign('fc_ports_selection', isys_format_json::encode($l_fc_ports_selected))
            ->assign('primary', $l_params['p_strPrim'])
            ->display('popup/fc_port.tpl');
        die;
    }
}
