<?php

namespace idoit\Module\Report\Validate;

use idoit\Module\Report\Protocol\Validation;
use isys_application;

/**
 * Report
 *
 * @package     idoit\Module\Report
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class Query implements Validation
{
    /**
     * Method for validating that there are no updates, drops, truncates, ... inside the query.
     *
     * @param   string $p_query
     *
     * @return  boolean
     * @throws  \Exception
     */
    public static function validate($p_query)
    {
        if (empty($p_query)) {
            return true;
        }

        // 1. (^(\s+(\-\-))|^(\-\-)).* Remove comments which is in this format -- test test at start of each line
        // 2. (?:\'.*\-\-.*\') Replace string which is in this format '-- test -- test' to '' which will not be checked within the next expressions
        // 3. (?:[\"].*\-\-.*\") Replace string which is in this format "-- test -- test" to "" which will not be checked within the next expressions
        // 4. (\-\-)\s\w+.*$ Remove comments starting from the end of the line
        $patterns = [
            '/(^(\s+(\-\-))|^(\-\-)).*/im' => '',
            "/(?:[\'].*\-\-.*\')/im" => '\'\'',
            '/(?:[\"].*\-\-.*\")/im' => '""',
            '/(\-\-)\s\w+.*$/im' => ''
        ];

        // Remove all Comments in the query
        $p_query = preg_replace(array_keys($patterns), array_values($patterns), $p_query);

        if (substr(trim($p_query), -1) !== ';') {
            $p_query .= ';';
        }

        if (strpos($p_query, '%3B') !== false) {
            throw new \Exception(isys_application::instance()->container->get('language')->get('LC__REPORT__POPUP__REPORT_PREVIEW__POSSIBLE_SQL_INJECTION'));
        }

        // "\b" is used for "whole word only" - So that "isys_obj__updated" will not match.
        if (preg_match_all("/.*?(\bDROP\b|\bGRANT\b|\bINSERT\b|\bREPLACE\b|\bUPDATE\b|\bTRUNCATE\b|\bDELETE\b|\bALTER\b)[\s]*[a-zA-Z-_`]+? .*?/is", $p_query, $l_register)) {
            throw new \Exception(isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__POPUP__REPORT_PREVIEW__ERROR_MANIPULATION') . " " . isys_application::instance()->container->get('language')
                    ->get('LC__SETTINGS__CMDB__VALIDATION__REGEX_CHECK_SUCCESS') . ": '" . $l_register[1][0] . "'.");
        }

        return true;
    }
}
