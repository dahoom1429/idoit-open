<?php

/**
 * i-doit
 *
 * DAO: specific category list for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_audit extends isys_component_dao_category_table_list
{

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    }

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    }

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        $l_table = $this->m_cat_dao->get_table();
        $l_type_table = 'isys_catg_audit_type';

        if ($p_row[$l_table . '__type'] > 0) {
            $l_sql = 'SELECT ' . $l_type_table . '__title FROM ' . $l_type_table . ' WHERE ' . $l_type_table . '__id = ' . $p_row[$l_table . '__type'] . ' LIMIT 1;';

            $l_query = $this->retrieve($l_sql);

            if ($l_row = $l_query->get_row()) {
                $p_row[$l_table . '__type'] = $l_row[$l_type_table . '__title'];
            }
        }

        if ($p_row[$l_table . '__apply']) {
            $p_row[$l_table . '__apply'] = isys_locale::get_instance()
                ->fmt_date($p_row[$l_table . '__apply']);
        } else {
            $p_row[$l_table . '__apply'] = isys_tenantsettings::get('gui.empty_value', '-');
        }
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_table = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__id'    => 'ID',
            $l_table . '__title' => $l_properties['title'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__type'  => $l_properties['type'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__apply' => $l_properties['apply'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    }
}
