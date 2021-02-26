<h3 class="p5 gradient border-bottom">Nagios Export</h3>

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__MODULE__NAGIOS__NAGIOSHOST" ident="LC__MODULE__NAGIOS__EXPORT__HOST"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__MODULE__NAGIOS__NAGIOSHOST" p_bEditMode=1 p_bDbFieldNN=1}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__EXPORT_WITH_VALIDATION" ident="LC__MODULE__NAGIOS__EXPORT__WITH_VALIDATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__EXPORT_WITH_VALIDATION" p_bEditMode=1 p_bDbFieldNN=1}]</td>
	</tr>
    <tr>
        <td></td>
        <td><button type="button" class="btn ml20" id="nagios_export_button"><span>Export</span></button></td>
    </tr>
</table>

<div id="export_result" class="mt15"></div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $export_button = $('nagios_export_button');

		if ($export_button) {
			$export_button.on('click', function () {
				$export_button.disable()
					.insert({top: new Element('img', {className: 'mr5', src: '[{$dir_images}]ajax-loading.gif'})})
					.down('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

				new Ajax.Request('[{$ajax_url}]&hid=' + $F('C__MODULE__NAGIOS__NAGIOSHOST') + '&validate=' + $F('C__EXPORT_WITH_VALIDATION'), {
					method: "get",
					onSuccess: function (transport) {
						$export_button.enable().select('img').invoke('remove');
						$export_button.down('span').update('Export');

						$('export_result').update(transport.responseText);
					}.bind(this)
				});
			});
		}
	})();
</script>
