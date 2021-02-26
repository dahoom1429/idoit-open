<h3>[{isys type="lang" ident="LC__CMDB__CATG__CAPACITY"}]</h3>
<table>
	<tr>
		<td></td>
		<td>[{isys type="lang" ident="LC__WIDGET__CALCULATOR__MEMORY_CALCULATOR__EXACT_CALCULATION"}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-memory-accuracy" p_arData=$rules.yes_no p_bDbFieldNN="1" p_strClass="input-mini"}]</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-memory-from" p_strClass="input-small" p_bInfoIconSpacer="0"}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-memory-from-type" p_strClass="input-mini" p_arData=$rules.memory_unit p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td>=</td>
		<td>[{isys type="f_text" name="[{$unique_id}]_calculate-memory-result" p_bReadonly="1" p_strClass="input-small" p_bInfoIconSpacer="0"}]</td>
		<td>[{isys type="f_dialog" name="[{$unique_id}]_calculate-memory-to-type" p_strClass="input-mini" p_arData=$rules.memory_unit p_bDbFieldNN="1"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var memory_units = {
				"C__MEMORY_UNIT__B": 1,
				"C__MEMORY_UNIT__KB": 1024,
				"C__MEMORY_UNIT__MB": Math.pow(1024, 2),
				"C__MEMORY_UNIT__GB": Math.pow(1024, 3),
				"C__MEMORY_UNIT__TB": Math.pow(1024, 4)
			},
			memory_units_inaccurate = {
				"C__MEMORY_UNIT__B": 1,
				"C__MEMORY_UNIT__KB": 1000,
				"C__MEMORY_UNIT__MB": Math.pow(1000, 2),
				"C__MEMORY_UNIT__GB": Math.pow(1000, 3),
				"C__MEMORY_UNIT__TB": Math.pow(1000, 4)
			},
			calculate_memory = function () {
				var $value_from = $('[{$unique_id}]_calculate-memory-from'),
					value_from = $value_from.getValue().replace(/[^0-9\.]+/g, ''),
					$type_from = $('[{$unique_id}]_calculate-memory-from-type'),
					type_from,
					$type_to = $('[{$unique_id}]_calculate-memory-to-type'),
					type_to;

				$value_from.setValue(value_from);

				if ($('[{$unique_id}]_calculate-memory-accuracy').getValue() == 1) {
					type_from = memory_units[$type_from.getValue()];
					type_to = memory_units[$type_to.getValue()];
				}
				else {
					type_from = memory_units_inaccurate[$type_from.getValue()];
					type_to = memory_units_inaccurate[$type_to.getValue()];
				}
				var value_from_byte = value_from * type_from;

				$('[{$unique_id}]_calculate-memory-result').value = value_from_byte / type_to;
			};

		$('[{$unique_id}]_calculate-memory-accuracy', '[{$unique_id}]_calculate-memory-from', '[{$unique_id}]_calculate-memory-from-type', '[{$unique_id}]_calculate-memory-to-type')
			.invoke('on', 'change', calculate_memory);

		$('[{$unique_id}]_calculate-memory-from').on('keyup', calculate_memory);
	})();
</script>