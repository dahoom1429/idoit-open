<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__SLA_SERVICE_ID" name="C__CATG__SLA__SERVICE_ID"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__SLA__SERVICE_ID"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__SLA__SERVICE_LEVEL" ident="LC__CMDB__CATG__SLA_SERVICELEVEL"}]</td>
		<td class="value">
			[{isys type="f_dialog" name="C__CATG__SLA__SERVICE_LEVEL"}]
			[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__SLA__SERVICE_LEVEL_DIALOG"}]
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" ident="LC__CMDB__CATG__SLA_SERVICELEVEL_DESCRIPTION"}]
		</td>
		<td>
			<div id="service-level-description" class="ml20">[{$servicelevel_description}]</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr class="mt5 mb5"/>
		</td>
	</tr>
	<tr>
		<td class="key">
			[{isys type="f_label" name="C__CATG__SLA__MONITORING" ident="LC__CMDB__CATS__SLA_SCHEMA__SERVICETIMEFRAME"}]
		</td>
		<td>
			<table class="pl20">
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__MONDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM"}]
						[{if isys_glob_is_edit_mode()}]
							<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
							<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__MONDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__TUESDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_FROM"}]
												[{if isys_glob_is_edit_mode()}]
						<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
						<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__WEDNESDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_FROM"}]
												[{if isys_glob_is_edit_mode()}]
						<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
						<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__THURSDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_FROM"}]
						[{if isys_glob_is_edit_mode()}]
							<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
							<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__FRIDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_FROM"}]
						[{if isys_glob_is_edit_mode()}]
							<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
							<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__SATURDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_FROM"}]
						[{if isys_glob_is_edit_mode()}]
							<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
							<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_TO"}]
					</td>
				</tr>
				<tr>
					<td>[{isys type="checkbox" name="C__CATG__SLA__WEEK_DAY__SUNDAY"}]</td>
					<td>
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_FROM"}]
						[{if isys_glob_is_edit_mode()}]
							<div class="input-group" style="width: 30px;"><strong class="input-group-addon input-group-addon-unstyled">&raquo;</strong></div>
						[{else}]
							<strong class="ml20">&raquo;</strong>
						[{/if}]
						[{isys type="f_text" name="C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_TO"}]
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__SLA_REACTIONTIME" name="C__CATG__SLA__REACTION_INTERVAL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__SLA__REACTION_INTERVAL"}][{isys type="f_dialog" name="C__CATG__SLA__REACTION_INTERVAL_UNIT" p_strTable="isys_unit_of_time"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__SLA_RECOVERYTIME" name="C__CATG__SLA__RECOVERY_INTERVAL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__SLA__RECOVERY_INTERVAL"}][{isys type="f_dialog" name="C__CATG__SLA__RECOVERY_INTERVAL_UNIT" p_strTable="isys_unit_of_time"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__SLA_CALENDAR" name="C__CMDB__CATS__SLA_CALENDAR"}]</td>
		<td class="value">[{isys name="C__CMDB__CATS__SLA_CALENDAR" type="f_popup"}]</td>
	</tr>
</table>

<style type="text/css">
	#service-level-description span,
	#service-level-description img {
		vertical-align: middle;
		margin-right: 5px;
	}
</style>

<script>
	(function () {
		"use strict";

		var $service_level = $('C__CATG__SLA__SERVICE_LEVEL_DIALOG'),
			$service_level_description = $('service-level-description');

		if ($service_level && $service_level_description) {
			$service_level.on('change', function () {
				$service_level_description.removeClassName('box-red')
					.update(new Element('img', {src:'[{$dir_images}]ajax-loading.gif'}))
					.insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]'));

				new Ajax.Request('?ajax=1&call=sla&func=get-service-level-description', {
					parameters: {
						service_level: $service_level.getValue()
					},
					method: "post",
					onComplete: function (response) {
						var json = response.responseJSON;

						if (json.success) {
							$service_level_description.update(json.data.isys_sla_service_level__description || '[{$servicelevel_description_empty}]');
						} else {
							$service_level_description.addClassName('box-red').update(json.message);
						}
					}
				});
			});
		}

		var check_accessability = function () {
			$('scroller').select('.week_day').each(function ($checkbox) {
				$checkbox.up('tr').select('input[type="text"]').invoke($checkbox.checked ? 'enable' : 'disable');
			});
		};

		if ($('C__CATG__SLA__WEEK_DAY__MONDAY')) {
			check_accessability();
			$('scroller').select('.week_day').invoke('on', 'change', check_accessability);
		}
	}());
</script>