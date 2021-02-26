<h3 class="popup-header">
	<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png" onclick="popup_close();" />
	<span>[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_PREVIEW"}]</span>
</h3>
<div class="popup-content" style="overflow-x: auto;height:480px;">
    [{if $groupingRelatedSortingHint}]
        <div class="m5 p10 box-blue">
            <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />
            [{$groupingRelatedSortingHint}]
        </div>
    [{/if}]
	<div id="mainList">
		[{if $message}]
			<div class="m5 p5 [{$message_class}]">[{$message}]</div>
		[{/if}]
	</div>
</div>
<script type="text/javascript">
	[{if $show_preview}]
	new Lists.ReportList('mainList', {
		data: [{$l_json_data}],
		draggable: false,
		checkboxes: false,
		tr_click: false,
        unsortedColumn: [{$unsortedColumns}]
	});
	[{/if}]
</script>