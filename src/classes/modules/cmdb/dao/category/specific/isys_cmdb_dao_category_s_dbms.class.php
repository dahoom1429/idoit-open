<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: specific category for database management systems (DBMS).
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_dbms extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'dbms';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

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
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_dbms_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_dbms_list__isys_obj__id
			INNER JOIN isys_dbms ON isys_dbms__id = isys_cats_dbms_list__isys_dbms__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_dbms_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_cats_dbms_list__status = ' . $this->convert_sql_int($p_status);
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
            'dbms' => (new DialogPlusProperty(
                'C__CMDB__CATS__DBMS',
                'DBMS',
                'isys_cats_dbms_list__isys_dbms__id',
                'isys_cats_dbms_list',
                'isys_dbms'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_dbms_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__DBMS', 'C__CATS__DBMS')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_dbms_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['dbms'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
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
        $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        if (!$l_catdata) {

            /* Also create a default database instance
            {
                $l_dao = new isys_cmdb_dao_category_s_database_instance($this->m_db);
                $l_dao->create($_GET[C__CMDB__GET__OBJECT], "Default", "", "");
            } */

            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, "", "");
        } else {
            $l_list_id = $l_catdata["isys_cats_dbms_list__id"];
        }

        $l_bRet = $this->save(
            $l_list_id,
            $_POST["C__CMDB__CATS__DBMS"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            C__RECORD_STATUS__NORMAL
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param    integer $p_id
     * @param    integer $p_dbms
     * @param    string  $p_description
     * @param    integer $p_status
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_dbms, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'UPDATE isys_cats_dbms_list SET
			isys_cats_dbms_list__isys_dbms__id = ' . $this->convert_sql_id($p_dbms) . ',
			isys_cats_dbms_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_dbms_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_cats_dbms_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param    integer $p_object_id
     * @param    integer $p_dbms
     * @param    string  $p_description
     * @param    integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_dbms, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = "INSERT IGNORE INTO isys_cats_dbms_list SET
			isys_cats_dbms_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
			isys_cats_dbms_list__isys_dbms__id = " . $this->convert_sql_id($p_dbms) . ",
			isys_cats_dbms_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_dbms_list__status = " . $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }
}
