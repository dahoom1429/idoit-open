<div style="overflow: auto;">
	[{foreach from=$result item=json key=name}]
		<h3>[{$name}]</h3>
		<div id="list-[{counter skip=0}]"></div>
		<input id="data-[{counter skip=1}]" class="report-data" type="hidden" value='[{$json}]' />

		<hr />
	[{/foreach}]
</div>
<script type="text/javascript">
	$$('input.report-data').each(function(i, e) {
		window.build_table('list-' + (e+1), i.value.evalJSON(), [{$ajax_pager}], '[{$ajax_url}]', '[{$preload_pages}]', '[{$max_pages}]');
	});
</script>