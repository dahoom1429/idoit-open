<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: specific category for ws network types.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_ws_net_type extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var string
     */
    protected $m_category = 'ws_net_type';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__WS_NET_TYPE';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__WS_NET_TYPE;

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
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_ws_net_type_list " . "INNER JOIN isys_obj " . "ON " . "isys_cats_ws_net_type_list__isys_obj__id = isys_obj__id " .
            "LEFT OUTER JOIN isys_net_type_title " . "ON " . "isys_net_type_title__id = isys_cats_ws_net_type_list__isys_net_type_title__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_ws_net_type_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_ws_net_type_list__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title' => (new DialogPlusProperty(
                'C__CATS__WS_NET_TYPE_TITLE_ID',
                'LC__CMDB__CATS__WS_NET_TYPE',
                'isys_cats_ws_net_type_list__isys_net_type_title__id',
                'isys_cats_ws_net_type_list',
                'isys_net_type_title'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_ws_net_type_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__WS_NET_TYPE', 'C__CATS__WS_NET_TYPE')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_ws_net_type_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Trigger save process of global category model
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     *
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_ws_net_type_list__status"];

        $l_list_id = $l_catdata["isys_cats_ws_net_type_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, null, null, null, null);
        }

        if ($l_list_id != "") {
            $l_bRet = $this->save(
                $l_list_id,
                $_POST['C__CATS__WS_NET_TYPE_TITLE_ID'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   string  $p_titleID
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @author Gezim Rugova <grugova@synetics.de>
     */
    public function save($p_cat_level, $p_titleID, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_strSql = "UPDATE isys_cats_ws_net_type_list SET " . "isys_cats_ws_net_type_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_ws_net_type_list__isys_net_type_title__id  = " . $this->convert_sql_id($p_titleID) . ", " . "isys_cats_ws_net_type_list__status = " .
            $this->convert_sql_int($p_status) . " " . "WHERE isys_cats_ws_net_type_list__id = " . $this->convert_sql_id($p_cat_level);

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_model__id $p_fk_id
     *
     * @param   array   $p_objID
     * @param   integer $p_titleID
     * @param   string  $p_description
     *
     * @return  mixed
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_titleID, $p_description)
    {
        $l_strSql = "INSERT IGNORE INTO isys_cats_ws_net_type_list SET " . "isys_cats_ws_net_type_list__description = " . $this->convert_sql_text($p_description) . ", " .
            "isys_cats_ws_net_type_list__isys_net_type_title__id  = " . $this->convert_sql_id($p_titleID) . ", " . "isys_cats_ws_net_type_list__status = " .
            C__RECORD_STATUS__NORMAL . ", " . "isys_cats_ws_net_type_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }
}
