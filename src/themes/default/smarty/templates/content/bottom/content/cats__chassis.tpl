<table class="contentTable" id="editview">
	<tr>
		<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__ASSIGNMENT"}]</td>
		<td>
			[{if $editmode == 1}]
				<div id="C__CMDB__CATS__CHASSIS__VIEW_FIELD" class="input input-small value ml20 fl" style="min-height:24px; height:auto;">
					[{$view_field_content}]
				</div>
				[{isys type="f_dialog" name="C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT" p_onChange="window.change_assignment('dialog');" p_strClass="input-small"}]
				[{isys type="f_popup" p_strPopupType="browser_object_ng" p_strClass="input-mini" name="C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES" p_strPlaceholder="LC__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__PLACEHOLDER" callback_accept="window.change_assignment('object', 'attach');" callback_detach="window.change_assignment('object', 'detach');"}]
				[{else}]
				<div id="C__CMDB__CATS__CHASSIS__VIEW_FIELD" class="input input-small value ml20 fl" style="min-height:24px; height:auto;">
					[{$view_field_content}]
				</div>
			[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS__ROLE" name="C__CMDB__CATS__CHASSIS__ROLE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CMDB__CATS__CHASSIS__ROLE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS" name="C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT__available_box"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CMDB__CATS__CHASSIS__SLOT_ASSIGNMENT" emptyMessage="LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS__EMPTY" placeholder="LC__CMDB__CATS__CHASSIS__ASSIGNED_SLOTS__PLACEHOLDER"}]</td>
	</tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		window.change_assignment = function change_assignment(p_type, p_direction) {
			var local_assignment = $('C__CMDB__CATS__CHASSIS__LOCAL_ASSIGNMENT'),
				assigned_devices__view = $('C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__VIEW'),
				assigned_devices__hidden = $('C__CMDB__CATS__CHASSIS__ASSIGNED_DEVICES__HIDDEN'),
				view_field = $('C__CMDB__CATS__CHASSIS__VIEW_FIELD'),

				l_striped_string,
				l_pos;

			switch (p_type) {
				case 'dialog':
					if (local_assignment.selectedIndex > 0) {
						view_field.update('[{isys type="lang" ident="LC__CMDB__CATG__VD__LOCAL_DEVICE" p_bHtmlEncode="0"}]: ' + local_assignment.select('option:selected')[0].text);
					} else {
						view_field.update('- [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS__NOTHING_ASSIGNED" p_bHtmlEncode="0"}] -');
					}

					assigned_devices__view.setValue('');
					assigned_devices__hidden.setValue('');
					break;

				case 'object':
					if (p_direction == 'attach') {
						local_assignment.selectedIndex = 0;
						l_pos = assigned_devices__view.value.indexOf('>>');

						if (l_pos > 0) {
							l_striped_string = assigned_devices__view.value.substring((l_pos + 3), assigned_devices__view.length);
						} else {
							l_striped_string = assigned_devices__view.value.substring(0, assigned_devices__view.value.indexOf('('));
						}

						view_field.update('[{isys type="lang" ident="LC__CMDB__CATS__CHASSIS__EXTERNAL_OBJECT" p_bHtmlEncode="0"}]: ' + l_striped_string);
					} else if (p_direction == 'detach' && local_assignment.selectedIndex == 0) {
						view_field.update('- [{isys type="lang" ident="LC__CMDB__CATS__CHASSIS__NOTHING_ASSIGNED" p_bHtmlEncode="0"}] -');
					}
					break;
			}
		};
	}());
</script>