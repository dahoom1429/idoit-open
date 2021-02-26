<?php

namespace idoit\Module\Cmdb\Model\Ci\Type;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;

class Date implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param mixed  $extra
     *
     * @return mixed
     */
    public static function render($data, $extra = null)
    {
        if (empty($data)) {
            return $data;
        }

        return isys_application::instance()->container['locales']->fmt_date($data);
    }
}
