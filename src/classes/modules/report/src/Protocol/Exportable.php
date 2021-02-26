<?php

namespace idoit\Module\Report\Protocol;

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
interface Exportable
{
    /**
     * Output to browser
     *
     * @param string $filename
     *
     * @return void
     */
    public function output($filename = null);

    /**
     * @param string $filename
     *
     * @return $this
     */
    public function write($filename);

    /**
     * Return as string
     *
     * @return $this
     */
    public function export();
}