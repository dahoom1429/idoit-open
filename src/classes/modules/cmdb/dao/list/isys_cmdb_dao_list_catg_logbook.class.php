<?php

/**
 * i-doit
 *
 * DAO: Table list for the category Logbook
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_logbook extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__LOGBOOK');
    }

    /**
     * Return constant of category type
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org> - 2006-09-27
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @return isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2005-12-12
     * @version Dennis Stuecken 2008-07-24
     * @desc    retrieve data for category logbook list view
     */
    public function get_result($p_strTable = null, $p_nObjID, $p_unused = null)
    {
        $l_strSQL = "";

        $l_strSQL .= "SELECT isys_logbook__id, isys_logbook__title, isys_logbook__date, isys_logbook__changes, isys_logbook_level__title, isys_logbook_level__id, isys_catg_logb_list__id
            FROM isys_logbook
            INNER JOIN isys_logbook_level ON isys_logbook__isys_logbook_level__id = isys_logbook_level__id
            INNER JOIN isys_catg_logb_list ON isys_catg_logb_list__isys_obj__id = " . $this->convert_sql_id($p_nObjID) .

            " GROUP BY isys_catg_logb_list__id ORDER BY isys_logbook__date DESC;";

        return $this->retrieve($l_strSQL);
    }

    /**
     * @param array $p_arrRow
     *
     * @author Niclas Potthast <npotthast@i-doit.org> - 2007-10-15
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        if ($p_arrRow['isys_logbook__id'] != null) {
            // Set alert level.
            $l_strAlertLevel = $p_arrRow['isys_logbook_level__id'];

            if ($l_strAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 1)) {
                $l_strAlertLevel = 'blue';
            } elseif ($l_strAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__1', 2)) {
                $l_strAlertLevel = 'green';
            } elseif ($l_strAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__2', 3)) {
                $l_strAlertLevel = 'yellow';
            } elseif ($l_strAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 4)) {
                $l_strAlertLevel = 'red';
            }

            $l_strImage = '<img width="15px" height="15px" src="' . $g_dirs['images'] . 'icons/infobox/' . $l_strAlertLevel . '.png" title="' .
                $p_arrRow['isys_logbook_level__title'] . '" />&nbsp;&nbsp;';

            // Format date.
            $p_arrRow['isys_logbook__date'] = isys_application::instance()->container->get('locales')->fmt_datetime($p_arrRow['isys_logbook__date']);

            // Get the alert level images.
            $p_arrRow['isys_logbook_level__title'] = $l_strImage;
        }
    }

    /**
     * @return array
     *
     * @param string $p_table
     *
     * @version Niclas Potthast <npotthast@i-doit.org> - 2005-12-12
     */
    public function get_fields()
    {
        $languageManager = isys_application::instance()->container->get('language');

        return [
            "isys_logbook__date"  => $languageManager->get("LC__CMDB__LOGBOOK__DATE"),
            "isys_logbook__title" => $languageManager->get("LC__CMDB__LOGBOOK__TITLE"),
        ];
    }
}
