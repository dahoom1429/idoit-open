<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\ObjectBrowserInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class ObjectBrowserProperty
 *
 * This class is a simple object browser with no special feature.
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param array  $formatCallback
     * @param string $categoryFilter
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
        $categoryFilter = null,
        $selection = null
    ) {
        parent::__construct();

        $objectTable = 'isys_obj';
        $objectTableId = $objectTable . '__id';
        $objectTableTitle = $objectTable . '__title';

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitle . ', \' {\', ' . $objectTableId . ',\'}\')');

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'object'
        ]);

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
            ->setSelect(
                SelectSubSelect::factory(
                    'SELECT ' . $selection . ' FROM ' . $sourceTable . ' 
                    INNER JOIN ' . $objectTable . ' ON ' . $objectTableId . ' = ' . $dataField,
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
                    $objectTable,
                    'LEFT',
                    $sourceTableObjectId,
                    $objectTableId
                )
            ]);

        $params = [
            'p_strPopupType' => 'browser_object_ng'
        ];

        if ($categoryFilter !== null) {
            $params[\isys_popup_browser_object_ng::C__CAT_FILTER] = $categoryFilter;
        }

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__POPUP)
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
