<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__HOST" ident="LC__CATG__NAGIOS_SERVICE_DEP__HOST"}]</td>
		<td class="value">[{isys name="C__CATG__NAGIOS_SERVICE_DEP__HOST" type="f_dialog" p_onChange="$('local-host').update(this.options[this.selectedIndex].innerHTML)"}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
    <tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY" ident="LC__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY"}]</td>
		<td class="value">
			[{isys
				name="C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY"
                id="C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY"
				type="f_popup"
				p_strPopupType="browser_object_ng"
                callback_accept="idoit.callbackManager.triggerCallback('nagios_service_dep__load_hosts');"
                callback_detach="idoit.callbackManager.triggerCallback('nagios_service_dep__remove_hosts');"}]
		</td>
	</tr>
    <tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY" ident="LC__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY"}]</td>
		<td class="value">[{isys name="C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY" type="f_dialog" p_onChange="$('dependent-host').update(this.options[this.selectedIndex].innerHTML)"}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key"></td>
		<td><span class="ml20">[{$doc_description}]</span></td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__INHERITS_PARENT" ident="inherits_parent"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEP__INHERITS_PARENT" p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD" ident="dependency_period"}]</td>
		<td class="value pl20">
			<div class="[{if isys_glob_is_edit_mode()}]input-group input-size-normal[{/if}]">
			[{if isys_glob_is_edit_mode()}]
				<div class="input-group-addon input-group-addon-unstyled">
					<input type="radio" name="C__DEP_PERIOD_SELECTION" disabled="disabled" />
				</div>
			[{/if}]
			[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD" p_bInfoIconSpacer=0 disableInputGroup=true}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="[{if isys_glob_is_edit_mode()}]mt5 input-group input-size-normal[{/if}]">
			[{if isys_glob_is_edit_mode()}]
				<div class="input-group-addon input-group-addon-unstyled">
					<input type="radio" name="C__DEP_PERIOD_SELECTION" disabled="disabled" />
				</div>
			[{/if}]
			[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD_PLUS" p_bInfoIconSpacer=0 disableInputGroup=true}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__EXEC_FAIL_CRITERIA__available_box" ident="execution_failure_criteria"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_SERVICE_DEP__EXEC_FAIL_CRITERIA"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEP__NOTIF_FAIL_CRITERIA__available_box" ident="notification_failure_criteria"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_SERVICE_DEP__NOTIF_FAIL_CRITERIA"}]</td>
	</tr>
</table>

<script>
	(function () {
		"use strict";

		idoit.callbackManager
			.registerCallback('nagios_service_dep__remove_hosts', function() {
				$('C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY').update();
			})
			.registerCallback('nagios_service_dep__load_hosts', function() {
				$('dependent-service').update($('C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY__VIEW').getValue());
				$('dependent-host').update('[{isys_tenantsettings::get('gui.empty_value', '-')}]');

				new Ajax.Request('[{$service_host_url}]', {
					parameters:{
						service_id:$F('C__CATG__NAGIOS_SERVICE_DEP__SERVICE_DEPENDENCY__HIDDEN')
					},
					method:"post",
					onComplete:function (response) {
						var i,
							variant = $('C__CATG__NAGIOS_SERVICE_DEP__HOST_DEPENDENCY'),
							json = response.responseJSON;

						variant.update(new Element('option', {value: '-1', selected: true}).insert('[{isys_tenantsettings::get('gui.empty_value', '-')}]'));

						for (i in json) {
							if (json.hasOwnProperty(i)) {
								variant.insert(new Element('option', {value: json[i].id}).insert(json[i].val));
							}
						}
					}
				});
			});

		var check_period = $('C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD'),
			check_period_plus = $('C__CATG__NAGIOS_SERVICE_DEP__DEP_PERIOD_PLUS');

		if (check_period && check_period_plus) {
			check_period.on('change', function () {
				$$('input[name="C__DEP_PERIOD_SELECTION"]')[0].checked = true;
				$$('input[name="C__DEP_PERIOD_SELECTION"]')[1].checked = false;
				check_period_plus.selectedIndex = 0;
			});

			check_period_plus.on('change', function () {
				$$('input[name="C__DEP_PERIOD_SELECTION"]')[0].checked = false;
				$$('input[name="C__DEP_PERIOD_SELECTION"]')[1].checked = true;
				check_period.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, just "looks" right).
			if ($F(check_period) > 0) {
				check_period.up('.input-group').down('.input-group-addon input[type="radio"]').checked = true;
			}

			if ($F(check_period_plus) > 0) {
				check_period_plus.up('.input-group').down('.input-group-addon input[type="radio"]').checked = true;
			}
		}
	}());
</script>