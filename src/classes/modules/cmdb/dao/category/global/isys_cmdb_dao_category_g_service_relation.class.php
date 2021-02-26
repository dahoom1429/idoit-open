<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_service_relation extends isys_cmdb_dao_category_g_relation
{
    protected $m_category_const = 'C__CATG__IT_SERVICE_RELATIONS';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__IT_SERVICE_RELATION';

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        $parentProperties = parent::properties();

        foreach ($parentProperties as $name => $value) {
            $parentProperties[$name][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT]
                ->setSelectFieldObjectID('isys_catg_relation_list__isys_obj__id__itservice')
                ->setSelectGroupBy(
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory([
                        'isys_catg_relation_list__isys_obj__id__itservice'
                    ])
                )->setSelectCondition(
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_relation_list__isys_obj__id__itservice != isys_catg_relation_list__isys_obj__id__master',
                        'AND',
                        'isys_catg_relation_list__isys_obj__id__itservice != isys_catg_relation_list__isys_obj__id__slave',
                    ])
                );
        }

        return $parentProperties;
    }
}
