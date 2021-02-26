<?php

namespace idoit\Module\Cmdb\Model\DataValue;

use idoit\Module\Cmdb\Model\DataValue\Traits\IntValue;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class NumericValue extends BaseValue implements DataValueInterface
{
    use IntValue;
}