<?php

use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_cards extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'assigned_cards';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ASSIGNED_CARDS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_assigned_cards_list__isys_obj__id__card';

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
    protected $m_object_browser_property = 'connected_obj';

    /**
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_assigned_cards_list__isys_obj__id';

    /**
     * Callback function for dataretrieval for UI
     *
     * @param isys_request $p_request
     *
     * @return isys_component_dao_result
     */
    public function callback_property_connected_obj(isys_request $p_request)
    {
        $l_obj_id = $p_request->get_object_id();

        return $this->get_assigned_object($l_obj_id);
    }

    /**
     * Create method.
     *
     * @param   integer $p_object_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  mixed  Integer of last inserted ID or boolean false.
     */
    public function create($p_object_id, $p_status, $p_connected_obj, $p_description = null)
    {
        $l_sql = 'INSERT INTO isys_catg_assigned_cards_list 
            SET isys_catg_assigned_cards_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_assigned_cards_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_assigned_cards_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
            isys_catg_assigned_cards_list__isys_obj__id__card = ' . $this->convert_sql_id($p_connected_obj) . ';';

        if ($this->update($l_sql)) {
            if ($this->apply_update()) {
                $this->m_strLogbookSQL .= $l_sql;

                $l_last_id = $this->get_last_insert_id();

                $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

                $l_dao_relation->handle_relation($l_last_id, "isys_catg_assigned_cards_list", defined_or_default('C__RELATION_TYPE__MOBILE_PHONE'), null, $p_connected_obj, $p_object_id);

                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Save method.
     *
     * @param   integer $p_id
     * @param   integer $p_status
     * @param   integer $p_connected_obj
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_id, $p_status, $p_connected_obj, $p_description = null)
    {
        if (is_numeric($p_id)) {
            $l_sql = 'UPDATE isys_catg_assigned_cards_list
                SET isys_catg_assigned_cards_list__isys_obj__id__card = ' . $this->convert_sql_id($p_connected_obj) . ',
                isys_catg_assigned_cards_list__status = ' . $this->convert_sql_int($p_status) . ',
                isys_catg_assigned_cards_list__description = ' . $this->convert_sql_text($p_description) . '
                WHERE isys_catg_assigned_cards_list__id = ' . $this->convert_sql_id($p_id) . ';';

            if ($this->update($l_sql)) {
                $this->m_strLogbookSQL = $l_sql;

                if ($this->apply_update()) {
                    $l_catdata = $this->get_data($p_id)
                        ->get_row();
                    $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

                    $l_dao_relation->handle_relation(
                        $p_id,
                        "isys_catg_assigned_cards_list",
                        defined_or_default('C__RELATION_TYPE__MOBILE_PHONE'),
                        $l_catdata["isys_catg_assigned_cards_list__isys_catg_relation_list__id"],
                        $p_connected_obj,
                        $l_catdata["isys_catg_assigned_cards_list__isys_obj__id"]
                    );

                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Do nothing
     *
     * @param $p_cat_level
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        return null;
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return mixed|null
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
            if (!in_array($l_row["isys_catg_assigned_cards_list__isys_obj__id__card"], $p_objects)) {
                $this->delete_entry($l_row[$this->m_source_table . '_list__id'], $this->m_source_table . '_list');
            } else {
                $l_currentObjects[$l_row["isys_catg_assigned_cards_list__isys_obj__id__card"]] = $l_row["isys_catg_assigned_cards_list__isys_obj__id__card"];
            }
        }

        $l_id = null;

        foreach ($p_objects as $l_object_id) {
            if (is_numeric($l_object_id)) {
                $l_res = $this->get_assigned_object(null, $l_object_id);
                if ($l_res->num_rows() == 0) {
                    $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id, "");
                } else {
                    $this->remove_component(null, $l_object_id);
                    $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $l_object_id, "");
                }
            }
        }

        return $l_id;
    }

    /**
     * Add a new component.
     *
     * @param   integer $p_mobile_id
     * @param   integer $p_card_id
     *
     * @return  mixed  Integer of last inserted ID or boolean false.
     */
    public function add_component($p_mobile_id, $p_card_id)
    {
        if ($this->get_assigned_object($p_mobile_id, $p_card_id)
                ->num_rows() <= 0) {
            return $this->create($p_mobile_id, C__RECORD_STATUS__NORMAL, $p_card_id, "");
        }

        return false;
    }

    /**
     * @param $p_object_id
     * @param $p_connection_id
     *
     * @return bool|void
     * @throws isys_exception_dao
     */
    public function remove_component($p_object_id, $p_connection_id)
    {
        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->m_db);

        $l_sql = "DELETE FROM isys_catg_assigned_cards_list " . "WHERE ";

        $l_res = $this->get_data(
            null,
            $p_object_id,
            "AND isys_catg_assigned_cards_list__isys_obj__id__card = " . $this->convert_sql_id($p_connection_id),
            null,
            C__RECORD_STATUS__NORMAL
        );

        if ($l_res->num_rows() == 0) {
            return false;
        }

        while ($l_row = $l_res->get_row()) {
            $l_sql .= " isys_catg_assigned_cards_list__id = " . $this->convert_sql_id($l_row["isys_catg_assigned_cards_list__id"]) . " OR";

            $l_dao_rel->delete_relation($l_row["isys_catg_assigned_cards_list__isys_catg_relation_list__id"]);
        }

        $l_sql = substr($l_sql, 0, -2);

        if ($this->update($l_sql) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param null $p_object
     * @param null $p_connected_obj
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_assigned_object($p_object = null, $p_connected_obj = null)
    {
        $l_sql = "SELECT * FROM isys_catg_assigned_cards_list " . "INNER JOIN isys_obj ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_obj__id " . "WHERE ";
        $l_add = false;

        if (!is_null($p_connected_obj)) {
            $l_sql .= " isys_catg_assigned_cards_list__isys_obj__id__card = " . $p_connected_obj;
            $l_add = true;
        }

        if (!is_null($p_object)) {
            if ($l_add) {
                $l_sql .= " AND";
            }

            $l_sql .= " isys_catg_assigned_cards_list__isys_obj__id = " . $this->convert_sql_id($p_object) . " ";
        }

        $l_res = $this->retrieve($l_sql);

        return $l_res;
    }

    /**
     * @param $p_connected_obj_id
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function get_assigned_mobile_id($p_connected_obj_id)
    {
        $l_sql = "SELECT isys_catg_assigned_cards_list__isys_obj__id FROM isys_catg_assigned_cards_list " . "WHERE isys_catg_assigned_cards_list__isys_obj__id__card = " .
            $this->convert_sql_id($p_connected_obj_id);

        return $this->retrieve($l_sql)
            ->get_row_value('isys_catg_assigned_cards_list__isys_obj__id');
    }

    /**
     * @param            $p_object
     * @param bool|false $p_asString
     *
     * @return array|string
     */
    public function get_assigned_objects_as_string($p_object, $p_asString = false)
    {
        $l_res = $this->get_assigned_object($p_object);

        while ($l_row = $l_res->get_row()) {
            $l_arr[] = $l_row["isys_catg_assigned_cards_list__isys_obj__id__card"];
        }

        if ($p_asString) {
            return implode(',', $l_arr);
        } else {
            return $l_arr;
        }
    }

    /**
     * @param  integer $objectId
     *
     * @return int|bool
     */
    public function get_count($objectId = null)
    {
        if ($objectId === null || $objectId <= 0) {
            $objectId = $this->m_object_id;
        }

        if ($objectId > 0) {
            $l_sql = 'SELECT count(isys_catg_assigned_cards_list__id) AS count 
              FROM isys_catg_assigned_cards_list
              WHERE isys_catg_assigned_cards_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ' 
              AND isys_catg_assigned_cards_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            return (int) $this->retrieve($l_sql)->get_row_value('count');
        }

        return false;
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT 	isys_catg_assigned_cards_list.*, " . "me.isys_obj__id, " . "me.isys_obj__title, " . "me.isys_obj__status, " . "me.isys_obj__isys_obj_type__id, " .
            "me.isys_obj__sysid, " . "card.isys_obj__title card_title, " . "card.isys_obj__status card_status, " . "card.isys_obj__sysid card_sysid, " .
            "card.isys_obj__id card_id, " . "card.isys_obj__isys_obj_type__id as card_type, " . "isys_obj_type__title as card_type_title " .
            "FROM isys_catg_assigned_cards_list " . "INNER JOIN isys_obj me " . "ON " . "isys_catg_assigned_cards_list__isys_obj__id = " . "me.isys_obj__id " .
            "INNER JOIN isys_obj card " . "ON " . "isys_catg_assigned_cards_list__isys_obj__id__card = " . "card.isys_obj__id " . "INNER JOIN isys_obj_type " . "ON " .
            "card.isys_obj__isys_obj_type__id = " . "isys_obj_type__id " .

            "LEFT JOIN isys_cats_krypto_card_list " . "ON " . "isys_cats_krypto_card_list__isys_obj__id = card.isys_obj__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_assigned_cards_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_assigned_cards_list__status = '{$p_status}')";
        }

        $l_sql .= " ORDER BY card.isys_obj__isys_obj_type__id ASC";

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_catg_assigned_cards_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_assigned_cards_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'connected_obj' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ASSIGNED_CARDS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Card Object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_assigned_cards_list__isys_obj__id__card',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__MOBILE_PHONE'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_assigned_cards',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_assigned_cards',
                        true
                    ]),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                                FROM isys_catg_assigned_cards_list
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id__card',
                        'isys_catg_assigned_cards_list',
                        'isys_catg_assigned_cards_list__id',
                        'isys_catg_assigned_cards_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_assigned_cards_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_assigned_cards_list',
                            'LEFT',
                            'isys_catg_assigned_cards_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_assigned_cards_list__isys_obj__id__card', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ASSIGNED_CARDS__OBJ',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection'                            => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__SIM_CARD;C__CATS__KRYPTO_CARD',
                        'p_strValue'                                => new isys_callback([
                            'isys_cmdb_dao_category_g_assigned_cards',
                            'callback_property_connected_obj'
                        ])

                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ])
        ];
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_title' => new DynamicProperty(
                'LC__CMDB__CATG__ASSIGNED_CARDS',
                'isys_catg_assigned_cards_list__isys_obj__id__card',
                'isys_catg_assigned_cards_list',
                [
                    $this,
                    'dynamicPropertyTitle'
                ]
            )
        ];
    }

    /**
     * @param array $p_row
     *
     * @return string
     */
    public function dynamicPropertyTitle($p_row)
    {
        $cardId = $p_row['isys_catg_assigned_cards_list__isys_obj__id__card'];
        $sql = 'SELECT isys_catg_cards_list__title FROM isys_catg_cards_list 
            WHERE isys_catg_cards_list__isys_obj__id=' . $this->convert_sql_int($cardId) . ' LIMIT 1;';
        $l_res = $this->get_database_component()->query($sql);
        $l_row = $this->get_database_component()->fetch_row_assoc($l_res);
        if (!empty($l_row)) {
            $title = $l_row['isys_catg_cards_list__title'];
        } else {
            $title = isys_tenantsettings::get('gui.empty_value', '-');
        }
        return $title;
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
                            $p_category_data['properties']['connected_obj'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['connected_obj'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
