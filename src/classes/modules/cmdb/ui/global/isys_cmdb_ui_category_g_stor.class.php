<?php

/**
 * i-doit
 *
 * CMDB UI: Global category storage
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Niclas Potthast <npotthast@i-doit.org> - 2006-06-16
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_stor extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_stor $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_devices = [];
        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_nStorID = $l_gets[C__CMDB__GET__CATLEVEL];
        $l_dao_ctrl = new isys_cmdb_dao_category_g_stor($this->get_database_component());
        $l_dao_raid = new isys_cmdb_dao_category_g_raid($this->get_database_component());

        $l_catdata = $p_cat->get_general_data();

        if (!$l_nStorID) {
            $l_nStorID = $_POST[C__GET__ID][0];
        }

        // ID.
        $l_rules["C__CATG__STORAGE"]["p_strValue"] = $l_nStorID;
        $l_rules["C__CATG__STORAGE_TYPE"]["p_strTable"] = "isys_stor_type";
        $l_rules["C__CATG__STORAGE_MANUFACTURER"]["p_strTable"] = "isys_stor_manufacturer";
        $l_rules["C__CATG__STORAGE_MODEL"]["p_strTable"] = "isys_stor_model";
        $l_rules["C__CATG__STORAGE_UNIT"]["p_strTable"] = "isys_memory_unit";
        $l_rules["C__CATG__STORAGE_CONNECTION_TYPE"]["p_strTable"] = "isys_stor_con_type";

        // Show controllers in combo box.
        $l_res = $l_dao_ctrl->get_controller_by_object_id($l_gets[C__CMDB__GET__OBJECT]);

        if ($l_res && $l_res->num_rows()) {
            $l_controllers = [];

            while ($l_row = $l_res->get_row()) {
                $l_controllers[$l_row["isys_catg_controller_list__id"]] = $l_row["isys_catg_controller_list__title"];
            }

            $l_rules["C__CATG__STORAGE_CONTROLLER"]["p_arData"] = $l_controllers;
        }

        // Show RAID-Group (for Hard disks...).
        // MUSS NOCH DIE KONSTANTE C__CMDB__RAID_TYPE__HARDWARE ANGELEGT WERDEN
        $l_res = $l_dao_raid->get_raids(null, 1, $_GET[C__CMDB__GET__OBJECT]);

        while ($l_row = $l_res->get_row()) {
            $l_devices[$l_row["isys_catg_raid_list__id"]] = $l_row["isys_catg_raid_list__title"];
        }

        $l_rules["C__CATG__STORAGE_RAIDGROUP"]["p_arData"] = $l_devices;

        // Show group of hard disks (for RAID-Groups).
        $l_arHDAll = [];
        $l_arHDSelected = [];
        $l_arHDAlloc = [];

        $l_res = $l_dao_ctrl->get_devices(null, $l_gets[C__CMDB__GET__OBJECT], null, defined_or_default('C__STOR_TYPE_DEVICE_HD'));

        if ($l_res && $l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                $l_arHDAll[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"];
            }
        }

        // Show hard disks connected to a specific RAID pool.
        $l_res = $l_dao_ctrl->get_devices(null, $l_gets[C__CMDB__GET__OBJECT], $l_nStorID, defined_or_default('C__STOR_TYPE_DEVICE_HD'));

        $l_num_disks = $l_res->num_rows();

        if ($l_res && $l_num_disks) {
            while ($l_row = $l_res->get_row()) {
                if ($l_row["isys_catg_stor_list__hotspare"] == "1") {
                    $l_num_disks--;
                    $l_arHDSelected[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"] . " (Hotspare)";
                } else {
                    $l_arHDSelected[$l_row["isys_catg_stor_list__id"]] = $l_row["isys_catg_stor_list__title"];
                }

                if (!isset($l_min_capacity) ||
                    isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD) <= $l_min_capacity) {
                    $l_min_capacity = isys_convert::memory($l_row["isys_catg_stor_list__capacity"], "C__MEMORY_UNIT__GB", C__CONVERT_DIRECTION__BACKWARD);
                }
            }
        }

        // Change the arrays for the dialogue list.
        foreach ($l_arHDAll as $key => $val) {
            $l_arHDAlloc[] = [
                "id"  => "$key",
                "val" => "$val",
                "sel" => 0,
                "url" => ""
            ];
        }

        foreach ($l_arHDSelected as $key => $val) {
            $l_arHDAlloc[] = [
                "id"  => "$key",
                "val" => "$val",
                "sel" => 1,
                "url" => ""
            ];
        }

        $l_rules["C__CATG__STORAGE_CONNECTION"]["p_arData"] = $l_arHDAlloc;

        // Evaluate SAN-Pools
        $l_res = $l_dao_ctrl->get_san_pools();

        if ($l_res && $l_res->num_rows()) {
            $l_pools = [];

            while ($l_row = $l_res->get_row()) {
                $l_pool_res = $l_dao_ctrl->get_san_pool_parent($l_row["isys_catg_sanpool_list__id"]);

                if ($l_pool_res && $l_pool_res->num_rows()) {
                    $l_pool_parent_row = $l_pool_res->get_row();
                    $l_parent_name = $l_pool_parent_row["isys_obj__title"] . " - ";
                } else {
                    $l_parent_name = "";
                }

                $l_pools[$l_row["isys_catg_sanpool_list__id"]] = $l_parent_name . $l_row["isys_catg_sanpool_list__title"];
            }

            $l_rules["C__CATG__STORAGE_SANPOOL"]["p_arData"] = $l_pools;
        }

        $l_rules["C__CATG__STORAGE_RAIDLEVEL"]["p_strTable"] = "isys_stor_raid_level";
        $l_rules["C__CATG__STORAGE_HOTSPARE"]["p_arData"] = get_smarty_arr_YES_NO();

        if ($l_nStorID > 0) {
            // Fill in the values from the db.
            $l_catdata["isys_catg_stor_list__capacity"] = isys_convert::memory($l_catdata["isys_catg_stor_list__capacity"],
                $l_catdata["isys_catg_stor_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

            $l_rules["C__CATG__STORAGE_HOTSPARE"]["p_strSelectedID"] = (isset($l_catdata["isys_catg_stor_list__hotspare"])) ? $l_catdata["isys_catg_stor_list__hotspare"] : 0;
            $l_rules["C__CATG__STORAGE_CAPACITY"]["p_strValue"] = $l_catdata["isys_catg_stor_list__capacity"];
            $l_rules["C__CATG__STORAGE_TITLE"]["p_strValue"] = $l_catdata["isys_catg_stor_list__title"];
            $l_rules["C__CATG__STORAGE_TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_type__id"];
            $l_rules["C__CATG__STORAGE_MANUFACTURER"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_manufacturer__id"];
            $l_rules["C__CATG__STORAGE_MODEL"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_model__id"];
            $l_rules["C__CATG__STORAGE_UNIT"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_memory_unit__id"];
            $l_rules["C__CATG__STORAGE_CONNECTION_TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_con_type__id"];
            $l_rules["C__CATG__STORAGE_CONTROLLER"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_catg_controller_list__id"];
            $l_rules["C__CATG__STORAGE_RAIDGROUP"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_catg_raid_list__id"];
            $l_rules["C__CATG__STORAGE_RAIDLEVEL"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_raid_level__id"];
            $l_rules["C__CATG__STORAGE_CONNECTION"]["p_strValue"] = $l_catdata[""];
            $l_rules["C__CATG__STORAGE_SANPOOL"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_catg_sanpool_list__id"];
            $l_rules["C__CATG__STORAGE_SERIAL"]["p_strValue"] = $l_catdata["isys_catg_stor_list__serial"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_stor_list__description"];
            $l_rules["C__CATG__STORAGE_LTO_TYPE"]["p_strSelectedID"] = $l_catdata["isys_catg_stor_list__isys_stor_lto_type__id"];
            $l_rules["C__CATG__STORAGE_FC_ADDRESS"]["p_strValue"] = $l_catdata["isys_catg_stor_list__fc_address"];
            $l_rules["C__CATG__STORAGE_FIRMWARE"]["p_strValue"] = $l_catdata["isys_catg_stor_list__firmware"];

            $l_rules["C__CATG__STORAGE_CAPACITY"]["p_strValue"] = isys_convert::formatNumber($l_rules["C__CATG__STORAGE_CAPACITY"]["p_strValue"]);
        }

        $l_rules["C__CATG__STORAGE_CONNECTION"]["p_bLinklist"] = "1";

        // Apply rules
        $this->get_template_component()
            ->assign('stor_id', $l_nStorID)
            ->assign('new_catg_stor', (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__NEW ||
                isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
