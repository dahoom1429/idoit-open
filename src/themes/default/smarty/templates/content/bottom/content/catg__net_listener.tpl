<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION' ident='LC__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION' p_strClass="normal"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__PROTOCOL' ident='LC__CMDB__CATG__NET_LISTENER__PROTOCOL'}]</td>
		<td class="value">[{isys type='f_popup' name='C__CMDB__CATG__NET_LISTENER__PROTOCOL' p_bDbFieldNN=1 p_strClass="small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__PROTOCOL_LAYER_5' ident='LC__CMDB__CATG__NET_LISTENER__LAYER_5_PROTOCOL'}]</td>
		<td class="value">[{isys type='f_popup' name='C__CMDB__CATG__NET_LISTENER__PROTOCOL_LAYER_5' p_bDbFieldNN=1 p_strClass="small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__IP_ADDRESS' ident='LC__CMDB__CATG__NET_LISTENER__IP_ADDRESS'}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CMDB__CATG__NET_LISTENER__IP_ADDRESS' p_bDbFieldNN=1 p_strClass="normal"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__GATEWAY' ident='LC__CATG__NET_CONNECTIONS__GATEWAY'}]</td>
		<td class="value">[{isys type='f_popup' name='C__CMDB__CATG__NET_LISTENER__GATEWAY'}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CMDB__CATG__NET_LISTENER__PORT_FROM' ident='LC__UNIVERSAL__PORT_RANGE'}]</td>
        <td class="value">
	        [{isys type='f_text' name='C__CMDB__CATG__NET_LISTENER__PORT_FROM'}]
	        <span class="[{if isys_glob_is_edit_mode()}]fl p5[{else}]ml5 mr5[{/if}]">-</span>
	        [{isys type='f_text' name='C__CMDB__CATG__NET_LISTENER__PORT_TO'}]
        </td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var $port_from = $('C__CMDB__CATG__NET_LISTENER__PORT_FROM'),
			$port_to = $('C__CMDB__CATG__NET_LISTENER__PORT_TO');

		if ($port_from && $port_to) {
			$port_from.on('change', function () {
				if ((! $port_from.getValue().blank() && $port_to.getValue().blank()) || (parseInt($port_from.getValue()) > parseInt($port_to.getValue()))) {
					$port_to.setValue($port_from.getValue());
				}
			});
		}
	}());
</script>