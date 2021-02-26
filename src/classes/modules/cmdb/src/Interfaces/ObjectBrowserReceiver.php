<?php

namespace idoit\Module\Cmdb\Interfaces;

/**
 * i-doit cmdb interfaces
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ObjectBrowserReceiver
{
    /**
     * Attach $objects to $object_id
     *
     * @param int   $object_id
     * @param array $objects
     *
     * @return int last inserted id
     */
    public function attachObjects($object_id, array $objects);

}