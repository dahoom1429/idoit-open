<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-url" ident="Feed URL"}]
		</td>
		<td class="value">
			[{isys type="f_text" id="widget-popup-config-url" name="widget-popup-config-url" p_strPlaceholder="https://www.i-doit.com/feed/" p_strStyle="width:80%;" p_strValue=$rules.url p_onChange="on_value_change();"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-count" ident="LC__WIDGET__RSS__CONFIG__COUNT"}]
		</td>
		<td class="value">
			[{isys type="f_count" id="widget-popup-config-count" name="widget-popup-config-count" p_strValue=$rules.count p_onChange="on_value_change();" p_strClass="input-mini"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	on_value_change = function ()
	{
		$('widget-popup-config-hidden').setValue(
			Object.toJSON({
				url: $F('widget-popup-config-url'),
				count: parseInt($F('widget-popup-config-count'))
			})
		);
		$('widget-popup-config-changed').setValue('1');
	};

	$('widget-popup-config-count').on('change', on_value_change);
</script>
