<?php
namespace idoit\Module\Report\SqlQuery\Condition\VirtualProperty;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

class DefaultCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        return '';
    }
}
