<script type="text/javascript">
	[{include file="modules/import/ocs.js"}]
</script>

<div>
	<div class="p10">
		<div id="ocs_error" class="box-red p5" style="display:none;">
			<h3>[{isys type="lang" ident="LC__OCS__DB_ERROR"}]</h3>
			<p>
				<a target="_blank" href="?moduleID=[{$smarty.const.C__MODULE__SYSTEM}]&what=ocsdb&[{$smarty.const.C__GET__MODULE_SUB_ID}]=[{$smarty.const.C__MODULE__IMPORT}]&[{$smarty.const.C__GET__TREE_NODE}]=[{$smarty.const.C__MODULE__IMPORT}]7">
					[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__TO_OCS_DATABASE_CONFIGURATION"}]
				</a>
			</p>
		</div>
		<table class="contentTable" id="ocs_db_list">
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__OCS_DATABASES"}]
				</td>
				<td class="value">
					[{isys type="f_dialog" name="selected_ocsdb" p_onChange="show_ocs_objects(this.value);" p_bEditMode=1 p_bInfoIconSpacer=1 p_bDbFieldNN="0" tab="10"}]
					<img src="images/ajax-loading.gif" id="ocs_db_ajax_loader" style="display:none;vertical-align:middle;">
					[{isys type="f_dialog" name="templaet_objtype_arr" p_strStyle="display:none;" p_strClass="ocs_objtype_dialog normal" p_bEditMode=1 p_bInfoIconSpacer=0 p_bDbFieldNN=0 tab="10"}]
				</td>
			</tr>
            <tr>
                <td class="key">
                    [{isys type="lang" ident="LC__MODULE__OCS_IMPORT__RECALCULATE_OBJECT_TYPE_FOR_EXISTING_OBJECTS_BASED_ON_TAGS"}]
                </td>
                <td class="value pl20">
                    <select name="" class="input input-mini" id="recalculate_type_for_existing_objects" onChange="recalculateTypesOfExistingObjects(this.value)">
                        <option value="0" selected="selected">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</option>
                        <option value="1">[{isys type="lang" ident="LC__UNIVERSAL__YES"}]</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="key">
					[{isys type="lang" ident="LC__MODULE__OCS_IMPORT__OBJECT_TYPE_FOR_ALL_DEVICES"}]
				</td>
				<td class="value">
					[{isys type="f_dialog" p_bDbFieldNN=0 name="all_objtypes" p_strSelectedID="-1" p_bEditMode=1 p_onChange="change_all_objtypes(this.value);" p_bInfoIconSpacer=1 tab="10"}]
				</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="f_label" name="ocs_overwrite_hostaddress_port_multi" ident="LC__MODULE__OCS_IMPORT__OVERWRITE_HOSTADDRESS_AND_PORTS"}]
				</td>
				<td class="value pl20">
					<select name="" class="input input-mini" id="ocs_overwrite_hostaddress_port_multi">
						<option value="0" selected="selected">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</option>
						<option value="1">[{isys type="lang" ident="LC__UNIVERSAL__YES"}]</option>
					</select>
				</td>
			</tr>
            <tr>
                <td class="key">
                    [{isys type="f_label" name="ocs_overwrite_all_categories" ident="LC__MODULE__OCS_IMPORT__OVERWRITE_ALL_CATEGORIES"}]
                </td>
                <td class="value pl20">
                    <select name="" class="input input-mini" id="ocs_overwrite_all_categories">
                        <option value="0" selected="selected">[{isys type="lang" ident="LC__UNIVERSAL__NO"}]</option>
                        <option value="1">[{isys type="lang" ident="LC__UNIVERSAL__YES"}]</option>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="key">
					[{isys type="f_label" name="ocs_logging" ident="LC__MODULE__JDISC__IMPORT__LOGGING"}]
				</td>
				<td class="value pl20">
					<select name="" class="input input-small" id="ocs_logging_multi">
						<option value="0" selected="selected">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_LESS"}]</option>
						<option value="1">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DETAIL"}]</option>
						<option value="2">[{isys type="lang" ident="LC__MODULE__JDISC__IMPORT__LOGGING_DEBUG"}]</option>
					</select>
				</td>
			</tr>
		</table>
	</div>

	<div id="ocs_list" style="height:550px;overflow:auto;">

		<table class="mainTable border-top" cellpadding="0" cellspacing="0">
			<thead>
			<tr>
				<th><input type="checkbox" value="X" onclick="CheckAllBoxes(this);"></th>
				<th>Tag</th>
				<th style="width: 300px">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT_TYPE"}]</th>
				<th>SNMP</th>
				<th>Name</th>
				<th>[{isys type="lang" ident="LC__OBJTYPE__OPERATING_SYSTEM"}]</th>
				<th>[{isys type="lang" ident="LC__CATP__IP__ADDRESS"}]</th>
				<th>[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__IMPORTED"}]</th>
			</tr>
			</thead>
			<tbody id="ocs_object_list">
			<tr>
				<td colspan="6">
					<img src="images/ajax-loading.gif" /> [{isys type="lang" ident="LC__MODULE__OCS_IMPORT__LOADING_OBJETS"}]
				</td>
			</tr>
			</tbody>
		</table>
	</div>

	<div class="m5" id="ocs_list_import_button">
		[{isys type="f_button" name="ocs_submitter" id="ocs_submitter" icon="images/icons/silk/database_copy.png" p_strValue="LC__UNIVERSAL__IMPORT" p_bEditMode="1" p_strStyle="display:none;" p_onClick="window.getOCSImportPopup()"}]

		<p class="box-red red p5 mt10" id="ocs_no_selection_error" style="display:none;">
			[{isys type="lang" ident="LC__MODULE__IMPORT__OCS__SELECT_IMPORT_HOSTS"}]
		</p>

	</div>
	<span style="display:none;" id="ocs_import_message"><img src="images/ajax-loading.gif" class="vam" /> [{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>

	<button class="btn m10" id="ocs_multi_button_back" style="display:none;" onclick="window.location.reload();">[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__BACK"}]</button>
	<pre id="ocs_import_done" class="m5" style="display:none;"></pre>

	<div id="ocs_object" style="display:none"></div>
</div>

<style type="text/css">
	pre {
		border-left: 11px solid #ccc;
		padding-left: 0.7em;
	}
</style>

<script type="text/javascript">
	[{$js_script}]
</script>
