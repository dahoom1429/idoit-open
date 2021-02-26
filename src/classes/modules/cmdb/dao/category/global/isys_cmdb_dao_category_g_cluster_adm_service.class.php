<?php

use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 * DAO: Global category for contacts
 *
 * @package       i-doit
 * @subpackage    CMDB_Categories
 * @copyright     synetics GmbH
 * @author        Van Quyen Hoang <qhoang@i-doit.org>
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cluster_adm_service extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cluster_adm_service';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLUSTER_ADM_SERVICE';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Flag
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'connected_object';

    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_cluster_adm_service_list__isys_obj__id';

    /**
     * Source table of this category
     *
     * @var string
     */
    protected $m_table = 'isys_catg_cluster_adm_service_list';

    /**
     * Dynamic property handling for getting the primary IP of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_administration_service($p_row)
    {
        global $g_comp_database;

        $l_return = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res = $l_dao->get_administration_services($p_row["isys_catg_cluster_adm_service_list__isys_obj__id"]);

        while ($l_row = $l_res->get_row()) {
            if ($l_row["isys_catg_cluster_adm_service_list__status"] == C__RECORD_STATUS__NORMAL) {
                $l_return[] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], isys_application::instance()->container->get('language')
                        ->get($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"], C__LINK__OBJECT);
            }
        }

        if (count($l_return) == 0) {
            return '';
        }

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    }

    /**
     * Dynamic property handling for getting the cluster members of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_cluster_members($p_row)
    {
        global $g_comp_database;

        $l_return = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res = $l_dao->get_cluster_members($p_row["isys_catg_cluster_adm_service_list__isys_obj__id"]);

        while ($l_row = $l_res->get_row()) {
            if ($l_row["isys_catg_cluster_members_list__status"] == C__RECORD_STATUS__NORMAL) {
                $l_return[] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], isys_application::instance()->container->get('language')
                        ->get($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"], C__LINK__OBJECT);
            }
        }

        if (count($l_return) == 0) {
            return '';
        }

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    }

    /**
     * Dynamic property handling for getting the cluster services of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_cluster_service($p_row)
    {
        global $g_comp_database;

        $l_return = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_dao = isys_cmdb_dao_category_g_cluster::instance($g_comp_database);
        $l_res = $l_dao->get_cluster_services($p_row["isys_catg_cluster_adm_service_list__isys_obj__id"]);

        while ($l_row = $l_res->get_row()) {
            $l_return[] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], isys_application::instance()->container->get('language')
                    ->get($l_dao->get_objtype_name_by_id_as_string($l_row["isys_obj__isys_obj_type__id"])) . " &rarr; " . $l_row["isys_obj__title"], C__LINK__OBJECT);
        }

        if (count($l_return) == 0) {
            return '';
        }

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    }

    /**
     * Add new graphic adapter.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  mixed
     */
    public function create($p_object_id, $p_status = C__RECORD_STATUS__NORMAL, $p_connected_obj)
    {
        $l_dao_con = new isys_cmdb_dao_connection($this->m_db);

        $l_sql = "INSERT INTO isys_catg_cluster_adm_service_list SET " . "isys_catg_cluster_adm_service_list__status = " . $this->convert_sql_int($p_status) . ", " .
            "isys_catg_cluster_adm_service_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ", " . "isys_catg_cluster_adm_service_list__isys_connection__id = " .
            $this->convert_sql_id($l_dao_con->add_connection($p_connected_obj)) . ";";

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                $l_last_id = $this->get_last_insert_id();
                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                $l_dao_relation->handle_relation(
                    $l_last_id,
                    "isys_catg_cluster_adm_service_list",
                    defined_or_default('C__RELATION_TYPE__CLUSTER_ADM_SERVICE'),
                    null,
                    $p_connected_obj,
                    $p_object_id
                );

                if ($l_dao_relation->apply_update()) {
                    return $l_last_id;
                }
            }
        }

        return false;
    }

    /**
     * Updates an existing entry.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_id, $p_status = C__RECORD_STATUS__NORMAL, $p_connected_obj)
    {
        if (is_numeric($p_id)) {
            $l_sql = "UPDATE isys_catg_cluster_adm_service_list " .
                "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id " . "SET " . "isys_connection__isys_obj__id = " .
                $this->convert_sql_id($p_connected_obj) . ", " . "isys_catg_cluster_adm_service_list__status = " . $this->convert_sql_int($p_status) . ", " . "WHERE " .
                "isys_catg_cluster_adm_service_list__id = " . $this->convert_sql_id($p_id) . ";";

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL = $l_sql;

                if ($this->apply_update()) {
                    $l_catdata = $this->get_data($p_id)
                        ->get_row();
                    $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

                    $l_dao_relation->handle_relation(
                        $p_id,
                        "isys_catg_cluster_adm_service_list",
                        defined_or_default('C__RELATION_TYPE__CLUSTER_ADM_SERVICE'),
                        $l_catdata["isys_catg_cluster_adm_service_list__isys_catg_relation_list__id"],
                        $p_connected_obj,
                        $l_catdata["isys_catg_cluster_adm_service_list__isys_obj__id"]
                    );

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Method for saving elements. Unused.
     *
     * @return  null
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        return null;
    }

    /**
     * @param array $p_post
     *
     * @return mixed
     * @throws Exception
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_id = null;
        $l_currentObjects = [];

        /**
         * 1) Check for delete objects in $l_members
         *  1a) Delete current connection if there is a deleted member
         * 2) Create a currentMember array to check if the entry is already existings afterwards
         */
        $l_current = $this->get_data_by_object($p_object_id);
        while ($l_row = $l_current->get_row()) {
            if (!in_array($l_row["isys_connection__isys_obj__id"], $p_objects)) {
                $this->delete_entry($l_row[$this->m_source_table . '_list__id'], $this->m_source_table . '_list');
            } else {
                $l_currentObjects[$l_row["isys_connection__isys_obj__id"]] = $l_row["isys_connection__isys_obj__id"];
            }
        }

        foreach ($p_objects as $l_object_id) {
            if (is_numeric($l_object_id)) {
                $l_res = $this->get_assigned_objects($p_object_id, $l_object_id);
                if ($l_res->num_rows() == 0) {
                    $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id);

                    if ($l_id) {
                        $this->m_strLogbookSQL = $this->get_last_query();
                    }
                } else {
                    $l_row = $l_res->get_row();
                    $this->save($l_row["isys_catg_cluster_adm_service_list"], C__RECORD_STATUS__NORMAL, $l_object_id);
                    $this->m_strLogbookSQL = $this->get_last_query();
                    $l_id = $l_row["isys_catg_cluster_adm_service_list__id"];
                }
            }
        }

        return $l_id;
    }

    /**
     * Get the assigned objects.
     *
     * @param   integer $p_object
     * @param   integer $p_connected_obj
     *
     * @return  isys_component_dao_result
     */
    public function get_assigned_objects($p_object, $p_connected_obj = null)
    {
        $l_sql = "SELECT * FROM isys_catg_cluster_adm_service_list " .
            "INNER JOIN isys_connection ON isys_connection__id = isys_catg_cluster_adm_service_list__isys_connection__id " .
            "INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id " .
            "WHERE isys_catg_cluster_adm_service_list__isys_obj__id = " . $this->convert_sql_id($p_object) . " ";

        if (!is_null($p_connected_obj)) {
            $l_sql .= " AND isys_connection__isys_obj__id = " . $p_connected_obj;
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'connected_object' => (new ObjectBrowserConnectionProperty(
                'C__CMDB__CATG__CLUSTER_ADM_SERVICE__CONNECTED_OBJECT',
                'LC__CMDB__CATG__CLUSTER_ADM_SERVICE_LIST__ADMINISTRATION_SERVICE',
                'isys_catg_cluster_adm_service_list__isys_connection__id',
                'isys_catg_cluster_adm_service_list'
            ))->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__CLUSTER_ADM_SERVICE')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_cluster_adm_service',
                    'callback_property_relation_handler'
                ], [
                    'isys_cmdb_dao_category_g_cluster_adm_service',
                    true
                ])
            )->mergePropertyUiParams([
                \isys_popup_browser_object_ng::C__MULTISELECTION => true
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => true,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'objtype'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__REPORT__FORM__OBJECT_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_obj__isys_obj_type__id',

                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__CLUSTER_ADM_SERVICE__OBJTYPE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'obj_type'
                    ]
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
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['connected_object'][C__DATA__VALUE]);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id']) {
                        $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['connected_object'][C__DATA__VALUE]);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
