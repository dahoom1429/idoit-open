<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="PDU" name="C__CMDB__CATS__PDU__PDU_ID"}]</td>
		<td class="value">[{isys type="f_data" name="C__CMDB__CATS__PDU__PDU_ID" tab="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Branch-ID" name="C__CMDB__CATS__PDU__BRANCH_ID"}]</td>
		<td class="value">[{isys type="f_count" name="C__CMDB__CATS__PDU__BRANCH_ID"}] [{$branch_title}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="Receptables" name="C__CMDB__CATS__PDU__RECEPTABLES"}]</td>
		<td class="value">[{isys type="f_count" name="C__CMDB__CATS__PDU__RECEPTABLES"}]</td>
	</tr>
</table>

[{if isys_tenantsettings::get('snmp.pdu.queries', false)}]
<fieldset class="overview">
	<legend><span>SNMP</span></legend>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="f_label" ident="Accumulated Energy" name="C__CMDB__CATS__PDU__ACC_ENERGY_BRANCH"}]</td>
			<td class="value">[{isys type="f_data" name="C__CMDB__CATS__PDU__ACC_ENERGY_BRANCH" default="0"}] kWh</td>
		</tr>
		<tr>
			<td class="key">[{isys type="f_label" ident="Accumulated Power" name="C__CMDB__CATS__PDU__ACC_POWER_BRANCH"}]</td>
			<td class="value">[{isys type="f_data" name="C__CMDB__CATS__PDU__ACC_POWER_BRANCH" default="0"}] Watt</td>
		</tr>
	</table>

	[{if is_array($receptables) && count($receptables) > 0}]
		<table class="listing">
		<colgroup>
			<col width="100" />
		</colgroup>
		<tr class="gradient text-shadow">
			<th>Receptable</th>
			<th>Current Power Out</th>
			<th>Accumulated Energy</th>
			<th>Connected Device</th>
		</tr>
		[{foreach from=$receptables item="r" key="i"}]

		<tr>
			<td><img src="[{$dir_images}]dtree/special/power_f_socket.gif" alt="" class="vam" /> [{$i}]</td>
			<td>[{$r.pwr_out|default:"n/a"}] Watt</td>
			<td>[{$r.acc_nrg|default:"n/a"}] kWh</td>
			<td><img src="[{$dir_images}]dtree/special/power_m_plug.gif" alt="" class="vam" /> [{$r.title|default:"n/a"}]</td>
		</tr>

		[{/foreach}]
		</table>
	[{else}]

	[{/if}]
</fieldset>
[{/if}]
