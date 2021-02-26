<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__POBJ_TYPE" name="C__CMDB__CATS__POBJ_TYPE"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_cats_eps_type"
				name="C__CMDB__CATS__POBJ_TYPE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__EPS__WARMUP_TIME" name="C__CMDB__CATS__EPS__WARMUP_TIME"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CMDB__CATS__EPS__WARMUP_TIME"}]
			[{isys
				type="f_dialog"
				p_bInfoIconSpacer="0"
				name="C__CMDB__CATS__EPS__WARMUP_TIME_UNIT"
				p_strTable="isys_unit_of_time"
                p_strClass="small"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__EPS__FUEL_TANK" name="C__CMDB__CATS__EPS__FUEL_TANK"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CMDB__CATS__EPS__FUEL_TANK"}]
			[{isys
				type="f_dialog"
				p_bInfoIconSpacer="0"
				name="C__CMDB__CATS__EPS__FUEL_TANK_UNIT"
				p_strTable="isys_volume_unit"
                p_strClass="small"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__EPS__AUTONOMY_TIME" name="C__CMDB__CATS__EPS__AUTONOMY_TIME"}]</td>
		<td class="value">
		[{isys type="f_text" name="C__CMDB__CATS__EPS__AUTONOMY_TIME"}]
		[{isys
			type="f_dialog"
			p_bInfoIconSpacer="0"
			name="C__CMDB__CATS__EPS__AUTONOMY_TIME_UNIT"
			p_strTable="isys_unit_of_time"
            p_strClass="small"}]
		</td>
	</tr>
</table>