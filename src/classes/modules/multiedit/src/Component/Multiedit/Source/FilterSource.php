<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Source;

use idoit\Component\Property\Configuration\PropertyData;
use idoit\Component\Property\Property;
use idoit\Exception\Exception;

/**
 * Class FilterSource
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Source
 */
class FilterSource extends Source
{

    /**
     * @var Property[]
     */
    protected $data;

    /**
     * @return $this|mixed
     */
    public function formatData()
    {
        if (empty($this->getData())) {
            return $this;
        }

        return $this;
    }
}
