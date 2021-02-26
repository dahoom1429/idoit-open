<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__TITLE' ident="LC__CMDB__CATG__INTERFACE_L__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__INTERFACE_L__TITLE" tab="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__DEST' ident="LC__CMDB__CONNECTED_WITH"}]</td>
		<td class="value">
		[{isys
			title="LC__BROWSER__TITLE__PORT"
			type="f_popup"
			p_strPopupType="browser_cable_connection_ng"
			name='C__CATG__INTERFACE_L__DEST'
            secondList='isys_cmdb_dao_category_g_network_ifacel::object_browser'
			only_log_ports=true
			secondSelection=true}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__NET__VIEW' ident="LC__CMDB__LAYER2_NET"}]</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__NET"
				name="C__CATG__INTERFACE_L__NET"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				multiselection="true"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__MAC' ident="LC__CMDB__CATG__PORT__MAC"}]</td>
		<td class="value">[{isys type="f_text" p_strID="C__CATG__INTERFACE_L__MAC" name="C__CATG__INTERFACE_L__MAC" tab="130"}]</td>
	</tr>
	<tr><td colspan="2"><hr class="partingLine" /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__TYPE' ident="LC__CMDB__CATG__INTERFACE_L__TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__INTERFACE_L__TYPE" tab="3"}]</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="lang" ident="LC__CMDB__CATG__INTERFACE_L__PORT_ALLOCATION"}]:
		</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__INTERFACE_L__PORT_ALLOCATION" tab="4" emptyMessage="LC__CMDB__CATG__INTERFACE_L__EMPTY_MESSAGE_PORT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__PARENT' ident="LC__CMDB__CATG__INTERFACE_L__PARENT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__INTERFACE_L__PARENT" tab="5"}]</td>
	</tr>
	<tr><td colspan="2"><hr class="partingLine" /></td></tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__STANDARD' ident="LC__CMDB__CATG__INTERFACE_L__STANDARD"}]</td>
		<td class="value">
			[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__INTERFACE_L__STANDARD" tab="6"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__INTERFACE_L__ACTIVE' ident="LC__CATP__IP__ACTIVE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__INTERFACE_L__ACTIVE" p_bDbFieldNN="1" tab="7"}]</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">[{isys type='f_label' name='C__CATG__PORT__IP_ADDRESS' ident="LC__CATG__IP_ADDRESS"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__PORT__IP_ADDRESS" tab="8" emptyMessage="LC__CMDB__CATG__INTERFACE_L__EMPTY_MESSAGE_IP"}]</td>
	</tr>
</table>