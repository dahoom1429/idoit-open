<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__BACKUP_TITLE" ident="LC__CMDB__CATG__BACKUP__TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__BACKUP_TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">
            [{if $reverse == 1}]
               [{isys type="f_label" name="C__CATG__BACKUP__ASSIGNED_OBJECT__VIEW" ident="LC__CMDB__CATG__BACKUP__BACKUPS"}]
            [{else}]
               [{isys type="f_label" name="C__CATG__BACKUP__ASSIGNED_OBJECT__VIEW" ident="LC__CMDB__CATG__BACKUP__IS_BACKUPEP"}]
            [{/if}]
        </td>
        <td class="value">[{isys name="C__CATG__BACKUP__ASSIGNED_OBJECT" type="f_popup" p_strPopupType="browser_object_ng"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__BACKUP__TYPE" ident="LC__CMDB__CATG__BACKUP__BACKUP_TYPE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__BACKUP__TYPE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__BACKUP__CYCLE" ident="LC__CMDB__CATG__BACKUP__CYCLE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__BACKUP__CYCLE" p_strTable="isys_backup_cycle"}]</td>
    </tr>
    <tr id="field_path_to_save" class="opacity-30">
        <td class="key">[{isys type="f_label" name="C__CATG__BACKUP__PATH_TO_SAVE" ident="LC__CMDB__CATG__BACKUP__PATH_TO_SAVE"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__BACKUP__PATH_TO_SAVE"}]</td>
    </tr>
</table>

<script type="text/javascript">
	(function () {
		"use strict";

		idoit.callbackManager
			.registerCallback('backup__show_path_to_save', function (value) {
				if (value == "[{$smarty.const.C__CMDB__BACKUP_TYPE__FILE}]") {
					$('field_path_to_save').removeClassName('opacity-30').select('input').invoke('enable');
				} else {
					$('field_path_to_save').addClassName('opacity-30').select('input').invoke('disable');
				}
			})
			.triggerCallback('backup__show_path_to_save', '[{$backup_type}]');
	}());
</script>