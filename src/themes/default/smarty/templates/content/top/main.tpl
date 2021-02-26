[{if $content_title}]
	<div id="contentHeader" class="contentHeaderSmall">
		<h2 class="m0 fl">[{$content_title}]</h2>

		[{*include file="content/top/list_paging.tpl"*}]
	</div>
[{else}]
	[{if (is_array($pages) && count($pages) > 0) || isset($smarty.post.filter)}]
		<div id="contentHeader" class="contentHeaderSmall"></div>
	[{/if}]
[{/if}]
