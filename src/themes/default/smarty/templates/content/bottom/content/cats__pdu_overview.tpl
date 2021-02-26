<script type="text/javascript">
	"use strict";

	window.pwr_calc = function() {
		$('sel_pwr').update(0); $('sel_nrg').update(0);

		$$('input.pwrcheck:checked').each(function(e){
			if ($('pwr_' + e.value) && $('pwr_' + e.value).innerHTML != '')
				$('sel_pwr').update((parseFloat($('sel_pwr').innerHTML) + parseFloat($('pwr_' + e.value).innerHTML)));

			if ($('nrg_' + e.value) && $('nrg_' + e.value).innerHTML != '')
				$('sel_nrg').update((parseFloat($('sel_nrg').innerHTML) + parseFloat($('nrg_' + e.value).innerHTML)));

			$('sel_pwr').highlight();
			$('sel_nrg').highlight();
		});
	};
</script>

<fieldset class="overview border-top-none">
	<legend><span>PDU [{isys type="lang" ident="LC__CMDB__CATS__LICENCE_OVERVIEW"}]</span></legend>

	[{if is_array($branches) && count($branches)}]

		<div class="p10 fl" style="border-right:1px solid #ccc;margin-right:10px;">
			<ul style="line-height:15px;">
			[{foreach from=$branches item="b"}]

				<li class="gradient p5 text-shadow">[{if !$b.title}]Branch[{/if}] [{counter}] [{if $b.title}](<strong>[{$b.title}]</strong>)[{/if}][{if $b.pwr}], [{$b.pwr}] W[{/if}][{if $b.nrg}], [{$b.nrg}] kWh[{/if}] (ID: [{$b.row.isys_cats_pdu_branch_list__branch_id}])</li>
				<li>
					<ul>
					[{foreach from=$b.receptables item="r" key="id"}]
					<li>
						<label>
							<input type="checkbox" class="pwrcheck" onclick="pwr_calc();" value="[{$id}]" /> [{$r.title|default:"Receptable $id"}]
							[{if $r.pwr}], <span id="pwr_[{$id}]">[{$r.pwr}]</span> W[{/if}][{if $r.nrg}], <span id="nrg_[{$id}]">[{$r.nrg}]</span> kWh[{/if}]
						</label>
					</li>
					[{/foreach}]
					</ul>
				</li>

			[{/foreach}]
			</ul>

		</div>

		<div class="p10">
			<p class="m10">
				<h3>[{isys type="lang" ident="LC__CMDB__CATS__PDU__CALCULATED_CONSUMPTION"}]:</h3>
				<hr /><br />
				<table class="listing" style="width:200px;display:inline;">
					<tr class="gradient text-shadow">
						<th>[{isys type="lang" ident="LC__CMDB__CATS__PDU__CURRENT_POWER_OUT"}]</th>
						<th>[{isys type="lang" ident="LC__CMDB__CATS__PDU__ACCUMULATED_ENERGY"}]</th>
					</tr>
					<tr>
						<td><strong id="sel_pwr">0</strong> Watt</td>
						<td><strong id="sel_nrg">0</strong> kWh</td>
					</tr>
				</table>
			</p>
		</div>

		<div class="cb"></div>

	[{else}]

		<div class="m10 box-grey p5">Es wurden bisher Keine PDU Branches definiert.</div>

	[{/if}]
</fieldset>