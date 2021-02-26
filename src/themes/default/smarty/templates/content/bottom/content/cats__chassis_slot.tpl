<table class="contentTable" id="editview">
	[{if $new_slot}]
		[{isys type="f_title_suffix_counter" name='C__CMDB__CATS__CHASSIS_SLOT__SUFFIX' title_identifier='C__CMDB__CATS__CHASSIS_SLOT__TITLE' label_counter='LC__CMDB__CATS__CHASSIS__NEW_SLOTS'}]
	[{/if}]
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__GLOBAL_TITLE" name="C__CMDB__CATS__CHASSIS_SLOT__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATS__CHASSIS_SLOT__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS__CONNECTOR_TYPE" name="C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATS__CHASSIS_SLOT__CONNECTOR_TYPE" p_strTable="isys_chassis_connector_type"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS__INSERTION" name="C__CMDB__CATS__CHASSIS_SLOT__INSERTION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATS__CHASSIS_SLOT__INSERTION" p_bDbFieldNN="1"}]</td>
	</tr>
	[{if ! $new_slot}]
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES" name="C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT__available_box"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CMDB__CATS__CHASSIS__ITEM_ASSIGNMENT"}]</td>
	</tr>
	[{/if}]
</table>