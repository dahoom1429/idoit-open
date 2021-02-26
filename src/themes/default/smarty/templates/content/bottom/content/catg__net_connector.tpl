<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS' ident='LC__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS' p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO' ident='LC__CMDB__CONNECTED_WITH'}]</td>
		<td class="value">
			[{isys type='f_popup'
			placeholder="LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_PLACEHOLDER"
			name='C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO'
			callback_accept="$('C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO__HIDDEN').fire('listenerSelection:updated');"
			callback_detach="$('C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO__HIDDEN').fire('listenerSelection:updated');"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_LISTENER' ident='LC__CATG__NET_LISTENER'}]</td>
		<td class="value">
			[{isys type='f_dialog' placeholder="LC__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_PLACEHOLDER" name='C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_LISTENER'}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_CONNECTOR__GATEWAY' ident='LC__CATG__NET_CONNECTIONS__GATEWAY'}]</td>
		<td class="value">[{isys type='f_popup' name='C__CMDB__CATG__NET_CONNECTOR__GATEWAY'}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_CONNECTOR__PORT_FROM' ident='LC__UNIVERSAL__PORT_RANGE'}]</td>
        <td class="value">
	        [{isys type='f_text' name='C__CMDB__CATG__NET_CONNECTOR__PORT_FROM'}]
	        <span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">-</span>
	        [{isys type='f_text' name='C__CMDB__CATG__NET_CONNECTOR__PORT_TO'}]
        </td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var $port_from = $('C__CMDB__CATG__NET_CONNECTOR__PORT_FROM'),
			$port_to = $('C__CMDB__CATG__NET_CONNECTOR__PORT_TO'),
			$connected_to = $('C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO__HIDDEN'),
			$listener = $('C__CMDB__CATG__NET_CONNECTOR__CONNECTED_TO_LISTENER');

		if ($port_from && $port_to) {
			$port_from.on('change', function () {
				if ((! $port_from.getValue().blank() && $port_to.getValue().blank()) || (parseInt($port_from.getValue()) > parseInt($port_to.getValue()))) {
					$port_to.setValue($port_from.getValue());
				}
			});
		}

		if ($connected_to) {
			$connected_to.on('listenerSelection:updated', function () {
				var object_id = this.value;
				$listener.update(new Element('option', {value: '-1', selected: true}).insert('-'));

				new Ajax.Request('?call=connector&method=load_listeners&ajax=1', {
					parameters: {
						'id':object_id
					},
					onSuccess: function (response) {
						try {
							var json = response.responseJSON;

							for(i in json){
								if (json.hasOwnProperty(i)) {
									$listener.insert(new Element('option', {value: i}).insert(json[i]));
								}
							}
						} catch (e) {
							idoit.Notify.error('Unable to retrieve net listeners for 1Object-ID "' + object_id + '": ' + e, {sticky: true});
						}
					},
					onFailure: function (response) {
						idoit.Notify.error('Unable to retrieve net listeners for 2Object-ID "' + object_id + '": ' + response.responseText, {sticky: true});
					}
				});
			});
		}

	}());
</script>