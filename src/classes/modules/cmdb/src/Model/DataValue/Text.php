<?php

namespace idoit\Module\Cmdb\Model\DataValue;

use idoit\Module\Cmdb\Model\DataValue\Traits\StringValue;

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
class Text extends BaseValue implements DataValueInterface
{
    use StringValue;

    public function __construct($value)
    {
        // First decode vocals like ü, then strip all html from editors
        parent::__construct(filter_var(html_entity_decode($value), FILTER_SANITIZE_STRING));
    }
}
