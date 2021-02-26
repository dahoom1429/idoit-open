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
interface isys_search_filter_interface
{

    /**
     * Return filter using wildcard
     *
     * @return string
     */
    public function get($p_wildcard = isys_search_filter::WILDCARD);

    /**
     * @param string $p_filter
     *
     * @return isys_search_filter_interface
     */
    public function set($p_filter);

}