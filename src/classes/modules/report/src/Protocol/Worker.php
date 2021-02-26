<?php

namespace idoit\Module\Report\Protocol;

/**
 * i-doit
 *
 *
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Worker
{
    /**
     * Worker function
     *
     * @param array $row
     *
     * @return mixed
     */
    public function work(array $row);
}