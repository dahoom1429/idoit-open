<?php

namespace idoit\Component\Browser\Condition;

use idoit\Component\Browser\Condition;
use isys_application;
use isys_report_dao;

class ReportCondition extends Condition
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'LC__CMDB__OBJECT_BROWSER__BY_REPORT';
    }

    /**
     * @inheritdoc
     */
    public function retrieveOverview()
    {
        $overview = [];

        $dao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));

        $reports = $dao->get_reports(null, null, null, false, false);

        foreach ($reports as $report) {
            $overview[$report['category_title']][$report['isys_report__id']] = $report['isys_report__title'];
        }

        return $overview;
    }

    /**
     * @inheritdoc
     */
    public function retrieveObjects()
    {
        $return = [];

        // Prevent errors by exiting, if no numeric parameter has been set.
        if (!is_numeric($this->parameter)) {
            return $return;
        }

        $reportDao = isys_report_dao::instance(isys_application::instance()->container->get('database_system'));

        $reportData = $reportDao->get_report($this->parameter);

        if ($reportDao->validate_query($reportData['isys_report__query'])) {
            $reportResult = $this->dao->retrieve($reportData['isys_report__query']);
            $objectIds = [];

            while ($reportRow = $reportResult->get_row()) {
                if (isset($reportRow['__id__'])) {
                    $objectIds[] = $reportRow['__id__'];
                } elseif (isset($reportRow['isys_obj__id'])) {
                    $objectIds[] = $reportRow['isys_obj__id'];
                }
            }

            $objectIds = array_filter(array_unique($objectIds));

            if (count($objectIds)) {
                /** @noinspection SyntaxError */
                $sql = 'SELECT isys_obj__id AS id
                    FROM isys_obj 
                    INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                    WHERE isys_obj__id ' . $this->dao->prepare_in_condition($objectIds) . '
                    AND isys_obj__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) .
                    $this->getFilterQueryConditions() . ';';

                $result = $this->dao->retrieve($sql);

                while ($row = $result->get_row()) {
                    $return[] = $row['id'];
                }
            }
        }

        return $return;
    }
}
