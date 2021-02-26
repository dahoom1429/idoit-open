<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

use idoit\Component\Property\Property;

/**
 * Class FilterProcessorConditionValue
 */
class FilterProcessorConditionValue extends AbstractFilterProcessorValue implements FilterProcessorValueInterface
{
    public function checkValue()
    {
        return stripos(strip_tags($this->getCheckValue()), strip_tags($this->getValue())) !== false;
    }
}
