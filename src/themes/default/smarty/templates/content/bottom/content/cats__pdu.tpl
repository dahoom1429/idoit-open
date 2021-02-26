<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="PDU ID" name="C__CMDB__CATS__PDU__PDU_ID"}]</td>
		<td class="value">[{isys type="f_count" name="C__CMDB__CATS__PDU__PDU_ID"}]</td>
	</tr>
</table>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__PDU__SNMP_QUERIES" name="C__CMDB__CATS__PDU__ACC_POWER_PDU"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__CATS__PDU__SNMP_QUERIES" p_strClass="input input-mini" default="0"}]</td>
	</tr>
</table>

[{if isys_tenantsettings::get('snmp.pdu.queries', false)}]
<fieldset class="overview">
	<legend><span>SNMP</span></legend>

	<table class="contentTable mt5">
		<tr>
			<td class="key">[{isys type="f_label" ident="Accumulated Energy" name="C__CMDB__CATS__PDU__ACC_ENERGY_PDU"}]</td>
			<td class="value">[{isys type="f_data" name="C__CMDB__CATS__PDU__ACC_ENERGY_PDU" default="0"}] kWh</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="Accumulated Power" name="C__CMDB__CATS__PDU__ACC_POWER_PDU"}]</td>
			<td class="value">[{isys type="f_data" name="C__CMDB__CATS__PDU__ACC_POWER_PDU" default="0"}] Watt</td>
		</tr>
	</table>
</fieldset>
[{/if}]