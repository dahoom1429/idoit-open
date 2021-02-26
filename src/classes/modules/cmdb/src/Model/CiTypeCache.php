<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Component\Provider\Singleton;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiTypeCache
{
    use Singleton;

    /**
     * @var CiType[]
     */
    private $ciTypes = [];

    /**
     * @return CiType[]
     */
    public function getCiTypes()
    {
        return $this->ciTypes;
    }

    /**
     * @param $ciTypeId
     *
     * @return CiType
     */
    public function get($ciTypeId)
    {
        return $this->ciTypes[$ciTypeId] ?: null;
    }

    /**
     * CiTypeCache constructor.
     *
     * @param \isys_component_database $database
     */
    public function __construct(\isys_component_database $database)
    {
        $types = \isys_cmdb_dao_object_type::instance($database)
            ->get_object_types()
            ->__as_array();

        foreach ($types as $type) {
            $this->ciTypes[$type['isys_obj_type__id']] = CiType::factory($type['isys_obj_type__id'], $type['isys_obj_type__title'], $type['isys_obj_type__const']);

            $this->ciTypes[$type['isys_obj_type__id']]->assignRawData($type);
        }
    }

}