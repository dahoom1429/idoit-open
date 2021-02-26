<h2>System database</h2>
<table cellspacing="0" class="sortable mt10 mb15">
	<colgroup>
		<col width="5%" />
		<col width="30%" />
		<col width="10%" />
	</colgroup>
	<thead>
		<tr>
			<th>Update</th>
			<th>Name</th>
			<th>Type</th>
			<th>Version</th>
		</tr>
	</thead>
	<tbody>
		<tr class="[{cycle values="even,odd"}]">
			<td><input type="checkbox" name="system_database" value="[{$g_system_database}]" checked="checked" /></td>
			<td><strong>[{$g_system_database}]</strong></td>
			<td>System</td>
			<td>[{$g_info.version|default:"n/a"}] - rev [{$g_info.revision|default:0}]</td>
		</tr>
	</tbody>
</table>

<h2>Tenant database(s)</h2>
<table cellspacing="0" class="sortable mt10 mb15">
	<colgroup>
		<col width="5%" />
		<col width="30%" />
		<col width="10%" />
	</colgroup>
	<thead>
	<tr>
		<th>Update</th>
		<th>Name</th>
		<th>Type</th>
		<th>Version</th>
	</tr>
	</thead>
	<tbody>
	[{foreach $databases as $i => $database}]
		<tr class="[{cycle values="even,odd"}]">
			<td><input type="checkbox" name="mandator_[{$i}]" value="[{$database.name}]" checked="checked" /></td>
			<td><strong>[{$database.name}]</strong></td>
			<td>[{$database.type|default:"Mandant"}]</td>
			<td>[{$database.version|default:"n/a"}] - rev [{$database.revision|default:0}]</td>
		</tr>
	[{/foreach}]
	</tbody>
</table>

<p class="ml5">
	<label><input type="checkbox" id="select-all" checked="checked" /> <strong>Select all</strong></label>
</p>

<p>Select the database(s) you want to update. It is highly recommended to update <strong>all</strong> databases.</p>

[{if $sql_mode}]
	<div class="error p5">
		<p>Warning: SQL-Strictmode is active!</p>
		<p>We recommend to disable the SQL-Strictmode for better compatibility. To prevent problems during the update process i-doit will disable it for you.</p>
	</div>
[{/if}]

<script type="text/javascript">
	var current_formdata = '[{$current_formdata_json}]'.evalJSON(),
		rows = $$('table.sortable tbody tr');

	// Select / Deselect the inputs and add observer.
	rows.each(function (el) {
			var input = el.down('input');

			input.checked = (current_formdata.hasOwnProperty(input.readAttribute('name')));
		}).invoke('on', 'click', function (ev) {
				var el = ev.findElement(),
					input = el.up('tr').down('input');

				// Only trigger, if we do not click the input itself.
				if (el.tagName.toLowerCase() != 'input') {
					input.checked = !input.checked;
				}
		});

	// Observe the "select all" button and set its "checked" state. (We use "observe" because "on" will not return the element but the event).
	$('select-all').observe('change', function (ev) {
		var all = ev.findElement('input');

		$$('table.sortable input').each(function (el) {
			el.checked = all.checked;
		});
	}).checked = ($$('table.sortable tbody input:checked').length == rows.length);

	window.next_callback = function () {
		if ($$('table.sortable tbody input:checked').length == 0) {

			$('update-error').removeClassName('hide').update('Please choose at least one database.').highlight();

			return false;
		}

		return true;
	}
</script>

