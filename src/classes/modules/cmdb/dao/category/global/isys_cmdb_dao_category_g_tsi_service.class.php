<?php

/**
 * i-doit
 *
 * DAO: Global category for TSI service
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_tsi_service extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'tsi_service';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_tsi_service_list " . "INNER JOIN isys_obj " . "ON " . "isys_catg_tsi_service_list__isys_obj__id = " . "isys_obj__id " .
            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_tsi_service_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_tsi_service_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'tsi_service_id' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TSI_SERVICE__TSI_SERVICE_ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'TSI service ID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_tsi_service_list__tsi_service_id'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TSI_SERVICE__TSI_SERVICE_ID'
                ]
            ]),
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_tsi_service_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__TSI_SERVICE', 'C__CATG__TSI_SERVICE'),
                ]
            ])
        ];
    }

    /**
     * Synchronize category content with $p_data
     *
     * @param array $p_data
     *
     * @return bool
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create($p_object_id);
                if ($p_category_data['data_id'] > 0) {
                    $l_indicator = true;
                }
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_category_data['data_id'], $p_category_data['properties']['tsi_service_id'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE], C__RECORD_STATUS__NORMAL);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Trigger save process of global category model
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_intErrorCode = -1; // ErrorCode

        $l_catdata = $this->get_general_data();

        $l_id = null;

        if ($l_catdata) {
            $p_intOldRecStatus = $l_catdata["isys_catg_tsi_service_list__status"];
            $l_id = $l_catdata["isys_catg_tsi_service_list__id"];
        }

        if (empty($l_id)) {
            $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, null);
        }

        $l_bRet = $this->save($l_id, $_POST["C__CATG__TSI_SERVICE__TSI_SERVICE_ID"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()], C__RECORD_STATUS__NORMAL);

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_manufacturerID
     * @param int    $p_titleID
     * @param String $p_productid
     * @param String $p_serial
     * @param String $p_firmware
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     */
    public function save($p_cat_level, $p_tsi_service_id = null, $p_description = null, $p_status = C__RECORD_STATUS__NORMAL)
    {

        $l_strSql = "UPDATE isys_catg_tsi_service_list SET " . "isys_catg_tsi_service_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_catg_tsi_service_list__tsi_service_id = " . $this->convert_sql_text($p_tsi_service_id) . ", " . "isys_catg_tsi_service_list__status = " .
            $this->convert_sql_int($p_status) . " " . "WHERE isys_catg_tsi_service_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_model__id $p_fk_id
     *
     * @param int    $p_fk_id
     * @param int    $p_manufacturerID
     * @param int    $p_titleID
     * @param String $p_productid
     * @param String $p_serial
     * @param String $p_firmware
     * @param String $p_description
     *
     * @return int the newly created ID or false
     */
    public function create($p_objID, $p_tsi_service_id = null, $p_description = null)
    {
        $l_strSql = "INSERT IGNORE INTO isys_catg_tsi_service_list SET " . "isys_catg_tsi_service_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_catg_tsi_service_list__tsi_service_id = " . $this->convert_sql_text($p_tsi_service_id) . ", " . "isys_catg_tsi_service_list__status = " .
            C__RECORD_STATUS__NORMAL . ", " . "isys_catg_tsi_service_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

}