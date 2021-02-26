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
class RoomNumber extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'roomNumber';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATS__ROOM_NUMBER';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 512;

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
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title, isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            INNER JOIN isys_cats_room_list ON isys_cats_room_list__isys_obj__id = isys_obj__id
            WHERE isys_cats_room_list__number = :value:
            AND isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT isys_cats_room_list__number AS \'' . self::KEY . '\' FROM isys_cats_room_list
            WHERE isys_cats_room_list__isys_obj__id = :objID: AND isys_cats_room_list__status = :status:
            AND isys_cats_room_list__number != \'\' AND isys_cats_room_list__number IS NOT NULL';
    }
}