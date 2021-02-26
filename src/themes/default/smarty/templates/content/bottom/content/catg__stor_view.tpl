<div class="p10">
	<ul id="tabs">
		<li>
			<a href="#tab1">[{isys type="lang" ident="LC__UNIVERSAL__DEVICE_CHAINS"}] </a>
		</li>
		<li>
			<a href="#tab2">[{isys type="lang" ident="LC__STORAGE_DEVICE"}]</a>
		</li>
		<li>
			<a href="#tab3">[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER"}]</a>
		</li>
		<li>
			<a href="#tab4">[{isys type="lang" ident="LC__CMDB__CATG__RAID"}] </a>
		</li>

	</ul>

	<div id="tab1" class="p10">
		<table class="listing" cellpadding="0" cellspacing="0">
			<colgroup>
					<col width="20%" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__DRIVES"}]</th>
					<th>&nbsp;</th>
					<th>[{isys type="lang" ident="LC__UNIVERSAL__DEVICES"}]</th>
					<th>&nbsp;</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER"}]</th>
				</tr>
			</thead>
			<tbody>

			[{if $das_chains_arr != ""}]
			[{foreach from=$das_chains_arr item=chains key=chain_key}]
				[{if $chain_key == "drives"}]
				[{foreach from=$chains item=chain}]
				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$chain.0}]
					</td>
					<td>
						[{if $chain.1 != ""}]
						>>
						[{/if}]
					</td>
					<td>
						[{$chain.1}]
					</td>
					<td>
						[{if $chain.2 != ""}]
						>>
						[{/if}]
					</td>
					<td>
						[{$chain.2}]
					</td>

				</tr>
				[{/foreach}]
				[{/if}]
				[{if $chain_key == "devices"}]
				[{foreach from=$chains item=chain}]
				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
					<td>
						[{$chain.0}]
					</td>
					<td>
						[{if $chain.1 != ""}]
						>>
						[{/if}]
					</td>
					<td>
						[{$chain.1}]
					</td>

				</tr>
				[{/foreach}]
				[{/if}]
			[{/foreach}]
			[{/if}]

			</tbody>
		</table>
	</div>

	<div id="tab2" class="p10">
		<table class="listing" cellpadding="0" cellspacing="0">
			<colgroup>
					<col width="20%" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CATG__RAID_TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CAPACITY"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER"}]</th>
				</tr>
			</thead>
			<tbody>
			[{if $devices_arr != ""}]
			[{foreach from=$devices_arr item=device}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$device.title}]
					</td>
					<td>
						[{isys type="lang" ident=$device.typ}]
					</td>
					<td>
						[{$device.capacity}] [{$device.capacity_unit}]
					</td>
					<td>
						[{$device.controller}]
					</td>
				</tr>
			[{/foreach}]
			[{/if}]
			</tbody>
		</table>
	</div>


	<div id="tab3" class="p10">
		<table class="listing" cellpadding="0" cellspacing="0">
			<colgroup>
					<col width="20%" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER_TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER_MANUFACTURER"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONTROLLER_MODEL"}]</th>
				</tr>
			</thead>
			<tbody>
			[{if $controllers_arr != ""}]
			[{foreach from=$controllers_arr item=controller}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$controller.title}]
					</td>
					<td>
						[{$controller.typ}]
					</td>
					<td>
						[{$controller.manufacturer}]
					</td>
					<td>
						[{$controller.modell}]
					</td>
				</tr>
			[{/foreach}]
			[{/if}]
			</tbody>
		</table>
	</div>

	<div id="tab4" class="p10">
		<table class="listing" cellpadding="0" cellspacing="0">
			<colgroup>
					<col width="20%" />
			</colgroup>
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__CATG__RAID_TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__CATD__DRIVE_RAIDLEVEL"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__RAID_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CAPACITY"}]</th>
				</tr>
			</thead>
			<tbody>

			[{if $raids_arr != ""}]
			[{foreach from=$raids_arr item=raid}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$raid.title}]
					</td>
					<td>
						[{$raid.raid_level}]
					</td>
					<td>
						[{isys type="lang" ident=$raid.typ}]
					</td>
					<td>
						[{$raid.capacity}] [{isys type="lang" ident=$raid.capacity_unit}]
					</td>
				</tr>
			[{/foreach}]
			[{/if}]

			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		new Tabs('tabs');
	}());
</script>