<table class="contentTable">
    [{if $nNewPort == "1" || $new_catg_port == "1"}]
	[{isys type='f_title_suffix_counter' name='C__CATG__PORT__SUFFIX' title_identifier='C__CATG__PORT__TITLE' label_counter='LC__CMDB__CATG__PORT__NUMBER_NEW'}]
	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>
    [{/if}]
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__TITLE' ident="LC__CMDB__CATG__PORT__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__PORT__TITLE" id="C__CATG__PORT__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__INTERFACE' ident="LC__CMDB__CATG__PORT__CON_INTERFACE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__PORT__INTERFACE"}]</td>
	</tr>
	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__TYPE' ident="LC__CMDB__CATG__PORT__TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__PORT__TYPE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__MODE' ident="LC__CMDB__CATG__PORT__MODE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__PORT__MODE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__PLUG' ident="LC__CMDB__CATG__PORT__PLUG"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__PORT__PLUG"}]</td>
	</tr>
	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__NEGOTIATION' ident="LC__CMDB__CATG__PORT__NEGOTIATION"}]</td>
		<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATG__PORT__NEGOTIATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__DUPLEX' ident="LC__CMDB__CATG__PORT__DUPLEX"}]</td>
		<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATG__PORT__DUPLEX"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__SPEED_VALUE' ident="LC__CMDB__CATG__PORT__SPEED"}]</td>
		<td class="value">
            [{isys type="f_text" name="C__CATG__PORT__SPEED_VALUE"}]
            [{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATG__PORT__SPEED"}]
        </td>
	</tr>
	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__STANDARD' ident="LC__CMDB__CATG__PORT__STANDARD"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__PORT__STANDARD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__MAC' ident="LC__CMDB__CATG__PORT__MAC"}]</td>
		<td class="value">[{isys type="f_text" p_strID="C__CATG__PORT__MAC" name="C__CATG__PORT__MAC"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__MTU' ident="LC__CMDB__CATG__PORT__MTU"}]</td>
		<td class="value">[{isys type="f_text" p_strID="C__CATG__PORT__MTU" name="C__CATG__PORT__MTU"}]</td>
	</tr>
	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__DEST' ident="LC__CMDB__CONNECTED_WITH"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__PORT"
				name="C__CATG__PORT__DEST"
				type="f_popup"
				p_strPopupType="browser_cable_connection_ng"
				secondSelection=true
				usageWarning="LC__BROWSER_CABLE_CONNECTION__ERROR"
                callback_accept="idoit.callbackManager.triggerCallback('catg_port__attached_port');"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__CABLE' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CABLE"
				name="C__CATG__PORT__CABLE"
				type="f_popup"
				p_strPopupType="browser_object_ng"}]
		</td>
	</tr>

	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>

	<tr>
		<td class="key" valign="top">[{isys type='f_label' name='C__CATG__LAYER2__DEST' ident="LC__CMDB__LAYER2_NET"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__PORT"
				name="C__CATG__LAYER2__DEST"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				multiselection="true"
				callback_accept="idoit.callbackManager.triggerCallback('port_loadlayer2VLANs');"
				callback_detach="idoit.callbackManager.triggerCallback('port_loadlayer2VLANs');"}]
		</td>
	</tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__DEFAULT_VLAN' ident="LC__CMDB__CATG__PORT__DEFAULT_VLAN"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__PORT__DEFAULT_VLAN"}]</td>
	</tr>

	<tr><td colspan="2"><hr style="margin:5px 0;" /></td></tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__PORT__ACTIVE' ident="LC__CMDB__CATG__PORT__ACTIVE"}]</td>
		<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATG__PORT__ACTIVE"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATG__PORT__IP_ADDRESS' ident="LC__CATG__IP_ADDRESS"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__PORT__IP_ADDRESS"}]</td>
	</tr>
</table>

<input type="hidden" name="port_id" value="[{$port_id}]" />

<script type="text/javascript">
	(function () {
		"use strict";

		var cableView = $('C__CATG__PORT__CABLE__VIEW');

		if (cableView) {
			if (cableView.getValue().blank()) {
				cableView.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
			}
		}

        idoit.callbackManager.registerCallback('port_loadlayer2VLANs', function () {
            new Ajax.Request('[{$port_ajax_url}]', {
                parameters: {
                    ids: $('C__CATG__LAYER2__DEST__HIDDEN').value
                },
                method: "post",
                onComplete: function (response) {
                    var i,
                        selectIndex = false,
                        previousSelection = false,
                        defaultVLAN = $('C__CATG__PORT__DEFAULT_VLAN'),
                        json = response.responseJSON;

                    if (defaultVLAN.selectedIndex == 0) {
                        selectIndex = 1;
                    } else {
                        previousSelection = defaultVLAN.options[defaultVLAN.selectedIndex].value;
                    }

                    defaultVLAN.update(new Element('option', {value: '-1', selected: true}).insert('-'));

                    for (i in json) {
                        if (json.hasOwnProperty(i)) {
                            defaultVLAN.insert(new Element('option', {value: json[i].id}).insert(json[i].title));

                            if (!selectIndex && previousSelection == json[i].id) {
                                selectIndex = defaultVLAN.options.length - 1;
                            }
                        }
                    }

                    if (selectIndex) {
                        defaultVLAN.selectedIndex = selectIndex;
                    }
                }
            });
        });

        idoit.callbackManager.registerCallback('catg_port__attached_port', function () {
            if (cableView.getValue().blank()) {
                cableView.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
                $('C__CATG__PORT__CABLE__HIDDEN').setValue('');
            }
        });
	}());
</script>