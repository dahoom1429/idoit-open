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
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NAME1' ident="name"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_NAME1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_HOST' ident="LC__MONITORING__EXPORT__CONFIGURATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_HOST"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_MAX_CHECK_ATTEMPTS' ident="max_check_attempts"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_MAX_CHECK_ATTEMPTS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD' ident="check_period"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="mt5 input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_INTERVAL' ident="notification_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD' ident="notification_period"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD"}]
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="mt5 input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__NOTIFICATION_PERIOD_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD_PLUS"}]
			</div>
		</td>
	</tr>
</table>

<h3 id="advanced_link" class="gradient border-top border-bottom mouse-pointer p5 mt10">
	<img src="[{$dir_images}]icons/silk/bullet_arrow_right.png" class="vam" /> <span class="vam">[{isys type="lang" ident="LC__EXTENDED"}]</span>
</h3>
<table class="contentTable" id="advanced_nagios_options">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATIONS_ENABLED' ident="notifications_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATIONS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS__available_box' ident="notification_options"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_OPTIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND' ident="check_command"}]</td>
		<td class="value pl20">
			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND"}]
				<div [{if isys_glob_is_edit_mode()}]class="input-group-addon input-group-addon-clickable"[{/if}]>
					<img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND" src="[{$dir_images}]icons/silk/information.png" />
				</div>
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="mt5 input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CHECK_COMMAND_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PARAMETERS' ident="check_command parameter"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PARAMETERS"}]</td>
	</tr>

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_INTERVAL' ident="check_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_RETRY_INTERVAL' ident="retry_interval"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_RETRY_INTERVAL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_ACTIVE_CHECKS_ENABLED' ident="active_checks_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_ACTIVE_CHECKS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_PASSIVE_CHECKS_ENABLED' ident="passive_checks_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_PASSIVE_CHECKS_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_INITIAL_STATE' ident="initial_state"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_INITIAL_STATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_OBSESS_OVER_HOST' ident="obsess_over_host"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_OBSESS_OVER_HOST"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_FRESHNESS' ident="check_freshness"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_FRESHNESS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_FRESHNESS_THRESHOLD' ident="freshness_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_FRESHNESS_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_ENABLED' ident="flap_detection_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS__available_box' ident="flap_detection_options"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_HOST_TPL_DEF_FLAP_DETECTION_OPTIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_LOW_FLAP_THRESHOLD' ident="low_flap_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_LOW_FLAP_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_HIGH_FLAP_THRESHOLD' ident="high_flap_threshold"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_HIGH_FLAP_THRESHOLD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_ENABLED' ident="event_handler_enabled"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_ENABLED"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER' ident="event_handler"}]</td>
		<td class="value pl20">

			<div class="input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER"}]
				<div [{if isys_glob_is_edit_mode()}]class="input-group-addon input-group-addon-clickable"[{/if}]>
					<img class="vam mouse-help" data-input-el="C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER" src="[{$dir_images}]icons/silk/information.png" />
				</div>
			</div>

			[{if isys_glob_is_edit_mode()}]<br class="cb" />[{/if}]

			<div class="mt5 input-group input-size-normal">
				[{if isys_glob_is_edit_mode()}]
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__EVENT_HANDLER_SELECTION" disabled="disabled" />
					</div>
				[{/if}]
				[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PLUS"}]
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PARAMETERS' ident="event_handler parameter"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PARAMETERS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_PROCESS_PERF_DATA' ident="process_perf_data"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_PROCESS_PERF_DATA"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_STATUS_INFORMATION' ident="retain_status_information"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_STATUS_INFORMATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_NONSTATUS_INFORMATION' ident="retain_nonstatus_information"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__NAGIOS_HOST_TPL_DEF_RETAIN_NONSTATUS_INFORMATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_FIRST_NOTIFICATION_DELAY' ident="first_notification_delay"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_FIRST_NOTIFICATION_DELAY"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS__available_box' ident="stalking_options"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_HOST_TPL_DEF_STALKING_OPTIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS__available_box' ident='escalations'}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__NAGIOS_HOST_TPL_DEF_ESCALATIONS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_ACTION_URL' ident="action_url"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_ACTION_URL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE' ident="icon_image"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE_ALT' ident="icon_image_alt"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_ICON_IMAGE_ALT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_VRML_IMAGE' ident="vrml_image"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_VRML_IMAGE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_STATUSMAP_IMAGE' ident="statusmap_image"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_STATUSMAP_IMAGE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTES' ident="notes"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTES"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_NOTES_URL' ident="notes_url"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_NOTES_URL"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME' ident="display_name"}]</td>
		<td class="value pl20">
			[{if isys_glob_is_edit_mode()}]
				<div class="input-group input-size-normal">
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]"
						       [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__OBJ_ID}]checked="checked"[{/if}] />
					</div>
					<div class="p5 pl0 text-normal">
						[{isys type="lang" ident="LC__UNIVERSAL__OBJECT_TITLE"}]
					</div>
				</div>

				<br class="cb" />

				<div class="input-group input-size-normal">
					<div class="input-group-addon input-group-addon-unstyled">
						<input type="radio" name="C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME_SELECTION" value="[{$smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]"
						       [{if $display_name_selection == $smarty.const.C__CATG_NAGIOS__NAME_SELECTION__INPUT}]checked="checked"[{/if}] />
					</div>
					[{isys type="f_text" name="C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME"}]
				</div>
			[{else}]
				[{$display_name_view}]
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__NAGIOS_HOST_TPL_DEF_CUSTOM_OBJ_VARS" ident="custom_object_vars"}]</td>
		<td class="value">[{isys type="f_textarea" name="C__CATG__NAGIOS_HOST_TPL_DEF_CUSTOM_OBJ_VARS"}]</td>
	</tr>
</table>

[{if $objectTableList}]
	<h3 class="mt15 p5 gradient" style="border:1px solid #b7b7b7; border-bottom: none;">[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONTAINER"}] Status (Subs)</h3>
	<div class="p5" style="margin-top:5px;">
		[{$objectTableList}]
	</div>
[{/if}]

<script>
	(function () {
		"use strict";

		var $advanced_link = $('advanced_link');

		if ($advanced_link)
		{
			$advanced_link.on('click', function (ev) {
				var $h3 = ev.findElement('h3'),
				    $table = $h3.next();

				if ($table.hasClassName('hide'))
				{
					$h3.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_down.png');
					$table.removeClassName('hide');
				}
				else
				{
					$h3.down('img').setAttribute('src', '[{$dir_images}]icons/silk/bullet_arrow_right.png');
					$table.addClassName('hide');
				}
			});
		}

		$('advanced_nagios_options').addClassName('hide');

		var check_command            = $('C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND'),
		    check_command_plus       = $('C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND_PLUS'),
		    event_handler            = $('C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER'),
		    event_handler_plus       = $('C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER_PLUS'),
		    check_period             = $('C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD'),
		    check_period_plus        = $('C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_PERIOD_PLUS'),
		    notification_period      = $('C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD'),
		    notification_period_plus = $('C__CATG__NAGIOS_HOST_TPL_DEF_NOTIFICATION_PERIOD_PLUS'),
		    display_name             = $('C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME'),
		    display_name_radios      = $$('input[name="C__CATG__NAGIOS_HOST_TPL_DEF_DISPLAY_NAME_SELECTION"]');

		if (check_command && check_command_plus)
		{
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
			if ($F(check_command) > 0)
			{
				check_command.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}

			if ($F(check_command_plus) > 0)
			{
				check_command_plus.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}
		}

		if (event_handler && event_handler_plus)
		{
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
			if ($F(event_handler) > 0)
			{
				event_handler.previous('.input-group-addong').down('input[type="radio"]').checked = true;
			}

			if ($F(event_handler_plus) > 0)
			{
				event_handler_plus.previous('.input-group-addong').down('input[type="radio"]').checked = true;
			}
		}

		if (check_period && check_period_plus)
		{
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
			if ($F(check_period) > 0)
			{
				check_period.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}

			if ($F(check_period_plus) > 0)
			{
				check_period_plus.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}
		}

		if (notification_period && notification_period_plus)
		{
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
			if ($F(notification_period) > 0)
			{
				notification_period.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}

			if ($F(notification_period_plus) > 0)
			{
				notification_period_plus.previous('.input-group-addon').down('input[type="radio"]').checked = true;
			}
		}

		if (display_name && display_name_radios.length == 3)
		{
			display_name.on('focus', function () {
				display_name_radios[0].checked = false;
				display_name_radios[1].checked = false;
				display_name_radios[2].checked = true;
			});
		}

		idoit.callbackManager
		     .registerCallback('nagios_tpl__check_command_description', function (el, value) {
			     if (Object.isUndefined(value))
			     {
				     value = $(el).getValue();
			     }

			     new Ajax.Request('?ajax=1&call=nagios&func=load_command_comment', {
				     parameters: {
					     command_id: value
				     },
				     method:     "post",
				     onComplete: function (response) {
					     new Tip($$('img[data-input-el="' + el + '"]')[0], response.responseJSON.data, {className: 'command-comment border'});
				     }.bind(el)
			     });
		     })
		     .triggerCallback('nagios_tpl__check_command_description', 'C__CATG__NAGIOS_HOST_TPL_DEF_CHECK_COMMAND', '[{$check_command_value}]')
		     .triggerCallback('nagios_tpl__check_command_description', 'C__CATG__NAGIOS_HOST_TPL_DEF_EVENT_HANDLER', '[{$event_handler_value}]');
	}());
</script>