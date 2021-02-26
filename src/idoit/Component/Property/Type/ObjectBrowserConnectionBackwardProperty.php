<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\ObjectBrowserInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class ObjectBrowserConnectionBackwardProperty
 *
 * This is for categories with mostly only on property which is the backward category of another category.
 * For example like the category group memberships or cluster memberships.
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserConnectionBackwardProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserConnectionBackwardProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $sourceTableConnectionField
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
        $sourceTableConnectionField = '',
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
        $sourceTableConnectionFieldId = $sourceTableConnectionField ?: $sourceTable . '__isys_connection__id';

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitle . ', \' {\', ' . $objectTableId . ',\'}\')');

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'object'
        ]);

        $joins = [
            SelectJoin::factory(
                $connectionTable,
                'LEFT',
                $connectionTableObjectId,
                'isys_obj__id'
            ),
            SelectJoin::factory(
                $sourceTable,
                'LEFT',
                $connectionTableId,
                $sourceTableConnectionFieldId
            ),
            SelectJoin::factory(
                $objectTable,
                'LEFT',
                $sourceTableObjectId,
                $objectTableId
            )
        ];

        $joinString = ' INNER JOIN ' . $objectTable . ' ON ' . $objectTableId . ' = ' . $sourceTableObjectId . ' 
            INNER JOIN ' . $connectionTable . ' ON ' . $connectionTableId . ' = ' . $sourceTableConnectionFieldId;


        $query = 'SELECT ' . $selection . ' FROM ' . $sourceTable . $joinString;

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(true);

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
                    $connectionTable,
                    $connectionTableId,
                    $connectionTableObjectId,
                    '',
                    '',
                    null,
                    SelectGroupBy::factory([$connectionTableObjectId])
                )
            )
            ->setJoins($joins);

        $params = [
            'p_strPopupType' => 'browser_object_ng',
            \isys_popup_browser_object_ng::C__MULTISELECTION => true
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
