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
 * @since       1.4.7
 */
class isys_ajax_handler_location extends isys_ajax_handler
{
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
            'data'    => null,
            'message' => null
        ];

        try {
            switch ($_GET['func']) {
                case 'get_logical_physical_path':
                    $l_return['data'] = $this->get_logical_physical_path($_POST[C__CMDB__GET__OBJECT]);
                    break;

                case 'get_location_path':
                    $l_return['data'] = $this->get_location_path($_POST[C__CMDB__GET__OBJECT]);
                    break;

                case 'get_geo_coordinates_from_object':
                    $l_return['data'] = $this->get_geo_coordinates_from_object($_POST[C__CMDB__GET__OBJECT]);
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
     * This method will return the logical physical path by object IDs.
     *
     * @param $p_obj_id
     *
     * @return mixed
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_logical_physical_path($p_obj_id)
    {
        return isys_cmdb_dao_category_g_logical_unit::instance($this->m_database_component)
            ->get_logical_physical_path($p_obj_id);
    }

    /**
     * This method will return the location path by object IDs. For example [512, 42, 532, 34, 20, 1]
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_location_path($p_obj_id)
    {
        return isys_cmdb_dao_category_g_location::instance($this->m_database_component)
            ->get_location_path($p_obj_id);
    }

    /**
     * This method will return the latitude and longitude of a given object.
     *
     * @param integer $p_obj_id
     *
     * @since  i-doit 1.8.1
     * @author Leonard Fischer <lfischer@i-doit.com>
     * @return array
     */
    protected function get_geo_coordinates_from_object($p_obj_id)
    {
        $l_row = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->database)
            ->get_data(null, $p_obj_id)
            ->get_row();

        return [
            'latitude'          => $l_row['latitude'],
            'longitude'         => $l_row['longitude'],
            'hasGeoCoordinates' => is_numeric($l_row['latitude']) && is_numeric($l_row['longitude'])
        ];
    }
}