[{*
Smarty template for global category for audits
@ author: Benjamin Heisig <bheisig@synetics.de>
@ copyright: synetics GmbH
@ license: <http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3>
*}]

<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__TITLE' ident='LC__CMDB__CATG__AUDIT__TITLE'}]</td>
		<td class="value">
            [{isys type='f_text' name='C__CMDB__CATG__AUDIT__TITLE' tab='1'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__TYPE' ident='LC__CMDB__CATG__AUDIT__TYPE'}]</td>
		<td class="value">
            [{isys type='f_popup' p_strPopupType='dialog_plus' name='C__CMDB__CATG__AUDIT__TYPE' p_strTable='isys_catg_audit_type' tab='2'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__COMMISSION__VIEW' ident='LC__CMDB__CATG__AUDIT__COMMISSION'}]</td>
		<td class="value">
            [{isys
				title='LC__BROWSER__TITLE__CONTACT'
				name='C__CMDB__CATG__AUDIT__COMMISSION'
				type='f_popup'
				p_strPopupType='browser_object_ng'
				catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
				multiselection='true'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__RESPONSIBLE__VIEW' ident='LC__CMDB__CATG__AUDIT__RESPONSIBLE'}]</td>
		<td class="value">
            [{isys
				title='LC__BROWSER__TITLE__CONTACT'
				name='C__CMDB__CATG__AUDIT__RESPONSIBLE'
				type='f_popup'
				p_strPopupType='browser_object_ng'
				catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
				multiselection='true'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__INVOLVED__VIEW' ident='LC__CMDB__CATG__AUDIT__INVOLVED'}]</td>
		<td class="value">
            [{isys
				title='LC__BROWSER__TITLE__CONTACT'
				name='C__CMDB__CATG__AUDIT__INVOLVED'
				type='f_popup'
				p_strPopupType='browser_object_ng'
				catFilter='C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION'
				multiselection='true'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER__VIEW' ident='LC__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER'}]</td>
		<td class="value">
            [{isys type='f_popup' name='C__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER' id='C__CMDB__CATG__AUDIT__PERIOD_MANUFACTURER' p_strPopupType='calendar' p_calSelDate='' p_bTime='0' tab='6'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__PERIOD_OPERATOR__VIEW' ident='LC__CMDB__CATG__AUDIT__PERIOD_OPERATOR'}]</td>
		<td class="value">
            [{isys type='f_popup' name='C__CMDB__CATG__AUDIT__PERIOD_OPERATOR' id='C__CMDB__CATG__AUDIT__PERIOD_OPERATOR' p_strPopupType='calendar' p_calSelDate='' p_bTime='0' tab='7'}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__APPLY__VIEW' ident='LC__CMDB__CATG__AUDIT__APPLY'}]</td>
		<td class="value">
            [{isys type='f_popup' name='C__CMDB__CATG__AUDIT__APPLY' id='C__CMDB__CATG__AUDIT__APPLY' p_strPopupType='calendar' p_calSelDate='' p_bTime='0' tab='8'}]
        </td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__RESULT' ident='LC__CMDB__CATG__AUDIT__RESULT'}]</td>
		<td class="value">
            [{isys type='f_textarea' name='C__CMDB__CATG__AUDIT__RESULT' tab='9'}]
        </td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__FAULT' ident='LC__CMDB__CATG__AUDIT__FAULT'}]</td>
		<td class="value">
            [{isys type='f_textarea' name='C__CMDB__CATG__AUDIT__FAULT' tab='10'}]
        </td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CMDB__CATG__AUDIT__INCIDENT' ident='LC__CMDB__CATG__AUDIT__INCIDENT'}]</td>
		<td class="value">
            [{isys type='f_textarea' name='C__CMDB__CATG__AUDIT__INCIDENT' tab='10'}]
        </td>
	</tr>
</table>
