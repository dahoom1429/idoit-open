<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DialogInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class DialogDataCaseProperty
 *
 * This factory is used if a value of the dialog field has a specific interpretation.
 * The data is retrieved from the parameter $uiArData. $uiArData can be an instance of isys_callback which returns an array or
 * an array which contains the interpretation of each possible the value.
 *
 * @package idoit\Component\Property\Type
 */
class DialogDataCaseProperty extends Property implements DialogInterface
{
    /**
     * DialogProperty constructor.
     *
     * @param string               $uiId
     * @param string               $title
     * @param string               $dataField
     * @param string               $sourceTable
     * @param array|\isys_callback $uiArData        if isys_callback the return value has to be an array
     * @param bool                 $chosen
     * @param array                $formatCallback
     * @param string               $selection
     *
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function __construct(
        $uiId,
        $title,
        $dataField,
        $sourceTable,
        $uiArData = [],
        $chosen = false,
        array $formatCallback = [],
        $selection = null
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $uiArDataForSelection = $uiArData;
        if ($uiArDataForSelection instanceof \isys_callback) {
            $uiArDataForSelection = $uiArDataForSelection->execute();
        }

        if (is_array($uiArDataForSelection) && $selection === null) {
            $selection = '(CASE ' . $dataField . ' ' . implode(' ', array_map(function ($item, $key) {
                if (is_array($item)) {
                    if (isset($item['id'])) {
                        return "WHEN '" . $key . "' THEN '" . $item['id'] . "' ";
                    }
                    if (isset($item['value'])) {
                        return "WHEN '" . $key . "' THEN '" . $item['value'] . "' ";
                    }

                    return "WHEN '" . $key . "' THEN '" . \isys_tenantsettings::get('gui.empty_value', '-') . "' ";
                }

                return "WHEN '" . $key . "' THEN '" . $item . "' ";
            }, $uiArDataForSelection, array_keys($uiArDataForSelection))) . " ELSE '" . \isys_tenantsettings::get('gui.empty_value', '-') . "' END)";
        }

        $selection = $selection ?: $dataField;

        $formatCallback = (!empty($formatCallback) ? $formatCallback : [
            'isys_export_helper',
            'dialog'
        ]);

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__DIALOG)
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
            ->setType(Property::C__PROPERTY__UI__TYPE__DIALOG)
            ->setDefault('-1')
            ->setParams([
                'p_arData' => $uiArData,
                'p_bDbFieldNN' => true,
                'chosen' => $chosen
            ]);

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
