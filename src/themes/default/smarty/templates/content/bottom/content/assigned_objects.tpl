<table class="contentTable">
  <tr>
  	<td>
		<div style="" id="object_list">
  			<h3 style="">[{isys type="lang" ident="LC__CMDB__CATG__ASSIGNED_OBJECTS"}]:</h3>
  			<br />

  			[{if is_array($g_assigned)}]
	  		<table class="contentInfoTable" cellspacing="0" width="100%" cellpadding="0">
	  			<thead>
	  				<tr>
	  					<th>[{isys type="lang" ident="LC__CMDB__CATG__TITLE"}]</th>
	  					<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</th>
	  					<th>[{isys type="lang" ident="LC__CATP__IP__ADDRESS"}]</th>
	  					<th>[{isys type="lang" ident="LC__CATG__ODEP_OBJ"}] Status</th>
	  					<!--
	  					<th>[{isys type="lang" ident="LC__CMDB__CATS__LICENCE_USER_P"}]</th>
	  					-->
	  				</tr>
	  			</thead>
	  			<tbody>
	  				[{foreach from=$g_assigned item=l_assigned}]
		  				<tr>
		  					<td class="bold">
			  					[{$l_assigned.isys_obj__title}]
		  					</td>
		  					<td
		  						[{if $l_assigned.isys_obj__status == $smarty.const.C__RECORD_STATUS__TEMPLATE}]
									[{if $smarty.const.C__TEMPLATE__COLORS}]style="color:[{$smarty.const.C__TEMPLATE__COLOR_VALUE}]"[{/if}]
								[{/if}]>
	  							[{isys type="lang" ident=$l_assigned.isys_obj_type__title}]
		  					</td>
		  					<td
		  						[{if $l_assigned.isys_obj__status == $smarty.const.C__RECORD_STATUS__TEMPLATE}]
									[{if $smarty.const.C__TEMPLATE__COLORS}]style="color:[{$smarty.const.C__TEMPLATE__COLOR_VALUE}]"[{/if}]
								[{/if}]>
		  						[{$l_assigned.isys_cats_net_ip_addresses_list__title|default:"-"}]
		  					</td>
		  					<td
		  						[{if $l_assigned.isys_obj__status == $smarty.const.C__RECORD_STATUS__TEMPLATE}]
									[{if $smarty.const.C__TEMPLATE__COLORS}]style="color:[{$smarty.const.C__TEMPLATE__COLOR_VALUE}];font-weight:bold;"[{/if}]
								[{/if}]
		  					>
		  						[{$l_assigned.status|default:"-"}]
		  					</td>

		  				</tr>
		  			[{/foreach}]
	  			</tbody>
	  		</table>
	  		[{else}]
	  		-
	  		[{/if}]
  		</div>
  	</td>
  </tr>
</table>