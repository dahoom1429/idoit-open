<table class="contentTable">
	[{if $new}]
	[{isys type='f_title_suffix_counter' name='C__CATG__CONNECTOR__SUFFIX' title_identifier='C__UNIVERSAL__TITLE' label_counter='LC__CMDB__CATG__CONNECTOR__NUMBER_NEW'}]

	<tr><td colspan="2"><hr /></td></tr>
	[{/if}]
	<tr>
		<td class="key">[{isys type='f_label' name='C__UNIVERSAL__TITLE' ident="LC__CMDB__CATG__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__UNIVERSAL__TITLE" id="C__UNIVERSAL__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='inout' ident="LC__CATG__CONNECTOR__INOUT"}]</td>
		<td class="value">[{isys type="f_dialog" id="inout" name="C__CATG__CONNECTOR__INOUT" p_bDbFieldNN="1"}]</td>
	</tr>

	[{if $new}]
	<tr id="applicable_outputs">
		<td class="key"></td>
		<td class="value">[{isys type="checkbox" id="C__CATG__CONNECTOR__CREATE_APPLICABLE_OUTPUTS" name="C__CATG__CONNECTOR__CREATE_APPLICABLE_OUTPUTS" p_strTitle="LC__CATG__CONNECTOR__CREATE_APPLICABLE_OUTPUTS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__SUFFIX_SCHEMA' ident="LC__CATG__CONNECTOR__SUFFIX_SCHEMA"}]</td>
		<td class="value">
			[{isys type="f_dialog" id="C__CATG__CONNECTOR__SUFFIX_SCHEMA" name="C__CATG__CONNECTOR__SUFFIX_SCHEMA" p_bDbFieldNN="1" p_strClass="input-small"}]
			[{isys type="f_text" p_strStyle="display:none;" name="C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN" id="C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN" p_strClass="input-small"}]
		</td>
	</tr>
    <tr id="C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN__HELP_TEXT" style="display: none">
        <td class="key"></td>
        <td class="value">
            <div class="box box-blue ml20 input-group input-size-normal" style="box-sizing:border-box;">
                <div class="p5">
                    <div class="mb10">
                        <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /> [{isys type="lang" ident="LC__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN__HELP_TEXT"}]
                    </div>
                    <table>
                        <tr >
                            <td><span class="bg-white p5 mr20 text-center" style="display:block">##INPUT##</span></td>
                            <td>[{isys type="lang" ident="LC__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN__HELP_TEXT_INPUT"}]</td>
                        </tr>
                        <tr></tr>
                        <tr>
                            <td><span class="bg-white p5 mr20 text-center" style="display:block">##COUNT##</span></td>
                            <td>[{isys type="lang" ident="LC__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN__HELP_TEXT_COUNT"}]</td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
	[{/if}]

	<tr><td colspan="2"><hr /></td></tr>

	[{if $C__CATG__CONNECTOR__CATEGORY_TYPE && !$new}]
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__CATEGORY_TYPE' ident="LC__CATG__CONNECTOR__CATEGORY_TYPE"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__CONNECTOR__CATEGORY_TYPE" p_strValue=$C__CATG__CONNECTOR__CATEGORY_TYPE}]</td>
	</tr>
	[{/if}]
	<tr id="sibling_out" [{if isset($out)}]style="display:none;"[{/if}]>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__SIBLING_OUT' ident="LC__CATG__CONNECTOR__SIBLING_OUT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__CONNECTOR__SIBLING_OUT"}]</td>
	</tr>
	<tr id="sibling_in" [{if isset($in)}]style="display:none;"[{/if}]>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__SIBLING_IN' ident="LC__CATG__CONNECTOR__SIBLING_IN"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__CONNECTOR__SIBLING_IN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__CONNECTED_NET__VIEW' ident="LC__CATG__CONNECTOR__CONNECTED_NET"}]</td>
		<td class="value">
			[{isys
			title="LC__BROWSER__TITLE__WIRING_SYSTEM"
			name="C__CATG__CONNECTOR__CONNECTED_NET"
			type="f_popup"
			p_strPopupType="browser_object_ng"
			catFilter="C__CATS__WS;C__CATS__WS_ASSIGNMENT;C__CATS__WS_NET_TYPE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__CONNECTION_TYPE' ident="LC__CATG__CONNECTOR__CONNECTION_TYPE"}]</td>
		<td class="value pl20">
			[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__CONNECTOR__CONNECTION_TYPE"}]
			[{$connection_type}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__INTERFACE' ident="LC__CATG__CONNECTOR__INTERFACE"}]</td>
		<td class="value">
			[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__CONNECTOR__INTERFACE" p_strClass="input"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS' ident="LC__CATG__CONNECTOR__FIBER_WAVE_LENGTHS"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS" p_strTable="fiber_wave_lengths"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__ASSIGNED_CONNECTOR__VIEW' ident="LC__CATG__CONNECTOR__ASSIGNED_CONNECTOR"}]</td>
		<td class="value">
			[{isys
			title="LC__POPUP__BROWSER__UI_CON_SELECTION"
			name="C__CATG__CONNECTOR__ASSIGNED_CONNECTOR"
			type="f_popup"
			p_strPopupType="browser_cable_connection_ng"
			secondSelection=true
			multiselection=true
            usageWarning="LC__BROWSER_CABLE_CONNECTION__ERROR"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__CABLE__VIEW' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
		<td class="value">[{isys name="C__CATG__CONNECTOR__CABLE" type="f_popup" p_strPopupType="browser_object_ng"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__USED_FIBER_LEAD_RX' ident="LC__CATG__CONNECTOR__USED_FIBER_LEAD_RX"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__CONNECTOR__USED_FIBER_LEAD_RX"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONNECTOR__USED_FIBER_LEAD_TX' ident="LC__CATG__CONNECTOR__USED_FIBER_LEAD_TX"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__CONNECTOR__USED_FIBER_LEAD_TX"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var $cable_view = $('C__CATG__CONNECTOR__CABLE__VIEW'),
			$suffix_schema = $('C__CATG__CONNECTOR__SUFFIX_SCHEMA'),
			$suffix_schema_own = $('C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN'),
            $suffix_schema_own_help_text = $('C__CATG__CONNECTOR__SUFFIX_SCHEMA_OWN__HELP_TEXT'),
			$create_output = $('C__CATG__CONNECTOR__CREATE_APPLICABLE_OUTPUTS'),
			$outputs = $('applicable_outputs'),
			$inout = $('inout'),
			$sibling_out = $('sibling_out'),
			$sibling_in = $('sibling_in');

		if ($cable_view) {
			if ($cable_view.getValue().blank()) {
				$cable_view.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}][{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY_SUFFIX" p_bHtmlEncode=false}]');
			}
		}

		if ($suffix_schema) {
			$suffix_schema.on('change', function () {
				if ($suffix_schema.getValue() == '-1') {
					$suffix_schema_own.show();
					$suffix_schema_own_help_text.show();
				} else {
					$suffix_schema_own.hide();
					$suffix_schema_own_help_text.hide();
				}
			});
		}

		if ($inout) {
			$inout.on('change', function () {
				if ($inout.getValue() == '[{$smarty.const.C__CONNECTOR__INPUT}]') {
					$sibling_out.show();
					$sibling_in.hide();

					if ($outputs) {
						$outputs.show();
					}
				} else {
					$sibling_out.hide();
					$sibling_in.show();

					if ($outputs) {
						$outputs.hide();
					}
				}
			});
		}

		if ($create_output) {
			$create_output.on('click', function () {
				if ($create_output.checked) {
					$sibling_in.hide();
					$sibling_out.hide();
				} else {
					$sibling_out.show();
				}
			});
		}

		idoit.callbackManager.registerCallback('catg_connector.attachFiberLead', function () {
			var cableObjectID = $F('C__CATG__CONNECTOR__CABLE__HIDDEN'),
				$usedFiberLeadRX = $('C__CATG__CONNECTOR__USED_FIBER_LEAD_RX'),
				$usedFiberLeadTX = $('C__CATG__CONNECTOR__USED_FIBER_LEAD_TX');

			// Reset property fields:
			idoit.callbackManager.triggerCallback('catg_connector.detachFiberLead');

			new Ajax.Request(
				'?call=connector&method=get_fiber_lead&ajax=1', {
					method: 'POST',
					parameters: {
						"cable_object_id": cableObjectID,
						"connector_id": [{$cateID}]
					},
					onSuccess: function (response) {
						try {
							response.responseJSON.each(function (fiberLead) {
								if (Object.isUndefined(fiberLead['isys_catg_fiber_lead_list__id'])) {
									throw 'unknown fiber/lead ID';
								}

								if (fiberLead['isys_cable_colour__title'] == null) {
									fiberLead['isys_cable_colour__title'] = '[{isys type="lang" ident="LC__CATG__CONNECTOR__UNKNOWN_FIBER_LEAD_COLOR"}]';
								}

								var $optionRX = new Element('option', {value: fiberLead['isys_catg_fiber_lead_list__id']})
									.update(fiberLead['isys_catg_fiber_lead_list__label'] + ' (' + fiberLead['isys_cable_colour__title'] + ')');

								if (fiberLead['disabled'] !== undefined && fiberLead['disabled'] === true) {
									$optionRX.disable();
								}

								$usedFiberLeadRX.insert($optionRX);
								$usedFiberLeadTX.insert($optionRX.clone(true));
							});
						} catch (e) {
							idoit.Notify.error('unable to fetch fibers/leads for cable with ID "' + cableObjectID + '": ' + e, {sticky: true});
						}
					},
					onFailure: function (response) {
						idoit.Notify.error('unable to fetch fibers/leads for cable with ID "' + cableObjectID + '": ' + response.responseText, {sticky: true});
					}
				});
		});

		idoit.callbackManager.registerCallback('catg_connector.detachFiberLead', function () {
			// Because of "references" we need to insert each empty option indiviudally.
			$('C__CATG__CONNECTOR__USED_FIBER_LEAD_RX').update(new Element('option', {value:'-1'}).update('-'));
			$('C__CATG__CONNECTOR__USED_FIBER_LEAD_TX').update(new Element('option', {value:'-1'}).update('-'));
		});

		[{if isys_glob_is_edit_mode()}]
		var $wavelength_input = $('C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS'),
			wavelength_chosen = null;

		// Function for refreshing the DNS domain chosen.
		idoit.callbackManager
			.registerCallback('cmdb-catg-connector-wavelength-update', function (selected) {
				if (wavelength_chosen !== null) {
					wavelength_chosen.destroy();
				}

				$wavelength_input.setValue(selected).fire('chosen:updated');
				wavelength_chosen = new Chosen($wavelength_input, {
					disable_search_threshold: 10,
					search_contains:          true
				});
			})
			.triggerCallback('cmdb-catg-connector-wavelength-update', $F('C__CATG__CONNECTOR__FIBER_WAVE_LENGTHS'));
		[{/if}]
	}());
</script>
