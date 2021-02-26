<?php

namespace idoit\Module\Report\SqlQuery\Condition\Comparison;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class UnderLocationCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return ($this->getConditionComparison() === 'under_location');
    }

    /**
     * @return string
     */
    public function format()
    {
        $conditionData = $this->getConditionData();
        $conditionValue = $this->getConditionValue();

        $db = \isys_application::instance()->container->get('database');

        return ' (' . $conditionData['location_lft'] . ' > ' .
            '(SELECT isys_catg_location_list__lft FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = \'' .
            $db->escape_string($conditionValue) . '\') AND ' . $conditionData['location_rgt'] . ' < ' .
            '(SELECT isys_catg_location_list__rgt FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = \'' .
            $db->escape_string($conditionValue) . '\')) ';
    }
}
