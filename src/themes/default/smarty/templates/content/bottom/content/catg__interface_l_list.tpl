[{if count($stack_ports) > 0}]
	<div id="mainTableAddition">
		<table cellspacing="0" class="mainTable mt10 border-top grey" id="mainTableAddition">
			<colgroup>
				<col/>
				<col/>
				<col/>
				<col/>
				<col/>
				<col/>
			</colgroup>
			<tbody>
			[{foreach $stack_ports as $stacking => $ports}]
			[{if is_array($ports) && count($ports)}]
				<tr>
					<th colspan="6">[{isys type="lang" ident="LC__CMDB__CATG__INTERFACE_L__LOG_PORTS_FROM_STACK"}] "[{$stacking}]"</th>
				</tr>
				[{foreach $ports as $port}]
					<tr class="listRow">
						<td><input type="checkbox" disabled="disabled" class="checkbox"></td>
						<td>[{$port.title}]</td>
						<td>[{$port.type}]</td>
						<td>[{$port.ip_address}]</td>
						<td>[{$port.layer2_net_assignment}]</td>
						<td>[{$port.destination}]</td>
					</tr>
				[{/foreach}]
			[{/if}]
			[{/foreach}]
			</tbody>
		</table>
	</div>

	<script type="text/javascript">
		var $table = $$('.mainTableHover')[0].addClassName('border-bottom'),
			$table_header = $table.select('th'),
			$sub_table = $('mainTableAddition'),
			$sub_table_cols = $sub_table.select('col');

		// This little script will set the "mainTableAddition" columns to the same width as the ones in "mainTable".
		$table.select('th').each(function ($th, i) {
			$sub_table_cols[i].setStyle({width: $th.getWidth() + 'px'});
		});

		// This little script will continue the "even/odd" colors for the sub-table.
		$sub_table.select('tr.listRow').each(function ($tr, i) {
			$tr.addClassName((i % 2) ? 'CMDBListElementsEven' : 'CMDBListElementsOdd');
		});
	</script>
[{/if}]
