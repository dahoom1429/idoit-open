<h2>Overview (Log)</h2>

<table cellspacing="0" class="sortable mt10">
	<colgroup>
		<col width="80%" />
		<col width="20%" />
	</colgroup>
	<thead>
		<tr>
			<th>Process</th>
			<th>Success</th>
		</tr>
	</thead>
	<tbody>
		[{foreach $log as $item}]
		<tr class="[{cycle values="even,odd"}]">
			<td><span class="[{$item.class}]">[{$item.message}]</span></td>
			<td><span class="bold" style="color:[{$item.color}]">[{$item.result}]</span></td>
		</tr>
		[{/foreach}]
	</tbody>
</table>