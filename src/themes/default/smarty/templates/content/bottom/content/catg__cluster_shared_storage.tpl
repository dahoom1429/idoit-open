<div class="p10">
	<div style="overflow-x: auto">
		<div class="fr">
			[{isys type="lang" ident="LC__CATG__CLUSTER_SHARED_STORAGE__UNUSED"}]
			<button type="button" class="btn ml5 mr5" onclick="$$('.sanmatrix .hidden').invoke('show');"><img src="[{$dir_images}]icons/silk/lightbulb.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__SHOW"}]</span></button>
			<button type="button" class="btn" onclick="$$('.sanmatrix .hidden').invoke('hide');"><img src="[{$dir_images}]icons/silk/lightbulb_off.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__HIDE"}]</span></button>
		</div>
		<div class="cb"></div>

	[{if is_array($sanlist) && count($sanlist) > 0 && is_array($c_members) && count($c_members) > 0}]
		<table class="sanmatrix fl mt10" cellspacing="0" cellpadding="0">
			<tr>
				<th class="minheight" style="background:#fff url('[{$dir_images}]matrix_diagonal_line.png') no-repeat;">
		  	        <span class="fr">
		  		        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/server.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_MEMBERS"}]" />
		  	        </span>

				    <span class="fl" style="margin-top:17px;">
				        <img style="border:1px solid #B6BABF;padding:1px;" src="[{$dir_images}]objecttypes/san.png" width="20" title="[{isys type="lang" ident="LC__CMDB__CATG__CLUSTER_SHARED_STORAGE"}] (LDEV Server)" />
				    </span>
				</th>
			</tr>
			[{foreach from=$sanlist item="s" key="san_id"}]
				<tr id="san_desc_[{$san_id}]">
					<td class="minheight gradient text-shadow head">
						<strong>[{$s.san_title}]</strong><br/>
						([{$s.object_link}])
					</td>
				</tr>
			[{/foreach}]
		</table>

		<div style="overflow-x:auto;position:relative;" id="matrix_scroller" class="mt10">
			<table class="sanmatrix" cellspacing="0" cellpadding="0">
				<tr>
				[{foreach from=$c_members item="cm"}]
					<th class="minheight gradient text-shadow">
						[{$cm.link}]
					</th>
				[{/foreach}]
				</tr>

				[{foreach from=$sanlist item="s" key="san_id"}]
					[{assign var=hidden value=true}]

					<tr id="san_row_[{$san_id}]">
						[{foreach from=$c_members item="cm"}]
							[{assign var=obj_id value=$cm.isys_obj__id}]

							[{if isset($coords[$obj_id][$san_id])}]
								[{assign var=hidden value=false}]
							[{/if}]

							<td class="center minheight [{if isset($coords[$obj_id][$san_id])}]gradient" style="background-color:rgba(0,0,0,.075);" title="LDEV Client: [{$coords[$obj_id][$san_id].isys_catg_ldevclient_list__title}][{/if}]">
								<label>
									[{isys type="checkbox" p_strOnClick="\$('ldevclient_hidden[$san_id][$obj_id]').value = (this.checked)?'1':'0';" p_bInfoIconSpacer=0 p_bChecked=$coords[$cm.isys_obj__id][$san_id] name="ldevclient[$san_id][$obj_id]" p_strValue=$obj_id}]
									<input type="hidden" id="ldevclient_hidden[[{$san_id}]][[{$obj_id}]]" name="ldevclient_hidden[[{$san_id}]][[{$obj_id}]]" value="[{if $coords[$cm.isys_obj__id][$san_id]}]1[{else}]0[{/if}]" />
								</label>
							</td>
						[{/foreach}]
					</tr>

					[{if $hidden === true}]
					<script type="text/javascript">
						(function () {
							"use strict";

							$('san_row_[{$san_id}]').hide().className = 'hidden';
							$('san_desc_[{$san_id}]').hide().className = 'hidden';
						}());
					</script>
					[{/if}]

				[{/foreach}]

			</table>
		</div>

		<div class="cb"></div>
	[{else}]
		[{isys type="lang" ident="LC__CMDB__CLUSTER_SHARED_STORAGE__NO_MEMBERS_OR_SANS"}]
	[{/if}]
	</div>
</div>