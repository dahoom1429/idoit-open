<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for Emergency plans
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_cloud_subscriptions extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CLOUD_SUBSCRIPTIONS');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Gets result list.
     *
     * @param   string  $p_str
     * @param   integer $p_obj_id
     * @param   integer $p_record_status
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_obj_id, $p_record_status = null)
    {
        $l_status = $p_record_status ?: $this->get_rec_status();
        $l_object = $p_obj_id ?: $this->m_cat_dao->get_object_id();

        $l_sql = 'SELECT cat.*, IF(s.subscribers, s.subscribers, 0) as subscribers, js.isys_jdisc_status_list__title AS jdisc_status
            FROM isys_catg_cloud_subscriptions_list AS cat
            LEFT JOIN isys_jdisc_status_list AS js ON js.isys_jdisc_status_list__id = cat.isys_catg_cloud_subscriptions_list__jdisc_status 
            LEFT JOIN (
                SELECT count(isys_catg_assigned_subscriptions_list__id) AS subscribers, isys_catg_assigned_subscriptions_list__cloud_subscr__id
                FROM isys_catg_assigned_subscriptions_list
                GROUP BY isys_catg_assigned_subscriptions_list__cloud_subscr__id
            ) AS s ON s.isys_catg_assigned_subscriptions_list__cloud_subscr__id = cat.isys_catg_cloud_subscriptions_list__id
            WHERE TRUE';

        if ($l_object) {
            $l_sql .= ' AND isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($l_object);
        }

        if ($l_status) {
            $l_sql .= ' AND isys_catg_cloud_subscriptions_list__status = ' . $this->convert_sql_int($l_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for retrieving the field-names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_cloud_subscriptions_list__uuid'                  => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID',
            'isys_catg_cloud_subscriptions_list__consumed_units'        => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS',
            'isys_catg_cloud_subscriptions_list__prepaid_enabled_units' => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS',
            'isys_catg_cloud_subscriptions_list__prepaid_suspended'     => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED',
            'isys_catg_cloud_subscriptions_list__prepaid_warning'       => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING',
            'subscribers'                                               => 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS',
            'jdisc_status'                                              => 'LC__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS',
            'isys_catg_cloud_subscriptions_list__description'           => 'LC__CMDB__CATG__DESCRIPTION',
        ];
    }

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        return $p_column . " " . $p_direction;
    }
}
