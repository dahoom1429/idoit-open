<div class="p10">
	<div style="overflow-x: auto">

	[{if (count($cluster_service_list) > 0 || count($virtual_machine_list) > 0) && count($c_members) > 0}]
		[{if $clusterServiceDisabled}]
		<div class="fl box-red p5"><img class="mr5" src="[{$dir_images}]icons/alert-icon.png">[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_VITALITY__DISABLED_CLUSTERSERVICES"}]</div>
		[{/if}]
		<div class="fr">
			<button type="button" class="btn" onclick="idoit.callbackManager.triggerCallback('cluster_vitality__member_click', '', $F('services_ids_arr'), $F('vms_ids_arr'))">
				<span>[{isys type="lang" ident="LC__UNIVERSAL__SHOW"}]/[{isys type="lang" ident="LC__UNIVERSAL__HIDE"}]</span>
			</button>
			<input type="hidden" id="all_hidden_visible" value="hidden">
		</div>
		<div class="cb"></div>

		<table class="sanmatrix fl mt10" cellspacing="0" cellpadding="0">
			<tr>
			  <th class="maxheight" style="background:#fff url('[{$dir_images}]matrix_diagonal_line_vitalitaet.png') no-repeat;">
			    <span class="fr" style="height:41px;">
			        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/server.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_MEMBERS"}]" />
			    </span>

			    <span class="fl" style="margin-top:17px;">
			        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/san.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_SERVICES"}]" />
			    </span>
			  </th>
			</tr>
			[{foreach from=$cluster_service_list item="s" key="service_key"}]
			[{assign var=services_ids_as_string value=$services_ids_as_string|cat:$service_key|cat:','}]
			<tr id="service_id_[{$service_key}]">
				<td id="service_td_id_[{$service_key}]" class="gradient text-shadow head minheight">
					<a href="javascript:" onclick="idoit.callbackManager.triggerCallback('cluster_vitality__service_click', [{$service_key}], '');">
						<img id="services_plus_minus_[{$service_key}]" src="[{$dir_images}]dtree/nolines_plus.gif" class="plus">
					</a>

					<strong style="position:relative;bottom:5px;">[{$s.object_link}]</strong><br />

					<div id="service_detail_[{$service_key}]" class="hideit">
						([{isys type="lang" ident=$s.cluster_type}])...<br />
						RAM: [{$s.memory}] [{$s.memory_unit}]<br />
						CPU: [{$s.cpu}] [{$s.cpu_unit}]<br />
						DISK: [{$s.disc_space}] [{$s.disc_space_unit}]<br />
						LAN: [{$s.bandwidth}] [{$s.bandwidth_unit}]<br />
					</div>
				</td>
			</tr>
			[{/foreach}]

			<tr>
				<td height="1%" style="border-right:0px;">
					&nbsp;
				</td>
			</tr>
			[{if $virtual_machine_list|count > 0}]
			[{foreach from=$virtual_machine_list item="vm" key="vm_key"}]
			[{assign var=vm_ids_as_string value=$vm_ids_as_string|cat:$vm_key|cat:','}]
			<tr id="vm_id_[{$vm_key}]">
				<td id="vms_td_id_[{$vm_key}]" class="gradient text-shadow head minheight">
					<a href="javascript:" onclick="idoit.callbackManager.triggerCallback('cluster_vitality__vm_click', [{$vm_key}], '');">
						<img id="vms_plus_minus_[{$vm_key}]" src="[{$dir_images}]dtree/nolines_plus.gif" class="plus">
					</a>

					<strong style="position:relative;bottom:5px;">[{$vm.object_link}]</strong><br />

					<div id="vm_detail_[{$vm_key}]" class="hideit">
						([{isys type="lang" ident=$vm.vm_title}])<br />
						RAM: [{$vm.memory}] [{$vm.memory_unit}]<br />
						CPU: [{$vm.cpu}] [{$vm.cpu_unit}]<br />
						DISK: [{$vm.disc_space}] [{$vm.disc_space_unit}]<br />
						LAN: [{$vm.bandwidth}] [{$vm.bandwidth_unit}]<br />
					</div>
				</td>
			</tr>
			[{/foreach}]

			<tr>
				<td height="1%" style="border-right:0px;">
					&nbsp;
				</td>
			</tr>
			[{/if}]
			<tr>
				<td class="gradient text-shadow head maxheight">
					<strong>[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__CONSUMPTION"}]</strong>
				</td>
			</tr>
			<tr>
				<td class="gradient text-shadow head maxheight">
					<strong>[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__REMAINING_RESSOURCES"}]</strong>
				</td>
			</tr>
		</table>
		<input type="hidden" id="services_ids_arr" value="[{$services_ids_as_string}]">
		<input type="hidden" id="vms_ids_arr" value="[{$vm_ids_as_string}]">

		<div style="overflow-x:auto;" id="matrix_scroller">

			<table class="sanmatrix mt10" cellspacing="0" cellpadding="0">

			<tr>
			[{assign var=counter value=0}]
			[{foreach from=$c_members item="cm" key="member_obj_id"}]
				<th class="gradient text-shadow maxheight" align="left">
					[{if isset($coords[$member_obj_id])}]
					<a href="javascript:" onclick="idoit.callbackManager.triggerCallback('cluster_vitality__member_click', '[{$member_obj_id}]','[{$cm.services}]', '[{$cm.vms}]');">
				  	<img id="member_plus_minus_[{$member_obj_id}]" src="[{$dir_images}]dtree/nolines_plus.gif" class="plus">
				  	</a>
				  	[{/if}]
		  			<strong>[{$cm.link}]</strong><br />
					RAM: [{$cm.memory}] [{$cm.memory_unit}]<br />
					CPU: [{$cm.cpu}] [{$cm.cpu_unit}]<br />
					DISK: [{$cm.disc_space}] [{$cm.disc_space_unit}]<br />
					LAN: [{$cm.bandwidth}] [{$cm.bandwidth_unit}]<br />
					[{assign var=counter value=$counter+1}]
				</th>
			[{/foreach}]

			</tr>
			[{assign var=cluster_type_active value="LC__CLUSTER_TYPE__ACTIVE"}]
			[{assign var=cluster_type_passive value="LC__CLUSTER_TYPE__PASSIVE"}]
			[{assign var=cluster_type_hpc value="LC__CLUSTER_TYPE__HPC"}]
			[{assign var=service_status_disabled value="LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS__DISABLED"}]

			[{foreach from=$cluster_service_list item="s" key="cluster_service_id"}]

				<tr id="service_row_id_[{$cluster_service_id}]" class="hideit">
				[{assign var=s_id value='service_'|cat:$cluster_service_id}]

				[{foreach from=$c_members item="cm"}]
					[{assign var=obj_id value=$cm.isys_obj__id}]

					<td style="vertical-align: top;" class="[{if ($coords_two[$s_id][$obj_id].service_status == $service_status_disabled) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_inactive [{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_active) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_aktiv[{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_passive) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_passiv[{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_hpc) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_hpc[{/if}] maxheight">
						[{if isset($coords_two[$s_id][$obj_id])}]
							<p style="text-align:center; margin-top:3px;"><strong>[{isys type="lang" ident=$coords_two[$s_id][$obj_id].cluster_type}]</strong></p>
							<br />
							RAM: [{$coords_two[$s_id][$obj_id].memory}] [{$coords_two[$s_id][$obj_id].memory_unit}]<br />
							CPU: [{$coords_two[$s_id][$obj_id].cpu}] [{$coords_two[$s_id][$obj_id].cpu_unit}]<br />
							DISK: [{$coords_two[$s_id][$obj_id].disc_space}] [{$coords_two[$s_id][$obj_id].disc_space_unit}]<br />
							LAN: [{$coords_two[$s_id][$obj_id].bandwidth}] [{$coords_two[$s_id][$obj_id].bandwidth_unit}]<br />
						[{/if}]
					</td>
				[{/foreach}]
				</tr>

				<tr id="service_row_no_display_id_[{$cluster_service_id}]" class="showit">
				[{assign var=s_id value='service_'|cat:$cluster_service_id}]

				[{foreach from=$c_members item="cm"}]
					[{assign var=obj_id value=$cm.isys_obj__id}]

					<td style="vertical-align: top;" class="[{if ($coords_two[$s_id][$obj_id].service_status == $service_status_disabled) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_inactive [{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_active) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_aktiv[{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_passive) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_passiv[{elseif ($coords_two[$s_id][$obj_id].cluster_type == $cluster_type_hpc) && isset($coords_two[$s_id][$obj_id])}]clusterdienst_hpc[{/if}] minheight">
						<p style="text-align:center; margin-top:3px;"><strong>[{isys type="lang" ident=$coords_two[$s_id][$obj_id].cluster_type}]</strong></p>
					</td>

				[{/foreach}]
				</tr>
			[{/foreach}]
			<tr>
				<td colspan="[{$counter}]" style="border-left:0;">&nbsp;
				</td>
			</tr>
			[{if $virtual_machine_list|@count > 0}]
			[{foreach from=$virtual_machine_list item="vm" key="vm_id"}]

				<tr id="vm_row_id_[{$vm_id}]" class="hideit">

				[{assign var=v_id value='virtual_'|cat:$vm_id}]

				[{foreach from=$c_members item="cm"}]
					[{assign var=obj_id value=$cm.isys_obj__id}]

					<td style="vertical-align: top;" class="maxheight [{if isset($coords_two[$v_id][$obj_id])}]clusterdienst_virtualmachine minheight[{/if}]">

						[{if isset($coords_two[$v_id][$obj_id])}]
							<p style="text-align:center; margin-top:3px;"><strong>[{$vm.vm_title}]</strong></p>
							<br />
							RAM: [{$coords_two[$v_id][$obj_id].memory}] [{$coords_two[$v_id][$obj_id].memory_unit}]<br />
							CPU: [{$coords_two[$v_id][$obj_id].cpu}] [{$coords_two[$v_id][$obj_id].cpu_unit}] <br />
							DISK: [{$coords_two[$v_id][$obj_id].disc_space}] [{$coords_two[$v_id][$obj_id].disc_space_unit}]<br />
							LAN: [{$coords_two[$v_id][$obj_id].bandwidth}] [{$coords_two[$v_id][$obj_id].bandwidth_unit}] <br />
						[{/if}]



					</td>
				[{/foreach}]
				</tr>

				<tr id="vm_row_no_display_id_[{$vm_id}]" class="minheight showit">
					[{foreach from=$c_members item="cm"}]
						[{assign var=obj_id value=$cm.isys_obj__id}]
						<td style="vertical-align: top;" class="[{if isset($coords_two[$v_id][$obj_id])}]clusterdienst_virtualmachine minheight[{/if}]">
							[{if isset($coords_two[$v_id][$obj_id])}]
							<p style="text-align:center; margin-top:3px;"><strong>[{$vm.vm_title}]</strong></p>
							[{/if}]
						</td>
					[{/foreach}]
				</tr>

			[{/foreach}]

			<tr>
				<td colspan="[{$counter}]" style="border-left:0;">&nbsp;
				</td>
			</tr>
			[{/if}]
			<tr>
				[{foreach from=$c_members item="member"}]
				[{assign var=obj_id value=$member.isys_obj__id}]
				[{if isset($consumption[$obj_id])}]
				<td class="maxheight">
					RAM: [{$consumption[$obj_id].memory}] [{isys type="lang" ident=$consumption[$obj_id].memory_unit}]<br />
					CPU: [{$consumption[$obj_id].cpu}] [{isys type="lang" ident=$consumption[$obj_id].cpu_unit}]<br />
					DISK: [{$consumption[$obj_id].disc_space}] [{isys type="lang" ident=$consumption[$obj_id].disc_space_unit}]<br />
					LAN: [{$consumption[$obj_id].bandwidth}] [{isys type="lang" ident=$consumption[$obj_id].bandwidth_unit}]<br />
				</td>
				[{else}]
				<td class="maxheight">
				</td>
				[{/if}]
				[{/foreach}]
			</tr>
			<tr>
				[{foreach from=$c_members item="members"}]
				[{assign var=obj_id value=$member.isys_obj__id}]
				<td class="maxheight">
					[{if $members.memory > 0}]
						[{assign var=memory_calc value=$members.memory_rest*100/$members.memory}]
					[{else}]
						[{assign var=memory_calc value=0}]
					[{/if}]
					<p class="[{if $members.memory_rest <= 0}]p_red[{else}][{if $members.memory_rest < $members.memory*0.2}]p_yellow[{else}]p_green[{/if}][{/if}]" title="[{$memory_calc|round:2}]%">
					RAM: [{$members.memory_rest}] [{$members.memory_unit}]<br />
					</p>
					[{if $members.cpu > 0}]
						[{assign var=cpu_calc value=$members.cpu_rest*100/$members.cpu}]
					[{else}]
						[{assign var=cpu_calc value=0}]
					[{/if}]
					<p class="[{if $members.cpu_rest <= 0}]p_red[{else}][{if $members.cpu_rest < $members.cpu*0.2}]p_yellow[{else}]p_green[{/if}][{/if}]" title="[{$cpu_calc|round:2}]%">
					CPU: [{$members.cpu_rest}] [{$members.cpu_unit}]<br />
					</p>
					[{if $members.disc_space > 0}]
						[{assign var=disc_space_calc value=$members.disc_space_rest*100/$members.disc_space}]
					[{else}]
						[{assign var=disc_space_calc value=0}]
					[{/if}]
					<p class="[{if $members.disc_space_rest <= 0}]p_red[{else}][{if $members.disc_space_rest < $members.disc_space*0.2}]p_yellow[{else}]p_green[{/if}][{/if}]" title="[{$disc_space_calc|round:2}]%">
					DISK: [{$members.disc_space_rest}] [{$members.disc_space_unit}]<br />
					</p>
					[{if $members.bandwith > 0}]
						[{assign var=bandwidth_calc value=$members.bandwidth_rest*100/$members.bandwidth}]
					[{else}]
						[{assign var=bandwidth_calc value=0}]
					[{/if}]
					<p class="[{if $members.bandwidth_rest <= 0}]p_red[{else}][{if $members.bandwidth_rest < $members.bandwidth*0.2}]p_yellow[{else}]p_green[{/if}][{/if}]" title="[{$bandwidth_calc|round:2}]%">
					LAN: [{$members.bandwidth_rest}] [{$members.bandwidth_unit}]<br />
					</p>
				</td>

				[{/foreach}]
			</tr>
			</table>

		</div>

		<div class="cb"></div>
	[{else}]
	[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__NO_MEMBERS_OR_SERVICES"}]
	[{/if}]
	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

		var service_click = function (p_id, p_action) {
			var $servic_detail = $('service_detail_' + p_id);

			if (!p_action.blank()) {
				if (p_action == 'hide') {
					service_sub_click_minus(p_id);
				} else if (p_action == 'show') {
					service_sub_click_plus(p_id);
				}
			} else {
				if ($servic_detail.hasClassName('showit')) {
					service_sub_click_minus(p_id);
				} else if ($servic_detail.hasClassName('hideit')) {
					service_sub_click_plus(p_id);
				}
			}

			return null;
		};

		var service_sub_click_plus = function(p_id){
			$('service_detail_'+p_id).removeClassName('hideit').addClassName('showit');
			$('service_row_id_'+p_id).removeClassName('hideit').addClassName('showit');
			$('service_td_id_'+p_id).removeClassName('minheight').addClassName('maxheight');
			$('service_row_no_display_id_'+p_id).removeClassName('showit').addClassName('hideit');
			$('services_plus_minus_'+p_id).writeAttribute('src', '[{$dir_images}]dtree/nolines_minus.gif');

			same_tr_height(p_id);
		};

		var service_sub_click_minus = function(p_id){
			$('service_detail_'+p_id).removeClassName('showit').addClassName('hideit');
			$('service_row_id_'+p_id).removeClassName('showit').addClassName('hideit');
			$('service_td_id_'+p_id).removeClassName('maxheight').addClassName('minheight');
			$('service_row_no_display_id_'+p_id).removeClassName('hideit').addClassName('showit');
			$('services_plus_minus_'+p_id).writeAttribute('src', '[{$dir_images}]dtree/nolines_plus.gif');

			same_tr_height(p_id);
		};

		var vm_sub_click_plus = function (p_id) {
			$('vm_detail_' + p_id).removeClassName('hideit').addClassName('showit');
			$('vm_row_id_' + p_id).removeClassName('hideit').addClassName('showit');
			$('vms_td_id_' + p_id).removeClassName('minheight').addClassName('maxheight');
			$('vm_row_no_display_id_' + p_id).removeClassName('showit').addClassName('hideit');
			$('vms_plus_minus_' + p_id).writeAttribute('src', '[{$dir_images}]dtree/nolines_minus.gif');
		};

		var vm_sub_click_minus = function(p_id){
			$('vm_detail_'+p_id).removeClassName('showit').addClassName('hideit');
			$('vm_row_id_'+p_id).removeClassName('showit').addClassName('hideit');
			$('vms_td_id_'+p_id).removeClassName('maxheight').addClassName('minheight');
			$('vm_row_no_display_id_'+p_id).removeClassName('hideit').addClassName('showit');
			$('vms_plus_minus_'+p_id).writeAttribute('src','[{$dir_images}]dtree/nolines_plus.gif');
		};

		var same_tr_height = function(p_id) {
			var $service_row = $('service_row_id_'+p_id),
				$service_id = $('service_id_'+p_id);

			if ($service_row.hasClassName("showit")) {
				$service_id.setStyle({height: $service_row.getHeight() + "px"});
			} else {
				$service_id.writeAttribute("style", "");
			}
		};

		var vm_click = function (p_id, p_action) {
			var $vm_detail = $('vm_detail_' + p_id);

			if (!p_action.blank()) {
				if (p_action == 'hide') {
					vm_sub_click_minus(p_id);
				} else if (p_action == 'show') {
					vm_sub_click_plus(p_id);
				}
			} else {
				if ($vm_detail.hasClassName('showit')) {
					vm_sub_click_minus(p_id);
				} else if ($vm_detail.hasClassName('hideit')) {
					vm_sub_click_plus(p_id);
				}
			}

			return null;
		};

		var member_click = function (p_member, p_services, p_vms) {
			var click_action = "",
				$toggle_all_button = $('all_hidden_visible'),
				$member_plus_minus = $('member_plus_minus_' + p_member),
				arr,
				lenght,
				i;

			if (p_member.blank()) {
				if ($toggle_all_button.getValue() == "hidden") {
					click_action = "show";
					$toggle_all_button.setValue("visible");

					$$(".sanmatrix a img").invoke('writeAttribute', 'src', '[{$dir_images}]dtree/nolines_minus.gif');
					$$(".sanmatrix .plus").invoke('removeClassName', 'plus').invoke('addClassName', 'minus');
				} else if ($toggle_all_button.getValue() == "visible") {
					click_action = "hide";
					$toggle_all_button.setValue("hidden");

					$$(".sanmatrix a img").invoke('writeAttribute', 'src', '[{$dir_images}]dtree/nolines_plus.gif');
					$$(".sanmatrix .minus").invoke('removeClassName', 'minus').invoke('addClassName', 'plus');
				}
			} else {
				if ($member_plus_minus.hasClassName('plus')) {
					$member_plus_minus.removeClassName('plus').addClassName('minus').writeAttribute('src', '[{$dir_images}]dtree/nolines_minus.gif');
					click_action = "show";
				} else if ($member_plus_minus.hasClassName('minus')) {
					$member_plus_minus.removeClassName('minus').addClassName('plus').writeAttribute('src', '[{$dir_images}]dtree/nolines_plus.gif');
					click_action = "hide";
				}
			}

			if (! p_services.blank()) {
				arr = p_services.split(",");

				for (i = 0, lenght = arr.length; i < lenght; i++) {
					if (! arr[i].blank()) {
						service_click(arr[i], click_action);
					}
				}
			}

			if (! p_vms.blank()) {
				arr = p_vms.split(",");

				for (i = 0, lenght = arr.length; i < lenght; i++) {
					if (! arr[i].blank()) {
						vm_click(arr[i], click_action);
					}
				}
			}

			return null;
		};

		idoit.callbackManager
			.registerCallback('cluster_vitality__vm_click', vm_click)
			.registerCallback('cluster_vitality__member_click', member_click)
			.registerCallback('cluster_vitality__service_click', service_click);
	}());
</script>