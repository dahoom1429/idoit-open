<div>

	<table class="contentTable">
		<tr>
			<td class="key">[{isys type="lang" ident="LC__REPORT__VIEW__PLEASE_CHOOSE_OBJECTGROUP"}]</td>
			<td class="value">[{isys
				name="C__DIALOG_OBJECTGROUP"
				type="f_dialog"
				p_bEditMode=true
				p_strSelectedID=$smarty.post.C__DIALOG_OBJECTGROUP|strip_tags|escape
				p_bDbFieldNN=1
				tab="3"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__REPORT__VIEW__PLEASE_CHOOSE_OBJECTTYPE"}]</td>
			<td class="value" id="C__DIALOG_OBJECTTYPE_TD">
				[{isys
				name="C__DIALOG_OBJECTTYPE"
				type="f_dialog"
				p_bEditMode=true
				p_strSelectedID=$smarty.post.C__DIALOG_OBJECTTYPE|strip_tags|escape
				condition="isys_obj_type__show_in_tree=1"
				p_bDbFieldNN=1
				tab="3"}]
			</td>
		</tr>
		<tr>
			<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__STATUS"}]</td>
			<td class="value">
				[{isys
				name="C__DIALOG_STATUS"
				type="f_dialog"
				p_bEditMode=true
				p_bDbFieldNN=1
				tab="3"}]
			</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td class="value">
				[{isys
				type="f_button"
				p_strValue="LC_UNIVERSAL__FILTER"
				p_bEditMode=true
				name="filter-report-view"
				p_strClass="ml20"}]<img src="[{$dir_images}]ajax-loading.gif" alt="" id="ajax_loading_view" style="display:none;" class="vam"/>
			</td>
		</tr>
	</table>

	<fieldset class="overview">
		<legend>
			<span>
				[{isys type="lang" ident="LC__UNIVERSAL__RESULT"}]
			</span>
		</legend>

		<div id="report_view_no_relations" class="mt10 mb10"></div>
	</fieldset>
</div>
<script type="text/javascript">
	(function () {
		"use strict";

		$('C__DIALOG_OBJECTGROUP').on('change', function(){
			var objgroup = this.value;
			new Ajax.Updater('C__DIALOG_OBJECTTYPE_TD', '[{$ajax_url}]&func=reload_objecttypes', {
				method: 'POST',
				parameters: {
					objTypeGroupID: objgroup
				}
			});
		});

		$('filter-report-view').on('click', function(){
			$('ajax_loading_view').style.display="";

			new Ajax.Request('[{$ajax_url}]&func=show_list', {
				method: "post",
				parameters: $('isys_form').serialize(true),
				onComplete: function (transport) {
					var json_data = transport.responseJSON;
					var log_data = json_data['data'];

					$('ajax_loading_view').style.display="none";

					if (json_data['success']) {
						var ajax_pager = false,
								ajax_pager_url = '',
								ajax_pager_preload = 0,
								max_pages = 0,
								page_limt = '[{$page_limit}]',
								name = 'report_view_no_relations';

						window.currentReportView = new Lists.Objects(name, {
							classPrefix: 'mainTable',
							max_pages: max_pages,
							ajax_pager: ajax_pager,
							ajax_pager_url: ajax_pager_url,
							ajax_pager_preload: ajax_pager_preload,
							data: log_data,
							filter: "top",
							paginate: "top",
							pageCount: page_limt,
							draggable: false,
							checkboxes: true
						});

						new Effect.SlideDown('view-no-relation-actions', {duration: 0.5});
					} else {
						$('report_view_no_relations').update(log_data);
					}
				}
			});
		});

		[{if $fire_filter === true}]
			$('filter-report-view').simulate('click');
		[{/if}]

	})();
</script>