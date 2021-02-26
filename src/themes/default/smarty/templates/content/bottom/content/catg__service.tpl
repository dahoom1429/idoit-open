<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__SERVICE__SERVICE_NUMBER" ident="LC__CMDB__CATG__SERVICE__SERVICE_NUMBER"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__CATG__SERVICE__SERVICE_NUMBER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__TYPE' ident="LC__CMDB__CATG__SERVICE__TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATG__SERVICE__TYPE" p_strTable="isys_service_type" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__CATEGORY' ident="LC__CMDB__CATG__SERVICE__CATEGORY"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATG__SERVICE__CATEGORY" p_strTable="isys_service_category" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align:middle">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__SERVICE_MANAGER' ident="LC__CMDB__CATG__SERVICE__SERVICE_MANAGER"}]</td>
		<td class="value"><img src="[{$dir_images}]empty.gif" width="20" height="15" class="fl">[{$contacts}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__ACTIVE' ident="LC__CMDB__CATG__SERVICE__ACTIVE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__SERVICE__ACTIVE" p_strClass="input input-mini" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__BUSINESS_UNIT' ident="LC__CMDB__CATG__SERVICE__BUSINESS_UNIT"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATG__SERVICE__BUSINESS_UNIT" p_strTable="isys_business_unit" p_strClass="input"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__SERVICE__ALIASE" name="C__CMDB__CATG__SERVICE__ALIAS"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATG__SERVICE__ALIAS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN' ident="LC__CMDB__CATG__SERVICE__DESCRIPTION_INTERN"}]</td>
		<td class="value">[{isys type="f_wysiwyg" name="C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN' ident="LC__CMDB__CATG__SERVICE__DESCRIPTION_EXTERN"}]</td>
		<td class="value">[{isys type="f_wysiwyg" name="C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN"}]</td>
	</tr>
</table>

[{if isys_glob_is_edit_mode()}]
	<script>
		(function () {
			'use strict';

			var $alias_input = $('C__CMDB__CATG__SERVICE__ALIAS'),
				alias_chosen = null;

			// Function for refreshing the DNS domain chosen.
			idoit.callbackManager
				.registerCallback('cmdb-catg-service-alias-update', function (selected) {
					if (alias_chosen !== null) {
						alias_chosen.destroy();
					}

					$alias_input.setValue(selected).fire('chosen:updated');
					alias_chosen = new Chosen($alias_input, {
						disable_search_threshold: 10,
						search_contains:          true
					});
				})
				.triggerCallback('cmdb-catg-service-alias-update', $F('C__CMDB__CATG__SERVICE__ALIAS'));
		})();
	</script>
	[{/if}]