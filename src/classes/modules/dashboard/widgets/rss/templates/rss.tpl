<div class="gradient text-shadow">
	[{if $rss->get_image_url()}]
		<div class="fr" style="margin:2px 5px 2px;">
			<img src="[{$rss->get_image_url()}]" height="20" />
		</div>
	[{/if}]

	<h3 class="p5">
		<a href="[{$rss->get_permalink()}]" target="_blank">[{$rss->get_title()}]</a>: [{$rss->get_description()}]
	</h3>
</div>

<img src="images/icons/silk/rss.png" class="m10 fr" height="16" />
<div class="rss">
[{foreach from=$rss->get_items(0, $count) item="item"}]

	<div class="item">
		<h3 class="title"><a href="[{$item->get_permalink()}]" target="_blank">[{$item->get_title()}]</a></h3>
		<p class="description">[{$item->get_description()}]</p>
		<p class="datetime"><small>[{isys type="lang" ident="LC__WIDGET__RSS__POSTED_ON"}] [{$item->get_date($dateFormat)}]</small></p>
	</div>

[{/foreach}]
</div>