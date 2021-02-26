<div id="contentHeader" class="contentHeaderSmall">
	<div class="fl border-right">
		[{isys type="object_image" p_bThumb="1"}]
	</div>

	<h2 class="m0 fl">
	[{if $categoryTitle != ""}]
		[{$content_title}]: [{isys type="f_data" name="C__CATG__TITLE" p_bInfoIconSpacer="0" len=50}] ([{$categoryTitle}])
	[{else}]
		[{$content_title}]
	[{/if}]

	[{if isset($g_locked)}]<span class="red">- LOCKED ([{$lock_user}]) -</span>[{/if}]
	</h2>

	[{*include file="content/top/list_paging.tpl"*}]

</div>