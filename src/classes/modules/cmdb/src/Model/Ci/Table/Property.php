<?php

namespace idoit\Module\Cmdb\Model\Ci\Table;

use isys_application;
use isys_cmdb_dao_category;

/**
 * i-doit
 *
 * Ci Table Property Config
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @since       1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Property
{
    /**
     * @var  boolean
     */
    private $indexed;

    /**
     * @var  string
     */
    private $class;

    /**
     * @var  string
     */
    private $key;

    /**
     * @var  string
     */
    private $categoryName;

    /**
     * @var  string
     */
    private $name;

    /**
     * @var  integer
     */
    private $customCatID;

    /**
     * @var  string
     */
    private $type;

    /**
     * @see  ID-6930  Due to this error, we need to provide "true" as default value.
     * @var  boolean
     */
    private $filterable = true;

    /**
     * Property constructor.
     *
     * @param string $class
     * @param string $key
     * @param string $categoryName
     * @param string $name
     * @param bool   $indexed
     * @param int    $customCatID
     * @param string $type
     * @param bool   $filterable
     */
    public function __construct($class = '', $key = '', $categoryName = '', $name = '', $indexed = false, $customCatID = null, $type = null, $filterable = true)
    {
        $this->setClass($class)
            ->setKey($key)
            ->setCategoryName($categoryName)
            ->setName($name)
            ->setIndexed($indexed)
            ->setCustomCatID($customCatID)
            ->setType($type)
            ->setFilterable($filterable);
    }

    /**
     * @return boolean
     */
    public function isIndexed()
    {
        return $this->indexed;
    }

    /**
     * @param boolean $indexed
     *
     * @return Property
     */
    public function setIndexed($indexed)
    {
        $this->indexed = $indexed;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return Property
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Property
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Builds the property key in form $(className)__$(key)
     *
     * @return string
     */
    public function getPropertyKey()
    {
        $key = $this->getClass();

        if ($this->getKey()) {
            $key .= '__' . $this->getKey();
        }

        if ($this->customCatID && $this->getKey() === 'description') {
            $key .= '_' . $this->customCatID;
        }

        return $key;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     *
     * @return Property
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Property
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $filterable
     *
     * @return Property
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * @return integer
     */
    public function getCustomCatID()
    {
        return $this->customCatID;
    }

    /**
     * @param integer $customCatID
     *
     * @return Property
     */
    public function setCustomCatID($customCatID)
    {
        $this->customCatID = (int)$customCatID;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Property
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get data of the property
     *
     * @return array|bool|null
     */
    public function getPropertyData()
    {
        try {
            $class = $this->getClass();
            if (class_exists($class) && is_subclass_of($class, 'isys_cmdb_dao_category')) {
                $dao = new $class(isys_application::instance()->container->get('database'));

                if (!$dao instanceof isys_cmdb_dao_category) {
                    return false;
                }

                if ($this->customCatID) {
                    $dao->set_catg_custom_id($this->customCatID);
                }

                return $dao->get_property_by_key($this->getKey());
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return $this->filterable;
    }
}
