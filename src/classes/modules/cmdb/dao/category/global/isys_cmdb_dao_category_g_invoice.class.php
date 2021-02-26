<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for invoices
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_invoice extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'invoice';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__INVOICE';

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'denotation';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic Property: Amount
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_locale
     * @throws Exception
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function dynamic_property_callback_amount($p_row)
    {
        return $this->dynamicMonetaryFormatter($p_row, 'isys_catg_invoice_list__amount', $p_row['isys_catg_invoice_list__isys_obj__id']);
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    protected function dynamic_properties()
    {
        $l_dao = new isys_cmdb_dao_category_g_invoice(isys_application::instance()->database);

        return [
            '_amount' => new DynamicProperty(
                'LC__CMDB__CATG__INVOICE__AMOUNT',
                'isys_catg_invoice_list__isys_obj__id',
                'isys_catg_invoice_list',
                [
                    $this,
                    'dynamic_property_callback_amount'
                ]
            ),
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'denotation'                    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__denotation',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_invoice_list__denotation FROM isys_catg_invoice_list',
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__INVOICE__DENOTATION'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            // @todo retrieve date format
            'date'                          => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Invoice Date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__date',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_invoice_list__date FROM isys_catg_invoice_list',
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__DATE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_calSelDate' => '',
                        'p_bTime'      => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'amount'                        => array_replace_recursive(isys_cmdb_dao_category_pattern::money(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__AMOUNT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Amount'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__amount',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        isys_cmdb_dao_category_g_accounting::build_costs_select_join(
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__amount'
                    ),
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_invoice_list', 'LEFT', 'isys_catg_invoice_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__INVOICE__AMOUNT'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                ]
            ]),
            // @todo retrieve date format
            'edited'                        => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__EDITED',
                    C__PROPERTY__INFO__DESCRIPTION => 'edited'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__edited',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_invoice_list__edited FROM isys_catg_invoice_list',
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__EDITED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_calSelDate' => '',
                        'p_bTime'      => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            // @todo retrieve date format
            'financial_accounting_delivery' => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Dispensing Tax Accounting'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__financial_accounting_delivery',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_invoice_list__financial_accounting_delivery FROM isys_catg_invoice_list',
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__FINANCIAL_ACCOUNTING_DELIVERY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_calSelDate' => '',
                        'p_bTime'      => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'charged'                       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__INVOICE__CHARGED',
                    C__PROPERTY__INFO__DESCRIPTION => 'Charged'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_invoice_list__charged',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_invoice_list__charged = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . ' WHEN isys_catg_invoice_list__charged = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_catg_invoice_list',
                        'isys_catg_invoice_list',
                        'isys_catg_invoice_list__id',
                        'isys_catg_invoice_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_invoice_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_invoice_list', 'LEFT', 'isys_catg_invoice_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__INVOICE__CHARGED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'description'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_invoice_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__INVOICE', 'C__CATG__INVOICE')
                ]
            ])
        ];
    }
}
