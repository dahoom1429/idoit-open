<h3>[{isys type="lang" ident="LC__CMDB__CATG__RAID"}]</h3>
<table>
    <tr>
        <td>[{isys type="lang" ident="LC__WIDGET__CALCULATOR__RAID_LEVEl"}]</td>
        <td>[{isys type="f_dialog" name="[{$unique_id}]_calculator-raid-raidlevel" p_bDbFieldNN="1" p_arData=$rules.raid_lvls p_strClass="input-mini"}]</td>
    </tr>
    <tr>
        <td>[{isys type="lang" ident="LC__WIDGET__CALCULATOR__MEMORY_UNIT"}]</td>
        <td>[{isys type="f_dialog" name="[{$unique_id}]_calculator-raid-memory_unit" p_bDbFieldNN="1" p_arData=$rules.memory_unit p_strClass="input-mini" p_strSelectedID=$rules.memory_selected}]</td>
    </tr>
</table>
<div id="[{$unique_id}]_calculator-raid-content">
    <button id="[{$unique_id}]_calculator-add-raid" type="button" class="btn mt5">
        <img src="[{$dir_images}]icons/silk/add.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span>
    </button>

    <table id="[{$unique_id}]_calculator-raid-content-fields" class="m5">
        <tbody>
        <tr>
            <td colspan="3" class="pb5" id="[{$unique_id}]_calculator-raid-content-result-content" style="display:none;">
                [{isys type="f_text"  name="[{$unique_id}]_calculator-raid-content-result" p_bReadonly="1" p_bEditMode="1" p_strClass="input input-mini" p_bInfoIconSpacer="0" p_strPlaceholder="0 GB"}]

                <button id="[{$unique_id}]_calculator-raid-content-button" type="button" class="btn ml5">
                    <img src="[{$dir_images}]icons/silk/table_edit.png" class="mr5" /><span>[{isys type="lang" ident="Calculate"}]</span>
                </button>
            </td>
            <td>
                <div id="[{$unique_id}]_calculator-messages_too_small_value" class="ml5 p5 box-red" style="display:none;"></div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<style>
    #[{$unique_id}]_calculator-raid-content-fields input {
        text-align: right;
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        var $content_container = $('[{$unique_id}]_calculator-raid-content'),
            $raid_level_select = $('[{$unique_id}]_calculator-raid-raidlevel'),
            $result            = $('[{$unique_id}]_calculator-raid-content-result'),
            $memory_unit       = $('[{$unique_id}]_calculator-raid-memory_unit'),
            $table             = $('[{$unique_id}]_calculator-raid-content-fields').down('tbody'),
            raid_min_disks     = {
                'C__STOR_RAID_LEVEL__0':    2,
                'C__STOR_RAID_LEVEL__1':    2,
                'C__STOR_RAID_LEVEL__5':    3,
                'C__STOR_RAID_LEVEL__6':    4,
                'C__STOR_RAID_LEVEL__10':   4,
                'C__STOR_RAID_LEVEL__JBOD': 2
            };
        
        idoit.Require.require('smartyRaid', function () {
            $memory_unit.on('change', function () {
                $table.select('.raidfield').invoke('down', 'td:last span').invoke('update', $memory_unit.down('option:selected').innerHTML)
            });

            $('[{$unique_id}]_calculator-raid-content-button').on('click', function () {
                var smallest_hd   = 0,
                    l_val         = 0,
                    raid_capacity = 0,
                    disk_amount   = 0,
                    min_disks;

                if ($raid_level_select.getValue() == -1) {
                    $raid_level_select.addClassName('box-red');
                } else if ($raid_level_select.getValue() == 'C__STOR_RAID_LEVEL__JBOD') {
                    $content_container.select('.raidfield input').each(function (ele) {
                        raid_capacity += parseInt(ele.value);
                    });

                    $result.setValue(raid_capacity + ' ' + $memory_unit.down('option:selected').innerHTML);
                } else {
                    $raid_level_select.removeClassName('box-red');

                    $content_container.select('.raidfield input').each(function ($el) {
                        l_val = parseInt($el.getValue());

                        if (smallest_hd == 0 || smallest_hd > l_val) {
                            smallest_hd = l_val;
                        }
                    });

                    switch ($memory_unit.getValue()) {
                        case 'C__MEMORY_UNIT__MB':
                            smallest_hd = parseFloat(smallest_hd / 1000);
                            break;
                        case 'C__MEMORY_UNIT__TB':
                            smallest_hd = parseFloat(smallest_hd * 1000);
                            break;
                        case 'C__MEMORY_UNIT__B':
                            smallest_hd = parseFloat(smallest_hd / Math.pow(1000, 3));
                            break;
                        case 'C__MEMORY_UNIT__KB':
                            smallest_hd = parseFloat(smallest_hd / Math.pow(1000, 2));
                            break;
                        default:
                            break;
                    }

                    min_disks = raid_min_disks[$raid_level_select.getValue()];

                    if ($content_container.select('.raidfield input').length > 0) {
                        $content_container.select('.raidfield input').each(function ($el) {
                            if ($el.getValue() > 0) {
                                disk_amount++;
                            }
                        });
                    }

                    if (disk_amount < min_disks) {
                        $('[{$unique_id}]_calculator-messages')
                            .update('[{isys type="lang" ident="LC__WIDGET__CALCULATOR__RAID_CAPACITY_CALCULATOR__MINIMUM_HARDDISKS"}] ' + min_disks);

                        Effect.Appear('[{$unique_id}]_calculator-messages', {
                            duration:    0.25,
                            afterFinish: function () {
                                setTimeout(function () {
                                    Effect.Fade('[{$unique_id}]_calculator-messages', {duration: 0.25});
                                }, 2500)
                            }
                        });
                        $result.setValue('');
                    } else {
                        raid_capacity = raidcalc(disk_amount, smallest_hd, $raid_level_select.down('option:selected').text, '');

                        if (raid_capacity < 0.0000001 || raid_capacity == undefined) {
                            $('[{$unique_id}]_calculator-messages_too_small_value')
                                .update('[{isys type="lang" ident="LC__WIDGET__CALCULATOR__RAID_CAPACITY_CALCULATOR__MINIMUM_VALUE"}] ');

                            Effect.Appear('[{$unique_id}]_calculator-messages_too_small_value', {
                                duration:    0.25,
                                afterFinish: function () {
                                    setTimeout(function () {
                                        Effect.Fade('[{$unique_id}]_calculator-messages_too_small_value', {duration: 0.25});
                                    }, 2500)
                                }
                            });
                            $result.setValue('');
                        } else {
                            $result.setValue(raid_capacity + ' GB');
                        }
                    }
                }
            });

            $('[{$unique_id}]_calculator-add-raid').on('click', function () {
                var counter = $content_container.select('.raidfield a').length + 1;

                $('[{$unique_id}]_calculator-raid-content-result-content').show();

                var $tr = new Element('tr', {className: 'raidfield'})
                    .insert(new Element('td').update(new Element('a', {className: 'btn btn-small'}).insert(new Element('img', {src: '[{$dir_images}]icons/silk/cross.png'}))))
                    .insert(new Element('td').update(new Element('span', {className: 'vam ml5 mr5'}).update('[{isys type="lang" ident="LC__STORAGE_TYPE__HARD_DISK"}] ' + counter)))
                    .insert(new Element('td').update(new Element('input', {type: 'text', className: 'input input-mini', placeholder: '0'})))
                    .insert(new Element('td').update(new Element('span', {className: 'vam ml5'}).update($memory_unit.down('option:selected').innerHTML)));

                $table.insert($tr);
            });

            $content_container.on('click', 'a', function (ev) {
                ev.findElement('a').up('tr').remove();

                if ($content_container.select('.raidfield a').length == 0) {
                    $('[{$unique_id}]_calculator-raid-content-result').setValue('');
                    $('[{$unique_id}]_calculator-raid-content-result-content').hide();
                } else {
                    $content_container.select('.raidfield').invoke('down', 'td', 1).each(function ($td, i) {
                        $td.down().update('[{isys type="lang" ident="LC__STORAGE_TYPE__HARD_DISK"}] ' + (i + 1));
                    })
                }
            });
        });
    })();
</script>
