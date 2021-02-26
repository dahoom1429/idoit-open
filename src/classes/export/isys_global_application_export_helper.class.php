<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_application_export_helper extends isys_export_helper
{
    /**
     * Export helper for application licence.
     *
     * @param   integer $p_id
     *
     * @return  mixed  Either boolean false or isys_export_data.
     */
    public function applicationLicence($p_id)
    {
        $l_result = false;

        if (isset($p_id) && $p_id > 0) {
            $l_data_licence = isys_cmdb_dao_category_s_lic::instance($this->m_database)
                ->get_data($p_id)
                ->get_row();

            $l_arr[] = [
                'id'        => $l_data_licence['isys_obj__id'],
                'sysid'     => $l_data_licence['isys_obj__sysid'],
                'type'      => 'C__OBJTYPE__LICENCE',
                'title'     => $l_data_licence['isys_obj__title'],
                'ref_id'    => $p_id,
                'ref_type'  => 'C__CATS__LICENCE',
                'ref_title' => $l_data_licence['isys_cats_lic_list__key'],
                'lic_type'  => $l_data_licence['isys_cats_lic_list__type'],
                'key'       => $l_data_licence['isys_cats_lic_list__key'],
                'amount'    => $l_data_licence['isys_cats_lic_list__amount'],
                'cost'      => $l_data_licence['isys_cats_lic_list__cost'],
                'start'     => $l_data_licence['isys_cats_lic_list__start'],
                'expire'    => $l_data_licence['isys_cats_lic_list__expire']
            ];

            $l_result = new isys_export_data($l_arr);
        }

        return $l_result;
    }

    /**
     * Import method for retrieving the license item ID.
     *
     * @param   array $p_value
     *
     * @return  mixed  Integer of the license item or boolean false.
     */
    public function applicationLicence_import($p_value)
    {
        $l_return = null;

        if (isset($p_value[C__DATA__VALUE])) {
            if (is_array($p_value[C__DATA__VALUE])) {
                $l_data = $p_value[C__DATA__VALUE][0];

                if (array_key_exists($l_data['id'], $this->m_object_ids)) {
                    $l_dao_licence = isys_cmdb_dao_category_s_lic::instance($this->m_database);
                    $l_res = $l_dao_licence->get_data(
                        null,
                        $this->m_object_ids[$l_data['id']],
                        'AND isys_cats_lic_list__key = ' . $l_dao_licence->convert_sql_text($l_data['key'])
                    );

                    if ($l_res->num_rows() > 0) {
                        $l_row = $l_res->get_row();
                        $l_return = $l_row['isys_cats_lic_list__id'];
                    } else {
                        $l_last_id = $l_dao_licence->create_connector('isys_cats_lic_list', $this->m_object_ids[$l_data['id']]);

                        // Category list content.
                        $l_content = [
                            'type'   => $l_data['lic_type'],
                            'status' => C__RECORD_STATUS__NORMAL,
                            'key'    => ($l_data['key']) ? $l_data['key'] : '',
                            'amount' => $l_data['amount'],
                            'cost'   => $l_data['cost'],
                            'start'  => $l_data['start'],
                            'expire' => $l_data['expire']
                        ];

                        // Builds an insert query.
                        $l_sql = $l_dao_licence->build_query('isys_cats_lic_list', $l_content, $l_last_id, C__DB_GENERAL__UPDATE);

                        // Do the update!
                        if ($l_dao_licence->update($l_sql) && $l_dao_licence->apply_update()) {
                            return $l_last_id;
                        }
                    }
                    // if
                }
            } elseif ($p_value[C__DATA__VALUE] > 0) {
                $l_return = $p_value[C__DATA__VALUE];
            }
        }

        return $l_return;
    }

    /**
     * @param $p_id
     *
     * @return isys_export_data|bool
     */
    public function applicationDatabaseSchema($p_id)
    {
        $cache = $this->getCacheContent('applicationDatabaseSchema', $p_id);

        if ($cache) {
            return $cache;
        }

        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_database);
        $l_res = $l_dao_relation->get_data($p_id);
        $l_result = false;

        if ($l_res->num_rows() > 0) {
            $l_rel_data = $l_res->get_row();

            $l_dao_dbms_access = isys_cmdb_dao_category_s_database_access::instance($this->m_database);
            $l_res = $l_dao_dbms_access->get_data(
                null,
                null,
                "AND isys_connection__isys_obj__id = " . $l_dao_dbms_access->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                null,
                C__RECORD_STATUS__NORMAL
            );

            if ($l_res->num_rows() > 0) {
                $l_dbms_data = $l_res->get_row();

                $l_objtype = $l_dao_dbms_access->get_objtype($l_dao_dbms_access->get_objTypeID($l_dbms_data["isys_obj__id"]))
                    ->get_row();

                $l_result[] = [
                    "id"    => $l_dbms_data["isys_obj__id"],
                    "sysid" => $l_dbms_data["isys_obj__sysid"],
                    "title" => $l_dbms_data["isys_obj__title"],
                    "type"  => $l_objtype["isys_obj_type__const"]
                ];

                $data = new isys_export_data($l_result);
                $this->setCacheContent('applicationDatabaseSchema', $p_id, $data);

                return $data;
            }
        }

        return $l_result;
    }

    /**
     * Export DBMS
     *
     * @param int $p_id
     *
     * @return array|bool|\isys_export_data
     */
    public function clusterServiceDatabaseSchema($p_id)
    {
        return $this->applicationDatabaseSchema($p_id);
    }

    /**
     * Import method for retrieving the database schema.
     *
     * @param   mixed $p_value
     *
     * @return  mixed
     */
    public function applicationDatabaseSchema_import($p_value)
    {
        $l_return = null;
        if (is_array($p_value[C__DATA__VALUE]) && isset($p_value[C__DATA__VALUE][0])) {
            if (is_array($p_value[C__DATA__VALUE][0]) && isset($p_value[C__DATA__VALUE][0]["id"])) {
                if (isset($this->m_object_ids[$p_value[C__DATA__VALUE][0]['id']])) {
                    $l_return = $this->m_object_ids[$p_value[C__DATA__VALUE][0]['id']];
                }
            } elseif (is_numeric($p_value[C__DATA__VALUE][0])) {
                if (isset($this->m_object_ids[$p_value[C__DATA__VALUE][0]])) {
                    $l_return = $this->m_object_ids[$p_value[C__DATA__VALUE][0]];
                }
            }
        } else {
            $l_return = (isset($p_value[C__DATA__VALUE])) ? $p_value[C__DATA__VALUE] : null;
        }

        return $l_return;
    }

    /**
     * Import method for retrieving the database schema.
     *
     * @param   mixed $p_value
     *
     * @return  mixed
     */
    public function clusterServiceDatabaseSchema_import($p_value)
    {
        return $this->applicationDatabaseSchema_import($p_value);
    }

    /**
     * @param $p_id
     *
     * @return array|bool|isys_export_data
     */
    public function applicationItService($p_id)
    {
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->m_database);
        $l_res = $l_dao_relation->get_data($p_id);
        $l_result = false;
        if ($l_res->num_rows() > 0) {
            $l_rel_data = $l_res->get_row();

            $l_dao_its_comp = isys_cmdb_dao_category_g_it_service_components::instance($this->m_database);
            $l_res = $l_dao_its_comp->get_data(
                null,
                null,
                "AND isys_connection__isys_obj__id = " . $l_dao_its_comp->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                null,
                C__RECORD_STATUS__NORMAL
            );

            if ($l_res->num_rows() > 0) {
                while ($l_its_data = $l_res->get_row()) {
                    $l_objtype = $l_dao_its_comp->get_objtype($l_dao_its_comp->get_objTypeID($l_its_data["isys_obj__id"]))
                        ->get_row();

                    $l_result[] = [
                        "id"    => $l_its_data["isys_obj__id"],
                        "sysid" => $l_its_data["isys_obj__sysid"],
                        "title" => $l_its_data["isys_obj__title"],
                        "type"  => $l_objtype["isys_obj_type__const"]
                    ];
                }

                return new isys_export_data($l_result);
            }
        }

        return $l_result;
    }

    /**
     * Import method for returning the IT-Service application.
     *
     * @param   array $p_value
     *
     * @return  integer
     */
    public function applicationItService_import($p_value)
    {
        $l_return = [];
        if (is_array($p_value[C__DATA__VALUE])) {
            foreach ($p_value[C__DATA__VALUE] as $l_data) {
                if (is_array($l_data) && isset($l_data['id'])) {
                    if (array_key_exists($l_data['id'], $this->m_object_ids)) {
                        $l_return[] = $this->m_object_ids[$l_data['id']];
                    }
                } elseif ($l_data > 0 && isset($this->m_object_ids[$l_data])) {
                    $l_return[] = $this->m_object_ids[$l_data];
                }
            }
        } elseif ($p_value[C__DATA__VALUE] > 0) {
            $l_return[] = $p_value[C__DATA__VALUE];
        }

        return $l_return;
    }

    /**
     * Export Helper for property assigned_variant for global category application
     *
     * @param $p_value
     *
     * @return array
     */
    public function applicationAssignedVariant($p_value)
    {
        if (!$p_value || !is_scalar($p_value)) {
            return null;
        }

        $cacheVariant = $this->getCacheContent('applicationAssignedVariant', $p_value);

        if ($cacheVariant) {
            return $cacheVariant;
        }

        $l_dao = isys_cmdb_dao_category_s_application_variant::instance($this->m_database);
        $l_data = $l_dao->get_data($p_value)
            ->get_row();

        $cacheObjectType = $this->getCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id']);

        if (!$cacheObjectType) {
            $cacheObjectType = $l_dao->get_objtype($l_data['isys_obj__isys_obj_type__id'])
                ->get_row();

            $this->setCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id'], $cacheObjectType);
        }

        $data = [
            'id'        => $l_data['isys_obj__id'],
            'title'     => $l_data['isys_obj__title'],
            'sysid'     => $l_data['isys_obj__sysid'],
            'type'      => $cacheObjectType['isys_obj_type__const'],
            'ref_id'    => $p_value,
            'ref_title' => $l_data['isys_cats_app_variant_list__title'],
            'ref_type'  => 'C__CATS__APPLICATION_VARIANT',
            'variant'   => $l_data['isys_cats_app_variant_list__variant']
        ];

        $this->setCacheContent('applicationAssignedVariant', $p_value, $data);

        return $data;
    }

    /**
     * Import Helper for property assigned_variant for global category application
     *
     * @param $p_value
     *
     * @return array
     */
    public function applicationAssignedVariant_import($p_value)
    {
        if (is_array($p_value)) {
            if (array_key_exists($p_value['id'], $this->m_object_ids)) {
                $l_dao_variant = isys_cmdb_dao_category_s_application_variant::instance($this->m_database);
                $l_sql = 'SELECT isys_cats_app_variant_list__id FROM isys_cats_app_variant_list ' . 'WHERE isys_cats_app_variant_list__isys_obj__id = ' .
                    $l_dao_variant->convert_sql_id($this->m_object_ids[$p_value['id']]) . ' ' . 'AND isys_cats_app_variant_list__title = ' .
                    $l_dao_variant->convert_sql_text($p_value['ref_title']) . ' ' . 'AND isys_cats_app_variant_list__variant = ' .
                    $l_dao_variant->convert_sql_text($p_value['variant']);

                $l_res = $l_dao_variant->retrieve($l_sql);
                if ($l_res && $l_res->num_rows() > 0) {
                    $l_row = $l_res->get_row();

                    return $l_row['isys_cats_app_variant_list__id'];
                } else {
                    $l_create_arr = [
                        'isys_obj__id' => $p_value['id'],
                        'status'       => C__RECORD_STATUS__NORMAL,
                        'title'        => $p_value['ref_title'],
                        'variant'      => $p_value['variant']
                    ];

                    return $l_dao_variant->create_data($l_create_arr);
                }
            }
        }

        return null;
    }

    /**
     * Import helper for application version.
     *
     * @param   integer $p_value
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function applicationAssignedVersion($p_value)
    {
        if (!$p_value) {
            return null;
        }

        $l_dao = isys_cmdb_dao_category_g_version::instance($this->m_database);
        $l_data = $l_dao->get_data($p_value)
            ->get_row();

        $cacheObjectType = $this->getCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id']);

        if (!$cacheObjectType) {
            $cacheObjectType = $l_dao->get_objtype($l_data['isys_obj__isys_obj_type__id'])
                ->get_row();
            $this->setCacheContent('object_type_rows', $l_data['isys_obj__isys_obj_type__id'], $cacheObjectType);
        }

        return [
            'id'          => $l_data['isys_obj__id'],
            'title'       => $l_data['isys_obj__title'],
            'sysid'       => $l_data['isys_obj__sysid'],
            'type'        => $cacheObjectType['isys_obj_type__const'],
            'ref_id'      => $p_value,
            'ref_title'   => $l_data['isys_catg_version_list__title'],
            'ref_type'    => 'C__CATG__VERSION',
            'servicepack' => $l_data['isys_catg_version_list__servicepack'],
            'hotfix'      => $l_data['isys_catg_version_list__hotfix'],
            'kernel'      => $l_data['isys_catg_version_list__kernel']
        ];
    }

    /**
     * Import Helper for property assigned_version for global category application
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function applicationAssignedVersion_import($p_value)
    {
        if (is_array($p_value)) {
            if (isset($p_value[C__DATA__VALUE])) {
                $p_value = $p_value[C__DATA__VALUE];
            }

            if (isset($this->m_object_ids[$p_value['id']])) {
                /**
                 * @var $l_dao_version isys_cmdb_dao_category_g_version
                 */
                $l_dao_version = isys_cmdb_dao_category_g_version::instance($this->m_database);
                $l_sql = 'SELECT isys_catg_version_list__id FROM isys_catg_version_list
					WHERE isys_catg_version_list__isys_obj__id = ' . $l_dao_version->convert_sql_id($this->m_object_ids[$p_value['id']]) . '
					AND isys_catg_version_list__title = ' . $l_dao_version->convert_sql_text($p_value['ref_title']);

                if (isset($p_value['servicepack']) && $p_value['servicepack'] != '') {
                    $l_sql .= ' AND isys_catg_version_list__servicepack = ' . $l_dao_version->convert_sql_text($p_value['servicepack']);
                }

                if (isset($p_value['hotfix']) && $p_value['hotfix'] != '') {
                    $l_sql .= ' AND isys_catg_version_list__hotfix = ' . $l_dao_version->convert_sql_text($p_value['hotfix']);
                }

                $l_res = $l_dao_version->retrieve($l_sql);

                if (is_countable($l_res) && count($l_res)) {
                    return $l_res->get_row_value('isys_catg_version_list__id');
                } else {
                    return $l_dao_version->create(
                        $this->m_object_ids[$p_value['id']],
                        C__RECORD_STATUS__NORMAL,
                        $p_value['ref_title'],
                        $p_value['servicepack'],
                        $p_value['hotfix'],
                        $p_value['kernel']
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param $value
     *
     * @return isys_export_data|null
     * @throws isys_exception_database
     */
    public function assignedDatabases($value)
    {
        if (!$value) {
            return null;
        }

        $dao = isys_cmdb_dao_category_g_application::instance($this->m_database);
        $attachedDatabases = $dao->getAttachedDatabasesData($value);
        $l_result = [];

        if (count($attachedDatabases)) {
            foreach ($attachedDatabases as $databaseData) {
                $l_result[] = [
                    'id'    => $databaseData['isys_obj__id'],
                    'sysid' => $databaseData['isys_obj__sysid'],
                    'title' => $databaseData['isys_obj__title'],
                    'type'  => $databaseData['isys_obj_type__const'],
                    'ref_id' => $databaseData['isys_catg_database_sa_list__id'],
                    'ref_title' => $databaseData['isys_catg_database_sa_list__title'],
                    'ref_type' => 'C__CATG__DATABASE',
                    'instance_name' => $databaseData['isys_catg_database_list__instance_name'],
                    'dbms' => $databaseData['assignedDbms']
                ];
            }

            return new isys_export_data($l_result);
        }

        return null;
    }

    /**
     * @param $data
     *
     * @return array
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function assignedDatabases_import($data)
    {
        $return = [];
        if (is_array($data[C__DATA__VALUE]) && count($data[C__DATA__VALUE])) {
            $dao = isys_cmdb_dao_category_g_database_sa::instance($this->m_database);

            $query = 'SELECT isys_catg_database_sa_list__id FROM isys_catg_database_sa_list
                INNER JOIN isys_catg_application_list ON isys_catg_application_list__id = isys_catg_database_sa_list__isys_catg_application_list__id
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                WHERE isys_catg_database_sa_list__isys_obj__id = %s 
                  AND isys_catg_database_sa_list__title = %s
                  AND isys_obj__title = %s
                  AND isys_obj_type__isysgui_cats__id = ' . $dao->convert_sql_id(defined_or_default('C__CATS__DBMS'));

            $queryDbms = 'SELECT isys_obj__id FROM isys_obj
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                WHERE isys_obj__title = %s
                    AND isys_obj_type__isysgui_cats__id = ' . $dao->convert_sql_id(defined_or_default('C__CATS__DBMS'));

            foreach ($data[C__DATA__VALUE] as $assignment) {
                if (isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE')][$assignment['ref_id']])) {
                    $return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__DATABASE')][$assignment['ref_id']];
                    continue;
                }

                $databaseQuery = sprintf(
                    $query,
                    $this->m_object_ids[$assignment['id']],
                    $dao->convert_sql_text($assignment['ref_title']),
                    $dao->convert_sql_text($assignment['dbms'])
                );
                $databaseId = $dao->retrieve($databaseQuery)->get_row_value('isys_catg_database_list__id');

                if (!$databaseId) {
                    // create entry
                    $preparedData = [
                        'isys_obj__id' => $this->m_object_ids[$assignment['id']],
                        'title' => $assignment['ref_title'],
                        'instance_name' => $assignment['instance_name'],
                        'assigned_dbms' => $dao->retrieve(sprintf($queryDbms, $dao->convert_sql_text($assignment['dbms'])))->get_row_value('isys_obj__id')
                    ];
                    $databaseId = $dao->create_data($preparedData);
                }

                $return[] = $databaseId;
            }
        }

        return $return;
    }
}
