<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__MAIL_ADDRESSES__TITLE" ident="LC__CONTACT__PERSON_MAIL_ADDRESS"}]</td>
		<td class="value">
			<div class="ml20 input-group input-size-normal">
				[{isys type="f_text" name="C__CMDB__CATG__MAIL_ADDRESSES__TITLE" p_bHtmlDecode=true disableInputGroup=true inputGroupMarginClass="" p_bInfoIconSpacer=0}]
				[{if isys_glob_is_edit_mode()}]
				<div class="input-group-addon">
					<img src="[{$dir_images}]icons/silk/email.png" />
				</div>
				[{/if}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CMDB__CATG__MAIL_ADDRESSES__PRIMARY" ident="LC__CATG__CONTACT_LIST__PRIMARY"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__MAIL_ADDRESSES__PRIMARY"}]</td>
	</tr>
</table>
