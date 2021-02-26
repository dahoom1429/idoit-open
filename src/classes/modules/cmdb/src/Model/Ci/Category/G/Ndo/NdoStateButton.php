<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Ndo;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;

/**
 * i-doit
 *
 * NDO Category "NDO State Button" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class NdoStateButton implements DynamicCallbackInterface
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

        return '<button type="button" class="btn btn-small" data-action="load-ndo-state" data-object-id="' . $data . '">' .
            '<img src="' . isys_application::instance()->www_path . 'images/icons/silk/arrow_right.png" class="mr5" />' .
            '<span>' . isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__LOAD') . '</span>' . '</button>';
    }
}
