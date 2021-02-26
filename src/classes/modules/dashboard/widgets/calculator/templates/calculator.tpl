<h3 class="gradient p5 text-shadow border-bottom border-grey">[{isys type="f_label" ident="LC__WIDGET__CALCULATOR"}]</h3>

<table class="m5">
	<tr>
		<td>[{isys type="f_label" ident="LC__WIDGET__CALCULATOR__TYPES" name="[{$unique_id}]_calculator-types"}]</td>
		<td>[{isys type="f_dialog" p_bDbFieldNN="0" name="[{$unique_id}]_calculator-types" p_arData=$calculator_types p_strClass="input-small"}]</td>
		<td><div id="[{$unique_id}]_calculator-messages" class="ml5 p5 box-red" style="display:none;"></div></td>
	</tr>
</table>

<div class="calculator-type-content m10" style="display:none;"></div>

<style type="text/css">
	.calculator-type-content {
		min-height: 100px;
	}
</style>

<script type="text/javascript">
(function () {
	"use strict";

	var $widget = $('[{$unique_id}]'),
		$calc_type = $('[{$unique_id}]_calculator-types'),
		$calc_container = $widget.down('.calculator-type-content');

	$calc_type.on('change', function (ev) {
		new Ajax.Updater($calc_container, '[{$ajax_url}]',
			{
				method: 'post',
				evalScripts: true,
				parameters: {
					calc_type: ev.findElement('select').getValue(),
					unique_id: '[{$unique_id}]'
				},
				onComplete: function () {
					$calc_container.appear();
				}
			}
		);
	});
})();
</script>