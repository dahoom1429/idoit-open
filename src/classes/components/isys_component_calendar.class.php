<?php

/**
 * i-doit
 *
 * Calendar class.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.2.0
 */
class isys_component_calendar extends isys_component
{
    /**
     * A simple wildchar :)
     *
     * @var  string
     */
    const WILDCHAR = '*';

    /**
     * Array for all the instances.
     *
     * @var  array
     */
    protected static $m_instances = [];

    /**
     * Array for all the calender events.
     *
     * @var  array
     */
    protected $m_events = [];

    /**
     * Array for all sorts of options.
     *
     * @var  array
     */
    protected $m_options = [];

    /**
     * @param   string $p_name
     * @param   array  $p_options
     *
     * @return  isys_component_calendar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_name, array $p_options = [])
    {
        if (!array_key_exists($p_name, self::$m_instances)) {
            self::$m_instances[$p_name] = new self($p_options);
        }

        return self::$m_instances[$p_name];
    }

    /**
     * This method adds an event to the calendar.
     *
     * @param   mixed $p_event
     *
     * @return  isys_component_calendar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_event(isys_component_calendar_event $p_event)
    {
        $this->m_events[] = $p_event;

        return $this;
    }

    /**
     * Method for adding the typical holidays to the given year.
     *
     * @param   integer $p_year
     *
     * @return  isys_component_calendar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function add_holidays($p_year = null)
    {
        $l_year = $p_year ?: $this->m_options['year'];

        /*
         * Some serious calculating for easter:
         * This algorithm is from Practical Astronomy With Your Calculator, 2nd Edition by Peter
         * Duffett-Smith. It was originally from Butcher's Ecclesiastical Calendar, published in
         * 1876. This algorithm has also been published in the 1922 book General Astronomy by
         * Spencer Jones; in The Journal of the British Astronomical Association (Vol.88, page
         * 91, December 1977); and in Astronomical Algorithms (1991) by Jean Meeus.
         */
        $l_a = $l_year % 19;
        $l_b = (int)($l_year / 100);
        $l_c = $l_year % 100;
        $l_d = (int)($l_b / 4);
        $l_e = $l_b % 4;
        $l_f = (int)(($l_b + 8) / 25);
        $l_g = (int)(($l_b - $l_f + 1) / 3);
        $l_h = (19 * $l_a + $l_b - $l_d - $l_g + 15) % 30;
        $l_i = (int)($l_c / 4);
        $l_j = $l_c % 4;
        $l_k = (32 + 2 * $l_e + 2 * $l_i - $l_h - $l_j) % 7;
        $l_l = (int)(($l_a + 11 * $l_h + 22 * $l_k) / 451);
        $l_m = ($l_h + $l_k - 7 * $l_l + 114) % 31;

        $l_month = (int)(($l_h + $l_k - 7 * $l_l + 114) / 31);
        $l_day = $l_m + 1;

        return $this->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
            ->get('LC__CALENDAR_EVENT__NEW_YEAR'), 1, 1))
            ->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
                ->get('LC__CALENDAR_EVENT__VALENTINES_DAY'), 14, 2))
            ->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
                ->get('LC__CALENDAR_EVENT__SAINT_PATRICKS_DAY'), 17, 3))
            ->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
                ->get('LC__CALENDAR_EVENT__EASTER'), $l_day, $l_month, $l_year))
            ->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
                ->get('LC__CALENDAR_EVENT__HALLOWEEN'), 31, 10))
            ->add_event(isys_component_calendar_event::factory(isys_application::instance()->container->get('language')
                ->get('LC__CALENDAR_EVENT__CHRISTMAS'), 24, 12));
    }

    /**
     * Render method for displaying the calendar.
     *
     * @param   bool $p_render_template
     *
     * @return  mixed  Depending on the parameter, you'll get a renderd calendar or the raw data.
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_render_template = true)
    {
        $l_data = [];

        $l_first = mktime(1, 0, 0, $this->m_options['month'], 1, $this->m_options['year']);
        $l_total = date('t', $l_first);
        $l_last = mktime(1, 0, 0, $this->m_options['month'], $l_total, $this->m_options['year']);

        $l_days = 0;
        $l_week = [];

        $l_week_start = 1;

        if ($this->m_options['week_start'] == C__DAY__SUNDAY) {
            $l_week_start = 0;
        }

        if (($l_pad = (int)date('w', $l_first) - $l_week_start) < 0) {
            $l_pad = 6;
        }

        if ($l_pad > 0) {
            // Number of days in the previous month
            $l_n = (int)date('t', mktime(1, 0, 0, $this->m_options['month'] - 1, 1, $this->m_options['year']));

            // i = number of day, t = number of days to pad
            for ($i = $l_n - $l_pad + 1, $l_t = $l_pad;$l_t > 0;$l_t--, $i++) {
                // Preparing the specific data, for this day.
                $l_week[] = $this->retrieve_day_data(mktime(1, 0, 0, $this->m_options['month'] - 1, $i, $this->m_options['year']));
                $l_days++;
            }
        }

        // i = number of day
        for ($i = 1;$i <= $l_total;$i++) {
            if ($l_days % 7 === 0 && !empty($l_week)) {
                // Start a new week
                $l_data[] = $l_week;
                $l_week = [];
            }

            // Preparing the specific data, for this day.
            $l_week[] = $this->retrieve_day_data(mktime(1, 0, 0, $this->m_options['month'], $i, $this->m_options['year']));
            $l_days++;
        }

        if (($l_pad = (int)date('w', $l_last) - $l_week_start) < 0) {
            $l_pad = 6;
        }

        if ($l_pad >= 0) {
            // i = number of day, t = number of days to pad
            for ($i = 1, $l_t = 6 - $l_pad;$l_t > 0;$l_t--, $i++) {
                // Preparing the specific data, for this day.
                $l_week[] = $this->retrieve_day_data(mktime(1, 0, 0, $this->m_options['month'] + 1, $i, $this->m_options['year']));
            }
        }

        if (!empty($l_week)) {
            // Append the remaining days
            $l_data[] = $l_week;
        }

        $l_month_names = isys_locale::get_instance()
            ->get_month_names();

        if ($p_render_template) {
            return isys_application::instance()->template->assign('year', $this->m_options['year'])
                ->assign('month', $l_month_names[($this->m_options['month'] - 1)])
                ->assign('month_num', $this->m_options['month'])
                ->assign('data', $l_data)
                ->fetch($this->m_options['template']);
        }

        return [
            'year'      => $this->m_options['year'],
            'month'     => $l_month_names[($this->m_options['month'] - 1)],
            'month_num' => $this->m_options['month'],
            'data'      => $l_data
        ];
    }

    /**
     * Method for setting the given options and overwriting already existing options.
     *
     * @param   array $p_options
     *
     * @return  isys_component_calendar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_options(array $p_options)
    {
        $this->m_options = $p_options;

        return $this;
    }

    /**
     * Method for merging the given options with the already existing options.
     *
     * @param   array $p_options
     *
     * @return  isys_component_calendar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function merge_options(array $p_options)
    {
        $this->m_options = array_replace_recursive($this->m_options, $p_options);

        return $this;
    }

    /**
     * Retrieve the relevant data to a day.
     *
     * @param   integer $p_timestamp
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function retrieve_day_data($p_timestamp)
    {
        $l_css_class = $l_date_event = [];

        if (date('w', $p_timestamp) == 6 || date('w', $p_timestamp) == 0) {
            $l_css_class[] = 'weekend';
        }

        if (date('n', $p_timestamp) != $this->m_options['month']) {
            $l_css_class[] = 'bg-lightgrey';
        }

        if (date('d.m.Y') == date('d.m.Y', $p_timestamp) && $this->m_options['mark_today']) {
            $l_css_class[] = 'today';
        }

        if (is_array($this->m_events) && count($this->m_events) > 0) {
            foreach ($this->m_events as $l_event) {
                if ($l_event->is_event_date(date('j', $p_timestamp), date('n', $p_timestamp), date('Y', $p_timestamp))) {
                    $l_css_class[] = 'event';
                    $l_date_event[] = $l_event->get_event();
                }
            }
        }

        return [
            'date'        => date('d', $p_timestamp),
            'css_class'   => implode(' ', array_unique($l_css_class)),
            'events'      => (is_countable($l_date_event) && count($l_date_event) > 0) ? $l_date_event : false
        ];
    }

    /**
     * Private constructor - Singleton.
     *
     * @param  array $p_options
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function __construct(array $p_options = [])
    {
        $this->m_options = array_merge([
            'year'       => date('Y'),
            'month'      => date('n'),
            'day'        => date('j'),
            'week_start' => C__DAY__MONDAY,
            'mark_today' => true,
            'template'   => 'content/calendar.tpl'
        ], $p_options);
    }
}
