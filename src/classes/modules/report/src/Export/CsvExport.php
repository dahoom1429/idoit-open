<?php

namespace idoit\Module\Report\Export;

use idoit\Component\Csv\Writer;
use idoit\Module\Report\Protocol\Exportable;
use idoit\Module\Report\Report;
use idoit\Module\Report\Worker\CsvWorker;
use isys_tenantsettings;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

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
class CsvExport extends Export implements Exportable
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var \idoit\Module\Report\Report
     */
    protected $report;

    /**
     * @param string $filename
     *
     * @return $this
     * @throws \Exception
     */
    public function write($filename)
    {
        if (!file_exists(dirname($filename))) {
            throw new FileNotFoundException(sprintf('Error: Directory %s does not exist.', dirname($filename)));
        }

        if (!is_writable(dirname($filename))) {
            throw new \Exception(sprintf('Error: The directory you are trying to write the csv file into is not writeable (%s)', dirname($filename)));
        }

        $fileHandle = fopen($filename, 'w+');

        if (is_resource($fileHandle)) {
            fwrite($fileHandle, $this->writer);
            fclose($fileHandle);
        } else {
            throw new \Exception(sprintf('Error: The csv file could not be written. Is the directory (%s) writable?', dirname($filename)));
        }

        return $this;
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
            $this->writer->output('report-' . $this->report->getId() . '.csv');
        } else {
            throw new \Exception('Export was not processed correctly.');
        }
    }

    /**
     * CsvExport constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->writer = Writer::createFromFileObject(new \SplTempFileObject(0));
        $this->report = $report->setWorker(new CsvWorker($this->writer));

        parent::__construct();
    }
}
