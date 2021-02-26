<?php

use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: specific category for rooms.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_room extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'room';

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
                'C__CATS__ROOM_TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_cats_room_list__isys_room_type__id',
                'isys_cats_room_list',
                'isys_room_type'
            )),
            'number'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROOM_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Room number'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__number'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__ROOM_NUMBER'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'floor'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__ROOM_FLOOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Floor'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__floor'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__ROOM_FLOOR'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_room_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ROOM', 'C__CATS__ROOM')
                ]
            ])
        ];
    }
}
