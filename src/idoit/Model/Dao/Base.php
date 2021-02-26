<?php

namespace idoit\Model\Dao;

/**
 * i-doit Model.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Base extends \isys_component_dao
{

    /**
     * Implode array of selectable columns.
     *
     * @param   array $mapping
     *
     * @return  string
     */
    public function selectImplode(array $mapping)
    {
        if (count($mapping) > 0) {
            array_walk($mapping, function (&$item, $key) {
                $item = $key . ' AS ' . $item;
            });

            return implode(', ', $mapping);
        }

        return '*';
    }
}