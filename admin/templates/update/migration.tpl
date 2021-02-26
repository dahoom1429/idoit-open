<h2>Migration</h2>

[{foreach $migration_log as $mandator => $log}]
	<h3>[{$mandator}]</h3>
	<table cellspacing="0" class="sortable mt10">
		<thead>
		<tr>
			<th>Log-Message</th>
		</tr>
		</thead>
		<tbody>
		[{foreach $log as $item}]
			[{foreach $item as $message}]
				<tr class="[{cycle values="even,odd"}]">
					[{if $message == "-"}]
						<td bgcolor="#ccc"></td>
					[{else}]
						<td><span>[{$message}]</span></td>
					[{/if}]
				</tr>
			[{/foreach}]
		[{/foreach}]
		</tbody>
	</table>
[{/foreach}]