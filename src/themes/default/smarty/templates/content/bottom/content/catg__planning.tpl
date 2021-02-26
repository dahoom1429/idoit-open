<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__STATUS' ident="LC__UNIVERSAL__CMDB_STATUS"}]</td>
        <td class="value">
            <div class="ml20 input-group input-size-small">
	            [{isys type="f_dialog" default="n/a" p_bDbFieldNN="1" name="C__CATG__PLANNING__STATUS" p_strTable="isys_cmdb_status" disableInputGroup=true p_bInfoIconSpacer="0"}]
                [{if isys_glob_is_edit_mode()}]
                <div class="input-group-addon">
	                <div class="cmdb-marker" id="cmdb_status_color" style="background-color:#[{$status_color}]; height:100%; width:100%; margin:0;"></div>
                </div>
                [{else}]
                <div class="cmdb-marker vam" id="cmdb_status_color" style="background-color:#[{$status_color}]; height:18px; margin:-2px 0 0; float:none; display: inline-block;"></div>
                [{/if}]
            </div>
            <br class="cb"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__START__VIEW' ident="LC__UNIVERSAL__VALIDITY"}]</td>
        <td class="value">
	        [{if isys_glob_is_edit_mode()}]
		        <div class="input-group input-size-small ml20">
		            [{isys type="f_popup" name="C__CATG__PLANNING__START" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0" disableInputGroup=true p_bInfoIconSpacer="0"}]
		            <div class="input-group-addon input-group-addon-unstyled">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</div>
		            [{isys type="f_popup" name="C__CATG__PLANNING__END" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0" disableInputGroup=true p_bInfoIconSpacer="0"}]
		        </div>

	        [{else}]
	            [{isys type="f_popup" name="C__CATG__PLANNING__START" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0"}]
	            <span class="ml5 mr5">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</span>
	            [{isys type="f_popup" name="C__CATG__PLANNING__END" p_strPopupType="calendar" disablePastDate="true" p_calSelDate="" p_bTime="0" p_bInfoIconSpacer="0"}]
	        [{/if}]
        </td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var cmdb_status = $('C__CATG__PLANNING__STATUS'),
			cmdb_status_colors = '[{$status_colors}]'.evalJSON();

		if (cmdb_status) {
			cmdb_status.on('change', function () {
				var selected_cmdb_status = $F(this);

				if (cmdb_status_colors.hasOwnProperty(selected_cmdb_status)) {
					$('cmdb_status_color').setStyle({backgroundColor: cmdb_status_colors[selected_cmdb_status]});
				}
			});

			cmdb_status.simulate('change');
		}
	}());
</script>