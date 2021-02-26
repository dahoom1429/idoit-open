<?php

namespace idoit\Module\Report\Worker;

use idoit\Module\Report\Protocol\Worker;
use idoit\Component\Csv\Writer;

/**
 * Report CSV Export
 *
 * @package     idoit\Module\Report\Export
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class CsvWorker extends FileExport implements Worker
{

    /**
     * @var Writer
     */
    private $csvWriter;

    /**
     * @var integer
     */
    private $index = 0;

    /**
     * @param array $row
     *
     * @return mixed|void
     */
    public function work(array $row)
    {
        if ($this->index === 0) {
            $this->csvWriter->insertOne(array_keys($row));
            $this->index++;
        }

        $this->csvWriter->insertOne(array_values($row));
    }

    /**
     * Send Csv data to browser
     *
     * @param string $filename
     *
     * @return void
     */
    public function output($filename = null)
    {
        $this->csvWriter->output($filename);
    }

    /**
     * Return Csv Data
     *
     * @return string
     */
    public function export()
    {
        return $this->csvWriter->__toString();
    }

    /**
     * Csv constructor.
     *
     * @param Writer|null $csvWriter
     */
    public function __construct(Writer $csvWriter = null)
    {
        $this->csvWriter = $csvWriter
            ->addFormatter(function ($data) {
                foreach ($data as &$k) {
                    $k = strtr(html_entity_decode($k), ["\t" => '', "\r" => '']);
                }

                return $data;
            });
    }
}
