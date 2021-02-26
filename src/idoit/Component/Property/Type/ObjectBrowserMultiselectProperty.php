<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\ObjectBrowserInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class ObjectBrowserMultiselectProperty
 *
 * This factory is for object browsers with an active multiselection via n2m Table.
 * If a referenced table is defined then the output comes from the referenced table instead of 'isys_obj'.
 *
 * There are 2 combinations for this factory:
 * Example 1:
 * - $sourceTable = 'isys_catg_wan_list'
 * - $nmTable = 'isys_catg_wan_list_2_router'
 *
 * Example 2:
 * - $sourceTable = 'isys_catg_cluster_service_list'
 * - $nmTable = 'isys_catg_drive_list_2_isys_catg_cluster_service_list',
 * - $referenceTable = 'isys_catg_drive_list'
 *
 * - Alias for $sourceTable is 'main'
 * - Alias for $nmTable is 'nm'
 * - Alias for $referenceTable is 'ref'
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserMultiselectProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserMultiselectProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $nmTable
     * @param string $nmTablePrefix
     * @param string $referenceTable
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
        $nmTable,
        $nmTablePrefix = null,
        $referenceTable = null,
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
        $sourceTableTitle = $sourceTable . '__title';

        $nmTablePrefix = ($nmTablePrefix ? $nmTablePrefix . '__': '');

        $nmTableSourceId = $nmTablePrefix . $sourceTableId;
        $nmTableReferenceId = $nmTablePrefix . $objectTableId;

        $sourceTableAlias = ($nmTablePrefix ? '' : 'main');
        $nmTableAlias = ($nmTablePrefix ? '' : 'nm');
        $objectTableAlias = ($nmTablePrefix ? '' : 'obj');

        $sourceTableIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableId;
        $sourceTableObjectIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableObjectId;
        $objectTableIdWithAlias = ($objectTableAlias ? $objectTableAlias . '.' : '') . $objectTableId;
        $objectTableTitleWithAlias = ($objectTableAlias ? $objectTableAlias . '.' : '') . $objectTableTitle;

        $groupBy = SelectGroupBy::factory([$sourceTableObjectIdWithAlias]);

        $joins = [
            SelectJoin::factory(
                $sourceTable,
                'LEFT',
                $sourceTableObjectId,
                'isys_obj__id',
                $sourceTableAlias,
                '',
                $sourceTableAlias
            ),
            SelectJoin::factory(
                $nmTable,
                'LEFT',
                $sourceTableId,
                $nmTableSourceId,
                $sourceTableAlias,
                $nmTableAlias,
                $nmTableAlias
            ),
            SelectJoin::factory(
                $objectTable,
                'LEFT',
                $nmTableReferenceId,
                $objectTableId,
                $nmTableAlias,
                $objectTableAlias,
                $objectTableAlias
            )
        ];

        if ($referenceTable !== null) {
            $referenceTableId = $referenceTable . '__id';
            $referenceTableObjectId = $referenceTable . '__isys_obj__id';
            $referenceTableTitle = $referenceTable . '__title';
            $referenceTableAlias = ($nmTablePrefix ? '' : 'ref');
            $nmTableReferenceId = $nmTablePrefix . $referenceTableId;

            $groupBy = SelectGroupBy::factory([$sourceTableIdWithAlias]);

            $joins = [
                SelectJoin::factory(
                    $sourceTable,
                    'LEFT',
                    $sourceTableObjectId,
                    'isys_obj__id',
                    $sourceTableAlias,
                    '',
                    $sourceTableAlias
                ),
                SelectJoin::factory(
                    $nmTable,
                    'LEFT',
                    $sourceTableId,
                    $nmTableSourceId,
                    $sourceTableAlias,
                    $nmTableAlias,
                    $nmTableAlias
                ),
                SelectJoin::factory(
                    $referenceTable,
                    'LEFT',
                    $nmTableReferenceId,
                    $referenceTableId,
                    $nmTableAlias,
                    $referenceTableAlias,
                    $referenceTableAlias
                ),
                SelectJoin::factory(
                    $objectTable,
                    'LEFT',
                    $referenceTableObjectId,
                    $objectTableId,
                    $referenceTableAlias,
                    $objectTableAlias,
                    $objectTableAlias
                )
            ];
        }

        /**
         * @var $join SelectJoin
         */
        $joinString = '';
        foreach ($joins as $key => $join) {
            if ($key === 0) {
                continue;
            }

            $joinString .= ' ' . $join->setJoinType('INNER')->__toString();
        }
        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitleWithAlias . ', \' {\', ' . $objectTableIdWithAlias . ',\'}\')');

        $subSelect = 'SELECT ' . $selection . ' FROM ' . $sourceTable . ($nmTablePrefix ? '' : ' as ' . $sourceTableAlias) . ' ' . $joinString;

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
            ->setSourceTable($referenceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setReferences([
                $nmTable,
                $nmTableSourceId
            ])
            ->setSelect(
                SelectSubSelect::factory(
                    $subSelect,
                    $sourceTable,
                    $sourceTableIdWithAlias,
                    $sourceTableObjectIdWithAlias,
                    '',
                    '',
                    null,
                    $groupBy
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
