<h3>[{isys type="lang" ident="LC__WIDGET__CALCULATOR__POWER"}]</h3>
<br />
<table>
	<tr>
		<td></td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-power-from" p_strClass="input-small" p_bInfoIconSpacer=0}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-power-from-type" p_strClass="input-mini" p_arData=$rules.power_unit p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td>=</td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-power-result" p_bReadonly=1 p_strClass="input-small" p_bInfoIconSpacer=0}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-power-to-type" p_strClass="input-mini" p_arData=$rules.power_unit p_bDbFieldNN="1"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var power_units = {
				"C__REF_CAPACITY_UNIT__BTU": 3.4144247838072,
				"C__REF_CAPACITY_UNIT__WATT": 1,
				"C__REF_CAPACITY_UNIT__KWATT": 1000,
				"C__REF_CAPACITY_UNIT__MWATT": 1000000,
				"C__REF_CAPACITY_UNIT__GWATT": 1000000000
			},
			calculate_power = function () {
				var $value_from = $('[{$unique_id}]_calculate-power-from'),
					value_from = $value_from.getValue().replace(/[^0-9\.]+/g, ''),
					$type_from = $('[{$unique_id}]_calculate-power-from-type'),
					type_from = power_units[$type_from.getValue()],
					$type_to = $('[{$unique_id}]_calculate-power-to-type'),
					type_to = power_units[$type_to.getValue()],
					$calculation_result = $('[{$unique_id}]_calculate-power-result'),
					value_from_power = 0;

				$value_from.setValue(value_from);

				if ($type_from.getValue() == 'C__REF_CAPACITY_UNIT__BTU' && $type_to.getValue() != 'C__REF_CAPACITY_UNIT__BTU') {
					value_from_power = value_from / type_from;
					$calculation_result.setValue(value_from_power / type_to);
				} else if ($type_from.getValue() != 'C__REF_CAPACITY_UNIT__BTU' && $type_to.getValue() == 'C__REF_CAPACITY_UNIT__BTU') {
					value_from_power = value_from * type_from;
					$calculation_result.value = value_from_power * type_to;
				} else {
					value_from_power = value_from * type_from;
					$calculation_result.value = value_from_power / type_to;
				}
			};

		$('[{$unique_id}]_calculate-power-from', '[{$unique_id}]_calculate-power-from-type', '[{$unique_id}]_calculate-power-to-type')
			.invoke('on', 'change', calculate_power);

		$('[{$unique_id}]_calculate-power-from').on('keyup', calculate_power);
	})();
</script>