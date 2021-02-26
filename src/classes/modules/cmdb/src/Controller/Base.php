<?php

namespace idoit\Module\Cmdb\Controller;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Base
{
    /**
     * @var \isys_module_cmdb
     */
    protected $module = null;

    /**
     * @param \isys_module $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }
}