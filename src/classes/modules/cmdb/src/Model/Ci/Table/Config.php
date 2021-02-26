<?php

namespace idoit\Module\Cmdb\Model\Ci\Table;

/**
 * i-doit
 *
 * Ci Table Config
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @since       1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Config
{
    /**
     * @var  array
     */
    private $properties = [];

    /**
     * @var  string
     */
    private $sortingProperty;

    /**
     * @var  string
     */
    private $sortingDirection;

    /**
     * @var  string
     */
    private $filterProperty;

    /**
     * @var  string
     */
    private $filterValue;

    /**
     * @var  boolean
     */
    private $rowClickable;

    /**
     * @var  integer
     */
    private $groupingType;

    /**
     * @var  boolean
     */
    private $filterWildcard;

    /**
     * @var  boolean
     */
    private $broadsearch = false;

    /**
     * @var  string
     */
    private $advancedOptionMemoryUnit;

    /**
     * Page number
     *
     * @var integer
     */
    private $paging;

    /**
     * Number of rows per page
     *
     * @var integer
     */
    private $rowsPerPage;

    /**
     * @var bool
     */
    private $showEmailLinks = true;

    /**
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Property $properties
     *
     * @return Config
     */
    public function addProperty(Property $properties)
    {
        $this->properties[] = $properties;

        return $this;
    }

    /**
     * @param Property[] $properties
     *
     * @return Config
     * @throws \isys_exception_general
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $property) {
            if (!is_a($property, 'idoit\\Module\\Cmdb\\Model\\Ci\\Table\\Property')) {
                throw new \isys_exception_general('All array items need to be instances of "idoit\\Module\\Cmdb\\Model\\Ci\\Table\\Property"!');
            }
        }

        $this->properties = $properties;

        return $this;
    }

    /**
     * @return  string
     */
    public function getSortingProperty()
    {
        return $this->sortingProperty;
    }

    /**
     * @param string $sortingProperty
     *
     * @return  Config
     */
    public function setSortingProperty($sortingProperty)
    {
        $this->sortingProperty = $sortingProperty;

        return $this;
    }

    /**
     * @return  string
     */
    public function getSortingDirection()
    {
        return $this->sortingDirection;
    }

    /**
     * @param string $sortingDirection
     *
     * @return  Config
     */
    public function setSortingDirection($sortingDirection)
    {
        $sortingDirection = strtoupper($sortingDirection);

        if ($sortingDirection === 'ASC' || $sortingDirection === 'DESC') {
            $this->sortingDirection = $sortingDirection;
        }

        return $this;
    }

    /**
     * @return  string
     */
    public function getFilterProperty()
    {
        return $this->filterProperty;
    }

    /**
     * @param string $filterProperty
     *
     * @return  Config
     */
    public function setFilterProperty($filterProperty)
    {
        $this->filterProperty = $filterProperty;

        return $this;
    }

    /**
     * @return  string
     */
    public function getFilterValue()
    {
        return $this->filterValue;
    }

    /**
     * @param string $filterValue
     *
     * @return  Config
     */
    public function setFilterValue($filterValue)
    {
        $this->filterValue = $filterValue;

        return $this;
    }

    /**
     * @return  boolean
     */
    public function isRowClickable()
    {
        return $this->rowClickable;
    }

    /**
     * @param boolean $rowClickable
     *
     * @return  Config
     */
    public function setRowClickable($rowClickable)
    {
        $this->rowClickable = !!$rowClickable;

        return $this;
    }

    /**
     * @return  integer
     */
    public function getGroupingType()
    {
        return $this->groupingType;
    }

    /**
     * @param integer $groupingType
     *
     * @return  Config
     */
    public function setGroupingType($groupingType)
    {
        $this->groupingType = (int)$groupingType;

        return $this;
    }

    /**
     * @return boolean
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function isFilterWildcard()
    {
        return $this->filterWildcard;
    }

    /**
     * @param boolean $filterWildcard
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setFilterWildcard($filterWildcard)
    {
        $this->filterWildcard = !!$filterWildcard;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getAdvancedOptionMemoryUnit()
    {
        return $this->advancedOptionMemoryUnit;
    }

    /**
     * @param string $advancedOptionMemoryUnit
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setAdvancedOptionMemoryUnit($advancedOptionMemoryUnit)
    {
        $this->advancedOptionMemoryUnit = $advancedOptionMemoryUnit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBroadsearch()
    {
        return $this->broadsearch;
    }

    /**
     * @param bool $broadsearch
     *
     * @return Config
     */
    public function setBroadsearch($broadsearch)
    {
        $this->broadsearch = $broadsearch;

        return $this;
    }

    /**
     * Get paging
     *
     * @return int
     */
    public function getPaging()
    {
        return $this->paging;
    }

    /**
     * Set paging
     *
     * @param int $paging
     *
     * @return Config
     */
    public function setPaging($paging)
    {
        $this->paging = $paging;

        return $this;
    }

    /**
     * Get number of rows per page
     *
     * @return int
     */
    public function getRowsPerPage()
    {
        return $this->rowsPerPage;
    }

    /**
     * Set number of rows per page
     *
     * @param int $rowsPerPage
     *
     * @return Config
     */
    public function setRowsPerPage($rowsPerPage)
    {
        $this->rowsPerPage = $rowsPerPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowEmailLinks(): bool
    {
        return $this->showEmailLinks;
    }

    /**
     * Determines if email should be linked with mailto
     *
     * @param bool $showEmailLinks
     *
     * @return $this
     */
    public function setShowEmailLinks($showEmailLinks)
    {
        $this->showEmailLinks = $showEmailLinks;

        return $this;
    }
}
