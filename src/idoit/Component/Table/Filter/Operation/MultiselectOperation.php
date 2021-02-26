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

use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_cmdb_dao_list_objects;

/**
 * Empty skip operation
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class MultiselectOperation extends PropertyOperation
{
    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     *
     * @return mixed
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value)
    {
        /**
         * @var $select SelectSubSelect
         */
        $select = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $idField = $select->getSelectFieldObjectID() ?: $select->getSelectReferenceKey();
        $selection = $select->getSelection();
        $new_selection = $idField . ' as id';
        $select->setSelectQuery(str_replace($selection, $new_selection, $select->getSelectQuery()));
        $select->getSelectCondition()
            ->addCondition(' AND ' . $selection . ' like ' . $value);

        $listDao->add_additional_conditions(' AND obj_main.isys_obj__id IN ' . $select);

        return true;
    }
}
