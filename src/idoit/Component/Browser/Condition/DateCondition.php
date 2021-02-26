<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;

class DateCondition extends Condition
{
    const LATEST_CREATED = 'latest-created';
    const LATEST_UPDATED = 'latest-updated';
    const THIS_MONTH     = 'this-month';
    const LAST_MONTH     = 'last-month';
    const OBJECT_LIMIT   = 5000;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_DATE';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        return [
            self::LATEST_CREATED => 'LC__CMDB__OBJECT_BROWSER__NEWLY_CREATED',
            self::LATEST_UPDATED => 'LC__CMDB__OBJECT_BROWSER__NEWLY_UPDATED',
            self::THIS_MONTH     => 'LC__CMDB__OBJECT_BROWSER__CREATED_THIS_MONTH',
            self::LAST_MONTH     => 'LC__CMDB__OBJECT_BROWSER__CREATED_LAST_MONTH'
        ];
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];

        // Prevent errors by exiting, if a numeric parameter has been set (like "-1").
        if (is_numeric($this->parameter)) {
            return $return;
        }

        $condition = '';
        $orderBy = 'ORDER BY isys_obj__created DESC';

        switch ($this->parameter) {
            case self::LATEST_CREATED:
                $orderBy = 'ORDER BY isys_obj__created DESC';
                break;
            case self::LATEST_UPDATED:
                $orderBy = 'ORDER BY isys_obj__updated DESC';
                break;
            case self::THIS_MONTH:
                $condition = 'AND MONTH(isys_obj__created) = MONTH(NOW()) ';
                break;
            case self::LAST_MONTH:
                $condition = 'AND MONTH(isys_obj__created) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) ';
                break;
        }

        $sql = 'SELECT isys_obj.isys_obj__id AS id
            FROM isys_obj 
            LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            ' . $condition . '
            ' . $this->getFilterQueryConditions() . '
            ' . $orderBy . ' 
            LIMIT ' . self::OBJECT_LIMIT . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['id'];
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function retainObjectOrder()
    {
        return true;
    }
}
