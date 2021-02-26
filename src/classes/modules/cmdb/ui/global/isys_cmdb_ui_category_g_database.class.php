<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_database extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @param   isys_cmdb_dao_category $catDao
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $catDao)
    {
        $catData = $catDao->get_general_data();

        $this->fill_formfields($catDao, $rules, $catData);

        $l_smarty_ajax_param = [
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'smartyplugin',
            'mode'            => 'edit'
        ];

        $applicationObjectId = $catDao->convert_sql_id($catData['isys_connection__isys_obj__id']) ?: 'NULL';
        $objectId = $catDao->get_object_id();

        $rules['C__CATG__DATABASE__VERSION']['condition'] = 'isys_catg_version_list__isys_obj__id = ' . $applicationObjectId;
        $rules['C__CATG__DATABASE__SIZE']['p_strValue'] = isys_convert::formatNumber(
            isys_convert::memory(
                $catData["isys_catg_database_list__size"],
                $catData["isys_catg_database_list__size_unit"],
                C__CONVERT_DIRECTION__BACKWARD
            )
        );

        $this->get_template_component()
            ->assign('objectId', $objectId)
            ->assign("smarty_ajax_url", isys_helper_link::create_url($l_smarty_ajax_param))
            ->assign('ajaxUrl', isys_helper_link::create_url([C__GET__AJAX => 1, C__GET__AJAX_CALL => 'database', 'func' => 'getApplicationData']))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }
}
