<h2 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS"}]</h2>

<script type="text/javascript">
  add_status = function() {

  	var row = '<tr class="success">'+
		'<td>-</td>'+
		'<td>-</td>'+
		'<td><input type="text" class="input input-small" name="new_status_title[]" onchange="this.up().next().down().value = \'C__CMDB_STATUS__\' + this.value.toUpperCase();" /></td>'+
		'<td><input type="text" class="input input-small" name="new_status_const[]" value="C__CMDB_STATUS__" /></td>'+
		'<td><input type="text" class="input input-mini jscolor" value="FFFFFF" name="new_status_color[]" autocomplete="off" /></td>'+
		'<td class="">'+
			'<button class="btn" type="button" onclick="this.up().up().remove();">'+
				'<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>'+
			'</button>'+
		'</td>'+
	'</tr>';

  	$('statustable_body').insert(row);
  	jscolor.init();

  };
</script>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__SETTING__STATUS__IMPORT" ident="LC__CMDB__SYSTEM_SETTING__DEFAULT_STATUS"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__SETTING__STATUS__IMPORT" p_bDbFieldNN="1" p_strClass="input input-mini"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__SETTING__STATUS__SHOW_FILTER" ident="LC__CMDB__SYSTEM_SETTING__SHOW_FILTER"}] (my-doit)</td>
		<td class="value">[{isys type="f_dialog" name="C__SETTING__STATUS__SHOW_FILTER" p_bDbFieldNN="1" p_strClass="input input-mini"}]</td>
	</tr>
</table>

<hr />
	<table class="mainTable" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>ID</th>
			<th>Status</th>
			<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST_NAME"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST"}]</th>
			<th>[{isys type="lang" ident="LC__UNIVERSAL__COLOR"}]</th>
			[{if isys_glob_is_edit_mode()}]<th>Optionen</th>[{/if}]
			</tr>
	</thead>
	<tbody id="statustable_body">
	[{foreach $cmdb_status as $s}]
		<tr class="[{cycle values="line0,line1"}]">
			<td>[{$s.isys_cmdb_status__id}]</td>
			[{if isys_glob_is_edit_mode()}]
			<td>[{isys type="lang" ident=$s.isys_cmdb_status__title}]</td>
			<td><input type="text" class="input input-small" name="status_title[[{$s.isys_cmdb_status__id}]]" value="[{$s.isys_cmdb_status__title}]" /></td>
			<td><input type="text" class="input input-small" style="color:#777;" name="status_const[[{$s.isys_cmdb_status__id}]]" value="[{$s.isys_cmdb_status__const}]" /></td>
			<td><input type="text" class="input input-mini jscolor" value="[{$s.isys_cmdb_status__color}]" name="status_color[[{$s.isys_cmdb_status__id}]]" autocomplete="off" /></td>
			<td>
				<button class="btn" type="button" onclick="if (confirm('[{isys type="lang" ident="LC__UNIVERSAL__REALLY_DELETE"}]?')) { this.up().up().remove(); $('delStatus').value = $('delStatus').value + '[{$s.isys_cmdb_status__id}],'; }">
					<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
				</button>
			</td>
			[{else}]
			<td>[{isys type="lang" ident=$s.isys_cmdb_status__title}]</td>
			<td><strong>[{$s.isys_cmdb_status__title}]</strong></td>
			<td>[{$s.isys_cmdb_status__const}]</td>
			<td><div class="cmdb-marker" style="background-color:#[{$s.isys_cmdb_status__color}]; width:12px; height:12px;"></div> #[{$s.isys_cmdb_status__color}]</td>
			[{/if}]
		</tr>
	[{/foreach}]
	</tbody>
	</table>

<script type="text/javascript">
	jscolor.init();
</script>

<input type="hidden" name="delStatus" id="delStatus" value="" />

[{if isys_glob_is_edit_mode()}]
<button type="button" class="btn mt5 ml5 mb10" onclick="add_status();"><img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span></button>
[{/if}]