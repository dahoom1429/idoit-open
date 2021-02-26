<?php

namespace idoit\Module\Report\Export;

use idoit\Module\Report\Report;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Export
{

    /**
     * @var Report
     */
    protected $report;

    /**
     * @param Report $report
     *
     * @return static
     */
    public static function factory(Report $report)
    {
        return new static($report);
    }

    /**
     * Return as string
     *
     * @return $this
     */
    public function export()
    {
        $this->report->execute();

        return $this;
    }

    /**
     * Export constructor.
     */
    public function __construct()
    {
        // Disable html context for this report since we don't need any html output in a csv file.
        $this->report->enableHtmlContext(false);

        // Quick-Fix: Always format location paths as text for csv exports, because html will be stripped anyway
        \isys_popup_browser_location::instance()
            ->set_format_as_text(true);
    }
}