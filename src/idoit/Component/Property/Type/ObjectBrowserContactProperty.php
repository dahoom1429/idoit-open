<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\TypeInterfaces\ObjectBrowserInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;

/**
 * Class ObjectBrowserContactProperty
 *
 * This is an object browser factory for contacts.
 * Its a replacement for all object browsers which is used for contacts.
 *
 * @package idoit\Component\Property\Type
 */
class ObjectBrowserContactProperty extends Property implements ObjectBrowserInterface
{
    /**
     * ObjectBrowserContactProperty constructor.
     *
     * @param string         $uiId
     * @param string         $title
     * @param string         $dataField
     * @param string         $sourceTable
     * @param \isys_callback $uiValueCallback
     * @param array          $formatCallbackArr
     * @param null           $categoryFilter
     * @param string         $selection
     *
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function __construct(
        $uiId,
        $title,
        $dataField,
        $sourceTable,
        $uiValueCallback,
        array $formatCallbackArr = [],
        $categoryFilter = null,
        $selection = null
    ) {
        parent::__construct();

        $objectTable = 'isys_obj';
        $objectTableId = $objectTable . '__id';
        $objectTableTitle = $objectTable . '__title';

        $contact2Object = 'isys_contact_2_isys_obj';
        $contact2ObjectObjectId = $contact2Object . '__isys_obj__id';
        $contact2ObjectContactId = $contact2Object . '__isys_contact__id';

        $sourceTableId = $sourceTable . '__id';
        $sourceTableObjectId = $sourceTable . '__isys_obj__id';

        $selection = ($selection !== null ? $selection : 'CONCAT(' . $objectTableTitle . ', \' {\', ' . $objectTableId . ',\'}\')');

        $formatCallbackArr = (!empty($formatCallbackArr) ? $formatCallbackArr : [
            'isys_export_helper',
            'contact'
        ]);

        $joins = [
            SelectJoin::factory(
                $sourceTable,
                'LEFT',
                $sourceTableObjectId,
                'isys_obj__id'
            ),
            SelectJoin::factory(
                $contact2Object,
                'LEFT',
                $dataField,
                $contact2ObjectContactId
            ),
            SelectJoin::factory(
                $objectTable,
                'LEFT',
                $contact2ObjectObjectId,
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
            ->setBackwardCompatible(true);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__INT)
            ->setSourceTable($sourceTable)
            ->setReadOnly(false)
            ->setIndex(false)
            ->setReferences([
                'isys_contact',
                'isys_contact__id'
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
                    SelectGroupBy::factory([$sourceTableObjectId]),
                    $sourceTableObjectId
                )
            )
            ->setJoins($joins);

        $params = [
            'p_strPopupType'                                 => 'browser_object_ng',
            'p_bReadonly'                                    => 1,
            'p_strValue'                                     => $uiValueCallback,
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
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => true,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => true,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => false,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => true
        ]);

        $this->getFormat()
            ->setCallback($formatCallbackArr);
    }
}
