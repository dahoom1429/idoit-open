<?php

/**
 * i-doit
 *
 * builds html-table for the logbook lists
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_list_logbook_archive extends isys_component_list_logbook
{
    /**
     * Table name
     *
     * @var string
     */
    protected $m_strTempTableName = "isys_archive_logbook";
}
