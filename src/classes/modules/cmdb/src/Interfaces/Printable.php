<?php

namespace idoit\Module\Cmdb\Interfaces;

/**
 * Printable inteface
 *
 * Please use this interface in cases when a category
 * needs to provide other data to print view as for export.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Printable
{
    /**
     * Get data for print view only
     *
     * @param int|string $categoryEntryId
     * @param int|string $objectId
     * @param string     $condition
     * @param string     $filter
     * @param int        $status
     *
     * @return \isys_component_dao_result_interface
     */
    public function getDataForPrintView($categoryEntryId, $objectId, $condition = "", $filter = null, $status = null);
}
