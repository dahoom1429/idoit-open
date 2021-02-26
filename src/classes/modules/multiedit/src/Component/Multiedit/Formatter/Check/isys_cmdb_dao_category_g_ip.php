<?php
namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Check;

use idoit\Component\Property\Property;
use idoit\Component\Helper\Ip;

/**
 * Class isys_cmdb_dao_category_g_ip
 *
 * Check for IPv4/IPv6 assignment, address
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Check
 */
class isys_cmdb_dao_category_g_ip implements CheckInterface
{
    private static $checkArr = [
        'ipv4_assignment',
        'ipv4_address',
        'ipv6_assignment',
        'ipv6_address'
    ];

    /**
     * @param string $propertyKey
     * @param array $data
     */
    public static function check($propertyKey, $data)
    {
        if (!in_array($propertyKey, self::$checkArr)) {
            return false;
        }

        if (Ip::validate_ip($data['isys_cmdb_dao_category_g_ip__ipv4_address']->getViewValue())) {
            if (in_array($propertyKey, ['ipv6_assignment', 'ipv6_address'])) {
                return true;
            }
            return false;
        }

        if (Ip::validate_ipv6($data['isys_cmdb_dao_category_g_ip__ipv6_address']->getViewValue())) {
            if (in_array($propertyKey, ['ipv4_assignment', 'ipv4_address'])) {
                return true;
            }
            return false;
        }
    }
}
