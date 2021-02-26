<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DialogInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class DialogListCategoryDataProperty
 *
 * This is only a dialog with multiselection which data comes from another category.
 * Its a replacement for the dialog_list pattern which data comes from another category.
 *
 * Used aliases:
 * - Alias for $sourceTable is 'main'
 * - Alias for $nmTable is 'nm'
 * - Alias for $referenceTable is 'ref'
 *
 * @package idoit\Component\Property\Type
 */
class DialogListCategoryDataProperty extends Property implements DialogInterface
{
    /**
     * DialogListCategoryDataProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $nmTable
     * @param string $referenceTable
     * @param string $nmTablePrefix
     * @param string $referenceField
     * @param array|\isys_callback  $uiArData
     * @param array  $formatCallback
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
        $referenceTable,
        $nmTablePrefix = null,
        $referenceField = null,
        $uiArData = [],
        array $formatCallback = [],
        $selection = null
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $sourceTableTitle = $sourceTable . '__title';
        $referenceField = $referenceField ?: $sourceTableId;
        $referenceTableId = $referenceTable . '__id';
        $referenceTableTitle = $referenceTable . '__title';
        $nmTableSourceId = ($nmTablePrefix ? $nmTablePrefix . '__' . $referenceField: $referenceField);
        $nmTableReferenceId = ($nmTablePrefix ? $nmTablePrefix . '__' . $referenceTableId: $referenceTableId);

        $sourceTableAlias = ($nmTablePrefix ? '' : 'main');
        $nmTableAlias = ($nmTablePrefix ? '' : 'nm');
        $referenceTableAlias = ($nmTablePrefix ? '' : 'ref');

        $sourceTableIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableId;
        $sourceTableObjectIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableObjectId;
        $sourceTableTitleWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableTitle;
        $referenceTableIdWithAlias = ($referenceTableAlias ? $referenceTableAlias . '.' : '') . $referenceTableId;
        $referenceTableTitleWithAlias = ($referenceTableAlias ? $referenceTableAlias . '.' : '') . $referenceTableTitle;
        $nmTableSourceIdWithAlias = ($nmTableAlias ? $nmTableAlias . '.' : '') . $nmTableSourceId;
        $nmTableReferenceIdWithAlias = ($nmTableAlias ? $nmTableAlias . '.' : '') . $nmTableReferenceId;

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'dialog_multiselect'
        ]);

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $sourceTableTitleWithAlias . ', \' > \', ' . $referenceTableTitleWithAlias . ')');

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
            )
        ];

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

        $query = 'SELECT ' . $selection . ' FROM ' . $sourceTable . ($sourceTableAlias ? ' as ' . $sourceTableAlias : '') . $joinString;

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__INT)
            ->setSourceTable($referenceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setSelect(
                SelectSubSelect::factory(
                    $query,
                    $sourceTable,
                    $sourceTableIdWithAlias,
                    $sourceTableObjectIdWithAlias,
                    '',
                    '',
                    null,
                    SelectGroupBy::factory([$sourceTableObjectIdWithAlias])
                )
            )
            ->setJoins($joins);

        $params = [
            'p_bDbFieldNN' => false
        ];

        if (!empty($uiArData)) {
            $params['p_arData'] = $uiArData;
        }

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__DIALOG_LIST)
            ->setDefault('')
            ->setParams($params);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => true,
            Property::C__PROPERTY__PROVIDES__EXPORT       => true,
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
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
