<?php

/**
 * AJAX handler for the calendar widget.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.2.0
 */
class isys_ajax_handler_dashboard_widgets_calendar extends isys_ajax_handler_dashboard
{
    /**
     * Static method for retrieving "planning" data for the given object. Gets called statically by "$this->callback()".
     *
     * @static
     *
     * @param   array $p_params
     *
     * @return  string
     * @throws  SmartyException
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_planning_data(array $p_params)
    {
        if (!class_exists('isys_cmdb_dao_category_g_planning')) {
            return '';
        }

        $l_dao = isys_cmdb_dao_category_g_planning::instance(isys_application::instance()->database);

        $l_planning_row = $l_dao->get_data($p_params['cat_id'], $p_params['obj_id'])
            ->get_row();

        $l_status = $l_dao->retrieve('SELECT isys_cmdb_status__title AS title, isys_cmdb_status__color AS color
			FROM isys_cmdb_status
			WHERE isys_cmdb_status__id = ' . $l_dao->convert_sql_id($l_planning_row['isys_catg_planning_list__isys_cmdb_status__id']) . ';')
            ->get_row();

        return isys_application::instance()->template->assign('data', [
            'obj_link'        => isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_planning_row['isys_obj__id']]),
            'obj_id'          => $l_planning_row['isys_obj__id'],
            'obj_title'       => $l_planning_row['isys_obj__title'],
            'obj_type_title'  => isys_application::instance()->container->get('language')
                ->get($l_planning_row['isys_obj_type__title']),
            'planning_start'  => date('d.m.Y', $l_planning_row['isys_catg_planning_list__start']),
            'planning_end'    => date('d.m.Y', $l_planning_row['isys_catg_planning_list__end']),
            'planning_status' => $l_status
        ])
            ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events/planning.tpl');
    }

    /**
     * Static method for retrieving "maintenance" data for the given object. Gets called statically by "$this->callback()".
     *
     * @static
     *
     * @param   array $p_params
     *
     * @return  string
     * @see     $this->callback()
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function get_maintenance_data(array $p_params)
    {
        global $g_comp_database;

        /* @var  isys_maintenance_dao $l_dao */
        $l_dao = isys_maintenance_dao::instance($g_comp_database);

        $l_maintenance_data = $l_dao->get_data($p_params['maintenance_id'])
            ->get_row();
        $l_object_data = $l_dao->get_object($p_params['obj_id'])
            ->get_row();
        $l_color = $l_dao->retrieve('SELECT isys_cmdb_status__color FROM isys_cmdb_status WHERE isys_cmdb_status__const = "C__CMDB_STATUS__UNDER_REPAIR";')
            ->get_row_value('isys_cmdb_status__color');

        return isys_application::instance()->template->assign('color', $l_color)
            ->assign('data', [
                'obj_link'          => isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_params['obj_id']]),
                'obj_id'            => $p_params['obj_id'],
                'obj_title'         => $l_object_data['isys_obj__title'],
                'obj_type_title'    => isys_application::instance()->container->get('language')
                    ->get($l_object_data['isys_obj_type__title']),
                'maintenance_start' => date('d.m.Y', strtotime($l_maintenance_data['isys_maintenance__date_from'])),
                'maintenance_end'   => date('d.m.Y', strtotime($l_maintenance_data['isys_maintenance__date_to'])),
                'maintenance_type'  => isys_application::instance()->container->get('language')
                    ->get($l_maintenance_data['isys_maintenance_type__title']),
            ])
            ->assign('module_link', isys_helper_link::create_url([
                C__GET__MODULE_ID     => defined_or_default('C__MODULE__MAINTENANCE'),
                C__GET__TREE_NODE     => defined_or_default('C__MODULE__MAINTENANCE') . 2,
                C__GET__SETTINGS_PAGE => defined_or_default('C__MAINTENANCE__PLANNING'),
                C__GET__ID            => $p_params['maintenance_id']
            ]))
            ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events/maintenance.tpl');
    }

    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'trigger_callback':
                    $l_return['data'] = $this->callback(isys_format_json::decode($_POST['events'], true), $_POST['day'], $_POST['month'], $_POST['year']);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Callback method for handlind calendar events (even if they're not type "callback").
     *
     * @param   array   $p_events
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function callback($p_events, $p_day, $p_month, $p_year)
    {
        $l_output = [];

        if (is_array($p_events) && count($p_events) > 0) {
            foreach ($p_events as $l_event) {
                if ($l_event['type'] == isys_component_calendar_event::TYPE_NOTE || $l_event['type'] == isys_component_calendar_event::TYPE_ALERT) {
                    $l_output[] = $l_event['name'];
                } else {
                    if ($l_event['type'] == isys_component_calendar_event::TYPE_CALLBACK && isset($l_event['callback'])) {
                        if (is_callable($l_event['callback']['callback'])) {
                            $l_output[] = [
                                'data' => call_user_func($l_event['callback']['callback'], $l_event['callback']['params'])
                            ];
                        } else {
                            $l_message = 'The given callback is not callable!';
                            $l_output[] = [
                                'data' => isys_application::instance()->template->assign('message', $l_message)
                                    ->fetch(isys_dashboard_widgets_calendar::get_tpl_dir() . 'events' . DS . 'error.tpl')
                            ];
                        }
                    }
                }
            }
        }

        return $l_output;
    }
}
