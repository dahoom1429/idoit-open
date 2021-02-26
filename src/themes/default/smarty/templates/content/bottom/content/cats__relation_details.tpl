<div class="p5">
	[{if $view == "relation"}]
	<table>
		<tr>
			<td class="p10">
				<span class="toolbar bold">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 1: [{$master}]</span>
				<input type="hidden" name="C__CATS__RELATION_DETAILS__MASTER__HIDDEN" value="[{$obj_id_master}]">
			</td>
			<td class="p10">
				[{$relation_type_description}]
				<input type="hidden" name="C__CATS__RELATION_DETAILS__DIRECTION" value="[{$direction}]">
			</td>
			<td class="p10">
				<span class="toolbar bold">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 2: [{$slave}]</span>
				<input type="hidden" name="C__CATS__RELATION_DETAILS__SLAVE__HIDDEN" value="[{$obj_id_slave}]">
			</td>
		</tr>
	</table>

	[{else}]

    [{* LF: is this case ever used? Please remove, if possible *}]

	<table class="contentTable m5">
		[{if isys_glob_is_edit_mode()}]
		<colgroup>
			<col style="width:33%;" />
			<col style="width:33%;" />
			<col style="width:33%;" />
		</colgroup>
		[{/if}]
		<tr>
			<td class="p10">
				<p>[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 1</p>
				[{isys type="f_popup" p_strPopupType="browser_object_ng" p_bDisableDetach="1" name="C__CATS__RELATION_DETAILS__MASTER" p_bInfoIconSpacer=0}]
			</td>
			<td class="p10">
				<p>&nbsp;</p>
				[{isys type="f_dialog" p_bDbFieldNN="1" p_bInfoIconSpacer="0" name="C__CATS__RELATION_DETAILS__DIRECTION"}]
			</td>
			<td class="p10">
				<p>[{isys type="lang"  ident="LC_UNIVERSAL__OBJECT"}] 2</p>
				[{isys type="f_popup" p_strPopupType="browser_object_ng" p_bDisableDetach="1" name="C__CATS__RELATION_DETAILS__SLAVE" p_bInfoIconSpacer=0}]
			</td>
		</tr>
	</table>
	[{/if}]

	<hr class="mt5 mb5" />

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CATG__RELATION__RELATION_TYPE" name="C__CATS__RELATION_DETAILS__RELATION_TYPE"}]</td>
			<td class="value">
				[{isys type="f_text" p_bDbFieldNN="1" name="C__CATS__RELATION_DETAILS__RELATION_TYPE" p_bReadonly="1"}]
				<input type="hidden" name="C__CATS__RELATION_DETAILS__RELATION_TYPE_VALUE" value="[{$relation_type}]">
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__CATG__RELATION__WEIGHTING" name="C__CATS__RELATION_DETAILS__WEIGHTING"}]</td>
			<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATS__RELATION_DETAILS__WEIGHTING"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="LC__OBJTYPE__IT_SERVICE" name="C__CATS__RELATION_DETAILS__ITSERVICE"}]</td>
			<td class="value">[{isys name="C__CATS__RELATION_DETAILS__ITSERVICE" p_bDbFieldNN="1" type="f_dialog"}]</td>
		</tr>
	</table>
</div>
