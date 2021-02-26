<?php

/**
 * i-doit
 *
 * DAO: global category list for e-mail addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_mail_addresses extends isys_component_dao_category_table_list
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
     * Modifies output of every row
     *
     * @param array $p_aRow
     */
    public function modify_row(&$p_aRow)
    {
        $l_table = $this->m_cat_dao->get_table();

        $p_aRow[$l_table . '__primary'] = $p_aRow[$l_table . '__primary'] ? '<span class="green">' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__YES') . '</span>' : '<span class="red">' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__NO') . '</span>';
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
            $l_table . '__title'   => $l_properties['title'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__primary' => $l_properties['primary'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__description' => $l_properties['description'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]
        ];
    }
}
