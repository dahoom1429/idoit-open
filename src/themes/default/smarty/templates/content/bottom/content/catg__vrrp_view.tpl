<div id="catg-vrrp-view" class="p5">
	[{foreach $rows as $vrrp}]
	<div class="border bg-white">
		<h3 class="p5 border-bottom gradient text-shadow">[{$vrrp.info.url}]</h3>

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="lang" ident="LC__CATG__VRRP__TYPE"}]</td>
				<td class="value pl20">[{$vrrp.info.vrrp_type}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__CATG__VRRP__VRID"}]</td>
				<td class="value pl20">[{$vrrp.info.vrrp_vr_id}]</td>
			</tr>
			<tr>
				<td colspan="2">
					<hr class="mt5 mb5" />
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__CMDB__NET_ASSIGNMENT"}]</td>
				<td class="value pl20">[{$vrrp.info.layer3_net}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__CMDB__CATG__IP__IPV4_ADDRESS"}]</td>
				<td class="value pl20">[{$vrrp.info.ip_address}]</td>
			</tr>
		</table>

		<fieldset class="overview">
			<legend><span>[{isys type="lang" ident="LC__CATG__VRRP_VIEW__CONNECTED_LOG_PORTS"}]</span></legend>
			[{foreach $vrrp.ports as $port}]
			<div><a href="[{$port.url}]"><img src="[{$dir_images}]icons/silk/link.png" class="vam mr5" />[{$port.title}]</a>[{if $port.mac}]<em class="grey ml5">[{$port.mac}]</em>[{/if}]
			[{if $port.parent}], [{isys type="lang" ident="LC__CMDB__CATG__INTERFACE_L__PARENT"}]: <a href="[{$port.parent.url}]"><img src="[{$dir_images}]icons/silk/link.png" class="vam mr5" />[{$port.parent.title}]</a>[{if $port.parent.mac}]<em class="grey ml5">[{$port.parent.mac}]</em>[{/if}][{/if}]
			</div>
			[{/foreach}]
		</fieldset>
	</div>
	[{foreachelse}]
		<p class="m5 p5 box-blue"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />[{isys type="lang" ident="LC__CATG__VRRP_VIEW__INFORMATION"}]</p>
	[{/foreach}]
</div>

<style type="text/css">
	#catg-vrrp-view > div {
		margin-bottom: 5px;
	}

	#catg-vrrp-view > div:last-child {
		margin-bottom: 0;
	}

	#catg-vrrp-view fieldset div {
		border-bottom: 1px solid #888;
		padding: 5px;
	}

	#catg-vrrp-view fieldset div:first-of-type {
		padding-top: 15px;
	}

	#catg-vrrp-view fieldset div:last-child {
		border-bottom: none;
	}

	#catg-vrrp-view .key {
		width: 140px;
	}
</style>