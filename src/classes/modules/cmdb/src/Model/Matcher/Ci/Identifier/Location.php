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
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Location extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'location';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__GLOBAL_LOCATION';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 2048;

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
     * RoomNumber constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT obj.isys_obj__id AS id, obj.isys_obj__title AS title, obj.isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj AS obj
            INNER JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = obj.isys_obj__id
            INNER JOIN isys_obj AS loc ON loc.isys_obj__id = isys_catg_location_list__parentid
            WHERE (loc.isys_obj__title = :value: OR loc.isys_obj__id = :value:)
            AND obj.isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT loc.isys_obj__title AS \'' . self::KEY . '\' FROM isys_catg_location_list
          INNER JOIN isys_obj AS loc ON isys_obj__id = isys_catg_location_list__parentid
          WHERE isys_catg_location_list__isys_obj__id = :objID: AND isys_catg_location_list__status = :status:';
    }
}