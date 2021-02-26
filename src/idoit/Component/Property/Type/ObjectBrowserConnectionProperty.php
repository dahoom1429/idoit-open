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
 * This is the object browser factory which referenced table is "isys_connection".
 * Its a replacement for all object browser patterns which referenced table is the isys_connection.
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserConnectionProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserConnectionProperty constructor.
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

        $connectionTable = 'isys_connection';
        $connectionTableId = $connectionTable . '__id';
        $connectionTableObjectId = $connectionTable . '__isys_obj__id';

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitle . ', \' {\', ' . $objectTableId . ',\'}\')');

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'connection'
        ]);

        $joins = [
            SelectJoin::factory(
                $sourceTable,
                'LEFT',
                $sourceTableObjectId,
                'isys_obj__id'
            ),
            SelectJoin::factory(
                $connectionTable,
                'LEFT',
                $dataField,
                $connectionTableId
            ),
            SelectJoin::factory(
                $objectTable,
                'LEFT',
                $connectionTableObjectId,
                $objectTableId
            )
        ];

        /**
         * @var SelectJoin $join
         */
        $joinString = '';
        foreach ($joins as $key => $join) {
            if ($key === 0) {
                continue;
            }

            $joinString .= ' ' . $join->setJoinType('INNER')->__toString();
        }

        $query = 'SELECT ' . $selection . ' FROM ' . $sourceTable . $joinString;

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
            ->setReferences([
                'isys_connection',
                'isys_connection__id'
            ])
            ->setSelect(
                SelectSubSelect::factory(
                    $query,
                    $sourceTable,
                    $sourceTableId,
                    $sourceTableObjectId,
                    '',
                    '',
                    null,
                    SelectGroupBy::factory([$sourceTableObjectId])
                )
            )
            ->setJoins($joins);

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
