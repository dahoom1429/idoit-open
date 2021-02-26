<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Component\Provider\Factory;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Data
{
    use Factory;

    /**
     * Data result
     *
     * @var int
     */
    private $dataResult = [];

    /**
     * @return Data[]
     */
    public function getDataResult()
    {
        return $this->dataResult;
    }

    /**
     * @param Data[]
     *
     * @return $this
     */
    public function setDataResult(array $dataResult)
    {
        $this->dataResult = $dataResult;

        return $this;
    }
}