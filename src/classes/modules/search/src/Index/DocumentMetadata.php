<?php

namespace idoit\Module\Search\Index;

/**
 * i-doit
 *
 * Document
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @codeCoverageIgnore
 */
class DocumentMetadata implements \JsonSerializable
{
    const PROPERTIES = [
        'categoryDao',
        'categoryConstant',
        'objectTypeId',
        'objectId',
        'objectStatus',
        'categoryTitle',
        'categoryId',
        'categoryStatus',
        'propertyTitle'
    ];

    /**
     * Classname of category dao
     *
     * @var string
     */
    private $categoryDao;

    /**
     * @var string
     */
    private $categoryConstant;

    /**
     * @var int
     */
    private $objectTypeId;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var int
     */
    private $objectStatus;

    /**
     * @var string
     */
    private $categoryTitle;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var int
     */
    private $categoryStatus;

    /**
     * @var string
     */
    private $propertyTitle;

    /**
     * DocumentMetadata constructor.
     *
     * @param string $categoryDao
     * @param string $categoryConstant
     * @param int    $objectTypeId
     * @param int    $objectId
     * @param int    $objectStatus
     * @param string $categoryTitle
     * @param int    $categoryId
     * @param int    $categoryStatus
     * @param string $propertyTitle
     */
    public function __construct($categoryDao, $categoryConstant, $objectTypeId, $objectId, $objectStatus, $categoryTitle, $categoryId, $categoryStatus, $propertyTitle)
    {
        $this->categoryDao = $categoryDao;
        $this->categoryConstant = $categoryConstant;
        $this->objectTypeId = $objectTypeId;
        $this->objectId = $objectId;
        $this->objectStatus = $objectStatus;
        $this->categoryTitle = $categoryTitle;
        $this->categoryId = $categoryId;
        $this->categoryStatus = $categoryStatus;
        $this->propertyTitle = $propertyTitle;
    }

    public function __toString()
    {
        return sprintf('%s.%s.%s.%s.%s', $this->objectTypeId, $this->objectId, $this->categoryTitle, $this->categoryId, $this->propertyTitle);
    }

    public static function createInstanceFromArray(array $data)
    {
        $metadata = new \ReflectionClass(self::class);
        $metadata = $metadata->newInstanceWithoutConstructor();

        foreach (self::PROPERTIES as $property) {
            if (property_exists($metadata, $property) === false) {
                continue;
            }

            $metadata->{$property} = $data[$property];
        }

        return $metadata;
    }

    /**
     * @return string
     */
    public function getCategoryDao()
    {
        return $this->categoryDao;
    }

    /**
     * @return string
     */
    public function getCategoryConstant()
    {
        return $this->categoryConstant;
    }

    /**
     * @return int
     */
    public function getObjectTypeId()
    {
        return $this->objectTypeId;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return int
     */
    public function getObjectStatus()
    {
        return $this->objectStatus;
    }

    /**
     * @return string
     */
    public function getCategoryTitle()
    {
        return $this->categoryTitle;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return int
     */
    public function getCategoryStatus()
    {
        return $this->categoryStatus;
    }

    /**
     * @return string
     */
    public function getPropertyTitle()
    {
        return $this->propertyTitle;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $return = [];
        foreach (self::PROPERTIES as $property) {
            $return [$property] = $this->{$property};
        }
        return $return;
    }
}
