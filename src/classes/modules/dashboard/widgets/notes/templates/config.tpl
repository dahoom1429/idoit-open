<table class="contentTable" id="config_[{$unique_id}]">
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-title" ident="LC__WIDGET__NOTES__CONFIG__TITLE"}]</td>
		<td class="value">[{isys type="f_text" id="widget-popup-config-title" name="widget-popup-config-title" p_strValue=$rules.title p_strClass="input-small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-color" ident="LC__WIDGET__NOTES__CONFIG__COLOR"}]</td>
		<td class="value">[{isys type="f_text" id="widget-popup-config-color" name="widget-popup-config-color" p_strValue=$rules.color p_strClass="input-small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="widget-popup-config-fontcolor" ident="LC__WIDGET__NOTES__CONFIG__FONTCOLOR"}]</td>
		<td class="value">[{isys type="f_text" id="widget-popup-config-fontcolor" name="widget-popup-config-fontcolor" p_strValue=$rules.fontcolor p_strClass="input-small"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="widget_popup_config_note" ident="LC__WIDGET__NOTES__CONFIG__NOTE"}]</td>
		<td>[{isys type="f_wysiwyg" name="widget_popup_config_note" p_strStyle="width:400px;" entities=false p_strValue=$rules.note}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		new jscolor.color($('widget-popup-config-color'));
		new jscolor.color($('widget-popup-config-fontcolor'));

		// This should work just fine, because this event will be observed before the one that actually updates the widget.
		$('widget-popup-accept').on('click', function () {
			var data = {
				title: $F('widget-popup-config-title'),
				color: '#' + $F('widget-popup-config-color'),
				fontcolor: '#' + $F('widget-popup-config-fontcolor'),
				note: $F('widget_popup_config_note')
			};

			$('widget-popup-config-hidden').setValue(Object.toJSON(data));
			$('widget-popup-config-changed').setValue('1');
		});
	})();
</script>