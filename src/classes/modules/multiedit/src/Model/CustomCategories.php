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
class CustomCategories extends Categories
{
    /**
     * @return $this|void
     */
    public function setData()
    {
        try {
            $supportedCategoryTypes = implode(',', $this->getSupportedCategoryTypes());
            $container = isys_application::instance()->container;
            $language = $container->get('language');

            $query = "SELECT *,
            (
                SELECT GROUP_CONCAT(isys_obj_type__title SEPARATOR ', ') FROM isys_obj_type
                INNER JOIN `isys_obj_type_2_isysgui_catg_custom` ON `isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id` = isys_obj_type__id
                WHERE isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = isysgui_catg_custom__id
                AND isys_obj_type__const != 'C__OBJTYPE__GENERIC_TEMPLATE'
            ) AS objTypes
            FROM isysgui_catg_custom
            WHERE isysgui_catg_custom__config NOT LIKE '%report_browser%'";

            $filter = $this->getFilter();

            if (!empty($filter->getObjects())) {
                $query .= ' AND isysgui_catg_custom__id IN (
                SELECT DISTINCT isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id FROM isys_obj_type_2_isysgui_catg_custom WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id IN (
                    SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ')
                )
            )';
            }

            $categories = $filter->getCategories();

            if (!empty($categories)) {
                $condition = ' AND isysgui_catg_custom__const IN (\'' . implode('\',\'', $categories) . '\')';
                if (is_numeric($categories[0])) {
                    $condition = ' AND isysgui_catg_custom__id IN (' . implode(',', $categories) . ')';
                }
                $query .= $condition;
            }

            $result = $this->retrieve($query);

            while ($row = $result->get_row()) {
                $objectTypes = $language->get_in_text($row['objTypes']);
                $categoryTitle = $language->get($row['isysgui_catg_custom__title']);

                if ($objectTypes) {
                    $categoryTitle .= ' (' . $objectTypes . ')';
                }

                $this->data[$this->getType() . '_' . $row['isysgui_catg_custom__id'] . ':' . $row['isysgui_catg_custom__class_name']] = $categoryTitle;
                $this->increment();
                if ($row['isysgui_catg_custom__list_multi_value'] > 0) {
                    $this->addToMultivalueCategories($row['isysgui_catg_custom__id']);
                }
            }

            return $this;
        } catch (\Exception $e) {
            throw new CategoryDataException('Collecting custom categories failed in File : ' . $e->getFile() . ' on Line: ' . $e->getLine() . ' with Message: ' . $e->getMessage());
        }
    }

    public function __construct(isys_component_database $p_db)
    {
        $this->setType(C__CMDB__CATEGORY__TYPE_CUSTOM);
        $this->setSupportedCategoryTypes([
            \isys_cmdb_dao_category::TYPE_EDIT,
            \isys_cmdb_dao_category::TYPE_ASSIGN,
        ]);
        parent::__construct($p_db);
    }
}
