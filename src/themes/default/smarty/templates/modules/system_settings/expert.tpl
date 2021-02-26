<div class="bg-white">

	<div>

		<table id="expertTable" class="listing p0" style="border:0;">
			<colgroup>
				<col style="width:35%;" />
				<col style="width:35%;" />
				<col style="width:30%;" />
			</colgroup>
			<thead>
			<tr>
				<th>Key</th>
				<th>Value</th>
				<th>Type</th>
			</tr>
			</thead>
			<tbody>
			[{foreach from=$settings item="keys" key="type"}]

				[{foreach from=$keys item="value" key="key"}]
					[{if strpos($key, 'admin.') !== 0}]
						<tr>
							<td>
								<input type="hidden" name="remove_settings[[{$type}]][[{$key}]]" class="remove" value="0" />
								[{$key}]
							</td>
							<td>
								[{if is_scalar($value) && strstr($value, "\n")}]
									<textarea rows="8" class="input input-block" placeholder="[{$setting.placeholder}]"
									          name="settings[[{$type}]][[{$key}]]">[{$value|default:$setting.default}]</textarea>
								[{else}]
									<input class="input input-block" type="text" name="settings[[{$type}]][[{$key}]]" value="[{$value|default:$setting.default}]" />
								[{/if}]
							</td>
							<td style="padding-left:5px;">
								<a href="javascript:" class="remove hide fr"><img src="[{$dir_images}]/icons/silk/cross.png" /></a>
								[{$type}]
							</td>
						</tr>
					[{/if}]
				[{/foreach}]

			[{/foreach}]
			<tr>
				<td>
					<input class="input input-block" type="text" name="custom_settings[key][]" value="" placeholder="key" />
				</td>
				<td>
					<input class="input input-block" type="text" name="custom_settings[value][]" value="" placeholder="value" />
				</td>
				<td style="padding-left:5px;">
					<select name="custom_settings[type][]" class="input input-mini">
						<option value="[{isys_module_system_settings::SYSTEM_WIDE}]">[{isys_module_system_settings::SYSTEM_WIDE}]</option>
						<option value="[{isys_module_system_settings::TENANT_WIDE}]">[{isys_module_system_settings::TENANT_WIDE}]</option>
						<option value="[{isys_module_system_settings::USER}]">[{isys_module_system_settings::USER}]</option>
					</select>
					<img src="images/icons/silk/add.png" alt="" class="vam" />
				</td>
			</tr>
			</tbody>
		</table>

	</div>
</div>

<script type="text/javascript">
	var $expertTable = $('expertTable');

	$expertTable.on('mouseover', 'tbody tr', function(ev, el) {
		if (ev.altKey)
		{
			var $link = el.down('td:last-child a.remove');
			if ($link)
			{
				$link.removeClassName('hide');
			}
		}
	});

	$expertTable.on('mouseout', 'tbody tr', function(ev, el) {
		var $link = el.down('td:last-child a');

		if ($link)
		{
			$link.addClassName('hide');
		}
	});

	$expertTable.on('click', 'tbody tr td a.remove', function(ev, el) {
		var tr = el.up('tr');

		if (tr) {
			var input = tr.down('td:first-child').down('input.remove');

			if (input) {
				if (input.value == '0') {
					input.value = '1';
					tr.setStyle('text-decoration:line-through;').addClassName('redbg');
				}
				else {
					input.value = '0';
					tr.setStyle('text-decoration:none;').removeClassName('redbg');
				}
			}
			else idoit.Notify.warning('Error deleting line.');
		}
	});

	$expertTable.on('click', 'tr:last-child td:last-child img', function(ev, el) {
		$expertTable.down('tbody').insert(el.up('tr').innerHTML);

		el.remove();

		$$('tr:last-child td input').each(function(inp) {
			inp.value = '';
		});

		$('contentWrapper').scrollTop = $('contentWrapper').down('table').getHeight();
	});
</script>