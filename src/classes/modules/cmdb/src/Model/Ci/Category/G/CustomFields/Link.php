<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\CustomFields;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_helper_link;

/**
 * i-doit
 *
 * Custom fields Category for Property type Link callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.9.4
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Link implements DynamicCallbackInterface
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
        global $g_dirs;

        if (!$data) {
            return '';
        }

        $link = strip_tags_deep($data);

        return isys_helper_link::create_anker($link, '_blank', '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" alt="Link" class="vam" /> <span class="vam">',
            '</span>');
    }
}