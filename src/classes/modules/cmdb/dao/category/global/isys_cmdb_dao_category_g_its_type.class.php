<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: global category for IT service types.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_its_type extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'its_type';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_its_type_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_its_type_list__isys_obj__id
            LEFT JOIN isys_its_type ON isys_its_type__id = isys_catg_its_type_list__isys_its_type__id
            WHERE TRUE " . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_its_type_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_its_type_list__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'its_type' => (new DialogPlusProperty(
                'C__CATG__ITS_TYPE__TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_catg_its_type_list__isys_its_type__id',
                'isys_catg_its_type_list',
                'isys_its_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_its_type_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ITS_TYPE', 'C__CATG__ITS_TYPE')
                ]
            ])
        ];
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['its_type'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['its_type'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Create a new category entry.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   integer $p_its_type_id
     * @param   string  $p_description
     *
     * @return  mixed  Integer of last id or boolean false.
     */
    public function create($p_object_id, $p_status, $p_its_type_id, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_its_type_list', $p_object_id);
        if ($this->save($l_id, $p_status, $p_its_type_id, $p_description)) {
            return $l_id;
        }

        return false;
    }

    /**
     * @param  integer $p_id
     * @param  integer $p_status
     * @param  integer $p_its_type_id
     * @param  string  $p_description
     *
     * @return boolean
     * @throws isys_exception_dao
     */
    public function save($p_id, $p_status = C__RECORD_STATUS__NORMAL, $p_its_type_id, $p_description)
    {
        $l_sql = 'UPDATE isys_catg_its_type_list SET
            isys_catg_its_type_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_its_type_list__isys_its_type__id = ' . $this->convert_sql_id($p_its_type_id) . ',
            isys_catg_its_type_list__status = ' . $this->convert_sql_int($p_status) . '
            WHERE isys_catg_its_type_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Save element method.
     *
     * @param   integer & $p_cat_level
     * @param   integer & $p_status
     * @param   boolean $p_create
     *
     * @return  mixed  Last inserted ID or boolean.
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        if ($p_create && empty($l_catdata)) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CATG__ITS_TYPE__TYPE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id > 0) {
                $p_cat_level = 1;

                return $l_id;
            }
        } else {
            $l_save = $this->save(
                $l_catdata["isys_catg_its_type_list__id"],
                ($l_catdata["isys_catg_its_type_list__status"] ?: C__RECORD_STATUS__NORMAL),
                $_POST["C__CATG__ITS_TYPE__TYPE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_save) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method for retrieving all it-services by a given it-service type.
     *
     * @param   integer $p_type
     * @param   string  $p_filter
     * @param   integer $p_limit
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_services_by_type($p_type = null, $p_filter = null, $p_limit = null)
    {
        $l_auth_condition = '';

        // ID-2897 - Only append the auth-condition, if this feature is enabled.
        if (!!isys_tenantsettings::get('auth.use-in-cmdb-explorer-service-browser', false)) {
            $l_auth_condition = isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition();
        }

        $l_sql = 'SELECT isys_obj__id, isys_obj__title FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' .
            ($p_type !== null ? 'INNER JOIN isys_catg_its_type_list ON isys_catg_its_type_list__isys_obj__id = isys_obj__id' : '') . '
            WHERE TRUE ' . $l_auth_condition . '
            AND isys_obj_type__id IN (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' .
            $this->convert_sql_int(defined_or_default('C__CATG__SERVICE')) . ')
            AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            ($p_type !== null ? ' AND isys_catg_its_type_list__isys_its_type__id = ' . $this->convert_sql_id($p_type) : '') .
            ($p_filter !== null && !empty($p_filter) ? ' AND isys_obj__title LIKE ' . $this->convert_sql_text('%' . $p_filter . '%') : '');

        if ($p_limit !== null && $p_limit > 0) {
            $l_sql .= ' LIMIT ' . $this->convert_sql_int($p_limit);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for counting all it-services by a given it-service type.
     *
     * @param integer $p_type
     * @param string  $p_filter
     *
     * @return integer
     * @throws isys_exception_database
     */
    public function count_services_by_type($p_type = null, $p_filter = null)
    {
        $l_auth_condition = '';

        // ID-2897 - Only append the auth-condition, if this feature is enabled.
        if (!!isys_tenantsettings::get('auth.use-in-cmdb-explorer-service-browser', false)) {
            $l_auth_condition = isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition();
        }

        $l_sql = 'SELECT COUNT(*) AS count FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' .
            ($p_type !== null ? 'INNER JOIN isys_catg_its_type_list ON isys_catg_its_type_list__isys_obj__id = isys_obj__id' : '') . '
            WHERE TRUE ' . $l_auth_condition . '
            AND isys_obj_type__id IN (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' .
            $this->convert_sql_int(defined_or_default('C__CATG__SERVICE')) . ')
            AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            ($p_type !== null ? ' AND isys_catg_its_type_list__isys_its_type__id = ' . $this->convert_sql_id($p_type) : '') .
            ($p_filter !== null && !empty($p_filter) ? ' AND isys_obj__title LIKE ' . $this->convert_sql_text('%' . $p_filter . '%') : '');

        return (int)$this->retrieve($l_sql)
            ->get_row_value('count');
    }
}
