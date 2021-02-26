<?php

/**
 * i-doit
 *
 * UI: global category for jdisc custom attribute
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_jdisc_custom_attributes extends isys_cmdb_ui_category_global
{
    /**
     * Sets the template file (*.tpl).
     *
     * @param   string $p_template
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_template($p_template)
    {
        global $g_dirs;
        $this->m_template_file = $g_dirs["class"] . "/modules/jdisc/templates/content/bottom/content/" . $p_template;

        return $this;
    }

    public function __construct(isys_component_template &$p_template)
    {
        global $g_dirs;
        parent::__construct($p_template);
        $this->set_template($g_dirs["class"] . "/modules/jdisc/templates/content/bottom/content/catg__jdisc_custom_attributes.tpl");
    }

}

?>