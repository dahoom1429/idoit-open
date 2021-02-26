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

use idoit\Component\Property\Property;
use isys_cmdb_dao_list_objects;
use isys_smarty_plugin_f_dialog;

class LocationPathFilterOperation extends PropertyOperation
{
    const MAX_JOINS = 59;

    /**
     * @param $filter
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($filter, $value)
    {
        $property = $this->getProperty($filter);

        return $property[Property::C__PROPERTY__DATA][Property::C__PROPERTY__DATA__FIELD] === 'isys_catg_location_list__parentid';
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
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value)
    {
        $items = [];

        $query = \isys_cmdb_dao_category_g_location::build_location_path_query(self::MAX_JOINS, 1000, 100, ' WHERE objMain.isys_obj__isys_obj_type__id = ' . $listDao->convert_sql_int($_GET['objTypeID']), true) . ' HAVING title LIKE \'%' . $value . '%\'';

        $query = substr_replace($query, 'SELECT objMain.isys_obj__id,', strpos($query, 'SELECT '), \strlen('SELECT '));

        foreach ($listDao->get_database_component()->retrieveArrayFromResource($listDao->get_database_component()->query($query)) as $row) {
            $items[] = (int) $row['isys_obj__id'];
        }

        if (!empty($items)) {
            $ids = implode(',', $items);
            $listDao->add_additional_conditions(" AND obj_main.isys_obj__id IN ({$ids})");
            return true;
        }

        return false;
    }
}
