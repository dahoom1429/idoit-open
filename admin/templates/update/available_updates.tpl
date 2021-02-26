<style type="text/css">
	#message-box span,
	#message-box img {
		vertical-align: middle;
	}

	#changelog-popup {

	}
</style>

<h2>Available Updates</h2>

[{if !$licence_error}]
	<h3>New Versions</h3>
	<p>New version can be retrieved <a href="[{$site_url}]">here</a>.</p>

	<table cellspacing="0" cellpadding="0">
		<tr>
			<td><p>But you can also let the updater do the work</p></td>
			<td>
				<button type="button" id="update-check-button" class="btn ml10" style="width:262px;">
					<img src="../images/icons/silk/zoom.png" class="mr5" />
					<span>Check for a new version</span>
				</button>
			</td>
		</tr>
		<tr>
			<td><p>Or enter the URL of an update package yourself</p></td>
			<td>
				<input type="text" id="download-input" class="input ml10 mt5" style="width:250px;" placeholder="[{$site_url}]/downloads/*"  />

				<button type="button" id="download-button" class="btn">
					<img src="../images/icons/silk/arrow_down.png" class="mr5" />
					<span>Download and extract</span>
				</button>
			</td>
		</tr>
	</table>

	<div id="message-box" class="p5"></div>

	<h3>You can update to the following already downloaded versions</h3>
	<table id="versions-table" cellspacing="0" class="sortable mt10">
		<colgroup>
			<col width="5%" />
			<col width="35%" />
			<col width="35%" />
			<col width="25%" />
		</colgroup>
		<thead>
		<tr>
			<th>Use</th>
			<th>Name</th>
			<th>Version</th>
			<th>Requirements</th>
		</tr>
		</thead>
		<tbody>

		</tbody>
	</table>

	<p>Select a version and click "Next" to go to the next step. Your current version is: <strong>[{$g_info.version|default:"< 1.4"}] (rev [{$g_info.revision|default:"< 17000"}])</strong></p>

	<fieldset id="changelog-popup" class="overview hidden">
		<legend>
			<span onclick="$('changelog-popup').addClassName('hidden');">Changelog</span>
		</legend>
		<p class="text" style="margin-top:15px;"></p>
	</fieldset>
[{else}]
	<div class="error p5">
		<img src="../images/icons/silk/cross.png" class="mr5 vam" /><span>[{$licence_error}]</span>
	</div>
[{/if}]


<script type="text/javascript">
	var messagebox = $('message-box'),
		updates = '[{$updates|escape:'javascript'}]'.evalJSON(),
		current_rev = parseInt('[{$g_info.revision|default:"< 17000"}]'),
		current_formdata = '[{$current_formdata_json}]'.evalJSON();

	$('update-check-button').on('click', function (ev) {
		var button = ev.findElement('button');

		button.down('img').writeAttribute('src', '../images/ajax-loading.gif');

		new Ajax.Request('[{$ajax_url}]', {
			parameters: {
				check_update: 1
			},
			onSuccess: function (response) {
				var json = response.responseJSON;

				button.down('img').writeAttribute('src', '../images/icons/silk/zoom.png');
				messagebox.removeClassName('success').removeClassName('error').removeClassName('info');

				if (json.success) {
					if (json.data.update !== null) {
						messagebox.addClassName('success')
							.update(new Element('img', {src: '../images/icons/silk/tick.png', className: 'mr5'}))
							.insert(new Element('span').update(json.data.message));

						// We set the download URL and notify the user.
						$('download-input').setValue(json.data.update.filename).highlight();
					} else {
						messagebox.addClassName('info')
							.update(new Element('img', {src: '../images/icons/silk/information.png', className: 'mr5'}))
							.insert(new Element('span').update(json.data.message));
					}
				} else {
					messagebox.addClassName('error')
						.update(new Element('img', {src: '../images/icons/silk/cross.png', className: 'mr5'}))
						.insert(new Element('span').update(json.message));
				}
			}.bind(button)
		});
	});

	$('download-button').on('click', function (ev) {
		var button = ev.findElement('button');

		button.down('img').writeAttribute('src', '../images/ajax-loading.gif');

		new Ajax.Request('[{$ajax_url}]', {
			parameters: {
				process_download: 1,
				process_download_url: $F('download-input')
			},
			onSuccess: function (response) {
				var json = response.responseJSON;

				button.down('img').writeAttribute('src', '../images/icons/silk/arrow_down.png');
				messagebox.removeClassName('success').removeClassName('error').removeClassName('info');

				if (json.success) {
					if (json.data.success) {
						// Update the list data.
						updates = json.data.updates.evalJSON();

						// And render the table.
						render_versions_table();

						messagebox.addClassName('success')
							.update(new Element('img', {src: '../images/icons/silk/tick.png', className: 'mr5'}))
							.insert(new Element('span').update(json.data.message));
					} else {
						messagebox.addClassName('error')
							.update(new Element('img', {src: '../images/icons/silk/cross.png', className: 'mr5'}))
							.insert(new Element('span').update(json.data.message));
					}
				} else {
					messagebox.addClassName('error')
						.update(new Element('img', {src: '../images/icons/silk/cross.png', className: 'mr5'}))
						.insert(new Element('span').update(json.message));
				}
			}.bind(button)
		});
	});

	function render_versions_table () {
		var row, table = $('versions-table').down('tbody').update(), changelog, last_radiobutton, radio, title_css_class;

		for (row in updates) {
			if (updates.hasOwnProperty(row)) {
				changelog = title_css_class = '';
				radio = new Element('input', {name:'update', value:updates[row].directory, type:'radio'});

				if (updates[row].hasOwnProperty('changelog') && Object.isString(updates[row].changelog) && ! updates[row].changelog.blank()) {
					changelog = ' (' + new Element('a', {href:'javascript:display_changelog(' + updates[row].revision + ')'}).update('changelog').outerHTML + ')';
				}

				if (current_rev > updates[row].revision) {
					radio.disable();
					title_css_class = 'grey';
				} else if (current_rev == updates[row].revision) {
					title_css_class = 'bold';
				}

				table.insert(
					new Element('tr', {className:(row % 2 ? 'odd' : 'even')})
						.update(new Element('td').update(radio))
						.insert(new Element('td').update(new Element('span', {className:title_css_class}).update(updates[row].title + changelog)))
						.insert(new Element('td').update(updates[row].version + ' (' + updates[row].revision + ')'))
						.insert(new Element('td').update(updates[row].requirement.version + ' rev ' + updates[row].requirement.revision))
				);
			}
		}

		last_radiobutton = table.select('input:not([disabled])');

		if (last_radiobutton.length > 0) {
			// This will check the (previously) selected update package.
			if (current_formdata.hasOwnProperty('update')) {
				$$('input[value="' + current_formdata.update + '"]')[0].checked = true;
			} else {
				last_radiobutton.last().checked = true;
			}
		} else {
			$('update-button-next').disable();
		}
	}

	window.display_changelog = function(rev) {
		var row;

		for (row in updates) {
			if (updates.hasOwnProperty(row)) {
				if (rev == updates[row].revision) {
					$('changelog-popup').removeClassName('hidden')
						.down('legend span').update('Changelog: ' + updates[row].title + ', rev ' + updates[row].revision);
					$('changelog-popup')
							.down('p.text').update('<pre>' + updates[row].changelog + '</pre>');
				}
			}
		}
	};

	render_versions_table();
</script>