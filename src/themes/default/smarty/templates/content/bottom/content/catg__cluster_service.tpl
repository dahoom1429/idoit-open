<input type="hidden" id="default_server" name="default_server" value="[{$default_server_id}]" />
<input type="hidden" id="cluster_service_obj_id" value="[{$cluster_service_obj_id}]">

<table class="contentTable">
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION__VIEW' ident="LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE"}]
		</td>
		<td class="value">
			[{isys
				title="LC__BROWSER__TITLE__SOFTWARE"
				name="C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION"
				type="f_popup"
				p_strPopupType="browser_object_ng"}]
		</td>
	</tr>
</table>

<div id="cluster_options" class="mt5 mb5" style="display:none;"></div>

<table class="contentTable mt5">
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CMDB__CATG__CLUSTER_SERVICE__TYPE' ident="LC__CMDB__CATG__CLUSTER_SERVICE__TYPE"}]
		</td>
		<td class="value">
			[{isys type="f_dialog" name="C__CMDB__CATG__CLUSTER_SERVICE__TYPE" p_strTable="isys_cluster_type" p_bDbFieldNN="1"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CATG__CLUSTER_SERVICE__SERVICE_STATUS' ident="LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS"}]
		</td>
		<td class="value">
			[{isys type="f_dialog" name="C__CATG__CLUSTER_SERVICE__SERVICE_STATUS" p_bSort="0" p_bDbFieldNN="1"}]
		</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">
			[{isys type='f_label' name='C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON__available_box' ident="LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON"}]
		</td>
		<td class="value">
			[{isys
				type="f_dialog_list"
				name="C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON"
				remove_callback="idoit.callbackManager.triggerCallback('clusterservice__runs_on_callback').triggerCallback('clusterservice__set_default_server');"
				emptyMessage="LC__CMDB__CATG__CLUSTER_SERVICE__NO_MEMBERS"
				p_arData=$cluster_members}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type='f_label' name='C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER' ident="LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER"}]
		</td>
		<td class="value">
			[{isys
				type="f_dialog"
				name="C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER"
				id="C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER"
				p_bDbFieldNN=1
				p_onChange="idoit.callbackManager.triggerCallback('clusterservice__set_default_server');"}]
		</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		var default_server = $('C__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER'),
			application = $('C__CMDB__CATG__CLUSTER_SERVICE__APPLICATION__HIDDEN'),
			cluster_obj = $('cluster_service_obj_id');

		idoit.callbackManager
			.registerCallback('clusterservice__set_default_server',function () {
				if (default_server) {
					$('default_server').setValue(default_server.getValue());
				}
			}).registerCallback('clusterservice__runs_on_callback', function () {
				if (default_server) {
					var selected_values = $F('C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON__selected_values'),
						is_in_option = false;

					default_server.update(new Element('option', { 'value': '-1' }).update('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

					$('C__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON__selected_box').select('option').each(function ($option) {
						if (selected_values.indexOf($option.readAttribute('value')) >= 0) {
							default_server.insert(new Element('option', {value:$option.readAttribute('value')}).update($option.text));

							if ($option.readAttribute('value') == $('default_server').getValue()) {
								is_in_option = true;
							}
						}
					});

					if (is_in_option) {
						default_server.setValue($('default_server').getValue());
					}
				}
			}).registerCallback('clusterservice__cluster_service_selected', function () {
				var app_id = (application && application.getValue() > 0) ? application.getValue() : cluster_obj.getValue();

				new Ajax.Updater('cluster_options', '[{$cluster_service_ajax_url}]', {
					parameters: {
						application_id: app_id,
						navMode: $('navMode').getValue()
					},
					method: 'post',
					onComplete: function () {
						new new Effect.SlideDown('cluster_options', {duration:0.4});
					}
				});
			});

		if (cluster_obj && cluster_obj.getValue() > 0) {
			idoit.callbackManager.triggerCallback('clusterservice__cluster_service_selected');
		}

		Position.prepare();
		Position.includeScrollOffsets = true;
	}());
</script>