<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

/**
 * Class VirtualProperty
 *
 * Factory for a virtual property
 *
 * @package idoit\Component\Property\Type
 */
class VirtualProperty extends Property
{
    /**
     * VirtualProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     *
     * @param string $selection
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField = '', $sourceTable = '', $selection = '')
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

            if ($dataField) {
                $this->getData()
                    ->setField($dataField);
            }

            $selection = $selection ?: $dataField;

            if ($selection) {
                $this->getData()
                    ->setSelect(
                        SelectSubSelect::factory(
                        'SELECT ' . $selection . ' FROM ' . $sourceTable,
                        $sourceTable,
                        $sourceTableId,
                        $sourceTableObjectId,
                        '',
                        '',
                        null,
                        SelectGroupBy::factory([$sourceTableObjectId])
                    )
                    );
            }
        }

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXT)
            ->setDefault(null);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => false,
            Property::C__PROPERTY__PROVIDES__EXPORT       => false,
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => false,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => false,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => true,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => true,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);
    }
}
