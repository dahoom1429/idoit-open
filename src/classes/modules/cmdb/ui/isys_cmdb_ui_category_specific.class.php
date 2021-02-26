<?php

/**
 * i-doit
 *
 * CMDB UI: specific category abstraction layer
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_ui_category_specific extends isys_cmdb_ui_category
{
    /**
     * Fetches category's title from database.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     *
     * @return  string
     * @author  André Wösten <awoesten@i-doit.org>
     */
    public function gui_get_title(isys_cmdb_dao_category &$p_cat)
    {
        $l_cat_id = $p_cat->get_category_id();

        $l_title = $p_cat->retrieve('SELECT isysgui_cats__title FROM isysgui_cats WHERE isysgui_cats__id = ' . $p_cat->convert_sql_id($l_cat_id) . ';')
            ->get_row_value('isysgui_cats__title');

        if (!empty($l_title)) {
            return isys_application::instance()->container->get('language')
                ->get($l_title);
        }

        return 'ERROR: isysgui_cats, title for selected catg not found (ID: ' . $l_cat_id . ').';
    }
}
