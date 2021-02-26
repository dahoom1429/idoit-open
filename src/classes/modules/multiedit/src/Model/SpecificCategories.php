<?php
namespace idoit\Module\Multiedit\Model;

use idoit\Module\Multiedit\Component\Multiedit\Exception\CategoryDataException;
use isys_component_database;
use isys_application;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SpecificCategories extends Categories
{
    /**
     * @return $this|void
     * @throws \isys_exception_database
     */
    public function setData()
    {
        $blackListAsString = implode(',', $this->getBlacklist());
        $supportedCategoryTypes = implode(',', $this->getSupportedCategoryTypes());
        $container = isys_application::instance()->container;
        $language = $container->get('language');
        $categoryFilterCondition = '';

        try {
            $query = "SELECT *, (
                SELECT GROUP_CONCAT(DISTINCT(subQ.isys_obj_type__title) SEPARATOR ', ') FROM    (
                        SELECT *, 1 AS 'check' FROM isys_obj_type
                        LEFT JOIN `isysgui_cats_2_subcategory` ON `isysgui_cats_2_subcategory__isysgui_cats__id__parent` = isys_obj_type__isysgui_cats__id OR `isysgui_cats_2_subcategory__isysgui_cats__id__child` = isys_obj_type__isysgui_cats__id
                        WHERE isys_obj_type__isysgui_cats__id IS NOT NULL
                    ) AS subQ
                 WHERE subQ.isysgui_cats_2_subcategory__isysgui_cats__id__child = main.isysgui_cats__id OR 
                    subQ.isysgui_cats_2_subcategory__isysgui_cats__id__parent = main.isysgui_cats__id OR 
                    subQ.isys_obj_type__isysgui_cats__id = main.isysgui_cats__id
            ) AS objTypes
            
            FROM isysgui_cats main
            LEFT JOIN isys_property_2_cat propCat ON propCat.isys_property_2_cat__isysgui_cats__id = main.isysgui_cats__id
            WHERE main.isysgui_cats__type IN ({$supportedCategoryTypes}) AND main.isysgui_cats__id NOT IN ({$blackListAsString}) AND 
            !LOCATE('_ROOT', main.isysgui_cats__const) ";

            $filter = $this->getFilter();

            if (!empty($filter->getObjects())) {
                $query .= ' AND main.isysgui_cats__id IN (
                SELECT isys_obj_type__isysgui_cats__id FROM (
                    SELECT isys_obj_type__isysgui_cats__id FROM isys_obj_type WHERE isys_obj_type__id IN (
                        SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ') 
                    )
                    UNION 
                    SELECT isysgui_cats_2_subcategory__isysgui_cats__id__child FROM isysgui_cats_2_subcategory WHERE isysgui_cats_2_subcategory__isysgui_cats__id__parent IN (
                        SELECT isys_obj_type__isysgui_cats__id FROM isys_obj_type WHERE isys_obj_type__id IN (
                            SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ') 
                        )
                    )
                    UNION
                    SELECT isysgui_cats_2_subcategory__isysgui_cats__id__parent FROM isysgui_cats_2_subcategory WHERE isysgui_cats_2_subcategory__isysgui_cats__id__child IN (
                        SELECT isys_obj_type__isysgui_cats__id FROM isys_obj_type WHERE isys_obj_type__id IN (
                            SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ') 
                        )
                    )
                ) AS filterCategory
            )';
            }

            $categories = $filter->getCategories();

            if (!empty($categories)) {
                $categoryFilterCondition = ' AND main.isysgui_cats__const IN (\'' . implode('\',\'', $categories) . '\')';
                if (is_numeric($categories[0])) {
                    $categoryFilterCondition = ' AND main.isysgui_cats__id IN (' . implode(',', $categories) . ')';
                }
                $query .= $categoryFilterCondition;
            }

            $result = $this->retrieve($query);

            while ($row = $result->get_row()) {
                // @see  ID-6622  This should prevent errors after categories have been removed.
                if (!class_exists($row['isysgui_cats__class_name'])) {
                    continue;
                }

                $objectTypes = $language->get_in_text($row['objTypes']);

                $categoryTitle = $language->get($row['isysgui_cats__title']);

                if ($objectTypes) {
                    $categoryTitle .= ' (' . $objectTypes . ')';
                }

                $this->data[$this->getType() . '_' . $row['isysgui_cats__id'] . ':' . $row['isysgui_cats__class_name']] = $categoryTitle;
                $this->increment();

                $checkSql = 'SELECT isys_property_2_cat__prop_key FROM isys_property_2_cat
                    WHERE isys_property_2_cat__cat_const = ' . $this->convert_sql_text($row['isysgui_cats__const']) . '
                        AND isys_property_2_cat__prop_type = ' . $this->convert_sql_int(C__PROPERTY_TYPE__STATIC) . '
                        AND isys_property_2_cat__prop_key != ' . $this->convert_sql_text('description') . '
                        AND isys_property_2_cat__prop_provides & ' . $this->convert_sql_int(C__PROPERTY__PROVIDES__MULTIEDIT);

                $checkResult = $this->retrieve($checkSql);
                $numProperties = $checkResult->num_rows();

                if ($numProperties === 1) {
                    $propKey = $checkResult->get_row_value('isys_property_2_cat__prop_key');
                    $catDao = $row['isysgui_cats__class_name']::instance($container->get('database'));
                    $properties = $catDao->get_properties();
                    $property = $properties[$propKey];

                    if ((int)$property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__OBJECT_BROWSER ||
                        (int)$property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__N2M) {
                        continue;
                    }
                } elseif ($numProperties === 0) {
                    // Remove category from list, because there are no properties which are usable for the multiedit list
                    unset($this->data[$this->getType() . '_' . $row['isysgui_cats__id'] . ':' . $row['isysgui_cats__class_name']]);
                    $this->decrement();
                    continue;
                }

                if ($row['isysgui_cats__list_multi_value'] > 0) {
                    $this->addToMultivalueCategories($row['isysgui_cats__id']);
                }
            }

            return $this;
        } catch (\Exception $e) {
            throw new CategoryDataException('Collecting specific categories failed in File : ' . $e->getFile() . ' on Line: ' . $e->getLine() . ' with Message: ' . $e->getMessage());
        }
    }

    public function __construct(isys_component_database $p_db)
    {
        $blacklist = filter_defined_constants([
            'C__CATS__PDU_OVERVIEW',
            'C__CATS__FILE',
            'C__CATS__FILE_VERSIONS',
            'C__CATS__FILE_ACTUAL',
            'C__CATS__FILE_OBJECTS',
//            'C__CATS__APPLICATION_ASSIGNED_OBJ',
            'C__CATS__RELATION_DETAILS',
            'C__CATS__PARALLEL_RELATION',
            'C__CATS__PDU_BRANCH',
            'C__CATS__CHASSIS',
            'C__CATS__CHASSIS_CABLING',
            'C__CATS__CHASSIS_DEVICES',
            'C__CATS__CHASSIS_VIEW',
            'C__CATS__DATABASE_SCHEMA',
            'C__CATS__DATABASE_ACCESS',
            'C__CATS__NET_IP_ADDRESSES',
            'C__CATS__LAYER2_NET',
            'C__CATS__LICENCE',
            'C__CATS__PERSON_LOGIN',
            'C__CATS__PERSON_GROUP_MEMBERS',
            'C__CATS__NET',
            'C__CATS__EMERGENCY_PLAN_ATTRIBUTE',
            'C__CATS__GROUP_TYPE',
            'C__CATS__PDU',
            'C__CATS__PDU_BRANCH',
            'C__CATS__PDU_OVERVIEW',
            'C__CATS__ORGANIZATION_CONTACT_ASSIGNMENT',
            'C__CATS__ORGANIZATION_PERSONS',
            'C__CATS__BASIC_AUTH',
            'C__CATS__ROUTER',
            'C__CATS__SAN_ZONING',
            //'C__CATS__PERSON_MASTER',
            'C__CATS__PERSON_GROUP_MASTER',
            'C__CATS__ORGANIZATION_MASTER_DATA',
            'C__CATS__CONTRACT_INFORMATION',
            'C__CATS__CONTRACT_ALLOCATION',
            'C__CATS__CLUSTER_SERVICE',
            'C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS',
            'C__CATS__LICENCE_OVERVIEW',
            // @see ID-934  Remove in i-doit 1.12
            'C__CMDB__SUBCAT__FILE_VERSIONS',
            'C__CMDB__SUBCAT__FILE_ACTUAL',
            'C__CMDB__SUBCAT__FILE_OBJECTS',
            'C__CMDB__SUBCAT__EMERGENCY_PLAN',
            'C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST',
            'C__CMDB__SUBCAT__LICENCE_OVERVIEW',
            // @see ID-1676
            'C__CATS__PERSON_NAGIOS',
            'C__CATS__PERSON_GROUP_NAGIOS',
        ]);

        $this->setType(C__CMDB__CATEGORY__TYPE_SPECIFIC);
        $this->setBlacklist($blacklist);
        $this->setSupportedCategoryTypes([
            \isys_cmdb_dao_category::TYPE_EDIT,
            \isys_cmdb_dao_category::TYPE_FOLDER,
            \isys_cmdb_dao_category::TYPE_ASSIGN,
            \isys_cmdb_dao_category::TYPE_REAR
        ]);
        parent::__construct($p_db);
    }
}
