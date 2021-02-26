<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\DynamicProperty\DefaultCondition;
use idoit\Module\Report\SqlQuery\Condition\DynamicProperty\DynamicYesNoProperty;

/**
 * Class DynamicPropertyProvider
 */
class DynamicPropertyProvider extends AbstractProvider implements ConditionProviderInterface
{
    /**
     * @return AbstractProvider
     */
    public static function factory()
    {
        return (new self())
            ->addConditionType(new DefaultCondition())
            ->addConditionType(new DynamicYesNoProperty());
    }
}
