<div id="content" class="content">
	<h2>Property Migration</h2>

	[{foreach from=$result key=db item=res}]
	<h3>Migrated classes in "[{$db}]"</h3>
	<table cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
		<thead>
		<tr>
			<th colspan="2">Class names</th>
		</tr>
		</thead>
		<tbody>
			[{foreach from=$res item=class}]
			<tr class="[{cycle values="even,odd"}]">
				<td>[{$class}]</td>
				<td><span class="bold" style="color:#11CC11;">DONE</span></td>
			</tr>
		[{/foreach}]
		</tbody>
	</table>
	[{/foreach}]
</div>