<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_calendar extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';

    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    public static function get_tpl_dir()
    {
        return __DIR__ . '/templates/';
    }

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/calendar.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @throws  Exception
     * @throws  SmartyException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $lang = isys_application::instance()->container->get('language');

        if (!isset($this->m_config['object_events'])) {
            $this->m_config['object_events'] = true;
        }

        $l_rules = [
            'title'         => $this->m_config['title'],
            'object_events' => $this->m_config['object_events'],
        ];

        $l_event_types = [
            isys_component_calendar_event::TYPE_NOTE => $lang->get('LC__CALENDAR_TYPE__NOTE')
        ];

        $l_events = $this->m_config['events'];

        if (is_array($l_events) && count($l_events) > 0) {
            foreach ($l_events as &$l_event) {
                $l_event['LC_type'] = $l_event_types[$l_event['type']];
            }
        }

        $l_url = isys_helper_link::create_url([
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'dashboard_widgets_calendar'
        ]);

        return $this->m_tpl->activate_editmode()
            ->assign('title', $lang->get('LC__WIDGET__CALENDAR_CONFIG'))
            ->assign('events', $l_events)
            ->assign('rules', $l_rules)
            ->assign('event_types', serialize($l_event_types))
            ->assign('calendar_ajax_url', $l_url)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $l_calendar = isys_component_calendar::factory($p_unique_id);

        if (!isset($this->m_config['object_events'])) {
            $this->m_config['object_events'] = true;
        }

        if (count($this->m_config['events']) > 0) {
            foreach ($this->m_config['events'] as $l_event) {
                list($l_day, $l_month, $l_year) = explode('.', $l_event['date']);

                $l_cal_event = isys_component_calendar_event::factory($l_event['name'], $l_day, $l_month, $l_year)
                    ->set_type($l_event['type']);

                if ($l_event['callback'] && is_callable($l_event['callback'])) {
                    $l_event->set_callback($l_event['callback']);
                }

                $l_calendar->add_event($l_cal_event);
            }
        }

        if ($this->m_config['object_events']) {
            $this->add_object_events($l_calendar);
        }

        $l_ajax_url = isys_helper_link::create_url([
            C__GET__AJAX_CALL => 'dashboard_widgets_calendar',
            C__GET__AJAX      => '1',
            'func'            => 'trigger_callback'
        ]);

        // refs #4964 - The last month of the previous year is beeing displayed wrong.
        $l_prev_options = [
            'month' => date('n') - 1,
            'year'  => date('Y')
        ];

        if ($l_prev_options['month'] == 0) {
            $l_prev_options = [
                'month' => 12,
                'year'  => date('Y') - 1
            ];
        }

        // refs #4964 - The first month of the next year is beeing displayed wrong.
        $l_next_options = [
            'month' => date('n') + 1,
            'year'  => date('Y')
        ];

        if ($l_next_options['month'] == 13) {
            $l_next_options = [
                'month' => 1,
                'year'  => date('Y') + 1
            ];
        }

        return $this->m_tpl->assign('ajax_url', $l_ajax_url)
            ->assign('unique_id', $p_unique_id)
            ->assign('title', $this->m_config['title'])
            ->assign('data', $l_calendar->render(false))
            ->assign('data_prev', $l_calendar->merge_options($l_prev_options)->render(false))
            ->assign('data_next', $l_calendar->merge_options($l_next_options)->render(false))
            ->fetch($this->m_tpl_file);
    }

    /**
     * Method for adding object specific events to the calendar.
     *
     * @param   isys_component_calendar $p_calendar
     *
     * @throws isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function add_object_events(isys_component_calendar $p_calendar)
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $userId = isys_application::instance()->container->get('session')->get_user_id();

        $dao = isys_cmdb_dao::instance($database);

        $l_cmdb = [];

        $l_res = $dao->retrieve('SELECT isys_cmdb_status__id, isys_cmdb_status__title FROM isys_cmdb_status;');

        while ($l_row = $l_res->get_row()) {
            $l_cmdb[$l_row['isys_cmdb_status__id']] = $language->get($l_row['isys_cmdb_status__title']);
        }

        $l_res = isys_cmdb_dao_category_s_person_contact_assign::instance($database)
            ->get_data(null, $userId, 'AND isys_catg_contact_list__isys_contact_tag__id = ' . $dao->convert_sql_id(defined_or_default('C__CONTACT_TYPE__ADMIN')));

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if (class_exists('isys_cmdb_dao_category_g_planning')) {
                    $l_planning_res = isys_cmdb_dao_category_g_planning::instance($database)
                        ->get_data(null, $l_row['isys_obj__id']);

                    if (count($l_planning_res) > 0) {
                        while ($l_planning_row = $l_planning_res->get_row()) {
                            list($l_day, $l_month, $l_year) = explode('-', date('j-n-Y', $l_planning_row['isys_catg_planning_list__start']));

                            $p_calendar->add_event(isys_component_calendar_event::factory($language->get('LC__UNIVERSAL__START') . ': ' .
                                $l_cmdb[$l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']], $l_day, $l_month, $l_year)
                                ->set_callback([
                                    'isys_ajax_handler_dashboard_widgets_calendar',
                                    'get_planning_data'
                                ], [
                                    'obj_id' => $l_row['isys_obj__id'],
                                    'cat_id' => $l_planning_row['isys_catg_planning_list__id']
                                ]));

                            list($l_day, $l_month, $l_year) = explode('-', date('j-n-Y', $l_planning_row['isys_catg_planning_list__end']));

                            $p_calendar->add_event(isys_component_calendar_event::factory($language->get('LC__UNIVERSAL__STOP') . ': ' .
                                $l_cmdb[$l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']], $l_day, $l_month, $l_year)
                                ->set_callback([
                                    'isys_ajax_handler_dashboard_widgets_calendar',
                                    'get_planning_data'
                                ], [
                                    'obj_id' => $l_row['isys_obj__id'],
                                    'cat_id' => $l_planning_row['isys_catg_planning_list__id']
                                ]));
                        }
                    }
                }

                // Look for maintenance events, if the module is active.
                if (class_exists('isys_maintenance_dao') && isys_module_manager::instance()->is_active('maintenance')) {
                    $maintenanceDao = isys_maintenance_dao::instance($database);

                    try {
                        $l_maintenance_res = $maintenanceDao->get_data_by_maintenance_object(
                            $l_row['isys_obj__id'],
                            mktime(0, 0, 0, date('m') - 1, 1, date('Y')),
                            mktime(0, 0, 0, date('m') + 2, 0, date('Y'))
                        );

                        if (count($l_maintenance_res)) {
                            while ($l_maintenance_row = $l_maintenance_res->get_row()) {
                                // @see  ID-3241  Create a period: each day from start to end.
                                $period = new DatePeriod(
                                    (new DateTime($l_maintenance_row['isys_maintenance__date_from']))->setTime(0, 0, 0),
                                    new DateInterval('P1D'),
                                    (new DateTime($l_maintenance_row['isys_maintenance__date_to']))->setTime(23, 59, 59)
                                );

                                foreach ($period as $date) {
                                    $p_calendar->add_event(isys_component_calendar_event::factory(
                                        $language->get('LC__UNIVERSAL__START') . ': ' . $language->get('LC__MODULE__MAINTENANCE'),
                                        $date->format('d'),
                                        $date->format('m'),
                                        $date->format('Y')
                                    )
                                        ->set_callback(
                                            ['isys_ajax_handler_dashboard_widgets_calendar', 'get_maintenance_data'],
                                            ['obj_id' => $l_row['isys_obj__id'], 'maintenance_id' => $l_maintenance_row['isys_maintenance__id']]
                                        ));
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Silently fail...
                    }
                }
            }
        }
    }
}
