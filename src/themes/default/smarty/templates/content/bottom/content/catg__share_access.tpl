<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS' ident="LC__CMDB__CATG__SHARE_ACCESS__ASSIGNED_OBJECT"}]</td>
		<td class="value">[{isys name="C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS" type="f_popup" p_strPopupType="browser_object_ng" multiselection="0" callback_accept="get_shares_from_object(\$F('C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS__HIDDEN'), 'C__CATG__SHARE_ACCESS__SHARE');" callback_detach="get_shares_from_object(\$F('C__CATG__SHARE_ACCESS__ASSIGNED_OBJECTS__HIDDEN'), 'C__CATG__SHARE_ACCESS__SHARE');"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__SHARE_ACCESS__SHARE' ident="LC__CMDB__CATG__SHARES"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__SHARE_ACCESS__SHARE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__SHARE_ACCESS__MOUNTPOINT' ident="LC__CMDB__CATG__SHARE_ACCESS__MOUNTPOINT"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__SHARE_ACCESS__MOUNTPOINT"}]</td>
	</tr>
</table>