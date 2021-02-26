<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\TextInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class PasswordProperty
 *
 * Factory for passwords.
 *
 * @package idoit\Component\Property\Type
 */
class PasswordProperty extends Property implements TextInterface
{
    /**
     * PasswordProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param bool   $maskPassword
     * @param bool   $encrypt
     * @param array  $formatCallback
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField, $sourceTable, $maskPassword = false, $encrypt = true, array $formatCallback = [])
    {
        parent::__construct();

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $formatCallback = $formatCallback ?: [
            'isys_global_password_export_helper',
            'password'
        ];

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__PASSWORD)
            ->setTitle($title)
            ->setPrimaryField(false)
            ->setBackwardCompatible(false);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__TEXT)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setEncrypt($encrypt)
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
            )->setJoins([
                SelectJoin::factory(
                    $sourceTable,
                    'LEFT',
                    $sourceTableObjectId,
                    'isys_obj__id'
                )
            ]);

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__TEXT)
            ->setParams([
                'maskPassword' => $maskPassword
            ])
            ->setDefault(null);

        $this->setPropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => true,
            Property::C__PROPERTY__PROVIDES__EXPORT       => true,
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => false,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => true,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => true,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);

        $this->getFormat()
            ->setCallback($formatCallback);

        $this->getCheck()
            ->setMandatory(false);
    }
}
