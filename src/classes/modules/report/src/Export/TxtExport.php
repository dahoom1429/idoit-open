<?php

namespace idoit\Module\Report\Export;

use idoit\Module\Report\Protocol\Exportable;
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
class TxtExport extends CsvExport implements Exportable
{
    /**
     * TxtExport constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        parent::__construct($report);
        $this->writer->setDelimiter("\t");
    }

    /**
     * Output to browser.
     *
     * @param string $filename
     *
     * @throws \Exception
     */
    public function output($filename = null)
    {
        $worker = $this->report->getWorker();

        if ($worker) {
            $this->writer->output('report-' . $this->report->getId() . '.txt');
        } else {
            throw new \Exception('Export was not processed correctly.');
        }
    }
}