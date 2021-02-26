<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_emergency_plan_assigned_obj extends isys_cmdb_ui_category_s_emergency_plan
{
    /**
     * Returns the title of the specific category
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return string
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        return isys_application::instance()->container->get('language')
            ->get("LC__CMDB__CATS__EMERGENCY_PLAN_LINKED_OBJECT_LIST");
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return null
     * @throws Exception
     * @throws isys_exception_general
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        global $index_includes;

        $this->object_browser_as_new([
            'name'                                          => 'C__CMDB__CATS__EMERGENCY_PLAN_ASSIGNED_OBJ__OBJECT',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__EMERGENCY_PLAN',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_s_emergency_plan_assigned_obj', 'get_assigned_objects'],
                $_GET[C__CMDB__GET__OBJECT],
                ['isys_catg_emergency_plan_list__isys_obj__id']
            ],
        ], 'LC__UNIVERSAL__OBJECT_ADD_REMOVE', 'LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION');

        $l_listdao = new isys_cmdb_dao_list_cats_emergency_plan_assigned_obj($this->get_database_component());

        $l_listres = $l_listdao->get_result(null, $_GET[C__CMDB__GET__OBJECT]);

        $l_arTableHeader = $l_listdao->get_fields();

        $l_objList = new isys_component_list(null, $l_listres, $l_listdao, $l_listdao->get_rec_status());

        $link = isys_helper_link::create_url([
            C__CMDB__GET__CATG   => defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__OBJECT =>'[{isys_obj__id}]'
        ]);

        $l_objList->config($l_arTableHeader, $link, "[{isys_catg_emergency_plan_list__id}]", true);

        //5. step: createTempTable() (optional)
        $l_objList->createTempTable();

        //6. step: getTempTableHtml()
        $l_strTempHtml = $l_objList->getTempTableHtml();

        //7. step: assign html to smarty
        $this->get_template_component()
            ->assign("objectTableList", $l_strTempHtml)
            ->assign('list_display', true)
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bDisabled=0')
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_strSelectedID=' . $_SESSION['cRecStatusListView'])
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_arData=' . serialize($l_listdao->get_rec_array()))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1");
        $index_includes['contentbottomcontent'] = $this->get_template();
    }

    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("object_table_list.tpl");
    }
}
