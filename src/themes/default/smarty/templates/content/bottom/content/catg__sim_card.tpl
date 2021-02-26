<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TYPE' ident="LC__CMDB__CATS_CP_CONTRACT__TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATS__CP_CONTRACT__TYPE" p_strTable="isys_cp_contract_type"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE__VIEW' ident="LC__CMDB__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE"}]</td>
		<td class="value">[{isys name="C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE" type="f_popup" p_strPopupType="browser_object_ng" catFilter="C__CATG__ASSIGNED_CARDS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__NETWORK_PROVIDER' ident="LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER"}]</td>
		<td class="value">[{isys name="C__CATS__CP_CONTRACT__NETWORK_PROVIDER" type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_network_provider"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TELEPHONE_RATE' ident="LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_telephone_rate" name="C__CATS__CP_CONTRACT__TELEPHONE_RATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__START_DATE__VIEW' ident="LC__CMDB__CATS_CP_CONTRACT__START_DATE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATS__CP_CONTRACT__START_DATE" p_strPopupType="calendar" p_calSelDate="" p_bTime="0"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__END_DATE__VIEW' ident="LC__CMDB__CATS_CP_CONTRACT__END_DATE"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATS__CP_CONTRACT__END_DATE" p_strPopupType="calendar" p_calSelDate="" p_bTime="0"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__THRESHOLD__VIEW' ident="LC__CMDB__CATS_CP_CONTRACT__THRESHOLD"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CATS__CP_CONTRACT__THRESHOLD" p_strPopupType="calendar" p_calSelDate="" p_bTime="0"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr /></td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__CLIENT_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__CLIENT_NUMBER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__CARD_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__CARD_NUMBER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__PHONE_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__PHONE_NUMBER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__PIN' ident="LC__CMDB__CATS_CP_CONTRACT__PIN"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__PIN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__PIN2' ident="LC__CMDB__CATS_CP_CONTRACT__PIN2"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__PIN2"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__PUK' ident="LC__CMDB__CATS_CP_CONTRACT__PUK"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__PUK"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__PUK2' ident="LC__CMDB__CATS_CP_CONTRACT__PUK2"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__PUK2"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__SERIAL_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__SERIAL_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__SERIAL_NUMBER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SIM_CARD__TWINCARD' ident="LC__CMDB__CATS_CP_CONTRACT__TWINCARD"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__SIM_CARD__TWINCARD" p_bDbFieldNN="1"}]</td>
	</tr>
</table>

<div id="twincard" class="mt10 [{if $g_twincard == 0}]hide[{/if}]">
	<h3 class="p5 border gradient">Twincard</h3>

	<table class="contentTable" style="border-top:none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_CARD_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_CARD_NUMBER"}]</td>
		</tr>
		<tr><td colspan="2"><hr class="partingLine" /></td></tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_PHONE_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_PHONE_NUMBER"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_PIN' ident="LC__CMDB__CATS_CP_CONTRACT__PIN"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_PIN"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_PIN2' ident="LC__CMDB__CATS_CP_CONTRACT__PIN2"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_PIN2"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_PUK' ident="LC__CMDB__CATS_CP_CONTRACT__PUK"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_PUK"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_PUK2' ident="LC__CMDB__CATS_CP_CONTRACT__PUK2"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_PUK2"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER' ident="LC__CMDB__CATS_CP_CONTRACT__SERIAL_NUMBER"}]</td>
			<td class="value">[{isys type="f_text" name="C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" name="C__CATS__CP_CONTRACT__TC_DESCRIPTION" ident="LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO"}]</td>
			<td class="value">[{isys type="f_textarea" name="C__CATS__CP_CONTRACT__TC_DESCRIPTION"}]</td>
		</tr>
	</table>
</div>

<script>
	(function () {
		"use strict";

		var twincard_dialog = $('C__CMDB__CATG__SIM_CARD__TWINCARD');

		if (twincard_dialog) {
			twincard_dialog.on('change', function () {
				$('twincard').toggleClassName('hide');
			});
		}
	}());
</script>