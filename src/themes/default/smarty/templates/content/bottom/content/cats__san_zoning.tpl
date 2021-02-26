<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__SAN_ZONING__MEMBERS" name="C__CATS__SAN_ZONING__MEMBERS__VIEW"}]</td>
		<td class="value">
            [{isys
				title="LC__BROWSER__TITLE__FC_PORT"
				name="C__CATS__SAN_ZONING__MEMBERS"
				id="C__CATS__SAN_ZONING__MEMBERS"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				multiselection=true
				callback_accept="idoit.callbackManager.triggerCallback('san_zoning_obj_attach');"
				callback_detach="idoit.callbackManager.triggerCallback('san_zoning_obj_attach');"}]
		</td>
	</tr>
</table>

<table class="mainTable border-top" id="fc_port_table">
	<thead>
	    <tr>
	        <th style="width:10%;">[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TYPE"}]</th>
		    <th style="width:20%;">[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]</th>
		    <th style="width:35%;">[{isys type="lang" ident="LC__STORAGE_FCPORT"}]</th>
		    <th style="width:35%;">[{isys type="lang" ident="LC__CATG__CONTROLLER_FC_PORT_NODE_WWN"}]</th>
	    </tr>
    </thead>
	<tbody>
    </tbody>
</table>

<input type="hidden" name="C__CATS__SAN_ZONING__FC_PORTS" id="C__CATS__SAN_ZONING__FC_PORTS" />
<input type="hidden" name="C__CATS__SAN_ZONING__WWNS" id="C__CATS__SAN_ZONING__WWNS" />

<script>
	(function () {
		"use strict";

		var $fc_port_table = $('fc_port_table'),
			data = '[{$data|escape:"javascript"}]'.evalJSON();

		idoit.callbackManager.registerCallback('san_zoning_obj_attach', function () {
			new Ajax.Request('?ajax=1&call=ports&func=load_fc_ports',
				{
					parameters:{
						'[{$smarty.const.C__CMDB__GET__OBJECT}]':$F('C__CATS__SAN_ZONING__MEMBERS__HIDDEN'),
						'[{$smarty.const.C__CMDB__GET__CATLEVEL}]':'[{$cat_id}]'
					},
					method:'post',
					onSuccess:function (transport) {

						is_json_response(transport, true);

						data = transport.responseJSON;

						draw_table();
					}
				});
		});

		var populate_form_fields = function () {
			var fc_port = [],
				wwn = [];

			$fc_port_table.select('tbody tr').each(function ($el) {
				if ($el.down('input.fc-port').checked) {
					fc_port.push($el.down('input.fc-port').getValue());
				}

				if ($el.down('input.wwn').checked) {
					wwn.push($el.down('input.wwn').getValue());
				}
			});

			$('C__CATS__SAN_ZONING__FC_PORTS').setValue(fc_port.join(','));
			$('C__CATS__SAN_ZONING__WWNS').setValue(wwn.join(','));
		};

		var draw_table = function() {
			var i,
				i2,
				fc_port,
				$fc_port_table_body = $fc_port_table.down('tbody').update(),
				table_row_classes = ['CMDBListElementsEven', 'CMDBListElementsOdd'],
				cnt = 0,
				form_fc_port,
				form_wwn;

			// Iterate over objects
			for (i in data) {
				if (data.hasOwnProperty(i)) {

					// Iterate over fc-ports
					for (i2 in data[i]) {
						if (data[i].hasOwnProperty(i2)) {
							fc_port = data[i][i2];

							form_fc_port = new Element('label')
								.update(new Element('input', {type:'checkbox', value:fc_port.fc_port_id, className:'vam fc-port', checked:fc_port.fc_port_selected}))
								.insert(new Element('span', {className:'ml5 vam'}).update(fc_port.fc_port_title));

							form_wwn = new Element('label')
								.update(new Element('input', {type:'checkbox', value:fc_port.fc_port_id, className:'vam wwn', checked:fc_port.wwn_selected, disabled:!fc_port.wwn_available}))
								.insert(new Element('span', {className:'ml5 vam'}).update(fc_port.wwn_title));

							$fc_port_table_body.insert(
								new Element('tr', {className: table_row_classes[cnt%2]})
									.update(new Element('td').update(fc_port.obj_type_title))
									.insert(new Element('td').update(fc_port.obj_title))
									.insert(new Element('td').update(form_fc_port))
									.insert(new Element('td').update(form_wwn))
							);

							cnt ++;
						}
					}
				}
			}

			$fc_port_table.select('input').invoke('stopObserving').invoke('on', 'change', populate_form_fields);

			[{if ! isys_glob_is_edit_mode()}]
			$fc_port_table.select('input').invoke('writeAttribute', 'disabled');
			[{/if}]

			populate_form_fields();
		};

		// Initial call for loading the FC Port list.
		draw_table();
	}());
</script>