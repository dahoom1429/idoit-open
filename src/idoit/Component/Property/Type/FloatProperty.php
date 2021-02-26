<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\NumberInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class FloatProperty
 *
 * Its a simple field with float attributes.
 * Its a replacement for the float pattern.
 *
 * @package idoit\Component\Property\Type
 */
class FloatProperty extends Property implements NumberInterface
{
    /**
     * FloatProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param array  $formatCallback
     *
     * @param string $selection
     *
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function __construct(
        $uiId,
        $title,
        $dataField,
        $sourceTable,
        array $formatCallback = [],
        $selection = null
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $selection = ($selection !== null ? $selection : $dataField);

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__FLOAT)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__FLOAT)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setSelect(SelectSubSelect::factory(
                'SELECT ' . $selection . ' FROM ' . $sourceTable,
                $sourceTable,
                $sourceTableId,
                $sourceTableObjectId,
                '',
                '',
                null,
                SelectGroupBy::factory([$sourceTableObjectId])
            ))
            ->setJoins([
                SelectJoin::factory(
                    $sourceTable,
                    'LEFT',
                    $sourceTableObjectId,
                    'isys_obj__id'
                )
            ]);


        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXT)
            ->setParams([
                'p_strClass' => 'input-medium',
                'p_strPlaceholder' => '0.00'
            ]);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => true,
            Property::C__PROPERTY__PROVIDES__EXPORT       => true,
            Property::C__PROPERTY__PROVIDES__REPORT       => true,
            Property::C__PROPERTY__PROVIDES__LIST         => true,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => true,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => false,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);

        $this->getCheck()
            ->setMandatory(false)
            ->setValidationType(FILTER_VALIDATE_FLOAT)
            ->setValidationType([])
            ->setSanitizationType(FILTER_CALLBACK)
            ->setSanitizationOptions([
                'options' => [
                    'isys_helper',
                    'filter_number'
                ]
            ]);

        $this->getFormat()->setCallback($formatCallback);
    }
}
