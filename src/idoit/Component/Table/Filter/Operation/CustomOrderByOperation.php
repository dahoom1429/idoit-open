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
 * Checks, if there are special sort field in the property definition
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class CustomOrderByOperation extends PropertyOperation
{
    public function isApplicable($filter, $value)
    {
        $property = $this->getProperty($filter);

        return $property && isset($property[C__PROPERTY__DATA], $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SORT]);
    }

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
        $sort = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SORT];
        $field = $name . '_sort';
        $listDao->add_additional_selects($sort, $field);
        $listDao->set_order_by($field, $value);

        return true;
    }
}