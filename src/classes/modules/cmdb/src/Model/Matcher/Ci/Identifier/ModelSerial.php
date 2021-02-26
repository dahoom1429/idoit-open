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
class ModelSerial extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'modelSerial';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__MODEL_SERIAL';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 16;

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
     * ModelSerial constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title,  isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            INNER JOIN isys_catg_model_list ON isys_catg_model_list__isys_obj__id = isys_obj__id
            WHERE isys_catg_model_list__serial = :value:
            AND isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT isys_catg_model_list__serial AS \'' . self::KEY . '\'
            FROM isys_catg_model_list WHERE isys_catg_model_list__isys_obj__id = :objID: AND isys_catg_model_list__status = :status:
            AND isys_catg_model_list__serial != \'\' AND isys_catg_model_list__serial IS NOT NULL';
    }
}