<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

class PropertyData extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var int|\isys_callback
     */
    protected $relationType;

    /**
     * @var \isys_callback
     */
    protected $relationHandler;

    /**
     * @var string
     */
    protected $fieldAlias;

    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * @var string
     */
    protected $sourceTable;

    /**
     * @var string[]
     */
    protected $references;

    /**
     * @var bool
     */
    protected $readOnly;

    /**
     * @var SelectJoin[]
     */
    protected $joins;

    /**
     * Not implemented
     *
     * @var mixed
     */
    protected $joinList;

    /**
     * @var bool
     */
    protected $index;

    /**
     * @var SelectSubSelect
     */
    protected $select;

    /**
     * @var string
     */
    protected $sort;

    /**
     * @var string
     */
    protected $sortAlias;

    /**
     * @var bool
     */
    protected $encrypt;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyData
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        if (isset($propertyArray[Property::C__PROPERTY__DATA__SELECT]) && !($propertyArray[Property::C__PROPERTY__DATA__SELECT] instanceof SelectSubSelect)) {
            throw new UnsupportedConfigurationTypeException('C__PROPERTY__DATA__SELECT needs to an instance of SelectSubSelect');
        }

        if (isset($propertyArray[Property::C__PROPERTY__DATA__JOIN]) && !is_array($propertyArray[Property::C__PROPERTY__DATA__JOIN])) {
            throw new UnsupportedConfigurationTypeException('C__PROPERTY__DATA__JOIN needs to be an array');
        }

        if (isset($propertyArray[Property::C__PROPERTY__DATA__JOIN])) {
            foreach ($propertyArray[Property::C__PROPERTY__DATA__JOIN] as $join) {
                if (!($join instanceof SelectJoin)) {
                    throw new UnsupportedConfigurationTypeException('Every memeber of C__PROPERTY__DATA__JOIN needs to be an instance of SelectJoin');
                }
            }
        }

        $propertyData = new static();

        return $propertyData->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyData
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->type = $propertyArray[Property::C__PROPERTY__DATA__TYPE];
        $this->field = $propertyArray[Property::C__PROPERTY__DATA__FIELD];
        $this->relationType = $propertyArray[Property::C__PROPERTY__DATA__RELATION_TYPE];
        $this->relationHandler = $propertyArray[Property::C__PROPERTY__DATA__RELATION_HANDLER];
        $this->fieldAlias = $propertyArray[Property::C__PROPERTY__DATA__FIELD_ALIAS];
        $this->tableAlias = $propertyArray[Property::C__PROPERTY__DATA__TABLE_ALIAS];
        $this->sourceTable = $propertyArray[Property::C__PROPERTY__DATA__SOURCE_TABLE];
        $this->references = $propertyArray[Property::C__PROPERTY__DATA__REFERENCES];
        $this->readOnly = (bool)$propertyArray[Property::C__PROPERTY__DATA__READONLY];
        $this->joins = $propertyArray[Property::C__PROPERTY__DATA__JOIN];
        $this->joinList = $propertyArray[Property::C__PROPERTY__DATA__JOIN_LIST];
        $this->index = (bool)$propertyArray[Property::C__PROPERTY__DATA__INDEX];
        $this->select = $propertyArray[Property::C__PROPERTY__DATA__SELECT];
        $this->sort = $propertyArray[Property::C__PROPERTY__DATA__SORT];
        $this->sortAlias = $propertyArray[Property::C__PROPERTY__DATA__SORT_ALIAS];
        $this->encrypt = (bool)$propertyArray[Property::C__PROPERTY__DATA__ENCRYPT];

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
     * @return PropertyData
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return PropertyData
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return int|\isys_callback
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    /**
     * @param int|\isys_callback $relationType
     *
     * @return PropertyData
     */
    public function setRelationType($relationType)
    {
        $this->relationType = $relationType;

        return $this;
    }

    /**
     * @return \isys_callback
     */
    public function getRelationHandler()
    {
        return $this->relationHandler;
    }

    /**
     * @param \isys_callback $relationHandler
     *
     * @return PropertyData
     */
    public function setRelationHandler($relationHandler)
    {
        $this->relationHandler = $relationHandler;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldAlias()
    {
        return $this->fieldAlias;
    }

    /**
     * @param string $fieldAlias
     *
     * @return PropertyData
     */
    public function setFieldAlias($fieldAlias)
    {
        $this->fieldAlias = $fieldAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @param string $tableAlias
     *
     * @return PropertyData
     */
    public function setTableAlias($tableAlias)
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceTable()
    {
        return $this->sourceTable;
    }

    /**
     * @param string $sourceTable
     *
     * @return PropertyData
     */
    public function setSourceTable($sourceTable)
    {
        $this->sourceTable = $sourceTable;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param string[] $references
     *
     * @return PropertyData
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly
     *
     * @return PropertyData
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * @return SelectJoin[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param SelectJoin[] $joins
     *
     * @return PropertyData
     */
    public function setJoins(array $joins)
    {
        foreach ($joins as $join) {
            if (!($join instanceof SelectJoin)) {
                throw new UnsupportedConfigurationTypeException('Every memeber of C__PROPERTY__DATA__JOIN needs to be an instance of SelectJoin');
            }
        }

        $this->joins = $joins;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJoinList()
    {
        return $this->joinList;
    }

    /**
     * @param mixed $joinList
     *
     * @return PropertyData
     */
    public function setJoinList($joinList)
    {
        $this->joinList = $joinList;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        return $this->index;
    }

    /**
     * @param bool $index
     *
     * @return PropertyData
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return SelectSubSelect
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param SelectSubSelect $select
     *
     * @return PropertyData
     */
    public function setSelect(SelectSubSelect $select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     *
     * @return PropertyData
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortAlias()
    {
        return $this->sortAlias;
    }

    /**
     * @param string $sortAlias
     *
     * @return PropertyData
     */
    public function setSortAlias($sortAlias)
    {
        $this->sortAlias = $sortAlias;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEncrypt()
    {
        return $this->encrypt;
    }

    /**
     * @param bool $encrypt
     *
     * @return PropertyData
     */
    public function setEncrypt($encrypt)
    {
        $this->encrypt = $encrypt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if ($offset === Property::C__PROPERTY__DATA__TYPE) {
            return $this->type !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD) {
            return $this->field !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_TYPE) {
            return $this->relationType !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_HANDLER) {
            return $this->relationHandler !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD_ALIAS) {
            return $this->fieldAlias !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__TABLE_ALIAS) {
            return $this->tableAlias !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__SOURCE_TABLE) {
            return $this->sourceTable !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__REFERENCES) {
            return $this->references !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__READONLY) {
            return $this->readOnly !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN) {
            return $this->joins !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN_LIST) {
            return $this->joinList !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__INDEX) {
            return $this->index !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__SELECT) {
            return $this->select instanceof SelectSubSelect;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT) {
            return $this->sort !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT_ALIAS) {
            return $this->sortAlias !== null;
        }

        if ($offset === Property::C__PROPERTY__DATA__ENCRYPT) {
            return $this->encrypt !== null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($offset === Property::C__PROPERTY__DATA__TYPE) {
            return $this->type;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD) {
            return $this->field;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_TYPE) {
            return $this->relationType;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_HANDLER) {
            return $this->relationHandler;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD_ALIAS) {
            return $this->fieldAlias;
        }

        if ($offset === Property::C__PROPERTY__DATA__TABLE_ALIAS) {
            return $this->tableAlias;
        }

        if ($offset === Property::C__PROPERTY__DATA__SOURCE_TABLE) {
            return $this->sourceTable;
        }

        if ($offset === Property::C__PROPERTY__DATA__REFERENCES) {
            return $this->references;
        }

        if ($offset === Property::C__PROPERTY__DATA__READONLY) {
            return $this->readOnly;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN) {
            return $this->joins;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN_LIST) {
            return $this->joinList;
        }

        if ($offset === Property::C__PROPERTY__DATA__INDEX) {
            return $this->index;
        }

        if ($offset === Property::C__PROPERTY__DATA__SELECT) {
            return $this->select;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT) {
            return $this->sort;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT_ALIAS) {
            return $this->sortAlias;
        }

        if ($offset === Property::C__PROPERTY__DATA__ENCRYPT) {
            return $this->encrypt;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === Property::C__PROPERTY__DATA__TYPE) {
            $this->type = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD) {
            $this->field = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_TYPE) {
            $this->relationType = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_HANDLER) {
            $this->relationHandler = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD_ALIAS) {
            $this->fieldAlias = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__TABLE_ALIAS) {
            $this->tableAlias = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__SOURCE_TABLE) {
            $this->sourceTable = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__REFERENCES) {
            $this->references = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__READONLY) {
            $this->readOnly = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN) {
            $this->joins = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN_LIST) {
            $this->joinList = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__INDEX) {
            $this->index = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__SELECT) {
            $this->select = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT) {
            $this->sort = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT_ALIAS) {
            $this->sortAlias = $value;
        }

        if ($offset === Property::C__PROPERTY__DATA__ENCRYPT) {
            $this->encrypt = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($offset === Property::C__PROPERTY__DATA__TYPE) {
            unset($this->type);
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD) {
            unset($this->field);
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_TYPE) {
            unset($this->relationType);
        }

        if ($offset === Property::C__PROPERTY__DATA__RELATION_HANDLER) {
            unset($this->relationHandler);
        }

        if ($offset === Property::C__PROPERTY__DATA__FIELD_ALIAS) {
            unset($this->fieldAlias);
        }

        if ($offset === Property::C__PROPERTY__DATA__TABLE_ALIAS) {
            unset($this->tableAlias);
        }

        if ($offset === Property::C__PROPERTY__DATA__SOURCE_TABLE) {
            unset($this->sourceTable);
        }

        if ($offset === Property::C__PROPERTY__DATA__REFERENCES) {
            unset($this->references);
        }

        if ($offset === Property::C__PROPERTY__DATA__READONLY) {
            unset($this->readOnly);
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN) {
            unset($this->joins);
        }

        if ($offset === Property::C__PROPERTY__DATA__JOIN_LIST) {
            unset($this->joinList);
        }

        if ($offset === Property::C__PROPERTY__DATA__INDEX) {
            unset($this->index);
        }

        if ($offset === Property::C__PROPERTY__DATA__SELECT) {
            unset($this->select);
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT) {
            unset($this->sort);
        }

        if ($offset === Property::C__PROPERTY__DATA__SORT_ALIAS) {
            unset($this->sortAlias);
        }

        if ($offset === Property::C__PROPERTY__DATA__ENCRYPT) {
            unset($this->encrypt);
        }
    }
}
