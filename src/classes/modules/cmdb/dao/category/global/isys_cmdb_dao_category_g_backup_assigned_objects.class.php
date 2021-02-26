<?php

/**
 * i-doit
 *
 * DAO: global category for backup systems (reverse view).
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_backup_assigned_objects extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'backup_assigned_objects';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__BACKUP__ASSIGNED_OBJECTS';

    /**
     * Category's constant.
     *
     * @var   string
     * @todo  Breaks with developer guidelines!
     */
    protected $m_category_const = 'C__CATG__BACKUP__ASSIGNED_OBJECTS';

    /**
     * Category's identifier.
     *
     * @var   integer
     * @todo  Breaks with developer guidelines!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__BACKUP__ASSIGNED_OBJECTS;

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_backup_list__isys_obj__id';

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
     * Field for the object id
     *
     * @var string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = 'isys_cmdb_dao_category_g_backup';

    /**
     * Main table where properties are stored persistently.
     *
     * @var   string
     * @todo  Breaks with developer guidelines!
     */
    protected $m_table = 'isys_catg_backup_list';

    /**
     * Category's template.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_tpl = 'catg__backup.tpl';

    /**
     * Save global category backup element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_backup_list__status"];

        if ($p_create || !is_array($l_catdata)) {
            $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL, $_POST['C__CATG__BACKUP_TITLE'], $_POST['C__CATG__BACKUP__ASSIGNED_OBJECT__HIDDEN'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()], $_POST['C__CATG__BACKUP__TYPE'], $_POST['C__CATG__BACKUP__CYCLE'],
                $_POST['C__CATG__BACKUP__PATH_TO_SAVE']);

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }

            $p_cat_level = 1;

            return $l_id;
        } else {
            if ($l_catdata['isys_catg_backup_list__id'] != "") {
                $l_bRet = $this->save($l_catdata['isys_catg_backup_list__id'], C__RECORD_STATUS__NORMAL, $_POST['C__CATG__BACKUP_TITLE'],
                    $_POST['C__CATG__BACKUP__ASSIGNED_OBJECT__HIDDEN'], $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()],
                    $_POST['C__CATG__BACKUP__TYPE'], $_POST['C__CATG__BACKUP__CYCLE'], $_POST['C__CATG__BACKUP__PATH_TO_SAVE']);

                $this->m_strLogbookSQL = $this->get_last_query();
            }

            return $l_bRet == true ? null : $l_intErrorCode;
        }
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer   $p_cat_level
     * @param   array|int $p_newRecStatus
     * @param   string    $p_title
     * @param   integer   $p_connectedObjID
     * @param   string    $p_description
     * @param   integer   $p_backup_type
     * @param   integer   $p_backup_cycle
     * @param   string    $p_path_to_save
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus = C__RECORD_STATUS__NORMAL,
        $p_title = null,
        $p_connectedObjID = null,
        $p_description = '',
        $p_backup_type = null,
        $p_backup_cycle = null,
        $p_path_to_save = null
    ) {
        if ($p_backup_type != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $p_path_to_save = null;
        }

        $l_strSql = "UPDATE isys_catg_backup_list SET
			isys_catg_backup_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ",
			isys_catg_backup_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_backup_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_backup_list__isys_backup_type__id = " . $this->convert_sql_id($p_backup_type) . ",
			isys_catg_backup_list__isys_backup_cycle__id = " . $this->convert_sql_id($p_backup_cycle) . ",
			isys_catg_backup_list__path_to_save = " . $this->convert_sql_text($p_path_to_save) . ",
			isys_catg_backup_list__status = " . $this->convert_sql_id($p_newRecStatus) . "
			WHERE isys_catg_backup_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql)) {
            if ($this->apply_update()) {
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
                $l_data = $this->get_data($p_cat_level)
                    ->__to_array();

                $l_relation_dao->handle_relation($p_cat_level, "isys_catg_backup_list", defined_or_default('C__RELATION_TYPE__BACKUP'),
                    $l_data["isys_catg_backup_list__isys_catg_relation_list__id"], $l_data["isys_connection__isys_obj__id"], $p_connectedObjID);

                return true;
            }
        }

        return false;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_backup__id $p_fk_id
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_connectedObjID
     * @param   string  $p_description
     * @param   integer $p_backup_type
     * @param   integer $p_backup_cycle
     * @param   string  $p_path_to_save
     *
     * @return  mixed  The newly created ID (integer) or false (boolean).
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_newRecStatus = C__RECORD_STATUS__NORMAL,
        $p_title = null,
        $p_connectedObjID = null,
        $p_description = null,
        $p_backup_type = null,
        $p_backup_cycle = null,
        $p_path_to_save = null
    ) {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());

        if (empty($p_newRecStatus)) {
            $p_newRecStatus = C__RECORD_STATUS__NORMAL;
        }

        if ($p_backup_type != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $p_path_to_save = null;
        }

        $l_strSql = "INSERT INTO isys_catg_backup_list SET " . "isys_catg_backup_list__title = " . $this->convert_sql_text($p_title) . ", " .
            "isys_catg_backup_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_objID)) . ", " . "isys_catg_backup_list__description = " .
            $this->convert_sql_text($p_description) . ", " . "isys_catg_backup_list__status = " . $this->convert_sql_id($p_newRecStatus) . ", " .
            "isys_catg_backup_list__isys_backup_type__id = " . $this->convert_sql_id($p_backup_type) . ", " . "isys_catg_backup_list__isys_backup_cycle__id = " .
            $this->convert_sql_id($p_backup_cycle) . ", " . "isys_catg_backup_list__path_to_save = " . $this->convert_sql_text($p_path_to_save) . ", " .
            "isys_catg_backup_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID);

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

            $l_relation_dao->handle_relation($l_last_id, "isys_catg_backup_list", defined_or_default('C__RELATION_TYPE__BACKUP'), null, $p_objID, $p_connectedObjID);

            return $l_last_id;
        } else {
            return false;
        }
    }

    public function get_count($p_obj_id = null)
    {

        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_catg_backup_list__id) AS count FROM isys_catg_backup_list " .
            "LEFT JOIN isys_connection ON isys_catg_backup_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_obj ON  isys_obj__id = isys_catg_backup_list__isys_obj__id " . "WHERE TRUE ";

        if (!empty($l_obj_id)) {
            $l_sql .= " AND (isys_connection__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ")";
        }

        $l_sql .= " AND (isys_catg_backup_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ")";

        $l_data = $this->retrieve($l_sql)
            ->get_row();

        return $l_data["count"];
    }

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
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_backup_list " . "LEFT JOIN isys_connection ON isys_catg_backup_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_obj ON  isys_obj__id = isys_catg_backup_list__isys_obj__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_backup_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_backup_list__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function properties()
    {
        $l_dao = new $this->m_reverse_category_of($this->get_database_component());

        // Basically we use the same properties as in the original category.
        $l_properties = $l_dao->properties();

        $l_data_join = [
            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_backup_list', 'LEFT', 'isys_connection__id', 'isys_catg_backup_list__isys_connection__id'),
            idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_backup_list__isys_obj__id', 'isys_obj__id')
        ];

        // With some very minor changes...
        $l_properties['title'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = true;
        $l_properties['title'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = false;
        $l_properties['title'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;
        $l_properties['title'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN] = $l_data_join;
        $l_properties['title'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_backup_list__title FROM isys_catg_backup_list
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_backup_list__isys_connection__id', 'isys_connection', 'isys_connection__id',
            'isys_connection__isys_obj__id');

        $l_properties['path_to_save'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = true;
        $l_properties['path_to_save'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = false;
        $l_properties['path_to_save'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;
        $l_properties['path_to_save'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN] = $l_data_join;
        $l_properties['path_to_save'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT isys_catg_backup_list__path_to_save FROM isys_catg_backup_list
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_backup_list__isys_connection__id', 'isys_connection', 'isys_connection__id',
            'isys_connection__isys_obj__id');

        $l_properties['backup'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = true;
        $l_properties['backup'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = false;
        $l_properties['backup'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;
        $l_properties['backup'][C__PROPERTY__DATA][C__PROPERTY__DATA__JOIN] = $l_data_join;
        $l_properties['backup'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_obj
                INNER JOIN isys_catg_backup_list ON isys_catg_backup_list__isys_obj__id = isys_obj__id
                INNER JOIN isys_connection ON isys_connection__id = isys_catg_backup_list__isys_connection__id', 'isys_connection', 'isys_connection__id',
            'isys_connection__isys_obj__id');

        $l_properties['backup_type'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = false;
        $l_properties['backup_type'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = false;
        $l_properties['backup_type'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;
        $l_properties['cycle'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__REPORT] = false;
        $l_properties['cycle'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] = false;
        $l_properties['cycle'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;
        $l_properties['description'][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] = false;

        $l_properties['backup'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] = 'LC__CMDB__CATG__BACKUP__BACKUPS';
        $l_properties['backup'][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] = 'isys_catg_backup_list__isys_obj__id';
        $l_properties['backup'][C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] = 'object';
        unset($l_properties['backup'][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]);

        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__BACKUP__ASSIGNED_OBJECTS', 'C__CATG__BACKUP__ASSIGNED_OBJECTS');

        return $l_properties;
    }

    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if (($p_category_data['data_id'])) {
                        return $this->create($p_object_id, C__RECORD_STATUS__NORMAL, $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['backup'][C__DATA__VALUE], $p_category_data['properties']['description'][C__DATA__VALUE]);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['backup'][C__DATA__VALUE], $p_category_data['properties']['description'][C__DATA__VALUE]);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}

?>