<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DialogInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class DialogPlusProperty
 *
 * Simple dialog plus factory which replaces the dialog_plus pattern
 *
 * @package idoit\Component\Property\Type
 */
class DialogPlusProperty extends Property implements DialogInterface
{
    /**
     * DialogPlusProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param string $referenceTable
     * @param bool   $chosen
     * @param bool   $multiselect
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
        $referenceTable,
        $chosen = false,
        $multiselect = false,
        array $formatCallback = [],
        $selection = null
    ) {
        parent::__construct();

        $referenceTableId = $referenceTable . '__id';
        $referenceTableDataField = $referenceTable . '__title';
        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';
        $selection = ($selection !== null ? $selection : $referenceTableDataField);

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'dialog_plus'
        ]);

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__DIALOG_PLUS)
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
                $referenceTable,
                $referenceTableId
            ])
            ->setSelect(SelectSubSelect::factory(
                'SELECT ' . $selection . ' FROM ' . $sourceTable . ' 
                INNER JOIN ' . $referenceTable . ' ON ' . $referenceTableId . ' = ' . $dataField,
                $sourceTable,
                $sourceTableId,
                $sourceTableObjectId,
                '',
                '',
                null,
                SelectGroupBy::factory([$sourceTableObjectId])
            ))
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
                )
            ]);

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__POPUP)
            ->setDefault('-1')
            ->setParams([
                'p_strPopupType' => 'dialog_plus',
                'p_strTable' => $referenceTable,
                'chosen' => $chosen,
                'multiselect' => $multiselect
            ]);

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
    }
}
