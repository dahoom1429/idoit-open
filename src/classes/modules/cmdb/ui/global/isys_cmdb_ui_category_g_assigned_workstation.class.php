<?php

/**
 * i-doit
 *
 * UI: logical units
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_assigned_workstation extends isys_cmdb_ui_category_g_logical_unit
{
    /**
     * isys_cmdb_ui_category_g_assigned_workstation constructor.
     *
     * @param isys_component_template $p_template
     *
     * @throws isys_exception_ui
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);

        $this->set_template('catg__assigned_workstation.tpl');
    }
}
