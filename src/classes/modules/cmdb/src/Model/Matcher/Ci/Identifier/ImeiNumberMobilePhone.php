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
 * @version     1.13.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ImeiNumberMobilePhone extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'imeiNumberMobile';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATS__SIM_CARD__IMEI_NUMBER__MATCHER';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 65536;

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
        'CSV'
    ];

    /**
     * ImeiNumber constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title,  isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            INNER JOIN isys_cats_mobile_phone_list ON isys_cats_mobile_phone_list__isys_obj__id = isys_obj__id
            WHERE isys_cats_mobile_phone_list__imei_number = :value:
            AND isys_cats_mobile_phone_list__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT GROUP_CONCAT(isys_cats_mobile_phone_list__imei_number) AS \'' . self::KEY . '\'
            FROM isys_cats_mobile_phone_list 
            WHERE isys_cats_mobile_phone_list__isys_obj__id = :objID: 
            AND isys_cats_mobile_phone_list__status = :status:
            AND isys_cats_mobile_phone_list__imei_number != \'\' 
            AND isys_cats_mobile_phone_list__imei_number IS NOT NULL
            GROUP BY isys_cats_mobile_phone_list__isys_obj__id';
    }
}
