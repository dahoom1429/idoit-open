<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__LAYER2_ID" name="C__CATS__LAYER2_ID"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__LAYER2_ID"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__LAYER2_STANDARD_VLAN" name="C__CATS__LAYER2_STANDARD_VLAN"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATS__LAYER2_STANDARD_VLAN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__LAYER2_TYPE" name="C__CATS__LAYER2_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_layer2_net_type" name="C__CATS__LAYER2_TYPE" p_bDbFieldNN="1"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__LAYER2_SUBTYPE" name="C__CATS__LAYER2_SUBTYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_layer2_net_subtype" name="C__CATS__LAYER2_SUBTYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__LAYER2__LAYER3_NET" name="C__CMDB__CATS__LAYER2__LAYER3_NET"}]</td>
		<td class="value">
			[{isys title="LC__BROWSER__TITLE__NET" name="C__CATS__LAYER2__LAYER3_NET" type="f_popup" multiselection=true p_strPopupType="browser_object_ng" catFilter="C__CATS__NET"}]
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__LAYER2__VRF" name="C__CATS__LAYER2__VRF"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__LAYER2__VRF"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__LAYER2__VRF_CAPACITY" name="C__CATS__LAYER2__VRF_CAPACITY"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__LAYER2__VRF_CAPACITY"}][{isys type="f_dialog" name="C__CATS__LAYER2__VRF_CAPACITY_UNIT"}]</td>
	</tr>
</table>

<h3 class="border-top border-bottom gradient text-shadow p5 mt5">[{isys type="lang" ident="LC__CMDB__CATS__LAYER2_NET__IP_HELPER_ADDRESS"}]</h3>
<table class="contentTable m5">
	<tr>
		<td>
			[{if isys_glob_is_edit_mode()}]
			<button id="l2net_add_iphelper" class="btn ml5 mb10" type="button">
				<img src="[{$dir_images}]icons/silk/add.png" alt="+">
				<span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
			</button>
			[{/if}]

			<table id="table_iphelper_addresses">
				[{if $ip_helper_address != false}]
				<tbody>
				[{foreach from=$ip_helper_address key="index" item="iphelper"}]
					[{assign var="tmp_counter" value=$index+1}]
					<tr id="row_[{$index+1}]">
						<td>
							<span class="ml10 bold mr10">[{isys type="lang" ident="LC__CMDB__CATS__LAYER2_TYPE"}]</span>
						</td>
						<td>
							[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_layer2_iphelper_type" name="ip_helper[$tmp_counter][type]" p_strClass="input-small ip_helper_types" p_strSelectedID=$iphelper.isys_cats_layer2_net_2_iphelper__isys_layer2_net_iphelper_type p_bInfoIconSpacer=0 callback_accept="idoit.callbackManager.triggerCallback('l2net_update_ip_helper');"}]
							[{isys type="f_text" name="ip_helper[$tmp_counter][ip]" p_strValue=$iphelper.isys_cats_layer2_net_2_iphelper__ip p_strClass="normal"}]
						</td>
						<td>
							[{if isys_glob_is_edit_mode()}]
							<button type="button" class="btn remove ml10 mr10">
								<img alt="x" src="[{$dir_images}]icons/silk/cross.png" class="mr5"><span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
							</button>
							[{/if}]
						</td>
					</tr>
					[{/foreach}]
				</tbody>
				[{else}]
				<tbody>
				<tr>
					<td>
						<p class="ml5">[{isys type="lang" ident="LC__CMDB__CATS__LAYER2_NET__NO_IP_HELPER_ADDRESSES"}]</p>
					</td>
				</tr>
				</tbody>
				[{/if}]
			</table>
		</td>
	</tr>
</table>

[{*  CONFIG  *}]
<div id="new_iphelper_template" class="hide">
    [{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_layer2_iphelper_type" name="{type_name}" id="{type_id}" p_strClass="input-small ip_helper_types" p_bInfoIconSpacer=0 callback_accept="idoit.callbackManager.triggerCallback('l2net_update_ip_helper');"}] [{isys type="f_text" name="{ip_name}" p_strClass="input-mini"}]
</div>
<input type="hidden" id="count_of_iphelpers" value="[{if $ip_helper_address != false}][{$ip_helper_address|@count}][{else}]0[{/if}]" />

<script type="text/javascript">
	(function () {
		"use strict";

		var add_iphelper = $('l2net_add_iphelper');

		if (add_iphelper) {
			add_iphelper.on('click', function () {
				var l_table = $('table_iphelper_addresses').down('tbody'),
					l_template = $('new_iphelper_template').innerHTML,
					l_count = $('count_of_iphelpers'),
					l_counter = $F('count_of_iphelpers'),
					l_next_count = parseInt(l_counter) + 1,
					l_new_row = new Element('tr', {id:'row_' + (l_next_count)});

				// Set names and ids of the templates.
				l_template = l_template.replace('{type_name}', 'ip_helper[' + (l_next_count) + '][type]');
				l_template = l_template.replace('{type_name}', 'ip_helper[' + (l_next_count) + '][type]');
				l_template = l_template.replace('{type_id}', 'ip_helper_type_' + (l_next_count));
				l_template = l_template.replace('{ip_name}', 'ip_helper[' + (l_next_count) + '][ip]');
				l_template = l_template.replace('{type_id}', 'ip_helper_ip_' + (l_next_count));

				if (l_counter == 0) {
					l_table.update();
				}

				l_new_row
					.update(new Element('td').update(new Element('span', {className:'ml10 bold mr10'}).update('[{isys type="lang" ident="LC__CMDB__CATS__LAYER2_TYPE"}]')))
					.insert(new Element('td').update(l_template))
					.insert(new Element('td').update(new Element('button', {type:'button', className:'btn remove ml10 mr10'})
						.update(new Element('img', {className:'mr5', alt:'x', src:'[{$dir_images}]icons/silk/cross.png'}))
						.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]'))));

				l_table.insert(l_new_row);

				l_count.setValue(l_next_count);
			});
		}

		$('table_iphelper_addresses').on('click', 'button.remove', function (ev) {
			ev.findElement().up('tr').remove();
		});

		idoit.callbackManager.registerCallback('l2net_update_ip_helper', function () {
			// At first we retrieve all ip-helper-types.
			new Ajax.Request('?call=combobox&func=load&ajax=1', {
				parameters: {table:'isys_layer2_iphelper_type'},
				onComplete: function(response) {
					var json = response.responseJSON;

					is_json_response(response, true);

					$$('select.ip_helper_types').each(function($el) {
						var selection = $F($el), i;

						$el.update(new Element('option', {value:-1}).update(' [{isys_tenantsettings::get('gui.empty_value', '-')}] '));

						for (i in json) {
							if (json.hasOwnProperty(i)) {
								$el.insert(new Element('option', {value:parseInt(i)}).update(json[i]));
							}
						}

						$el.setValue(selection);
					});
				}
			});
		});
	}());
</script>