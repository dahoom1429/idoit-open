<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\DialogInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class DialogYesNoProperty
 *
 * Its a simple yes/no dialog factory.
 * Its a replacement for the dialog pattern which data is an array of yes and no.
 *
 * @package idoit\Component\Property\Type
 */
class DialogYesNoProperty extends Property implements DialogInterface
{
    /**
     * DialogProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param int    $default       0 = No, 1 = Yes
     *
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function __construct(
        $uiId,
        $title,
        $dataField,
        $sourceTable,
        $default = 0
    ) {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $selection = ' (CASE WHEN ' . $dataField . ' = \'1\' THEN 
            \'LC__UNIVERSAL__YES\' WHEN ' . $dataField . ' = \'0\' THEN \'LC__UNIVERSAL__NO\' END)';

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
            ->setDefault($default)
            ->setParams([
                'p_arData' => get_smarty_arr_YES_NO(),
                'p_bDbFieldNN' => true
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
            ->setCallback([
                'isys_export_helper',
                'get_yes_or_no'
            ]);
    }
}
