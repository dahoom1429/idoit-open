<h2 class="header gradient text-shadow p5">Scripts</h2>
<div class="p5 bg-white">
	<ul class="m0 list-style-none">
		[{foreach from=$scripts item=script}]
		<li>
			<a class="btn" href="[{$import_path}]scripts/[{$script}]"><img src="[{$dir_images}]icons/silk/disk.png" class="mr5"/><span>[{$script}]</span></a>
		</li>
		[{/foreach}]
	</ul>
</div>