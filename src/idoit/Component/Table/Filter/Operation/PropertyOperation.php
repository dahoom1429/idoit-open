<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use isys_cmdb_dao_category;
use isys_cmdb_dao_list_objects;

/**
 * Operation, that uses property
 *
 * @package idoit\Component\Table\Filter\Operation
 */
abstract class PropertyOperation extends Operation
{
    protected function getProperty($name)
    {
        global $g_comp_database;
        try {
            list($class, $prop) = explode('__', $name);
            if (class_exists($class) && is_subclass_of($class, 'isys_cmdb_dao_category')) {
                $dao = new $class($g_comp_database);
                if (!$dao instanceof isys_cmdb_dao_category) {
                    return false;
                }

                if ($dao instanceof \isys_cmdb_dao_category_g_custom_fields) {
                    $customIdData = $dao->get_database_component()->retrieveArrayFromResource(
                        $dao->get_database_component()->query(
                            '
                            SELECT isys_catg_custom_fields_list__isysgui_catg_custom__id,
                                   CONCAT(isys_catg_custom_fields_list__field_type, \'_\', isys_catg_custom_fields_list__field_key) as combined_field 
                            FROM isys_catg_custom_fields_list
                            HAVING combined_field = \''.$prop.'\'
                            LIMIT 1;'
                        )
                    );

                    if (empty($customIdData[0]['isys_catg_custom_fields_list__isysgui_catg_custom__id'])) {
                        return false;
                    }

                    $dao->set_catg_custom_id((int) $customIdData[0]['isys_catg_custom_fields_list__isysgui_catg_custom__id']);
                }

                return $dao->get_property_by_key($prop);
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    protected function applyFormatted(isys_cmdb_dao_list_objects $listDao, $name, $value)
    {
        $property = $this->getProperty($name);

        if (!$property) {
            return false;
        }

        return $this->applyProperty($listDao, $property, $name, $value);
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     *
     * @return mixed
     */
    abstract protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value);
}
