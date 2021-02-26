<?php
namespace idoit\Module\Multiedit\Component\Synchronizer\Converter\G\Sla;

use idoit\Module\Multiedit\Component\Synchronizer\Converter\ConvertInterface;

class Days implements ConvertInterface
{

    /**
     * @param string $value
     *
     * @return string
     */
    public function convertValue($value)
    {
        return decbin(array_sum(explode(',', $value)));
    }
}
