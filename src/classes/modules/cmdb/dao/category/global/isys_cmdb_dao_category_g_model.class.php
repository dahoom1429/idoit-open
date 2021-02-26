<?php

use idoit\Component\Property\Type\DialogPlusDependencyChildProperty;
use idoit\Component\Property\Type\DialogPlusDependencyParentProperty;

/**
 * i-doit
 *
 * DAO: global category for models
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_model extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'model';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Callback method to get the model manufacturer id.
     *
     * @param   isys_request $p_request
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_title_ui_params_secTableID(isys_request $p_request)
    {
        $l_cat_id = $p_request->get_category_data_id();
        $l_obj_id = $p_request->get_object_id();

        if ($l_cat_id > 0) {
            return $this->get_data($l_cat_id)
                ->get_row_value('isys_catg_model_list__isys_model_manufacturer__id');
        } elseif ($l_obj_id > 0) {
            return $this->get_data(null, $l_obj_id)
                ->get_row_value('isys_catg_model_list__isys_model_manufacturer__id');
        } else {
            return -1;
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_model_list
			INNER JOIN isys_obj ON isys_catg_model_list__isys_obj__id = isys_obj__id
			LEFT OUTER JOIN isys_model_title ON isys_model_title__id = isys_catg_model_list__isys_model_title__id
			LEFT OUTER JOIN isys_model_manufacturer ON isys_model_manufacturer__id = isys_catg_model_list__isys_model_manufacturer__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_model_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_model_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'manufacturer' => new DialogPlusDependencyParentProperty(
                'C__CATG__MODEL_MANUFACTURER',
                'LC__CATG__STORAGE_MANUFACTURER',
                'isys_catg_model_list__isys_model_manufacturer__id',
                'isys_catg_model_list',
                'isys_model_manufacturer',
                'isys_model_title',
                'C__CATG__MODEL_TITLE_ID'
            ),
            'title' => new DialogPlusDependencyChildProperty(
                'C__CATG__MODEL_TITLE_ID',
                'LC__CMDB__CATG__MODEL',
                'isys_catg_model_list__isys_model_title__id',
                'isys_catg_model_list',
                'isys_model_title',
                'isys_model_manufacturer',
                'isys_cmdb_dao_category_g_model::manufacturer',
                'C__CATG__MODEL_MANUFACTURER',
                new isys_callback([
                    'isys_cmdb_dao_category_g_model',
                    'callback_property_title_ui_params_secTableID'
                ]),
                [
                    'isys_export_helper',
                    'model_title'
                ]
            ),
            'productid'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MODEL_PRODUCTID',
                    C__PROPERTY__INFO__DESCRIPTION => 'Product ID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_model_list__productid'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__MODEL_PRODUCTID'
                ]
            ]),
            'service_tag'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MODEL_SERVICE_TAG',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service tag'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_model_list__service_tag'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__MODEL_SERVICE_TAG'
                ]
            ]),
            'serial'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Serial number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_model_list__serial',
                    C__PROPERTY__DATA__INDEX => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__MODEL_SERIAL'
                ]
            ]),
            'firmware'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__FIRMWARE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Firmware'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_model_list__firmware'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__MODEL_FIRMWARE'
                ]
            ]),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_model_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__MODEL', 'C__CATG__MODEL')
                ]
            ])
        ];
    }

    /**
     * Synchronize category content with $p_data.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['productid'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['service_tag'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            $p_category_data['properties']['manufacturer'][C__DATA__VALUE],
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['productid'][C__DATA__VALUE],
                            $p_category_data['properties']['serial'][C__DATA__VALUE],
                            $p_category_data['properties']['firmware'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['service_tag'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Import-Handler for this category
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function import($p_data)
    {
        if (is_array($p_data)) {
            $l_bios = $p_data["bios"] ?: null;
            $l_battery_data = $p_data["battery"] ?: null;
            $p_data = $p_data[0] ?: null;
        } else {
            $l_bios = $l_battery_data = null;
        }

        if (is_array($p_data)) {
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];
            $l_list_id = $this->create_connector($this->get_table(), $l_object_id);

            if ($l_list_id > 0) {
                if ($l_battery_data) {
                    $l_battery = "\nBattery: " . $l_battery_data["name"] . " (" . $l_battery_data["description"] . ")";
                }

                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()] = "System: " . $p_data["systemtype"] . "\n" . "BIOS: " .
                    trim($l_bios["manufacturer"]) . " (" . trim($l_bios["name"]) . ")" . $l_battery;

                // Manufacturer.
                $_POST['C__CATG__MODEL_MANUFACTURER'] = isys_import::check_dialog("isys_model_manufacturer", $p_data["manufacturer"]);

                // Title.
                $l_title = $p_data["model"];
                if (!empty($p_data["systemtype"])) {
                    $l_title .= " (" . $p_data["systemtype"] . ")";
                }

                $_POST['C__CATG__MODEL_TITLE_ID'] = isys_import::check_dialog("isys_model_title", $l_title, null, $_POST['C__CATG__MODEL_MANUFACTURER']);

                // Various.
                $_POST['C__CATG__MODEL_SERIAL'] = $l_bios["serialnumber"];
                $_POST['C__CATG__MODEL_FIRMWARE'] = ($p_data["firmware"]) ? $p_data["firmware"] : trim($l_bios["name"]);

                // Save.
                $this->save(
                    $l_list_id,
                    $_POST['C__CATG__MODEL_MANUFACTURER'],
                    $_POST['C__CATG__MODEL_TITLE_ID'],
                    null,
                    $_POST['C__CATG__MODEL_SERIAL'],
                    $_POST['C__CATG__MODEL_FIRMWARE'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );
            }
        }

        return $l_list_id;
    }

    /**
     * Trigger save process of global category model.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     * @return int|mixed|null
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_model_list__status"];

        if ($l_catdata["isys_catg_model_list__id"] != "") {
            $l_bRet = $this->save(
                $l_catdata["isys_catg_model_list__id"],
                $_POST['C__CATG__MODEL_MANUFACTURER'],
                $_POST['C__CATG__MODEL_TITLE_ID'],
                $_POST["C__CATG__MODEL_PRODUCTID"],
                $_POST['C__CATG__MODEL_SERIAL'],
                $_POST['C__CATG__MODEL_FIRMWARE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__MODEL_SERVICE_TAG']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? null : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param   integer $p_cat_level
     * @param   integer $p_manufacturerID
     * @param   integer $p_titleID
     * @param   string  $p_productid
     * @param   string  $p_serial
     * @param   string  $p_firmware
     * @param   string  $p_description
     * @param   string  $p_service_tag
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_manufacturerID, $p_titleID, $p_productid, $p_serial, $p_firmware, $p_description, $p_service_tag = null)
    {
        $l_strSql = "UPDATE isys_catg_model_list SET
			isys_catg_model_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_model_list__isys_model_manufacturer__id  = " . $this->convert_sql_id($p_manufacturerID) . ",
			isys_catg_model_list__isys_model_title__id  = " . $this->convert_sql_id($p_titleID) . ",
			isys_catg_model_list__productid  = " . $this->convert_sql_text($p_productid) . ",
			isys_catg_model_list__serial  = " . $this->convert_sql_text($p_serial) . ",
			isys_catg_model_list__firmware  = " . $this->convert_sql_text($p_firmware) . ",
			isys_catg_model_list__service_tag  = " . $this->convert_sql_text($p_service_tag) . ",
			isys_catg_model_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE isys_catg_model_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Executes the query to create a new category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_manufacturerID
     * @param   integer $p_titleID
     * @param   string  $p_productid
     * @param   string  $p_serial
     * @param   string  $p_firmware
     * @param   string  $p_description
     * @param   string  $p_service_tag
     *
     * @return  mixed
     */
    public function create(
        $p_objID,
        $p_manufacturerID = null,
        $p_titleID = null,
        $p_productid = null,
        $p_serial = null,
        $p_firmware = null,
        $p_description = null,
        $p_service_tag = null
    ) {
        $l_id = $this->create_connector('isys_catg_model_list', $p_objID);
        if ($this->save($l_id, $p_manufacturerID, $p_titleID, $p_productid, $p_serial, $p_firmware, $p_description, $p_service_tag)) {
            return $l_id;
        }

        return false;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param   array $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        $l_title = $l_manufacturer = null;

        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_data['manufacturer']);
        }

        if (!empty($p_data['title'])) {
            $l_title = isys_import_handler::check_dialog('isys_model_title', $p_data['title'], null, $l_manufacturer);
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'title'        => [
                    'value' => $l_title
                ],
                'serial'       => [
                    'value' => $p_data['serial']
                ],
                'productid'    => [
                    'value' => $p_data['productid']
                ],
                'firmware'     => [
                    'value' => $p_data['firmware']
                ],
                'service_tag'  => [
                    'value' => $p_data['service_tag']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }
}
