<table class="contentTable fl m5">
	<tr>
		<td class="key"></td>
		<td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_FRONT"}]</td>
		<td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_BACK"}]</td>
		<td>[{isys type="lang" ident="LC__CMDB__CATG__LOCATION_COMBINED"}]</td>
	</tr>
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__FREE_SLOTS"}]</span></td>
		<td class="value border-top border-grey">[{$stats.free.horizontal.front}]</td>
		<td class="value border-top border-grey">[{$stats.free.horizontal.back}]</td>
		<td class="value border-top border-grey">
			[{$stats.free.horizontal.front + $stats.free.horizontal.back}] ([{$stats.free.horizontal.percent}] %)
			<div class="bar">
				<div style="width:[{$stats.free.horizontal.percent}]%; background:[{$stats.free.horizontal.percentColor}];"></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__USED_SLOTS"}]</span></td>
		<td class="value">[{$stats.used.horizontal.front}]</td>
		<td class="value">[{$stats.used.horizontal.back}]</td>
		<td class="value">[{$stats.used.horizontal.front + $stats.used.horizontal.back}]</td>
	</tr>
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__FREE_V_SLOTS"}]</span></td>
		<td class="value border-top border-grey">[{$stats.free.vertical.front}]</td>
		<td class="value border-top border-grey">[{$stats.free.vertical.back}]</td>
		<td class="value border-top border-grey">
			[{$stats.free.vertical.front + $stats.free.vertical.back}] ([{$stats.free.vertical.percent}] %)
			<div class="bar">
				<div style="width:[{$stats.free.vertical.percent}]%; background:[{$stats.free.vertical.percentColor}];"></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__USED_V_SLOTS"}]</span></td>
		<td class="value">[{$stats.used.vertical.front}]</td>
		<td class="value">[{$stats.used.vertical.back}]</td>
		<td class="value">[{$stats.used.vertical.front + $stats.used.vertical.back}]</td>
	</tr>
</table>

<table class="contentTable fl m5">
	<tr>
		<td class="key"></td>
		<td class="pl15">[{isys type="lang" ident="LC__CATG__CONNECTOR__CONNECTION_TYPE"}]</td>
		<td>[{isys type="lang" ident="LC__UNIVERSAL__FREE"}]</td>
		<td>[{isys type="lang" ident="LC__UNIVERSAL__USED"}]</td>
	</tr>

	[{foreach $stats.connectors as $objectType => $connectors}]
		<tr>
			<td class="key"><span class="mr10">[{isys type="lang" ident=$objectType}] <span class="text-grey">([{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__FRONT"}])</span></span></td>
			<td class="value border-top border-grey">[{foreach $connectors.in as $type => $connector}][{$type}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $connectors.in as $type => $connector}][{$connector.free}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $connectors.in as $type => $connector}][{$connector.used}]<br />[{/foreach}]</td>
		</tr>
		<tr>
			<td class="key"><span class="mr10">[{isys type="lang" ident=$objectType}] <span class="text-grey">([{isys type="lang" ident="LC__CMDB__CATG__CONNECTOR__BACK"}])</span></span></td>
			<td class="value border-top border-grey">[{foreach $connectors.out as $type => $connector}][{$type}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $connectors.out as $type => $connector}][{$connector.free}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $connectors.out as $type => $connector}][{$connector.used}]<br />[{/foreach}]</td>
		</tr>
	[{/foreach}]
	[{foreach $stats.ports as $objectType => $ports}]
		<tr>
			<td class="key"><span class="mr10">[{isys type="lang" ident=$objectType}]</span></td>
			<td class="value border-top border-grey">[{foreach $ports as $type => $port}][{$type}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $ports as $type => $port}][{$port.free}]<br />[{/foreach}]</td>
			<td class="value border-top border-grey">[{foreach $ports as $type => $port}][{$port.used}]<br />[{/foreach}]</td>
		</tr>
	[{/foreach}]
</table>

[{* Statistics for watt consumption *}]
<table class="contentTable fl m5">
    <tr>
        <td class="key text-right">
            <strong><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_WATT"}]</span></strong>
        </td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_WATT_IN_COMPONENTS"}]</span></td>
        <td class="value text-right">[{$stats.watt}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__WATT"}]</td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_WATT_IN_RACK"}]</span></td>
        <td class="value text-right">[{$stats.rack_watt}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__WATT"}]</td>
    </tr>
    <tr>
        <td colspan="2"><hr/></td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_WATT_TOTAL"}]</span></td>
        <td class="value text-right">[{$stats.total_watt}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__WATT"}]</td>
    </tr>
</table>

[{* Statistics for btu consumption *}]
<table class="contentTable fl m5">
    <tr>
        <td class="key text-right">
            <strong><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_BTU"}]</span></strong>
        </td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_BTU_IN_COMPONENTS"}]</span></td>
        <td class="value text-right">[{$stats.btu}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__BTU"}]</td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_BTU_IN_RACK"}]</span></td>
        <td class="value text-right">[{$stats.rack_btu}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__BTU"}]</td>
    </tr>
    <tr>
        <td colspan="2"><hr/></td>
    </tr>
    <tr>
        <td class="key"><span class="mr10">[{isys type="lang" ident="LC__CMDB__CATS__RACK__STATS__CONSUMPTION_OF_BTU_TOTAL"}]</span></td>
        <td class="value text-right">[{$stats.total_btu}] [{isys type="lang" ident="LC__CMDB__CATG__POWER_SUPPLIER__BTU"}]</td>
    </tr>
</table>

<br class="clear" />