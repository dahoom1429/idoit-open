<div>
	<div class="p10">

		<table style="width:700px;">
			<tbody>
				<tr>
					<th class="" style="width:200px;height:35px">
					</th>
				    <td class="td_width text-shadow">
					    [{foreach from=$main_obj item=main_data key=obj_id}]
					    [{assign var=main_obj_id value=$obj_id}]
					    <strong class="ml5" style="position:relative;bottom:5px;">[{$main_data.link}]</strong><br />
					    <div class="ml5" id="vm_detail_[{$obj_id}]" >
						    <table class="matrixvalues">
						        <tr>
						            <td >RAM: [{$main_data.memory['value']}] [{$main_data.memory['unit']}]
						            </td>
						            <td>CPU: [{$main_data.cpu['value']}] [{$main_data.cpu['unit']}]
						            </td>
						            <td>DISK: [{$main_data.disc_space['value']}] [{$main_data.disc_space['unit']}]<img class="vam" src="images/icons/infobox/blue.png" title="[{isys type="lang" ident="LC__CMDB__CATG__OBJECT_VITALITY__DISK_INFO"}]">
						            </td>
						            <td>LAN: [{$main_data.bandwidth['value']}] [{$main_data.bandwidth['unit']}]<br />
						            </td>
						        </tr>
							</table>
						</div>
						[{/foreach}]
				    </td>
				</tr>
			</tbody>
		</table>
		<div id="matrix_scroller" style="overflow:auto;width:700px;max-height:205px;" >

			<table class="" style="width:700px;margin-top:0px" cellspacing="0" cellpadding="0">
			[{foreach from=$c_members item="s" key="obj_id"}]
			<tr>
			  <th class="text-shadow" style="width:200px;height:45px">
			    <strong style="position:relative;bottom:5px;">[{$s.link}]</strong><br />
				    <div id="service_detail_[{$service_key}]" class="">
					    ([{isys type="lang" ident=$s.type}])<br />
					</div>
			  </th>
			  <td  class="td_width">
			    [{foreach from=$main_obj item=main_data key=obj_id}]
			    [{assign var=main_obj_id value=$obj_id}]
			    <div id="vm_detail_[{$obj_id}]" class="ml5">
				    <table class="matrixvalues">
				        <tr>
				            <td>RAM: [{$s.memory['value']}] [{$s.memory['unit']}]<br />
				            </td>
					        <td>CPU: [{$s.cpu['value']}] [{$s.cpu['unit']}]<br />
					        </td>
							<td>DISK: [{$s.disc_space['value']}] [{$s.disc_space['unit']}]<br />
							</td>
							<td>LAN: [{$s.bandwidth['value']}] [{$s.bandwidth['unit']}]<br />
							</td>
						</tr>
					</table>
				</div>
				[{/foreach}]
			  </td>
			</tr>
			[{/foreach}]
			</table>
		</div>
		<div style="width:700px;border-bottom: #9b9b9b solid 2px;"></div>
		<table class="" style="width:700px;" cellspacing="0" cellpadding="0">
			<tr>
			  <th class="text-shadow" style="width:200px;height:35px;">
			        <strong>[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__CONSUMPTION"}]</strong>
			  </th>
			  <td  class="td_width">
				  <div class="ml5">
				    <table class="matrixvalues" >
				        <tr>
			                <td>RAM: [{$main_obj[$main_obj_id].memory_consumption['value']}] [{$main_obj[$main_obj_id].memory_consumption['unit']}]
				            </td>
							<td>CPU: [{$main_obj[$main_obj_id].cpu_consumption['value']}] [{$main_obj[$main_obj_id].cpu_consumption['unit']}]
							</td>
							<td>DISK: [{$main_obj[$main_obj_id].disc_space_consumption['value']}] [{$main_obj[$main_obj_id].disc_space_consumption['unit']}]
							</td>
							<td>LAN: [{$main_obj[$main_obj_id].bandwidth_consumption['value']}] [{$main_obj[$main_obj_id].bandwidth_consumption['unit']}]
							</td>
						</tr>
					</table>
				  </div>
			  </td>
			</tr>
			<tr>
				<th class="text-shadow" style="width:200px;height:35px;">
			        <strong>[{isys type="lang" ident="LC__CMDB__OBJECT_VITALITY__REMAINING_RESOURCES"}]</strong>
			    </th>
				<td class="td_width">
					<div class="ml5">
						<table class="matrixvalues" >
				            <tr >
				                <td>
								[{if $main_obj[$main_obj_id].memory['value'] > 0}]
				                    [{assign var=memory_calc value=$main_obj[$main_obj_id].memory['value']}]
								[{else}]
									[{assign var=memory_calc value=0}]
							    [{/if}]
				                <p style="color: [{if $main_obj[$main_obj_id].memory_rest['value'] <= 0 || $main_obj[$main_obj_id].memory_rest['negative']}]#E70000[{else}][{if $main_obj[$main_obj_id].memory_rest['value'] < $main_obj[$main_obj_id].memory['value']*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$memory_calc|round:2}]%">
								RAM: [{if $main_obj[$main_obj_id].memory_rest['negative']}]-[{/if}][{$main_obj[$main_obj_id].memory_rest['value']}] [{$main_obj[$main_obj_id].memory_rest['unit']}]
								</p>
					            </td>
								<td>
								[{if $main_obj[$main_obj_id].cpu['value'] > 0}]
									[{assign var=cpu_calc value=$main_obj[$main_obj_id].cpu_rest['value']*100/$main_obj[$main_obj_id].cpu['value']}]
								[{else}]
									[{assign var=cpu_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].cpu_rest['value'] <= 0 || $main_obj[$main_obj_id].cpu_rest['negative']}]#E70000[{else}][{if $main_obj[$main_obj_id].cpu_rest['value'] < $main_obj[$main_obj_id].cpu['value']*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$cpu_calc|round:2}]%">
								CPU: [{if $main_obj[$main_obj_id].cpu_rest['negative']}]-[{/if}] [{$main_obj[$main_obj_id].cpu_rest['value']}] [{$main_obj[$main_obj_id].cpu_rest['unit']}]<br />
								</p>
								</td>
								<td>
								[{if $main_obj[$main_obj_id].disc_space['value'] > 0}]
									[{assign var=disc_space_calc value=$main_obj[$main_obj_id].disc_space_rest['value']*100/$main_obj[$main_obj_id].disc_space['value']}]
								[{else}]
									[{assign var=disc_space_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].disc_space_rest['value'] <= 0 || $main_obj[$main_obj_id].disc_space_rest['negative']}]#E70000[{else}][{if $main_obj[$main_obj_id].disc_space_rest['value'] < $main_obj[$main_obj_id].disc_space['value']*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$disc_space_calc|round:2}]%">
								DISK: [{if $main_obj[$main_obj_id].disc_space_rest['negative']}]-[{/if}] [{$main_obj[$main_obj_id].disc_space_rest['value']}] [{$main_obj[$main_obj_id].disc_space_rest['unit']}]<br />
								</p>
								</td>
								<td>
								[{if $main_obj[$main_obj_id].bandwidth['value'] > 0}]
									[{assign var=bandwidth_calc value=$main_obj[$main_obj_id].bandwidth_rest['value']*100/$main_obj[$main_obj_id].bandwidth['value']}]
								[{else}]
									[{assign var=bandwidth_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].bandwidth_rest['value'] <= 0 || $main_obj[$main_obj_id].bandwidth_rest['negative']}]#E70000[{else}][{if $main_obj[$main_obj_id].bandwidth_rest['value'] < $main_obj[$main_obj_id].bandwidth['value']*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$bandwidth_calc|round:2}]%">
								LAN: [{if $main_obj[$main_obj_id].bandwidth_rest['negative']}]-[{/if}] [{$main_obj[$main_obj_id].bandwidth_rest['value']}] [{$main_obj[$main_obj_id].bandwidth['unit']}]<br />
								</p>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>

		<div class="cb"></div>
	</div>
</div>
