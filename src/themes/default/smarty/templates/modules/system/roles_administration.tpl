<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__MODULE__SYSTEM__ROLES_ADMINISTRATION"}]</h2>

<script type="text/javascript">
	var updRoles = [],
		delRoles = [];

	window.add_contact_role = function () {
		var $select = new Element('select', {className:'input input-small', name:'new_role_relation_type[]'}),
			$row = new Element('tr', {className:'row'});

		[{foreach $relation_types as $rt_id => $rt}]
		$select.insert(new Element('option', {value:'[{$rt_id}]'}).update('[{$rt.title}]'));
		[{/foreach}]

		$row
			.update(new Element('td', {className:'m10'})
				.update(new Element('input', {className:'input input-small', type:'text', name:'new_role_title[]'})))
			.insert(new Element('td')
				.update($select))
			.insert(new Element('td')
				.update(new Element('button', {className:'btn', type:'button', onclick:'this.up().up().remove();'})
					.update(new Element('img', {src:'[{$dir_images}]icons/silk/cross.png', className:'mr5'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]'))));

		$('roles_body').insert($row);
	};

	window.add_update_contact_role = function (p_value) {
		if (updRoles.indexOf(p_value) == -1) {
			updRoles.push(p_value);
			$('updRoles').value = updRoles.join(',');
		}
	};

	window.add_delete_contact_role = function (p_value) {
		if (delRoles.indexOf(p_value) == -1) {
			delRoles.push(p_value);
			$('delRoles').value = delRoles.join(',');

			if ((posi = updRoles.indexOf(p_value)) != -1) {
				updRoles.splice(posi, 1);
				$('updRoles').value = updRoles.join(',');
			}
		}
	}
</script>

<table class="mainTable">
	<thead>
	<tr>
		<th>[{isys type="lang" ident="LC__CMDB__CONTACT_ROLE"}]</th>
		<th>[{isys type="lang" ident="LC__CATG__RELATION__RELATION_TYPE"}]</th>
		[{if isys_glob_is_edit_mode()}]
		<th>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__OPTIONS"}]</th>
		[{/if}]
	</tr>
	</thead>
	<tbody id="roles_body">
	[{while $l_row = $contact_roles->get_row()}]
		<tr class="[{cycle values="line0,line1"}]">
			[{if isys_glob_is_edit_mode() && $l_row.isys_contact_tag__const <> 'C__CONTACT_TYPE__ADMIN'}]
			<td class="m10" style="vertical-align: middle">
				[{if $l_row.isys_contact_tag__const == NULL}]
				<input type="text" class="input input-small" onchange="window.add_update_contact_role([{$l_row.isys_contact_tag__id}]);" name="role_title[[{$l_row.isys_contact_tag__id}]]" value="[{$l_row.isys_contact_tag__title}]"/>
				[{else}]
				[{isys type="lang" ident=$l_row.isys_contact_tag__title}]
				[{/if}]
			</td>
			<td class="m10">
				<!-- TODO ADD Relation types -->
				<select class="input input-small" onchange="window.add_update_contact_role([{$l_row.isys_contact_tag__id}])" name="role_relation_type[[{$l_row.isys_contact_tag__id}]]">
					[{foreach from=$relation_types item=rt key=rt_id}]
					<option value="[{$rt_id}]" [{if $rt_id eq $l_row.isys_contact_tag__isys_relation_type__id}]selected="selected" [{/if}]>[{$rt.title}]</option>
					[{/foreach}]
				</select>
			</td>
			<td class="m10">
				[{if $l_row.isys_contact_tag__const == NULL}]
				<button class="btn" type="button" onclick="window.add_delete_contact_role([{$l_row.isys_contact_tag__id}]); this.up().up().remove();">
					<img src="[{$dir_images}]icons/silk/cross.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
				</button>
				[{/if}]
			</td>
			[{else}]
			<td class="m10 vam">[{isys type="lang" ident=$l_row.isys_contact_tag__title}]</td>
			<td class="m10">[{$relation_types[$l_row.isys_contact_tag__isys_relation_type__id].title}]</td>
			[{/if}]
		</tr>
		[{/while}]
	</tbody>
</table>

<input type="hidden" name="delRoles" id="delRoles" value=""/>
<input type="hidden" name="updRoles" id="updRoles" value=""/>

[{if isys_glob_is_edit_mode()}]
<button type="button" class="btn m10" onclick="window.add_contact_role();">
	<img src="[{$dir_images}]icons/silk/add.png" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span>
</button>
[{/if}]