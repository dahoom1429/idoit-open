<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;

class RelationTypeCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_RELATIONS';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $language = isys_application::instance()->container->get('language');
        $overview = [];

        $normalStatus = $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
        $countSql = "''";

        if ($this->displayObjectCount) {
            $countSql = 'COUNT(1)';
        }

        $sql = "SELECT isys_relation_type__id AS id, isys_relation_type__title AS title, {$countSql} as count 
            FROM isys_relation_type
			INNER JOIN isys_catg_relation_list ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj__status = {$normalStatus}
            AND isys_relation_type__status = {$normalStatus} " .
            $this->getFilterQueryConditions() . '
            GROUP BY isys_relation_type__id;';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $overview[$row['id']] = $language->get($row['title']);

            if ($this->displayObjectCount) {
                $overview[$row['id']] .= ' (' . $row['count'] . ')';
            }
        }

        natcasesort($overview);

        return $overview;
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];

        $sql = 'SELECT isys_obj__id AS id
            FROM isys_obj 
            INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_relation_list__isys_relation_type__id = ' . $this->dao->convert_sql_id($this->parameter) . '
            AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
            $this->getFilterQueryConditions() . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['id'];
        }

        return $return;
    }
}
