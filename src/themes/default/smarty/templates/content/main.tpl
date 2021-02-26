[{isys_group name="content"}]
	[{if isset($index_includes.navbar)}]
		[{isys_group name="navbar"}]
			[{include file=$index_includes.navbar}]
		[{/isys_group}]
	[{/if}]

	<div id="contentWrapper" class="display-container">
		<div id="ajaxReturnNote" style="display:none;" class="m5"></div>

		[{isys_group name="top"}]
			[{if $index_includes.contenttop !== false}]
				[{include file=$index_includes.contenttop|default:"content/top/main.tpl"}]
			[{/if}]
		[{/isys_group}]

		[{isys_group name="bottom"}]
			[{include file=$index_includes.contentbottom|default:"content/bottom/main.tpl"}]
		[{/isys_group}]
    </div>
[{/isys_group}]