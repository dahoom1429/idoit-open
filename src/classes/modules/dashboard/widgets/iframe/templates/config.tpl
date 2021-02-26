<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-title" ident="LC__WIDGET__IFRAME__CONFIG__TITLE"}]
		</td>
		<td class="value">
			[{isys type="f_text" id="widget-popup-config-title" name="widget-popup-config-title" p_strPlaceholder="Web-Browser" p_strStyle="width:80%;" p_strValue=$rules.title p_onChange="on_value_change();"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-url" ident="URL"}]
		</td>
		<td class="value">
			[{isys type="f_text" id="widget-popup-config-url" name="widget-popup-config-url" p_strPlaceholder="https://www.i-doit.com/" p_strStyle="width:80%;" p_strValue=$rules.url p_onChange="on_value_change();"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="widget-popup-config-height" ident="LC__WIDGET__IFRAME__CONFIG__HEIGHT"}]
		</td>
		<td class="value">
			[{isys type="f_count" id="widget-popup-config-height" name="widget-popup-config-height" p_strValue=$rules.height p_onChange="on_value_change();" p_strClass="input-mini"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
    on_value_change = function () {
        $('widget-popup-config-hidden').setValue(
            Object.toJSON({
                title:  $F('widget-popup-config-title'),
                url:    $F('widget-popup-config-url'),
                height: parseInt($F('widget-popup-config-height'))
            })
        );
        $('widget-popup-config-changed').setValue('1');
    };

    $('widget-popup-config-height').on('change', on_value_change);
</script>