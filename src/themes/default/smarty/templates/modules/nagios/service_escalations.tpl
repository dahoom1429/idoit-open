<input type="hidden" name="id" value="[{$seID}]" />

<table class="contentTable">
	<tr>
		<td class="key">Name: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__COMMAND_NAME" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">contacts: </td>
		<td class="value">[{isys type="f_dialog_list" name="C__MODULE__NAGIOS__CONTACTS" tab="20"}]</td>
	</tr>
	<tr>
		<td class="key">first_notification: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__FIRST_NOTIFICATION" tab="30"}]</td>
	</tr>
	<tr>
		<td class="key">last_notification: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__LAST_NOTIFICATION" tab="30"}]</td>
	</tr>
	<tr>
		<td class="key">notification_interval: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__NOTIFICATION_INTERVAL" tab="40"}]</td>
	</tr>
	<tr>
		<td class="key">escalation_period: </td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__NAGIOS__ESCALATION_PERIOD" tab="50"}]</td>
	</tr>
	<tr>
		<td class="key">escalation_options_interval: </td>
		<td class="value">[{isys type="f_dialog_list" name="C__MODULE__NAGIOS__ESCALATION_OPTIONS" tab="60"}]</td>
	</tr>
</table>