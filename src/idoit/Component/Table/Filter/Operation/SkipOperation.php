<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use isys_cmdb_dao_list_objects;

/**
 * Empty skip operation
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class SkipOperation extends Operation
{
    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        return true;
    }
}
