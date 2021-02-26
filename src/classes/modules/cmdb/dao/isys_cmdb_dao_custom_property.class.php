<?php

use idoit\Component\Property\Configuration\PropertyProvides;

/**
 * i-doit
 *
 * Custom properties DAO
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_custom_property extends isys_cmdb_dao
{
    /**
     * Retrieve contents from isys_custom_properties.
     *
     * @param   integer $p_config_id
     * @param   integer $p_cat_id
     * @param   string  $p_cat_type
     *
     * @return  isys_component_dao_result
     * @author      Selcuk Kekec <skekec@i-doit.org>
     */
    public function get_data($p_config_id = null, $p_cat_id = null, $p_cat_type = 'g', $p_property = null)
    {
        $l_sql = 'SELECT * FROM isys_custom_properties WHERE TRUE ';

        if ($p_config_id !== null) {
            $l_sql .= 'AND isys_custom_properties__id = ' . $this->convert_sql_id($p_config_id) . ' ';
        }

        if ($p_cat_id !== null) {
            $l_sql .= 'AND isys_custom_properties__isysgui_' . $p_cat_type . '__id = ' . $this->convert_sql_id($p_cat_id) . ' ';
        }

        if ($p_property !== null) {
            $l_sql .= 'AND isys_custom_properties__property = ' . $this->convert_sql_text($p_property) . ' ';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for resetting the complete custom property configuration.
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function truncate()
    {
        return ($this->update('TRUNCATE isys_custom_properties;') && $this->apply_update());
    }

    /**
     * Method for creating a new property config in the database.
     *
     * @param   array $p_data
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.org>
     */
    public function create(array $p_data)
    {
        if (is_array($p_data['data'])) {
            $l_json = isys_format_json::encode($p_data['data']);

            if (isset($p_data['catg'])) {
                $l_field = 'isys_custom_properties__isysgui_catg__id';
                $l_category_id = $p_data['catg'];
            } else {
                $l_field = 'isys_custom_properties__isysgui_cats__id';
                $l_category_id = $p_data['cats'];
            }

            $l_update_values = $l_field . ' = ' . $this->convert_sql_id($l_category_id) . ',
                isys_custom_properties__property         = ' . $this->convert_sql_text($p_data['property']) . ',
                isys_custom_properties__data             = ' . $this->convert_sql_text($l_json);

            // Check for existing entry
            $l_sql = 'SELECT isys_custom_properties__id FROM isys_custom_properties ' . 'WHERE ' . $l_field . ' = ' . $this->convert_sql_id($l_category_id) . ' AND ' .
                'isys_custom_properties__property = ' . $this->convert_sql_text($p_data['property']) . ';';

            $l_res = $this->retrieve($l_sql);

            if ($l_res->num_rows()) {
                $l_sql = 'UPDATE isys_custom_properties SET ' . $l_update_values . ' WHERE isys_custom_properties__id = ' .
                    $this->convert_sql_id($l_res->get_row_value('isys_custom_properties__id'));
            } else {
                $l_sql = 'INSERT INTO isys_custom_properties SET ' . $l_update_values;
            }

            if (isset($p_data['catg'])) {
                $l_field = 'isys_property_2_cat__isysgui_catg__id';
                $l_category_id = $p_data['catg'];
            } else {
                $l_field = 'isys_property_2_cat__isysgui_cats__id';
                $l_category_id = $p_data['cats'];
            }

            $l_propertyProvidesArray = [
                C__PROPERTY__PROVIDES__SEARCH       => true,
                C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                C__PROPERTY__PROVIDES__IMPORT       => true,
                C__PROPERTY__PROVIDES__EXPORT       => true,
                C__PROPERTY__PROVIDES__REPORT       => true,
                C__PROPERTY__PROVIDES__LIST         => true,
                C__PROPERTY__PROVIDES__MULTIEDIT    => true,
                C__PROPERTY__PROVIDES__VALIDATION   => true,
                C__PROPERTY__PROVIDES__VIRTUAL      => false,
                C__PROPERTY__PROVIDES__FILTERABLE   => true
            ];
            $l_propertyProvides = (new PropertyProvides())
                ->mapAttributes($l_propertyProvidesArray)
                ->getProvidesBit();

            $l_update_values = $l_field . ' = ' . $this->convert_sql_id($l_category_id) . ',
                isys_property_2_cat__cat_const           = ' . $this->convert_sql_text($p_data['const']) . ', 
                isys_property_2_cat__prop_type           = 1, 
                isys_property_2_cat__prop_key            = ' . $this->convert_sql_text($p_data['property']) . ', 
                isys_property_2_cat__prop_title          = ' . $this->convert_sql_text($p_data['data'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]) . ', 
                isys_property_2_cat__prop_provides       = ' . $this->convert_sql_int($l_propertyProvides);

            $l_sqlSelect2cat = 'SELECT isys_property_2_cat__id FROM isys_property_2_cat 
                WHERE ' . $l_field . ' = ' . $this->convert_sql_id($l_category_id) . ' AND ' .
                'isys_property_2_cat__prop_key = ' . $this->convert_sql_text($p_data['property']) . ';';

            $l_res = $this->retrieve($l_sqlSelect2cat);

            if ($l_res->num_rows()) {
                $l_sqlUpdate2cat = 'UPDATE isys_property_2_cat SET ' . $l_update_values . ' WHERE isys_property_2_cat__id = ' .
                    $this->convert_sql_id($l_res->get_row_value('isys_property_2_cat__id'));
            } else {
                $l_sqlUpdate2cat = 'INSERT INTO isys_property_2_cat SET ' . $l_update_values;
            }

            $this->update($l_sqlUpdate2cat);
            $this->apply_update();

            return ($this->update($l_sql) && $this->apply_update());
        }
    }
}
