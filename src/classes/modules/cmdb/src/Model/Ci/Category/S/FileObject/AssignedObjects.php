<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\S\FileObject;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;

/**
 * Class AssignedObjects
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @since       1.14.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class AssignedObjects implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param null   $extra
     *
     * @return mixed|string
     * @throws \Exception
     */
    public static function render($data, $extra = null)
    {
        if (empty($data)) {
            return '';
        }

        $isHtml = strpos($data, '<ul><li>') === 0;
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $connectionIds = array_filter(explode(' ', preg_replace('~[^\d]+~', ' ', $data)));

        $returnValues = [];
        $sql = 'SELECT
            (CASE
                WHEN isys_catg_file_list__id IS NOT NULL THEN CONCAT(file_assignment.isys_obj__title, \' {\', file_assignment.isys_obj__id, \'}\')
                WHEN isys_catg_manual_list__id IS NOT NULL THEN CONCAT(manual_assignment.isys_obj__title, \' {\', manual_assignment.isys_obj__id, \'}\')
                WHEN isys_catg_emergency_plan_list__id IS NOT NULL THEN CONCAT(ep_assignment.isys_obj__title, \' {\', ep_assignment.isys_obj__id, \'}\')
            END) AS object
            FROM isys_connection
            LEFT JOIN isys_catg_file_list ON isys_catg_file_list__isys_connection__id = isys_connection__id
            LEFT JOIN isys_obj AS file_assignment ON file_assignment.isys_obj__id = isys_catg_file_list__isys_obj__id
            LEFT JOIN isys_catg_manual_list ON isys_catg_manual_list__isys_connection__id = isys_connection__id
            LEFT JOIN isys_obj AS manual_assignment ON manual_assignment.isys_obj__id = isys_catg_manual_list__isys_obj__id
            LEFT JOIN isys_catg_emergency_plan_list ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id
            LEFT JOIN isys_obj AS ep_assignment ON ep_assignment.isys_obj__id = isys_catg_emergency_plan_list__isys_obj__id
            WHERE isys_connection__id ' . $dao->prepare_in_condition($connectionIds) . '
            AND (file_assignment.isys_obj__status = ' . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                OR manual_assignment.isys_obj__status = ' . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                OR ep_assignment.isys_obj__status = ' . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ')';

        $result = $dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $returnValues[] = $row['object'];
        }

        return $isHtml ? ('<ul><li>' . implode('</li><li>', array_filter($returnValues)) . '</li></ul>') : implode(', ', $returnValues);
    }
}
