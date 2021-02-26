<?php

namespace idoit\Model;

/**
 * i-doit Breadcrumb Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class Model
{

    /**
     * Assign raw data array into class properties
     *
     * $row should be something like [
     *   'isys_obj_type__id' => 1,
     *   'isys_obj_type__title' => 'Title'
     * ];
     *
     * @param array $row
     */
    public function assignRawData(array $row)
    {
        foreach ($this->columnMap() as $classProp => $dbValue) {
            if (isset($row[$dbValue])) {
                $this->$classProp = $row[$dbValue];
            }
        }
    }

    /**
     * Return current model as array, according to a column map, if provided.
     *
     * @return array
     */
    public function toArray($columnMap = [])
    {
        $return = [];
        foreach (get_object_vars($this) as $key => $value) {

            if (isset($columnMap[$key])) {
                $return[$columnMap[$key]] = $value;
            } else {
                $return[$key] = $value;
            }

        }

        return $return;
    }

    /**
     * Defines a column map for raw data associations
     *
     * Exmaple: [
     * 'id' => 'isys_obj_type__id',
     * 'title' => 'isys_obj_type__title'
     * ];
     *
     * @return array
     */
    public function columnMap()
    {
        return [];
    }

}
