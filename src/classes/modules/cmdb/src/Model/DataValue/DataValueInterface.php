<?php

namespace idoit\Module\Cmdb\Model\DataValue;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface DataValueInterface
{
    /**
     * Return the value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set the value
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value);
}