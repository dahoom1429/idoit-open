<style type="text/css">
	#[{$unique_id}].welcome div.marker {
		width: 15px;
		height: 15px;
		text-align: center;
		font-weight: bold;
		color: #E64117;
	}
</style>

<h3 class="p5 border-bottom border-grey gradient text-shadow">[{$salutation}]!</h3>


<div id="[{$unique_id}]" class="m5 welcome">
	<table class="two-col mt10" cellspacing="0" cellpadding="0" width="100%" height="180px">
		<tr>
			<td class="vat">
				<p class="mb10 mr5">[{isys type="lang" ident="LC__WIDGET__WELCOME__INTRODUCTION" p_bHtmlEncode=false}]</p>
				<p class="mb10 mr5">[{isys type="lang" ident="LC__WIDGET__WELCOME__INTRODUCTION2" p_bHtmlEncode=false}]</p>
				<p>[{$date}]</p>
			</td>
			<td class="vat">
				<table class="two-col sorting-view" cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="vat">
							<div class="border mr5" style="height: 30px;">
								<div class="marker fr mr5 mt5">1</div>
							</div>
							<div class="border mt5 mr5" style="height: 65px;">
								<div class="marker fr mr5 mt5">3</div>
							</div>
						</td>
						<td class="vat">
							<div class="border" style="height: 65px;">
								<div class="marker fl ml5 mt5">2</div>
							</div>
							<div class="border mt5" style="height: 30px;">
								<div class="marker fl ml5 mt5">4</div>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript">
	// We need to do this, so the boxes won't animate after changing the configuration.
	clearInterval(window.sorting_interval_[{$unique_id}]);

	[{if $animate}]
	var sorting_view_[{$unique_id}] = function () {
		if ($('[{$unique_id}]')) {
			$('[{$unique_id}]').select('.sorting-view .border').each(function (el) {
				new Effect.Morph(el, {style:'height:' + parseInt(Math.random() * 50 + 30) + 'px;', duration: 2});
			});
		} else {
			clearInterval(window.sorting_interval_[{$unique_id}]);
		}
	};

	// We'll animate the sorting-boxes every 5 seconds... Looks nice.
	window.sorting_interval_[{$unique_id}] = setInterval(function () {sorting_view_[{$unique_id}]();}, 5000);
	[{/if}]
</script>