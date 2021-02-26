<h3>[{isys type="lang" ident="LC__CMDB__CATG__COMPUTING_RESOURCES__NETWORK_BANDWIDTH"}]</h3>
<br />
<table>
	<tr>
		<td></td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-net-from" p_strClass="input-small" p_bInfoIconSpacer="0"}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-net-from-type" p_strClass="input-mini" p_arData=$rules.net_unit p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td>=</td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-net-result" p_bReadonly="1" p_strClass="input-small" p_bInfoIconSpacer="0"}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-net-to-type" p_strClass="input-mini" p_arData=$rules.net_unit p_bDbFieldNN="1"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var net_units = {
				"C__PORT_SPEED__BIT_S": 1,
				"C__PORT_SPEED__KBIT_S": 1000,
				"C__PORT_SPEED__MBIT_S": 1000000,
				"C__PORT_SPEED__GBIT_S": 1000000000
			},
			calculate_net = function () {
				var $value_from = $('[{$unique_id}]_calculate-net-from'),
					value_from = $value_from.getValue().replace(/[^0-9\.]+/g, ''),
					value_cut = value_from.substring(0,12), //ID-4187 avoid to use more than 12 characters
					type_from = net_units[$('[{$unique_id}]_calculate-net-from-type').getValue()],
					type_to = net_units[$('[{$unique_id}]_calculate-net-to-type').getValue()],
					value_from_bit = value_cut * type_from;

				$value_from.setValue(value_cut);

				$('[{$unique_id}]_calculate-net-result').setValue(value_from_bit / type_to);
			};

		$('[{$unique_id}]_calculate-net-from', '[{$unique_id}]_calculate-net-from-type', '[{$unique_id}]_calculate-net-to-type').invoke('on', 'change', calculate_net);

		$('[{$unique_id}]_calculate-net-from').on('keyup', calculate_net);
	})();
</script>