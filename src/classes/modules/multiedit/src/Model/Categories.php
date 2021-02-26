<?php
namespace idoit\Module\Multiedit\Model;

use idoit\Model\Dao\Base;
use isys_component_database;
use idoit\Module\Multiedit\Component\Multiedit\Exception;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class Categories extends Base
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    protected $type;

    /**
     * @var array
     */
    protected $blacklist;

    /**
     * @var array
     */
    protected $multivalueCategories = [];

    /**
     * @var array
     */
    protected $supportedCategoryTypes = [];

    /**
     * @var \idoit\Module\Multiedit\Component\Filter\CategoryFilter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @return $this
     */
    public function increment()
    {
        $this->count++;
        return $this;
    }

    /**
     * @return $this
     */
    public function decrement()
    {
        $this->count--;
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return $this
     */
    public function resetCount()
    {
        $this->count = 0;
        return $this;
    }

    /**
     * @return void
     */
    abstract public function setData();

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return array
     * @throws \isys_exception_database
     */
    public function getCategoryClass()
    {
        switch ($this->getType()) {
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                $table = 'isysgui_catg_custom';
                $selection = 'isysgui_catg_custom__class_name';
                $conditionField = 'isysgui_catg_custom__id';
                break;
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $table = 'isysgui_cats';
                $selection = 'isysgui_cats__class_name';
                $conditionField = 'isysgui_cats__id';
                break;
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
            default:
                $table = 'isysgui_catg';
                $selection = 'isysgui_catg__class_name';
            $conditionField = 'isysgui_catg__id';
                break;
        }

        $categoryClass = [];
        $filter = $this->getFilter();
        $categories = implode(',', $filter->getCategories());

        if ($categories !== '') {
            $condition = "WHERE {$conditionField} IN ({$categories})";
        }

        $query = "SELECT {$selection}, {$conditionField}  FROM {$table} {$condition};";
        $result = $this->retrieve($query);
        while ($row = $result->get_row()) {
            $categoryClass[$row[$conditionField]] = $row[$selection];
        }

        return $categoryClass;
    }

    /**
     * @return array
     * @throws \isys_exception_database
     */
    public function getCategoryDao()
    {
        $categoryClass = $this->getCategoryClass();
        $database = \isys_application::instance()->container->get('database');
        $daoInstances = [];

        /**
         * @var $daoClass \isys_cmdb_dao_category
         */
        foreach ($categoryClass as $categoryId => $class) {
            $daoClass = $class::instance($database);

            if (method_exists($daoClass, 'set_catg_custom_id')) {
                $daoClass->set_catg_custom_id($categoryId);
            }
            if (method_exists($daoClass, 'setCategoryInfo')) {
                $daoClass->setCategoryInfo($categoryId);
            }
            $daoInstances[$categoryId] = $daoClass;
        }
        return $daoInstances;
    }

    /**
     * @return $this
     * @throws \isys_exception_database
     */
    public function setProperties()
    {
        $categoryClass = $this->getCategoryClass();

        $database = \isys_application::instance()->container->get('database');
        $language = \isys_application::instance()->container->get('language');
        $properties = [];

        $filter = $this->getFilter();
        $categories = $filter->getCategories();
        try {
            /**
             * @var $daoClass \isys_cmdb_dao_category
             */
            foreach ($categoryClass as $categoryId => $class) {
                $daoClass = $class::instance($database);

                if (method_exists($daoClass, 'set_catg_custom_id')) {
                    $daoClass->set_catg_custom_id($categoryId);
                }

                $categoryProperties = $daoClass->get_properties(C__PROPERTY__WITH__VALIDATION);
                $newProperties = [];
                foreach ($categoryProperties as $key => $categoryProperty) {
                    $newProperties[$class . '__' . $key] = $categoryProperty;
                }
                $properties[$language->get($daoClass->getCategoryTitle())] = $newProperties;
            }
        } catch (\Exception $e) {
            throw new Exception\CategoryPropertiesException($e->getMessage());
        }

        $this->properties = $properties;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * @param mixed $blacklist
     *
     * @return Categories
     */
    public function setBlacklist($blacklist)
    {
        $this->blacklist = $blacklist;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return Categories
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getMultivalueCategories()
    {
        return $this->multivalueCategories;
    }

    /**
     * @param $category
     *
     * @return Categories
     */
    public function addToMultivalueCategories($category)
    {
        $this->multivalueCategories[$this->getType() . '_' . $category] = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getSupportedCategoryTypes()
    {
        return $this->supportedCategoryTypes;
    }

    /**
     * @param array $supportedCategoryTypes
     *
     * @return Categories
     */
    public function setSupportedCategoryTypes($supportedCategoryTypes)
    {
        $this->supportedCategoryTypes = $supportedCategoryTypes;
        return $this;
    }

    /**
     * @return \idoit\Module\Multiedit\Component\Filter\CategoryFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param \idoit\Module\Multiedit\Component\Filter\CategoryFilter $filter
     *
     * @return Categories
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }
}
