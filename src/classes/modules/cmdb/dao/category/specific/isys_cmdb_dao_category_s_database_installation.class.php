<?php

/**
 * i-doit
 *
 * DAO: specific category for applications with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_installation extends isys_cmdb_dao_category_s_application_assigned_obj
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_installation';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao = new isys_cmdb_dao_category_g_application($this->m_db);

        if ($p_obj_id > 0) {
            $l_condition = ' AND isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($p_obj_id);
        } else {
            $l_condition = '';
        }

        return $l_dao->get_data($p_cats_list_id, null, $l_condition, $p_filter, $p_status);
    }

    /**
     * Get assigned databases by application assignment id
     *
     * @param int $applicationId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getAssignedDatabases($applicationId)
    {
        $data = [];

        $query = 'SELECT isys_catg_database_list__id as id, isys_obj__id as objectId, isys_obj__isys_obj_type__id AS objectTypeId, isys_catg_database_list__title as title 
          FROM isys_catg_database_list
          INNER JOIN isys_obj ON isys_obj__id = isys_catg_database_list__isys_obj__id
          WHERE isys_catg_database_list__isys_catg_application_list__id = ' . $this->convert_sql_id($applicationId);
        $result = $this->retrieve($query);
        if ($result instanceof isys_component_dao_result && count($result)) {
            while ($row = $result->get_row()) {
                $data[$row['id']] = [
                    C__CMDB__GET__OBJECT => $row['objectId'],
                    C__CMDB__GET__OBJECTTYPE => $row['objectTypeId'],
                    'title' => $row['title']
                ];
            }
        }
        return $data;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        $properties = parent::properties();

        $properties['assigned_databases'] = array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
            C__PROPERTY__INFO     => [
                C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__INSTALLED_ON',
                C__PROPERTY__INFO__DESCRIPTION => 'Installed on',
                C__PROPERTY__INFO__BACKWARD    => true
            ],
            C__PROPERTY__DATA     => [
                C__PROPERTY__DATA__FIELD            => 'isys_catg_application_list__id',
                C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_database_list__title
                            FROM isys_catg_application_list
                              LEFT JOIN isys_catg_database_list ON isys_catg_database_list__isys_catg_application_list__id = isys_catg_application_list__id',
                    'isys_catg_application_list',
                    'isys_catg_application_list__id',
                    'isys_catg_application_list__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_application_list__id'])
                ),
                C__PROPERTY__DATA__JOIN             => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_catg_application_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_database_list',
                        'LEFT',
                        'isys_catg_application_list__id',
                        'isys_catg_database_list__isys_catg_application_list__id'
                    )
                ]
            ],
            C__PROPERTY__UI       => [
                C__PROPERTY__UI__ID     => 'C__CATS__DATABASE_INSTALLATION',
                C__PROPERTY__UI__PARAMS => [
                    isys_popup_browser_object_ng::C__CAT_FILTER => "C__CATG__APPLICATION"
                ]
            ],
            C__PROPERTY__PROVIDES => [
                C__PROPERTY__PROVIDES__VALIDATION => false
            ]
        ]);

        return $properties;
    }
}
