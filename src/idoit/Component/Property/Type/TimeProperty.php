<?php

namespace idoit\Component\Property\Type;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;

/**
 * Class TimeProperty
 *
 * Factory for a simple time property
 *
 * @package idoit\Component\Property\Type
 */
class TimeProperty extends TextProperty
{
    /**
     * TextProperty constructor.
     *
     * @param string $uiId
     * @param string $title
     * @param string $dataField
     * @param string $sourceTable
     * @param int    $status
     * @param array  $formatCallback
     *
     * @throws UnsupportedConfigurationTypeException
     */
    public function __construct($uiId, $title, $dataField, $sourceTable, $status = C__RECORD_STATUS__NORMAL, $formatCallback = [])
    {
        parent::__construct();

        $this->getInfo()
            ->setType(Property::C__PROPERTY__INFO__TYPE__TIME);

        $this->getData()
            ->setField($dataField)
            ->setType(C__TYPE__TIME);

        $this->getUi()
            ->setId($uiId)
            ->setType(Property::C__PROPERTY__UI__TYPE__TIME)
            ->setParams([
                'p_nMaxLen' => 5
            ]);
    }
}
