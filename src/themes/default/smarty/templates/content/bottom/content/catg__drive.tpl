<input type="hidden" name="C__CATD__DRIVE_TYPE" value="1">

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__DRIVE_LETTER" ident="LC__CATD__DRIVE_LETTER"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATD__DRIVE_LETTER" p_bStripSlashes=0}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__DRIVE_TITLE" ident="LC__CATD__DRIVE_TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATD__DRIVE_TITLE"}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mt5 mb5"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__DRIVE__SYSTEM_DRIVE" ident="LC__CMDB__CATG__DRIVE__SYSTEM_DRIVE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__DRIVE__SYSTEM_DRIVE" p_bDbFieldNN="1" p_strClass="input input-small"}]</td>
    </tr>

    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__DRIVE_FILESYSTEM" ident="LC__CATD__DRIVE_FILESYSTEM"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATD__DRIVE_FILESYSTEM" p_strTable="isys_filesystem_type" p_strClass="input input-small"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__DRIVE_CAPACITY" ident="LC__CATD__DRIVE_CAPACITY"}]</td>
        <td class="value">
            [{isys type="f_text" name="C__CATD__DRIVE_CAPACITY"}]
            [{isys type="f_dialog" name="C__CATD__DRIVE_UNIT" p_strTable="isys_memory_unit"}]
        </td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__DRIVE__FREE_SPACE" ident="LC__CMDB__CATG__DRIVE__FREE_SPACE"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CMDB__CATG__DRIVE__FREE_SPACE"}]
			[{isys type="f_dialog" name="C__CMDB__CATG__DRIVE__FREE_SPACE_UNIT" p_strTable="isys_memory_unit"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__DRIVE__USED_SPACE" ident="LC__CMDB__CATG__DRIVE__USED_SPACE"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CMDB__CATG__DRIVE__USED_SPACE"}]
			[{isys type="f_dialog" name="C__CMDB__CATG__DRIVE__USED_SPACE_UNIT" p_strTable="isys_memory_unit"}]
		</td>
	</tr>
	<tr>
        <td class="key">[{isys type='f_label' name='C__CATD__DRIVE_SERIAL' ident="LC__CMDB__CATG__SERIAL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATD__DRIVE_SERIAL"}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mt5 mb5" />
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__DRIVE_DEVICE" ident="LC__CATD__DRIVE_DEVICE"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATD__DRIVE_DEVICE" p_strClass="input input-small"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATD__SOFTWARE_RAID" ident="LC__CATD_DRIVE_TYPE__RAID_GROUP"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATD__SOFTWARE_RAID" p_strClass="input input-small"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__DRIVE_FIRMWARE' ident="LC__CMDB__CATG__DRIVE__FIRMWARE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__DRIVE_FIRMWARE"}]</td>
    </tr>
</table>