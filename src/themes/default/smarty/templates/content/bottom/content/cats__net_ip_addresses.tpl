[{if $ipv4 && !$ipv6}]
	[{include file="content/bottom/content/cats__net_ipv4_addresses.tpl"}]
[{elseif !$ipv4 && $ipv6}]
	[{include file="content/bottom/content/cats__net_ipv6_addresses.tpl"}]
[{else}]
	<div class="p5">
		<div class="p5 box-red">
			[{isys type="lang" ident="LC__CMDB__CATS__NET_IP_ADDRESSES__NO_NETWORK_DEFINED"}]
		</div>
	</div>
[{/if}]