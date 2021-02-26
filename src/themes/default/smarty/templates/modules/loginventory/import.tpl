<script type="text/javascript">
	[{include file="modules/loginventory/loginventory.js"}]
</script>

<div>

	<div class="p10">
		<p class="mb10"><img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" /><span class="vam">[{isys type="lang" ident="LC__LOGINVENTORY__VERSION_COMPABILITY" p_bHtmlEncode=false}]</span></p>

		<div id="loginventory_error" style="display:none;">
			<h3>[{isys type="lang" ident="LC__LOGINVENTORY__DB_ERROR"}]</h3>
			<p class="m5"><a target="_blank" href="?moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=loginventory_databases&[{$smarty.const.C__GET__MODULE_SUB_ID}]=[{$smarty.const.C__MODULE__LOGINVENTORY}]&[{$smarty.const.C__GET__TREE_NODE}]=[{$smarty.const.C__MODULE__LOGINVENTORY}]10">[{isys type="lang" ident="LC__MODULE__IMPORT__LOGINVENTORY__TO_LOGINVENTORY_DATABASE_CONFIGURATION"}]</a></p>
			[{if !$is_win}]<p class="m5">[{isys type="lang" ident="LC__LOGINVENTORY__INFO__TDS_VERSION"}]</p>[{/if}]
		</div>

		<table class="contentTable">
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC__MODULE__IMPORT__LOGINVENTORY__LOGINVENTORY_DATABASES"}]:
				</td>
				<td class="value">
					[{isys type="f_dialog" name="selected_loginventory_db" p_onChange="show_loginventory_objects(this.value);" p_bEditMode=1 p_bDbFieldNN="1" tab="10"}]
				</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC__OCS__DEFAULT_OBJ_TYPE"}]:
				</td>
				<td class="value">
					[{isys type="f_dialog" name="C__LOGINVENTORY__OBJTYPE" p_bEditMode=1 p_bDbFieldNN="1" tab="10"}]
				</td>
			</tr>
		</table>
	</div>

	<div id="loginventory_list" class="mt10">

		<table class="mainTable" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th><input type="checkbox" value="X" onclick="CheckAllBoxes(this);"></th>
				<th style="cursor: pointer" id="loginventory_header_name" onclick="show_loginventory_objects($('selected_loginventory_db').value);">Name</th>
				<input type="hidden" id="table_ordered_by" value="">
				<th>[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__IMPORTED"}]</th>
			</tr>
			</thead>
			<tbody id="loginventory_object_list">
			</tbody>
		</table>

		<div class="m5">
			[{isys type="f_button" name="loginventory_submitter" id="loginventory_submitter" p_strValue="LC__UNIVERSAL__IMPORT" p_bEditMode="1" p_strStyle="display:none;" p_onClick="loginventory_multi_import();"}]
		</div>
		<pre id="loginventory_multi_import_done" class="m5" style="display:none;overflow:hidden;border:1px solid #ccc;background:#eee;font-family:Courier New, Monospace;font-size:11px;"></pre>

	</div>

	<div id="loginventory_object" style="display:none">
	</div>
</div>

<script>
	[{$js_script}]
</script>