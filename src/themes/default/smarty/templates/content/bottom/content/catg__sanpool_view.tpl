<div class="p5">
	<ul id="tabs" class="mt0">
		<li>
			<a href="#tab1">[{isys type="lang" ident="LC__UNIVERSAL__DEVICE_CHAINS"}] </a>
		</li>
		<li>
			<a href="#tab2">[{isys type="lang" ident="LC__CMDB__CATG__LDEV_SERVER"}]</a>
		</li>
		<li>
			<a href="#tab3">[{isys type="lang" ident="LC__CMDB__CATG__LDEV_CLIENT"}]</a>
		</li>
		<li>
			<a href="#tab4">[{isys type="lang" ident="LC__STORAGE_FCPORT"}] </a>
		</li>
		<li>
			<a href="#tab5">[{isys type="lang" ident="LC__CMDB__CATG__HBA"}] </a>
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
					<th>[{isys type="lang" ident="LC__CMDB__CATG__LDEV_CLIENT"}]</th>
					<th>&nbsp;</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__HBA"}]</th>
				</tr>
			</thead>
			<tbody>

			[{if $san_chains_arr != ""}]
			[{foreach from=$san_chains_arr item=chains key=chain_key}]
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
					<th>[{isys type="lang" ident="LC__CATD__SANPOOL_LUN"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CAPACITY"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__LDEV_CLIENT"}]</th>
				</tr>
			</thead>
			<tbody>
			[{if $ldevserver_arr != ""}]
			[{foreach from=$ldevserver_arr item=sanitem}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$sanitem.title}]
					</td>
					<td>
						[{$sanitem.lun}]
					</td>
					<td>
						[{$sanitem.capacity}] [{$sanitem.capacity_unit}]
					</td>
					<td>
						[{$sanitem.controller}]
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
					<th>[{isys type="lang" ident="LC__CMDB__CATG__UI_ASSIGNED_UI"}]</th>
					<th>[{isys type="lang" ident="LC__CATD__SANPOOL_LUN"}]</th>
				</tr>
			</thead>
			<tbody>
			[{if $ldevclient_arr != ""}]
			[{foreach from=$ldevclient_arr item=clientitem}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$clientitem.title}]
					</td>
					<td>
						[{$clientitem.assigned}]
					</td>
					<td>
						[{$clientitem.lun}]
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
					<th>[{isys type="lang" ident="LC_UNIVERSAL__PORT_TITLE"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__HBA"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_TYPE"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_FCPORT__MEDIUM"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK__TARGET_OBJECT"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]</th>
				</tr>
			</thead>
			<tbody>

			[{if $fc_port_arr != ""}]
			[{foreach from=$fc_port_arr item=portitem}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$portitem.title}]
					</td>
					<td>
						[{$portitem.hba}]
					</td>
					<td>
						[{$portitem.type}]
					</td>
					<td>
						[{$portitem.medium}]
					</td>
					<td>
						[{$portitem.target}]
					</td>
					<td>
						[{$portitem.connection_type}]
					</td>
				</tr>
			[{/foreach}]
			[{/if}]

			</tbody>
		</table>
	</div>

	<div id="tab5" class="p10">
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

			[{if $hba_arr != ""}]
			[{foreach from=$hba_arr item=hba}]

				<tr class="[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]">
					<td>
						[{$hba.title}]
					</td>
					<td>
						[{$hba.type}]
					</td>
					<td>
						[{$hba.manufacturer}]
					</td>
					<td>
						[{$hba.model}]
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