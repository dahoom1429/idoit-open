<?php

namespace idoit\Module\Report\SqlQuery\Condition;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable();

    /**
     * @return string
     */
    public function format();
}
