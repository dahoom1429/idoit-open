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

use isys_cmdb_dao_list_objects;

/**
 * Sets additional having conditions with like
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class HavingLikeOperation extends Operation
{
    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        $name = $dao->get_database_component()->escapeColumnName($name);
        $dao->add_additional_having_conditions($name . ' LIKE ' . $value);

        return true;
    }
}
