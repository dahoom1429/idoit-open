<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_ajax_handler_ocs_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @global  isys_component_database $g_comp_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [];

        switch ($_GET['func']) {
            case 'object_list':
                $l_return = $this->get_ocs_object_list($_POST['ocs_id']);
                break;
        }

        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     *
     * @param   integer $p_db_id
     *
     * @return  array
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_locale
     */
    private function get_ocs_object_list($p_db_id)
    {
        global $g_comp_database;

        $l_dao_comp = new isys_component_dao_ocs($g_comp_database);
        $l_settings = $l_dao_comp->getOCSDB($p_db_id);

        try {
            if ($l_settings == null) {
                throw new Exception("Es wurde keine Datenbank angegeben");
            }

            $l_ocsdb = isys_component_database::get_database("mysqli", $l_settings["isys_ocs_db__host"], $l_settings["isys_ocs_db__port"], $l_settings["isys_ocs_db__user"],
                isys_helper_crypt::decrypt($l_settings["isys_ocs_db__pass"]), $l_settings["isys_ocs_db__schema"]);

        } catch (Exception $e) {
            return null;
        }

        $l_daoOCS = new isys_component_dao_ocs($l_ocsdb);

        $l_inventory = $l_daoOCS->getHardware();

        $l_ocsObj = $l_prefix_arr = [];

        $l_possible_prefixes = filter_array_by_value_of_defined_constants([
            'server'  => 'C__OBJTYPE__SERVER',
            'client'  => 'C__OBJTYPE__CLIENT',
            'router'  => 'C__OBJTYPE__ROUTER',
            'switch'  => 'C__OBJTYPE__SWITCH',
            'printer' => 'C__OBJTYPE__PRINTER'
        ]);

        foreach ($l_possible_prefixes AS $l_prefix => $l_objtype) {
            $l_tag_prefix = isys_tenantsettings::get('ocs.prefix.' . $l_prefix);

            if ($l_tag_prefix) {
                $l_prefix_arr[$l_tag_prefix] = $l_objtype;
            }
        }

        while ($l_row = $l_inventory->get_row()) {
            $l_sql = "SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = " . $l_dao_comp->convert_sql_text($l_row["NAME"]) . ";";

            $l_data_res = $l_dao_comp->retrieve($l_sql);
            $l_row['imported'] = null;
            $l_row['objtype'] = null;

            if ($l_data_res->num_rows() > 0) {
                $l_data = $l_data_res->get_row();
                $l_row['imported'] = isys_locale::get_instance()
                    ->fmt_date($l_data['isys_obj__imported']);
                $l_row['objtype'] = $l_data['isys_obj__isys_obj_type__id'];
            } else if (isset($l_prefix_arr[$l_row['TAG']])) {
                $l_row['objtype'] = $l_prefix_arr[$l_row['TAG']];
            }

            $l_row["snmp"] = 0;

            $l_ocsObj[] = [
                'ID'       => $l_row['ID'],
                'TAG'      => $l_row['TAG'],
                'NAME'     => $l_row['NAME'],
                'OSNAME'   => $l_row['OSNAME'],
                'IPADDR'   => $l_row['IPADDR'],
                'imported' => $l_row['imported'],
                'objtype'  => $l_row['objtype']
            ];
        }

        try {
            if ($l_daoOCS->does_snmp_exist()) {
                $l_snmp_inventory = $l_daoOCS->getHardwareSnmp();
                $l_already_set = [];
                while ($l_row = $l_snmp_inventory->get_row()) {
                    if (isset($l_already_set[$l_row['NAME']])) {
                        continue;
                    }

                    $l_already_set[$l_row['NAME']] = true;
                    $l_data_res = $l_dao_comp->retrieve("SELECT isys_obj__imported, isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__hostname = " .
                        $l_dao_comp->convert_sql_text($l_row["NAME"]) . ";");
                    $l_row["imported"] = null;
                    $l_row['objtype'] = null;

                    if ($l_data_res->num_rows() > 0) {
                        $l_data = $l_data_res->get_row();
                        $l_imported = $l_data['isys_obj__imported'];
                        $l_row["imported"] = isys_locale::get_instance()
                            ->fmt_date($l_imported);
                        $l_row['objtype'] = $l_data['isys_obj__isys_obj_type__id'];
                    } else if (isset($l_prefix_arr[$l_row['TAG']])) {
                        $l_row['objtype'] = $l_prefix_arr[$l_row['TAG']];
                    }

                    $l_row["snmp"] = 1;

                    $l_ocsObj[] = [
                        'ID'       => $l_row['ID'],
                        'TAG'      => $l_row['TAG'],
                        'NAME'     => $l_row['NAME'],
                        'OSNAME'   => $l_row['OSNAME'],
                        'IPADDR'   => $l_row['IPADDR'],
                        'imported' => $l_row['imported'],
                        'objtype'  => $l_row['objtype'],
                        'snmp'     => $l_row['snmp']
                    ];
                }
            }
        } catch (Exception $e) {
            // Older OCS Inventory Version
        }

        return $l_ocsObj;
    }
}