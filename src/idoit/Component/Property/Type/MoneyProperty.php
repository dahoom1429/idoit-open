<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\NumberInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

/**
 * Class MoneyProperty
 *
 * Simple double field which builds the specific selection for the currency.
 * Its a replacement for the money pattern.
 *
 * @package idoit\Component\Property\Type
 */
class MoneyProperty extends Property implements NumberInterface
{
    /**
     * MoneyProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField, $sourceTable)
    {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $selection = 'CONCAT(\'{currency,\', (' . $dataField . ' * 1), \',1}\')';

        $filterCallbackOptions = [
            'options' => [
                'isys_helper',
                'filter_number'
            ]
        ];

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__MONEY)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__DOUBLE)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
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
            )->setJoins([
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
                'p_strPlaceholder' => '0.00',
                'default'          => '0.00'
            ])
            ->setDefault(null);

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

        $this->getFormat()
            ->setCallback([
                'isys_export_helper',
                'money_format'
            ]);

        $this->getCheck()
            ->setMandatory(false)
            ->setValidationType(FILTER_CALLBACK)
            ->setValidationOptions($filterCallbackOptions)
            ->setSanitizationType(FILTER_CALLBACK)
            ->setSanitizationOptions($filterCallbackOptions);
    }
}
