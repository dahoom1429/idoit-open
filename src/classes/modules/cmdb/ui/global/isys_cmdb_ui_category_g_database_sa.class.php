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
class isys_cmdb_ui_category_g_database_sa extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template.
     *
     * @param   isys_cmdb_dao_category $categoryDao
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  void
     */
    public function process(isys_cmdb_dao_category $categoryDao)
    {
        $catData = $categoryDao->get_general_data();

        $this->fill_formfields($categoryDao, $rules, $catData);

        $rules['C__CATG__DATABASE_SA__SIZE']['p_strValue'] = isys_convert::memory(
            $catData["isys_catg_database_sa_list__size"],
            $catData["isys_catg_database_sa_list__size_unit"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $rules['C__CATG__DATABASE_SA__MAX_SIZE']['p_strValue'] = isys_convert::memory(
            $catData["isys_catg_database_sa_list__max_size"],
            $catData["isys_catg_database_sa_list__max_size_unit"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $this->get_template_component()
            ->assign('ajaxUrl', isys_helper_link::create_url([C__GET__AJAX => 1, C__GET__AJAX_CALL => 'database', 'func' => 'getDatabaseInstances']))
            ->assign('databaseAccess', $categoryDao->getDatabaseAccess($catData['isys_catg_database_sa_list__id']))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }
}
