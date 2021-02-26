<?php

/**
 * i-doit
 *
 * Search Filters
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.5.4, 1.6
 */
abstract class isys_search_filter
{
    /**
     * Wildcard search (LIKE '%xyz%')
     */
    const WILDCARD = 1;

    /**
     * Starting with (LIKE 'xyz%')
     */
    const STARTING_WITH = 2;

    /**
     * Ending with (LIKE '%xyz')
     */
    const ENDING_WITH = 3;

    /**
     * Exact match (= 'xyz')
     */
    const EXACT = 4;

    /**
     * Greater than (> 123)
     */
    const GT = 5;

    /**
     * Greater than or equal(>= 123)
     */
    const GTE = 6;

    /**
     * Lower than (< 123)
     */
    const LT = 7;

    /**
     * Lower than or equal (<= 123)
     */
    const LTE = 7;

    /**
     * Between range (BETWEEN 1 AND 2)
     */
    const BETWEEN = 8;

    /**
     * Date filter (= DATE_FORMAT(Y-m-d))
     */
    const DATE = 9;

    /**
     * The filter itself
     *
     * @var mixed
     */
    protected $m_filter = null;

    /**
     * SQL Wildcard
     *
     * @var string
     */
    protected $m_wildcard = '%';

    /**
     * @param string|array $p_filterstring
     */
    protected function prepare($p_filterstring, $p_wildcard = self::WILDCARD)
    {
        switch ($p_wildcard) {
            case self::WILDCARD;
                return 'LIKE \'' . $this->escape($this->m_wildcard . $p_filterstring . $this->m_wildcard) . '\'';
                break;
            case self::STARTING_WITH;
                return 'LIKE \'' . $this->escape($p_filterstring . $this->m_wildcard) . '\'';
                break;
            case self::ENDING_WITH:
                return 'LIKE \'' . $this->escape($this->m_wildcard . $p_filterstring) . '\'';
                break;
            case self::GT:
                return '> ' . (int)$p_filterstring;
                break;
            case self::GTE:
                return '>= ' . (int)$p_filterstring;
                break;
            case self::LT:
                return '< ' . (int)$p_filterstring;
                break;
            case self::LTE:
                return '<= ' . (int)$p_filterstring;
                break;
            case self::BETWEEN:
                if (is_array($p_filterstring)) {
                    return 'BETWEEN ' . (int)$p_filterstring[0] . ' AND ' . (int)$p_filterstring[1];
                }
                break;
            case self::DATE;
                return '= \'' . date('Y-m-d', strtotime($p_filterstring)) . '\'';
                break;
        }

        return '';
    }

    /**
     * Escape filter sequence
     *
     * @param string $p_sequence
     *
     * @return string
     */
    private function escape($p_sequence)
    {
        return isys_application::instance()->database->escape_string($p_sequence);
    }
}