<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_TYPE" name="C__CATS__AC_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATS__AC_TYPE" }]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_THRESHOLD" name="C__CATS__AC_THRESHOLD"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__AC_THRESHOLD"}]
			[{isys type="f_dialog" name="C__CATS__AC_THRESHOLD_UNIT"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_REFRIGERATING_CAPACITY" name="C__CATS__AC_REFRIGERATING_CAPACITY"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__AC_REFRIGERATING_CAPACITY"}]
			[{isys type="f_dialog" name="C__CATS__AC_REFRIGERATING_CAPACITY_UNIT"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_AIR_QUANTITY" name="C__CATS__AC_AIR_QUANTITY"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATS__AC_AIR_QUANTITY"}]
			[{isys type="f_dialog" name="C__CATS__AC_AIR_QUANTITY_UNIT"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_DIMENSIONS" name="C__CATS__AC_DIMENSIONS_WIDTH"}]</td>
		<td class="value pl20">
			[{if isys_glob_is_edit_mode()}]<div class="input-group input-size-medium">[{/if}]
				[{isys type="f_text" name="C__CATS__AC_DIMENSIONS_WIDTH"}]
				<span class="[{if isys_glob_is_edit_mode()}]input-group-addon input-group-addon-unstyled p5[{else}]ml5 mr5[{/if}]">&times;</span>
				[{isys type="f_text" name="C__CATS__AC_DIMENSIONS_HEIGHT"}]
				<span class="[{if isys_glob_is_edit_mode()}]input-group-addon input-group-addon-unstyled p5[{else}]ml5 mr5[{/if}]">&times;</span>
				[{isys type="f_text" name="C__CATS__AC_DIMENSIONS_DEPTH"}]
			[{if isys_glob_is_edit_mode()}]</div>[{/if}]
			[{isys type="f_dialog" name="C__CATS__AC_DIMENSIONS_UNIT"}]
		</td>
	</tr>
</table>