<?php declare(strict_types=1);

namespace idoit\Module\Multiedit\Component\Synchronizer\Converter;

abstract class AbstractDefaultConverter
{
    private $converterType = null;

    /**
     * @return null
     */
    public function getConverterType()
    {
        return $this->converterType;
    }
}
