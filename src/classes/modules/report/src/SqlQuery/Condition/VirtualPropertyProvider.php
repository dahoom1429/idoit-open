<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;

/**
 * Class VirtualPropertyProvider
 */
class VirtualPropertyProvider extends AbstractProvider implements ConditionProviderInterface
{
    public static function factory()
    {
        return (new self());
    }
}
