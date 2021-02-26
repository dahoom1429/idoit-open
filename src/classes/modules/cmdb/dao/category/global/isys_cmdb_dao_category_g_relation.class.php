<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Context\Context;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_relation extends isys_cmdb_dao_category_global
{
    /**
     * Cache array for already selected relation types.
     *
     * @var  array
     */
    protected static $m_relation_type_cache = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'relation';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var  string
     */
    private $m_relation_field = '';

    /**
     * @var  string
     */
    private $m_relation_type = '';

    /**
     * Callback method which returns the master object
     *
     * @param array $p_row
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_relation_object_master(array $p_row)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_row = isys_cmdb_dao::instance(isys_application::instance()->database)
            ->get_object_by_id($p_row['isys_catg_relation_list__isys_obj__id__master'])
            ->get_row();

        return $l_quick_info->get_quick_info($l_row['isys_obj__id'], isys_application::instance()->container->get('language')
                ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__OBJECT);
    }

    /**
     * Callback method which returns the slave object
     *
     * @param array $p_row
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_relation_object_slave(array $p_row)
    {
        $l_quick_info = new isys_ajax_handler_quick_info();
        $l_row = isys_cmdb_dao::instance(isys_application::instance()->database)
            ->get_object_by_id($p_row['isys_catg_relation_list__isys_obj__id__slave'])
            ->get_row();

        return $l_quick_info->get_quick_info($l_row['isys_obj__id'], isys_application::instance()->container->get('language')
                ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__OBJECT);
    }

    /**
     * Callback method for the relation-overview field.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Selcuk Kekec <skekec@synetics.de>
     */
    public function dynamic_property_callback_relation_overview(array $p_row)
    {
        global $g_comp_database;

        $l_return = [];
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_res = isys_cmdb_dao_category_g_relation::instance($g_comp_database)
            ->get_data(null, $p_row['isys_catg_relation_list__isys_obj__id']);

        if ($l_res->num_rows()) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_quickinfo->get_quick_info($l_row["isys_obj__id"], '<strong>' . $l_row['slave_title'] . '</strong> ' .
                    isys_application::instance()->container->get('language')
                        ->get($l_row['isys_relation_type__master']) . ' <strong>' . $l_row['master_title'] . '</strong>', C__LINK__OBJECT);
            }
        }

        return '<ul><li>' . implode('</li><li>', $l_return) . '</li></ul>';
    }

    /**
     * Callback method for the it-service dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function callback_property_itservice(isys_request $p_request)
    {
        $l_dao = new isys_cmdb_dao($this->get_database_component());
        $l_return = [
            0 => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__GLOBAL')
        ];

        $l_res = $l_dao->get_objects_by_type(defined_or_default('C__OBJTYPE__IT_SERVICE'));

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            }
        }

        return $l_return;
    }

    /**
     * Dynamic property handling for retrieving the IT-service.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_itservice(array $p_row)
    {
        if (empty($p_row["isys_catg_relation_list__isys_obj__id__itservice"])) {
            return isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__GLOBAL');
        } else {
            global $g_comp_database;
            $l_quickinfo = new isys_ajax_handler_quick_info();

            return $l_quickinfo->get_quick_info($p_row["isys_catg_relation_list__isys_obj__id__itservice"], isys_cmdb_dao_category_g_relation::instance($g_comp_database)
                ->get_obj_name_by_id_as_string($p_row["isys_catg_relation_list__isys_obj__id__itservice"]));
        }
    }

    /**
     * Dynamic property handling for retrieving the formulated relation.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_relation(array $p_row)
    {
        global $g_comp_database;

        $l_quickinfo = new isys_ajax_handler_quick_info();
        $l_row = isys_cmdb_dao_category_g_relation::instance($g_comp_database)
            ->get_data_by_relation_object($p_row['isys_catg_relation_list__isys_obj__id'])
            ->get_row();

        // @todo Remove the "style" attribute for the new layout!
        return $l_quickinfo->get_quick_info($l_row["isys_obj__id"], '<strong>' . $l_row['slave_title'] . '</strong> ' .
            isys_application::instance()->container->get('language')
                ->get($l_row['isys_relation_type__master']) . ' <strong>' . $l_row['master_title'] . '</strong>', C__LINK__OBJECT);
    }

    /**
     * Counts implicit or explicit relations
     *
     * @param int    $p_object_id
     * @param int    $p_relation_type
     * @param string $p_direction
     *
     * @return int
     */
    public function count_relations($p_object_id, $p_relation_type = C__RELATION__IMPLICIT, $p_direction = "all")
    {
        if (($p_relation_type != C__RELATION__IMPLICIT && $p_relation_type != C__RELATION__EXPLICIT) && !is_null($p_relation_type)) {
            $p_relation_type = C__RELATION__EXPLICIT;
        }

        $l_sql = "SELECT COUNT(isys_catg_relation_list__id) AS `count`
			FROM isys_catg_relation_list
			INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
			INNER JOIN isys_obj ON isys_catg_relation_list__isys_obj__id = isys_obj__id
			WHERE ";

        switch ($p_direction) {
            case "master":
                $l_sql .= "isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_object_id) . " ";
                break;

            case "slave":
                $l_sql .= "isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_object_id) . " ";
                break;

            case "self":
                $l_sql .= "isys_catg_relation_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . " ";
                break;

            default:
            case "all":
                $l_sql .= "(isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_object_id) . " OR isys_catg_relation_list__isys_obj__id__master = " .
                    $this->convert_sql_id($p_object_id) . ") ";

                break;
        }

        if ($p_relation_type) {
            $l_sql .= "AND isys_relation_type__type = " . $this->convert_sql_id($p_relation_type) . " ";
        }

        $l_sql .= "AND isys_obj__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			AND isys_catg_relation_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ";";

        return (int)$this->retrieve($l_sql)
            ->get_row_value('count');
    }

    /**
     * Create an implicit releation.
     * Set $p_catg_id to NULL if you want to update the relation connector yourself.
     *
     * @param   string  $p_category_table
     * @param   integer $p_catg_id
     * @param   integer $p_obj_master
     * @param   integer $p_obj_slave
     * @param   integer $p_relation_type
     * @param   integer $p_weighting
     * @param   integer $p_status
     *
     * @return  mixed  Integer with the last inserted ID on success, boolean false on failure.
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     */
    public function create_relation(
        $p_category_table,
        $p_catg_id,
        $p_obj_master,
        $p_obj_slave,
        $p_relation_type,
        $p_weighting = null,
        $p_status = C__RECORD_STATUS__NORMAL
    ) {
        if ($p_weighting === null && defined('C__WEIGHTING__5')) {
            $p_weighting = C__WEIGHTING__5;
        }
        if (empty($p_weighting)) {
            throw new isys_exception_cmdb("Weighting should not be empty");
        }

        if (empty($p_relation_type)) {
            throw new isys_exception_cmdb("Relation type should not be empty");
        }

        if (empty($p_obj_master)) {
            throw new isys_exception_cmdb("Your relation Master is empty!");
        }

        if (empty($p_obj_slave)) {
            throw new isys_exception_cmdb("Your relation Slave is empty!");
        }

        if ($this->m_relation_type != $p_relation_type) {
            $this->m_relation_type = $p_relation_type;
            $this->set_relation_field($p_category_table);
        }

        if ($p_obj_master > 0 && $p_obj_slave > 0) {
            $relationType = $this->get_relation_type($p_relation_type, null, true);

            if ($this->get_objtype_configuration($p_obj_slave) && $relationType['isys_relation_type__const'] === null &&
                $relationType['isys_relation_type__type'] !== C__RELATION__EXPLICIT) {
                $l_tmp = $p_obj_master;
                $p_obj_master = $p_obj_slave;
                $p_obj_slave = $l_tmp;
                unset($l_tmp);
            }

            $l_relation_obj = $this->create_object($this->format_relation_name(
                $this->get_obj_name_by_id_as_string($p_obj_master),
                $this->get_obj_name_by_id_as_string($p_obj_slave),
                $relationType["isys_relation_type__master"]
            ), defined_or_default('C__OBJTYPE__RELATION'), $p_status);

            if ($l_relation_obj > 0) {
                $l_relation_id = $this->create($l_relation_obj, $p_obj_master, $p_obj_slave, $relationType['isys_relation_type__id'], null, $p_weighting, $p_status);

                if ($l_relation_id > 0) {
                    if (!is_null($p_catg_id) && !empty($p_category_table)) {
                        $l_sql = 'UPDATE ' . $p_category_table . ' SET ' . $this->m_relation_field . ' = ' . $this->convert_sql_id($l_relation_id) . ' WHERE ' .
                            $p_category_table . '__id = ' . $this->convert_sql_id($p_catg_id);

                        $this->update($l_sql);
                        $this->apply_update();
                    }

                    if (isys_tenantsettings::get('logbook.relations.entries', 'initiated') === 'both') {
                        $objectTypeId = isys_cmdb_dao::instance($this->m_db)
                            ->get_type_by_object_id($p_obj_slave)
                            ->get_row_value('isys_obj_type__id');

                        $typeTranslationHack = str_replace(
                            'LC__CMDB__',
                            'C__',
                            $relationType['isys_relation_type__title']
                        );

                        $changes = [
                             $typeTranslationHack => [
                                'from' => isys_cmdb_dao::instance($this->m_db)
                                    ->get_obj_name_by_id_as_string($p_obj_master),
                                'to'   => isys_cmdb_dao::instance($this->m_db)
                                    ->get_obj_name_by_id_as_string($p_obj_slave)
                            ]
                        ];

                        if (Context::instance()->getContextCustomer() !== Context::CONTEXT_LDAP_SYNC) {
                            isys_event_manager::getInstance()->triggerCMDBEvent(
                                'C__LOGBOOK_EVENT__RELATION_CREATED',
                                $this->m_strLogbookSQL,
                                $p_obj_slave,
                                $objectTypeId,
                                isys_application::instance()->container->get('language')
                                    ->get($relationType['isys_relation_type__title']),
                                serialize($changes)
                            );
                        }
                    }

                    return $l_relation_id;
                }
            }

            $this->cancel_update();

            return false;
        } else {
            throw new isys_exception_cmdb('Master and slave objects should not be empty');
        }
    }

    /**
     * Updates a implicit relation
     *
     * @param   string  $p_category_table
     * @param   integer $p_catg_id
     * @param   integer $p_obj_master
     * @param   integer $p_obj_slave
     * @param   string  $p_relation_type
     * @param   string  $p_weighting
     * @param   integer $p_status
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_database
     */
    public function save_relation($p_category_table, $p_catg_id, $p_obj_master, $p_obj_slave, $p_relation_type, $p_weighting = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (empty($p_relation_type)) {
            throw new isys_exception_cmdb("Relation type should not be empty");
        }
        if (empty($p_obj_master)) {
            throw new isys_exception_cmdb("Your relation Master is empty!");
        }
        if (empty($p_obj_slave)) {
            throw new isys_exception_cmdb("Your relation Slave is empty!");
        }

        if ($this->m_relation_type != $p_relation_type) {
            $this->m_relation_type = $p_relation_type;
            $this->set_relation_field($p_category_table);
        }

        if ($p_catg_id > 0 && !empty($p_category_table)) {
            $relationType = $this->get_relation_type($p_relation_type, null, true);

            if ($this->get_objtype_configuration($p_obj_slave) && $relationType['isys_relation_type__const'] === null &&
                $relationType['isys_relation_type__type'] !== C__RELATION__EXPLICIT) {
                $l_tmp = $p_obj_master;
                $p_obj_master = $p_obj_slave;
                $p_obj_slave = $l_tmp;
                unset($l_tmp);
            }

            $l_relation_id = $this->retrieve("SELECT " . $this->m_relation_field . " FROM " . $p_category_table . " WHERE " . $p_category_table . "__id = " .
                $this->convert_sql_id($p_catg_id) . ";")
                ->get_row_value($this->m_relation_field);

            if ($p_obj_master > 0 && $p_obj_slave > 0) {
                $l_data = $this->get_data($l_relation_id)
                    ->__to_array();

                if ($this->save($l_relation_id, $p_obj_master, $p_obj_slave, $p_relation_type, null, $p_weighting, $p_status, "")) {
                    $this->update_relation_object($l_data["isys_catg_relation_list__isys_obj__id"], $p_obj_master, $p_obj_slave, $p_relation_type);
                }
            }
        }

        return false;
    }

    /**
     * Get object type configuration
     *
     * @param $p_obj_id
     *
     * @return bool
     */
    public function get_objtype_configuration($p_obj_id)
    {
        if ($p_obj_id) {
            $l_sql = "SELECT isys_obj_type__relation_master FROM isys_obj_type 
              INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id 
              WHERE isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";
            $l_tmp = $this->retrieve($l_sql)
                ->get_row();
            if (isset($l_tmp['isys_obj_type__relation_master'])) {
                return !!$l_tmp['isys_obj_type__relation_master'];
            }
        }

        return false;
    }

    /**
     * Creates a new relation.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_obj_id_master
     * @param   integer $p_obj_id_slave
     * @param   integer $p_relation_type
     * @param   integer $p_obj_id_itservice
     * @param   integer $p_weighting
     * @param   integer $p_status
     * @param   string  $p_description
     *
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     * @return  boolean
     */
    public function create(
        $p_obj_id,
        $p_obj_id_master,
        $p_obj_id_slave,
        $p_relation_type,
        $p_obj_id_itservice,
        $p_weighting,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_description = ""
    ) {
        if (empty($p_relation_type)) {
            throw new isys_exception_cmdb("Relation type should not be empty");
        }

        if (empty($p_obj_id_master)) {
            throw new isys_exception_cmdb("Your relation Master is empty!");
        }

        if (empty($p_obj_id_slave)) {
            throw new isys_exception_cmdb("Your relation Slave is empty!");
        }

        $l_sql = 'INSERT INTO isys_catg_relation_list 
            SET isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
            isys_catg_relation_list__isys_obj__id__master = ' . $this->convert_sql_id($p_obj_id_master) . ',
            isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($p_obj_id_slave) . ',
            isys_catg_relation_list__isys_obj__id__itservice = ' . $this->convert_sql_id($p_obj_id_itservice) . ',
            isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id($p_relation_type) . ',
            isys_catg_relation_list__isys_weighting__id = ' . $this->convert_sql_id($p_weighting) . ',
            isys_catg_relation_list__status = ' . $this->convert_sql_int($p_status) . ',
            isys_catg_relation_list__description = ' . $this->convert_sql_text($p_description) . ';';

        if ($this->update($l_sql)) {
            $l_last_id = $this->m_db->get_last_insert_id();
            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Saves a relation.
     *
     * @param   integer $p_id
     * @param   integer $p_obj_id_master
     * @param   integer $p_obj_id_slave
     * @param   integer $p_relation
     * @param   integer $p_obj_id_itservice
     * @param   integer $p_weighting
     * @param   integer $p_status
     * @param   string  $p_description
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function save($p_id, $p_obj_id_master, $p_obj_id_slave, $p_relation, $p_obj_id_itservice, $p_weighting, $p_status = C__RECORD_STATUS__NORMAL, $p_description = "")
    {
        $l_weighting = $p_weighting ? "isys_catg_relation_list__isys_weighting__id = " . $this->convert_sql_id($p_weighting) . ", " : '';

        $l_sql = "UPDATE isys_catg_relation_list SET
			isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_obj_id_master) . ",
			isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_obj_id_slave) . ",
			isys_catg_relation_list__isys_obj__id__itservice = " . $this->convert_sql_id($p_obj_id_itservice) . ",
			isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id($p_relation) . ",
			" . $l_weighting . "
			isys_catg_relation_list__status = " . $this->convert_sql_int($p_status) . ",
			isys_catg_relation_list__description = " . $this->convert_sql_text($p_description) . "
			WHERE isys_catg_relation_list__id = " . $this->convert_sql_id($p_id) . ";";

        if ($this->update($l_sql)) {
            // Check if relation is connected to a custom field entry
            $query = sprintf(
                'SELECT isys_catg_custom_fields_list__id FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__isys_catg_relation_list__id = %s',
                $p_id
            );
            $result = $this->retrieve($query);

            if (($customFieldId = $result->get_row_value('isys_catg_custom_fields_list__id'))) {
                $updateCustomFieldEntry = "UPDATE isys_catg_custom_fields_list SET 
                  isys_catg_custom_fields_list__field_content = (
                    CASE 
                      WHEN isys_catg_custom_fields_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id_master) . "
                      THEN " . $this->convert_sql_text($p_obj_id_slave) . "
                      ELSE " . $this->convert_sql_text($p_obj_id_master) . "
                    END
                  ) WHERE isys_catg_custom_fields_list__id = " . $this->convert_sql_id($customFieldId);
                $this->update($updateCustomFieldEntry);
            }

            if ($this->apply_update()) {
                $this->m_strLogbookSQL = $l_sql;

                return true;
            }
        }

        return false;
    }

    /**
     * Create connector does not work here, becuase master and slave object relations are mandatory.
     *
     * @param string $p_table
     * @param null   $p_obj_id
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_relation' => new DynamicProperty(
                'LC__UNIVERSAL__RELATION',
                'isys_catg_relation_list__isys_obj__id',
                'isys_catg_relation_list',
                [
                    $this,
                    'dynamic_property_callback_relation'
                ]
            ),
            '_relation_overview' => new DynamicProperty(
                'LC__UNIVERSAL__RELATION_OVERVIEW',
                'isys_catg_relation_list__isys_obj__id',
                'isys_catg_relation_list',
                [
                    $this,
                    'dynamic_property_callback_relation_overview'
                ]
            ),
        ];
    }

    /**
     * Return database field to be used as breadcrumb title
     *
     * @return  string
     */
    public function get_breadcrumb_field()
    {
        return 'isys_obj__title';
    }

    /**
     * Count relations of a given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $this->m_object_id;

        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        }

        $l_count_self = 0;
        $l_count_implizit = $this->count_relations($l_obj_id, C__RELATION__IMPLICIT, "all");
        $l_count_explicit = $this->count_relations($l_obj_id, C__RELATION__EXPLICIT, "all");

        if ($this->get_objTypeID($l_obj_id) == defined_or_default('C__OBJTYPE__RELATION')) {
            $l_count_self = $this->count_relations($l_obj_id, null, 'self');
        }

        return ($l_count_implizit + $l_count_explicit + $l_count_self);
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT isys_catg_relation_list.*, isys_weighting.*, relobj.*, isys_obj_type__title, isys_obj_type__relation_master, isys_relation_type.*, 
                masterobj.isys_obj__title AS slave_title, slaveobj.isys_obj__title AS master_title
            FROM isys_catg_relation_list
            LEFT JOIN isys_relation_type ON isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
            LEFT JOIN isys_weighting ON isys_catg_relation_list__isys_weighting__id = isys_weighting__id
            INNER JOIN isys_obj relobj ON isys_catg_relation_list__isys_obj__id = relobj.isys_obj__id
            INNER JOIN isys_obj_type ON relobj.isys_obj__isys_obj_type__id = isys_obj_type__id
            INNER JOIN isys_obj masterobj ON masterobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
            INNER JOIN isys_obj slaveobj ON slaveobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
            WHERE TRUE ";

        if ($p_condition && strstr($p_condition, "isys_obj")) {
            $p_condition = str_replace(["(isys_obj", " isys_obj"], ["(relobj.isys_obj", " relobj.isys_obj"], $p_condition);
        }

        $l_sql .= $p_condition;

        if (is_array($p_obj_id)) {
            $l_sql .= " AND ((isys_catg_relation_list__isys_obj__id__master " . $this->prepare_in_condition($p_obj_id) . ") OR
                (isys_catg_relation_list__isys_obj__id__slave " . $this->prepare_in_condition($p_obj_id) . "))";
        } elseif ($p_obj_id > 0) {
            $l_sql .= " AND ((isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_obj_id) . ") OR
                (isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_obj_id) . "))";
        }

        if ($p_catg_list_id > 0) {
            $l_sql .= " AND isys_catg_relation_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND relobj.isys_obj__status = " . $this->convert_sql_int($p_status) . " AND isys_catg_relation_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Return Category Data. Basically the same as get_data but without the str_replace() call and fewer parameters.
     *
     * @param   string $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_data_for_obj_browser_ng($p_condition = "")
    {
        $l_sql = 'SELECT isys_catg_relation_list.*, isys_weighting.*, relobj.*, isys_obj_type__title, isys_relation_type.*, masterobj.isys_obj__title AS slave_title, slaveobj.isys_obj__title AS master_title
            FROM isys_catg_relation_list
            LEFT JOIN isys_relation_type ON isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
            LEFT JOIN isys_weighting ON isys_catg_relation_list__isys_weighting__id = isys_weighting__id
            INNER JOIN isys_obj relobj ON isys_catg_relation_list__isys_obj__id = relobj.isys_obj__id
            INNER JOIN isys_obj_type ON relobj.isys_obj__isys_obj_type__id = isys_obj_type__id
            INNER JOIN isys_obj masterobj ON masterobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
            INNER JOIN isys_obj slaveobj ON slaveobj.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
            WHERE TRUE ' . $p_condition . '
            AND relobj.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            AND isys_catg_relation_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
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
                    C__PROPERTY__INFO__TITLE       => 'Object 1',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object 1'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_relation_list__isys_obj__id__master',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'masterobj',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                              FROM isys_catg_relation_list
                              INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master
                              INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__master', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RELATION_MASTER',
                    C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__POPUP,
                    C__PROPERTY__UI__PARAMS => [
                        'p_bDisableDetach'  => 1,
                        'p_bDbFieldNN'      => 1,
                        'p_strClass'        => 'input-block',
                        'p_bInfoIconSpacer' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            // @todo object browser for normal objects and dialog field for it service objects
            'object2'       => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Object 2',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object 2'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_relation_list__isys_obj__id__slave',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'slaveobj',
                    C__PROPERTY__DATA__REFERENCES  => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                              FROM isys_catg_relation_list
                              INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
                              INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__slave', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RELATION_SLAVE',
                    C__PROPERTY__UI__TYPE   => C__PROPERTY__UI__TYPE__POPUP,
                    C__PROPERTY__UI__PARAMS => [
                        'p_bDisableDetach'  => 1,
                        'p_bDbFieldNN'      => 1,
                        'p_strClass'        => 'input-block',
                        'p_bInfoIconSpacer' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'relation_type' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RELATION__RELATION_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Relation type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_relation_list__isys_relation_type__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_relation_type',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_relation_type',
                        'isys_relation_type__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_relation_type__title
                            FROM isys_catg_relation_list
                            INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_relation_type',
                            'LEFT',
                            'isys_catg_relation_list__isys_relation_type__id',
                            'isys_relation_type__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RELATION__RELATION_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'     => 'isys_relation_type',
                        'p_strPopupType' => 'relation_type'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'weighting'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__RELATION__WEIGHTING',
                    C__PROPERTY__INFO__DESCRIPTION => 'Weighting'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_relation_list__isys_weighting__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_weighting',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_weighting',
                        'isys_weighting__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_weighting__title
                            FROM isys_catg_relation_list
                            INNER JOIN isys_weighting ON isys_weighting__id = isys_catg_relation_list__isys_weighting__id',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_weighting',
                            'LEFT',
                            'isys_catg_relation_list__isys_weighting__id',
                            'isys_weighting__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__RELATION__WEIGHTING',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_weighting',
                        'p_bSort'    => false,
                        'order'      => 'isys_weighting__sort'
                    ]
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY => true
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_plus'
                    ]
                ]
            ]),
            'itservice'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IT_SERVICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Service'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_relation_list__isys_obj__id__itservice',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj_type__title, \' > \', isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_relation_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__itservice
                            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_relation_list', 'LEFT', 'isys_catg_relation_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_relation_list__isys_obj__id__itservice', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__RELATION__ITSERVICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_relation',
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
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_relation_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_relation_list__description FROM isys_catg_relation_list',
                        'isys_catg_relation_list',
                        'isys_catg_relation_list__id',
                        'isys_catg_relation_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_relation_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__RELATION', 'C__CATG__RELATION')
                ]
            ])
        ];
    }

    /**
     * Rank records
     * Prohibits ranking of relation objects created by an implicit relation.
     *
     * @param   array  $p_relations
     * @param   string $p_direction
     * @param   string $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_relations, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        $l_errors = [];

        foreach ($p_relations as $l_catid) {
            $l_data = $this->get_data($l_catid)
                ->__to_array();

            if ($l_data["isys_relation_type__type"] == C__RELATION__EXPLICIT) {
                parent::rank_record($l_catid, $p_direction, $p_table);
            } else {
                $l_errors[] = $l_data["isys_obj__title"];
            }
        }

        if (count($l_errors) > 0) {
            isys_application::instance()->container['notify']->error("The following implicit relations could not be deleted:\n&nbsp;&nbsp;" .
                implode("\n&nbsp;&nbsp;", $l_errors) . "\nDelete these relations inside their corresponding category, please.");
        }

        return true;
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $l_relation_data = $this->get_relation_type($p_category_data['properties']['relation_type'][C__DATA__VALUE], null, true);
                    /* Create a relation object for this explicit relation */
                    $l_relation_object = $this->create_object(
                        $this->format_relation_name(
                            $this->get_obj_name_by_id_as_string($p_category_data['properties']['object1'][C__DATA__VALUE]),
                            $this->get_obj_name_by_id_as_string($p_category_data['properties']['object2'][C__DATA__VALUE]),
                            $l_relation_data['isys_relation_type__master']
                        ),
                        defined_or_default('C__OBJTYPE__RELATION')
                    );
                    /* If relation object creation was successfull, fill relation category with its details */
                    if ($l_relation_object > 0) {
                        if ($p_category_data['data_id'] = $this->create(
                            $l_relation_object,
                            $p_category_data['properties']['object1'][C__DATA__VALUE],
                            $p_category_data['properties']['object2'][C__DATA__VALUE],
                            $p_category_data['properties']['relation_type'][C__DATA__VALUE],
                            $p_category_data['properties']['itservice'][C__DATA__VALUE],
                            $p_category_data['properties']['weighting'][C__DATA__VALUE],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        )) {
                            $l_indicator = true;
                        }
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($this->save(
                        $p_category_data['data_id'],
                        $p_category_data['properties']['object1'][C__DATA__VALUE],
                        $p_category_data['properties']['object2'][C__DATA__VALUE],
                        $p_category_data['properties']['relation_type'][C__DATA__VALUE],
                        $p_category_data['properties']['itservice'][C__DATA__VALUE],
                        $p_category_data['properties']['weighting'][C__DATA__VALUE],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    )) {
                        // Get object id from relation
                        $l_rel_obj_id = $this->retrieve("SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list WHERE isys_catg_relation_list__id = " .
                            $this->convert_sql_id($p_category_data['data_id']))
                            ->get_row_value('isys_catg_relation_list__isys_obj__id');
                        // Update relation object
                        $l_indicator = $this->update_relation_object(
                            $l_rel_obj_id,
                            $p_category_data['properties']['object1'][C__DATA__VALUE],
                            $p_category_data['properties']['object2'][C__DATA__VALUE],
                            $p_category_data['properties']['relation_type'][C__DATA__VALUE]
                        );
                    }
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data
     * @param   mixed $p_prepend_table_field
     *
     * @return  mixed  Returns true on a successful validation, otherwise an associative array with property tags as keys and error messages as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        // Bugfix for not saving, on the overview page. Refs #3450.
        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')) {
            return true;
        }

        try {
            $l_catdata = $this->get_general_data();

            // If implicit, don't check the master ("object1").
            if ($l_catdata["isys_relation_type__type"] == C__RELATION__IMPLICIT) {
                unset($p_data['object1'], $p_data['relation_type']);
            }
        } catch (Exception $l_exception) {
            ; // This might happen.
        }

        return parent::validate($p_data);
    }

    /**
     * Updates the title of the relation object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_master
     * @param   integer $p_slave
     * @param   integer $p_relation_type
     *
     * @return  boolean
     */
    public function update_relation_object($p_obj_id, $p_master, $p_slave, $p_relation_type)
    {
        $l_relation_data = $this->get_relation_type($p_relation_type, null, true);

        $l_obj_title = $this->format_relation_name(
            $this->get_obj_name_by_id_as_string($p_master),
            $this->get_obj_name_by_id_as_string($p_slave),
            $l_relation_data["isys_relation_type__master"]
        );

        $l_sql = "UPDATE isys_obj SET " . "isys_obj__title = " . $this->convert_sql_text($l_obj_title) . " " . "WHERE isys_obj__id = " . $this->convert_sql_text($p_obj_id) .
            ";";

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @param $p_object_id
     *
     * @return string
     */
    public function get_relation_type_by_object_id($p_object_id)
    {
        $l_sql = "SELECT isys_relation_type__title FROM isys_catg_relation_list
					INNER JOIN isys_relation_type ON isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
					WHERE isys_catg_relation_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return isset($l_row['isys_relation_type__title']) ? $l_row['isys_relation_type__title'] : '';
    }

    /**
     * Returns relation types.
     *
     * @param   mixed   $p_id
     * @param   string  $p_condition
     * @param   boolean $p_as_array
     *
     * @return  mixed  May be an isys_component_dao_result or array
     */
    public function get_relation_type($p_id = null, $p_condition = null, $p_as_array = false)
    {
        // We cache the already selected data, but only if no special condition is given.
        if ($p_as_array === true && $p_id !== null && $p_condition === null && array_key_exists($p_id, self::$m_relation_type_cache)) {
            // This saves us several hundred (or thousands) of queries during an import.
            return self::$m_relation_type_cache[$p_id];
        }

        $l_sql = "SELECT * FROM isys_relation_type WHERE TRUE " . $p_condition;

        if (is_numeric($p_id)) {
            $l_sql .= " AND isys_relation_type__id = " . $this->convert_sql_id($p_id);
        } elseif (is_string($p_id)) {
            $l_sql .= " AND isys_relation_type__const = " . $this->convert_sql_text($p_id);
        }

        $l_sql .= " AND isys_relation_type__status = " . C__RECORD_STATUS__NORMAL . " ORDER BY isys_relation_type__title";

        $l_res = $this->retrieve($l_sql);

        // We can use this for caching purpose.
        if ($p_as_array === true) {
            $l_return = [];

            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_row;

                self::$m_relation_type_cache[$l_row['isys_relation_type__id']] = $l_row;
            }

            if (count($l_return) == 1) {
                return $l_return[0];
            } else {
                return $l_return;
            }
        }

        return $l_res;
    }

    /**
     * Get relation types.
     *
     * @param   array   $p_relation_ids
     * @param   integer $p_relation_type
     * @param   string  $p_condition
     *
     * @return  array
     */
    public function get_relation_types_as_array($p_relation_ids = null, $p_relation_type = null, $p_condition = null)
    {
        $l_condition = "";
        $l_rtypes = [];

        if ($p_relation_ids) {
            $l_condition .= "AND isys_relation_type__id " . $this->prepare_in_condition($p_relation_ids);
        }

        if ($p_relation_type !== null) {
            $l_condition .= " AND isys_relation_type__type = " . $this->convert_sql_int($p_relation_type);
        }

        $l_condition .= " " . $p_condition;

        $l_types = $this->get_relation_type(null, $l_condition);

        while ($l_row = $l_types->get_row()) {
            if ($l_row["isys_relation_type__category"] == "C__CATG__CONTACT") {
                $l_row["isys_relation_type__title"] = isys_application::instance()->container->get('language')
                        ->get($l_row["isys_relation_type__title"]) . " (" . isys_application::instance()->container->get('language')
                        ->get($l_row["isys_relation_type__master"]) . ")";
            }

            if ($l_row['isys_relation_type__const'] == 'C__RELATION_TYPE__ORGANIZATION') {
                $l_row["isys_relation_type__title"] = isys_application::instance()->container->get('language')
                        ->get($l_row["isys_relation_type__title"]) . " (" . isys_application::instance()->container->get('language')
                        ->get($this->get_cats_name_by_id_as_string(constant($l_row['isys_relation_type__category']))) . ")";
            }

            $l_rtypes[$l_row["isys_relation_type__id"]] = [
                "title"      => $l_row["isys_relation_type__title"],
                "master"     => $l_row["isys_relation_type__master"],
                "slave"      => $l_row["isys_relation_type__slave"],
                "default"    => $l_row["isys_relation_type__default"],
                "category"   => $l_row["isys_relation_type__category"],
                "const"      => $l_row["isys_relation_type__const"],
                "title_lang" => isys_application::instance()->container->get('language')
                    ->get($l_row["isys_relation_type__title"]),
                "weighting"  => $l_row["isys_relation_type__isys_weighting__id"],
                "type"       => $l_row["isys_relation_type__type"],
                "id"         => $l_row["isys_relation_type__id"]
                // in case if we need it as an json array
            ];
        }

        return $l_rtypes;
    }

    /**
     * Save method.
     *
     * @param   array   $p_cat_level
     * @param   integer $p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_status, $p_create = false)
    {
        try {
            $l_relation_type = $_POST["C__CATG__RELATION__RELATION_TYPE"];

            if ($_GET[C__CMDB__GET__CATLEVEL]) {
                $l_catdata = $this->get_data($_GET[C__CMDB__GET__CATLEVEL])
                    ->__to_array();
            }

            $l_master = $_POST["C__CATG__RELATION_MASTER__HIDDEN"] ?: $_POST["C__CATG__RELATION_MASTER"];
            $l_slave = $_POST["C__CATG__RELATION_SLAVE__HIDDEN"] ?: $_POST["C__CATG__RELATION_SLAVE"];

            /* Set default direction */
            $l_master_object = $l_master;
            $l_slave_object = $l_slave;

            if (isset($l_catdata)) {
                // Switch direction if selected and if relation is implicit.
                if ($l_catdata['isys_relation_type__editable'] && $_POST['C__RELATION__DIRECTION__CHANGED']) {
                    $l_master_object = $l_slave;
                    $l_slave_object = $l_master;
                } elseif ($l_catdata['isys_relation_type__type'] == C__RELATION__EXPLICIT && $_POST['C__CATG__RELATION__DIRECTION'] == C__RELATION_DIRECTION__I_DEPEND_ON) {
                    $l_master_object = $l_slave;
                    $l_slave_object = $l_master;
                }

                if (is_value_in_constants($_GET[C__CMDB__GET__OBJECTTYPE], ['C__OBJTYPE__IT_SERVICE', 'C__OBJTYPE__RELATION'])) {
                    // When saving a relation, the object is maybe unready. So we need to change the status here.
                    $this->set_object_status($l_master_object, C__RECORD_STATUS__NORMAL);
                }
            } else {
                if ($_POST['C__CATG__RELATION__DIRECTION'] == C__RELATION_DIRECTION__I_DEPEND_ON) {
                    $l_master_object = $l_slave;
                    $l_slave_object = $l_master;
                }
            }

            if (!isset($l_catdata["isys_catg_relation_list__id"]) || !$l_catdata["isys_catg_relation_list__id"]) {
                if (isset($l_master_object) && isset($l_slave_object)) {
                    // Get relation info.
                    $l_relation_data = $this->get_relation_type($l_relation_type, null, true);

                    // Create a relation object for this explicit relation.
                    $l_relation_object = $this->create_object($this->format_relation_name(
                        $this->get_obj_name_by_id_as_string($l_master_object),
                        $this->get_obj_name_by_id_as_string($l_slave_object),
                        $l_relation_data["isys_relation_type__master"]
                    ), defined_or_default('C__OBJTYPE__RELATION'));

                    // If relation object creation was successfull, fill relation category with its details.
                    if ($l_relation_object > 0) {
                        $l_id = $this->create(
                            $l_relation_object,
                            $l_master_object,
                            $l_slave_object,
                            $l_relation_type,
                            $_POST["C__CATG__RELATION__ITSERVICE"],
                            $_POST["C__CATG__RELATION__WEIGHTING"],
                            C__RECORD_STATUS__NORMAL,
                            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                        );

                        if ($l_id > 0) {
                            $p_cat_level = -1;

                            return $l_id;
                        }
                    } else {
                        throw new Exception("Could not create relation: Relation object missing.");
                    }
                } else {
                    throw new Exception('Error: Master or slave object missing');
                }
            } else {
                if (isset($l_master_object) && isset($l_slave_object)) {
                    $l_saved = $this->save(
                        $l_catdata["isys_catg_relation_list__id"],
                        $l_master_object,
                        $l_slave_object,
                        $l_relation_type,
                        $_POST["C__CATG__RELATION__ITSERVICE"],
                        $_POST["C__CATG__RELATION__WEIGHTING"],
                        $l_catdata["isys_catg_relation_list__status"],
                        $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                    );

                    if ($l_saved) {
                        $this->update_relation_object($l_catdata["isys_catg_relation_list__isys_obj__id"], $l_master_object, $l_slave_object, $l_relation_type);

                        return null;
                    }
                }

                return false;
            }
        } catch (Exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }

        return false;
    }

    /**
     * Formats a relation name, where $p_master_title is the language constant taken from isys_relation_type__master.
     *
     * @param   string $p_title_master
     * @param   string $p_title_slave
     * @param   string $p_master_title
     *
     * @return  string
     */
    public function format_relation_name($p_title_master, $p_title_slave, $p_master_title)
    {
        return $p_title_master . ' ' . isys_application::instance()->container->get('language')
                ->get($p_master_title) . ' ' . $p_title_slave;
    }

    /**
     * Returns the relation type of a category.
     *
     * @param   string $p_category
     *
     * @return  integer
     */
    public function get_relation_type_by_category($p_category)
    {
        $l_sql = "SELECT isys_relation_type__id " . "FROM isys_relation_type " . "WHERE isys_relation_type__category = " . $this->convert_sql_text($p_category) . ";";

        return (int)$this->retrieve($l_sql)
            ->get_row_value('isys_relation_type__id');
    }

    /**
     * Returns the relation type of a category.
     *
     * @param   string $p_category
     *
     * @return  integer
     */
    public function get_relation_type_by_const($p_const)
    {
        $l_sql = "SELECT isys_relation_type__id " . "FROM isys_relation_type " . "WHERE isys_relation_type__const = " . $this->convert_sql_text($p_const) . ";";

        return (int)$this->retrieve($l_sql)
            ->get_row_value('isys_relation_type__id');
    }

    /**
     * Returns relation info by relation object.
     *
     * @param   integer $p_relation_type
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_relation_type($p_relation_type, $p_condition = "")
    {
        return $this->get_data(null, null, "AND (isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id($p_relation_type) . ") " . $p_condition);
    }

    /**
     * Method for retrieving relation-data by a given object and an optional relation-type.
     *
     * @param  integer $p_context
     * @param  array   $p_request
     *
     * @return array|string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser_get_data_by_object_and_relation_type($p_context, $p_request = null)
    {
        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Not used.

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_return = [];
                $l_filters = [];

                if ($p_request[0] !== null) {
                    $l_filters = array_map('constant', explode(';', $p_request[0]));
                }

                if (count($l_filters) > 0) {
                    $l_res = $this->get_data(
                        null,
                        (int)$_GET[C__CMDB__GET__OBJECT],
                        "AND isys_catg_relation_list__isys_relation_type__id " . $this->prepare_in_condition($l_filters)
                    );
                } else {
                    $l_res = $this->get_data(null, (int)$_GET[C__CMDB__GET__OBJECT]);
                }

                while ($l_row = $l_res->get_row()) {
                    $l_return[] = [
                        '__checkbox__'                            => $l_row['isys_obj__id'],
                        $language->get('LC__UNIVERSAL__TITLE')    => $l_row['isys_obj__title'],
                        $language->get('LC__UNIVERSAL__RELATION') => $language->get($l_row['isys_relation_type__title'])
                    ];
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // Not necessary, because the selection will only contain objects.
        }

        return null;
    }

    /**
     * Returns relation info by relation object.
     *
     * @param   integer $p_object_id
     * @param   string  $p_condition
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_relation_object($p_object_id, $p_condition = "")
    {
        return $this->get_data(null, null, "AND (isys_catg_relation_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ") " . $p_condition);
    }

    /**
     * @param int $p_object_id
     * @param int $p_relation_object_id
     *
     * @return isys_component_dao_result
     */
    public function object_belongs_to_relation($p_object_id, $p_relation_object_id)
    {
        $l_sql = "SELECT isys_catg_relation_list__isys_obj__id " . "FROM isys_catg_relation_list " . "WHERE " . "(" . "(isys_catg_relation_list__isys_obj__id__master = " .
            $this->convert_sql_id($p_object_id) . ") OR " . "(isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_object_id) . ") " . ") AND " .
            "(isys_catg_relation_list__isys_obj__id = " . $this->convert_sql_id($p_relation_object_id) . ")" . ";";

        return ($this->retrieve($l_sql)
                ->num_rows() > 0) ? true : false;
    }

    /**
     * Gets the related objects from a parent object.
     *
     * @param integer  $p_parent_object
     * @param mixed    $p_relation_type May be an integer or an array of integers/constants.
     * @param null|int $status
     *
     * @return        isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_related_objects($p_parent_object, $p_relation_type = null, $status = null)
    {
        $l_sql = "SELECT 
            relation_object.isys_obj__id, 
            relation_object.isys_obj__title, 
            isys_obj_type__id, 
            isys_obj_type__title, 
            isys_catg_relation_list__isys_obj__id__master, 
            isys_catg_relation_list__isys_obj__id__slave, 
            isys_catg_relation_list__isys_obj__id,
            CASE isys_catg_relation_list__isys_obj__id__master WHEN '" . $p_parent_object . "' THEN isys_catg_relation_list__isys_obj__id__slave 
            ELSE isys_catg_relation_list__isys_obj__id__master END AS related 
            FROM isys_catg_relation_list
            INNER JOIN isys_obj relation_object ON relation_object.isys_obj__id = isys_catg_relation_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id";

        // Additional joins for requesting object statuses
        if (!empty($status)) {
            $l_sql .= " INNER JOIN isys_obj relatedObject ON relatedObject.isys_obj__id = CASE isys_catg_relation_list__isys_obj__id__master WHEN '" . $p_parent_object . "' 
                                                                                          THEN isys_catg_relation_list__isys_obj__id__slave 
                                                                                          ELSE isys_catg_relation_list__isys_obj__id__master END
                        INNER JOIN isys_obj parentObject ON parentObject.isys_obj__id = " . $this->convert_sql_id($p_parent_object);
        }

        $l_sql .= " WHERE (isys_catg_relation_list__isys_obj__id__master = " . $this->convert_sql_id($p_parent_object) . " 
                OR isys_catg_relation_list__isys_obj__id__slave = " . $this->convert_sql_id($p_parent_object) . ")";

        if (!is_null($p_relation_type)) {
            if (is_array($p_relation_type)) {
                $l_sql .= " AND isys_catg_relation_list__isys_relation_type__id " . $this->prepare_in_condition($p_relation_type);
            } else {
                $l_sql .= " AND isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id($p_relation_type);
            }
        }

        // Add status condition
        if (!empty($status)) {
            $status = $this->convert_sql_id($status);

            $l_sql .= ' AND (relation_object.isys_obj__status    = ' . $status . ')';
        }

        $l_sql .= " GROUP BY related;";

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_relation_members_by_obj_id($p_objID)
    {
        $l_query = 'SELECT 
            isys_catg_relation_list__isys_obj__id__master, 
            isys_catg_relation_list__isys_obj__id__slave, 
            isys_catg_relation_list__isys_relation_type__id 
            FROM isys_catg_relation_list 
            WHERE isys_catg_relation_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

        $l_row = $this->retrieve($l_query)
            ->get_row();

        return [
            $l_row["isys_catg_relation_list__isys_obj__id__master"],
            $l_row["isys_catg_relation_list__isys_obj__id__slave"],
            $l_row['isys_catg_relation_list__isys_relation_type__id']
        ];
    }

    public function get_relation_title_by_relation_data($p_master_title, $p_slave_title, $p_relation_type)
    {
        $l_relation_type = $this->get_relation_type($p_relation_type)
            ->get_row();

        return $p_master_title . ' ' . isys_application::instance()->container->get('language')
                ->get($l_relation_type['isys_relation_type__master']) . ' ' . $p_slave_title;
    }

    public function get_relation_title_by_relation_object($p_object_id)
    {
        list($l_master, $l_slave, $l_relation_type) = $this->get_relation_members_by_obj_id($p_object_id);

        return $this->get_relation_title_by_relation_data($l_master, $l_slave, $l_relation_type);
    }

    /**
     * Method for deleting a relation.
     *
     * @param   integer $p_cat_level
     *
     * @return  boolean
     */
    public function delete_relation($p_cat_level)
    {
        $query = 'SELECT 
            isys_catg_relation_list__isys_obj__id, 
            isys_catg_relation_list__isys_obj__id__master,
            isys_catg_relation_list__isys_obj__id__slave,
            relationObject.isys_obj__title as relationTitle,
            objectType.isys_obj_type__id,
            isys_relation_type__title 
            FROM isys_catg_relation_list 
            LEFT JOIN isys_obj as relationObject on relationObject.isys_obj__id = isys_catg_relation_list__isys_obj__id
            LEFT JOIN isys_obj as slaveObject on slaveObject.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
            LEFT JOIN isys_obj_type as objectType on slaveObject.isys_obj__isys_obj_type__id = objectType.isys_obj_type__id
            INNER JOIN isys_relation_type on isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
            WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($p_cat_level) ;

        $data = $this->retrieve($query)->get_row();

        if (is_array($data)) {
            if (isys_tenantsettings::get('logbook.relations.entries', 'initiated') === 'both') {
                $objectTypeId = $data['isys_obj_type__id'];

                $typeTranslationHack = str_replace(
                    'LC__CMDB__',
                    'C__',
                    $data['isys_relation_type__title']
                );

                $changes = [
                    $typeTranslationHack => [
                        'from' => $data['relationTitle'],
                        'to'   => '-'
                    ]
                ];

                if (Context::instance()->getContextCustomer() !== Context::CONTEXT_LDAP_SYNC) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__RELATION_DELETED',
                            $this->m_strLogbookSQL,
                            $data['isys_catg_relation_list__isys_obj__id__slave'],
                            $objectTypeId,
                            isys_application::instance()->container->get('language')->get($data['isys_relation_type__title']),
                            serialize($changes)
                        );
                }
            }

            return $this->delete_object($data['isys_catg_relation_list__isys_obj__id']);
        } else {
            return false;
        }
    }

    /**
     * Relation handling.
     *
     * @param   integer $p_category_id
     * @param   string  $p_table
     * @param   integer $p_const
     * @param   integer $p_relation_id
     * @param   integer $p_master_obj_id
     * @param   integer $p_slave_obj_id
     * @param   integer $p_status
     *
     * @throws  Exception
     * @throws  isys_exception_cmdb
     * @throws  isys_exception_dao
     */
    public function handle_relation(
        $p_category_id,
        $p_table,
        $p_const,
        $p_relation_id = null,
        $p_master_obj_id = null,
        $p_slave_obj_id = null,
        $p_status = C__RECORD_STATUS__NORMAL
    ) {
        try {
            $relationType = $this->get_relation_type($p_const, null, true);

            if (!empty($relationType)) {
                $relationTypeId = $relationType['isys_relation_type__id'];
                if ($this->m_relation_type != $relationTypeId) {
                    $this->m_relation_type = $relationTypeId;

                    if (!$this->set_relation_field($p_table)) {
                        throw new isys_exception_cmdb(sprintf('Relation field in table %s missing!', $p_table));
                    }
                }

                $defaultWeighting = $relationType['isys_relation_type__isys_weighting__id'] ?: defined_or_default('C__WEIGHTING__5', 5);

                if ($p_master_obj_id > 0 && $p_slave_obj_id > 0) {
                    if ($p_relation_id > 0) {
                        // Save this existing relation.
                        $method = 'save_relation';
                        $defaultWeighting = null;
                    } else {
                        $method = 'create_relation';
                    }
                    return $this->$method($p_table, $p_category_id, $p_master_obj_id, $p_slave_obj_id, $relationTypeId, $defaultWeighting, $p_status);
                }

                if ($p_relation_id > 0) {
                    // Delete this relation (In case of an unattachment).
                    $l_sql = 'UPDATE ' . $p_table . ' SET ' . $this->m_relation_field . ' = NULL ' . 'WHERE ' . $p_table . '__id = ' . $this->convert_sql_id($p_category_id);

                    if ($this->update($l_sql)) {
                        if ($this->apply_update()) {
                            return $this->delete_relation($p_relation_id);
                        }
                    }
                }
            } else {
                throw new isys_exception_cmdb('Error: You provided a wrong relation type for \'' . $p_table . '\': \'' . $p_const . '\'.' .
                    (!is_numeric($p_const) ? ' (The constant cache is maybe not available here)' : ''));
            }
        } catch (isys_exception_cmdb $e) {
            throw $e;
        }
    }

    /**
     * Post rank is called after a regular rank.
     *
     * @param  integer $p_list_id
     * @param  integer $p_direction
     * @param  string  $p_table
     */
    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_data = $this->get_data($p_list_id)
            ->__to_array();

        if ($l_data['isys_catg_relation_list__isys_obj__id'] > 0) {
            $this->rank_record($l_data['isys_catg_relation_list__isys_obj__id'], $p_direction, 'isys_obj');
        }
    }

    /**
     * Set status of all relation objects and all category entries referenced to the object id.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_status
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function set_relation_status($p_obj_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_res = $this->get_data(null, $p_obj_id);

        if ($l_res->num_rows() > 0) {
            $l_cond = [];

            while ($l_row = $l_res->get_row()) {
                $l_cond[] = " isys_obj__id = " . $this->convert_sql_id($l_row["isys_catg_relation_list__isys_obj__id"]);
            }

            $l_sql = "UPDATE isys_obj SET isys_obj__status = " . $this->convert_sql_int($p_status) . " WHERE TRUE AND (" . implode(' OR ', $l_cond) . ")";

            if ($this->update($l_sql)) {
                return $this->apply_update();
            } else {
                throw new isys_exception_dao("Relations could not be ranked for Object ID: " . $p_obj_id, '');
            }
        }

        return false;
    }

    /**
     * Set the status of a given relation.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_cat_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_update = "UPDATE isys_catg_relation_list SET isys_catg_relation_list__status = " . $this->convert_sql_int($p_status) . " WHERE isys_catg_relation_list__id = " .
            $this->convert_sql_id($p_cat_id) . ";";

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Sets it service for relation
     *
     * @param $p_id
     * @param $p_it_service
     *
     * @return bool
     */
    public function set_it_service($p_id, $p_it_service)
    {
        $l_update = 'UPDATE isys_catg_relation_list SET isys_catg_relation_list__isys_obj__id__itservice = ' . $this->convert_sql_id($p_it_service) . ' ' .
            'WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Update relation object status via relation id
     *
     * @param int $p_id
     * @param int $p_status
     *
     * @return bool
     * @throws isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_relation_object_status($p_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_update = 'UPDATE isys_catg_relation_list AS rel INNER JOIN isys_obj AS obj ON obj.isys_obj__id = rel.isys_catg_relation_list__isys_obj__id
            SET obj.isys_obj__status = ' . $this->convert_sql_int($p_status) . ' WHERE rel.isys_catg_relation_list__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Returns the condition for selecting explicit relations.
     *
     * @return  string
     */
    public function get_export_condition()
    {
        return " AND isys_relation_type__type = " . $this->convert_sql_int(C__RELATION__EXPLICIT) . " ";
    }

    /**
     * Checks if category tables has a relation to isys_catg_relation_list.
     *
     * @param  string $table
     *
     * @return boolean
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function has_relation_field($table)
    {
        if (!$this->table_exists($table)) {
            return false;
        }

        if ($table !== 'isys_catg_relation_list') {
            $l_row = $this->retrieve('SHOW CREATE TABLE ' . $table . ';')->get_row();

            return (strpos($l_row['Create Table'], 'isys_catg_relation_list') !== false);
        }

        return false;
    }

    /**
     * Adds a new relation type. Is only for the enhanced custom fields module.
     *
     * @param   string  $p_title
     * @param   string  $p_title_master
     * @param   string  $p_title_slave
     * @param   integer $p_direction
     * @param   string  $p_const
     * @param   integer $p_editable
     * @param   integer $p_status
     * @param   integer $p_weighting
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @deprecated  Please consider using `idoit\Module\System\Model\RelationType->save()`
     */
    public function add_new_relation_type(
        $p_title,
        $p_title_master,
        $p_title_slave,
        $p_direction,
        $p_const = null,
        $p_editable = 1,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_weighting = 5,
        $p_type = C__RELATION__IMPLICIT
    ) {
        $l_sort = $this->retrieve('SELECT MAX(isys_relation_type__sort) AS sort FROM isys_relation_type;')
                ->get_row_value('sort') + 1;

        $l_sql_insert = 'INSERT INTO isys_relation_type SET
			isys_relation_type__title = ' . $this->convert_sql_text($p_title) . ',
			isys_relation_type__master = ' . $this->convert_sql_text($p_title_master) . ',
			isys_relation_type__slave = ' . $this->convert_sql_text($p_title_slave) . ',
			isys_relation_type__type = ' . $this->convert_sql_int($p_type) . ',
			isys_relation_type__default = ' . $this->convert_sql_int($p_direction) . ',
			isys_relation_type__const = ' . (is_null($p_const) ? 'NULL' : $this->convert_sql_text($p_const)) . ',
			isys_relation_type__category = "C__CATG__CUSTOM_FIELDS",
			isys_relation_type__editable = ' . $this->convert_sql_boolean($p_editable) . ',
			isys_relation_type__sort = ' . $this->convert_sql_int($l_sort) . ',
			isys_relation_type__isys_weighting__id = ' . $this->convert_sql_id($p_weighting) . ',
			isys_relation_type__status = ' . $this->convert_sql_int($p_status) . ';';

        return ($this->update($l_sql_insert) && $this->apply_update());
    }

    /**
     * Updates selected relation type. Is only for the enhanced custom fields module
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_title_master
     * @param   string  $p_title_slave
     * @param   integer $p_direction
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @deprecated  Please consider using `idoit\Module\System\Model\RelationType->save()`
     */
    public function update_relation_type($p_id, $p_title, $p_title_master, $p_title_slave, $p_direction, $p_type)
    {
        $l_sql = 'UPDATE isys_relation_type SET
			isys_relation_type__title = ' . $this->convert_sql_text($p_title) . ',
			isys_relation_type__master = ' . $this->convert_sql_text($p_title_master) . ',
			isys_relation_type__slave = ' . $this->convert_sql_text($p_title_slave) . ',
			isys_relation_type__type = ' . $this->convert_sql_int($p_type) . ',
			isys_relation_type__default = ' . $this->convert_sql_int($p_direction) . '
			WHERE isys_relation_type__id = ' . $this->convert_sql_id($p_id);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Deletes a relation type. Is only for the enhanced custom fields module.
     *
     * @param   integer|array $p_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function remove_relation_type($p_id)
    {
        if (is_array($p_id)) {
            $l_sql_condition = ' WHERE isys_relation_type__id IN (' . rtrim(implode(',', $p_id), ',') . ');';
        } else {
            $l_sql_condition = ' WHERE isys_relation_type_id = ' . $this->convert_sql_id($p_id);
        }

        $l_delete = 'SELECT isys_catg_relation_list__isys_obj__id FROM isys_catg_relation_list ' .
            'INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id ' . $l_sql_condition;

        $l_res = $this->retrieve($l_delete);

        // Delete all relations with the specified relation type.
        if ($l_res->num_rows() > 0) {
            $l_objects = [];
            while ($l_row = $l_res->get_row()) {
                $l_objects[] = $l_row['isys_catg_relation_list__isys_obj__id'];
            }

            foreach ($l_objects as $l_obj_id) {
                $this->delete_object_and_relations($l_obj_id);
            }
        }

        return ($this->update('DELETE FROM isys_relation_type ' . $l_sql_condition) && $this->apply_update());
    }

    /**
     * @param   integer $p_relation_type_id
     * @param   integer $p_default_weighting_id
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function update_relation_type_weighting($p_relation_type_id, $p_default_weighting_id)
    {
        $l_sql = 'UPDATE isys_relation_type
			SET isys_relation_type__isys_weighting__id = ' . $this->convert_sql_id($p_default_weighting_id) . '
			WHERE isys_relation_type__id = ' . $this->convert_sql_id($p_relation_type_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     *
     * @param   string $p_table
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_database
     */
    private function set_relation_field($p_table)
    {
        if ($p_table == 'isys_catg_custom_fields_list') {
            $this->m_relation_field = 'isys_catg_custom_fields_list__isys_catg_relation_list__id';

            return true;
        }

        $l_res = $this->retrieve("SHOW FIELDS FROM " . $p_table . " WHERE FIELD LIKE '%isys_catg_relation_list__id'");

        if (is_countable($l_res) && count($l_res)) {
            $l_row = $l_res->get_row();
            if (strstr($l_row['Field'], 'isys_catg_relation_list__id')) {
                $this->m_relation_field = $l_row['Field'];

                return true;
            }
        }

        return false;
    }

    /**
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function reset()
    {
        $this->m_relation_type = '';
        $this->m_relation_field = '';

        return $this;
    }
}
