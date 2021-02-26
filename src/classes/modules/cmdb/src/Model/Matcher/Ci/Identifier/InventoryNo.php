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
class InventoryNo extends AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = 'inventoryNo';

    /**
     * @inherit
     * @var string
     */
    protected $title = 'LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO';

    /**
     * @inherit
     * @var int
     */
    protected static $bit = 2;

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
     * InventoryNo constructor.
     */
    public function __construct()
    {
        $this->sqlSelect = 'SELECT isys_obj__id AS id, isys_obj__title AS title,  isys_obj__isys_obj_type__id AS type, \'' . self::KEY . '\' AS identKey
            FROM isys_obj
            INNER JOIN isys_catg_accounting_list ON isys_catg_accounting_list__isys_obj__id = isys_obj__id
            WHERE isys_catg_accounting_list__inventory_no = :value:
            AND isys_obj__status = :status: :condition:';

        $this->dataSqlSelect = 'SELECT isys_catg_accounting_list__inventory_no AS \'' . self::KEY . '\' FROM isys_catg_accounting_list
            WHERE isys_catg_accounting_list__isys_obj__id = :objID: AND isys_catg_accounting_list__status = :status:
            AND isys_catg_accounting_list__inventory_no != \'\' AND isys_catg_accounting_list__inventory_no IS NOT NULL
            ';
    }
}