<input type="hidden" name="id" value="[{$tpID}]" />
 
<table class="contentTable">
	<tr>
		<td class="key">Name: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__TIMEPERIOD_NAME" p_strStyle="width:358px;" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">Alias: </td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__TIMEPERIOD_ALIAS" p_strStyle="width:358px;" tab="20"}]</td>
	</tr>
	<tr>
		<td class="key">Definition: </td>
		<td class="value">[{isys type="f_textarea" name="C__MODULE__NAGIOS__TIMEPERIOD_DEFINITION" p_strStyle="width:358px;" tab="30"}]</td>
	</tr>
	<tr>
		<td class="key">exclude: </td>
		<td class="value">[{isys type="f_dialog_list" name="C__MODULE__NAGIOS__TIMEPERIOD_EXCLUDE" p_bLinklist="1" tab="35"}]</td>
	</tr>
	<tr>
		<td class="key">Default check_period: </td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__NAGIOS__DEFAULT_CHECK_PERIOD" p_bDbFieldNN="1" tab="40"}]</td>
	</tr>
	<tr>
		<td class="key">Default notification_period: </td>
		<td class="value">[{isys type="f_dialog" name="C__MODULE__NAGIOS__DEFAULT_NOTIFICATION_PERIOD" p_bDbFieldNN="1" tab="50"}]</td>
	</tr>
</table>