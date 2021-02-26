<?php

namespace idoit\Module\Report\Worker;

use idoit\Module\Report\Protocol\Worker;

/**
 * Report Array Worker
 *
 * @package     idoit\Module\Report\Export
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class ArrayWorker implements Worker
{

    /**
     * @var array
     */
    private $array;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param array $row
     */
    public function work(array $row)
    {
        $this->array[] = $row;
    }

    /**
     * Return array data
     *
     * @return string
     */
    public function export()
    {
        return $this->array;
    }

    /**
     * Csv constructor.
     *
     * @param int $numberOfRows Specify the number of maximum rows that the report will have. This optimizes the array memory consumption.
     */
    public function __construct($numberOfRows = null)
    {
        if ($numberOfRows) {
            $this->array = new \SplFixedArray($numberOfRows);
        }
    }

}