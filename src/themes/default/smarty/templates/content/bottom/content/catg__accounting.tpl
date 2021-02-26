<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_INVENTORY_NO" ident="LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCOUNTING_INVENTORY_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING__ACCOUNT" ident="LC__CMDB__CATG__ACCOUNTING_ACCOUNT"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__ACCOUNTING__ACCOUNT" p_strTable="isys_account"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_ORDER_DATE" ident="LC__CMDB__CATG__ACCOUNTING_ORDER_DATE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATG__ACCOUNTING_ORDER_DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_DELIVERY_DATE" ident="LC__CMDB__CATG__ACCOUNTING_DELIVERY_DATE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATG__ACCOUNTING_DELIVERY_DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_ACQUIRE__VIEW" ident="LC__CMDB__CATG__ACCOUNTING_DATE_OF_INVOICE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATG__ACCOUNTING_ACQUIRE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__PURCHASE_CONTACT__VIEW" ident="LC__CMDB__CATG__GLOBAL_PURCHASED_AT"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CONTACT"
				name="C__CATG__PURCHASE_CONTACT"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
				multiselection="true"
				p_bReadonly="1"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_PRICE" ident="LC__CMDB__CATG__GLOBAL_PRICE"}]</td>
		<td class="value">[{isys type="f_money_number" name="C__CATG__ACCOUNTING_PRICE"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING__OPERATION_EXPENSE" ident="LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE"}]</td>
        <td class="value">[{isys type="f_money_number" name="C__CATG__ACCOUNTING__OPERATION_EXPENSE"}][{isys type="f_dialog" name="C__CATG__ACCOUNTING__OPERATION_EXPENSE_INTERVAL"}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_COST_UNIT" ident="LC__CMDB__CATG__ACCOUNTING_COST_UNIT"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__ACCOUNTING_COST_UNIT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_ORDER_NO" ident="LC__CMDB__CATG__GLOBAL_ORDER_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCOUNTING_ORDER_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_DELIVERY_NOTE_NO" ident="LC__CMDB__CATG__ACCOUNTING_DELIVERY_NOTE_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCOUNTING_DELIVERY_NOTE_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_INVOICE_NO" ident="LC__CMDB__CATG__GLOBAL_INVOICE_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__ACCOUNTING_INVOICE_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_PROCUREMENT" ident="LC__CMDB__CATG__ACCOUNTING_PROCUREMENT"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__ACCOUNTING_PROCUREMENT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD" ident="LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD__BASE"}][{isys type="f_text" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD"}][{isys type="f_dialog" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD_UNIT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE" ident="LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__ACCOUNTING_GUARANTEE_STATUS" ident="LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__ACCOUNTING_GUARANTEE_STATUS"}]</td>
	</tr>
</table>