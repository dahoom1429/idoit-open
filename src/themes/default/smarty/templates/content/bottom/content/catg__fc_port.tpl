<table class="contentTable">
[{if $nNewPort == "1"}]
	[{isys type='f_title_suffix_counter' name='C__CATG__FC_PORT__SUFFIX' title_identifier='C__CATG__CONTROLLER_FC_PORT_TITLE' label_counter='LC__CMDB__CATG__PORT__NUMBER_NEW'}]

	<tr><td colspan="2"><hr /></td></tr>
[{/if}]
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_PORT_TITLE' ident="LC__CATG__CONTROLLER_FC_PORT_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__CONTROLLER_FC_PORT_TITLE" id="C__CATG__CONTROLLER_FC_PORT_TITLE" tab="30"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_PORT_TYPE' ident="LC__CATG__CONTROLLER_FC_PORT_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__CONTROLLER_FC_PORT_TYPE" tab="40"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_CONTROLLER' ident="LC__CATG__CONTROLLER_FC_CONTROLLER"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__CONTROLLER_FC_CONTROLLER" tab="20"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_PORT_MEDIUM' ident="LC__CATG__CONTROLLER_FC_PORT_MEDIUM"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__CONTROLLER_FC_PORT_MEDIUM" tab="50"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__FCPORT__SPEED_VALUE' ident="LC__CMDB__CATG__PORT__SPEED"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__FCPORT__SPEED_VALUE"}]
			[{isys type="f_dialog" name="C__CATG__FCPORT__SPEED"}]
		</td>
	</tr>
	<tr><td colspan="2"><hr /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_PORT_NODE_WWN' ident="LC__CATG__CONTROLLER_FC_PORT_NODE_WWN"}]</td>
		<td class="value">[{isys type="f_text" id="C__CATG__CONTROLLER_FC_PORT_NODE_WWN" name="C__CATG__CONTROLLER_FC_PORT_NODE_WWN" tab="60"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__CONTROLLER_FC_PORT_PORT_WWN' ident="LC__CATG__CONTROLLER_FC_PORT_PORT_WWN"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__CONTROLLER_FC_PORT_PORT_WWN" tab="70"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CMDB__CATS__SAN_ZONE__VIEW' ident="LC__CMDB__CATG__FC_PORT__SAN_ZONING"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="browser_fc_port_san_zoning" name="C__CMDB__CATS__SAN_ZONE" tab="70"}]</td>
	</tr>

[{if $nNewPort != "1"}]
	<tr><td colspan="2"><hr /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__FCPORT__DEST__VIEW' ident="LC__CATG__CONTROLLER_FC_PORT_CONNECTION"}]</td>
		<td class="value">
			[{isys
				title="LC__POPUP__BROWSER__UI_CON_SELECTION"
				name="C__CATG__FCPORT__DEST"
				type="f_popup"
				p_strPopupType="browser_cable_connection_ng"
				secondSelection=true}]
		</td>
	</tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__FCPORT__CABLE__VIEW' ident="LC__CATG__CONNECTOR__CABLE"}]</td>
		<td class="value">
			[{isys
				title="LC__UNIVERSAL__CABLE_SELECTION"
				name="C__CATG__FCPORT__CABLE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter="C__CATG__CABLE;C__CATG__CABLE_CONNECTION"}]
		</td>
	</tr>
[{/if}]
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var cable_view = $('C__CATG__FCPORT__CABLE__VIEW');

		if (cable_view) {
			if (cable_view.getValue().blank()) {
				cable_view.setValue('[{isys type="lang" ident="LC__CABLE_CONNECTION__CREATE_AUTOMATICALLY"}]');
			}
		}
	}());
</script>