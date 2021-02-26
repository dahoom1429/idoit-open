<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;

/**
 * i-doit
 *
 * DAO: specific category for printers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_prt extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'prt';

    /**
     * Category entry is purgable.
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type' => (new DialogPlusProperty(
                'C__CATS__PRT_TYPE',
                'LC__CMDB__CATS__PRT_TYPE',
                'isys_cats_prt_list__isys_cats_prt_type__id',
                'isys_cats_prt_list',
                'isys_cats_prt_type'
            ))->mergePropertyUiParams(
                [
                    'p_bDbFieldNN' => 1
                ]
            )->setPropertyUiDefault(defined_or_default('C__CATS_PRT_TYPE__OTHER')),
            'is_color' => new DialogYesNoProperty(
                'C__CATS__PRT_ISCOLOR',
                'LC__CMDB__CATS__PRT_ISCOLOR',
                'isys_cats_prt_list__iscolor',
                'isys_cats_prt_list'
            ),
            'is_duplex' => new DialogYesNoProperty(
                'C__CATS__PRT_ISDUPLEX',
                'LC__CMDB__CATS__PRT_ISDUPLEX',
                'isys_cats_prt_list__isduplex',
                'isys_cats_prt_list'
            ),
            'emulation' => (new DialogPlusProperty(
                'C__CATS__PRT_EMULATION',
                'LC__CMDB__CATS__PRT_EMULATION',
                'isys_cats_prt_list__isys_cats_prt_emulation__id',
                'isys_cats_prt_list',
                'isys_cats_prt_emulation'
            ))->mergePropertyUiParams(
                [
                    'p_bDbFieldNN' => 1
                ]
            )->setPropertyUiDefault(defined_or_default('C__CATS_PRT_EMULATION__OTHER')),
            'paper_format' => (new DialogPlusProperty(
                'C__CATS__PRT_PAPER',
                'LC__CMDB__CATS__PRT_PAPER',
                'isys_cats_prt_list__isys_cats_prt_paper__id',
                'isys_cats_prt_list',
                'isys_cats_prt_paper'
            )),
            'description'  => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_prt_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PRT', 'C__CATS__PRT')
                ]
            ])
        ];
    }
}
