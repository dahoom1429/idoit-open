<style type="text/css">
	.centerIt * {
		text-align: center !important;
	}
</style>

<table class="contentTable centerIt">
	[{foreach $connections as $connection}]
	<tr>
		<td class="value">
			[{isys type="lang" ident="LC__CMDB__LOGBOOK__TITLE"}]<br />
			[{$connection[0].object_link}]
			<br /><br />
			[{isys type="lang" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]<br />
			[{$connection[0].connection_link}]
		</td>
		<td class="key">
			<strong>&lsaquo;&mdash; [{isys type="lang" ident="LC__CATS__CABLE__CONNECTION"}] &mdash;&rsaquo;</strong>
		</td>
		<td class="value">
			[{isys type="lang" ident="LC__CMDB__LOGBOOK__TITLE"}]<br />
			[{$connection[1].object_link|default:"-"}]
			<br /><br />
			[{isys type="lang" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]<br />
			[{$connection[1].connection_link|default:"-"}]
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<hr />
		</td>
	</tr>
	[{/foreach}]
</table>
