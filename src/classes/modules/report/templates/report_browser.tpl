<div id="reportBrowser" style="display:none; width:100%;">
	<table class="mainTable mouse-pointer" style="white-space: normal;">
		<thead>
		<tr>
			<th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__CATG__DESCRIPTION"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</th>
		</tr>
		</thead>
		<tbody id="reports">

		</tbody>
	</table>
</div>

<div id="reportExecute" style="display:none"></div>

<script type="text/javascript">
	[{include file="./report.js"}]

	var reports = [];

	new Ajax.Request(
		'[{$config.www_dir}]proxy.php?path=/&version=[{$gProductInfo.version}]',
		{
			method: 'POST',
			parameters: {
				json: 1,
				path: '/',
				version: '[{$gProductInfo.version}]'
			},
			onSuccess: function (transport) {
				var $report_table = $('reports');

				reports = transport.responseJSON;

				if (!reports) {
					alert(transport.responseText.stripTags());
					return;
				}

				for (var i = 0; i < reports.length; i++) {
					$report_table
						.insert(new Element('tr', {className:(i%2 ? 'line0' : 'line1'), 'data-index':i})
							.update(new Element('td').update(reports[i].isys_report__title))
							.insert(new Element('td').update(reports[i].isys_report__description))
							.insert(new Element('td', {className:'download', style:'text-align:center;'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/disk.png'}))));
				}

				$report_table.on('click', 'td:not(.download)', function (ev) {
					var index = ev.findElement('tr').readAttribute('data-index');

					show_report(reports[index]);
				});

				$report_table.on('click', 'td.download,td img', function (ev) {
					var index = ev.findElement('tr').readAttribute('data-index');

					download_report(reports[index], index);
				});

				Effect.Appear('reportBrowser', {duration: 0.25});
			}
		}
	);

	var show_report = function (row) {
		new Ajax.Updater(
			'reportExecute',
			'?ajax=1&[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__REPORT}]&request=executeReport',
			{
				method: 'POST',
				parameters: {
					reportID: row.isys_report__id,
					title: row.isys_report__title,
					desc: row.isys_report__description,
					query: row.isys_report__query
				},
				evalScripts:true,
				onSuccess: function () {
					$('reportBrowser').hide();

					Effect.Appear('reportExecute', {duration: 0.25});

					// We use this trick to start the TableOrder plugin after the ajax.
					setTimeout(function () {
						var data = $('data');

						if (data) {
							// When we find the "data"-input field we get the value and start the browser.
							window.build_table('list', data.getValue().evalJSON());
						} else if ($('data-1')) {
							// If we find a "data-1"-input field we can be sure that we've got some groups to iterate.
							$$('input.report-data').each(function (e, i) {
								window.build_table('list-' + (i + 1), e.value.evalJSON());
							})
						}
					}, 10);
				}
			}
		);
	};

	var download_report = function (row, index) {
		new Ajax.Updater(
			'infoBox',
			'?ajax=1&[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__REPORT}]&request=downloadReport',
			{
				method: 'POST',
				parameters: {
					reportID: row.isys_report__id,
					title: row.isys_report__title,
					desc: row.isys_report__description,
					query: row.isys_report__query
				},
				onSuccess: function () {
					$('reports').down('tr[data-index="' + this.index + '"]').highlight();
				}.bind({index:index})
			}
		);
	};

	// This will be called from a reloaded template (by showing a report and loading its detail view).
	function showReportList() {
		$('reportExecute').hide();
		Effect.Appear('reportBrowser', {duration: 0.5});
	}
</script>