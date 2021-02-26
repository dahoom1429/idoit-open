<div id="catg-stack-view" class="p5">
	[{if $is_stacked}]
		[{foreach $stacks as $stack}]
		<div class="mb5 border">
			<h3 class="border-bottom gradient p5 text-shadow">[{isys type="lang" ident="LC__CATG__STACK_MEMBERSHIP__STACK"}]: [{$stack.quickinfo}]</h3>

			<ul class="list-style-none m0">
				[{foreach $stack.members as $member}]
				<li>[{$member}]</li>
				[{/foreach}]
			</ul>
		</div>
		[{/foreach}]
	[{else}]
		<p class="p5 box-blue"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span>[{isys type="lang" ident="LC__CATG__STACK_MEMBERSHIP__NO_MEMBERSHIP"}]</span></p>
	[{/if}]
</div>

<style type="text/css">
	#catg-stack-view li {
		padding: 5px;
		border-bottom: 1px solid #888;
	}

	#catg-stack-view li:last-child {
		border-bottom: none;
	}
</style>