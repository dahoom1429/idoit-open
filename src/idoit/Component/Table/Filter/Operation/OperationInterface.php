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

/**
 * Defines the Sql operation for DAO-list object.
 * Provides the functionality to apply the filter with its value to the dao object
 */
use isys_cmdb_dao_list_objects;

interface OperationInterface
{
    /**
     * Apply the filter with this operation to the dao object
     *
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    public function apply(isys_cmdb_dao_list_objects $dao, $name, $value);
}
