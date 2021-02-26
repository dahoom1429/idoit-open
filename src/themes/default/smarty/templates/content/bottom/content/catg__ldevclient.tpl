<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CATG__LDEVCLIENT_TITLE' ident="LC__CATG__STORAGE_TITLE"}]
		</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__LDEVCLIENT_TITLE" tab="10"}]
		</td>
	</tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__LDEVCLIENT_PATHS' ident="LC__CMDB__FC_PATH"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_fc_port" name="C__CATG__LDEVCLIENT_PATHS" tab="70"}]</td>
	</tr>

	<tr>
		<td name="san" class="key">
			[{isys type='f_label' name='C__CATG__LDEVCLIENT_SANPOOL__VIEW' ident="LC__CMDB__CATG__LDEV_SERVER"}]
		</td>
	<td class="value">
		[{isys
			name="C__CATG__LDEVCLIENT_SANPOOL"
			type="f_popup"
			p_strPopupType="browser_object_ng"
			secondSelection="true"
			secondList="isys_cmdb_dao_category_g_sanpool::object_browser"
			secondListFormat="isys_cmdb_dao_category_g_sanpool::format_selection"}]
	</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__LDEVCLIENT_TITLE' ident="LC__CMDB__CATG__LDEV_MULTI_PATH"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_ldev_multipath" name="C__CATG__LDEVCLIENT_MULTIPATH"}]</td>
	</tr>
</table>
