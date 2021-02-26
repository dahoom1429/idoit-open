<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyProvides extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var bool
     */
    protected $search;

    /**
     * @var bool
     */
    protected $import;

    /**
     * @var bool
     */
    protected $export;

    /**
     * @var bool
     */
    protected $report;

    /**
     * @var bool
     */
    protected $list;

    /**
     * @var bool
     */
    protected $multiedit;

    /**
     * @var bool
     */
    protected $validation;

    /**
     * @var bool
     */
    protected $virtual;

    /**
     * @var bool
     */
    protected $searchIndex;

    /**
     * @var bool
     */
    protected $filterable;

    /**
     * @return int
     */
    public function getProvidesBit()
    {
        return ($this->isSearch() ? Property::C__PROPERTY__PROVIDES__SEARCH : 0) + ($this->isImport() ? Property::C__PROPERTY__PROVIDES__IMPORT : 0) +
            ($this->isExport() ? Property::C__PROPERTY__PROVIDES__EXPORT : 0) + ($this->isReport() ? Property::C__PROPERTY__PROVIDES__REPORT : 0) +
            ($this->isList() ? Property::C__PROPERTY__PROVIDES__LIST : 0) + ($this->isMultiedit() ? Property::C__PROPERTY__PROVIDES__MULTIEDIT : 0) +
            ($this->isValidation() ? Property::C__PROPERTY__PROVIDES__VALIDATION : 0) + ($this->isVirtual() ? Property::C__PROPERTY__PROVIDES__VIRTUAL : 0) +
            ($this->isSearchIndex() ? Property::C__PROPERTY__PROVIDES__SEARCH_INDEX : 0) + ($this->isFilterable() ? Property::C__PROPERTY__PROVIDES__FILTERABLE : 0);
    }

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyProvides
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $propertyProvides = new static();

        return $propertyProvides->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $providesArray
     *
     * @return PropertyProvides
     */
    public function mapAttributes(array $providesArray)
    {
        $this->search = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__SEARCH];
        $this->import = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__IMPORT];
        $this->export = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__EXPORT];
        $this->report = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__REPORT];
        $this->list = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__LIST];
        $this->multiedit = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__MULTIEDIT];
        $this->validation = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__VALIDATION];
        $this->virtual = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__VIRTUAL];
        $this->searchIndex = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__SEARCH_INDEX];
        $this->filterable = (bool)$providesArray[Property::C__PROPERTY__PROVIDES__FILTERABLE];

        return $this;
    }

    /**
     * @return bool
     */
    public function isSearch()
    {
        return $this->search;
    }

    /**
     * @param bool $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * @return bool
     */
    public function isImport()
    {
        return $this->import;
    }

    /**
     * @param bool $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }

    /**
     * @return bool
     */
    public function isExport()
    {
        return $this->export;
    }

    /**
     * @param bool $export
     */
    public function setExport($export)
    {
        $this->export = $export;
    }

    /**
     * @return bool
     */
    public function isReport()
    {
        return $this->report;
    }

    /**
     * @param bool $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return bool
     */
    public function isList()
    {
        return $this->list;
    }

    /**
     * @param bool $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * @return bool
     */
    public function isMultiedit()
    {
        return $this->multiedit;
    }

    /**
     * @param bool $multiedit
     */
    public function setMultiedit($multiedit)
    {
        $this->multiedit = $multiedit;
    }

    /**
     * @return bool
     */
    public function isValidation()
    {
        return $this->validation;
    }

    /**
     * @param bool $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        return $this->virtual;
    }

    /**
     * @param bool $virtual
     */
    public function setVirtual($virtual)
    {
        $this->virtual = $virtual;
    }

    /**
     * @return bool
     */
    public function isSearchIndex()
    {
        return $this->searchIndex;
    }

    /**
     * @param bool $searchIndex
     */
    public function setSearchIndex($searchIndex)
    {
        $this->searchIndex = $searchIndex;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * @param bool $filterable
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH) {
            return $this->search !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__IMPORT) {
            return $this->import !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__EXPORT) {
            return $this->export !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__REPORT) {
            return $this->report !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__LIST) {
            return $this->list !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__MULTIEDIT) {
            return $this->multiedit !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VALIDATION) {
            return $this->validation !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VIRTUAL) {
            return $this->virtual !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH_INDEX) {
            return $this->searchIndex !== null;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__FILTERABLE) {
            return $this->filterable !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH) {
            return $this->search;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__IMPORT) {
            return $this->import;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__EXPORT) {
            return $this->export;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__REPORT) {
            return $this->report;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__LIST) {
            return $this->list;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__MULTIEDIT) {
            return $this->multiedit;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VALIDATION) {
            return $this->validation;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VIRTUAL) {
            return $this->virtual;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH_INDEX) {
            return $this->searchIndex;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__FILTERABLE) {
            return $this->filterable;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH) {
            $this->search = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__IMPORT) {
            $this->import = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__EXPORT) {
            $this->export = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__REPORT) {
            $this->report = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__LIST) {
            $this->list = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__MULTIEDIT) {
            $this->multiedit = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VALIDATION) {
            $this->validation = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VIRTUAL) {
            $this->virtual = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH_INDEX) {
            $this->searchIndex = $value;
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__FILTERABLE) {
            $this->filterable = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH) {
            unset($this->search);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__IMPORT) {
            unset($this->import);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__EXPORT) {
            unset($this->export);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__REPORT) {
            unset($this->report);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__LIST) {
            unset($this->list);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__MULTIEDIT) {
            unset($this->multiedit);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VALIDATION) {
            unset($this->validation);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__VIRTUAL) {
            unset($this->virtual);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__SEARCH_INDEX) {
            unset($this->searchIndex);
        }

        if ($offset === Property::C__PROPERTY__PROVIDES__FILTERABLE) {
            unset($this->filterable);
        }
    }
}
