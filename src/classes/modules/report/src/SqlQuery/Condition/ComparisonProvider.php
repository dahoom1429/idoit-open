<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\Comparison\GreaterThanCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\LessThanCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\LikeCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotLikeCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotNullCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NullCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\PlaceholderCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\UnderLocationCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\InCondition;
use idoit\Module\Report\SqlQuery\Condition\Comparison\NotInCondition;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ComparisonProvider extends AbstractProvider implements ConditionProviderInterface
{
    /**
     * @return AbstractProvider
     */
    public static function factory()
    {
        return (new self())
            ->addConditionType(new UnderLocationCondition())
            ->addConditionType(new InCondition())
            ->addConditionType(new NotInCondition())
            ->addConditionType(new NotNullCondition())
            ->addConditionType(new NullCondition())
            ->addConditionType(new LikeCondition())
            ->addConditionType(new NotLikeCondition())
            ->addConditionType(new PlaceholderCondition())
            ->addConditionType(new GreaterThanCondition())
            ->addConditionType(new LessThanCondition());
    }
}
