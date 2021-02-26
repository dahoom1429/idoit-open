<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\TextInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * Class CommentaryProperty
 * This factory replaces the commentary pattern.
 *
 * @package idoit\Component\Property\Type
 */
class CommentaryProperty extends Property implements TextInterface
{
    /**
     * CommentaryProperty constructor.
     *
     * @param string $uiId
     * @param string $dataField
     * @param string $sourceTable
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $dataField, $sourceTable)
    {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__COMMENTARY)
            ->setTitle('LC__CMDB__CATG__DESCRIPTION')
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__TEXT_AREA)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setSelect(
                SelectSubSelect::factory(
                    'SELECT ' . $dataField . ' FROM ' . $sourceTable,
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
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXTAREA);

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

        $this->getCheck()
            ->setMandatory(false)
            ->setValidationType(FILTER_CALLBACK)
            ->setValidationOptions([
                'options' => [
                    'isys_helper',
                    'filter_textarea'
                ]
            ]);
    }
}
