<?php

/**
 * i-doit
 *
 * CMDB Global category voice over IP phones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_cmdb_ui_category_g_voip_phone extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category_g_voip_phone $p_cat
     *
     * @global  array                               $index_includes
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_rules = [];

        $this// This is a new method - It tries to fills the category forms automatically.
        ->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data())// Display the commentary.
        ->activate_commentary($p_cat)// Assign all the data to the template.
        ->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * UI constructor.
     *
     * @param   isys_component_template $p_template
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__voip_phone.tpl");
    }
}

?>