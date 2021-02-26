<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='CONTACT_NAGIOS_IS_EXPORTABLE' ident="LC__CATG__NAGIOS_CONFIG_EXPORT"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_NAGIOS_IS_EXPORTABLE" p_bDbFieldNN=1}]</td>
    </tr>
    <tr>
        <td colspan="2">
            <hr class="mt5 mb5" />
        </td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="contact_name" name="CONTACT_NAME"}]</td>
		<td class="value">
			<div class="ml20">
				[{if isys_glob_is_edit_mode()}]
					<label>
						<input type="radio" name="CONTACT_NAME_SELECTION" class="ml5 mr5" value="[{$smarty.const.C__NAGIOS__PERSON_OPTION__OBJECT_TITLE}]" [{if $contact_name_selection == $smarty.const.C__NAGIOS__PERSON_OPTION__OBJECT_TITLE}]checked="checked"[{/if}] />
						[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}] ("<span class="text-grey">[{$obj_title}]</span>")
					</label>
					<br />
					<label>
						<input type="radio" name="CONTACT_NAME_SELECTION" class="ml5 mr5" value="[{$smarty.const.C__NAGIOS__PERSON_OPTION__USERNAME}]" [{if $contact_name_selection == $smarty.const.C__NAGIOS__PERSON_OPTION__USERNAME}]checked="checked"[{/if}] />
						[{isys type="lang" ident="LC__CONTACT__PERSON_USER_NAME"}] ("<span class="text-grey">[{$user_name}]</span>")
					</label>
					<br />
					<div class="input-group input-size-normal">
						<div class="input-group-addon input-group-addon-unstyled">
							<input type="radio" name="CONTACT_NAME_SELECTION" value="[{$smarty.const.C__NAGIOS__PERSON_OPTION__INPUT}]" [{if $contact_name_selection == $smarty.const.C__NAGIOS__PERSON_OPTION__INPUT}]checked="checked"[{/if}] />
						</div>

						[{isys type="f_text" name="CONTACT_NAME"}]
					</div>
				[{else}]
					[{$contact_name}]
				[{/if}]
			</div>
		</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="alias" name="CONTACT_ALIAS"}]</td>
        <td class="value">[{isys type="f_text" name="CONTACT_ALIAS"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="host_notification_enabled" name="CONTACT_HOST_NOTIFICATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_HOST_NOTIFICATION" p_bDbFieldNN=1}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="service_notification_enabled" name="CONTACT_SERVICE_NOTIFICATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_SERVICE_NOTIFICATION" p_bDbFieldNN=1}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="host_notification_period" name="CONTACT_HOST_NOTIFICATION_PERIOD"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="CONTACT_HOST_NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="CONTACT_HOST_NOTIFICATION_PERIOD"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="mt5 input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="CONTACT_HOST_NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="CONTACT_HOST_NOTIFICATION_PERIOD_PLUS"}]
			</div>
		</td>
	</tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="service_notification_period" name="CONTACT_SERVICE_NOTIFICATION_PERIOD"}]</td>
        <td class="value pl20">
            <div class="input-group input-size-normal">
	            [{if isys_glob_is_edit_mode()}]
		            <div class="input-group-addon input-group-addon-unstyled">
			            <input type="radio" name="CONTACT_SERVICE_NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
		            </div>
	            [{/if}]
	            [{isys type="f_dialog" name="CONTACT_SERVICE_NOTIFICATION_PERIOD"}]
            </div>

	        [{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

            <div class="mt5 input-group input-size-normal">
	            [{if isys_glob_is_edit_mode()}]
		            <div class="input-group-addon input-group-addon-unstyled">
			            <input type="radio" name="CONTACT_SERVICE_NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
		            </div>
	            [{/if}]
	            [{isys type="f_popup" p_strPopupType="dialog_plus" name="CONTACT_SERVICE_NOTIFICATION_PERIOD_PLUS"}]
            </div>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="host_notification_options" name="HOST_NOTIFICATION_OPTIONS"}]</td>
        <td class="value">[{isys type="f_dialog_list" name="HOST_NOTIFICATION_OPTIONS" p_bLinklist="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="service_notification_options" name="SERVICE_NOTIFICATION_OPTIONS"}]</td>
        <td class="value">[{isys type="f_dialog_list" name="SERVICE_NOTIFICATION_OPTIONS" p_bLinklist="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="host_notification_commands" name="CONTACT_HOST_NOTIFICATION_COMMANDS"}]</td>
        <td class="value">[{isys type="f_dialog_list" name="CONTACT_HOST_NOTIFICATION_COMMANDS" p_bLinklist="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="service_notification_commands" name="CONTACT_SERVICE_NOTIFICATION_COMMANDS"}]</td>
        <td class="value">[{isys type="f_dialog_list" name="CONTACT_SERVICE_NOTIFICATION_COMMANDS" p_bLinklist="1"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="can_submit_commands" name="CONTACT_CAN_SUBMIT_COMMANDS"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_CAN_SUBMIT_COMMANDS" p_bDbFieldNN=1}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="retain_status_information" name="CONTACT_RETAIN_STATUS_INFORMATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_RETAIN_STATUS_INFORMATION" p_bDbFieldNN=1}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="retain_nonstatus_information" name="CONTACT_RETAIN_NONSTATUS_INFORMATION"}]</td>
        <td class="value">[{isys type="f_dialog" name="CONTACT_RETAIN_NONSTATUS_INFORMATION" p_bDbFieldNN=1}]</td>
    </tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATS__PERSON_NAGIOS__CUSTOM_OBJ_VARS" ident="custom_object_vars"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__CATS__PERSON_NAGIOS__CUSTOM_OBJ_VARS"}]</td>
	</tr>
</table>

<script>
    var contact_name_input = $('CONTACT_NAME'),
        contact_name_radios = $$('input[name="CONTACT_NAME_SELECTION"]'),
	    host_notification_period = $('CONTACT_HOST_NOTIFICATION_PERIOD'),
        host_notification_period_plus = $('CONTACT_HOST_NOTIFICATION_PERIOD_PLUS'),
        service_notification_period = $('CONTACT_SERVICE_NOTIFICATION_PERIOD'),
        service_notification_period_plus = $('CONTACT_SERVICE_NOTIFICATION_PERIOD_PLUS'),
	    radios;

    if (contact_name_input && contact_name_radios.length == 3) {
	    contact_name_input.on('focus', function () {
		    contact_name_radios[0].checked = false;
		    contact_name_radios[1].checked = false;
		    contact_name_radios[2].checked = true;
	    });
    }

    if (host_notification_period && host_notification_period_plus) {
	    var contact_host_notification_period_selection = $$('input[name="CONTACT_HOST_NOTIFICATION_PERIOD_SELECTION"]');

	    host_notification_period.on('change', function () {
		    contact_host_notification_period_selection[0].checked = true;
		    contact_host_notification_period_selection[1].checked = false;
		    host_notification_period_plus.selectedIndex = 0;
	    });

	    host_notification_period_plus.on('change', function () {
		    contact_host_notification_period_selection[0].checked = false;
		    contact_host_notification_period_selection[1].checked = true;
		    host_notification_period.selectedIndex = 0;
	    });

	    // Visual selection (has no effect on any logic, just "looks" right).
	    if ($F(host_notification_period) > 0) {
		    host_notification_period.previous('.input-group-addon').down('input[type="radio"]').checked = true;
	    }

	    if ($F(host_notification_period_plus) > 0) {
		    host_notification_period_plus.previous('.input-group-addon').down('input[type="radio"]').checked = true;
	    }
    }

    if (service_notification_period && service_notification_period_plus) {
	    var contact_service_notification_period_selection = $$('input[name="CONTACT_SERVICE_NOTIFICATION_PERIOD_SELECTION"]');

	    service_notification_period.on('change', function () {
		    contact_service_notification_period_selection[0].checked = true;
		    contact_service_notification_period_selection[1].checked = false;
		    service_notification_period_plus.selectedIndex = 0;
	    });

	    service_notification_period_plus.on('change', function () {
		    contact_service_notification_period_selection[0].checked = false;
		    contact_service_notification_period_selection[1].checked = true;
		    service_notification_period.selectedIndex = 0;
	    });

	    // Visual selection (has no effect on any logic, just "looks" right).
	    if ($F(service_notification_period) > 0) {
		    service_notification_period.previous('.input-group-addon').down('input[type="radio"]').checked = true;
	    }

	    if ($F(service_notification_period_plus) > 0) {
		    service_notification_period_plus.previous('.input-group-addon').down('input[type="radio"]').checked = true;
	    }
    }
</script>