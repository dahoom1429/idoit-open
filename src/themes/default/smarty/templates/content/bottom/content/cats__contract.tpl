<style type="text/css">
    .x30 {
        width: 12.65% !important;
    }
</style>

<script type="text/javascript">
(function () {
    'use strict';

    var current_date_format          = '[{$current_date_format}]',
        current_date_format_splitter = '[{$current_date_format_splitter}]',
        day_index                    = parseInt(current_date_format.indexOf('d')),
        month_index                  = parseInt(current_date_format.indexOf('m')),
        year_index                   = parseInt(current_date_format.indexOf('Y'));

    window.calculate_runtime = function () {
        if (!$F('C__CATS__CONTRACT__START_DATE__VIEW').blank() && !$F('C__CATS__CONTRACT__END_DATE__VIEW').blank()) {
            var js_day = 1000 * 60 * 60 * 24;

            // Start date
            var start_date  = $F('C__CATS__CONTRACT__START_DATE__VIEW').split(current_date_format_splitter),
                start_year  = start_date[year_index],
                start_month = start_date[month_index],
                start_day   = start_date[day_index];

            if (start_year == undefined || start_month == undefined || start_day == undefined) {
                if (!$F('C__CATS__CONTRACT__START_DATE__HIDDEN').blank()) {
                    start_date = $F('C__CATS__CONTRACT__START_DATE__HIDDEN').split('-');
                    start_year = start_date[0];
                    start_month = start_date[1];
                    start_day = start_date[2];
                }
            }

            var start_obj  = new Date(start_year, start_month, start_day),
                start_time = start_obj.getTime();

            // End date
            var end_date  = $F('C__CATS__CONTRACT__END_DATE__VIEW').split(current_date_format_splitter),
                end_year  = end_date[year_index],
                end_month = end_date[month_index],
                end_day   = end_date[day_index];

            if (end_year == undefined || end_month == undefined || end_day == undefined) {
                if (!$F('C__CATS__CONTRACT__END_DATE__HIDDEN').blank())
                {
                    end_date = $F('C__CATS__CONTRACT__END_DATE__HIDDEN').split('-');
                    end_year = end_date[0];
                    end_month = end_date[1];
                    end_day = end_date[2];
                }
            }

            var end_obj       = new Date(end_year, end_month, end_day),
                end_time      = end_obj.getTime(),

                day_diff      = Math.ceil((end_time - start_time) / js_day),

                run_time_unit = '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_DAYS}]';

            if (day_diff < 0) {
                $('C__CATS__CONTRACT__RUNTIME').setValue(0);
                return;
            }

            if (start_day == end_day && start_month != end_month && (start_year == end_year || ((end_year - start_year) > 0))) {
                if (end_year == start_year) {
                    $('C__CATS__CONTRACT__RUNTIME').setValue(end_month - start_month);
                } else {
                    $('C__CATS__CONTRACT__RUNTIME').setValue((parseInt(end_month)) - start_month + (((end_year - start_year)) * 12));
                }
                run_time_unit = '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_MONTH}]';
            } else if (start_day == end_day && start_month == end_month && start_year != end_year) {
                $('C__CATS__CONTRACT__RUNTIME').setValue(end_year - start_year);
                run_time_unit = '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_YEARS}]';
            } else {
                if ((day_diff % 7) == 0) {
                    $('C__CATS__CONTRACT__RUNTIME').setValue(day_diff / 7);
                    run_time_unit = '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_WEEKS}]';
                } else {
                    $('C__CATS__CONTRACT__RUNTIME').setValue(day_diff);
                }
            }

            $('C__CATS__CONTRACT__RUNTIME_PERIOD_UNIT').setValue(run_time_unit);
        } else {
            $('C__CATS__CONTRACT__RUNTIME').setValue('');
        }
    };

    window.calculate_end_date = function () {
        var runtime_unit = $F('C__CATS__CONTRACT__RUNTIME_PERIOD_UNIT'),
            runtime_val  = parseFloat($F('C__CATS__CONTRACT__RUNTIME')),
            dateformat   = '[{$date_format}]',
            add_month    = 1;

        if (runtime_val > 0) {
            var start_date  = $F('C__CATS__CONTRACT__START_DATE__VIEW').split(current_date_format_splitter),
                start_year  = start_date[year_index],
                start_month = start_date[month_index],
                start_day   = start_date[day_index];

            if (start_year == undefined || start_month == undefined || start_day == undefined) {
                if (!$F('C__CATS__CONTRACT__START_DATE__HIDDEN').blank()) {
                    start_date = $F('C__CATS__CONTRACT__START_DATE__HIDDEN').split('-');
                    start_year = start_date[0];
                    start_month = start_date[1];
                    start_day = start_date[2];
                }
            }

            var date = new Date(start_year + '-' + start_month + '-' + start_day);

            switch (runtime_unit) {
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_DAYS}]':
                    date.setDate(parseInt(date.getDate()) + runtime_val);
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_WEEKS}]':
                    date.setDate((parseInt(date.getDate()) + (runtime_val * 7)));
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_MONTH}]':
                    date.setMonth((parseFloat(date.getMonth()) + (runtime_val)));
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_YEARS}]':
                    date.setFullYear((parseInt(date.getFullYear()) + runtime_val));
                    break;
            }

            var year  = date.getFullYear(),
                month = (parseInt(date.getMonth()) >= 9) ? (parseInt(date.getMonth()) + add_month) : '0' + (parseInt(date.getMonth()) + add_month),
                day   = (parseInt(date.getDate()) > 9) ? date.getDate() : '0' + date.getDate();

            var new_date = dateformat.replace(/d|D/g, day).replace(/m|M/g, month).replace(/y|Y/g, year);

            $('C__CATS__CONTRACT__END_DATE__HIDDEN').setValue(year + '-' + month + '-' + day);
            $('C__CATS__CONTRACT__END_DATE__VIEW').setValue(new_date);
        }
    };

    window.calculate_next_end_date = function (contract_end_date, expiration_date, expiration_period, expiration_period_unit, expiration_period_type) {

        var dateformat     = '[{$date_format}]',
            notice_type,
            notice_value,
            notice_unit,
            rem_value,
            notice_date_hidden,
            notice_date,
            contract_end_date_hidden,
            $result_input  = $('C__CATS__CONTRACT__CONTRACT_END'),
            $result_input2 = $('C__CATS__CONTRACT__NOTICE_END_DATE'),
            tmp_date;

        if ((contract_end_date != undefined || expiration_date != undefined) && expiration_period != undefined && expiration_period_unit != undefined && expiration_period_type != undefined) {
            notice_type = expiration_period_type;
            notice_value = parseInt(expiration_period);
            notice_unit = expiration_period_unit;
            rem_value = '';
            notice_date_hidden = expiration_date;
            contract_end_date_hidden = contract_end_date;
        } else if ($('C__CATS__CONTRACT__NOTICE_PERIOD_TYPE') && $('C__CATS__CONTRACT__NOTICE_VALUE') && $('C__CATS__CONTRACT__NOTICE_UNIT')) {
            notice_type = $F('C__CATS__CONTRACT__NOTICE_PERIOD_TYPE');
            notice_value = parseInt($F('C__CATS__CONTRACT__NOTICE_VALUE'));
            notice_unit = $F('C__CATS__CONTRACT__NOTICE_UNIT');
            rem_value = '';

            // Notice date
            notice_date = $F('C__CATS__CONTRACT__NOTICE_DATE__VIEW').split(current_date_format_splitter);
            var notice_year  = notice_date[year_index],
                notice_month = notice_date[month_index] - 1,
                notice_day   = notice_date[day_index];

            if (notice_year == undefined || notice_month == undefined || notice_day == undefined) {
                if (!$F('C__CATS__CONTRACT__NOTICE_DATE__HIDDEN').blank()) {
                    notice_date = $F('C__CATS__CONTRACT__NOTICE_DATE__HIDDEN').split('-');
                    notice_year = notice_date[0];
                    notice_month = notice_date[1] - 1;
                    notice_day = notice_date[2];
                }
            }

            notice_date_hidden = notice_year + '-' + notice_month + '-' + notice_day;

            // End date
            var end_date  = $F('C__CATS__CONTRACT__END_DATE__VIEW').split(current_date_format_splitter),
                end_year  = end_date[year_index],
                end_month = end_date[month_index] - 1,
                end_day   = end_date[day_index];

            if (end_year == undefined || end_month == undefined || end_day == undefined) {
                if (!$F('C__CATS__CONTRACT__END_DATE__HIDDEN').blank()) {
                    end_date = $F('C__CATS__CONTRACT__END_DATE__HIDDEN').split('-');
                    end_year = end_date[0];
                    end_month = end_date[1] - 1;
                    end_day = end_date[2];
                }
            }

            contract_end_date_hidden = end_year + '-' + end_month + '-' + end_day;
        }

        if (notice_type > 0 && notice_unit > 0 && notice_value > 0) {
            switch (notice_type) {
                case '[{$smarty.const.C__CONTRACT__FROM_NOTICE_DATE}]':
                    rem_value = '[{isys type="lang" ident="LC__UNIVERSAL__ANYTIME"}]';
                    var op = '+';

                    if (notice_date_hidden != '' &&
                        notice_date_hidden != '1970-0-01' &&
                        notice_date_hidden != 'undefined-undefined-undefined') {
                        notice_date = notice_date_hidden.split('-');

                        var date = new Date(notice_date[0], notice_date[1], notice_date[2]);
                        tmp_date = new Date(notice_date[0], notice_date[1], 1);
                    } else {
                        $result_input2.update('-');
                        $result_input.update('-'); // See ID-711 We don't want to display this value in edit mode.
                        return;
                    }

                    break;
                case '[{$smarty.const.C__CONTRACT__ON_CONTRACT_END}]':
                    $result_input = $('C__CATS__CONTRACT__NOTICE_END_DATE');
                    $result_input2 = $('C__CATS__CONTRACT__CONTRACT_END');

                    var op = '-';

                    if (contract_end_date_hidden != '' &&
                        contract_end_date_hidden != '1970-0-01' &&
                        contract_end_date_hidden != 'undefined-undefined-undefined') {

                        var contract_end = contract_end_date_hidden.split('-');
                        var date = new Date(contract_end[0], contract_end[1], contract_end[2]);
                        tmp_date = new Date(contract_end[0], contract_end[1], 1);
                        var rem_value_arr = contract_end_date_hidden.split('-');
                        rem_value_arr[1] = (parseInt(rem_value_arr[1]) + 1);
                        rem_value = dateformat.replace(/d|D/g, rem_value_arr[2])
                            .replace(/m|M/g, ((rem_value_arr[1] > 9) ? rem_value_arr[1] : '0' + rem_value_arr[1]))
                            .replace(/y|Y/g, rem_value_arr[0]);
                    } else {
                        rem_value = '[{isys type="lang" ident="LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED"}]';

                        $result_input2.update(rem_value);
                        $result_input.update(rem_value);
                        return;
                    }
                    break;
            }

            switch (notice_unit) {
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_DAYS}]':
                    if (op == '+') {
                        date.setDate(parseInt(date.getDate()) + notice_value);
                    } else {
                        date.setDate(parseInt(date.getDate()) - notice_value);
                    }
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_WEEKS}]':
                    if (op == '+') {
                        date.setDate((parseInt(date.getDate()) + (notice_value * 7)));
                    } else {
                        date.setDate((parseInt(date.getDate()) - (notice_value * 7)));
                    }
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_MONTH}]':
                    if (op == '+') {
                        var addition = (parseInt(date.getMonth()) + notice_value);

                        if (addition > 11) {
                            // Numerical months begin by 0 = january, 1 = february, ...
                            addition = 0;
                        }
                        date.setMonth(addition);
                        tmp_date.setMonth(addition);

                        if (date.getMonth() != tmp_date.getMonth()) {
                            date.setDate(parseInt(tmp_date.get_days_in_month()));
                            date.setMonth(parseInt(tmp_date.getMonth()));
                        }

                    } else {
                        date.setMonth((parseInt(date.getMonth()) - notice_value));
                        tmp_date.setMonth((parseInt(tmp_date.getMonth()) - notice_value));

                        if (date.getMonth() != tmp_date.getMonth()) {
                            date.setDate(parseInt(tmp_date.get_days_in_month()));
                            date.setMonth((parseInt(date.getMonth()) - 1));
                        }
                    }
                    break;
                case '[{$smarty.const.C__GUARANTEE_PERIOD_UNIT_YEARS}]':
                    if (op == '+') {
                        date.setFullYear((parseInt(date.getFullYear()) + notice_value));
                    } else {
                        date.setFullYear((parseInt(date.getFullYear()) - notice_value));
                    }
                    break;
            }

            var date_month = parseInt(date.getMonth()) + 1,
                date_day   = parseInt(date.getDate()),
                date_year  = parseInt(date.getFullYear()),
                day        = (date_day > 9) ? date_day : '0' + date.getDate(),
                month      = (date_month > 9) ? date_month : '0' + date_month,
                year       = date_year,
                new_date   = dateformat.replace(/d|D/g, day).replace(/m|M/g, month).replace(/y|Y/g, year);

            if (!date_month || !date_day || !date_year) {
                new_date = '[{isys type="lang" ident="LC__CMDB__CATS__CONTRACT__CONTRACT_EXPIRATION_DATE_IS_NOT_DEFINED"}]';
                rem_value = new_date;
            }
            $result_input.update(new_date);
            $result_input2.update(rem_value);
        } else {
            $result_input.update('-');
            $result_input2.update('-');
        }
    };

    window.date_callback = function () {
        window.calculate_runtime();
        window.calculate_next_end_date();
    };

    window.date_callback_runtime = function () {
        window.calculate_end_date();
        window.calculate_next_end_date();

        if (!parseInt($F('C__CATS__CONTRACT__RUNTIME')) || !parseInt($F('C__CATS__CONTRACT__RUNTIME_PERIOD_UNIT'))) {
            $('C__CATS__CONTRACT__RUNTIME').setValue('');
            $('C__CATS__CONTRACT__END_DATE__VIEW').setValue('');
            $('C__CATS__CONTRACT__END_DATE__HIDDEN').setValue('');
        }
    };
}());
</script>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__TYPE" name="C__CATS__CONTRACT__TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_type" name="C__CATS__CONTRACT__TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__CONTRACT_NO" name="C__CATS__CONTRACT__CONTRACT_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CONTRACT__CONTRACT_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__CUSTOMER_NO" name="C__CATS__CONTRACT__CUSTOMER_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CONTRACT__CUSTOMER_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__INTERNAL_NO" name="C__CATS__CONTRACT__INTERNAL_NO"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CONTRACT__INTERNAL_NO"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__COSTS" name="C__CATS__CONTRACT__COSTS"}]</td>
		<td class="value">[{isys type="f_money_number" name="C__CATS__CONTRACT__COSTS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__COST_CALCULATION" name="C__CATS__CONTRACT__COST_CALCULATION"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATS__CONTRACT__COST_CALCULATION"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__PAYMENT_PERIOD" name="C__CATS__CONTRACT__PAYMENT_PERIOD"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATS__CONTRACT__PAYMENT_PERIOD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__PRODUCT" name="C__CATS__CONTRACT__PRODUCT"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATS__CONTRACT__PRODUCT"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__REACTION_RATE" name="C__CATS__CONTRACT__REACTION_RATE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_reaction_rate" name="C__CATS__CONTRACT__REACTION_RATE"}]</td>
	</tr>
	<tr>
		<td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__STATUS" name="C__CATS__CONTRACT__CONTRACT_STATUS"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATS__CONTRACT__CONTRACT_STATUS"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__START_DATE" name="C__CATS__CONTRACT__START_DATE" description=$description_date_format}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="calendar" enableCloseOnBlur=1 name="C__CATS__CONTRACT__START_DATE" p_onChange="window.calculate_runtime();" cellCallback="window.calculate_runtime"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__END_DATE" name="C__CATS__CONTRACT__END_DATE" description=$description_date_format}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="calendar" enableCloseOnBlur=1 p_onChange="window.calculate_runtime();" cellCallback="window.date_callback" name="C__CATS__CONTRACT__END_DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__LEASING__RUNTIME" name="C__CATS__CONTRACT__RUNTIME"}]</td>
		<td class="value">
            [{isys type="f_text" name="C__CATS__CONTRACT__RUNTIME"}]
            [{isys type="f_dialog" name="C__CATS__CONTRACT__RUNTIME_PERIOD_UNIT"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__CONTRACT_END" name="C__CATS__CONTRACT__CONTRACT_END"}]</td>
		<td class="value">[{isys type="f_data" p_strValue=$contract_end id="C__CATS__CONTRACT__CONTRACT_END"}]</td>
	</tr>
	<tr>
        <td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__END_TYPE" name="C__CATS__CONTRACT__END_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_end_type" name="C__CATS__CONTRACT__END_TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__CONTRACT_NOTICE_END" name="C__CATS__CONTRACT__NOTICE_END_DATE"}]</td>
		<td class="value">[{isys type="f_data" id="C__CATS__CONTRACT__NOTICE_END_DATE"  p_strValue=$expiration_date}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__NOTICE_DATE" name="C__CATS__CONTRACT__NOTICE_DATE" description=$description_date_format}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="calendar" enableCloseOnBlur=1 cellCallback="window.calculate_next_end_date" name="C__CATS__CONTRACT__NOTICE_DATE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__NOTICE_VALUE" name="C__CATS__CONTRACT__NOTICE_VALUE"}]</td>
		<td class="value">
            [{isys type="f_text" name="C__CATS__CONTRACT__NOTICE_VALUE"}]
            [{isys type="f_dialog" name="C__CATS__CONTRACT__NOTICE_UNIT"}]
			<br class="cb" />
            [{isys type="f_dialog" name="C__CATS__CONTRACT__NOTICE_PERIOD_TYPE"}]
        </td>
	</tr>
	<tr>
        <td colspan="2"><hr class="mt5 mb5" /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD" name="C__CATS__CONTRACT__MAINTENANCE_PERIOD"}]</td>
		<td class="value">
            [{isys type="f_text" name="C__CATS__CONTRACT__MAINTENANCE_PERIOD"}]
            [{isys type="f_dialog" name="C__CATS__CONTRACT__MAINTENANCE_PERIOD_UNIT"}]
        </td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__MAINTENANCE_END" name="C__CATS__CONTRACT__MAINTENANCE_END"}]</td>
		<td class="value">[{isys type="f_data" p_strValue=$maintenance_end name="C__CATS__CONTRACT__MAINTENANCE_END"}]</td>
	</tr>
</table>