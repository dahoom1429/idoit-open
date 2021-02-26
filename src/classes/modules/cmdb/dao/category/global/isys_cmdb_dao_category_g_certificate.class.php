<?php

use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * DAO: global category for Certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_certificate extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'certificate';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CERTIFICATE';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Method for returning the properties.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @return  array
     */
    protected function properties()
    {
        return [
            'type' => new DialogProperty(
                'C__CATG__CERTIFICATE__TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_catg_certificate_list__isys_certificate_type__id',
                'isys_catg_certificate_list',
                'isys_certificate_type'
            ),
            'create_date' => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CERTIFICATE__CREATE_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Creation date'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_certificate_list__created',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_certificate_list__created FROM isys_catg_certificate_list',
                        'isys_catg_certificate_list',
                        'isys_catg_certificate_list__id',
                        'isys_catg_certificate_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_certificate_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__CERTIFICATE__CREATE_DATE'
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'date'
                    ]
                ]
            ]),
            'expire_date' => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CERTIFICATE__EXPIRE_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Expire date'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_certificate_list__expire',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_certificate_list__expire FROM isys_catg_certificate_list',
                        'isys_catg_certificate_list',
                        'isys_catg_certificate_list__id',
                        'isys_catg_certificate_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_certificate_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID => 'C__CATG__CERTIFICATE__EXPIRE_DATE'
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'date'
                    ]
                ]
            ]),
            'common_name' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__CERTIFICATE__COMMON_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Common name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_certificate_list__common_name',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_certificate_list__common_name FROM isys_catg_certificate_list',
                        'isys_catg_certificate_list',
                        'isys_catg_certificate_list__id',
                        'isys_catg_certificate_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_certificate_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__CERTIFICATE__COMMON_NAME'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_certificate_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CERTIFICATE', 'C__CATG__CERTIFICATE')
                ]
            ])
        ];
    }
}
