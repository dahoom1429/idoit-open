<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Ndo;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;

/**
 * i-doit
 *
 * NDO Category "NDO State" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class NdoState implements DynamicCallbackInterface
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

        return '<button type="button" class="btn btn-small autostart" data-action="load-ndo-state" data-object-id="' . $data . '"></button>';
    }
}