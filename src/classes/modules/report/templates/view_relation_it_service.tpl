<script type="text/javascript">

	window.show_list = function () {

		$('C__OBJ__BROWSER__HIDDEN').value;

		$('ajax_loading_view').show();

		new Ajax.Updater('relation_list',
				this.location + "&request=show_list",
				{
					method:     'POST',
					parameters: {
						objID: $('C__OBJ__BROWSER__HIDDEN').value
					},
					onComplete: function () {
						$('ajax_loading_view').hide();
					}
				}
		);

	};

	window.collapse_it_service = function (p_it_service_id) {

		$('ajax_loading_view_' + p_it_service_id).style.display = "";

		if (!$("childs_" + p_it_service_id).visible())
		{

			$("childs_" + p_it_service_id).show();

			if ($("childs_content_" + p_it_service_id).innerHTML == "")
			{

				// GET PATH
				new Ajax.Updater('childs_content_' + p_it_service_id,
						this.location + "&request=get_path",
						{
							method:     'POST',
							parameters: {
								itservice: p_it_service_id,
								objID:     $('C__OBJ__BROWSER__HIDDEN').value
								//objID: 6373
							},
							onSuccess:  function () {

								$(p_it_service_id).style.background = "#EBF7F8";
								//$("childs_"+p_it_service_id).style.background = "#EDF6FF";
								if ($(p_it_service_id + "_plusminus"))
									$(p_it_service_id + "_plusminus").src = "[{$image_dir}]/nolines_minus.gif";
							}
						}
				);
			}
			else
			{
				$(p_it_service_id).style.background = "#EBF7F8";
				//$("childs_"+p_it_service_id).style.background = "#EDF6FF";
				if ($(p_it_service_id + "_plusminus"))
					$(p_it_service_id + "_plusminus").src = "[{$image_dir}]/nolines_minus.gif";
			}

		}
		else
		{
			$("childs_" + p_it_service_id).style.display = "none";
			$(p_it_service_id).style.background = "";
			if ($(p_it_service_id + "_plusminus"))
				$(p_it_service_id + "_plusminus").src = "[{$image_dir}]/nolines_plus.gif";
		}

		$('ajax_loading_view_' + p_it_service_id).style.display = "none";
	};
</script>

<style type="text/css">

	table.report_listing {
		border-collapse: collapse;
		border-spacing: 0px;
		border: 1px dotted #DDDDDD;
		padding: 0.33em 0.5em;
		vertical-align: top;

	}

	td.report_listing {
		border: 1px dotted #DDDDDD;
		padding: 0.33em 0.5em;
		vertical-align: top;
	}

	td.report_listing:hover {
		background-color: #EBF7F8;
	}

</style>

<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type="lang" ident="LC__CATG__CMDB__ODEP_ERROR_SELECT_OBJECT"}]
		</td>
		<td class="value">
			[{isys
			type="f_popup"
			p_strPopupType="browser_object_ng"
			callback_accept="show_list();"
			name="C__OBJ__BROWSER"}]
		</td>
	</tr>
</table>

<img src="[{$dir_images}]ajax-loading.gif" alt="" id="ajax_loading_view" style="display:none;" />

<div id="view_scroller" style="overflow:auto;">
	<table class="mainTable" id="view_table">
		<thead>
		<th class="pl5">
			[{isys type="lang" ident="LC__CMDB__CATG__IT_SERVICE"}]
		</th>
		</thead>
		<tr>
			<td>
				<div id="relation_list">
				</div>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	$('view_scroller').style.width = ($('contentArea').getWidth() - 50) + 'px';
	$('view_table').style.width = ($('view_scroller').getWidth() - 10) + 'px';
</script>