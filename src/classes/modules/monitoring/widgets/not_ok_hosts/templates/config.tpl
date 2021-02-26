<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-monitoring-config-host" ident="LC__CATG__MONITORING__INSTANCE"}]
		</td>
		<td class="value">
			[{isys type="f_dialog" id="widget-monitoring-config-host" name="widget-monitoring-config-host" p_arData=$rules.hosts p_strSelectedID=$rules.selected_host p_strClass="normal"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function() {
		"use strict";

		var host = $('widget-monitoring-config-host');

		host.observe('change', function () {
		    $('widget-popup-config-changed').setValue('1');
		    $('widget-popup-config-hidden').setValue(Object.toJSON({
		        host:host.getValue()
		    }));
		}).simulate('change');
	})();
</script>