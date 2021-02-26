<?php

namespace idoit\Module\Report\SqlQuery\Placeholder;

/**
 * Placeholder Interface
 *
 * @package     idoit\Module\Report\Placeholder
 * @subpackage  Core
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.11
 */
interface Placeholder
{
    /**
     * Retrieve identifier of placeholder
     */
    public static function getIdentifier();

    /**
     * Replace placeholder by given class, e.g. CurrentDatetime would be NOW()
     *
     * @param string $placeholder
     * @param string $userInput
     *
     * @return string
     */
    public function replacePlaceholder($placeholder, $userInput = '');

    /**
     * Determines if a placeholder needs external data or can be handled internally
     *
     * @return bool
     */
    public function isInternal();

    /**
     * HTML for user input field which will be used as a condition inside the report manager
     *
     * @return array
     */
    public function getFieldsForUserInput();
}
