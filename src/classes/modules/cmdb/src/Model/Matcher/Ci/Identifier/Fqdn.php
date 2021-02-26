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
class Fqdn extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'fqdn';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'FQDN';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 64;

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
        'H-Inventory',
        'OCS',
        'CSV'
    ];

    /**
     * Fqdn constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = "SELECT isys_obj__id AS id, isys_obj__title AS title, isys_obj__isys_obj_type__id AS type, '" . self::KEY . "' AS identKey
            FROM isys_obj
            INNER JOIN (
                SELECT CONCAT_WS('.', isys_catg_ip_list__hostname, isys_catg_ip_list__domain) AS fqdn, isys_catg_ip_list__isys_obj__id AS id
                FROM isys_catg_ip_list
                WHERE isys_catg_ip_list__hostname != '') AS fqdnjoin ON fqdnjoin.id = `isys_obj__id`
            WHERE fqdnjoin.fqdn = :value:
            AND isys_obj__status = :status: :condition:

            UNION

            SELECT isys_obj__id AS id, isys_obj__title AS title, isys_obj__isys_obj_type__id AS type, '" . self::KEY . "' AS identKey
            FROM isys_obj
            INNER JOIN (
                SELECT CONCAT_WS('.', isys_hostaddress_pairs__hostname, isys_hostaddress_pairs__domain) AS fqdn, isys_catg_ip_list__isys_obj__id AS id
                FROM isys_catg_ip_list
                INNER JOIN isys_hostaddress_pairs ON isys_hostaddress_pairs__isys_catg_ip_list__id = isys_catg_ip_list__id
                WHERE isys_catg_ip_list__hostname != '') AS fqdnjoin ON fqdnjoin.id = `isys_obj__id`
            WHERE fqdnjoin.fqdn = :value:
            AND isys_obj__status = :status: :condition:";

        $this->dataSqlSelect = "SELECT CONCAT_WS('.', isys_catg_ip_list__hostname, isys_catg_ip_list__domain) AS '" . self::KEY . "'
            FROM isys_catg_ip_list
            WHERE isys_catg_ip_list__isys_obj__id = :objID: AND isys_catg_ip_list__status = :status:
            AND isys_catg_ip_list__hostname != '' AND isys_catg_ip_list__hostname IS NOT NULL AND isys_catg_ip_list__primary = 1

            UNION

            SELECT CONCAT_WS('.', isys_hostaddress_pairs__hostname, isys_hostaddress_pairs__domain) AS '" . self::KEY . "'
            FROM isys_catg_ip_list
            INNER JOIN isys_hostaddress_pairs ON isys_catg_ip_list__id = isys_hostaddress_pairs__isys_catg_ip_list__id
            WHERE isys_catg_ip_list__isys_obj__id = :objID: AND isys_catg_ip_list__status = :status:
            AND isys_hostaddress_pairs__hostname != '' AND isys_hostaddress_pairs__hostname IS NOT NULL AND isys_catg_ip_list__primary = 1";

    }
}