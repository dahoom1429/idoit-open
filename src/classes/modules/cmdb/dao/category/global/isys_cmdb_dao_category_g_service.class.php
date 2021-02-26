<?php

use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;

/**
 * i-doit
 *
 * DAO: global category for service
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_service extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'service';

    /**
     * Callback method for the service alias field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_service_alias(isys_request $p_request)
    {
        $l_cat_list = [];
        $l_res = $this->get_service_alias();

        while ($l_row = $l_res->get_row()) {
            $l_cat_list[] = [
                "caption" => $l_row['isys_service_alias__title'],
                "value"   => $l_row['isys_service_alias__id']
            ];
        }

        return $l_cat_list;
    }

    /**
     * Gets all existing service aliase with a normal status.
     *
     * @param   string $p_filter
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_service_alias($p_filter = '')
    {
        return $this->retrieve('SELECT * FROM isys_service_alias WHERE isys_service_alias__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' ' . $p_filter .
            ';');
    }

    /**
     * Gets all assigned service aliase
     *
     * @param integer $p_obj_id
     * @param integer $p_id
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_assigned_service_aliase($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id)) {
            return false;
        }

        $l_sql = 'SELECT srv_alias.isys_service_alias__id, srv_alias.isys_service_alias__title
			FROM isys_catg_service_list_2_isys_service_alias AS main
			INNER JOIN isys_service_alias srv_alias ON main.isys_service_alias__id = srv_alias.isys_service_alias__id ';

        $l_condition = '';

        if ($p_obj_id > 0) {
            $l_condition = ' WHERE main.isys_catg_service_list__id = (SELECT isys_catg_service_list__id FROM isys_catg_service_list WHERE isys_catg_service_list__isys_obj__id = ' .
                $this->convert_sql_id($p_obj_id) . ')';
        }

        if ($p_id > 0) {
            $l_condition = ' WHERE main.isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);
        }

        return $this->retrieve($l_sql . $l_condition . ';');
    }

    /**
     * Remove assigned service alias connections
     *
     * @param null $p_obj_id
     * @param null $p_id
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function clear_assigned_service_aliase($p_obj_id = null, $p_id = null)
    {
        if (empty($p_obj_id) && empty($p_id)) {
            return false;
        }

        $l_condition = '';

        if ($p_obj_id > 0) {
            $l_condition = ' isys_catg_service_list__id = (SELECT isys_catg_service_list__id FROM isys_catg_service_list WHERE isys_catg_service_list__isys_obj__id = ' .
                $this->convert_sql_id($p_obj_id) . ')';
        }

        if ($p_id > 0) {
            $l_condition = ' isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);
        }

        $l_sql = 'DELETE FROM isys_catg_service_list_2_isys_service_alias WHERE ' . $l_condition;

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Save category entry.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_intOldRecStatus
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_service_list__status"];

        $l_list_id = $l_catdata["isys_catg_service_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_catg_service_list");
        }

        $l_bRet = $this->save(
            $l_list_id,
            C__RECORD_STATUS__NORMAL,
            $_POST["C__CMDB__CATG__SERVICE__TYPE"],
            $_POST["C__CMDB__CATG__SERVICE__CATEGORY"],
            $_POST["C__CMDB__CATG__SERVICE__ACTIVE"],
            $_POST["C__CMDB__CATG__SERVICE__BUSINESS_UNIT"],
            $_POST['C__CMDB__CATG__SERVICE__ALIAS'],
            $_POST["C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN"],
            $_POST["C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            $_POST['C__CMDB__CATG__SERVICE__SERVICE_NUMBER']
        );

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Save action.
     *
     * @param  integer $p_id
     * @param  integer $p_status
     * @param  integer $p_service_type
     * @param  integer $p_service_category
     * @param  integer $p_active
     * @param  integer $p_business_unit
     * @param  array   $p_service_aliase
     * @param  string  $p_srv_descr_intern
     * @param  string  $p_srv_descr_extern
     * @param  string  $p_description
     * @param  string  $p_service_number
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function save(
        $p_id,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_service_type = null,
        $p_service_category = null,
        $p_active = null,
        $p_business_unit = null,
        $p_service_aliase = null,
        $p_srv_descr_intern = null,
        $p_srv_descr_extern = null,
        $p_description = null,
        $p_service_number = null
    ) {
        $l_update = 'UPDATE isys_catg_service_list SET
            isys_catg_service_list__service_number = ' . $this->convert_sql_text($p_service_number) . ',
            isys_catg_service_list__active = ' . $this->convert_sql_int($p_active) . ',
            isys_catg_service_list__isys_service_type__id = ' . $this->convert_sql_id($p_service_type) . ',
            isys_catg_service_list__isys_service_category__id = ' . $this->convert_sql_id($p_service_category) . ',
            isys_catg_service_list__isys_business_unit__id = ' . $this->convert_sql_id($p_business_unit) . ',
            isys_catg_service_list__service_description_intern = ' . $this->convert_sql_text($p_srv_descr_intern) . ',
            isys_catg_service_list__service_description_extern = ' . $this->convert_sql_text($p_srv_descr_extern) . ',
            isys_catg_service_list__status = ' . $this->convert_sql_int($p_status) . ', 
            isys_catg_service_list__description = ' . $this->convert_sql_text($p_description) . ' 
            WHERE isys_catg_service_list__id = ' . $this->convert_sql_id($p_id);

        $l_assigned_aliase_res = $this->get_assigned_service_aliase(null, $p_id);

        while ($l_row = $l_assigned_aliase_res->get_row()) {
            $l_assigned_aliase[$l_row['isys_service_alias__id']] = true;
        }

        $this->update($l_update);

        if (is_array($p_service_aliase) && count($p_service_aliase)) {
            $this->clear_assigned_service_aliase(null, $p_id);

            if (!empty($p_service_aliase[0])) {
                $l_values = '';
                foreach ($p_service_aliase as $l_alias_id) {
                    $l_values .= ' (' . $this->convert_sql_id($p_id) . ', ' . $this->convert_sql_id($l_alias_id) . '),';
                }

                $l_insert = 'INSERT INTO isys_catg_service_list_2_isys_service_alias (isys_catg_service_list__id, isys_service_alias__id) VALUES ' . rtrim($l_values, ',');
                $this->update($l_insert);
            }
        }

        return $this->apply_update();
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'service_number'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__SERVICE_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_NUMBER'
                ]
            ]),
            'type' => new DialogPlusProperty(
                'C__CMDB__CATG__SERVICE__TYPE',
                'LC__CMDB__CATG__SERVICE__TYPE',
                'isys_catg_service_list__isys_service_type__id',
                'isys_catg_service_list',
                'isys_service_type'
            ),
            'category' => new DialogPlusProperty(
                'C__CMDB__CATG__SERVICE__CATEGORY',
                'LC__CMDB__CATG__SERVICE__CATEGORY',
                'isys_catg_service_list__isys_service_category__id',
                'isys_catg_service_list',
                'isys_service_category'
            ),
            'business_unit' => new DialogPlusProperty(
                'C__CMDB__CATG__SERVICE__BUSINESS_UNIT',
                'LC__CMDB__CATG__SERVICE__BUSINESS_UNIT',
                'isys_catg_service_list__isys_business_unit__id',
                'isys_catg_service_list',
                'isys_business_unit'
            ),
            'active' => new DialogYesNoProperty(
                'C__CMDB__CATG__SERVICE__ACTIVE',
                'LC__CMDB__CATG__SERVICE__ACTIVE',
                'isys_catg_service_list__active',
                'isys_catg_service_list',
                1
            ),
            'service_description_intern' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__DESCRIPTION_INTERN',
                    C__PROPERTY__INFO__DESCRIPTION => 'Internal service description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_description_intern',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_INTERN'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                ]
            ]),
            'service_description_extern' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__DESCRIPTION_EXTERN',
                    C__PROPERTY__INFO__DESCRIPTION => 'External service description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__service_description_extern',
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__SERVICE__SERVICE_DESCRIPTION_EXTERN'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__MULTIEDIT => true,
                ]
            ]),
            'service_alias'              => array_replace_recursive(isys_cmdb_dao_category_pattern::multiselect(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERVICE__ALIASE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Aliase'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_service_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'srv_alias',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_service_alias',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_service_list_2_isys_service_alias',
                        'isys_catg_service_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT sa.isys_service_alias__title
                            FROM isys_catg_service_list AS main
                            INNER JOIN isys_catg_service_list_2_isys_service_alias AS sec ON sec.isys_catg_service_list__id = main.isys_catg_service_list__id
                            INNER JOIN isys_service_alias AS sa ON sa.isys_service_alias__id = sec.isys_service_alias__id',
                        'isys_catg_service_list',
                        'main.isys_catg_service_list__id',
                        'main.isys_catg_service_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_catg_service_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__SERVICE__ALIAS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_service_alias',
                        'placeholder'  => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__SERVICE__ALIASE'),
                        'p_onComplete' => "idoit.callbackManager.triggerCallback('cmdb-catg-service-alias-update', selected);",
                        'multiselect'  => true
                        //'p_arData' => new isys_callback(array('isys_cmdb_dao_category_g_service', 'callback_property_service_alias'))
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH  => false,
                    C__PROPERTY__PROVIDES__LIST    => true,
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__REPORT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_multiselect'
                    ]
                ]
            ]),
            'description'                => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Categories description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_service_list__description',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SERVICE', 'C__CATG__SERVICE')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE || empty($p_category_data['data_id'])) {
                $p_category_data['data_id'] = $this->create_connector('isys_catg_service_list', $p_object_id);
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $l_success = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $p_category_data['properties']['type'][C__DATA__VALUE],
                    $p_category_data['properties']['category'][C__DATA__VALUE],
                    $p_category_data['properties']['active'][C__DATA__VALUE],
                    $p_category_data['properties']['business_unit'][C__DATA__VALUE],
                    $p_category_data['properties']['service_alias'][C__DATA__VALUE],
                    $p_category_data['properties']['service_description_intern'][C__DATA__VALUE],
                    $p_category_data['properties']['service_description_extern'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['service_number'][C__DATA__VALUE]
                );

                if ($l_success) {
                    return $p_category_data['data_id'];
                }
            }
        }

        return false;
    }
}
