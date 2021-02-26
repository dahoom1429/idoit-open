<?php

/**
 * i-doit
 *
 * DAO: specific category for middleware.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_middleware extends isys_cmdb_dao_category_s_application
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'middleware';

    /**
     * @var string
     */
    protected $m_table = 'isys_cats_application_list';

    /**
     * @var string
     */
    protected $m_tpl = 'cats__application.tpl';

    /**
     * @var string
     */
    protected $m_ui = 'isys_cmdb_ui_category_s_application'; // function

    /**
     * Returns how many entries exists. The folder only needs to know if there are any entries in its subcategories.
     *
     * @param null $p_obj_id
     *
     * @return int
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($p_obj_id = null)
    {
        if ($this->get_category_id() == defined_or_default('C__CATS__MIDDLEWARE')) {
            $l_sql = 'SELECT
				(
				IFNULL((SELECT isys_cats_application_list__id AS cnt FROM isys_cats_application_list
					WHERE isys_cats_application_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_cats_app_variant_list__id AS cnt FROM  isys_cats_app_variant_list
					WHERE  isys_cats_app_variant_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				+
				IFNULL((SELECT isys_catg_application_list__id AS cnt FROM isys_catg_application_list
					INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
					WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' LIMIT 1), 0)
				)
				AS cnt';

            return ($this->retrieve($l_sql)
                    ->get_row_value('cnt') > 0) ? 1 : 0;
        } else {
            return parent::get_count($p_obj_id);
        }
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    protected function properties()
    {
        $l_properties = parent::properties();

        $l_properties['description'] = array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
            C__PROPERTY__INFO => [
                C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                C__PROPERTY__INFO__DESCRIPTION => 'Description'
            ],
            C__PROPERTY__DATA => [
                C__PROPERTY__DATA__FIELD => 'isys_cats_application_list__description'
            ],
            C__PROPERTY__UI   => [
                C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__MIDDLEWARE')
            ]
        ]);

        return $l_properties;
    }
}
