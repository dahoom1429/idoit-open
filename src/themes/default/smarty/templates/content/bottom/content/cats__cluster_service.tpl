<script type="text/javascript">
	(function () {
		"use strict";

		var cluster_service_selected = function () {
			new Ajax.Updater('cluster_members', '[{$cluster_service_ajax_url}]',
				{
					parameters: {
						cluster_id: $('C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER__HIDDEN').value,
						navMode: $('navMode').value
					},
					method: 'post',
					onComplete: function () {
						runs_on_callback();
					}
				});
		};

		var runs_on_callback = function ($update) {
			var $default_server = $('C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER'),
				$runs_on = $('C__CATS__CLUSTER_SERVICE__RUNS_ON__selected_box'),
				default_server_value = 0,
				default_server_value_in_selection = false;

            $update = $update === undefined ? false : true;

			if ($default_server) {
				default_server_value = $default_server.getValue();
				$default_server.update(new Element('option', {value:-1}).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

				$runs_on.select('option:selected').each(function($option) {
					$default_server.insert(new Element('option', {value:$option.value}).update($option.innerHTML));

					if ($option.value == default_server_value) {
						default_server_value_in_selection = true;
					}
				});

				if (default_server_value_in_selection) {
					$default_server.setValue(default_server_value);
				}

				if (!$update) {
                    new Chosen($runs_on);
                }
			}
		};

		var set_default_server = function () {
			$('default_server').setValue($F('C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER'));
		};

		var updateDefaultServerList = function () {
            runs_on_callback(1);
        };

		idoit.callbackManager
			.registerCallback('cluster_service__cluster_service_selected', cluster_service_selected)
			.registerCallback('cluster_service__runs_on_callback', runs_on_callback)
			.registerCallback('cluster_service__set_default_server', set_default_server)
            .registerCallback('cluster_service__update_default_server', updateDefaultServerList);
	}());
</script>

<input type="hidden" id="default_server" name="default_server" value="[{$default_server_id}]">
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER" name="C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER__VIEW"}]</td>
		<td class="value">
			[{isys
				name="C__CATS__CLUSTER_SERVICE__ASSIGNED_CLUSTER"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter="C__CATG__CLUSTER_ROOT"
				callback_accept="idoit.callbackManager.triggerCallback('cluster_service__cluster_service_selected');"}]
		</td>
	</tr>
	<tr>
		<td colspan="2"><hr /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES" name="C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.get.objID|strip_tags|escape
				dataretrieval="isys_cmdb_dao_category_g_ip::catdata_browser"
				name="C__CATS__CLUSTER_SERVICE__HOST_ADDRESSES"
				title="LC__POPUP__BROWSER__IP_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES" name="C__CATS__CLUSTER_SERVICE__VOLUMES__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.get.objID|strip_tags|escape
				dataretrieval="isys_cmdb_dao_category_g_drive::catdata_browser"
				name="C__CATS__CLUSTER_SERVICE__VOLUMES"
				title="LC__POPUP__BROWSER__DRIVE_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__SHARES" name="C__CATS__CLUSTER_SERVICE__SHARES__VIEW"}]</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.get.objID|strip_tags|escape
				dataretrieval="isys_cmdb_dao_category_g_shares::catdata_browser"
				name="C__CATS__CLUSTER_SERVICE__SHARES"
				title="LC__POPUP__BROWSER__SHARE_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA__VIEW" ident="LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"}]</td>
		<td class="value">
			[{isys
			title="LC__BROWSER__TITLE__DATABASE_SCHEMATA"
			p_strSelectedID=$preselectionDBMS
			name="C__CATS__CLUSTER_SERVICE_DATABASE_SCHEMATA"
			type="f_popup"
			catFilter="C__CATS__DATABASE_SCHEMA"
			p_strPopupType="browser_object_ng"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__TYPE" name="C__CATS__CLUSTER_SERVICE__TYPE"}]</td>
		<td class="value">
			[{isys
				type="f_dialog"
				name="C__CATS__CLUSTER_SERVICE__TYPE"
				p_strTable="isys_cluster_type"
				p_bDbFieldNN="1"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS" name="C__CATS__CLUSTER_SERVICE__SERVICE_STATUS"}]</td>
		<td class="value">
			[{isys
			type="f_dialog"
			name="C__CATS__CLUSTER_SERVICE__SERVICE_STATUS"
			p_bSort="0"
			p_bDbFieldNN="1"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER" name="C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"}]</td>
		<td class="value">
			[{isys
				type="f_dialog"
				name="C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"
				id="C__CATS__CLUSTER_SERVICE__DEFAULT_SERVER"
				p_bDbFieldNN="0"
				p_onChange="idoit.callbackManager.triggerCallback('cluster_service__set_default_server');"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON" name="C__CATS__CLUSTER_SERVICE__RUNS_ON__available_box"}]</td>
		<td class="value">
			<div id="cluster_members">
				[{isys
					type="f_dialog_list"
					name="C__CATS__CLUSTER_SERVICE__RUNS_ON"
					id="C__CATS__CLUSTER_SERVICE__RUNS_ON"
					add_callback="idoit.callbackManager.triggerCallback('cluster_service__update_default_server').triggerCallback('cluster_service__set_default_server');"
					p_arData=$cluster_members}]
			</div>
		</td>
	</tr>
</table>
