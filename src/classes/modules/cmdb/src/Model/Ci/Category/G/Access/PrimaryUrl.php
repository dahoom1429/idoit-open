<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Access;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_helper;

/**
 * i-doit
 *
 * Access Category property "primary_url" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang<qhoang@i-doit.com>
 * @version     1.11.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PrimaryUrl implements DynamicCallbackInterface
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
        if ($data === null) {
            return '';
        }

        return isys_helper::covertUrlsToHtmlLinks($data);
    }
}
