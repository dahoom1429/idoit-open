<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_ajax_handler_statistic extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        $l_return = [];

        switch ($_GET['func']) {
            case 'get_rack_statistics':
                $this->getRackStatistics($_POST['obj_id']);
                break;
        }

        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * @param  integer $rackObjectId
     *
     * @return void
     */
    protected function getRackStatistics($rackObjectId)
    {
        $rackData = [];

        $locales = isys_application::instance()->container->get('locales');

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $rackData = isys_cmdb_dao_category_s_enclosure::instance($this->m_database_component)
                ->get_data(null, $rackObjectId)
                ->get_row();
        }

        $formfactorData = isys_cmdb_dao_category_g_formfactor::instance($this->m_database_component)
            ->get_data(null, $rackObjectId)
            ->get_row();

        $result = isys_cmdb_dao_location::instance($this->m_database_component)
            ->get_location($rackObjectId, null);

        $statistics = [
            'slots'      => [
                'horizontal' => [
                    'front' => (int)$formfactorData['isys_catg_formfactor_list__rackunits'],
                    'back'  => (int)$formfactorData['isys_catg_formfactor_list__rackunits']
                ],
                'vertical'   => [
                    'front' => (int)($rackData['isys_cats_enclosure_list__vertical_slots_front'] ?: 0),
                    'back'  => (int)($rackData['isys_cats_enclosure_list__vertical_slots_rear'] ?: 0)
                ]
            ],
            'free'       => [
                'horizontal' => [
                    'front' => 0,
                    'back'  => 0
                ],
                'vertical'   => [
                    'front' => 0,
                    'back'  => 0
                ]
            ],
            'used'       => [
                'horizontal' => [
                    'front' => 0,
                    'back'  => 0
                ],
                'vertical'   => [
                    'front' => 0,
                    'back'  => 0
                ]
            ],
            'connectors' => [],
            'ports'      => []
        ];

        while ($row = $result->get_row()) {
            $rackUnits = (int)($row['isys_catg_formfactor_list__rackunits'] ?: 1);

            if ($row['isys_catg_location_list__option'] == C__RACK_INSERTION__HORIZONTAL && $row['isys_catg_location_list__insertion'] != null &&
                $row['isys_catg_location_list__pos'] > 0) {
                if ($row['isys_catg_location_list__insertion'] == C__INSERTION__FRONT) {
                    $statistics['used']['horizontal']['front'] += $rackUnits;
                } else if ($row['isys_catg_location_list__insertion'] == C__INSERTION__REAR) {
                    $statistics['used']['horizontal']['back'] += $rackUnits;
                } else {
                    $statistics['used']['horizontal']['front'] += $rackUnits;
                    $statistics['used']['horizontal']['back'] += $rackUnits;

                }
            } else {
                if ($row['isys_catg_location_list__option'] == C__RACK_INSERTION__VERTICAL && $row['isys_catg_location_list__insertion'] != null &&
                    $row['isys_catg_location_list__pos'] > 0) {
                    if ($row['isys_catg_location_list__insertion'] == C__INSERTION__FRONT) {
                        $statistics['used']['vertical']['front']++;
                    } else {
                        $statistics['used']['vertical']['back']++;
                    }
                }
            }

            $connectorStats = $this->get_connector_statistics($row['isys_obj__id']);

            if ($connectorStats['conns_num'] > 0) {
                foreach ($connectorStats['conns'] as $type => $connectorStat) {
                    $statistics['connectors'][$row['isys_obj_type__title']]['in'][$type]['free'] += $connectorStat['free_in'];
                    $statistics['connectors'][$row['isys_obj_type__title']]['in'][$type]['used'] += $connectorStat['used_in'];
                    $statistics['connectors'][$row['isys_obj_type__title']]['out'][$type]['free'] += $connectorStat['free_out'];
                    $statistics['connectors'][$row['isys_obj_type__title']]['out'][$type]['used'] += $connectorStat['used_out'];
                }
            }

            $portStats = $this->get_port_statistics($row['isys_obj__id']);

            if ($portStats['ports_num'] > 0) {
                foreach ($portStats['ports'] as $type => $l_port_stats) {
                    $statistics['ports'][$row['isys_obj_type__title']][$type]['free'] += $l_port_stats['free'];
                    $statistics['ports'][$row['isys_obj_type__title']][$type]['used'] += $l_port_stats['used'];
                }
            }
        }

        $statistics['free']['horizontal']['front'] = $statistics['slots']['horizontal']['front'] - $statistics['used']['horizontal']['front'];
        $statistics['free']['horizontal']['back'] = $statistics['slots']['horizontal']['back'] - $statistics['used']['horizontal']['back'];
        $amountHorizontalSlots = array_sum($statistics['slots']['horizontal']);
        $freeHorizontalSlots = array_sum($statistics['free']['horizontal']);
        $statistics['free']['horizontal']['percent'] = 0;
        $statistics['free']['horizontal']['percentColor'] = isys_helper_color::retrieve_color_by_percent(0);

        if ($amountHorizontalSlots) {
            $statistics['free']['horizontal']['percent'] = round(($freeHorizontalSlots / $amountHorizontalSlots) * 100, 2);
            $statistics['free']['horizontal']['percentColor'] = isys_helper_color::retrieve_color_by_percent($statistics['free']['horizontal']['percent']);
        }

        $statistics['free']['vertical']['front'] = $statistics['slots']['vertical']['front'] - $statistics['used']['vertical']['front'];
        $statistics['free']['vertical']['back'] = $statistics['slots']['vertical']['back'] - $statistics['used']['vertical']['back'];
        $amountVerticalSlots = array_sum($statistics['slots']['vertical']);
        $freeVerticalSlots = array_sum($statistics['free']['vertical']);
        $statistics['free']['vertical']['percent'] = 0;
        $statistics['free']['vertical']['percentColor'] = isys_helper_color::retrieve_color_by_percent(0);

        if ($amountVerticalSlots) {
            $statistics['free']['vertical']['percent'] = round(($freeVerticalSlots / $amountVerticalSlots) * 100, 2);
            $statistics['free']['vertical']['percentColor'] = isys_helper_color::retrieve_color_by_percent($statistics['free']['vertical']['percent']);
        }

        // Calculate the consumption of electricity.
        $componentConsumption = $this->get_consumption_of_electricity($rackObjectId);
        $rackConsumption = $this->getOwnConsumptionOfElectricity($rackObjectId);
        $statistics['watt'] = $locales->fmt_numeric($componentConsumption['watt']);
        $statistics['btu'] = $locales->fmt_numeric($componentConsumption['btu']);
        $statistics['rack_watt'] = $locales->fmt_numeric($rackConsumption['watt']);
        $statistics['rack_btu'] = $locales->fmt_numeric($rackConsumption['btu']);
        $statistics['total_watt'] = $locales->fmt_numeric($componentConsumption['watt'] + $rackConsumption['watt']);
        $statistics['total_btu'] = $locales->fmt_numeric($componentConsumption['btu'] + $rackConsumption['btu']);

        isys_application::instance()->container->get('template')
            ->assign('stats', $statistics)
            ->display("file:" . $this->m_smarty_dir . "templates/ajax/rack_statistics.tpl");

        $this->_die();
    }

    /**
     * Method which returns an array with connector statistics of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_connector_statistics($p_obj_id)
    {
        $l_return = [];

        $lang = isys_application::instance()->container->get('language');

        $l_res = isys_cmdb_dao_category_g_connector::instance($this->m_database_component)
            ->get_data(null, $p_obj_id);
        $l_plug = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_database_component);

        $l_return['conns_num'] = $l_res->num_rows();

        while ($l_row = $l_res->get_row()) {
            $l_plug_title = $l_plug->get_data($l_row['isys_catg_connector_list__isys_connection_type__id']);
            $l_plug_title = $lang->get($l_plug_title['isys_connection_type__title']);

            $l_inout = '_in';
            if ($l_row['isys_catg_connector_list__type'] == C__CONNECTOR__OUTPUT) {
                $l_inout = '_out';
            }

            if (empty($l_plug_title)) {
                $l_plug_title = '<em class="text-normal">' . $lang->get('LC_UNIVERSAL__NOT_SPECIFIED') . '</em>';
            }

            if ($l_row['con_connector'] > 0) {
                $l_return['conns'][$l_plug_title]['used' . $l_inout]++;
            } else {
                $l_return['conns'][$l_plug_title]['free' . $l_inout]++;
            }
        }

        return $l_return;
    }

    /**
     * Method for calculating the energy consumption inside an object and it's children (recursive).
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function get_consumption_of_electricity($p_obj_id)
    {
        $l_return = [
            'watt' => 0,
            'btu'  => 0
        ];

        $l_dao = isys_cmdb_dao::instance($this->m_database_component);
        $l_sql = "SELECT
			isys_catg_pc_list__watt, isys_catg_pc_list__btu, content_obj.isys_obj__id, isys_catg_pc_list__active, content_type.isys_obj_type__container
			FROM isys_catg_location_list rack_loc
			LEFT JOIN isys_obj rack_obj ON rack_loc.isys_catg_location_list__isys_obj__id = rack_obj.isys_obj__id
			INNER JOIN isys_catg_location_list content_loc ON content_loc.isys_catg_location_list__parentid = rack_obj.isys_obj__id
			INNER JOIN isys_obj content_obj ON content_loc.isys_catg_location_list__isys_obj__id = content_obj.isys_obj__id
			INNER JOIN isys_obj_type content_type ON content_obj.isys_obj__isys_obj_type__id = content_type.isys_obj_type__id
			LEFT JOIN isys_catg_pc_list ON isys_catg_pc_list__isys_obj__id = content_loc.isys_catg_location_list__isys_obj__id
			WHERE (rack_loc.isys_catg_location_list__status = 2)
			AND rack_obj.isys_obj__id = " . $l_dao->convert_sql_id($p_obj_id) . ";";

        $l_res = $l_dao->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                if ($l_row['isys_catg_pc_list__active']) {
                    $l_return['watt'] += $l_row['isys_catg_pc_list__watt'];
                    $l_return['btu'] += $l_row['isys_catg_pc_list__btu'];
                }

                // We found a container object, so we check inside for more objects.
                if ($l_row['isys_obj_type__container'] == 1) {
                    $l_recursive_call = $this->get_consumption_of_electricity($l_row['isys_obj__id']);

                    $l_return['watt'] += $l_recursive_call['watt'];
                    $l_return['btu'] += $l_recursive_call['btu'];
                }
            }
        }

        return $l_return;
    }

    /**
     * Get energy consumption by object
     *
     * @param $objectId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getOwnConsumptionOfElectricity($objectId) {
        // Consumption data
        $consumptionData = [
            'watt' => 0,
            'btu' => 0,
        ];

        // Create dao instance
        $dao = isys_cmdb_dao::instance($this->m_database_component);

        // SQL for retrieving needed data
        $sql = '
            SELECT SUM(isys_catg_pc_list__watt) AS total_watt, SUM(isys_catg_pc_list__btu) AS total_btu
            FROM isys_obj obj
            INNER JOIN isys_catg_location_list location ON location.isys_catg_location_list__isys_obj__id = obj.isys_obj__id
            INNER JOIN isys_catg_pc_list       pc       ON pc.isys_catg_pc_list__isys_obj__id             = obj.isys_obj__id
            WHERE location.isys_catg_location_list__status = ' . C__RECORD_STATUS__NORMAL . '
            AND                           obj.isys_obj__id = ' . $dao->convert_sql_id($objectId) . '
            AND 
            (
                    pc.isys_catg_pc_list__status = ' . C__RECORD_STATUS__NORMAL . ' AND 
                    pc.isys_catg_pc_list__active = 1
            )
        ';

        // Get resource and check for existing result set
        $resource = $dao->retrieve($sql);
        if (is_countable($resource) && count($resource) > 0) {
            // Get result and add it to consumption data
            $row = $resource->get_row();

            $consumptionData['watt']   += $row['total_watt'];
            $consumptionData['btu']    += $row['total_btu'];
        }

        return $consumptionData;
    }

    /**
     * Method which returns an array with port statistics of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function get_port_statistics($p_obj_id)
    {
        $l_return = [];

        $lang = isys_application::instance()->container->get('language');

        $l_res = isys_cmdb_dao_category_g_network_port::instance($this->m_database_component)
            ->get_data(null, $p_obj_id);
        $l_plug = isys_factory_cmdb_dialog_dao::get_instance('isys_connection_type', $this->m_database_component);

        $l_return['ports_num'] = $l_res->num_rows();

        while ($l_row = $l_res->get_row()) {
            $l_plug_title = $l_plug->get_data($l_row['isys_catg_port_list__isys_plug_type__id']);
            $l_plug_title = $lang->get($l_plug_title['isys_connection_type__title']);

            if (empty($l_plug_title)) {
                $l_plug_title = '<em class="text-normal">' . $lang->get('LC_UNIVERSAL__NOT_SPECIFIED') . '</em>';
            }

            if ($l_row['con_connector'] > 0) {
                $l_return['ports'][$l_plug_title]['used']++;
            } else {
                $l_return['ports'][$l_plug_title]['free']++;
            }
        }

        return $l_return;
    }
}
