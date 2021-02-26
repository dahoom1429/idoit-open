<?php

/**
 * Class isys_cmdb_dao_list_catg_support_entitlement
 */
class isys_cmdb_dao_list_catg_support_entitlement extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__SUPPORT_ENTITLEMENT');
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
     * @param string     $table
     * @param int|null   $objectId
     * @param int|null   $recordStatus
     *
     * @return  isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_result($table = 'isys_catg_support_entitlement_list', $objectId = null, $recordStatus = null)
    {
        $status = $recordStatus ?: $this->get_rec_status();
        $objectId = $objectId ?: $this->m_cat_dao->get_object_id();

        $query = 'SELECT *, (CURDATE() BETWEEN isys_catg_support_entitlement_list__start_date AND isys_catg_support_entitlement_list__end_date) as state FROM isys_catg_support_entitlement_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_support_entitlement_list__isys_obj__id
			WHERE TRUE';

        if ($objectId) {
            $query .= ' AND isys_catg_support_entitlement_list__isys_obj__id = ' . $this->convert_sql_int($objectId);
        }

        if ($status) {
            $query .= ' AND isys_catg_support_entitlement_list__status = ' . $this->convert_sql_int($status);
        }

        return $this->retrieve($query);
    }

    /**
     * Method for retrieving the field-names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'state' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE',
            'isys_catg_support_entitlement_list__description' => 'LC__CMDB__CATG__DESCRIPTION',
            'isys_catg_support_entitlement_list__partnumber' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER',
            'isys_catg_support_entitlement_list__start_date' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE',
            'isys_catg_support_entitlement_list__end_date' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE',
            'expires' => 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES',
        ];
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $state = (bool) $row['state'];
        $row['state'] = isys_module_jdisc::getStatusMarker($state);
        $row['expires'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($row['isys_catg_support_entitlement_list__start_date']) &&
            !empty($row['isys_catg_support_entitlement_list__end_date'])) {
            $row['expires'] = isys_module_jdisc::expiresInDays(
                new DateTime($row['isys_catg_support_entitlement_list__start_date']),
                new DateTime($row['isys_catg_support_entitlement_list__end_date'])
            );
        }

        if (!$state && is_numeric($row['expires'])) {
            $row['expires'] = 'NOT STARTED';
        }
    }
}
