<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_TITLE' ident="LC__CATD__SANPOOL_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATD__SANPOOL_TITLE" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_LUN' ident="LC__CATD__SANPOOL_LUN"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATD__SANPOOL_LUN" tab="20"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_SEGMENT_SIZE' ident="LC__CATD__SANPOOL_SEGMENT_SIZE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATD__SANPOOL_SEGMENT_SIZE" tab="30"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_CAPACITY' ident="LC__CATD__SANPOOL_CAPACITY"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATD__SANPOOL_CAPACITY" tab="40"}]
			[{isys type="f_dialog" name="C__CATD__SANPOOL_UNIT"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_DEVICES__VIEW' ident="LC__CATD__SANPOOL_DEVICES"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_sanpool" name="C__CATD__SANPOOL_DEVICES" tab="60"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATD__SANPOOL_PATHS__VIEW' ident="LC__CMDB__FC_PATH"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_fc_port" name="C__CATD__SANPOOL_PATHS" tab="70"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__LDEVCLIENT_TITLE' ident="LC__CMDB__CATG__LDEV_MULTI_PATH"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_ldev_multipath" name="C__CATD__SANPOOL_CLIENTS__MULTIPATH"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__LDEV__TIERCLASS' ident="LC__CATG__LDEV__TIERCLASS"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_ldev_multipath" name="C__CATG__LDEV__TIERCLASS"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATD__SANPOOL_CLIENTS__VIEW' ident="LC__CMDB__CATG__LDEV_CLIENT"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_object_ng" name="C__CATD__SANPOOL_CLIENTS"}]</td>
	</tr>
</table>