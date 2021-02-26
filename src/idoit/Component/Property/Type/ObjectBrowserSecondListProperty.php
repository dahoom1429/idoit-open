<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\ObjectBrowserInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class ObjectBrowserSecondListProperty
 *
 * This is a Object Browser factory with a second list.
 * Its a replacement for all object browser patterns with a second list.
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserSecondListProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserSecondListProperty constructor.
     *
     * @param string            $uiId
     * @param string            $title
     * @param string            $dataField
     * @param string            $sourceTable
     * @param string            $referenceTable
     * @param string|array      $secondList
     * @param string|array      $secondListFormat
     * @param array             $formatCallback
     * @param string            $categoryFilter
     * @param string            $selection
     * @param string            $secondListSelection
     * @param SelectCondition   $selectCondition
     *
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function __construct(
        $uiId,
        $title,
        $dataField,
        $sourceTable,
        $referenceTable,
        $secondList,
        $secondListFormat = null,
        array $formatCallback = [],
        $categoryFilter = null,
        $selection = null,
        $secondListSelection = null,
        $selectCondition = null
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $sourceTableTitle = $sourceTable . '__title';
        $referenceTableId = $referenceTable . '__id';
        $referenceTableTitle = $referenceTable . '__title';
        $referenceTableObjectId = $referenceTable . '__isys_obj__id';
        $objectTable = 'isys_obj';
        $objectTableId = $objectTable . '__id';
        $objectTableTitle = $objectTable . '__title';

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitle . ',  \' {\', ' . $objectTableId . ', \'}\')');

        $dataReferences = [
            $referenceTable,
            $referenceTableId
        ];

        if ($secondListSelection !== null) {
            $dataReferences[] = $secondListSelection;
        }

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__INT)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setReferences($dataReferences)
            ->setSelect(
                SelectSubSelect::factory(
                    'SELECT ' . $selection . ' FROM ' . $sourceTable . '  
                    INNER JOIN ' . $referenceTable . ' ON ' . $referenceTableId . ' = ' . $dataField . ' 
                    INNER JOIN ' . $objectTable . ' ON ' . $objectTableId . ' = ' . $referenceTableObjectId,
                    $sourceTable,
                    $sourceTableId,
                    $sourceTableObjectId,
                    $dataField,
                    '',
                    $selectCondition,
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
                    $referenceTable,
                    'LEFT',
                    $dataField,
                    $referenceTableId
                ),
                SelectJoin::factory(
                    $objectTable,
                    'LEFT',
                    $referenceTableObjectId,
                    $objectTableId
                )
            ]);

        $params = [
            'p_strPopupType' => 'browser_object_ng',
            \isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
            \isys_popup_browser_object_ng::C__SECOND_LIST => $secondList
        ];

        if ($secondListFormat !== null) {
            $params[\isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT] = $secondListFormat;
        }

        if ($categoryFilter !== null) {
            $params[\isys_popup_browser_object_ng::C__CAT_FILTER] = $categoryFilter;
        }

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__POPUP)
            ->setDefault('')
            ->setParams($params);

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
            ->setCallback($formatCallback);
    }
}
