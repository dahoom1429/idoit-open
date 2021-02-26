<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\NumberInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class FloatWithFactorProperty
 *
 * Its a float field which has a relation to a unit table which also has a factor in the dialog table.
 * Its a replacement for the float pattern which has a dependency to a unit field with a factor property.
 *
 * @package idoit\Component\Property\Type
 */
class FloatWithFactorProperty extends Property implements NumberInterface
{
    /**
     * FloatWithFactorProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $unitTable
     * @param string $formatUnitKey
     * @param string $convertMethod
     * @param string $referenceField
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
        $unitTable,
        $formatUnitKey,
        $convertMethod,
        $referenceField = null,
        $selection = null
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $unitTableId = $unitTable . '__id';
        $unitTableFactorField = $unitTable . '__factor';
        $unitTableTitle = $unitTable . '__title';
        $referenceField = $referenceField !== null ? $referenceField : $sourceTable . '__' . $unitTableId;
        $selection = ($selection !== null ? $selection :
            'CONCAT(ROUND(' . $dataField . ' / ' . $unitTableFactorField . '), \' \', ' . $unitTableTitle . ')');

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
            ->setSelect(
                SelectSubSelect::factory(
                    'SELECT ' . $selection . '  
                    FROM ' . $sourceTable . ' 
                    INNER JOIN ' . $unitTable . ' ON ' . $unitTableId . ' = ' . $referenceField,
                    $sourceTable,
                    $sourceTableId,
                    $sourceTableObjectId,
                    '',
                    '',
                    null,
                    SelectGroupBy::factory([$sourceTableObjectId])
                )
            )
            ->setJoins([
                SelectJoin::factory(
                    $sourceTable,
                    'LEFT',
                    $sourceTableObjectId,
                    'isys_obj__id'
                ),
                SelectJoin::factory(
                    $unitTable,
                    'LEFT',
                    $referenceField,
                    $unitTableId
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

        $this->getFormat()
            ->setUnit($formatUnitKey)
            ->setCallback([
                'isys_export_helper',
                'convert',
                [$convertMethod]
            ]);
    }
}
