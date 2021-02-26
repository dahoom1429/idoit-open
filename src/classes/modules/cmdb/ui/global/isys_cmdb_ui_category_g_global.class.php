<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * CMDB Global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Wösten <awoesten@i-doit.de>
 * @version     Dennis Blümer <dbluemer@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * Notice: This category is special.
 * After creating an object the object gets the status NORMAL only if the data for catg global is saved.
 * Otherwise the object gets BIRTH status.
 */
class isys_cmdb_ui_category_g_global extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return void
     * @throws Exception
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        /** @var isys_cmdb_dao_category_g_global $p_cat */

        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_posts = isys_module_request::get_instance()->get_posts();

        $l_tag_selection = [];
        $locales = isys_application::instance()->container->get('locales');
        $language = isys_application::instance()->container->get('language');
        $template = $this->get_template_component();

        $objectId = $l_gets[C__CMDB__GET__OBJECT] ?? $_GET[C__CMDB__GET__OBJECT];

        // Fetch data.
        $l_catdata = $p_cat->get_general_data();

        if ($l_catdata === null) {
            $p_cat->create($objectId);

            $l_catdata = $p_cat->get_data(null, $objectId)->get_row();
        }

        // Because "Birth" is no option, the user might get confused by an awkward status.
        if ($l_catdata['isys_obj__status'] == C__RECORD_STATUS__BIRTH) {
            $l_catdata['isys_obj__status'] = C__RECORD_STATUS__NORMAL;
        }

        if (isset($_POST['template']) && $_POST['template'] != '') {
            $templateType = (int)$_POST['template'];

            if ($templateType === 1) {
                $l_catdata['isys_obj__status'] = C__RECORD_STATUS__TEMPLATE;
            } elseif ($templateType === C__RECORD_STATUS__MASS_CHANGES_TEMPLATE) {
                $l_catdata['isys_obj__status'] = C__RECORD_STATUS__MASS_CHANGES_TEMPLATE;
            } else {
                $l_catdata['isys_obj__status'] = C__RECORD_STATUS__NORMAL;
            }
        }

        // Prepare tags.
        if ($l_catdata['isys_obj__id']) {
            $l_tag_selection = $p_cat->get_assigned_tag($l_catdata['isys_obj__id'], true);
        }

        // We create the commentary key here, to keep the lines nice and short.
        $commentaryKey = 'C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id();

        $l_rules = [
            'C__CATG__GLOBAL_CREATED'  => [
                'p_strValue' => $locales->fmt_datetime($l_catdata['isys_obj__created'], true, false) . ' (' . $l_catdata['isys_obj__created_by'] . ')'
            ],
            'C__CATG__GLOBAL_UPDATED'  => [
                'p_strValue' => $locales->fmt_datetime($l_catdata['isys_obj__updated'], true, false) . ' (' . $l_catdata['isys_obj__updated_by'] . ')'
            ],
            'C__OBJ__ID'               => ['p_strValue' => $l_catdata['isys_obj__id']],
            'C__OBJ__TYPE'             => [
                'p_strSelectedID' => $l_catdata['isys_obj_type__id'],
                'p_arData'        => []
            ],
            'C__OBJ__STATUS'           => [
                'p_strValue'      => $p_cat->get_record_status_as_string($l_catdata['isys_obj__status']),
                'p_arData'        => [
                    C__RECORD_STATUS__NORMAL                => 'LC__CMDB__RECORD_STATUS__NORMAL',
                    C__RECORD_STATUS__TEMPLATE              => 'LC__CMDB__RECORD_STATUS__TEMPLATE',
                    C__RECORD_STATUS__ARCHIVED              => 'LC__CMDB__RECORD_STATUS__ARCHIVED',
                    C__RECORD_STATUS__DELETED               => 'LC__CMDB__RECORD_STATUS__DELETED',
                    C__RECORD_STATUS__MASS_CHANGES_TEMPLATE => 'LC__MASS_CHANGE__CHANGE_TEMPLATE'
                ],
                'p_arDisabled'    => [
                    C__RECORD_STATUS__NORMAL  => $l_catdata['isys_obj__status'] != C__RECORD_STATUS__NORMAL,
                    C__RECORD_STATUS__DELETED  => $l_catdata['isys_obj__status'] != C__RECORD_STATUS__DELETED,
                    C__RECORD_STATUS__ARCHIVED => $l_catdata['isys_obj__status'] != C__RECORD_STATUS__ARCHIVED
                ],
                'p_strSelectedID' => $l_catdata['isys_obj__status'],
                'p_bDbFieldNN'    => true
            ],
            'C__CATG__GLOBAL_TITLE'    => ['p_strValue' => $l_catdata['isys_obj__title']],
            'C__CATG__GLOBAL_SYSID'    => [
                'p_strValue'  => $l_catdata['isys_obj__sysid'],
                'p_bDisabled' => C__SYSID__READONLY
            ],
            'C__CATG__GLOBAL_PURPOSE'  => [
                'p_strSelectedID' => $l_catdata['isys_catg_global_list__isys_purpose__id'],
                'p_strTable'      => 'isys_purpose'
            ],
            'C__CATG__GLOBAL_CATEGORY' => [
                'p_strSelectedID' => $l_catdata['isys_catg_global_list__isys_catg_global_category__id'],
                'p_strTable'      => 'isys_catg_global_category'
            ],
            'C__OBJ__CMDB_STATUS'      => [
                'p_strTable'      => 'isys_cmdb_status',
                'p_strSelectedID' => defined_or_default('C__CMDB_STATUS__IN_OPERATION'),
                'condition'       => "isys_cmdb_status__id NOT IN ('" . defined_or_default('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') . "')",
                'p_arDisabled'    => [],
                'p_bDbFieldNN'    => 1
            ],
            'C__CATG__GLOBAL_TAG'      => [
                'p_strTable'      => 'isys_tag',
                'emptyMessage'    => $language->get('LC__CMDB__CATG__GLOBAL__NO_TAGS_FOUND'),
                'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-catg-global-tag-update', selected);",
                'p_strSelectedID' => implode(',', $l_tag_selection),
                'multiselect'     => true
            ],
            $commentaryKey             => [
                'p_strValue' => $l_catdata['isys_obj__description']
            ]
        ];

        if (defined('C__CMDB_STATUS__IDOIT_STATUS')) {
            $l_rules['C__OBJ__CMDB_STATUS']['p_arDisabled'][constant('C__CMDB_STATUS__IDOIT_STATUS')] = 'LC__CMDB_STATUS__IDOIT_STATUS';
        }

        $l_cmdb_status_colors = [];
        $l_cmdb_statuses = isys_factory_cmdb_dialog_dao::get_instance('isys_cmdb_status', $this->get_database_component())
            ->get_data();

        foreach ($l_cmdb_statuses as $l_cmdb_status) {
            $l_cmdb_status_colors[$l_cmdb_status['isys_cmdb_status__id']] = '#' . $l_cmdb_status['isys_cmdb_status__color'];
        }

        if ($l_catdata['isys_obj__isys_cmdb_status__id'] > 0) {
            $l_rules['C__OBJ__CMDB_STATUS']['p_strSelectedID'] = $l_catdata['isys_obj__isys_cmdb_status__id'];
        }

        $l_show_in_tree = true;

        // See isys_quick_configuration_wizard_dao $m_skipped_objecttypes
        $l_blacklisted_object_types = filter_defined_constants([
            'C__OBJTYPE__GENERIC_TEMPLATE',
            'C__OBJTYPE__LOCATION_GENERIC',
            'C__OBJTYPE__RELATION',
            'C__OBJTYPE__CONTAINER',
            'C__OBJTYPE__PARALLEL_RELATION',
            'C__OBJTYPE__SOA_STACK'
        ]);

        // Check if object is a template
        if ($l_posts['template'] !== ''
            || $l_catdata['isys_obj__status'] == C__RECORD_STATUS__MASS_CHANGES_TEMPLATE
            || $l_catdata['isys_obj__status'] == C__RECORD_STATUS__TEMPLATE
            || in_array((int)$l_catdata['isys_obj__isys_obj_type__id'], $l_blacklisted_object_types)) {
            $l_show_in_tree = null;
            $l_rules['C__OBJ__CMDB_STATUS']['p_arDisabled'] = [];
        }

        $l_res = $p_cat->get_object_types(null, $l_show_in_tree);

        while ($l_row = $l_res->get_row()) {
            $l_rules['C__OBJ__TYPE']['p_arData'][$l_row['isys_obj_type__id']] = $l_row['isys_obj_type__title'];
        }

        $placeholderData = false;

        if (isys_glob_is_edit_mode()) {
            $sql = 'SELECT isys_obj__id AS id, isys_obj__isys_obj_type__id AS typeId, isys_obj__title as title, isys_obj__sysid AS sysid 
                FROM isys_obj 
                WHERE isys_obj__id = ' . $p_cat->convert_sql_id($l_catdata['isys_obj__id']) . '
                LIMIT 1;';

            if ($l_catdata['isys_obj__status'] == C__RECORD_STATUS__BIRTH) {
                $sql = 'SELECT isys_obj__id AS id, isys_obj__isys_obj_type__id AS typeId, isys_obj__title as title, isys_obj__sysid AS sysid 
                        FROM isys_obj 
                        WHERE isys_obj__isys_obj_type__id ' . $p_cat->prepare_in_condition(filter_defined_constants(['C__OBJTYPE__CABLE', 'C__OBJTYPE__RELATION']), true) . ' 
                        ORDER BY RAND() 
                        LIMIT 1;';
            }

            $objectData = $p_cat->retrieve($sql)->get_row();

            $placeholderData = isys_cmdb_dao_category_g_accounting::get_placeholders_info_with_data(
                true,
                $objectData['id'],
                $objectData['typeId'],
                $objectData['title'],
                $objectData['sysid']
            );
        }

        // Apply rules.
        $template
            ->assign('placeholders_g_global', $placeholderData)
            ->assign('created_by', $l_catdata['isys_obj__created_by'])
            ->assign('changed_by', $l_catdata['isys_obj__updated_by'])
            ->assign('status_color', $l_catdata['isys_cmdb_status__color'])
            ->assign('status_colors', isys_format_json::encode($l_cmdb_status_colors))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
