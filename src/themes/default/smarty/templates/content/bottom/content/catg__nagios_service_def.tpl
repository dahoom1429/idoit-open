<style type="text/css">
	.tooltip .commentary {
		margin: 0;
	}

	.command-comment {
		padding: 6px 3px;
		background: #fff;
	}
</style>

<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__CATG__NAGIOS_EXPORT"}]</h3>
<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_SERVICE_DEF__IS_EXPORTABLE' ident="LC__CATG__NAGIOS_CONFIG_EXPORT"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__IS_EXPORTABLE" p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="lang" ident="LC__CATG__NAGIOS_SERVICE_TPL"}]
		</td>
		<td class="value">
			[{isys
				name="C__CATG__NAGIOS_SERVICE_DEF__TEMPLATE"
				type="f_popup"
				p_strPopupType="browser_object_ng"
				catFilter="C__CATG__NAGIOS_SERVICE_TPL_FOLDER"
				multiselection=true
				sortSelection=true}]
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5" />
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__SERVICE_DESCRIPTION" ident="service_description"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__SERVICE_DESCRIPTION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND" ident="check_command"}]</td>
		<td class="value pl20">
			<div class="[{if isys_glob_is_edit_mode()}]input-group input-size-normal[{/if}]">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND" p_onChange="idoit.callbackManager.triggerCallback('nagios_service__check_command_description', this.id);"}]
				[{if isys_glob_is_edit_mode()}]<div class="input-group-addon input-group-addon-clickable">[{/if}]
					<img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND" src="[{$dir_images}]icons/silk/information.png" />
				[{if isys_glob_is_edit_mode()}]</div>[{/if}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="input-group input-size-normal mt5">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND_PARAMETERS" ident="check_command parameter"}]</td>
		<td class="value">
			[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND_PARAMETERS"}]
		</td>
	</tr>
</table>

<h3 id="advanced_link" class="gradient border-top border-bottom mouse-pointer p5 mt10">
	<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__EXTENDED"}]</span>
</h3>
<table class="contentTable" id="advanced_nagios_options">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__MAX_CHECK_ATTEMPTS" ident="max_check_attempts"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__MAX_CHECK_ATTEMPTS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_INTERVAL" ident="check_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__RETRY_INTERVAL" ident="retry_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__RETRY_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD" ident="check_period"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]
			<div class="input-group input-size-normal mt5">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATIONS_ENABLED" ident="notifications_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATIONS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_INTERVAL" ident="notification_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD" ident="notification_period"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="input-group input-size-normal mt5">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_OPTIONS__available_box" ident="notification_options"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_OPTIONS"}]</td>
	</tr>


	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__ACTIVE_CHECKS_ENABLED" ident="active_checks_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__ACTIVE_CHECKS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__PASSIVE_CHECKS_ENABLED" ident="passive_checks_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__PASSIVE_CHECKS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__INITIAL_STATE" ident="initial_state"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__INITIAL_STATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__FIRST_NOTIFICATION_DELAY" ident="first_notification_delay"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__FIRST_NOTIFICATION_DELAY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_ENABLED" ident="flap_detection_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_OPTIONS__available_box" ident="flap_detection_options"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_SERVICE_DEF__FLAP_DETECTION_OPTIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__LOW_FLAP_THRESHOLD" ident="low_flap_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__LOW_FLAP_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__HIGH_FLAP_THRESHOLD" ident="high_flap_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__HIGH_FLAP_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__IS_VOLATILE" ident="is_volatile"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__IS_VOLATILE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__OBSESS_OVER_SERVICE" ident="obsess_over_service"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__OBSESS_OVER_SERVICE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_FRESHNESS" ident="check_freshness"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__CHECK_FRESHNESS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__FRESHNESS_THRESHOLD" ident="freshness_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__FRESHNESS_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER_ENABLED" ident="event_handler_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER" ident="event_handler"}]</td>
		<td class="value pl20">
			<div class="[{if isys_glob_is_edit_mode()}]input-group input-size-normal[{/if}]">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER"}]
				[{if isys_glob_is_edit_mode()}]<div class="input-group-addon input-group-addon-clickable">[{/if}]
					<img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER" src="[{$dir_images}]icons/silk/information.png" />
				[{if isys_glob_is_edit_mode()}]</div>[{/if}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="input-group input-size-normal mt5">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__PROCESS_PERF_DATA" ident="process_perf_data"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__PROCESS_PERF_DATA"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__RETAIN_STATUS_INFORMATION" ident="retain_status_information"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__RETAIN_STATUS_INFORMATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__RETAIN_NONSTATUS_INFORMATION" ident="retain_nonstatus_information"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_SERVICE_DEF__RETAIN_NONSTATUS_INFORMATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__ESCALATIONS__available_box" ident="escalations"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_SERVICE_DEF__ESCALATIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__ACTION_URL" ident="action_url"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__ACTION_URL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__ICON_IMAGE" ident="icon_image"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__ICON_IMAGE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__ICON_IMAGE_ALT" ident="icon_image_alt"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__ICON_IMAGE_ALT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTES" ident="notes"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__NOTES"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__NOTES_URL" ident="notes_url"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__NOTES_URL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME" ident="display_name"}]</td>
		<td class="value pl20">
		[{if isys_glob_is_edit_mode()}]
			<div class="input-group input-size-normal">
				<div class="input-group-addon input-group-addon-unstyled">
					<input type="radio" name="C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]" [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]checked="checked"[{/if}] />
				</div>
				[{isys type="f_text" name="C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="input-group input-size-normal mt5">
				<div class="input-group-addon input-group-addon-unstyled">
					<input type="radio" name="C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]" [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] />
				</div>
				<div class="input" style="border:none; box-shadow:none;">
					[{isys type="lang" ident="LC__CMDB__CATG__APPLICATION_OBJ_APPLICATION"}]
				</div>
			</div>
		[{else}]
			[{$display_name_view}]
		[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_SERVICE_DEF__CUSTOM_OBJ_VARS" ident="custom_object_vars"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__CATG__NAGIOS_SERVICE_DEF__CUSTOM_OBJ_VARS"}]</td>
	</tr>
</table>

<script>
	(function () {
		"use strict";

		var advanced_link = $('advanced_link');

		if (advanced_link) {
			advanced_link.on('click', function () {
				var table = this.next();

				if (table.hasClassName('hide')) {
					this.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
					table.removeClassName('hide');
				} else {
					this.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
					table.addClassName('hide');
				}
			});
		}

		$('advanced_nagios_options').addClassName('hide');

		var display_name = $('C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME'),
			display_name_radios = $$('input[name="C__CATG__NAGIOS_SERVICE_DEF__DISPLAY_NAME_SELECTION"]'),
			check_period = $('C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD'),
			check_period_plus = $('C__CATG__NAGIOS_SERVICE_DEF__CHECK_PERIOD_PLUS'),
			event_handler = $('C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER'),
			event_handler_plus = $('C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER_PLUS'),
			notification_period = $('C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD'),
			notification_period_plus = $('C__CATG__NAGIOS_SERVICE_DEF__NOTIFICATION_PERIOD_PLUS'),
			check_command = $('C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND'),
			check_command_plus = $('C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND_PLUS');

		if (check_command && check_command_plus) {
			check_command.on('change', function () {
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[0].checked = true;
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[1].checked = false;
				check_command_plus.selectedIndex = 0;
			});

			check_command_plus.on('change', function () {
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[0].checked = false;
				$$('input[name="C__CHECK_COMMAND_SELECTION"]')[1].checked = true;
				check_command.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, just "looks" right).
			if ($F(check_command) > 0) {
				check_command.previous('input[type="radio"]').checked = true;
			}

			if ($F(check_command_plus) > 0) {
				check_command_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (check_period && check_period_plus) {
			check_period.on('change', function () {
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = true;
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = false;
				check_period_plus.selectedIndex = 0;
			});

			check_period_plus.on('change', function () {
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[0].checked = false;
				$$('input[name="C__CHECK_PERIOD_SELECTION"]')[1].checked = true;
				check_period.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, just "looks" right).
			if ($F(check_period) > 0) {
				check_period.previous('input[type="radio"]').checked = true;
			}

			if ($F(check_period_plus) > 0) {
				check_period_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (notification_period && notification_period_plus) {
			notification_period.on('change', function () {
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[0].checked = true;
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[1].checked = false;
				notification_period_plus.selectedIndex = 0;
			});

			notification_period_plus.on('change', function () {
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[0].checked = false;
				$$('input[name="C__NOTIFICATION_PERIOD_SELECTION"]')[1].checked = true;
				notification_period.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, just "looks" right).
			if ($F(notification_period) > 0) {
				notification_period.previous('input[type="radio"]').checked = true;
			}

			if ($F(notification_period_plus) > 0) {
				notification_period_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (event_handler && event_handler_plus) {
			event_handler.on('change', function () {
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[0].checked = true;
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[1].checked = false;
				event_handler_plus.selectedIndex = 0;
			});

			event_handler_plus.on('change', function () {
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[0].checked = false;
				$$('input[name="C__EVENT_HANDLER_SELECTION"]')[1].checked = true;
				event_handler.selectedIndex = 0;
			});

			// Visual selection (has no effect on any logic, just "looks" right).
			if ($F(event_handler) > 0) {
				event_handler.previous('input[type="radio"]').checked = true;
			}

			if ($F(event_handler_plus) > 0) {
				event_handler_plus.previous('input[type="radio"]').checked = true;
			}
		}

		if (display_name && display_name_radios.length == 2) {
			display_name.on('focus', function () {
				display_name_radios[0].checked = true;
				display_name_radios[1].checked = false;
			});
		}

		idoit.callbackManager
			.registerCallback('nagios_service__check_command_description', function (el, value) {
				if (Object.isUndefined(value)) {
					value = $(el).getValue();
				}

				new Ajax.Request('?ajax=1&call=nagios&func=load_command_comment', {
					parameters:{
						command_id:value
					},
					method:"post",
					onComplete:function (response) {
						new Tip($$('img[data-input-el="' + el + '"]')[0], response.responseJSON.data, {className:'command-comment border'});
					}.bind(el)
				});
			})
			.triggerCallback('nagios_service__check_command_description', 'C__CATG__NAGIOS_SERVICE_DEF__CHECK_COMMAND', '[{$check_command_value}]')
			.triggerCallback('nagios_service__check_command_description', 'C__CATG__NAGIOS_SERVICE_DEF__EVENT_HANDLER', '[{$event_handler_value}]');
	}());
</script>