<table class="mainTable">
	<thead>
	<tr>
		<th>[{isys type="lang" ident="LC__UNIVERSAL__ID"}]</th>
		<th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK"}]</th>
		<th>[{isys type="lang" ident="LC__CMDB__CATS__NET__ZONE_RANGE_FROM"}]</th>
		<th>[{isys type="lang" ident="LC__CMDB__CATS__NET__ZONE_RANGE_TO"}]</th>
	</tr>
	</thead>
	<tbody>
	[{if is_array($layer3_usage) && count($layer3_usage)}]
		[{foreach $layer3_usage as $layer3}]
			<tr>
				<td>[{$layer3.id}]</td>
				<td>[{$layer3.title}]</td>
				<td>[{$layer3.from}]</td>
				<td>[{$layer3.to}]</td>
			</tr>
		[{/foreach}]
	[{else}]
		<tr>
			<td colspan="4"></td>
		</tr>
	[{/if}]
	</tbody>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

	}());
</script>