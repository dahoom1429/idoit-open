<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_TYPE" name="C__CATS__AC_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strTable="isys_ac_type" p_strPopupType="dialog_plus" name="C__CATS__AC_TYPE" tab="10"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_THRESHOLD" name="C__CATS__AC_THRESHOLD"}]</td>
		<td class="value">
		[{isys p_strStyle="width:100px;" type="f_text" name="C__CATS__AC_THRESHOLD" tab="20"}]
			[{isys p_bInfoIconSpacer="0" p_strStyle="width:100px;" p_strTable="isys_temp_unit" type="f_dialog" name="C__CATS__AC_THRESHOLD_UNIT" tab="30"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_REFRIGERATING_CAPACITY" name="C__CATS__AC_REFRIGERATING_CAPACITY"}]</td>
		<td class="value">
		[{isys p_strStyle="width:100px;" type="f_text" name="C__CATS__AC_REFRIGERATING_CAPACITY" tab="40"}]
			[{isys p_bInfoIconSpacer="0" p_strStyle="width:100px;" p_strTable="isys_ac_refrigerating_capacity_unit" type="f_dialog" name="C__CATS__AC_REFRIGERATING_CAPACITY_UNIT" tab="50"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_AIR_QUANTITY" name="C__CATS__AC_AIR_QUANTITY"}]</td>
		<td class="value">
		[{isys p_strStyle="width:100px;" type="f_text" name="C__CATS__AC_AIR_QUANTITY" tab="60"}]
			[{isys p_bInfoIconSpacer="0" p_strStyle="width:100px;" p_strTable="isys_ac_air_quantity_unit" type="f_dialog" name="C__CATS__AC_AIR_QUANTITY_UNIT" tab="70"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATS__AC_DIMENSIONS" name="C__CATS__AC_DIMENSIONS_WIDTH"}]</td>
		<td class="value">
		[{isys p_strStyle="width:50px;" type="f_text" name="C__CATS__AC_DIMENSIONS_WIDTH" tab="80"}] &times;
		[{isys p_bInfoIconSpacer="0" p_strStyle="width:50px;" type="f_text" name="C__CATS__AC_DIMENSIONS_HEIGHT" tab="90"}] &times;
		[{isys p_bInfoIconSpacer="0" p_strStyle="width:50px;" type="f_text" name="C__CATS__AC_DIMENSIONS_DEPTH" tab="100"}]
		[{isys type="f_dialog" p_strTable="isys_depth_unit" p_strStyle="width:50px;" name="C__CATS__AC_DIMENSIONS_UNIT" p_bDbFieldNN="1" tab="30"}]
		</td>
	</tr>
</table>