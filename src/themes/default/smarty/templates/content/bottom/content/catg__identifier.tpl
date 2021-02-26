[{*
Smarty template for global category for custom identifiers
@ author: Selcuk Kekec <skekec@i-doit.com>
@ copyright: synetics GmbH
@ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__KEY' ident='LC__CMDB__CATG__IDENTIFIER__KEY'}]</td>
		<td class="value">
            [{isys type='f_text' name='C__CMDB__CATG__IDENTIFIER__KEY' tab='1'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__VALUE' ident='LC__CMDB__CATG__IDENTIFIER__VALUE'}]</td>
		<td class="value">
			[{isys type='f_text' name='C__CMDB__CATG__IDENTIFIER__VALUE' tab='2'}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__TYPE' ident='LC__CMDB__CATG__IDENTIFIER__TYPE'}]</td>
		<td class="value">
            [{isys type='f_popup' p_strPopupType='dialog_plus' name='C__CMDB__CATG__IDENTIFIER__TYPE' p_strTable='isys_catg_identifier_type' tab='3'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__GROUP' ident='LC__CMDB__CATG__IDENTIFIER__GROUP'}]</td>
		<td class="value">
			[{isys type='f_text' name='C__CMDB__CATG__IDENTIFIER__GROUP' tab='4'}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__LAST_SCAN' ident='LC__CMDB__CATG__IDENTIFIER__LAST_SCAN'}]</td>
		<td class="value">
			[{isys type='f_popup' p_strPopupType="calendar" name='C__CMDB__CATG__IDENTIFIER__LAST_SCAN' p_bReadonly=1}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__LAST_EDITED' ident='LC__CMDB__CATG__IDENTIFIER__LAST_EDITED'}]</td>
		<td class="value">
			[{isys type='f_popup' p_strPopupType="calendar" name='C__CMDB__CATG__IDENTIFIER__LAST_EDITED' p_bReadonly=1}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__IDENTIFIER__LAST_UPDATED' ident='LC__CMDB__CATG__IDENTIFIER__LAST_UPDATED'}]</td>
		<td class="value">
			[{isys type='f_popup' p_strPopupType="calendar" name='C__CMDB__CATG__IDENTIFIER__LAST_UPDATED' p_bReadonly=1}]
		</td>
	</tr>
</table>
