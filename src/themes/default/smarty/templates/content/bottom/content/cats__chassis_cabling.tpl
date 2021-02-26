<style type="text/css">
	#C__CATS__CHASSIS_CABLING .chassis-cabling-box {
		min-width: 950px;
		position: relative;
		overflow-x: auto;
	}

	#C__CATS__CHASSIS_CABLING .chassis-cabling-box table.contentTable {
		width: 100%;
		border-top: none;
		margin: 0;
	}

	#C__CATS__CHASSIS_CABLING .chassis-cabling-box h3:first-child {
		border-top: none;
	}

	#C__CATS__CHASSIS_CABLING .chassis-cabling-box h4 {
		cursor: pointer;
	}

	#C__CATS__CHASSIS_CABLING span.icon {
		display: inline-block;
		width: 16px;
		height: 16px;
	}

	#C__CATS__CHASSIS_CABLING h4 span.icon {
		background: url('[{$dir_images}]icons/silk/bullet_arrow_right.png');
	}

	#C__CATS__CHASSIS_CABLING h4.show span.icon {
		background: url('[{$dir_images}]icons/silk/bullet_arrow_down.png');
	}

	#C__CATS__CHASSIS_CABLING table.log-ports {
		width: 100%;
	}

	#C__CATS__CHASSIS_CABLING .chassis-cabling-box em {
		color: #999;
	}
</style>

<div id="C__CATS__CHASSIS_CABLING">
	[{foreach from=$objects item=object name=cabling_objects}]
	<div data-obj-id="[{$object.id}]" class="chassis-cabling-box [{if ! $smarty.foreach.cabling_objects.last}]mb15[{/if}]">
		<h3 class="p5 gradient border-top text-shadow">[{$object.title}] ([{$object.type_title}])</h3>

		<fieldset class="overview">
			<legend><span>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__INTERNAL_NETWORKING"}] ([{isys type="lang" ident="LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L"}])</span></legend>
		</fieldset>

		<table class="two-col log-ports mainTable mt5" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__CONNECTED_OBJECT"}]</th>
				</tr>
			</thead>
			<tbody>
				[{foreach from=$object.log_ports item=port}]
				<tr class="[{cycle values="line0,line1"}]" data-log-port-id="[{$port.id}]">
					<td>
						<span class="vam">[{$port.title}] [{if $port.type_title}]([{$port.type_title}])[{/if}]</span>
					</td>
					<td>
						[{if isys_glob_is_edit_mode()}]
							[{isys
								title="LC__BROWSER__TITLE__PORT"
								type="f_popup"
								p_strPopupType="browser_cable_connection_ng"
								p_strStyle="width:80%;"
								name=$port.conn_obj_browser_name
								p_bInfoIconSpacer=0
								secondSelection=true
								only_log_ports=true}]
						[{else}]
							[{if $port.conn_obj_id}]
								[{$port.conn_obj_title}]
							[{else}]
								<em>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</em>
							[{/if}]
						[{/if}]
					</td>
				</tr>
				[{/foreach}]
			</tbody>
		</table>

		[{if isys_glob_is_edit_mode()}]
		<button type="button" class="btn m10 new-log-port-button">
			<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__CREATE_NEW_CONNECTION_TO"}]</span>
		</button>

		<div class="new-log-port-box" id="new-log-port-[{$object.id}]">
			<table class="contentTable" cellpadding="0" cellspacing="0">
				<tr>
					<td class="key">[{isys type="f_label" name=$object.log_port_dialog_name ident="LC__CMDB__CATS__CHASSIS_CABLING__CONNECTED_OBJECT"}]</td>
					<td class="value">[{isys type="f_dialog" name=$object.log_port_dialog_name}]</td>
				</tr>
				<tr>
					<td class="key">[{isys type="f_label" name=$object.log_port_dialog_name ident="LC__CMDB__LAYER2_NET"}]</td>
					<td class="value">
						[{isys
							title="LC__BROWSER__TITLE__NET"
							name=$object.log_port_l2net_name
							type="f_popup"
							p_strPopupType="browser_object_ng"
							catFilter="C__CATS__LAYER2_NET"
							multiselection="true"}]
					</td>
				</tr>
				<tr>
					<td class="key"></td>
					<td class="value">
						<button type="button" class="btn create-new-log-port-button ml20 mr5" data-obj-id="[{$object.id}]">
							<img src="[{$dir_images}]icons/silk/disk.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
						</button>
						<button type="button" class="btn cancel-new-log-port-button">
							<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__CANCEL"}]</span>
						</button>
					</td>
				</tr>
			</table>
		</div>
		[{/if}]

		[{if (is_array($object.ports) && count($object.ports) > 0) || (is_array($object.fc_ports) && count($object.fc_ports) > 0)}]
		<fieldset class="overview">
			<legend><span>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__EXTERNAL_NETWORKING"}] ([{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__PHYSICAL_PORTS"}])</span></legend>
		</fieldset>

			[{if is_array($object.ports) && count($object.ports) > 0}]
			<h4 class="mt10"><span class="vam icon"></span> <span>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORTS"}] &raquo; [{$object.counter.ports}] [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__DATASETS"}]</span></h4>
			<table class="three-col listing m5" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__PORT"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLE"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__CONNECTED_OBJECT"}]</th>
				</tr>
				</thead>
				<tbody>
				[{foreach from=$object.ports item=port}]
				<tr>
					<td>[{$port.title}] [{if $port.type_title}]([{$port.type_title}])[{/if}]</td>
					<td>
						[{if $port.cable_obj_id || isys_glob_is_edit_mode()}]
							[{isys
							title="LC__BROWSER__TITLE__CABLE"
							type="f_popup"
							p_strPopupType="browser_object_ng"
							p_strStyle="width:80%;"
							name=$port.cable_obj_browser_name
							p_bInfoIconSpacer=0
							catFilter="C__CATG__CABLE;C__CATG__CABLE_CONNECTION"}]
						[{else}]
							<em>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</em>
						[{/if}]
					</td>
					<td>
						[{if $port.conn_obj_id || isys_glob_is_edit_mode()}]
							[{isys
							title="LC__BROWSER__TITLE__PORT"
							type="f_popup"
							p_strPopupType="browser_cable_connection_ng"
							p_strStyle="width:80%;"
							name=$port.conn_obj_browser_name
							p_bInfoIconSpacer=0
							secondSelection=true}]
						[{else}]
							<em>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</em>
						[{/if}]
					</td>
				</tr>
				[{/foreach}]
				</tbody>
			</table>
			[{/if}]

			[{if is_array($object.fc_ports) && count($object.fc_ports) > 0}]
			<h4><span class="vam icon"></span> <span>[{isys type="lang" ident="LC__STORAGE_FCPORT"}] &raquo; [{$object.counter.fc_ports}] [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__DATASETS"}]</span></h4>
			<table class="three-col listing m5" cellpadding="0" cellspacing="0">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__STORAGE_FCPORT"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__CONNECTOR__CABLE"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__CONNECTED_OBJECT"}]</th>
				</tr>
				</thead>
				<tbody>
				[{foreach from=$object.fc_ports item=port}]
				<tr>
					<td>[{$port.title}] [{if $port.type_title}]([{$port.type_title}])[{/if}]</td>
					<td>
						[{if $port.cable_obj_id || isys_glob_is_edit_mode()}]
							[{isys
							title="LC__BROWSER__TITLE__CABLE"
							type="f_popup"
							p_strPopupType="browser_object_ng"
							p_strStyle="width:80%;"
							name=$port.cable_obj_browser_name
							p_bInfoIconSpacer=0
							catFilter="C__CATG__CABLE;C__CATG__CABLE_CONNECTION"}]
						[{else}]
							<em>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</em>
						[{/if}]
					</td>
					<td>
						[{if $port.conn_obj_id || isys_glob_is_edit_mode()}]
							[{isys
							title="LC__BROWSER__TITLE__PORT"
							type="f_popup"
							p_strPopupType="browser_cable_connection_ng"
							p_strStyle="width:80%;"
							name=$port.conn_obj_browser_name
							p_bInfoIconSpacer=0
							secondSelection=true}]
						[{else}]
							<em>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</em>
						[{/if}]
					</td>
				</tr>
				[{/foreach}]
				</tbody>
			</table>
			[{/if}]
		[{/if}]
	</div>
	[{foreachelse}]
		<p>[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_CONNECTED_OBJECT"}]</p>
	[{/foreach}]
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		// Hide all ports, or set the className "show" - Depending on editmode.
		$$('div.chassis-cabling-box table.three-col.listing, .new-log-port-box').invoke('hide');

		// Open ports on click and add a "show" class to the "h3" element (for styling purpose).
		$$('div.chassis-cabling-box h4').invoke('on', 'click', function () {
			this.toggleClassName('show').next('table.listing').toggle();
		});

		// Display the "create new logical port" form.
		$$('.new-log-port-button').invoke('on', 'click', function () {
			Effect.BlindUp(this, {duration: 0.5});
			Effect.BlindDown(this.next('.new-log-port-box'), {duration: 0.5});
		});

		// Save the new logical port.
		$$('.create-new-log-port-button').invoke('on', 'click', function () {
			var obj_id = this.readAttribute('data-obj-id'),
				dest_obj_id = $('C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_' + obj_id).value,
				layer2_nets = $('C__CMDB__CATS__CHASSIS_CABLING__NEW_LOG_PORT_L2NET_' + obj_id + '__HIDDEN').value;

			if (obj_id > 0 && dest_obj_id > 0) {
				new Ajax.Request('?ajax=1&call=chassis&func=add_log_port_to_device',
					{
						parameters:{
							obj_id: obj_id,
							dest_obj_id: dest_obj_id,
							layer2_nets: layer2_nets
						},
						method:"post",
						onSuccess:function (transport) {
							var json = transport.responseJSON;

							for (var i=0; i < json.length; i++) {
								if (json.hasOwnProperty(i)) {
									var item = json[i],
										table = $$('div.chassis-cabling-box[data-obj-id=' + item.obj_id + '] table.log-ports tbody'),
										cssClass = 'line0';

									if (table.length > 0) {

										if (table[0].down('tr:last').hasClassName('line0')) {
											cssClass = 'line1';
										}

										table[0].insert(new Element('tr', {className:cssClass, 'data-log-port-id': item.cat_id})
											.update(new Element('td').update(new Element('span', {className: 'vam'}).update('&nbsp;' + item.title)))
											.insert(new Element('td').update(item.conn_title)));
									}
								}
							}

							Effect.BlindUp(this.up('.new-log-port-box'), {duration: 0.5});
							Effect.BlindDown(this.up('.new-log-port-box').previous('.new-log-port-button'), {duration: 0.5});

							window.create_remove_buttons();
						}.bind(this)
					});
			} else {
				idoit.Notify.info('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_OBJECT_SELECTED"}]', {life:10});
			}
		});

		$$('.cancel-new-log-port-button').invoke('on', 'click', function () {
			Effect.BlindUp(this.up('.new-log-port-box'), {duration: 0.5});
			Effect.BlindDown(this.up('.new-log-port-box').previous('.new-log-port-button'), {duration: 0.5});
		});

		// (Re-) Create buttons for removing logical ports.
		window.create_remove_buttons = function () {
			$$('button.remove').invoke('remove');

			$$('.log-ports tbody tr').each(function($el) {
				var cat_id = $el.readAttribute('data-log-port-id'),
					td = $el.down('td'),
					button = new Element('button', {type:'button', className: 'btn remove', 'data-log-port-id': cat_id}).update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png'}));

				// Set the "remove logical port" observer.
				button.on('click', window.remove_log_port);

				td.insert({top: button});
			});
		};

		window.remove_log_port = function () {
			var really_delete = confirm('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__DELETE_PORT" p_bHtmlEncode=0}]');

			if (really_delete === true) {
				var cat_id = this.up('tr').readAttribute('data-log-port-id');

				new Ajax.Request('?ajax=1&call=chassis&func=remove_log_port_from_device',
					{
						parameters:{
							cat_id: cat_id
						},
						method:"post",
						onSuccess:function (transport) {
							var json = transport.responseJSON;

							if (json.success === true) {
								if (json.removed.length == 2) {
									$$('tr[data-log-port-id=' + json.removed[0] + ']')[0].remove();
									$$('tr[data-log-port-id=' + json.removed[1] + ']')[0].remove();
								} else {
									$$('tr[data-log-port-id=' + json.removed[0] + ']')[0].remove();
								}
							}
						}
					});
			}
		};

		window.copy_value_from = function (from_obj_browser, to_obj_browser) {
			var object_browser = $$('#C__CATS__CHASSIS_CABLING .log-port-hidden-' + from_obj_browser)[0],
				log_port = object_browser.value,
				tr = object_browser.up('tr');

			if (parseInt(log_port) > 0) {
				$$('#C__CATS__CHASSIS_CABLING .log-port-' + log_port)[0].value = tr.down('td span.vam', 1).innerHTML;
				$$('#C__CATS__CHASSIS_CABLING .log-port-hidden-' + log_port)[0].value = from_obj_browser;
			} else if (parseInt(to_obj_browser) > 0) {
				$$('#C__CATS__CHASSIS_CABLING .log-port-' + to_obj_browser)[0].value = $$('#C__CATS__CHASSIS_CABLING .log-port-' + from_obj_browser)[0].value;
				$$('#C__CATS__CHASSIS_CABLING .log-port-hidden-' + to_obj_browser)[0].value = '';
			}
		};

		[{if isys_glob_is_edit_mode()}]
		window.create_remove_buttons();
		[{/if}]
	}());
</script>