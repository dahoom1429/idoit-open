<?php

namespace idoit\Module\Report\SqlQuery\Placeholder;

use isys_application;

/**
 * Placeholder for the current user id
 *
 * @package     idoit\Module\Report\Placeholder
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.14.2
 */
class CurrentUserId implements Placeholder
{
    /**
     * Retrieve identifier of placeholder
     */
    public static function getIdentifier()
    {
        return 'current-user-id';
    }

    /**
     * Replace placeholder by given class, e.g. CurrentDatetime would be NOW()
     *
     * @param string $placeholder
     * @param string $userInput
     *
     * @return string
     */
    public function replacePlaceholder($placeholder, $userInput = '')
    {
        $userId = (int)isys_application::instance()->container->get('session')->get_user_id();
        return '=' . ($userId > 0 ? $userId : 'FALSE');
    }

    /**
     * Determines if a placeholder needs external data or can be handled internally
     *
     * @return bool
     */
    public function isInternal()
    {
        return true;
    }

    /**
     * HTML for user input field which will be used as a condition inside the report manager
     *
     * @return array
     */
    public function getFieldsForUserInput()
    {
        $field = new \isys_smarty_plugin_f_text();

        return [
            'firstLevel' => $field->navigation_edit(isys_application::instance()->template, [
                'name'              => 'querycondition[#{queryConditionBlock}][#{queryConditionLvl}][user_input]',
                'id'                => 'querycondition_#{queryConditionBlock}_#{queryConditionLvl}_user_input',
                'p_strClass'        => 'reportInput',
                'p_strStyle'        => 'margin-left: 10px; width: 120px;',
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strPlaceholder'  => 'LC__REPORT__PLACEHOLDER__CURRENT_USER'
            ]),
            'subLevel'   => $field->navigation_edit(isys_application::instance()->template, [
                'name'              => 'querycondition[#{queryConditionBlock}][#{queryConditionLvl}][subcnd][#{queryConditionSubLvlProp}][#{queryConditionSubLvl}][user_input]',
                'id'                => 'querycondition_#{queryConditionBlock}_#{queryConditionLvl}_#{queryConditionSubLvl}_#{queryConditionSubLvlProp}_user_input',
                'p_strClass'        => 'reportInput',
                'p_strStyle'        => 'margin-left: 10px; width: 120px;',
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strPlaceholder'  => 'LC__REPORT__PLACEHOLDER__CURRENT_USER'
            ])
        ];
    }
}
