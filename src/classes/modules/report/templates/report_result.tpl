<div class="p5 gradient border-bottom" style="text-shadow: 0 1px 0 #FFFFFF;">
	<h2>[{$reportTitle}] <span class="grey small">[[{$listing.num}] [{isys type="lang" ident="LC__REPORT__MATCHES"}]]</span></h2>
	<p>[{$reportDescription}]</p>
</div>

<div class="p5 mb5" style="cursor:pointer;" onclick="showReportList();">
	<img src="[{$dir_images}]icons/silk/arrow_up.png" class="vam"/> [{isys type="lang" ident="LC__UNIVERSAL__BACK"}]
</div>

[{if is_array($listing.headers)}]
	<script type="text/javascript">
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__EMPTY_RESULTS"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_DATA"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__ERROR_URL"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__FILTER_LABEL"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__LOADING"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__SEARCH_LABEL"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_OF"}]');
		idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__SCRIPT_JS__PAGINATEN_PAGES"}]');
	</script>

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
