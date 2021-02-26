<input type="hidden" name="stor_id" value="[{$stor_id}]" />

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_TYPE" ident="LC__CATG__STORAGE_TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_TYPE" p_onChange="idoit.callbackManager.triggerCallback('stor__change_type', this.value);"}]</td>
	</tr>

	[{if $new_catg_stor == "1"}]
	<tr>
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	[{isys type='f_title_suffix_counter' name='C__CATG__STORAGE__SUFFIX' title_identifier='C__CATG__STORAGE_TITLE' label_counter='LC__CMDB__CATG__STORAGE__NUMBER_NEW'}]
	[{/if}]

	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_TITLE" ident="LC__CATG__STORAGE_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_TITLE"}]</td>
	</tr>
	<tr class="type-not-san">
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_MANUFACTURER" ident="LC__CATG__STORAGE_MANUFACTURER"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_MANUFACTURER"}]</td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_MODEL" ident="LC__CATG__STORAGE_MODEL"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_MODEL"}]</td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CAPACITY" ident="LC__CATG__STORAGE_CAPACITY"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_CAPACITY"}] [{isys type="f_dialog" name="C__CATG__STORAGE_UNIT"}]</td>
	</tr>
	<tr class="type-hd">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_HOTSPARE" ident="LC__CATG__STORAGE_HOTSPARE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_HOTSPARE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr class="type-not-san">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CONNECTION_TYPE" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_CONNECTION_TYPE"}]</td>
	</tr>
	<tr class="type-hd">
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CONTROLLER" ident="LC__CATG__STORAGE_CONTROLLER"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_CONTROLLER"}]</td>
	</tr>
	<tr class="type-hd">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_RAIDGROUP" ident="LC__CATG__RAIDGROUP"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_RAIDGROUP"}]</td>
	</tr>
	<tr class="type-basics">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_SERIAL' ident="LC__CATG__STORAGE_SERIAL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_SERIAL"}]</td>
	</tr>
	<tr class="type-raid">
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAIDLEVEL' ident="LC__CATG__STORAGE_RAIDLEVEL"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_RAIDLEVEL" id="C__CATG__STORAGE_RAIDLEVEL"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_CONNECTION' ident="LC__CATG__STORAGE_CONNECTION"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__STORAGE_CONNECTION"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAID_TOTALCAPACITY' ident="LC__CATG__CMDB_MEMORY_TOTALCAPACITY"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__STORAGE_RAID_TOTALCAPACITY" id="C__CATG__STORAGE_RAID_TOTALCAPACITY"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL' ident="LC__CATG__CMDB__MEMORY__USABLE_TOTALCAPACITY"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL" id="C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL"}]</td>
	</tr>
	<tr class="type-san">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_SANPOOL' ident="LC__CATG__STORAGE_SANPOOL"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_SANPOOL"}]</td>
	</tr>
	<tr class="type-streamer">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_FC_ADDRESS' ident="LC__CATG__STORAGE_FC_ADDRESS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_FC_ADDRESS"}]</td>
	</tr>
	<tr class="type-tape type-streamer">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_LTO_TYPE" ident="LC__CATG__STORAGE_LTO_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_LTO_TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_FIRMWARE' ident="LC__CATG__STORAGE_FIRMWARE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_FIRMWARE"}]</td>
	</tr>
</table>

<script type="text/javascript">
    (function () {
        'strict mode';

        /**
         * Change the device-type and show different fields.
         *
         * @param   type
         * @author  Leonard Fischer <lfischer@i-doit.com>
         */
        idoit.callbackManager
            .registerCallback('stor__change_type', function (type) {
                $$('.type-not-san,.type-hd,.type-raid,.type-san,.type-streamer,.type-basics,.type-tape').invoke('hide');

                if (type == '[{$smarty.const.C__STOR_TYPE_DEVICE_HD}]' || type == '[{$smarty.const.C__STOR_TYPE_DEVICE_SSD}]' || type == '[{$smarty.const.C__STOR_TYPE_DEVICE_SD_CARD}]') {
                    $$('.type-hd,.type-not-san,.type-basics,.type-tape').invoke('show');
                } else if (type == '[{$smarty.const.C__STOR_TYPE_DEVICE_TAPE}]') {
                    $$('.type-san,.type-tape').invoke('show');
                } else if (type == '[{$smarty.const.C__STOR_TYPE_DEVICE_STREAMER}]') {
                    $$('.type-streamer,.type-basics').invoke('show');
                } else {
                    $$('.type-basics,.type-not-san').invoke('show');
                }
            })
            .triggerCallback('stor__change_type', $$('input[name="SM2__C__CATG__STORAGE_TYPE[p_strSelectedID]"]')[0].getValue());

        idoit.Require.require('smartyRaid', function () {
            raidcalc('[{$raid.numdisks}]', '[{$raid.each}]', '[{$raid.level}]', 'C__CATG__STORAGE_RAID_TOTALCAPACITY', 'C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL');
        });
    })();
</script>
