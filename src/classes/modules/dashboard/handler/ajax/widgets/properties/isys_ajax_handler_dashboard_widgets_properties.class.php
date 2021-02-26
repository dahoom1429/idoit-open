<?php

/**
 * Ajax handler for widget properties
 *
 * @package       i-doit
 * @subpackage    General
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @version       1.0
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since         1.2.0
 */
class isys_ajax_handler_dashboard_widgets_properties extends isys_ajax_handler_dashboard
{
    /**
     * Dao object
     *
     * @var isys_cmdb_dao
     */
    private $m_dao = '';

    /**
     * Iniit method
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

        // CMDB dao property
        $this->m_dao = isys_cmdb_dao_category_property_ng::instance($this->m_database_component);

        $l_object_ids = isys_format_json::decode($_POST[C__CMDB__GET__OBJECT]);

        // Creates the list query
        if (count($l_object_ids) > 0) {
            try {
                $l_new_arr = [];
                foreach ($l_object_ids AS $l_id) {
                    $l_new_arr[] = [
                        'isys_obj__id' => $l_id
                    ];
                }

                $l_return['data']['list_query'] = $this->m_dao->create_property_query($_POST['properties'], $l_new_arr, true, true);
            } catch (Exception $e) {
                $l_return['success'] = false;
            }
        }

        // This part builds the config for the list
        $l_properties_arr = isys_format_json::decode($_POST['properties']);
        if (count($l_properties_arr) > 0) {
            $l_g_distributor = new isys_cmdb_dao_distributor($this->m_database_component, 1, C__CMDB__CATEGORY__TYPE_GLOBAL);
            $l_s_distributor = new isys_cmdb_dao_distributor($this->m_database_component, 1, C__CMDB__CATEGORY__TYPE_SPECIFIC);
            $l_list_config = [];

            foreach ($l_properties_arr as $l_category_info) {
                $l_category_info = (array)$l_category_info;
                foreach ($l_category_info as $l_cat_type => $l_category) {
                    $l_category = (array)$l_category;
                    foreach ($l_category as $l_cat_const => $l_selected_property) {
                        $l_selected_property = (array)$l_selected_property;
                        if ($l_cat_type == 'g') {
                            $l_category = $l_g_distributor->get_category(constant($l_cat_const));
                        } else if ($l_cat_type == 's') {
                            $l_category = $l_s_distributor->get_category(constant($l_cat_const));
                        }

                        if (is_object($l_category) && method_exists($l_category, 'get_properties_ng')) {
                            $l_sel_prop = array_pop($l_selected_property);
                            if (strpos($l_sel_prop, '_') !== 0) {
                                $l_properties = $l_category->get_properties();
                                $l_method_info = get_class($l_category) . '::' . 'get_properties_ng';
                                $l_property_type = C__PROPERTY_TYPE__STATIC;

                                if (!isset($l_properties[$l_sel_prop][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                                    $l_field = $l_properties[$l_sel_prop][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                } else {
                                    $l_field = $l_properties[$l_sel_prop][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                                }

                                $l_callback = false;
                            } else {
                                $l_properties = $l_category->get_dynamic_properties();
                                $l_method_info = get_class($l_category) . '::' . 'get_dynamic_properties';
                                $l_property_type = C__PROPERTY_TYPE__DYNAMIC;
                                $l_field = false;
                                $l_callback = [
                                    get_class($l_properties[$l_sel_prop][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]),
                                    $l_properties[$l_sel_prop][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                ];
                            }

                            $l_list_config[] = [
                                $l_property_type,
                                $l_sel_prop,
                                $l_field,
                                $l_properties[$l_sel_prop][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
                                $l_method_info,
                                $l_callback
                            ];
                        }
                    }
                }
            }
            $l_return['data']['list_config'] = $l_list_config;
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
}