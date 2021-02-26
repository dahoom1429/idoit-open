<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Livestatus;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;

/**
 * i-doit
 *
 * Livestatus Category "Livestatus State" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class LivestatusState implements DynamicCallbackInterface
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
        if (!$data) {
            return '';
        }

        return '<button type="button" class="btn btn-small autostart" data-action="load-livestatus-state" data-object-id="' . $data . '"></button>';
    }
}