<?php

/**
 * i-doit
 *
 * CMDB UI: global category for virtual categories
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_virtual extends isys_cmdb_ui_category_global
{
    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        if ($this->get_template()) {
            $this->deactivate_commentary()
                ->get_template_component()
                ->include_template('contentbottomcontent', $this->get_template());
        }
    }
}