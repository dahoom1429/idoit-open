<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-count" ident="LC__WIDGET__MYOBJECTS__CONFIG__OBJECT_COUNTER"}]</td>
		<td class="value">[{isys type="f_count" id="widget-popup-config-count" name="widget-popup-config-count" p_strValue=$rules.count p_onChange="idoit.callbackManager.triggerCallback('widget-popup-config-count-change');" p_strClass="input-mini"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		'use strict';

		var $counter = $('widget-popup-config-count');

		idoit.callbackManager.registerCallback('widget-popup-config-count-change', function () {
			$counter.setValue($counter.getValue().replace(/\D/g, ''));
			$('widget-popup-config-hidden').setValue(Object.toJSON({objects:parseInt($counter.getValue())}));
		});

		$('widget-popup-config-changed').setValue('1');
	})();
</script>