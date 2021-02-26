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
class isys_search_filter_string extends isys_search_filter implements isys_search_filter_interface
{

    /**
     * Get filter string
     *
     * @param int $p_wildcard
     *
     * @return string
     */
    public function get($p_wildcard = isys_search_filter::WILDCARD)
    {
        return $this->prepare($this->m_filter, $p_wildcard);
    }

    /**
     * Set filter string
     *
     * @param string $p_filter
     *
     * @return isys_search_filter_interface
     */
    public function set($p_filter)
    {
        $this->m_filter = strval($p_filter);

        return $this;
    }

    /**
     * @param string $p_filter
     */
    public function __construct($p_filter)
    {
        $this->set($p_filter);
    }
}