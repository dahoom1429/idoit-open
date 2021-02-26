<?php

/**
 * i-doit
 *
 * DAO: abstraction layer for CMDB Specific Categories.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_dao_category_specific extends isys_cmdb_dao_category
{
    /**
     * Category type's identifier.
     *
     * @var  integer
     */
    protected $m_cat_type = C__CMDB__CATEGORY__TYPE_SPECIFIC;

    /**
     * Category type's abbrevation.
     *
     * @var  string
     */
    protected $m_category_type_abbr = 'cats';

    /**
     * Category type's constant.
     *
     * @var  string
     */
    protected $m_category_type_const = 'C__CMDB__CATEGORY__TYPE_SPECIFIC';
}
