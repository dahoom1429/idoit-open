<?php

use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * DAO: specific category for relation details.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_relation_details extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'relation_details';

    /**
     * Category table
     *
     * @var string
     */
    protected $m_table = 'isys_catg_relation_list';

    /**
     *  Callback method for dialog field it service
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_itservice(isys_request $p_request)
    {
        $l_itservices = [0 => "Global"];
        $l_objects = $this->get_objects_by_type_id(defined_or_default('C__OBJTYPE__IT_SERVICE'), C__RECORD_STATUS__NORMAL);

        while ($l_row = $l_objects->get_row()) {
            $l_itservices[isys_application::instance()->container->get('language')
                ->get('LC__OBJTYPE__IT_SERVICE')][$l_row["isys_obj__id"]] = $l_row["isys_obj__title"];
        }

        return $l_itservices;
    }

    /**
     * Save specific category relation details
     *
     * @param $p_cat_level        int level to save, default 0
     * @param &$p_intOldRecStatus int status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();
        $p_intOldRecStatus = $l_catdata["isys_catg_relation_list__status"];

        $l_id = $l_catdata['isys_catg_relation_list__id'];

        if ($_POST['C__CATS__RELATION_DETAILS__DIRECTION'] == C__RELATION_DIRECTION__I_DEPEND_ON) {
            $l_master_obj_id = $_POST['C__CATS__RELATION_DETAILS__SLAVE__HIDDEN'];
            $l_slave_obj_id = $_POST['C__CATS__RELATION_DETAILS__MASTER__HIDDEN'];
        } else {
            $l_master_obj_id = $_POST['C__CATS__RELATION_DETAILS__MASTER__HIDDEN'];
            $l_slave_obj_id = $_POST['C__CATS__RELATION_DETAILS__SLAVE__HIDDEN'];
        }

        if ($l_id) {
            $l_bRet = $this->save(
                $l_id,
                C__RECORD_STATUS__NORMAL,
                $l_master_obj_id,
                $l_slave_obj_id,
                $_POST['C__CATS__RELATION_DETAILS__RELATION_TYPE_VALUE'],
                $_POST['C__CATS__RELATION_DETAILS__WEIGHTING'],
                $_POST['C__CATS__RELATION_DETAILS__ITSERVICE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        } else {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $l_master_obj_id,
                $l_slave_obj_id,
                $_POST['C__CATS__RELATION_DETAILS__RELATION_TYPE_VALUE'],
                $_POST['C__CATS__RELATION_DETAILS__WEIGHTING'],
                $_POST['C__CATS__RELATION_DETAILS__ITSERVICE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
            $l_bRet = true;
        }

        return $l_bRet == true ? $l_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param String $p_specification
     * @param int    $p_manufacturerID
     * @param String $p_release
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_obj_id_master, $p_obj_id_slave, $p_relation_type, $p_weighting, $p_itservice, $p_description)
    {
        $l_dao_rel = new isys_cmdb_dao_category_g_relation($this->m_db);

        $l_strSql = "UPDATE isys_catg_relation_list SET " . " isys_catg_relation_list__isys_weighting__id  = " . $this->convert_sql_text($p_weighting) . ", " .
            " isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_obj_id_master) . ", " . " isys_catg_relation_list__isys_obj__id__slave= " .
            $this->convert_sql_id($p_obj_id_slave) . ", " . " isys_catg_relation_list__isys_obj__id__itservice = " . $this->convert_sql_id($p_itservice) . ", " .
            " isys_catg_relation_list__description = " . $this->convert_sql_text($p_description) . ", " . " isys_catg_relation_list__status = " .
            $this->convert_sql_id($p_newRecStatus) . " " . "WHERE isys_catg_relation_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql)) {
            $l_bRet = $this->apply_update();

            if ($l_bRet && $p_relation_type == defined_or_default('C__RELATION_TYPE__DEFAULT')) {
                $l_catdata = $this->get_data($p_cat_level)
                    ->__to_array();
                $l_dao_rel->update_relation_object($l_catdata["isys_catg_relation_list__isys_obj__id"], $p_obj_id_master, $p_obj_id_slave, $p_relation_type);
            }

            if ($p_itservice > 0) {
                $l_dao_components = new isys_cmdb_dao_category_g_it_service_components($this->m_db);
                $l_dao_components->add_component($p_itservice, $p_obj_id_master);
                $l_dao_components->add_component($p_itservice, $p_obj_id_slave);
            }

            return $l_bRet;
        } else {
            return false;
        }
    }

    /**
     * Creates new category data.
     *
     * @param int    $p_relation_id   Relation object identifier
     * @param int    $p_master_id     Master object identifier
     * @param int    $p_slave_id      Slave object identifier
     * @param int    $p_relation_type Relation type identifier
     * @param int    $p_weighting     Relation weightning identifier
     * @param int    $p_it_service    IT service identifier
     * @param string $p_description   Description
     * @param int    $p_record_status Record status
     *
     * @version Van Quyen Hoang <qhoang@synetics.de> 2012-03-16
     *
     * @return mixed Category data identifier (int) on success, otherwise false
     * (bool)
     */
    public function create(
        $p_relation_id,
        $p_master_id,
        $p_slave_id,
        $p_relation_type = null,
        $p_weighting = null,
        $p_it_service = null,
        $p_description = null,
        $p_record_status = C__RECORD_STATUS__NORMAL
    ) {
        assert(is_numeric($p_relation_id) && $p_relation_id > 0);
        assert(is_numeric($p_master_id) && $p_master_id > 0);
        assert(is_numeric($p_slave_id) && $p_slave_id > 0);

        $l_table = 'isys_catg_relation_list';

        $l_query = 'INSERT INTO ' . $l_table . ' SET %s';

        $l_data = [
            'isys_obj__id'         => $this->convert_sql_id($p_relation_id),
            'isys_obj__id__master' => $this->convert_sql_id($p_master_id),
            'isys_obj__id__slave'  => $this->convert_sql_id($p_slave_id)
        ];

        if (isset($p_relation_type)) {
            assert(is_numeric($p_relation_type) && $p_relation_type > 0);
            $l_data['isys_relation_type__id'] = $this->convert_sql_id($p_relation_type);
        }
        if (isset($p_weighting)) {
            assert(is_numeric($p_weighting) && $p_weighting > 0);
            $l_data['isys_weighting__id'] = $this->convert_sql_id($p_weighting);
        }
        if (isset($p_it_service)) {
            assert(is_numeric($p_it_service) && $p_it_service > 0);
            $l_data['isys_obj__id__itservice'] = $this->convert_sql_id($p_it_service);
        }
        if (isset($p_description)) {
            assert(is_string($p_description));
            $l_data['description'] = $this->convert_sql_text($p_description);
        }
        if (isset($p_record_status)) {
            assert(is_numeric($p_record_status) && $p_record_status > 0);
            $l_data['status'] = $this->convert_sql_id($p_record_status);
        }

        $l_sets = null;
        foreach ($l_data as $l_key => $l_value) {
            $l_sets[] = $l_table . '__' . $l_key . ' = ' . $l_value;
        }
        $l_set = implode(', ', $l_sets);
        $l_query = sprintf($l_query, $l_set);

        if ($this->update($l_query) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            // Update object title
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
            $l_relation_type = $l_dao_relation->get_relation_type($p_relation_type, null, true);
            $l_obj_title = $this->get_obj_name_by_id_as_string($p_master_id) . ' ' . isys_application::instance()->container->get('language')
                    ->get($l_relation_type['isys_relation_type__master']) . ' ' . $this->get_obj_name_by_id_as_string($p_slave_id);

            $l_update_sql = 'UPDATE isys_obj SET isys_obj__title = ' . $this->convert_sql_text($l_obj_title) . ' ' . 'WHERE isys_obj__id = ' .
                $this->convert_sql_id($p_relation_id);
            $this->update($l_update_sql);
            $this->apply_update();

            if ($p_it_service > 0) {
                $l_dao_components = new isys_cmdb_dao_category_g_it_service_components($this->get_database_component());
                $l_dao_components->add_component($p_it_service, $p_master_id);
                $l_dao_components->add_component($p_it_service, $p_slave_id);
            }

            return $l_last_id;
        }

        return false;
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        $l_dynamic_properties = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_g_relation', $this->get_database_component())
            ->get_dynamic_properties();

        unset($l_dynamic_properties['_relation_overview']);
        unset($l_dynamic_properties['_relation']);
        unset($l_dynamic_properties['_type']);
        unset($l_dynamic_properties['_itservice']);

        return $l_dynamic_properties;
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_relation_list " . "LEFT JOIN isys_relation_type " . "ON " . "isys_catg_relation_list__isys_relation_type__id = " .
            "isys_relation_type__id " . "LEFT JOIN isys_weighting " . "ON " . "isys_catg_relation_list__isys_weighting__id = " . "isys_weighting__id " .
            "INNER JOIN isys_obj " . "ON " . "isys_catg_relation_list__isys_obj__id = " . "isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_relation_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_relation_list__status = '{$p_status}')";
        }

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
                $l_sql = ' AND (isys_catg_relation_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'object1'       => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Object1',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object1'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_relation_list__isys_obj__id__master',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_relation_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__master', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__RELATION_DETAILS__MASTER'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'object2'       => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Object2',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object2'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_relation_list__isys_obj__id__slave',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_relation_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__slave',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__slave', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__RELATION_DETAILS__SLAVE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'itservice'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IT_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_relation_list__isys_obj__id__itservice',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_relation_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__itservice',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__itservice', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__RELATION_DETAILS__ITSERVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_s_relation_details',
                            'callback_property_itservice'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'relation_type' => (new DialogProperty(
                'C__CATS__RELATION_DETAILS__RELATION_TYPE_VALUE',
                'LC__CATG__RELATION__RELATION_TYPE',
                'isys_catg_relation_list__isys_relation_type__id',
                'isys_catg_relation_list',
                'isys_relation_type'
            )),
            'weighting' => (new DialogProperty(
                'C__CATS__RELATION_DETAILS__WEIGHTING',
                'LC__CATG__RELATION__WEIGHTING',
                'isys_catg_relation_list__isys_weighting__id',
                'isys_catg_relation_list',
                'isys_weighting'
            ))->mergePropertyUiParams([
                'p_bSort'    => false,
                'order'      => 'isys_weighting__sort'
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_relation_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC .
                        defined_or_default('C__CATS__RELATION_DETAILS', 'C__CATS__RELATION_DETAILS')
                ],
            ])
        ];
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $this->get_property('object1'),
                        $this->get_property('object2'),
                        $this->get_property('relation_type'),
                        $this->get_property('weighting'),
                        $this->get_property('itservice'),
                        $this->get_property('description')
                    );
                    if ($p_category_data['data_id'] > 0) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $this->get_property('object1'),
                        $this->get_property('object2'),
                        $this->get_property('relation_type'),
                        $this->get_property('weighting'),
                        $this->get_property('itservice'),
                        $this->get_property('description')
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
