<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="isys_relation_type"}]</h2>

<table class="mainTable" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATION_TYPES__RELATION_TITLE"}]</th>
		<th>[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATION_TYPES__DESCRIPTION_MASTER"}]</th>
		<th>[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATION_TYPES__DESCRIPTION_SLAVE"}]</th>
		<th>[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATION_TYPES__DEFAULT_DIRECTION"}]</th>
		<th>[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATION_TYPES__DEFAULT_WEIGHTING"}]</th>
        <th>[{isys type="lang" ident="LC__CMDB__RELATION_IMPLICIT"}]/[{isys type="lang" ident="LC__CMDB__RELATION_EXPLICIT"}]</th>
		[{if isys_glob_is_edit_mode()}]
			<th>Optionen</th>
		[{/if}]
	</tr>
	</thead>
	<tbody id="relationtable_body">
	[{foreach from=$relation_types item=s}]
		<tr data-relation-type-id="[{$s.id}]" class="[{cycle values="line0,line1"}]">
			[{if isys_glob_is_edit_mode() && strpos($s.const, 'C__RELATION_TYPE__') === false}]
				<td><input type="text" class="input input-small" name="relation_title[[{$s.id}]]" value="[{$s.title}]" /></td>
				<td><input type="text" class="input input-mini" name="relation_title_master[[{$s.id}]]" value="[{$s.master}]" /></td>
				<td><input type="text" class="input input-mini" name="relation_title_slave[[{$s.id}]]" value="[{$s.slave}]" /></td>
				<td>
					<select class="input input-mini" name="relation_direction[[{$s.id}]]">
						<option [{if $s.default == $smarty.const.C__RELATION_DIRECTION__DEPENDS_ON_ME}]selected="selected"[{/if}] value="[{$smarty.const.C__RELATION_DIRECTION__DEPENDS_ON_ME}]">[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_MASTER"}]</option>
						<option [{if $s.default == $smarty.const.C__RELATION_DIRECTION__I_DEPEND_ON}]selected="selected"[{/if}] value="[{$smarty.const.C__RELATION_DIRECTION__I_DEPEND_ON}]">[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_SLAVE"}]</option>
					</select>
				</td>
				<td>[{$s.weighting}]</td>
                <td>
                    <select class="input input-mini" name="relation_type[[{$s.id}]]">
                        <option [{if $s.type == $smarty.const.C__RELATION__IMPLICIT}]selected="selected"[{/if}] value="[{$smarty.const.C__RELATION__IMPLICIT}]">[{isys type="lang" ident="LC__CMDB__RELATION_IMPLICIT"}]</option>
                        <option [{if $s.type == $smarty.const.C__RELATION__EXPLICIT}]selected="selected"[{/if}] value="[{$smarty.const.C__RELATION__EXPLICIT}]">[{isys type="lang" ident="LC__CMDB__RELATION_EXPLICIT"}]</option>
                    </select>
                </td>
				<td>
					<button type="button" class="btn btn-small delete-relation">
						<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
					</button>
				</td>
			[{else}]
				<td>[{isys type="lang" ident=$s.title}]</td>
				<td>[{isys type="lang" ident=$s.master}]</td>
				<td>[{isys type="lang" ident=$s.slave}]</td>
				<td>
					[{if $s.default == $smarty.const.C__RELATION_DIRECTION__DEPENDS_ON_ME}]
						[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_MASTER"}]
					[{else}]
						[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_SLAVE"}]
					[{/if}]
				</td>
				<td>[{if isys_glob_is_edit_mode()}][{$s.weighting}][{else}][{$s.weighting_text}][{/if}]</td>
                <td>[{if $s.type == $smarty.const.C__RELATION__IMPLICIT}][{isys type="lang" ident="LC__CMDB__RELATION_IMPLICIT"}][{else}][{isys type="lang" ident="LC__CMDB__RELATION_EXPLICIT"}][{/if}]</td>
				[{if isys_glob_is_edit_mode()}]<td>[{isys_tenantsettings::get('gui.empty_value', '-')}]</td>[{/if}]
			[{/if}]
		</tr>
	[{/foreach}]
	</tbody>
</table>

<table id="hidden-row" class="hide">
	<tr class="bg-green">
		<td><input type="text" class="input input-small" name="new_relation_title[]" value="" /></td>
		<td><input type="text" class="input input-mini" name="new_relation_title_master[]" value="" /></td>
		<td><input type="text" class="input input-mini" name="new_relation_title_slave[]" value="" /></td>
		<td>
			<select class="input input-small" name="new_relation_direction[]">
				<option value="[{$smarty.const.C__RELATION_DIRECTION__DEPENDS_ON_ME}]">[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_MASTER"}]</option>
				<option value="[{$smarty.const.C__RELATION_DIRECTION__I_DEPEND_ON}]">[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__CURRENT_OBJECT_IS_SLAVE"}]</option>
			</select>
		</td>
		<td>[{$weighting_tpl}]</td>
        <td>
            <select class="input input-mini" name="new_relation_type[]">
                <option value="[{$smarty.const.C__RELATION__IMPLICIT}]">[{isys type="lang" ident="LC__CMDB__RELATION_IMPLICIT"}]</option>
                <option value="[{$smarty.const.C__RELATION__EXPLICIT}]">[{isys type="lang" ident="LC__CMDB__RELATION_EXPLICIT"}]</option>
            </select>
        </td>
		<td>
			<button class="btn btn-small delete-relation-now" type="button">
				<img class="mr5" src="[{$dir_images}]icons/silk/cross.png"><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
			</button>
		</td>
	</tr>
</table>

<input type="hidden" name="delRelTypes" id="delRelTypes" value="" />

[{if isys_glob_is_edit_mode()}]
<button id="button_add_relation_type" type="button" class="m10 btn btn-small">
	<img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span>
</button>

<script type="text/javascript">
	var delRelTypes = [];

	$('button_add_relation_type').on('click', function () {
		$('relationtable_body').insert($('hidden-row').down('tr').clone(true));
	});

	$('relationtable_body').on('click', 'button.delete-relation', function (ev) {
		var $row = ev.findElement('button').up('tr'),
			relation_type_id = $row.readAttribute('data-relation-type-id');

		if (confirm('[{isys type="lang" ident="LC__MODULE__SYSTEM__RELATIONSHIP_TYPES__REALLY_DELETE" p_bHtmlEncode=false}]')) {
			if (delRelTypes.indexOf(relation_type_id) == -1 && relation_type_id !== null) {
				delRelTypes.push(relation_type_id);
				$('delRelTypes').value = delRelTypes.join(',');
			}

			$row.remove();
		}
	});

	// This is used for newly added relation types, which have not yet been saved (we skip the confirm stuff).
	$('relationtable_body').on('click', 'button.delete-relation-now', function (ev) {
		ev.findElement('button').up('tr').remove();
	});
</script>
[{/if}]
