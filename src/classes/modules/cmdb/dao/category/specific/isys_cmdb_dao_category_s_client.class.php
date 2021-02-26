<?php

use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: specific category for clients.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_client extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'client';

    /**
     * Category entry is purgable
     *
     * @var  boolean
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
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_cats_client_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_client_list__isys_obj__id
			LEFT JOIN isys_client_type ON isys_client_type__id = isys_cats_client_list__isys_client_type__id
			WHERE TRUE ' . $p_condition . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_cats_client_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_cats_client_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     */
    protected function properties()
    {
        return [
            'type' => new DialogPlusProperty(
                'C__CATS__CLIENT_TYPE',
                'LC__CMDB__CATS__CLIENT_TYPE',
                'isys_cats_client_list__isys_client_type__id',
                'isys_cats_client_list',
                'isys_client_type'
            ),
            'keyboard_layout' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UI_CON_TYPE__KEYBOARD_LAYOUT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Keyboard Layout'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_client_list__keyboard_layout'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CLIENT__KEYBOARD_LAYOUT'
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_client_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__CLIENT', 'C__CATS__CLIENT')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_client_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['keyboard_layout'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save method.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_client_list__status"];

        $l_list_id = $l_catdata["isys_cats_client_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_client_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST["C__CATS__CLIENT_TYPE"],
            $_POST["C__CATS__CLIENT__KEYBOARD_LAYOUT"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return ($l_bRet == true) ? $l_list_id : -1;
    }

    public function import($p_data, $p_object_id)
    {
        $l_catdata = $this->get_data(null, $p_object_id)
            ->__to_array();
        $p_intOldRecStatus = $l_catdata["isys_cats_client_list__status"];
        $l_list_id = $l_catdata["isys_cats_client_list__id"];

        if (is_array($p_data)) {
            if ($l_list_id > 0) {
                $l_ret = $this->save($l_list_id, $p_intOldRecStatus, isys_import::check_dialog("isys_client_type", $p_data["type"]), $p_data["keyboard"]["layout"], null);
            } else {
                $l_ret = $this->create(
                    $p_object_id,
                    C__RECORD_STATUS__NORMAL,
                    isys_import::check_dialog("isys_client_type", $p_data["type"]),
                    $p_data["keyboard"]["layout"],
                    null
                );
            }

            return $l_ret;
        }
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   integer $p_typeID
     * @param   string  $p_layout
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_typeID, $p_layout, $p_description)
    {
        $l_sql = "UPDATE isys_cats_client_list SET
			isys_cats_client_list__isys_client_type__id  = " . $this->convert_sql_id($p_typeID) . ",
			isys_cats_client_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_client_list__keyboard_layout = " . $this->convert_sql_text($p_layout) . ",
			isys_cats_client_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_cats_client_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param  array   $p_object_id
     * @param  integer $p_newRecStatus
     * @param  integer $p_typeID
     * @param  string  $p_layout
     * @param  string  $p_description
     *
     * @return  mixed  The newly created ID (integer) or false (boolean)
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_object_id, $p_newRecStatus, $p_typeID, $p_layout, $p_description)
    {
        $l_sql = 'INSERT IGNORE INTO isys_cats_client_list SET
			isys_cats_client_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_cats_client_list__isys_client_type__id  = ' . $this->convert_sql_id($p_typeID) . ',
			isys_cats_client_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_cats_client_list__keyboard_layout = ' . $this->convert_sql_text($p_layout) . ',
			isys_cats_client_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }
}
