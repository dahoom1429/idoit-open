<?php

/**
 * i-doit
 *
 * Popup class for Dialog+ Boxes
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_dialog_plus extends isys_component_popup
{
    /**
     * Handles SMARTY request for dialog plus lists and builds the list base on the specified table.
     *
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        if (!isset($p_params['p_strSelectedID']) && $p_params['p_bDbFieldNN'] != '1') {
            $p_params['p_strSelectedID'] = '-1';
        }

        if (!isset($p_params['p_bPlus'])) {
            $p_params['p_bPlus'] = 'on';
        }

        if (!isset($p_params['id'])) {
            $p_params['id'] = $p_params['name'];
        }

        if (isset($p_params['multiselect']) && $p_params['multiselect']) {
            $p_params['p_bDbFieldNN'] = true;
            $p_params['p_multiple'] = true;
            $p_params['name'] .= '[]';
        }

        // This is necessary to prevent multiple nested input-groups.
        $p_params['disableInputGroup'] = true;

        $l_auth_identifier = null;
        $l_auth_path = null;

        // Check if the current user is allowed to write dialog-plus fields.
        if (($p_params['p_strTable'] != 'isys_dialog_plus_custom')) {
            $l_auth_identifier = $p_params['p_strTable'];
            $l_auth_path = 'TABLE';
        } else {
            $l_auth_identifier = $p_params['p_identifier'];
            $l_auth_path = 'CUSTOM';
        }

        if (!isys_auth_dialog_admin::instance()
            ->is_allowed_to(isys_auth::EDIT, $l_auth_path . '/' . $l_auth_identifier)) {
            $p_params['p_bPlus'] = 'off';
        }

        // Redirect request to responsible dialog plus plugin.
        $l_dialog_obj = new isys_smarty_plugin_f_dialog();
        $l_arParams = $p_params;

        if ($l_arParams['p_bPlus'] == 'off' || !isset($l_arParams['p_bPlus'])) {
            $l_arParams['p_strLink'] = '';
        } else {
            $l_arParams['p_strLink'] = $this->process_overlay('', 480, '70%', $l_arParams, $l_arParams['p_strPopupReceiver'] ?: null);
        }

        // Prevent adding values if parent field is not setted.
        if (isset($l_arParams['p_strSecDataIdentifier'])) {
            // Get class and property tag of parent
            list($l_class, $l_property_tag) = explode('::', $l_arParams['p_strSecDataIdentifier']);

            // Default error if identifier for sec data is not present.
            $l_error = $this->language->get('LC__POPUP__DIALOG_PLUS__ERROR_SEC_VALUE_REQUIRED');

            // Check class exists and property tag is setted.
            if (isset($l_class) && class_exists($l_class) && isset($l_property_tag)) {
                // Get properties of sec class
                /** @var isys_cmdb_dao_category $l_properties */
                $l_properties = isys_factory::get_instance($l_class, $this->database)
                    ->get_properties();

                // Is the sec property present?
                if (is_array($l_properties) && isset($l_properties[$l_property_tag])) {
                    // Generate error message
                    $l_error = $this->language->get('LC__POPUP__DIALOG_PLUS__ERROR_SEC_VALUE_REQUIRED_FIELD', [
                        $this->language->get($l_properties[$l_property_tag][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE])
                    ]);
                }
            }

            // Check for valid value in parent selectbox
            $l_arParams['p_strLink'] = "if ($('" . $l_arParams['p_strSecTableIdentifier'] . "').value > 0) {
                                            " . $l_arParams['p_strLink'] . "}
                                        else {
                                            $('" . $l_arParams['p_strSecTableIdentifier'] . "').highlight();
                                            idoit.Notify.warning('" . $l_error . "');
                                        }";
        }

        // Ajax call to get the data for the second dialog box.
        if ($l_arParams['p_ajaxTable'] && $l_arParams['p_ajaxIdentifier']) {
            $l_arParams['p_onChange'] = rtrim($l_arParams['p_onChange'], ';');

            $l_onchange_temp = ";new Ajax.Request('?call=combobox&func=load_sub&ajax=1', {
				parameters: {
					p_id: this.value,
					p_table: '" . $l_arParams['p_strTable'] . "',
					p_child_table: '" . $l_arParams['p_ajaxTable'] . "'
				},
				method: 'post',
				onSuccess: function(transport) {
					var eltarget = $('" . $l_arParams['p_ajaxIdentifier'] . "')
						.update()
						.insert(new Element('option', {value: '-1'}).update('-'));
					\$A(transport.responseJSON).each(function(el){
						eltarget.insert(new Element('option', {value: el.id}).update(el.title));
					});
				}
			});";

            $l_onchange_temp = str_replace([
                "\t",
                "\n"
            ], '', $l_onchange_temp);

            $l_arParams['p_onChange'] .= $l_onchange_temp;
        }

        if ((isset($l_arParams['p_bEditMode']) && $l_arParams['p_bEditMode']) || isys_glob_is_edit_mode()) {
            return $l_dialog_obj->navigation_edit($this->template, $l_arParams);
        }

        return $l_dialog_obj->navigation_view($this->template, $l_arParams);
    }

    /**
     * Method for handling the module request.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template
     * @throws Exception
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Unpack module request.
        $parameters = isys_format_json::decode(base64_decode($_POST['params']), true);

        if (!isset($parameters['callback_accept'])) {
            $parameters['callback_accept'] = '';
        }

        $this->template->activate_editmode()
            ->assign('self', $parameters['id'])
            ->assign('callback_accept', $parameters['callback_accept'])
            ->assign('parent', $parameters['p_strSecTableIdentifier'])
            ->assign('parent_table', $parameters['secTable'])
            ->assign('child', $parameters['p_ajaxIdentifier'])
            ->assign('child_table', $parameters['p_ajaxTable'])
            ->assign('notnull_parameter', (bool)$parameters['p_bDbFieldNN'])
            ->assign('table', $parameters['p_strTable'])
            ->assign('condition', $parameters['condition'])
            ->assign('cat_table_object', $parameters['p_strCatTableObj'])
            ->assign('onComplete', $parameters['p_onComplete'])
            ->assign('onClose', $parameters['p_onClose'])
            ->assign('multiselect', $parameters['multiselect'])
            ->display('popup/dialog_plus.tpl');
        die;
    }
}
