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

	td.child {
		cursor: pointer;
		vertical-align: middle;
		background-color: #eee;
		padding: 0 5px 5px;
		border: 2px solid #ddd;
	}
</style>

<script type="text/javascript">
	var show_relations = function (id) {
		if ($("row_" + id).innerHTML == "") {
			$('ajax_loading_view_' + id).style.display = "";
			new Ajax.Updater('row_' + id,
				this.location + "&request=show_relations&ajax=1",
				{
					method: 'POST',
					parameters: {
						objID: id
					},
					onComplete: function () {
						$('ajax_loading_view_' + id).style.display = "none";
					}
				}
			);
		}
	};

	var collapse_it_service = function (p_it_service_id) {
		var row = $("row_" + p_it_service_id);

		if (row.style.display == "none" || row.innerHTML == "") {
			row.style.display = "";

			if ($(p_it_service_id + "_plusminus"))
				$(p_it_service_id + "_plusminus").src = "[{$image_dir}]/nolines_minus.gif";

		} else {
			row.style.display = "none";

			if ($(p_it_service_id + "_plusminus"))
				$(p_it_service_id + "_plusminus").src = "[{$image_dir}]/nolines_plus.gif";
		}
	};
</script>
<div id="view_scroller" style="overflow:auto;">
	[{$viewContent}]
</div>