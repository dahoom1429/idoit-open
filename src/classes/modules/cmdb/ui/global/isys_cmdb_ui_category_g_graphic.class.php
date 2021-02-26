<?php

/**
 * i-doit
 * CMDB Graphicscard category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_graphic extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_graphic $p_cat
     *
     * @return  array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        // Rewrite the memory data.
        $l_catdata["isys_catg_graphic_list__memory"] = isys_convert::memory($l_catdata["isys_catg_graphic_list__memory"],
            $l_catdata["isys_catg_graphic_list__isys_memory_unit__id"], C__CONVERT_DIRECTION__BACKWARD);

        $l_catdata["isys_catg_graphic_list__memory"] = isys_convert::formatNumber($l_catdata["isys_catg_graphic_list__memory"]);

        $this->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}