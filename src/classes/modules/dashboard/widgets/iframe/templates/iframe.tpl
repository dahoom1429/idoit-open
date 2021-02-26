<div class="gradient p5 text-shadow"><h3>[{$title|default:"Web-Browser"}]</h3></div>

[{if $url}]
	<iframe [{$sandbox}] src="[{$url}]" style="width:100%;height:[{$height|default:400}]px;border:0;"></iframe>
[{/if}]
