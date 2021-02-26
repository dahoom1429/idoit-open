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
 * Defines the structure of sql operations
 * Operations have applicability - the set of conditions to fulfill to apply it.
 * Operation can modify the DAO object to apply the filter criterias
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class OperationProvider
{
    /**
     * Set of operations
     *
     * @var array
     */
    protected $operations = [];

    /**
     * @var isys_cmdb_dao_list_objects
     */
    protected $dao;

    /**
     * OperationProvider constructor.
     *
     * @param isys_cmdb_dao_list_objects $dao
     */
    public function __construct(isys_cmdb_dao_list_objects $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param OperationInterface $operation
     *
     * @return $this
     */
    public function addOperation(OperationInterface $operation)
    {
        $this->operations[] = $operation;

        return $this;
    }

    /**
     * @return array
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Checks through the operations of OperationProvider the applicability and applies the operations to the DAO
     *
     * @param array $filters
     */
    public function apply(array $filters)
    {
        foreach ($filters as $filter => $filterValue) {
            foreach ($this->operations as $operation) {
                if ($operation instanceof OperationInterface && $operation->apply($this->dao, $filter, $filterValue)) {
                    break;
                }
            }
        }
    }
}
