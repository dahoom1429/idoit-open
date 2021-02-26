<?php

/**
 *
 * @package    i-doit
 * @author     Dennis Stücken <dstuecken@i-doit.org>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_relation extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        if (isset($_GET[C__CMDB__GET__OBJECTTYPE], $_GET[C__CMDB__GET__CATG]) && defined('C__OBJTYPE__IT_SERVICE') && $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__IT_SERVICE) {
            return $_GET[C__CMDB__GET__CATG];
        }

        return defined_or_default('C__CATG__RELATION');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string     $table
     * @param   string|int $objectId
     * @param   string|int $recordStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_result($table = null, $objectId, $recordStatus = null)
    {
        $objectId = $this->convert_sql_id($objectId);
        $recordStatus = $this->convert_sql_int(empty($recordStatus) ? $this->get_rec_status() : $recordStatus);

        if (is_value_in_constants($_GET[C__CMDB__GET__CATG], ['C__CATG__IT_SERVICE_RELATIONS', 'C__CATG__RELATION_ROOT'])) {
            $sqlSelect = "SELECT main.*, isys_relation_type.*, isys_weighting.*, main_obj.*
                FROM isys_catg_relation_list main
                LEFT JOIN isys_relation_type ON main.isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
                LEFT JOIN isys_weighting ON main.isys_catg_relation_list__isys_weighting__id = isys_weighting__id
                INNER JOIN isys_obj main_obj ON main.isys_catg_relation_list__isys_obj__id = main_obj.isys_obj__id
                LEFT JOIN isys_catg_relation_list object1_master ON object1_master.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__master
                LEFT JOIN isys_catg_relation_list object2_master ON object2_master.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__master
                LEFT JOIN isys_catg_relation_list object1_slave ON object1_slave.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__slave
                LEFT JOIN isys_catg_relation_list object2_slave ON object2_slave.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__slave
                WHERE TRUE
                AND (main_obj.isys_obj__id = {$objectId} OR main.isys_catg_relation_list__isys_obj__id__itservice = {$objectId})
				AND main.isys_catg_relation_list__isys_obj__id__master != {$objectId}
				AND main.isys_catg_relation_list__isys_obj__id__slave != {$objectId}
				AND main.isys_catg_relation_list__status = {$recordStatus}";
        } else {
            // @see ID-2772
            $sqlSelect = "SELECT main.*, isys_relation_type.*, isys_weighting.*, main_obj.*
	            FROM (
	                SELECT isys_catg_relation_list__id
                    FROM isys_catg_relation_list
                    WHERE isys_catg_relation_list__isys_obj__id__slave = {$objectId} OR isys_catg_relation_list__isys_obj__id__master = {$objectId}

	                UNION DISTINCT

	                SELECT main.isys_catg_relation_list__id
                    FROM isys_catg_relation_list main
                    LEFT JOIN isys_catg_relation_list object1_slave ON object1_slave.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__slave
                    WHERE object1_slave.isys_catg_relation_list__isys_obj__id__slave = {$objectId} OR object1_slave.isys_catg_relation_list__isys_obj__id__master = {$objectId}

	                UNION DISTINCT

	                SELECT main.isys_catg_relation_list__id
                    FROM isys_catg_relation_list main
                    LEFT JOIN isys_catg_relation_list object1_slave ON object1_slave.isys_catg_relation_list__isys_obj__id = main.isys_catg_relation_list__isys_obj__id__master
                    WHERE object1_slave.isys_catg_relation_list__isys_obj__id__slave = {$objectId} OR object1_slave.isys_catg_relation_list__isys_obj__id__master = {$objectId}
                ) AS filter
	            INNER JOIN isys_catg_relation_list main  ON main.isys_catg_relation_list__id = filter.isys_catg_relation_list__id
	            LEFT JOIN isys_relation_type ON main.isys_catg_relation_list__isys_relation_type__id = isys_relation_type__id
	            LEFT JOIN isys_weighting ON main.isys_catg_relation_list__isys_weighting__id = isys_weighting__id
	            INNER JOIN isys_obj main_obj ON main.isys_catg_relation_list__isys_obj__id = main_obj.isys_obj__id
	            WHERE main.isys_catg_relation_list__status = {$recordStatus}";
        }


        // @see  ID-6435, ID-5543 We had to update this code due to a second (consequential) bug.
        $sqlCondition = " AND (SELECT 1 FROM isys_obj WHERE isys_obj__id = main.isys_catg_relation_list__isys_obj__id__slave AND isys_obj__status = {$recordStatus})
            AND (SELECT 1 FROM isys_obj WHERE isys_obj__id = main.isys_catg_relation_list__isys_obj__id__master AND isys_obj__status = {$recordStatus})";

        return $this->retrieve($sqlSelect . $sqlCondition . ';');
    }

    /**
     * @param array $p_row
     */
    public function modify_row(&$p_row)
    {
        $language = isys_application::instance()->container->get('language');
        $l_quickinfo = new isys_ajax_handler_quick_info();

        // Get Master object.
        $p_row['master'] = $l_quickinfo->getQuickInfoReplacement(
            $p_row['isys_catg_relation_list__isys_obj__id__master'],
            $this->m_cat_dao->get_obj_name_by_id_as_string($p_row['isys_catg_relation_list__isys_obj__id__master'])
        );

        // Get Slave object.
        $p_row['slave'] = $l_quickinfo->getQuickInfoReplacement(
            $p_row['isys_catg_relation_list__isys_obj__id__slave'],
            $this->m_cat_dao->get_obj_name_by_id_as_string($p_row['isys_catg_relation_list__isys_obj__id__slave'])
        );

        // Assign relation description.
        $p_row['relation'] = $p_row['isys_relation_type__master'];

        /*
         * Check if current object is equal to the master relation object.
         * If true, set Slave to be Object 1 and Master to be object 2, use standard direction otherwise.
         */
        if ($p_row['isys_catg_relation_list__isys_obj__id__master'] == $_GET[C__CMDB__GET__OBJECT] ||
            $this->m_cat_dao->object_belongs_to_relation($_GET[C__CMDB__GET__OBJECT], $p_row['isys_catg_relation_list__isys_obj__id__master'])) {
            $p_row['slave'] .= ' (Slave)';
            $p_row['master'] .= ' (Master)';
        } else {
            $l_tmp = $p_row["slave"];

            $p_row['slave'] = $p_row['master'] . ' (Master)';
            $p_row['master'] = $l_tmp . ' (Slave)';
            $p_row['relation'] = $p_row['isys_relation_type__slave'];
        }

        // Display correct IT Service, if no IT Service is seleceted, show keyword "Global".
        if (empty($p_row['isys_catg_relation_list__isys_obj__id__itservice'])) {
            $p_row['itservice'] = 'Global';
        } else {
            $p_row['itservice'] = $l_quickinfo->getQuickInfoReplacement(
                $p_row['isys_catg_relation_list__isys_obj__id__itservice'],
                $this->m_cat_dao->get_obj_name_by_id_as_string($p_row['isys_catg_relation_list__isys_obj__id__itservice'])
            );
        }

        // Retrieve relation type.
        switch ($p_row['isys_relation_type__type']) {
            case C__RELATION__EXPLICIT:
                $p_row['type'] = $language->get('LC__CMDB__RELATION_EXPLICIT');
                break;
            default:
            case C__RELATION__IMPLICIT:
                $p_row['type'] = $language->get('LC__CMDB__RELATION_IMPLICIT');
                break;
        }

        $l_dao = new isys_cmdb_dao_category_s_parallel_relation($this->m_db);
        $l_siblibgs = $l_dao->get_pool_siblings_as_array($p_row['isys_obj__id']);

        $p_row['parallel'] = sprintf($language->get('LC__PARALLEL_RELATIONS__X_RELATIONS'), is_countable($l_siblibgs) ? count($l_siblibgs) : 0);
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        $language = isys_application::instance()->container->get('language');

        return [
            'isys_relation_type__title' => 'LC__CATG__RELATION__RELATION_TYPE',
            'type'                      => '',
            'master'                    => $language->get('LC_UNIVERSAL__OBJECT') . ' 1',
            'relation'                  => '',
            'slave'                     => $language->get('LC_UNIVERSAL__OBJECT') . ' 2',
            'isys_weighting__title'     => 'LC__CATG__RELATION__WEIGHTING',
            'itservice'                 => 'Service',
            'parallel'                  => 'LC__PARALLEL_RELATIONS__ALIGNED_TO'
        ];
    }
}
