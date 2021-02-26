<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__UI_TITLE' ident="LC__CMDB__CATG__UI_TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__UI_TITLE"}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mb5 mt5"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__UI_CONNECTION_TYPE' ident="LC__CMDB__CATG__UI_CONNECTION_TYPE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__UI_CONNECTION_TYPE" p_bDbFieldNN="1" p_strTable="isys_ui_con_type"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__UI_PLUG_TYPE' ident="LC__CMDB__CATG__UI_PLUG_TYPE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__UI_PLUG_TYPE" p_bDbFieldNN="1" p_strTable="isys_ui_plugtype"}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mb5 mt5"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__UI__ASSIGNED_UI__VIEW' ident="LC__CMDB__CATG__UI_ASSIGNED_UI"}]</td>
        <td class="value">
            [{isys
			title="LC__BROWSER__TITLE__CONNECTION"
			name="C__CATG__UI__ASSIGNED_UI"
			type="f_popup"
			p_strPopupType="browser_cable_connection_ng"
            secondList='isys_cmdb_dao_category_g_connector::object_browser'
			secondSelection=true
			p_strValue=""}]
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__UI__ASSIGNED_CABLE__VIEW' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
        <td class="value">
            [{isys
			title="LC__BROWSER__TITLE__CABLE"
			name="C__CATG__UI__ASSIGNED_CABLE"
			type="f_popup"
			p_strPopupType="browser_object_ng"
			catFilter="C__CATG__CABLE;C__CATG__CABLE_CONNECTION"}]
        </td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

	    var assigned_cable = $('C__CATG__UI__ASSIGNED_CABLE__VIEW');

	    if (assigned_cable) {
	        if (assigned_cable.getValue().blank()) {
	            assigned_cable.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
	        }
	    }
	}());
</script>