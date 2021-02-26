<table class="contentTable">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__REGEDIT__KEY"}]: </td>
		<td class="value">
			[{isys type="f_text" name="reg_key" p_strValue=$regdata.key}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__REGEDIT__VALUE"}]: </td>
		<td class="value">
			[{isys type="f_textarea" p_nCols="60" p_nRows="5" name="reg_val" p_strValue=$regdata.val}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__REGEDIT__DELETEABLE"}]: </td>
		<td class="value">
			<!--input type="checkbox" name="reg_deletable" /-->
			[{isys type="f_dialog" name="reg_deletable" p_arData=$deletableData p_strSelectedID=$deletableDataSelectedID}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="lang" ident="LC__REGEDIT__EDITABLE"}]: </td>
		<td class="value">[{isys type="f_dialog" name="reg_editable" p_arData=$editableData p_strSelectedID=$editableDataSelectedID}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<div style="font-weight: bold; color: #FF0000">
				[{$regdata.error}]
			</div>
		</td>
	</tr>
</table>
<input type="hidden" name="reg_action" value="" />
<input type="hidden" name="reg_id" value="[{$regdata.id}]" />