<?php

/**
 * Example ajax handler for the widget bookmark
 * To use the widget ajax handler
 *
 * @author Van Quyen Hoang <qhoang@i-doit.org>
 */
class isys_ajax_handler_dashboard_widgets_calculator extends isys_ajax_handler_dashboard
{
    /**
     * Dao object.
     *
     * @var  isys_cmdb_dao
     */
    private $m_dao = null;

    /**
     * Iniit method
     */
    public function init()
    {
        global $g_config;

        $l_tpl_dir = $g_config['base_dir'] . 'src/classes/modules/dashboard/widgets/calculator/templates/';

        $this->m_dao = isys_cmdb_dao::instance($this->m_database_component);

        $l_calc_type = $_POST['calc_type'];

        $l_rules = [];

        switch ($l_calc_type) {
            case isys_dashboard_widgets_calculator::C__WIDGET__CALCULATOR__NET:
                $l_calc_tpl = 'calculator_net.tpl';

                $l_condition = 'isys_port_speed__const != ' . $this->m_dao->convert_sql_text('C__PORT_SPEED__NOT_SELECTED');

                $l_rules = ['net_unit' => serialize($this->get_units('isys_port_speed', $l_condition))];
                break;

            case isys_dashboard_widgets_calculator::C__WIDGET__CALCULATOR__MEMORY:
                $l_calc_tpl = 'calculator_memory.tpl';

                $l_rules = [
                    'memory_unit' => serialize($this->get_units('isys_memory_unit')),
                    'yes_no'      => get_smarty_arr_YES_NO()
                ];
                break;

            case isys_dashboard_widgets_calculator::C__WIDGET__CALCULATOR__RAID:
                $l_calc_tpl = 'calculator_raid.tpl';

                $l_rules = [
                    'memory_selected' => 'C__MEMORY_UNIT__GB',
                    'raid_lvls'       => $this->get_units('isys_stor_raid_level', 'isys_stor_raid_level__const != ' . $this->m_dao->convert_sql_text('C__STOR_RAID_LEVEL__2')),
                    'memory_unit'     => $this->get_units('isys_memory_unit'),
                ];
                break;

            case isys_dashboard_widgets_calculator::C__WIDGET__CALCULATOR__POWER:
                $l_calc_tpl = 'calculator_power.tpl';
                $l_rules = ['power_unit' => $this->get_units('isys_ac_refrigerating_capacity_unit')];
                break;

            default:
                $l_calc_tpl = 'blank.tpl';
                break;
        }

        isys_application::instance()->template->activate_editmode()
            ->assign('unique_id', $_POST['unique_id'])
            ->assign("rules", $l_rules)
            ->display($l_tpl_dir . $l_calc_tpl);

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
     * Gets units as array by specified table.
     *
     * @param   string $p_table
     * @param   string $p_condition
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    private function get_units($p_table, $p_condition = '')
    {
        $l_return = [];
        $l_sql = 'SELECT * FROM ' . $p_table . ' WHERE TRUE';

        if ($p_condition != '') {
            $l_sql .= ' AND ' . $p_condition;
        }

        $l_res = $this->m_dao->retrieve($l_sql);

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row[$p_table . '__const']] = $l_row[$p_table . '__title'];
            }
        }

        return $l_return;
    }
}