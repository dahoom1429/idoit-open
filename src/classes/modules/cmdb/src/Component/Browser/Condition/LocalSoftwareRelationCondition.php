<?php

namespace idoit\Module\Cmdb\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;

/**
 * Class LocalSoftwareRelationCondition
 *
 * @package idoit\Module\Cmdb\Component\Browser\Condition
 */
class LocalSoftwareRelationCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__CONDITION__LOCAL_SOFTWARE_RELATION';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $language = isys_application::instance()->container->get('language');

        if (!$this->displayObjectCount) {
            return [
                defined_or_default('C__RELATION_TYPE__SOFTWARE') => $language->get('LC__DATABASE_ASSIGNMENT_BROWSER__SOFTWARE_INSTANCES')
            ];
        }

        $sql = 'SELECT COUNT(1) as cnt
            FROM isys_catg_relation_list
            INNER JOIN isys_obj ON isys_catg_relation_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_relation_list__isys_relation_type__id = ' . $this->dao->convert_sql_id(defined_or_default('C__RELATION_TYPE__SOFTWARE')) . '
            AND (isys_catg_relation_list__isys_obj__id__master = ' . $this->dao->convert_sql_id($this->contextObjectId) . ' 
                OR isys_catg_relation_list__isys_obj__id__slave = ' . $this->dao->convert_sql_id($this->contextObjectId) . ') 
            ' . $this->getFilterQueryConditions() . ';';

        $count = $this->dao->retrieve($sql)->get_row_value('cnt');

        return [
            defined_or_default('C__RELATION_TYPE__SOFTWARE') => $language->get('LC__DATABASE_ASSIGNMENT_BROWSER__SOFTWARE_INSTANCES') . ' (' . $count . ')'
        ];
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];

        $sql = 'SELECT isys_obj__id as id
            FROM isys_catg_relation_list
            INNER JOIN isys_obj ON isys_catg_relation_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_relation_list__isys_relation_type__id = ' . $this->dao->convert_sql_id($this->parameter) . '
            AND (isys_catg_relation_list__isys_obj__id__master = ' . $this->dao->convert_sql_id($this->contextObjectId) . ' 
                OR isys_catg_relation_list__isys_obj__id__slave = ' . $this->dao->convert_sql_id($this->contextObjectId) . ') 
            ' . $this->getFilterQueryConditions() . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['id'];
        }

        return $return;
    }
}
