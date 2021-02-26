<div>
    <div class="p10">
        <table class="contentTable">
            <tr>
                <td class="key">[{isys type="f_label" name="C__CMDB_CHANGES__PERIOD" ident="LC__REPORT__VIEW__CMDB_CHANGES__PERIOD"}]</td>
                <td class="value">
                    [{isys type="f_popup" name="C__CMDB_CHANGES__PERIOD_FROM" p_strPopupType="calendar" p_strClass="input-mini" p_bTime=0}]
	                [{isys type="f_popup" name="C__CMDB_CHANGES__PERIOD_TO" p_strPopupType="calendar" p_strClass="input-mini" p_bTime=0}]
                </td>
            </tr>
            <tr>
                <td class="key">[{isys type="f_label" name="C__CMDB_CHANGES__PERSONS" ident="LC__REPORT__VIEW__CMDB_CHANGES__PERSONS"}]</td>
                <td class="value">
                    [{isys
                    type="f_popup"
                    name="C__CMDB_CHANGES__PERSONS"
                    p_strPopupType="browser_object_ng"
                    p_strClass="input-small"
                    multiselection=true
                    catFilter="C__CATS__PERSON_MASTER"}]
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="button" id="data-loader" class="btn ml20">
	                    <img src="[{$dir_images}]icons/silk/database_table.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</span>
                    </button>
                </td>
            </tr>
        </table>
    </div>

    <fieldset class="overview">
        <legend>
			<span>
				[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]
			</span>
        </legend>

        <div id="report_view_cmdb_changes_result" class="mt10 pt10"></div>
    </fieldset>
</div>

<script type="text/javascript">
(function () {
	"use strict";

	var $button = $('data-loader');

	$button.on('click', function () {
		$button
			.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
			.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

		new Ajax.Request('[{$ajax_url}]', {
			method: "post",
			parameters: $('isys_form').serialize(true),
			onComplete: function (transport) {
				var json_data = transport.responseJSON;
				var log_data = json_data['data'];

				if (json_data['success']) {
					var ajax_pager = false,
						ajax_pager_url = '',
						ajax_pager_preload = 0,
						max_pages = 0,
						page_limt = '[{$page_limit}]',
						name = 'report_view_cmdb_changes_result';

					window.currentReportView = new Lists.Objects(name, {
						max_pages: max_pages,
						ajax_pager: ajax_pager,
						ajax_pager_url: ajax_pager_url,
						ajax_pager_preload: ajax_pager_preload,
						data: log_data,
						filter: "top",
						paginate: "top",
						pageCount: page_limt,
						draggable: false,
						checkboxes: false
					});
				} else {
					$('report_view_cmdb_changes_result').update(log_data);
				}

				$button
					.down('img').writeAttribute('src', '[{$dir_images}]icons/silk/database_table.png')
					.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]');
			}
		});
	});
}());
</script>