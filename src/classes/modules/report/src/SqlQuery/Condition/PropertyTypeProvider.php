<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\PropertyType\DialogCaseData;
use idoit\Module\Report\SqlQuery\Condition\PropertyType\DialogList;
use idoit\Module\Report\SqlQuery\Condition\PropertyType\Multiselect;
use idoit\Module\Report\SqlQuery\Condition\PropertyType\ObjectBrowser;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PropertyTypeProvider extends AbstractProvider implements ConditionProviderInterface
{
    /**
     * @return AbstractProvider
     */
    public static function factory()
    {
        return (new self())
            ->addConditionType(new DialogCaseData())
            ->addConditionType(new DialogList())
            ->addConditionType(new Multiselect())
            ->addConditionType(new ObjectBrowser());
    }
}
