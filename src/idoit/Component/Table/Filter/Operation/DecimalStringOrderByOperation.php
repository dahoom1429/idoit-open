<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use isys_application;
use isys_cmdb_dao_category;
use isys_cmdb_dao_list_objects;

/**
 * Sets order by using finding the decimal part
 *
 * It sorts with next steps:
 * * gets the sorting_index - index of the first decimal symbol in the sorting field
 * * sort with prefix before this index
 * * within the same prefix sorts by casted number
 * * within the same prefix and number -> as usual
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class DecimalStringOrderByOperation extends Operation
{
    protected function getDaoForProperty($name)
    {
        try {
            list($class, $prop) = explode('__', $name);
            if (class_exists($class) && is_subclass_of($class, 'isys_cmdb_dao_category')) {
                $dao = new $class(isys_application::instance()->container->get('database'));
                if (!$dao instanceof isys_cmdb_dao_category) {
                    return false;
                }
                return $dao;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        if (strpos($name, 'obj_main') !== 0) {
            $listDao = $this->getDaoForProperty($name);

            if (!$listDao || $listDao->is_multivalued()) {
                return false;
            }
        }

        $name = $dao->get_database_component()->escapeColumnName($name);
        $dao->add_additional_selects('(
			SELECT
				min(IF(locate(chars.n, ' . $name . ') = 0, LENGTH(' . $name . '), locate(chars.n, ' . $name . '))) as position
			FROM (SELECT 0 as n UNION SELECT 1 as n UNION SELECT 2 as n UNION SELECT 3 as n UNION SELECT 4 as n UNION SELECT 5 as n UNION SELECT 6 as n UNION SELECT 7 as n UNION SELECT 8 as n UNION SELECT 9 as n) chars
       )', 'sorting_index');
        $orderBy = 'SUBSTRING(' . $name . ', 1, sorting_index - 1) ' . $value . ',
	        cast(substr(' . $name . ', sorting_index) as unsigned) ' . $value . ',
        ' . $name;
        $dao->set_order_by($orderBy, $value);

        return true;
    }
}
