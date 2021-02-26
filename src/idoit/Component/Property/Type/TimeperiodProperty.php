<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\TimeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

/**
 * Class TimeperiodProperty
 *
 * Factory for a timeperiod property
 *
 * @package idoit\Component\Property\Type
 */
class TimeperiodProperty extends Property implements TimeInterface
{
    /**
     * TimeperiodProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param array  $formatCallback
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField, $sourceTable, array $formatCallback = [])
    {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $formatCallback = $formatCallback ?:[];

        $params = [
            'p_strClass'       => 'input-mini',
            'p_strPlaceholder' => 'hh:mm'
        ];

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__TIMEPERIOD)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__TEXT)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setSelect(
                SelectSubSelect::factory(
                    'SELECT ' . $dataField . ' FROM ' . $sourceTable,
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
                )
            ]);

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXT)
            ->setDefault('hh:mm')
            ->setParams($params);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => false,
            Property::C__PROPERTY__PROVIDES__SEARCH       => true,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => true,
            Property::C__PROPERTY__PROVIDES__EXPORT       => true,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => true,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => false,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);

        $this->getFormat()
            ->setCallback($formatCallback);

        $this->getCheck()
            ->setMandatory(false);
    }
}
