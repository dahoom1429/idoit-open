<?php

/**
 * i-doit
 * CMDB UI: SLA category.
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @since      1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_sla extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_sla $p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $l_catdata = $p_cat->get_general_data();

        $l_monday = isys_format_json::decode($l_catdata['isys_catg_sla_list__monday_time']);
        $l_tuesday = isys_format_json::decode($l_catdata['isys_catg_sla_list__tuesday_time']);
        $l_wednesday = isys_format_json::decode($l_catdata['isys_catg_sla_list__wednesday_time']);
        $l_thursday = isys_format_json::decode($l_catdata['isys_catg_sla_list__thursday_time']);
        $l_friday = isys_format_json::decode($l_catdata['isys_catg_sla_list__friday_time']);
        $l_saturday = isys_format_json::decode($l_catdata['isys_catg_sla_list__saturday_time']);
        $l_sunday = isys_format_json::decode($l_catdata['isys_catg_sla_list__sunday_time']);

        $l_days = bindec($l_catdata['isys_catg_sla_list__days']);

        // Use the new method to automatically fill the formfields with the category data.
        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Applying rules, that don't get covered by "fill_formfields()".
        foreach ([
                     'MONDAY',
                     'TUESDAY',
                     'WEDNESDAY',
                     'THURSDAY',
                     'FRIDAY',
                     'SATURDAY',
                     'SUNDAY'
                 ] as $l_index => $l_day) {
            $l_rules['C__CATG__SLA__WEEK_DAY__' . $l_day] = [
                'p_bChecked'        => !!($l_days & pow(2, 6 - $l_index)),
                'p_strTitle'        => 'LC__UNIVERSAL__CALENDAR__DAYS_' . $l_day,
                'p_strClass'        => 'week_day mr5',
                'p_strValue'        => 1,
                'p_bInfoIconSpacer' => 0
            ];

            $l_rules['C__CATG__SLA__WEEK_DAY__' . $l_day . '_TIME_TO'] = [
                'p_strClass'            => 'input-mini',
                'p_strPlaceholder'      => 'hh:mm',
                'inputGroupMarginClass' => ''
            ];
        }

        $l_rules['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_FROM']['p_strValue'] = isset($l_monday['from']) ? $p_cat::calculate_seconds_to_time($l_monday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__MONDAY_TIME_TO']['p_strValue'] = isset($l_monday['to']) ? $p_cat::calculate_seconds_to_time($l_monday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_FROM']['p_strValue'] = isset($l_tuesday['from']) ? $p_cat::calculate_seconds_to_time($l_tuesday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__TUESDAY_TIME_TO']['p_strValue'] = isset($l_tuesday['to']) ? $p_cat::calculate_seconds_to_time($l_tuesday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_FROM']['p_strValue'] = isset($l_wednesday['from']) ? $p_cat::calculate_seconds_to_time($l_wednesday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__WEDNESDAY_TIME_TO']['p_strValue'] = isset($l_wednesday['to']) ? $p_cat::calculate_seconds_to_time($l_wednesday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_FROM']['p_strValue'] = isset($l_thursday['from']) ? $p_cat::calculate_seconds_to_time($l_thursday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__THURSDAY_TIME_TO']['p_strValue'] = isset($l_thursday['to']) ? $p_cat::calculate_seconds_to_time($l_thursday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_FROM']['p_strValue'] = isset($l_friday['from']) ? $p_cat::calculate_seconds_to_time($l_friday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__FRIDAY_TIME_TO']['p_strValue'] = isset($l_friday['to']) ? $p_cat::calculate_seconds_to_time($l_friday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_FROM']['p_strValue'] = isset($l_saturday['from']) ? $p_cat::calculate_seconds_to_time($l_saturday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__SATURDAY_TIME_TO']['p_strValue'] = isset($l_saturday['to']) ? $p_cat::calculate_seconds_to_time($l_saturday['to']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_FROM']['p_strValue'] = isset($l_sunday['from']) ? $p_cat::calculate_seconds_to_time($l_sunday['from']) : null;
        $l_rules['C__CATG__SLA__WEEK_DAY__SUNDAY_TIME_TO']['p_strValue'] = isset($l_sunday['to']) ? $p_cat::calculate_seconds_to_time($l_sunday['to']) : null;
        $l_rules['C__CATG__SLA__SERVICE_LEVEL']['p_arData'] = $p_cat->callback_property_service_level(isys_request::factory());

        // Apply rules.
        $this->get_template_component()
            ->assign('servicelevel_description', $l_catdata['isys_sla_service_level__description'] ?: isys_tenantsettings::get('gui.empty_value', '-'))
            ->assign('servicelevel_description_empty', isys_tenantsettings::get('gui.empty_value', '-'))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}