<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken - <dstuecken@i-doit.org>
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_file_object extends isys_cmdb_ui_category_specific
{
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Prepare needed parameters
        $objectId = $_GET[C__CMDB__GET__OBJECT];
        $connectionId = $_GET[C__CMDB__GET__CATLEVEL];

        // Define some rules
        $rules = [
            'C__CATS__FILE__ASSIGNMENT_TYPE' => [
                'p_bDbFieldNN' => true,
                'p_arData'     => filter_array_by_keys_of_defined_constants([
                    'C__CATG__FILE'           => 'LC__CMDB__CATG__FILE',
                    'C__CATG__MANUAL'         => 'LC__CMDB__CATG__MANUAL',
                    'C__CATG__EMERGENCY_PLAN' => 'LC__CMDB__CATG__EMERGENCY_PLAN',
                ]),
            ]
        ];

        // Check whether edit/create
        if (!empty($connectionId)) {
            // Retrieve data by connection id
            $data = $p_cat->get_data(null, $objectId, ' AND isys_connection__id = ' . $p_cat->convert_sql_id($connectionId), '', C__RECORD_STATUS__NORMAL)
                ->get_row();

            $categoryConstant = null;
            $assignedObjectId = null;

            // Process file
            if (!empty($data['isys_catg_file_list__id'])) {
                $categoryConstant = defined_or_default('C__CATG__FILE');
                $assignedObjectId = $data['isys_catg_file_list__isys_obj__id'];

                $rules['C__CATS__FILE__FILE_LINK']['p_strValue'] = $data['isys_catg_file_list__link'];
            } elseif (!empty($data['isys_catg_manual_list__id'])) {
                $categoryConstant = defined_or_default('C__CATG__MANUAL');
                $assignedObjectId = $data['isys_catg_manual_list__isys_obj__id'];
                $rules['C__CATS__FILE__MANUAL_TITLE']['p_strValue'] = $data['isys_catg_manual_list__title'];
            } elseif (!empty($data['isys_catg_emergency_plan_list__id'])) {
                $categoryConstant = defined_or_default('C__CATG__EMERGENCY_PLAN');
                $assignedObjectId = $data['isys_catg_emergency_plan_list__isys_obj__id'];
                $rules['C__CATS__FILE__EMERGENCY_PLAN_TITLE']['p_strValue'] = $data['isys_catg_emergency_plan_list__title'];
            }

            $rules['C__CATS__FILE__ASSIGNMENT_TYPE']['p_strSelectedID'] = $categoryConstant;
            $rules['C__CATS__FILE__OBJECT']['p_strSelectedID'] = $assignedObjectId;
            $rules['C__CATS__FILE__ASSIGNMENT_TYPE']['p_bDisabled'] = true;
            $rules['C__CATS__FILE__OBJECT']['p_bDisabled'] = true;
        }

        // Apply rules.
        $this->get_template_component()
            ->assign('assignedCategoryConstant', $categoryConstant ?: defined_or_default('C__CATG__FILE'))
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }
}
