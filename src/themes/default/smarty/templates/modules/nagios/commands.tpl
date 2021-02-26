<input type="hidden" name="id" value="[{$cID}]" />

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="Name" name="C__MODULE__NAGIOS__COMMAND_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__COMMAND_NAME"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Commandline" name="C__MODULE__NAGIOS__COMMAND_LINE"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__MODULE__NAGIOS__COMMAND_LINE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CAT__COMMENTARY" name="C__MODULE__NAGIOS__COMMAND_DESCRIPTION"}]</td>
		<td>[{isys type="f_wysiwyg" name="C__MODULE__NAGIOS__COMMAND_DESCRIPTION"}]</td>
	</tr>
</table>