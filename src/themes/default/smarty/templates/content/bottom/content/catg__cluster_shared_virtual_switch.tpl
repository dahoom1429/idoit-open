<div class="p10">
	<div style="overflow-x: auto">

		<div class="fr">
			[{isys type="lang" ident="LC__CATG__CLUSTER_SHARED_VIRTUAL_SWITCH__UNUSED"}]

			<button type="button" class="btn ml5 mr5" onclick="idoit.callbackManager.triggerCallback('cluster_shared_virtual_switch__show_unused');">
				<img src="[{$dir_images}]icons/silk/lightbulb.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__UNIVERSAL__SHOW"}]</span>
			</button>

			<button type="button" class="btn" onclick="idoit.callbackManager.triggerCallback('cluster_shared_virtual_switch__hide_unused');">
				<img src="[{$dir_images}]icons/silk/lightbulb_off.png" class="mr5" />
				<span>[{isys type="lang" ident="LC__UNIVERSAL__HIDE"}]</span>
			</button>
		</div>
		<div class="cb"></div>

		<table class="sanmatrix fl mt10" cellspacing="0" cellpadding="0">
			<tr>
			  <th class="minheight" style="background:#fff url('[{$dir_images}]matrix_diagonal_line_virtuelle_switche.png') no-repeat;">
			    <span class="fr">
			        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/server.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_MEMBERS"}]" />
			    </span>

			    <span class="fl" style="margin-top:17px;">
			        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/san.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_SHARED_STORAGE"}] (LDEV Server)" />
			    </span>
			  </th>
			</tr>
		[{foreach from=$vswitchlist item="s" key="vswitch_id"}]
			<tr id="san_desc_[{$vswitch_id}]" >
			  <td class="gradient text-shadow head">
			    <div class="portgroup_head"><strong title="[{$s.title}]">[{if strlen($s.title) > 20}][{$s.title|substr:0:20}]...[{else}]<strong>[{$s.title}][{/if}]</strong></div>
			        <table class="portgroup">
				    [{foreach from=$s item="p" key="p_key"}]
				        [{if is_numeric($p_key)}]
						[{foreach from=$p.port_group item="group"}]
				        <tr>
							<td>
				                [{$group}]
				            </td>
				        </tr>
				        [{/foreach}]
				        [{/if}]
				    [{/foreach}]
			        </table>
			  </td>
			</tr>
		[{/foreach}]
		</table>

		<div style="overflow-x: auto;position:relative;" id="matrix_scroller" class="mt10">
			<table class="sanmatrix" cellspacing="0" cellpadding="0" >

				<tr>
				[{assign var=counter value=0}]
				[{foreach from=$c_members item="cm"}]
					<th class="gradient text-shadow minheight" id="member_[{$cm.isys_obj__id}]">
						[{$cm.link}]
					</th>
					[{assign var=counter value=$counter+1}]
				[{/foreach}]
				</tr>

				[{foreach from=$vswitchlist item="s" key="san_id"}]

				[{assign var=counter value=0}]
				<tr id="san_row_[{$san_id}]">
				[{foreach from=$c_members item="cm"}]

					[{assign var=obj_id value=$cm.isys_obj__id}]
					<td id="content_[{$obj_id}]_[{$san_id}]" class="center [{if isset($coords[$obj_id][$san_id])}]gradient" style="background-color:rgba(0,0,0,.075)" title="Virtueller Switch: [{$coords[$obj_id][$san_id].isys_catg_virtual_switch_list__title}]"[{/if}]">
						<div class="portgroup_head gradient">

						</div>
						<table class="portgroup">
					  	[{foreach from=$s item="p" key="port_id"}]

					  		[{if is_numeric($port_id)}]
					  		[{foreach from=$p.port_group item="g"}]
					  		[{assign var=g_id value=$g|replace:' ':''}]

							<tr>
								<td>
					  				[{isys type="checkbox" p_strOnClick="\$('port_group_hidden[$san_id][$obj_id][$g_id]').value = (this.checked)?'1':'0';" p_bInfoIconSpacer=0 p_bChecked=$coords[$cm.isys_obj__id][$san_id].port_group[$g_id] name="port_group[$san_id][$obj_id][$port_id]"}]

				  						<!--<input type="hidden" id="port_group_hidden[[{$san_id}]][[{$obj_id}]][[{$g_id}]]" name="port_group_hidden[[{$san_id}]][[{$obj_id}]][[{$g_id}]]" value="[{if is_array($coords[$cm.isys_obj__id][$san_id].port_group[$g_id])}]1[{else}]0[{/if}]" />-->
				  						<input type="hidden" id="port_group_hidden[[{$san_id}]][[{$obj_id}]][[{$g_id}]]" name="port_group_hidden[[{$san_id}]][[{$obj_id}]][[{$g_id}]]" value="[{if is_array($coords[$obj_id][$san_id].port_group[$g_id])}]1[{else}]0[{/if}]" />

					  			</td>
					  		</tr>
					  		[{/foreach}]
					  		[{/if}]
					  	[{/foreach}]
				  		</table>
					</td>

					[{assign var=counter value=$counter+1}]
				[{/foreach}]
				</tr>

				[{/foreach}]

			</table>
		</div>

		<div class="cb"></div>

	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";


		idoit.callbackManager
			.registerCallback('cluster_shared_virtual_switch__show_unused', function() {
				[{foreach from=$c_members item="cm"}]
					[{assign var=obj_id value=$cm.isys_obj__id}]
					$('member_[{$obj_id}]').show();
					[{foreach from=$vswitchlist item="s" key="san_id"}]
					$('content_[{$obj_id}]_[{$san_id}]').show();
					[{/foreach}]
				[{/foreach}]
			})
			.registerCallback('cluster_shared_virtual_switch__hide_unused', function(){
				[{foreach $c_members as $cm}]
					[{assign var=obj_id value=$cm.isys_obj__id}]
					[{assign var=counter value=0}]
					[{assign var=maxi value=0}]

					[{foreach $vswitchlist as $san_id => $s}]
					[{foreach $s as $port_id => $p}]
					[{if is_numeric($port_id)}]
					[{foreach $p.port_group as $g}]
					[{assign var=g_id value=$g|replace:' ':''}]
					[{if !is_array($coords[$obj_id][$san_id].port_group[$g_id])}]
					[{assign var=counter value=$counter+1}]
					[{/if}]
					[{assign var=maxi value=$maxi+1}]
					[{/foreach}]
					[{/if}]
					[{/foreach}]
					[{/foreach}]

					[{if $counter == $maxi}]
					$('member_[{$obj_id}]').hide();
					[{foreach $vswitchlist as $san_id => $s}]
					$('content_[{$obj_id}]_[{$san_id}]').hide();
					[{/foreach}]
					[{/if}]
				[{/foreach}]
			});
	}());
</script>