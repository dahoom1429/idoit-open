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
class ObjectTitle extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'objectTitle';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__UNIVERSAL__OBJECT_TITLE';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 32;

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
     * ObjectTitle constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title,  isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            WHERE isys_obj__title = :value:
            AND isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT isys_obj__title AS \'' . self::KEY . '\' FROM isys_obj
            WHERE isys_obj__id = :objID: AND isys_obj__status = :status:';
    }
}