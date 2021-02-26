<div class="content">
	<h2>Property Migration</h2>

	<table cellspacing="0" class="sortable mt10">
		<colgroup>
			<col width="75%" />
			<col width="25%" />
		</colgroup>
		<thead>
		<tr>
			<th colspan="2">Tenant</th>
		</tr>
		</thead>
		<tbody>
		[{foreach $result as $mandator}]
		<tr>
			<td>[{$mandator}]</td>
			<td class="green bold">DONE</td>
		</tr>
		[{foreachelse}]
		<tr><td colspan="2">No tenants to migrate.</td></tr>
		[{/foreach}]
		</tbody>
	</table>
</div>