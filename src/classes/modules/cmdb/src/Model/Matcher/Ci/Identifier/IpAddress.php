<?php

namespace idoit\Module\Cmdb\Model\Matcher\Identifier;

use idoit\Module\Cmdb\Model\Matcher\AbstractIdentifier;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class IpAddress extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'ipAddress';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CATP__IP__ADDRESS';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 1;

    /**
     * @inherit
     * @var string
     */
    protected $sqlSelect = '';

    /**
     * @inherit
     * @var string
     */
    protected $dataSqlSelect = '';

    /**
     * Usage options for Match Identifier
     *
     * @var array
     */
    protected $usableIn = [
        'JDisc',
        'CSV'
    ];

    /**
     * IpAddress constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title,  isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
            WHERE (isys_cats_net_ip_addresses_list__title = :value: OR isys_cats_net_ip_addresses_list__ip_address_long = :value:)
            AND isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT isys_cats_net_ip_addresses_list__title AS \'' . self::KEY . '\' FROM isys_catg_ip_list
            INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
            WHERE isys_catg_ip_list__isys_obj__id = :objID: AND isys_catg_ip_list__status = :status:
            AND isys_cats_net_ip_addresses_list__title != \'\' AND isys_cats_net_ip_addresses_list__title IS NOT NULL AND isys_catg_ip_list__primary = 1';
    }
}