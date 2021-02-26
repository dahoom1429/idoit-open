<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DialogInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class DialogPlusMultiselectProperty
 *
 * This factory is for properties where you want a dialog plus with multiselection.
 * It is a replacement for the multiselect pattern
 *
 * - Alias for $sourceTable is 'main'
 * - Alias for $nmTable is 'nm'
 * - Alias for $referenceTable is 'ref'
 *
 * @package idoit\Component\Property\Type
 */
class DialogPlusMultiselectProperty extends Property implements DialogInterface
{
    /**
     * DialogPlusMultiselectProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $nmTable
     * @param string $referenceTable
     * @param string $nmTablePrefix
     * @param array  $formatCallback
     * @param string $selection
     * @param bool   $chosen
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
        $nmTablePrefix = '',
        array $formatCallback = [],
        $selection = null,
        $chosen = true
    ) {
        parent::__construct();

        $nmTablePrefix = ($nmTablePrefix ? $nmTablePrefix . '__' : '');

        $referenceTableId = $referenceTable . '__id';
        $referenceTableDataField = $referenceTable . '__title';
        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $nmTableSourceId = $nmTablePrefix . $sourceTableId;
        $nmTableReferenceId = $nmTablePrefix . $referenceTableId;

        $sourceTableAlias = ($nmTablePrefix ? '' : 'main');
        $nmTableAlias = ($nmTablePrefix ? '': 'nm');
        $referenceTableAlias = ($nmTablePrefix ? '' : 'ref');

        $sourceTableIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableId;
        $sourceTableObjectIdWithAlias = ($sourceTableAlias ? $sourceTableAlias . '.' : '') . $sourceTableObjectId;
        $nmTableSourceIdWithAlias = ($nmTableAlias ? $nmTableAlias . '.' : '') . $nmTableSourceId;
        $nmTableReferenceIdWithAlias = ($nmTableAlias ? $nmTableAlias . '.' : '') . $nmTableReferenceId;
        $referenceTableIdWithAlias = ($referenceTableAlias ? $referenceTableAlias . '.' : '') . $referenceTableId;
        $referenceTableDataFieldWithAlias = ($referenceTableAlias ? $referenceTableAlias . '.' : '') . $referenceTableDataField;

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
        $selection = ($selection !== null ? $selection : $referenceTableDataFieldWithAlias);

        $subSelect = 'SELECT ' . $selection . ' FROM ' . $sourceTable . ($nmTablePrefix ? '' : ' as ' . $sourceTableAlias) . ' ' . $joinString;

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'dialog_multiselect'
        ]);

        $this->getInfo()
            ->setTitle($title)
            ->setType(Property::C__PROPERTY__INFO__TYPE__MULTISELECT)
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
            ->setSelect(SelectSubSelect::factory(
                $subSelect,
                $sourceTable,
                $sourceTableIdWithAlias,
                $sourceTableObjectIdWithAlias,
                '',
                '',
                null,
                SelectGroupBy::factory([$sourceTableIdWithAlias])
            ))
            ->setJoins($joins);

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__POPUP)
            ->setDefault(null)
            ->setParams([
                'type'           => 'f_popup',
                'multiselect'    => true,
                'p_strPopupType' => 'dialog_plus',
                'p_strTable' => $referenceTable,
                'chosen' => $chosen
            ]);

        $this->setPropertyProvides([
            C__PROPERTY__PROVIDES__SEARCH       => true,
            C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            C__PROPERTY__PROVIDES__IMPORT       => true,
            C__PROPERTY__PROVIDES__EXPORT       => true,
            C__PROPERTY__PROVIDES__REPORT       => true,
            C__PROPERTY__PROVIDES__LIST         => true,
            C__PROPERTY__PROVIDES__MULTIEDIT    => true,
            C__PROPERTY__PROVIDES__VALIDATION   => false,
            C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);

        $this->getFormat()
            ->setCallback($formatCallback);

        $this->getCheck()
            ->setValidationType(FILTER_CALLBACK)
            ->setValidationOptions([
                'options' => [
                    'isys_helper',
                    'filter_list_of_ids'
                ]
            ]);
    }
}
