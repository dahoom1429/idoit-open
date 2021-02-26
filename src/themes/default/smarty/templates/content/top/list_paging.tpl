[{if is_array($pages) || isset($smarty.post.filter)}]
	<div class="right paging fl">
		[{if $C__NAVBAR_BUTTON__BACK}]
			<div class="pager-back">[{$C__NAVBAR_BUTTON__BACK}]</div>
		[{/if}]

		[{if is_array($pages) && count($pages) > 0}]
			<div class="pager-current fl">
				<select name="current_page" id="current_page"  onchange="change_page($('current_page').value, false, 'main_content', 'post');">
					[{html_options selected=$page_start options=$pages}]
				</select>
			</div>
		[{/if}]

		[{if $C__NAVBAR_BUTTON__FORWARD}]
			<div class="pager-forward fl">[{$C__NAVBAR_BUTTON__FORWARD}]</div>
		[{/if}]

		<div class="pager-results fl">
			[{if $page_current != "" && $page_max != "" && $page_info != ""}]
				<div style="display:inline;margin:5px 0;">
					<img class="vam" style="margin-top:-2px;" src="[{$dir_images}]/icons/silk/page_white_stack.png" width="15px" height="15px" alt="" />
					<span>[{$page_info}]</span>
				</div>
			[{/if}]

			[{if $page_results >0}]
				<div style="display:inline;margin:1px;">
					<img class="vam" style="margin-top:-2px;" src="[{$dir_images}]/icons/navbar/new_icon_inactive.png" width="15px" height="15px" alt="" />
					<span>[{$page_results}]</span>
				</div>
			[{/if}]
		</div>

		<div class="pager-filter fl">
			<input name="filter" type="search" class="input input-mini" placeholder="[{isys type="lang" ident="LC_UNIVERSAL__FILTER_LIST"}]" id="filter" incremental="incremental" value="[{$smarty.post.filter}]" />
		</div>
	</div>

	<script type="text/javascript">
		var $filter = $('filter'),
			$navPageStart = $('navPageStart');


		$filter.on('search', function() {
			$navPageStart.setValue(0);
			form_submit();
		});

		$filter.on('keypress', function (ev) {
			if ((ev.which && ev.which == Event.KEY_RETURN) || (ev.keyCode && ev.keyCode == Event.KEY_RETURN)) {
				$navPageStart.setValue(0);
				form_submit();
				ev.preventDefault();

				return false;
			}

			return true;
		});

		[{if $smarty.post.filter}]$filter.focus();$filter.setValue($filter.value);[{/if}]
	</script>
[{/if}]