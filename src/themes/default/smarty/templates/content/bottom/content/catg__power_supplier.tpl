<table class="contentTable">
	<tr>
      <td class="key">[{isys type='f_label' name='C__CMDB__CATG__POWER_SUPPLIER__VOLT' ident="LC__CMDB__CATG__POWER_SUPPLIER__VOLT"}]</td>
      <td class="value">[{isys type="f_text" name="C__CMDB__CATG__POWER_SUPPLIER__VOLT" id="C__CMDB__CATG__POWER_SUPPLIER__VOLT" p_nDecCount="2" tab="3"}]</td>
  	</tr>
	<tr>
      <td class="key">[{isys type='f_label' name='C__CMDB__CATG__POWER_SUPPLIER__WATT' ident="LC__CMDB__CATG__POWER_SUPPLIER__WATT"}]</td>
      <td class="value">[{isys type="f_text" name="C__CMDB__CATG__POWER_SUPPLIER__WATT" id="C__CMDB__CATG__POWER_SUPPLIER__WATT" p_nDecCount="2" tab="3"}]</td>
  	</tr>
  	<tr>
      <td class="key">[{isys type='f_label' name='C__CMDB__CATG__POWER_SUPPLIER__AMPERE' ident="LC__CMDB__CATG__POWER_SUPPLIER__AMPERE"}]</td>
      <td class="value">[{isys type="f_text" name="C__CMDB__CATG__POWER_SUPPLIER__AMPERE" id="C__CMDB__CATG__POWER_SUPPLIER__AMPERE" p_nDecCount="2" tab="3"}]</td>
  	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__POWER_SUPPLIER__DEST' ident="LC__CATG__CONTROLLER_FC_PORT_CONNECTION"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CONNECTION"
				name="C__CATG__POWER_SUPPLIER__DEST"
				type="f_popup"
				p_strPopupType="browser_cable_connection_ng"
				secondSelection=true
				p_strValue=""}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__POWER_SUPPLIER__CABLE' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__CABLE"
				name="C__CATG__POWER_SUPPLIER__CABLE"
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

        var cable_view = $('C__CATG__POWER_SUPPLIER__CABLE__VIEW');

        if (cable_view) {
            if (cable_view.getValue().blank()) {
                cable_view.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
            }
        }

        idoit.Require.require('smartyVWA', function () {
            $('C__CMDB__CATG__POWER_SUPPLIER__VOLT', 'C__CMDB__CATG__POWER_SUPPLIER__WATT', 'C__CMDB__CATG__POWER_SUPPLIER__AMPERE').invoke('on', 'blur', function () {
                vwa_autocalc($('C__CMDB__CATG__POWER_SUPPLIER__VOLT'), $('C__CMDB__CATG__POWER_SUPPLIER__WATT'), $('C__CMDB__CATG__POWER_SUPPLIER__AMPERE'));
            });
        });
    }());
</script>
[{/if}]