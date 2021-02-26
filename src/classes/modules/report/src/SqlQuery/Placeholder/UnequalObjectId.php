<?php

namespace idoit\Module\Report\SqlQuery\Placeholder;

/**
 * Not Object ID Placeholder
 *
 * @package     idoit\Module\Report\Placeholder
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.11
 */
class UnequalObjectId extends ObjectId implements Placeholder
{
    /**
     * Retrieve identifier of placeholder
     */
    public static function getIdentifier()
    {
        return 'unequal-object-id';
    }

    /**
     * Replaces placeholder with "!= object-id"
     *
     * @param string $placeholder
     * @param string $userInput
     *
     * @return string
     */
    public function replacePlaceholder($placeholder, $userInput = '')
    {
        return '!=' . ((int)$_GET[C__CMDB__GET__OBJECT]);
    }
}
