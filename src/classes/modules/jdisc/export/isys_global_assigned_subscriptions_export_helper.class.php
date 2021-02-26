<?php

class isys_global_assigned_subscriptions_export_helper extends isys_export_helper
{
    /**
     * Import helper for application version.
     *
     * @param   integer $value
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function assignedUsersSubscriptionsUuid($value)
    {
        if (!empty($value)) {
            $dao = isys_cmdb_dao_category_g_cloud_subscriptions::instance($this->m_database);
            $data = $dao->get_data($value)
                ->get_row();

            return [
                'id'      => $value,
                'title'   => $data['isys_catg_cloud_subscriptions_list__uuid'],
                'type'    => 'C__CATG__CLOUD_SUBSCRIPTIONS'
            ];
        }

        return null;
    }

    /**
     * Import Helper for property assigned_version for global category application
     *
     * @param   array $value
     *
     * @return  mixed
     */
    public function assignedUsersSubscriptionsUuid_import($value)
    {
        $data = $value;
        if (is_array($value[C__DATA__VALUE])) {
            $data = $value[C__DATA__VALUE];
        }

        $category = defined_or_default('C__CATG__CLOUD_SUBSCRIPTIONS');

        if ($category && isset($data['id']) && array_key_exists($data['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$data['id']];
        }

        return null;
    }

    /**
     * Export Helper for property uuid for global category assigned subscriptions
     *
     * @param $value
     *
     * @return array
     */
    public function assignedSubscriptionsUuid($value)
    {
        if (!$value || !is_scalar($value)) {
            return null;
        }

        $cacheUuid = $this->getCacheContent('assignedSubscriptionsUuid', $value);

        if ($cacheUuid) {
            return $cacheUuid;
        }

        $dao = isys_cmdb_dao_category_g_cloud_subscriptions::instance($this->m_database);
        $categoryData = $dao->get_data($value)
            ->get_row();

        $cacheObjectType = $this->getCacheContent('object_type_rows', $categoryData['isys_obj__isys_obj_type__id']);

        if (!$cacheObjectType) {
            $cacheObjectType = $dao->get_objtype($categoryData['isys_obj__isys_obj_type__id'])
                ->get_row();

            $this->setCacheContent('object_type_rows', $categoryData['isys_obj__isys_obj_type__id'], $cacheObjectType);
        }

        $data = [
            'id'        => $categoryData['isys_obj__id'],
            'title'     => $categoryData['isys_obj__title'],
            'sysid'     => $categoryData['isys_obj__sysid'],
            'type'      => $cacheObjectType['isys_obj_type__const'],
            'ref_id'    => $value,
            'ref_title' => $categoryData['isys_catg_cloud_subscriptions_list__uuid'],
            'ref_type'  => 'C__CATG__CLOUD_SUBSCRIPTIONS'
        ];
        $this->setCacheContent('assignedSubscriptionsUuid', $value, $data);

        return $data;
    }

    /**
     * Import Helper for property uuid for global category assigned subscriptions
     *
     * @param $value
     *
     * @return array
     */
    public function assignedSubscriptionsUuid_import($value)
    {
        if (is_array($value)) {
            if (array_key_exists($value['id'], $this->m_object_ids)) {
                $dao = isys_cmdb_dao_category_g_cloud_subscriptions::instance($this->m_database);
                $query = 'SELECT isys_catg_cloud_subscriptions_list__id FROM isys_catg_cloud_subscriptions_list 
                    WHERE isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $dao->convert_sql_id($this->m_object_ids[$value['id']]) . ' 
                    AND isys_catg_cloud_subscriptions_list__uuid = ' . $dao->convert_sql_text($value['ref_title']);

                $result = $dao->retrieve($query);
                if ($result && $result->num_rows() > 0) {
                    $data = $result->get_row();

                    return $data['isys_catg_cloud_subscriptions_list__id'];
                } else {
                    $createData = [
                        'isys_obj__id' => $value['id'],
                        'status'       => C__RECORD_STATUS__NORMAL,
                        'uuid'        => $value['ref_title'],
                    ];

                    return $dao->create_data($createData);
                }
            }
        }

        return null;
    }
}
