<?php

namespace idoit\Module\Report\SqlQuery\Condition\DynamicProperty;

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\DynamicYesNoProperty as DynamicYesNoPropertyType;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use isys_cmdb_dao_category;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DynamicYesNoProperty extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return $this->getProperty() instanceof DynamicYesNoPropertyType;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        return 'TRUE';
    }
}
