<div style="min-width:900px;overflow: auto;">
	<table id="port_overview_table" class="mainTable" style="border:none;" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE_P"}]</th>
				<th>[{isys type="lang" ident="LC__CATD__PORT"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__PORT__MODE"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__LAYER2_NET"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__LAYER3_NET"}]</th>
				<th>[{isys type="lang" ident="LC__CATG__IP_ADDRESS"}]</th>
				<th>
					-
				</th>
				<th>[{isys type="lang" ident="LC__CMDB__CONNECTED_WITH"}]</th>
				<th>[{isys type="lang" ident="LC__CATG__IP_ADDRESS"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__LAYER3_NET"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__LAYER2_NET"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__PORT__MODE"}]</th>
				<th>[{isys type="lang" ident="LC__CATD__PORT"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK_TREE_CONFIG_INTERFACE_P"}]</th>
			</tr>
		</thead>
		<tbody>
			<!-- Filled by ajax -->
			<tr class="loading">
				<td colspan="13"><img src="[{$dir_images}]ajax-loading.gif" /></td>
			</tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var content_height = ($('contentWrapper').getHeight()-$('contentHeader').getHeight()-50);

		if (parseInt('[{$port_count}]') > 0) {
			new Ajax.Request('?ajax=1&call=ports&func=load_port_overview',  {
				parameters: {
					'[{$smarty.const.C__CMDB__GET__OBJECT}]': parseInt('[{$obj_id}]')
				},
				method: "post",
				onSuccess: function(transport) {
					var json = transport.responseJSON,
						i,
						$table = $('port_overview_table'),
						$tbody = $table.down('tbody');

					for (i in json) {
						if (json.hasOwnProperty(i)) {

							$tbody.insert(new Element('tr', {className:(i%2 ? 'line0' : 'line1')})
								.insert(new Element('td').update(json[i]._first.interface))
								.insert(new Element('td').update(json[i]._first.link))
								.insert(new Element('td').update(json[i]._first.mode))
								.insert(new Element('td').update(json[i]._first.layer2))
								.insert(new Element('td').update(json[i]._first.layer3))
								.insert(new Element('td').update(json[i]._first.ip_address))
								.insert(new Element('td').update('-'))
								.insert(new Element('td').update(json[i]._last.obj_link))
								.insert(new Element('td').update(json[i]._last.ip_address))
								.insert(new Element('td').update(json[i]._last.layer3))
								.insert(new Element('td').update(json[i]._last.layer2))
								.insert(new Element('td').update(json[i]._last.mode))
								.insert(new Element('td').update(json[i]._last.link))
								.insert(new Element('td').update(json[i]._last.interface)));
						}
					}

					if($table.getHeight() > content_height)
					{
						$('scroller').setStyle({'height':content_height+'px'});
						$table.up('div').setStyle({'height':(content_height-10)+'px'});
					}

					$$('table tr.loading').invoke('remove');
				}
			});
		}
		else
		{
            var tbody = $('port_overview_table').down('tbody');
            tbody.insert(new Element('tr', {className:'line0'})
                    .insert(new Element('td', {colspan:13}).update('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS_CABLING__NO_PORTS"}]')));
            $$('table tr.loading').invoke('remove');
		}
	}());
</script>