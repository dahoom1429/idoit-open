<div class="p5 gradient" id="reportHeader" style="text-shadow: 0 1px 0 #FFFFFF;">
	[{if $showReportExport !== false}]
	<div class="fr export-report" style="margin:-2px;">
        [{if $allowedObjectGroup}]<a id="createObjectGroup" class="btn btn-small" href="javascript: void(0);"><img class="mr5" src="[{$dir_images}]icons/silk/page_white_star.png" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT"}]</span></a>[{/if}]
		<a class="btn btn-small export-btn" data-report-id="[{$report_id}]" data-export-type="txt"><img class="mr5" src="[{$dir_images}]icons/silk/page_white_text.png" /><span>TXT</span></a>
		<a class="btn btn-small export-btn" data-report-id="[{$report_id}]" data-export-type="csv"><img class="mr5" src="[{$dir_images}]icons/silk/page_white_office.png" /><span>CSV</span></a>
		<a class="btn btn-small export-btn" data-report-id="[{$report_id}]" data-export-type="xml"><img class="mr5" src="[{$dir_images}]icons/silk/page_white_code.png" /><span>XML</span></a>
		<a class="btn btn-small export-btn" data-report-id="[{$report_id}]" data-export-type="pdf"><img class="mr5" src="[{$dir_images}]icons/silk/page_white_acrobat.png" /><span>PDF</span></a>
	</div>
	[{/if}]
    <input type="hidden" name="report_id" value="[{$report_id}]">
    <input type="hidden" name="querybuilder" id="querybuilder" value='[{$querybuilder|escape:"javascript"}]'>
	<h2><span id="report-title">[{$reportTitle}]</span> <span class="text-grey report-matches" data-count="[{$rowcount}]">[[{$rowcount}] [{isys type="lang" ident="LC__REPORT__MATCHES"}]]</span></h2>
	<p>[{$reportDescription}]</p>
</div>

<script type="text/javascript">
	[{include file="./report.js"}]
</script>

[{if is_array($listing.headers)}]
	[{if $listing.grouped}]
		[{include file="./listing_group.tpl"}]
	[{else}]
		[{include file="./listing.tpl"}]
	[{/if}]

[{elseif $listing.num eq 0}]
	<div class="p5">
		<p>[{isys type="lang" ident="LC__REPORT__EMPTY_RESULT"}]</p>
	</div>
[{else}]
	<div class="p5">
		<p>[{isys type="lang" ident="LC__REPORT__EXCEPTION_TRIGGERED"}]</p>
	</div>
[{/if}]

