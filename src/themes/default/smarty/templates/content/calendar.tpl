<table class="calendar" width="100%">
	<thead>
	<tr>
		<th colspan="7">
			[{$year}]
		</th>
	</tr>
	</thead>
	<tbody>
	[{foreach from=$data item=week}]
	<tr>
		[{foreach from=$week item=day}]
		<td class="[{$day.css_class}]">
			[{$day.date}]
		</td>
		[{/foreach}]
	</tr>
	[{/foreach}]
	</tbody>
</table>