<?php

/**
 * i-doit
 *
 * Export DAO
 *
 * @package    i-doit
 * @subpackage Modules
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
class isys_statistics_dao extends isys_module_dao
{
    private $m_cmdb_dao = null;

    /**
     * @param   integer $objectTypeId
     *
     * @return  integer
     * @throws  Exception
     */
    public function count_objects($objectTypeId = null)
    {
        // @see  ID-3349 Skip certain objects.
        $skipObjectCount = [
            "'C__OBJ__ROOT_LOCATION'",
            "'C__OBJ__PERSON_GUEST'",
            "'C__OBJ__PERSON_READER'",
            "'C__OBJ__PERSON_EDITOR'",
            "'C__OBJ__PERSON_AUTHOR'",
            "'C__OBJ__PERSON_ARCHIVAR'",
            "'C__OBJ__PERSON_ADMIN'",
            "'C__OBJ__PERSON_GROUP_READER'",
            "'C__OBJ__PERSON_GROUP_EDITOR'",
            "'C__OBJ__PERSON_GROUP_AUTHOR'",
            "'C__OBJ__PERSON_GROUP_ARCHIVAR'",
            "'C__OBJ__PERSON_GROUP_ADMIN'",
            "'C__OBJ__NET_GLOBAL_IPV4'",
            "'C__OBJ__NET_GLOBAL_IPV6'",
            "'C__OBJ__PERSON_API_SYSTEM'",
            "'C__OBJ__RACK_SEGMENT__2SLOT'",
            "'C__OBJ__RACK_SEGMENT__4SLOT'",
            "'C__OBJ__RACK_SEGMENT__8SLOT'"
        ];

        $skipObjectTypeCount = [
            "'C__OBJTYPE__RELATION'",
            "'C__OBJTYPE__PARALLEL_RELATION'",
            "'C__OBJTYPE__NAGIOS_SERVICE'",
            "'C__OBJTYPE__NAGIOS_HOST_TPL'",
            "'C__OBJTYPE__NAGIOS_SERVICE_TPL'"
        ];

        $condition = ' AND isys_obj__id NOT IN (SELECT isys_obj__id FROM isys_obj WHERE isys_obj__const IN  (' . implode(',', $skipObjectCount) . ')) ' .
            'AND isys_obj__isys_obj_type__id NOT IN (SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const IN (' . implode(',', $skipObjectTypeCount) . '))';

        if ($this->m_cmdb_dao) {
            return $this->m_cmdb_dao->count_objects(null, $objectTypeId, true, $condition);
        }

        throw new Exception('Could not count objects: isys_cmdb_dao was not found.');
    }

    /**
     * @return mixed
     */
    public function count_cmdb_references()
    {
        $l_sql = "SELECT COUNT(*) AS counter FROM isys_connection";
        $l_dao = $this->retrieve($l_sql);
        $l_row = $l_dao->get_row();

        return $l_row["counter"];
    }

    /**
     * @return array
     */
    public function get_db_version()
    {
        global $g_comp_database_system;

        if (empty($this->m_isys_info)) {
            $l_mret = $g_comp_database_system->query("SELECT * FROM isys_db_init;");

            while ($l_mrow = $g_comp_database_system->fetch_row_assoc($l_mret)) {
                if ($l_mrow["isys_db_init__key"] == "version") {
                    $l_version = $l_mrow["isys_db_init__value"];
                }

                if ($l_mrow["isys_db_init__key"] == "revision") {
                    $l_revision = (int)$l_mrow["isys_db_init__value"];
                }

                if ($l_mrow["isys_db_init__key"] == "title") {
                    $l_title = $l_mrow["isys_db_init__value"];
                }
            }

            $this->m_isys_info = [
                "name"     => @$l_title,
                "version"  => @$l_version,
                "revision" => @$l_revision,
                "type"     => "System"
            ];

            unset($l_version, $l_revision);
        }

        return $this->m_isys_info;
    }

    /**
     * @param [int $p_id]
     *
     * @return isys_component_dao_result
     */
    public function get_data()
    {
        return false;
    }

    /**
     * @param isys_component_database $p_db
     * @param isys_cmdb_dao           $p_cmdb_dao
     */
    public function __construct(isys_component_database $p_db, isys_cmdb_dao $p_cmdb_dao)
    {
        parent::__construct($p_db);
        $this->m_cmdb_dao = $p_cmdb_dao;
    }
}
