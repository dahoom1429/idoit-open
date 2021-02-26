<?php

/**
 * i-doit
 *
 * CMDB Specific category EPS
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dsteucken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_parallel_relation extends isys_cmdb_ui_category_specific
{
    /**
     * Method for retrieving the assigned relations.
     *
     * @param   isys_cmdb_dao_category_s_parallel_relation $p_cat
     *
     * @return  string
     */
    public static function get_relation_pool(isys_cmdb_dao_category_s_parallel_relation $p_cat)
    {
        global $g_dirs;

        $html = [];

        $l_pool = $p_cat->get_relation_pool($_GET[C__CMDB__GET__OBJECT]);
        $remove = isys_application::instance()->container->get('language')->get("LC__UNIVERSAL__REMOVE");

        while ($l_row = $l_pool->get_row()) {
            $html[] = '<li class="box-white">' .
                '<button type="button" class="btn btn-small fr" onclick="remove_from_pool(this, \'' . ((int) $l_row["isys_obj__id"]) . '\');">' .
                    '<img src="' . $g_dirs["images"] . 'icons/silk/cross.png" class="mr5" /><span>' . $remove . '</span>' .
                '</button>' .
                '<span>' . $l_row["isys_obj__title"] . '</span>'.
                '</li>';
        }

        return implode('', $html);
    }

    /**
     * @global  array                                      $index_includes
     *
     * @param   isys_cmdb_dao_category_s_parallel_relation $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        // Get result set
        if (is_object(($l_result = $p_cat->get_result()))) {
            $l_catdata = $l_result->__to_array();
        }

        // Assign rules.
        $l_rules["C__CMDB__CATS__RELPL__TITLE"]["p_strValue"] = $l_catdata["isys_cats_relpool_list__title"];
        $l_rules["C__CMDB__CATS__RELPL__THRESHOLD"]["p_strValue"] = $l_catdata["isys_cats_relpool_list__threshold"];
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_cats_relpool_list__description"];
        $l_rules['C__CMDB__CATS__RELPL__RELATION_POOL'][isys_popup_browser_object_relation::C__SELECTION] = $p_cat->getRelations($l_catdata['isys_cats_relpool_list__isys_obj__id']);
        $l_rules['C__CMDB__CATS__RELPL__RELATION_POOL']['p_strValue'] = $p_cat->getRelations($l_catdata['isys_cats_relpool_list__isys_obj__id']);
        $l_rules['C__CMDB__CATS__RELPL__RELATION_POOL'][isys_popup_browser_object_relation::C__CALLBACK__DETACH] = ';detachRelationPool();';

        $this->get_template_component()
            ->assign("link_pool", self::get_relation_pool($p_cat))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * Constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("cats__parallel_relation.tpl");
    }
}

?>
