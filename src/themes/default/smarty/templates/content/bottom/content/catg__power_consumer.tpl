<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__TITLE' ident="LC__CMDB__CATG__POWER_CONSUMER__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__POWER_CONSUMER__TITLE"}]</td>
  	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__ACTIVE' ident="LC__CMDB__CATG__POWER_CONSUMER__ACTIVE"}]</td>
		<td class="value">[{isys type='f_dialog' name='C__CATG__POWER_CONSUMER__ACTIVE' p_bDbFieldNN="1"}]</td>
	</tr>
	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__MANUFACTURER_ID' ident="LC__CMDB__CATG__POWER_CONSUMER__MANUFACTURE"}]</td>
      	<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__POWER_CONSUMER__MANUFACTURER_ID" p_strTable="isys_pc_manufacturer" p_bDbFieldNN="0"}]</td>
  	</tr>
	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__MODEL_ID' ident="LC__CMDB__CATG__POWER_CONSUMER__MODEL"}]</td>
      	<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__POWER_CONSUMER__MODEL_ID" p_strTable="isys_pc_model" p_bDbFieldNN="0"}]</td>
  	</tr>
  	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__VOLT' ident="LC__CMDB__CATS__POBJ_VOLT"}]</td>
      	<td class="value">[{isys type="f_text" name="C__CATG__POWER_CONSUMER__VOLT" id="C__CATG__POWER_CONSUMER__VOLT"}]</td>
  	</tr>
	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__WATT' ident="LC__CMDB__CATS__POBJ_WATT"}]</td>
      	<td class="value">[{isys type="f_text" name="C__CATG__POWER_CONSUMER__WATT" id="C__CATG__POWER_CONSUMER__WATT"}]</td>
  	</tr>
  	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__AMPERE' ident="LC__CMDB__CATS__POBJ_AMPERE"}]</td>
      	<td class="value">[{isys type="f_text" name="C__CATG__POWER_CONSUMER__AMPERE" id="C__CATG__POWER_CONSUMER__AMPERE"}]</td>
  	</tr>
  	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__BTU' ident="BTU"}]</td>
      	<td class="value">[{isys type="f_text" name="C__CATG__POWER_CONSUMER__BTU"}]</td>
  	</tr>
  	<tr>
      	<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__DEST__VIEW' ident="LC__CATG__CONTROLLER_FC_PORT_CONNECTION"}]</td>
      	<td class="value">
      		[{isys
      			title="LC__BROWSER__TITLE__CONNECTION"
      			name="C__CATG__POWER_CONSUMER__DEST"
      			type="f_popup"
      			p_strPopupType="browser_cable_connection_ng"
      			secondSelection=true}]
      	</td>
  	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__POWER_CONSUMER__CABLE' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CABLE"
				name="C__CATG__POWER_CONSUMER__CABLE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter="C__CATG__CABLE;C__CATG__CABLE_CONNECTION"}]
		</td>
	</tr>
</table>

[{if isys_glob_is_edit_mode()}]
<script type="text/javascript">
    (function () {
        'use strict';

        var cable_view = $('C__CATG__POWER_CONSUMER__CABLE__VIEW');

        if (cable_view) {
            if (cable_view.getValue().blank()) {
                cable_view.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
            }
        }

        idoit.Require.require('smartyVWA', function () {
            $('C__CATG__POWER_CONSUMER__VOLT', 'C__CATG__POWER_CONSUMER__WATT', 'C__CATG__POWER_CONSUMER__AMPERE').invoke('on', 'blur', function () {
                vwa_autocalc($('C__CATG__POWER_CONSUMER__VOLT'), $('C__CATG__POWER_CONSUMER__WATT'), $('C__CATG__POWER_CONSUMER__AMPERE'));
            });
        });
    }());
</script>
[{/if}]