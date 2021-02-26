<?php
namespace idoit\Module\Report\SqlQuery\Condition;

/**
 * Interface ConditionProviderInterface
 *
 * @package idoit\Module\Report\SqlQuery\Condition
 */
interface ConditionProviderInterface
{
    /**
     * @return AbstractProvider
     */
    public static function factory();
}
