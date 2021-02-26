<?php

/**
 * i-doit
 *
 * Calendar class
 *
 *
 * @package     i-doit
 * @subpackage  popups
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_calendar extends isys_component_popup
{
    /**
     * @param  isys_component_template &$p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        global $g_dirs;

        $l_language = $this->language->get_loaded_language();
        $locales = isys_application::instance()->container->get('locales');

        if (!$l_language) {
            $l_language = 'en';
        }

        $l_name = str_replace(['[', ']', '-'], '_', $p_params['name']);
        $l_hidden_date = '';

        if (strpos($p_params['name'], '[') !== false && strpos($p_params['name'], ']') !== false) {
            $l_tmp = explode('[', $p_params['name']);

            $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $l_view = $p_params['name'] . '__VIEW';
            $l_hidden = $p_params['name'] . '__HIDDEN';
        }

        $l_readonly = false;

        if (isset($p_params['p_bReadonly'])) {
            $l_readonly = filter_var($p_params['p_bReadonly'], FILTER_VALIDATE_BOOLEAN);
        }

        /**
         * DATE and TIME
         */
        if (isset($p_params['p_bTime']) && $p_params['p_bTime']) {
            if ($p_params['p_strValue']) {
                $l_time_value = date('H:i:s', strtotime($p_params['p_strValue']));
            } else {
                $l_time_value = '00:00:00';
            }

            if ($this->template->editmode() || $p_params['p_bEditMode']) {
                $onChange = $l_attr_readonly = '';

                if ($l_readonly) {
                    $l_attr_readonly = ' readonly="readonly"';
                }

                if ($p_params['timeOnChange']) {
                    $onChange = 'onchange="' . $p_params['timeOnChange'] . '"' ;
                }

                $l_time = '<input type="text" class="input input-group-addon m0" style="width:30%;" id="' . $l_name . '__TIME" name="' . $l_name . '__TIME" value="' .
                    $l_time_value . '" ' . $l_attr_readonly . ' ' . $onChange . '/>';
            } else {
                $l_time = ' - ' . $l_time_value;
            }

            if ($p_params['p_strValue']) {
                $l_hidden_date = date('Y-m-d H:i:s', strtotime($p_params['p_strValue']));
                $p_params['p_strValue'] = $locales->fmt_date($p_params['p_strValue']);
            }

            /**
             * DATE
             */
        } else {
            $l_time = '';

            if ($p_params['p_strValue']) {
                $l_hidden_date = date('Y-m-d', strtotime($p_params['p_strValue']));
                $p_params['p_strValue'] = $locales->fmt_date($p_params['p_strValue']);
            }
        }

        if (!isset($p_params['enableYearBrowse']) && $p_params['enableYearBrowse']) {
            $p_params['enableYearBrowse'] = 1;
        } else {
            $p_params['enableYearBrowse'] = 0;
        }

        if (isset($p_params['disableFutureDate']) && $p_params['disableFutureDate']) {
            $p_params['disableFutureDate'] = 'true';
        } else {
            $p_params['disableFutureDate'] = 'false';
        }

        if (isset($p_params['disablePastDate']) && $p_params['disablePastDate']) {
            $p_params['disablePastDate'] = 'true';
        } else {
            $p_params['disablePastDate'] = 'false';
        }

        if (isset($p_params['enableCloseOnBlur']) && $p_params['enableCloseOnBlur']) {
            $p_params['enableCloseOnBlur'] = 'true';
        } else {
            $p_params['enableCloseOnBlur'] = 'false';
        }

        if (isset($p_params['cellCallback'])) {
            $l_cellCallback = ',cellCallback : ' . $p_params['cellCallback'];
        } else {
            $l_cellCallback = '';
        }

        if (isset($p_params['clickCallback'])) {
            $l_clickCallback = ',clickCallback: ' . $p_params['clickCallback'];
        } else {
            $l_clickCallback = '';
        }

        $p_params['p_strID'] = $l_view;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if ($this->template->editmode() || $p_params['p_bEditMode'] == true) {
            if (isset($p_params['p_onChange'])) {
                $p_params['p_onChange'] = rtrim($p_params['p_onChange'], ';') . ';';
            }

            $l_raw_date_format = $locales->get_date_format();
            $l_date_splitter = (strpos($l_raw_date_format, '.') ? '.' : '-');
            $l_date_format = explode($l_date_splitter, str_replace(['d', 'm', 'Y'], ['dd', 'mm', 'yyyy'], $l_raw_date_format));
            $l_new_date_format = isys_format_json::encode($l_date_format);

            if ($l_readonly === false) {
                // @see ID-1904  Changed the DatePickerFormatter according to lines below.
                $p_params['p_onChange'] .= "var val = ''; if(! this.value.blank()) { var df = new DatePickerFormatter(" . str_replace('"', "'", $l_new_date_format) . ", '" .
                    $l_date_splitter . "').match(this.value); val = df[0] + '-' + df[1] + '-' + df[2];} $('" . $l_hidden . "').setValue(val);";
            }

            $l_strHiddenField = '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_hidden_date . '" />';

            $p_params['disableInputGroup'] = true;

            $p_params['p_strClass'] .= ' m0';

            $l_strOut = $l_objPlugin->navigation_edit($this->template, $p_params) . $l_time . '<span class="input-group-addon"><img src="' . $g_dirs['images'] .
                'icons/silk/calendar.png" alt="" /></span>' . $l_strHiddenField;

            $p_params['closeOnBlurDelay'] = 15;

            if ($l_readonly === false) {
                $l_strOut .= "<script type=\"text/javascript\">" . "var dpck_" . $l_name . "	= new DatePicker({
  relative	: '" . $l_view . "',
  hidden	: '" . $l_hidden . "',
  time		: '" . $l_name . "__TIME',
  language	: '" . $l_language . "',
  observeScrollingParent: '" . $p_params['observeScrollingParent'] . "',
  closeEffect	: 'fade',
  showEffect	: 'slide',
  keepFieldEmpty : true,
  disableFutureDate: " . $p_params["disableFutureDate"] . ",
  disablePastDate: " . $p_params["disablePastDate"] . ",
  topOffset : 26,
  leftOffset : 1,
  enableYearBrowse : " . $p_params["enableYearBrowse"] . ",
  enableCloseOnBlur : " . $p_params["enableCloseOnBlur"] . ",
  closeOnBlurDelay: " . $p_params["closeOnBlurDelay"] . ",
  wrongFormatMessage: '" . $this->language->get('LC_CALENDAR_POPUP__WRONGDATE') . "',
  zindex : 99999
  " . $l_cellCallback . $l_clickCallback . "
});
dpck_" . $l_name . ".setDateFormat(" . $l_new_date_format . ", \"" . $l_date_splitter . "\");
dpck_" . $l_name . ".setHiddenFormat([ \"yyyy\", \"mm\", \"dd\" ], \"-\");

Event.observe(window, 'load', function() {
	delete dpck_" . $l_name . ";
	var dpck_" . $l_name . "	= new DatePicker({
		relative	: '" . $l_view . "',
		hidden		: '" . $l_hidden . "',
		time		: '" . $l_name . "__TIME',
		language	: '" . $l_language . "',
		observeScrollingParent: '" . $p_params['observeScrollingParent'] . "',
		closeEffect	: 'fade',
		showEffect	: 'slide',
		keepFieldEmpty : true,
		disableFutureDate: " . $p_params["disableFutureDate"] . ",
		disablePastDate: " . $p_params["disablePastDate"] . ",
		topOffset : 26,
		leftOffset : 1,
		enableYearBrowse : " . $p_params["enableYearBrowse"] . ",
		enableCloseOnBlur : " . $p_params["enableCloseOnBlur"] . ",
		closeOnBlurDelay: " . $p_params["closeOnBlurDelay"] . ",
		wrongFormatMessage: '" . $this->language->get('LC_CALENDAR_POPUP__WRONGDATE') . "',
		zindex : 99999
		" . $l_cellCallback . $l_clickCallback . "
	 });
	 dpck_" . $l_name . ".setDateFormat(" . $l_new_date_format . ", \"" . $l_date_splitter . "\");
	 dpck_" . $l_name . ".setHiddenFormat([ \"yyyy\", \"mm\", \"dd\" ], \"-\");
});" .

                    "</script>";
            }

            return $l_strOut;
        }

        $p_params['p_bHtmlDecode'] = true;

        return $l_objPlugin->navigation_view($this->template, $p_params) . $l_time;
    }

    /**
     * @deprecated
     *
     * @param   isys_module_request $p_modreq
     *
     * @return  null
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        return null;
    }
}
