<h3 class="gradient p5 text-shadow border-bottom border-grey">Quicklaunch</h3>

<div class="p5 quicklaunch">
	[{if count($function_list) > 0}]
	<div class="fl">
		<h4 class="m5"><img src="[{$dir_images}]icons/silk/table_edit.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_FUNCTIONS"}]</span></h4>
		<ul class="m0 list-style-none">
			[{foreach from=$function_list key="url" item="name"}]
			<li><a class="btn btn-block mb5" href="[{$url}]">[{$name}]</a></li>
			[{/foreach}]
		</ul>
	</div>
	[{/if}]

	[{if count($configuration_list) > 0}]
	<div class="fl">
		<h4 class="m5"><img src="[{$dir_images}]icons/silk/cog.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_CONFIGURATION"}]</span></h4>
		<ul class="m0 list-style-none">
			[{foreach from=$configuration_list key="url" item="name"}]
			<li><a class="btn btn-block mb5" href="[{$url}]">[{$name}]</a></li>
			[{/foreach}]
		</ul>
	</div>
	[{/if}]

	[{if $allow_update}]
	<div class="fl">
		<h4 class="m5"><img src="[{$dir_images}]icons/silk/arrow_refresh.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE"}]</span></h4>
		<ul class="m0 list-style-none">
			<li><a class="btn btn-block mb5" href="./updates">i-doit Update</a></li>
		</ul>
	</div>
	[{/if}]
	<br class="cb" />
</div>

<style type="text/css">
	.quicklaunch .fl {
		width: 200px;
		padding-right: 20px;
	}

	.quicklaunch .fl:last-of-type {
		padding-right: 0;
	}
</style>