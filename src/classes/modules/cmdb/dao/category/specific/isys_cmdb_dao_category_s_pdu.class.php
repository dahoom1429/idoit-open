<?php

/**
 * i-doit
 *
 * DAO: specific category for power device units (PDU).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_pdu extends isys_cmdb_dao_category_specific
{

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'pdu';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_pdu_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_pdu_list__isys_obj__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_pdu_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_cats_pdu_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'pdu_id'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'PDU',
                    C__PROPERTY__INFO__DESCRIPTION => 'PDU'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_list__pdu_id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__PDU__PDU_ID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PDU', 'C__CATS__PDU')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_pdu_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save($p_category_data['data_id'], $p_category_data['properties']['pdu_id'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE], C__RECORD_STATUS__NORMAL);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * @param integer $p_cat_level
     * @param integer &$p_intOldRecStatus
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {

        $l_catdata = $this->get_general_data();

        $p_status = $l_catdata["isys_cats_pdu_list__status"];
        $l_list_id = $l_catdata["isys_cats_pdu_list__id"];

        $l_bRet = $this->save($l_list_id, $_POST["C__CMDB__CATS__PDU__PDU_ID"], $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            $p_status);

        if (isset($_POST["C__CMDB__CATS__PDU__SNMP_QUERIES"])) {
            isys_tenantsettings::set('snmp.pdu.queries', $_POST["C__CMDB__CATS__PDU__SNMP_QUERIES"] ? 1 : 0);
            isys_tenantsettings::force_save();
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_id
     * @param   array   $p_pdu_id
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_pdu_id, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'UPDATE isys_cats_pdu_list SET
			isys_cats_pdu_list__pdu_id = ' . $this->convert_sql_id($p_pdu_id) . ',
			isys_cats_pdu_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_pdu_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_cats_pdu_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   array   $p_object_id
     * @param   integer $p_pdu_id
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_pdu_id, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'INSERT IGNORE INTO isys_cats_pdu_list SET
			isys_cats_pdu_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_cats_pdu_list__pdu_id = ' . $this->convert_sql_id($p_pdu_id) . ',
			isys_cats_pdu_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_pdu_list__status = ' . $this->convert_sql_id($p_status) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Create element method.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_new_id
     *
     * @return  null
     */
    public function attachObjects(array $p_post)
    {
        $p_new_id = $this->create($_GET[C__CMDB__GET__OBJECT], 1, '');

        return null;
    }
}