<?php

/**
 * i-doit
 *
 * Popup class for various category-data selections (multiselection only!).
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_cat_data extends isys_component_popup
{
    /**
     * Constant for automatic data retrieval.
     * Example: "isys_cmdb_dao_category_g_ip::catdata_browser".
     */
    const C__DATARETRIEVAL = 'dataretrieval';

    /**
     * Method for displaying the browser.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $l_params = isys_format_json::decode(base64_decode($_POST['params']), true);

        if ($l_params[C__CMDB__GET__OBJECT] > 0 && class_exists($l_params[self::C__DATARETRIEVAL][0])) {
            $l_dao = new $l_params[self::C__DATARETRIEVAL][0]($this->database);

            if (method_exists($l_dao, $l_params[self::C__DATARETRIEVAL][1])) {
                $l_data = $l_dao->{$l_params[self::C__DATARETRIEVAL][1]}($l_params[C__CMDB__GET__OBJECT]);

                $this->template->assign('browser_title', $this->language->get($l_params['title']))
                    ->assign('preselection', $l_params['preselection'])
                    ->assign('data', $l_data)
                    ->assign('obj_title', $l_dao->get_obj_name_by_id_as_string($l_params[C__CMDB__GET__OBJECT]));
            }
        }

        $this->template->assign('hidden_field', $l_params['hidden'])
            ->assign('view_field', $l_params['view'])
            ->display('popup/cat_data.tpl');
        die();
    }

    /**
     * Handles SMARTY request for location browser.
     *
     * @param  isys_component_template &$p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     * @global array                   $g_dirs
     *
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        $l_objPlugin = new isys_smarty_plugin_f_text();

        $l_selection = [];
        $l_data = [];

        $p_params['disableInputGroup'] = true;

        $l_browser_params = [
            self::C__DATARETRIEVAL => explode('::', $p_params[self::C__DATARETRIEVAL]),
            C__CMDB__GET__OBJECT   => $p_params['p_strSelectedID'],
            'preselection'         => isys_format_json::decode(html_entity_decode($p_params['p_preSelection']), true),
            'hidden'               => $p_params['name'] . '__HIDDEN',
            'view'                 => $p_params['name'] . '__VIEW',
            'title'                => $p_params['title']
        ];

        if (class_exists($l_browser_params[self::C__DATARETRIEVAL][0])) {
            $l_dao = new $l_browser_params[self::C__DATARETRIEVAL][0]($this->database);

            if (method_exists($l_dao, $l_browser_params[self::C__DATARETRIEVAL][1])) {
                $l_data = $l_dao->{$l_browser_params[self::C__DATARETRIEVAL][1]}($l_browser_params[C__CMDB__GET__OBJECT]);
            }
        }

        if (isset($l_browser_params['preselection']) && is_array($l_browser_params['preselection'])) {
            foreach ($l_browser_params['preselection'] as $l_preselection) {
                if (array_key_exists($l_preselection, $l_data)) {
                    $l_selection[] = strip_tags($l_data[$l_preselection]);
                }
            }
        }

        $p_params['p_strValue'] = implode(', ', $l_selection);

        $l_hidden_field = '<input id="' . $p_params['name'] . '__HIDDEN" name="' . $p_params['name'] . '__HIDDEN" type="hidden" value="' . $p_params['p_preSelection'] . '" />';

        // Set params for the f_text plugin.
        $p_params['name'] .= '__VIEW';
        $p_params['p_bReadonly'] = 1;

        if (isys_glob_is_edit_mode()) {
            return $l_objPlugin->navigation_edit($this->template, $p_params) .
                '<a href="javascript:" class="input-group-addon input-group-addon-clickable" title="' . $this->language->get('LC_POPUP_IMAGE__CHOOSE_IMAGE') . '" onClick="' . $this->process_overlay('', 800, 400, $l_browser_params) . ';">' .
                '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/zoom.png" alt="' . $this->language->get('LC_UNIVERSAL__MAGNIFIER') . '" />' .
                '</a>' . $l_hidden_field;
        }

        return '<img style="width:15px; height:15px;" src="' . isys_application::instance()->www_path . 'images/empty.gif" class="infoIcon vam mr5">' . $p_params['p_strValue'] . $l_hidden_field;
    }
}
