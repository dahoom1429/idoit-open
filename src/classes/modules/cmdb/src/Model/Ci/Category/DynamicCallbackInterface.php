<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

/**
 * i-doit
 *
 * Category Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param mixed  $extra
     *
     * @return mixed
     */
    public static function render($data, $extra = null);
}