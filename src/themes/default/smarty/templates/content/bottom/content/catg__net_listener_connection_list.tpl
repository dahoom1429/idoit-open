<fieldset class="overview">
	<legend><span>[{isys type="lang" ident="LC__CATG__NET_CONNECTIONS"}]</span></legend>

	<table class="listing">
		<thead>
			<tr>
				<th>[{isys type="lang" ident="LC__CMDB__CONNECTED_WITH"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__NET_LISTENER__PROTOCOL"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__NET_CONNECTOR__IP_ADDRESS"}]</th>
				<th>Port [{isys type="lang" ident="LC_UNIVERSAL__FROM"}]/[{isys type="lang" ident="LC__UNIVERSAL__TO"}]</th>
				<th>[{isys type="lang" ident="LC__CMDB__CATG__RELATION__RELATION_OBJECT"}]</th>
			</tr>
		</thead>
		<tbody>
		[{foreach from=$connectors item="row"}]

			<tr>
				<td><a href="?objID=[{$row.source_object_id}]">[{$row.source_object}]</a></td>
				<td>[{$row.protocol}]</td>
				<td>[{$row.source_ip}]</td>
				<td>[{$row.source_port_from}] - [{$row.source_port_to}]</td>
				<td><a href="?objID=[{$row.relation_object_id}]">[{$row.relation_object}]</a></td>

			</tr>

		[{/foreach}]
		</tbody>
	</table>
</fieldset>