<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\DynamicProperty as DynProperty;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

/**
 * Class DynamicProperty
 *
 * Factory for a dynamic property
 *
 * @package idoit\Component\Property\Type
 */
class DynamicProperty extends DynProperty
{
    /**
     * DynamicProperty constructor.
     *
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param array  $formatCallback
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($title = '', $dataField = '', $sourceTable = '', $formatCallback = [])
    {
        parent::__construct();

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__TEXT)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setType(C__TYPE__TEXT)
            ->setReadOnly(false)
            ->setField($dataField)
            ->setIndex(false);

        if ($sourceTable) {
            $sourceTableId = $sourceTable . '__id';
            $sourceTableObjectId = $sourceTable . '__isys_obj__id';

            $this->getData()
                ->setSourceTable($sourceTable)
                ->setJoins([
                    SelectJoin::factory(
                        $sourceTable,
                        'LEFT',
                        $sourceTableObjectId,
                        'isys_obj__id'
                    )
                ]);
        }
        if (!empty($formatCallback)) {
            $this->getFormat()
                ->setCallback($formatCallback);
        }

        $this->getUi()
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXT)
            ->setDefault(null);

        $this->setPropertyProvides(
            [
                Property::C__PROPERTY__PROVIDES__LIST       => false,
                Property::C__PROPERTY__PROVIDES__REPORT     => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                Property::C__PROPERTY__PROVIDES__IMPORT     => false,
                Property::C__PROPERTY__PROVIDES__EXPORT     => false,
                Property::C__PROPERTY__PROVIDES__SEARCH     => false,
                Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                Property::C__PROPERTY__PROVIDES__FILTERABLE => false,
                Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                Property::C__PROPERTY__PROVIDES__VIRTUAL    => false
            ]
        );
    }
}
