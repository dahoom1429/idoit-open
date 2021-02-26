<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CATG__SOA_STACKS__TITLE' ident="LC__CMDB__LOGBOOK__TITLE"}]
		</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__SOA_STACKS__TITLE" tab="20"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CATG__SOA_STACKS__COMPONENTS_LIST' ident='LC__CMDB__CATG__SOA_COMPONENTS'}]
		</td>
		<td class="value">
			[{isys
			type="f_dialog_list"
			name="C__CATG__SOA_STACKS__COMPONENTS_LIST"
			p_arData=$soa_components}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CATG__SOA_STACKS__IT_SERVICE__VIEW' ident='LC__CMDB__CATG__IT_SERVICE'}]
		</td>
		<td class="value">
			[{isys
				name="C__CATG__SOA_STACKS__IT_SERVICE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				multiselection=1}]
		</td>
	</tr>
</table>