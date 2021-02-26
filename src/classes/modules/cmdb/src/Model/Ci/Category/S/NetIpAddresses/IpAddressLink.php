<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\S\NetIpAddresses;

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
class IpAddressLink implements DynamicCallbackInterface
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

        preg_match('~(.*?) {(\d+)}~', $data, $matches);

        return '<a href="' . \isys_helper_link::create_url([
                C__CMDB__GET__OBJECT => $matches[2],
                C__CMDB__GET__CATS   => defined_or_default('C__CATS__NET_IP_ADDRESSES')

            ]) . '">' . $matches[1] . '</a>';
    }
}