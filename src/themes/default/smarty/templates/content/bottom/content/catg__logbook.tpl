<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__LOGBOOK__TITLE' ident="LC__CMDB__LOGBOOK__TITLE"}]</td>
		<td class="value">[{isys type="f_text"  name="C__CMDB__LOGBOOK__TITLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__LOGBOOK__DATE' ident="LC_UNIVERSAL__DATE"}]</td>
		<td class="value">[{isys type="f_text"  name="C__CMDB__LOGBOOK__DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__LOGBOOK__LEVEL' ident="LC__CMDB__LOGBOOK__LEVEL"}]</td>
		<td class="value">[{isys type="f_text"  name="C__CMDB__LOGBOOK__LEVEL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__LOGBOOK__USER' ident="LC__CMDB__LOGBOOK__USER"}]</td>
		<td class="value">[{isys type="f_text"  name="C__CMDB__LOGBOOK__USER"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__LOGBOOK__CHANGED_FIELDS' ident="LC__CMDB__LOGBOOK__CHANGED_FIELDS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CMDB__LOGBOOK__CHANGED_FIELDS"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type='f_label' name='C__CMDB__LOGBOOK__COMMENT' ident="LC__POPUP__COMMENTARY__TITLE"}]</td>
		<td class="value m5">[{isys type="f_textarea"  name="C__CMDB__LOGBOOK__COMMENT" htmlEnabled="1"}]</td>
	</tr>
	<tr>
		<td class="key vat">[{isys type='f_label' name='C__CMDB__LOGBOOK__REASON' ident="LC__POPUP__COMMENTARY__REASON"}]</td>
		<td class="value">[{isys type="f_popup" name="C__CMDB__LOGBOOK__REASON" p_strPopupType="dialog_plus" p_strTable="isys_logbook_reason"}]</td>
	</tr>
	<tr>
		<td class="key vat">SQL [{isys type="lang" ident="LC__CMDB__LOGBOOK__DESCRIPTION"}] / Log</td>
		<td class="value pl20">
			<button type="button" class="btn btn-small mb10" onclick="new Effect.toggle('description_long', 'blind', {duration:0.4});">
				<img src="[{$dir_images}]icons/silk/find.png" class="mr5"><span>[{isys type="lang" ident="LC__CMDB__LOGBOOK__SHOW_DETAILS"}]</span>
			</button>

			<br/>

			<div style="display:none;" id="description_long">
				[{isys type="f_textarea"  name="C__CMDB__LOGBOOK__DESCRIPTION" htmlEnabled=true p_bInfoIconSpacer=0}]
			</div>
		</td>
	</tr>
</table>

[{if $changes}]
	<div class="p10">
		<h3>[{isys type="lang" ident="LC__UNIVERSAL__CHANGES"}]</h3>

		<div class="mt5">[{$changes}]</div>
	</div>
[{/if}]