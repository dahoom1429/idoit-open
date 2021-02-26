[{if $inherited_guest_systems}]
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
		<tr>
			<th colspan="6">[{isys type="lang" ident="LC__CMDB__CATG__GUEST_SYSTEM_INHERITED_BY_CLUSTER"}]</th>
		</tr>
		[{foreach $inherited_guest_systems as $inheritance}]
			<tr class="listRow">
				<td><input type="checkbox" disabled="disabled" class="checkbox"></td>
				<td>[{$inheritance.obj_title}]</td>
				<td>[{$inheritance.obj_type_title}]</td>
				<td>[{$inheritance.hostname}]</td>
				<td>[{$inheritance.ip_address}]</td>
				<td>[{$inheritance.runs_on}]</td>
			</tr>
		[{/foreach}]
		</tbody>
	</table>
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
