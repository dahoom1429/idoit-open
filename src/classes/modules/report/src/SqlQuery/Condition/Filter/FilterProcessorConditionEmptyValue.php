<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

use idoit\Component\Property\Property;
use isys_tenantsettings;

/**
 * Class FilterProcessorConditionEmptyValue
 */
class FilterProcessorConditionEmptyValue extends AbstractFilterProcessorValue implements FilterProcessorValueInterface
{
    public function checkValue()
    {
        return (empty($this->getCheckValue()) || $this->getCheckValue() === isys_tenantsettings::get('gui.empty_value', '-'));
    }
}
