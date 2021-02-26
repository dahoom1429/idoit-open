<?php

use idoit\Component\Helper\Date;
use idoit\Component\Interval\Config;

/**
 * i-doit
 *
 * Calendar class
 *
 *
 * @package     i-doit
 * @subpackage  popups
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_interval extends isys_component_popup
{
    /**
     * @param isys_component_template $tpl
     * @param array                   $params
     *
     * @return string
     * @throws \idoit\Exception\DateException
     * @throws isys_exception_general
     */
    public function handle_smarty_include(isys_component_template &$tpl, $params)
    {
        global $g_dirs;

        $smartyPluginText = new isys_smarty_plugin_f_text();

        if (! ($this->template->editmode() || $params['p_bEditMode'])) {
            return $smartyPluginText->navigation_view($this->template, $params);
        }

        $configuration = false;

        if (isset($params['config'])) {
            if (is_array($params['config'])) {
                $configuration = Config::byArray($params['config']);
            } elseif (isys_format_json::is_json_array($params['config'])) {
                $configuration = Config::byJSON($params['config']);
            }
        }

        $hiddenParams = [
            'name'              => $params['name'] . '__HIDDEN',
            'p_bInvisible'      => true,
            'disableInputGroup' => true,
            'p_bInfoIconSpacer' => 0,
            'p_strValue'        => ($configuration ? isys_format_json::encode($configuration->toArray()) : '')
        ];

        $params['p_strValue'] = ($configuration ? $configuration->getHumanReadable(true) : $this->language->get('LC__INTERVAL__NO_INTERVAL_DEFINED'));
        $params['p_bReadonly'] = true;
        $params['disableInputGroup'] = true;

        $onClick = $this->process_overlay('', 480, 280, ['name' => $params['name'], 'config' => $params['config']]);
        $icon = $g_dirs['images'] . 'icons/silk/clock.png';
        $popupButton = '<span class="input-group-addon input-group-addon-clickable" onclick="' . $onClick . '"><img src="' . $icon . '" alt="" /></span>';

        $onClick = "$('" . $params['name'] . "').setValue('" . $this->language->get('LC__INTERVAL__NO_INTERVAL_DEFINED') . "'); $('" . $hiddenParams['name'] .
            "').setValue(''); ";
        $icon = $g_dirs['images'] . 'icons/silk/cross.png';
        $cancelButton = '<span class="input-group-addon input-group-addon-clickable" onclick="' . $onClick . '"><img src="' . $icon . '" alt="" /></span>';

        return $smartyPluginText->navigation_edit($this->template, $hiddenParams) . $smartyPluginText->navigation_edit($this->template, $params) . $popupButton . $cancelButton;
    }

    /**
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws \idoit\Exception\DateException
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_general
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Unpack module request.
        $params = isys_format_json::decode(base64_decode($_POST['params']), true);

        // This date is the start of the interval.
        $date = null;

        if (isset($params['date']) && is_numeric($params['date'])) {
            $date = (int)$params['date'];
        }

        if (isset($params['config'])) {
            if (is_array($params['config'])) {
                $config = Config::byArray($params['config']);
            } elseif (isys_format_json::is_json_array($params['config'])) {
                $config = Config::byJSON($params['config']);
            } else {
                // Use a default config.
                $config = new Config(new DateTime());
            }
        } else {
            // Use a default config.
            $config = new Config(new DateTime());
        }

        $endAfter = $config->getEndAfter();

        $rules = [
            'C__INTERVAL__REPEAT_EVERY'      => [
                'p_strClass' => 'input-mini',
                'p_onChange' => "$('C__INTERVAL__REPEAT_EVERY').fire('updated:value')",
                'p_strValue' => $config->getRepeatEvery()
            ],
            'C__INTERVAL__REPEAT_EVERY_UNIT' => [
                'p_strClass'      => 'input-mini',
                'p_arData'        => [
                    Config::REPEAT_UNIT_DAY   => 'LC__UNIVERSAL__DAY',
                    Config::REPEAT_UNIT_WEEK  => 'LC__UNIVERSAL__WEEK',
                    Config::REPEAT_UNIT_MONTH => 'LC__UNIVERSAL__MONTH',
                    Config::REPEAT_UNIT_YEAR  => 'LC__UNIVERSAL__YEAR'
                ],
                'p_bSort'         => false,
                'p_bDbFieldNN'    => true,
                'p_strSelectedID' => $config->getRepeatEveryUnit()
            ],
            'C__INTERVAL__END_DATE'          => [
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strValue'        => ($endAfter == Config::END_AFTER_DATE ? $config->getEndDetails()->format('Y-m-d H:i:s') : null)
            ],
            'C__INTERVAL__END_EVENT_AMOUNT'  => [
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strValue'        => ($endAfter == Config::END_AFTER_EVENTS ? $config->getEndDetails() : null)
            ]
        ];

        // Calculate the week number of the current date of this month (13.Nov 2017 = "second monday of november")
        $relativeDate = $this->language->get('LC__INTERVAL__MONTHLY_AT') . ' ' . strtolower(Date::getRelativeWeekOfMonthByDay($date, true)) . ' ' . Date::getDayName($date);

        // Prepare array of days.
        $days = [
            Date::MONDAY    => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_MONDAY'), 0, 2),
            Date::TUESDAY   => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY'), 0, 2),
            Date::WEDNESDAY => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY'), 0, 2),
            Date::THURSDAY  => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY'), 0, 2),
            Date::FRIDAY    => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY'), 0, 2),
            Date::SATURDAY  => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY'), 0, 2),
            Date::SUNDAY    => substr($this->language->get('LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY'), 0, 2)
        ];

        // Display the dialog template and return it.
        $this->template->activate_editmode()
            ->smarty_tom_add_rules('tom.popup.interval', $rules)
            ->assign('relativeDate', $relativeDate)
            ->assign('selfView', $params['name'])
            ->assign('selfHidden', $params['name'] . '__HIDDEN')
            ->assign('config', $config->toArray())
            ->assign('days', $days)
            ->assign('repeatUnitDay', Config::REPEAT_UNIT_DAY)
            ->assign('repeatUnitWeek', Config::REPEAT_UNIT_WEEK)
            ->assign('repeatUnitMonth', Config::REPEAT_UNIT_MONTH)
            ->assign('repeatUnitYear', Config::REPEAT_UNIT_YEAR)
            ->assign('endAfterNever', Config::END_AFTER_NEVER)
            ->assign('endAfterDate', Config::END_AFTER_DATE)
            ->assign('endAfterEvents', Config::END_AFTER_EVENTS)
            ->assign('ajaxUrl', isys_helper_link::create_url([C__GET__AJAX => 1, C__GET__AJAX_CALL => 'interval'], true))
            ->display('popup/interval.tpl');
        die;
    }
}
