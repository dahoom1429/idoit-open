<input type="hidden" name="raid_id" value="[{$raid_id}]"/>
<input type="hidden" id="raid_type" name="raid_type" value="[{$raid_type}]"/>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CMDB__RAID_TYPE' ident="LC__CMDB__RAID_TYPE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CMDB__RAID_TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RAID_TITLE' ident="LC__CATG__STORAGE_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__RAID_TITLE"}]</td>
	</tr>

	<!-- START HARDWARE RAID -->
	<tr>
		<td class="hardraid" colspan="2"><hr class="partingLine"/></td>
	</tr>
	<tr class="hardraid">
		<td class="key">[{isys type='f_label' name='C__CATG__RAID_CONTROLLER' ident="LC__CATG__STORAGE_CONTROLLER"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__RAID_CONTROLLER"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr class="partingLine"/></td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RAID_LEVEL' ident="LC__CATG__STORAGE_RAIDLEVEL"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__RAID_LEVEL" id="C__CATG__RAID_LEVEL" }]</td>
	</tr>
	<tr>
		<td class="hardraid key">[{isys type='f_label' name='C__CATG__RAID_CONNECTION' ident="LC__CMDB__CATG__STORAGE__CONNECTED_DEVICES"}]</td>
		<td class="hardraid value">[{isys type="f_dialog_list" name="C__CATG__RAID_CONNECTION" emptyMessage="LC__CMDB__CATG__RAID__EMPTY_MESSAGE__DEVICES"}]</td>
	</tr>
	<!-- ENDE HARDWARE RAID -->

	<!-- START SOFWARE RAID-->
	<tr class="softraid">
		<td colspan="2"><hr class="partingLine"/></td>
	</tr>
	<tr>
		<td class="softraid key">[{isys type='f_label' name='C__CATG__RAID_DRIVE_CONNECTION' ident="LC__CATG__DRIVE_CONNECTION"}]</td>
		<td class="softraid value">[{isys type="f_dialog_list" name="C__CATG__RAID_DRIVE_CONNECTION" emptyMessage="LC__CMDB__CATG__RAID__EMPTY_MESSAGE__DRIVES"}]</td>
	</tr>
	<tr class="softraid">
		<td colspan="2"><hr class="partingLine"/></td>
	</tr>
	<!-- ENDE SOFWARE RAID-->

	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RAID_TOTALCAPACITY' ident="LC__CMDB__CATG__RAID_CAPACITY"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__RAID_TOTALCAPACITY" id="C__CATG__RAID_TOTALCAPACITY"}]</td>
	</tr>
	<tr>
		<td class="hardraid key">[{isys type="lang" ident="LC__CATG__CMDB_MEMORY_TOTALCAPACITY"}] ([{isys type="lang" ident="LC__CMDB__CATG__RAID__ALL_DEVICES"}])</td>
		<td class="hardraid value">[{isys type="f_data" name="C__CATG__RAID_TOTALCAPACITY_REAL" id="C__CATG__RAID_TOTALCAPACITY_REAL"}]</td>
	</tr>
	<tr>
		<td class="softraid key">[{isys type="lang" ident="LC__CATG__CMDB_MEMORY_TOTALCAPACITY"}] ([{isys type="lang" ident="LC__CMDB__CATG__RAID__ALL_DRIVES"}])</td>
		<td class="softraid value">[{isys type="f_data" name="C__CATG__RAID_TOTALCAPACITY_REAL" id="C__CATG__RAID_TOTALCAPACITY_REAL"}]</td>
	</tr>
</table>

<script language="JavaScript" type="text/javascript">
    (function () {
        'use strict';

        var $container = $('scroller'),
            $raid_type = $('C__CMDB__RAID_TYPE');

        if ($raid_type) {
            $raid_type.on('change', function () {
                $container.select('.hardraid,.softraid').invoke('hide');
                if ($raid_type.getValue() == 1) {
                    // Display "hardware raid".
                    $container.select('.hardraid').invoke('show');
                } else if ($raid_type.getValue() == 2) {
                    // Display "software raid".
                    $container.select('.softraid').invoke('show');
                }
            });

            $raid_type.simulate('change');
        } else {
            $container.select('.hardraid,.softraid').invoke('hide');
            if ($('raid_type').value == 1) {
                $container.select('.hardraid').invoke('show');
            } else {
                $container.select('.softraid').invoke('show');
            }
        }

        [{if $calculate_raid}]
        idoit.Require.require('smartyRaid', function () {
            raidcalc('[{$raid.numdisks}]', '[{$raid.each}]', '[{$raid.level}]', 'C__CATG__RAID_TOTALCAPACITY', 'C__CATG__RAID_TOTALCAPACITY_REAL');
        });
        [{/if}]
    })();
</script>