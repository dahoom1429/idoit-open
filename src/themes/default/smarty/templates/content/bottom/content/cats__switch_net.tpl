<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATG__SWITCH__VLAN_MANAGEMENT_PROTOCOL" name="C__CMDB__CATS__SWITCH_NET__VLAN"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_vlan_management_protocol"
				name="C__CMDB__CATS__SWITCH_NET__VLAN"
				tab="2"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATG__SWITCH__ROLE" name="C__CMDB__CATS__SWITCH_NET__ROLE"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_switch_role"
				name="C__CMDB__CATS__SWITCH_NET__ROLE"
				tab="2"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CATG__SWITCH__SPANNING_TREE" name="C__CMDB__CATS__SWITCH_NET__SPANNING_TREE"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="dialog_plus"
				p_strTable="isys_switch_spanning_tree"
				name="C__CMDB__CATS__SWITCH_NET__SPANNING_TREE"
				tab="2"}]
		</td>
	</tr>
</table>

<fieldset class="overview" style="margin-top:15px;">
	<legend><span>[{isys type="lang" ident="LC__CATG__SWITCH__VLAN_HELD_BY_OBJECT"}] ([{$vlans|count}])</span></legend>
	<div class="p10">
		<table class="mainTable">
			<thead>
				<th>[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}] ([{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__VLAN_ID"}])</th>
			</thead>
			<tbody>
				[{foreach from=$vlans item=vlan}]
				<tr>
					<td>
						<a [{if $vlan.default}]class="bold" title="Default VLAN"[{/if}] href="?[{$smarty.const.C__CMDB__GET__OBJECT}]=[{$vlan.obj_id}]">[{$vlan.obj_title}] ([{$vlan.layer2_vlan_id}])</a>
					</td>
				</tr>
				[{/foreach}]
			</tbody>
		</table>
	</div>
</fieldset>
