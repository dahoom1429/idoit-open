<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__LABEL" ident="LC__CATG__FIBER_LEAD__LABEL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__FIBER_LEAD__LABEL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__CATEGORY" ident="LC__CATG__FIBER_LEAD__CATEGORY"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__FIBER_LEAD__CATEGORY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__COLOR" ident="LC__CATG__FIBER_LEAD__COLOR"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__FIBER_LEAD__COLOR"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__FIBER_LEAD__DAMPING" ident="LC__CATG__FIBER_LEAD__DAMPING"}]</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
				<div class="input-group input-size-small ml20">
					[{isys type="f_text" name="C__CATG__FIBER_LEAD__DAMPING" p_bInfoIconSpacer=0 disableInputGroup=true}]
					<div class="input-group-addon">DB</div>
				</div>
			[{else}]
				[{isys type="f_text" name="C__CATG__FIBER_LEAD__DAMPING"}] DB
			[{/if}]
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key">RX</td>
		<td class="value pl20">[{$connected_rx}]</td>
	</tr>
	<tr>
		<td class="key">TX</td>
		<td class="value pl20">[{$connected_tx}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
</table>