<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DateInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * Class DateProperty
 * This factory replaces the date and datetime property.
 *
 * @package idoit\Component\Property\Type
 */
class DateProperty extends Property implements DateInterface
{
    /**
     * DateProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param bool   $withTime
     * @param bool   $readOnly
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField, $sourceTable, $withTime = false, $readOnly = false)
    {
        parent::__construct();

        $locales = \isys_application::instance()->container->get('locales');
        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $params = [
            'p_strPopupType' => 'calendar',
            'p_bTime'        => $withTime,
            'p_bReadonly'    => $readOnly
        ];

        $formatCallback = ['isys_export_helper', 'date'];
        $infoType = Property::C__PROPERTY__INFO__TYPE__DATE;
        $dataType = C__TYPE__DATE;
        $uiType = Property::C__PROPERTY__UI__TYPE__DATE;
        $dateFormat = $locales->get_date_format(true, false);
        $selection = 'DATE_FORMAT(' . $dataField . ', \'' . $dateFormat . '\')';

        if ($withTime) {
            $formatCallback = ['isys_export_helper', 'datetime'];
            $infoType = Property::C__PROPERTY__INFO__TYPE__DATETIME;
            $dataType = C__TYPE__DATE_TIME;
            $uiType = Property::C__PROPERTY__UI__TYPE__DATETIME;
            $timeFormat = $locales->get_time_format(false, false);
            $selection = 'DATE_FORMAT(' . $dataField . ', \'' . $dateFormat . ' - ' . $timeFormat . '\')';
        }

        $this->getInfo()
            ->setType($infoType)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType($dataType)
            ->setSourceTable($sourceTable)
            ->setReadOnly($readOnly)
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
            ->setType($uiType)
            ->setParams($params);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => true,
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
            ->setCallback($formatCallback);

        $this->getCheck()
            ->setMandatory(false)
            ->setValidationType(FILTER_CALLBACK)
            ->setValidationOptions([
                'options' => [
                    'isys_helper',
                    'filter_date'
                ]
            ]);
    }
}
