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

class AliasOrderByOperation extends PropertyOperation
{
    public function isApplicable($filter, $value)
    {
        $property = $this->getProperty($filter);

        return $property && isset($property[C__PROPERTY__DATA], $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SORT_ALIAS]);
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
        $sort = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SORT_ALIAS];
        $listDao->set_order_by($sort, $value);

        return true;
    }
}