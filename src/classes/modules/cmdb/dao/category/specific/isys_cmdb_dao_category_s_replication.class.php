<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: Specific category for replications.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_replication extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'replication';

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
        $l_sql = "SELECT * FROM isys_cats_replication_list " . "INNER JOIN isys_obj ON isys_cats_replication_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_replication_mechanism ON isys_cats_replication_list__isys_replication_mechanism__id = isys_replication_mechanism__id " . "WHERE TRUE";

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_replication_list__id) = '{$p_cats_list_id}'";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_replication_list__status = '{$p_status}')";
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
            'replication_mechanism' => (new DialogPlusProperty(
                'C__CATS__REPLICATION__MECHANISM',
                'LC__CMDB__CATS__REPLICATION__MECHANISM',
                'isys_cats_replication_list__isys_replication_mechanism__id',
                'isys_cats_replication_list',
                'isys_replication_mechanism'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'description'           => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_replication_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__REPLICATION', 'C__CATS__REPLICATION')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('replication_mechanism'),
                        $this->get_property('description')
                    ))) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], $this->get_property('replication_mechanism'), $this->get_property('description'));
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    public function create($p_object_id, $p_recStatus, $p_mechanism, $p_description)
    {
        $l_sql = "INSERT IGNORE INTO isys_cats_replication_list SET " . "isys_cats_replication_list__status = " . $p_recStatus . " ," .
            "isys_cats_replication_list__description = " . $this->convert_sql_text($p_description) . " ," . "isys_cats_replication_list__isys_obj__id = " .
            $this->convert_sql_id($p_object_id) . " ," . "isys_cats_replication_list__isys_replication_mechanism__id = " . $this->convert_sql_id($p_mechanism) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    public function save_element(&$p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        if (!empty($l_catdata["isys_cats_replication_list__id"])) {
            return $this->save(
                $l_catdata["isys_cats_replication_list__id"],
                $_POST["C__CATS__REPLICATION__MECHANISM"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        } else {
            return $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATS__REPLICATION__MECHANISM"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        }
    }

    public function save($p_cat_level, $p_mechanism, $p_description)
    {
        $l_sql = "UPDATE isys_cats_replication_list SET " . "isys_cats_replication_list__isys_replication_mechanism__id = " . $this->convert_sql_id($p_mechanism) . " ," .
            "isys_cats_replication_list__description = " . $this->convert_sql_text($p_description) . " " . "WHERE isys_cats_replication_list__id = " .
            $this->convert_sql_id($p_cat_level) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return true;
        }

        return false;
    }
}
