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
class ImeiNumberTelephoneFax extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'imeiNumberTelephone';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__TELEPHONE_FAX__IMEI__MATCHER';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 262144;

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
            INNER JOIN isys_catg_telephone_fax_list ON isys_catg_telephone_fax_list__isys_obj__id = isys_obj__id
            WHERE isys_catg_telephone_fax_list__imei = :value:
            AND isys_catg_telephone_fax_list__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT GROUP_CONCAT(isys_catg_telephone_fax_list__imei) AS \'' . self::KEY . '\'
            FROM isys_catg_telephone_fax_list 
            WHERE isys_catg_telephone_fax_list__isys_obj__id = :objID: 
            AND isys_catg_telephone_fax_list__status = :status:
            AND isys_catg_telephone_fax_list__imei != \'\' 
            AND isys_catg_telephone_fax_list__imei IS NOT NULL
            GROUP BY isys_catg_telephone_fax_list__isys_obj__id';
    }
}
