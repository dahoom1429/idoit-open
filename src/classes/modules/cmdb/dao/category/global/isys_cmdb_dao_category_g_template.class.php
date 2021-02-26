<?php

/**
 * i-doit
 *
 * DAO: global category for <category name>s
 *
 * This is a template for global category DAOs (it's also useful for specific
 * category DAOs). If you want to write a new category DAO, you can just copy
 * this template, rename the file and change the code for your needs. Beware of
 * placeholder like <category name> or the many 'template' strings. And never
 * forget: Please, follow our developer guidelines!
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     <author> <<mail>>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_template extends isys_cmdb_dao_category_global
{

    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'template';

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @todo make a small example for the new property-system.
     */
    protected function properties()
    {
        return [
            'title' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TEMPLATE__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_template_list__title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__TEMPLATE__TITLE'
                ]
            ])
        ];
    }

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
// Defaults to false.
//	protected $m_multivalued = true;

    /**
     * Category's template file.
     *
     * @var string
     *
     * @todo No standard behavior!
     */
//    protected $m_tpl = 'catg__strange_name.tpl';

    /**
     * Category's identifier
     *
     * @var int
     *
     * @todo No standard behavior!
     */
//	protected $m_category_id = C__CMDB__SUBCAT__STRANGE_NAME;

    /**
     * Category's constant
     *
     * @var string
     *
     * @todo No standard behavior!
     */
//    protected $m_category_const = 'C__CMDB__SUBCAT__STRANGE_NAME';

    /**
     * Main table where properties are stored persistently
     *
     * @var string
     *
     * @todo No standard behavior!
     */
//    protected $m_table = 'isys_catg_strange_name_list';

    /**
     * Category's list DAO
     *
     * @var string
     *
     * @todo No standard behavior!
     */
//	protected $m_list = 'isys_cmdb_dao_list_catg_strange_name';

    /**
     * Category's user interface
     *
     * @var string
     *
     * @todo No standard behavior!
     */
//    protected $m_ui = 'isys_cmdb_ui_category_g_strange_name';

}