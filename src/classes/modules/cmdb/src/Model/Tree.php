<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Model\Dao\Base;
use isys_application as Application;
use isys_auth;
use isys_auth_cmdb_objects;
use isys_cmdb_dao_category_g_logical_unit;
use isys_cmdb_dao_location;
use isys_component_dao_result;
use isys_helper_link;

/**
 * i-doit Tree Model
 *
 * @package     idoit\Module\Cmdb\Model
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.11.1
 */
class Tree extends Base
{
    /**
     * Constant for the tree-mode "physical".
     * @var string
     */
    const MODE_PHSYICAL = 'physical';

    /**
     * Constant for the tree-mode "logical".
     * @var string
     */
    const MODE_LOGICAL = 'logical';

    /**
     * Constant for the tree-mode "combined" (logical + physical).
     * @var string
     */
    const MODE_COMBINED = 'combined';

    /**
     * Retrieve information of a given object.
     *
     * @param  int    $objectId
     * @param  string $mode
     * @param  bool   $onlyContainer
     * @param  int    $levels
     * @param  bool   $considerRights
     *
     * @return array
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    public function getLocationChildren($objectId, $mode = null, $onlyContainer = null, $levels = null, $considerRights = null)
    {
        $language = Application::instance()->container->get('language');

        $mode = $mode ?? self::MODE_COMBINED;
        $onlyContainer = $onlyContainer ?? false;
        $considerRights = $considerRights ?? false;
        $levels = $levels ?? 1;

        // @see  ID-3236 and ID-6808  Instead of simply displaying children of the root-location, display objects that we are allowed to see.
        if ($objectId === C__OBJ__ROOT_LOCATION) {
            $result = $this->prepareRoot($considerRights);
        } else {
            $result = $this->prepareNode($objectId);
        }

        $nextLevel = $levels - 1;
        $children = 0;
        $result['nodeTitle'] = $language->get($result['nodeTitle']);
        $result['nodeTypeTitle'] = $language->get($result['nodeTypeTitle']);
        $result['nodeTypeIcon'] = isys_helper_link::get_base() . $result['nodeTypeIcon'];

        if ($mode === self::MODE_PHSYICAL || $mode === self::MODE_COMBINED) {
            $physicalChildren = $this->getPhysicalChildren($objectId, $onlyContainer, $considerRights);
            $childrenCount = count($physicalChildren);

            if ($levels > 0 && $childrenCount) {
                foreach ($physicalChildren as $child) {
                    $result['children'][$child] = $this->getLocationChildren($child, $mode, $onlyContainer, $nextLevel, $considerRights);
                }
            }

            $children += $childrenCount;
        }

        if ($mode === self::MODE_LOGICAL || $mode === self::MODE_COMBINED) {
            $logicalChildren = $this->getLogicalChildren($objectId, $onlyContainer, $considerRights);
            $childrenCount = count($logicalChildren);

            if ($levels > 0 && $childrenCount) {
                foreach ($logicalChildren as $child) {
                    if (isset($result['children'][$child])) {
                        // Skip objects we already found.
                        continue;
                    }

                    $result['children'][$child] = $this->getLocationChildren($child, $mode, $onlyContainer, $nextLevel, $considerRights);
                }
            }

            $children += $childrenCount;
        }

        $result['children'] = array_values($result['children']);
        $result['hasChildren'] = $children > 0;

        return $result;
    }

    /**
     * Retrieve the root node.
     *
     * @param  bool $considerRights
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function prepareRoot($considerRights)
    {
        if ($considerRights && !\isys_auth_cmdb_objects::instance()->is_allowed_to(isys_auth::VIEW, 'location/' . C__OBJ__ROOT_LOCATION)) {
            // The root location is NOT allowed, we set a placeholder root node.
            return [
                'nodeId'        => 0,
                'nodeTitle'     => 'LC__CMDB__OBJECT_BROWSER__LOCATION_VIEW',
                'nodeTypeId'    => 0,
                'nodeTypeTitle' => '',
                'nodeTypeColor' => '#ffffff',
                'nodeTypeIcon'  => 'images/icons/silk/page_white.png',
                'isContainer'   => true,
                'children'      => [],
            ];
        }

        // The root location is allowed, we set the root node accordinly.
        $return = $this->prepareNode(C__OBJ__ROOT_LOCATION);
        $return['nodeTitle'] = 'LC__OBJ__ROOT_LOCATION';
        return $return;
    }

    /**
     * Retrieve a node.
     *
     * @param  int $objectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function prepareNode($objectId)
    {
        $select = parent::selectImplode([
            'isys_obj__id'             => 'nodeId',
            'isys_obj__title'          => 'nodeTitle',
            'isys_obj_type__id'        => 'nodeTypeId',
            'isys_obj_type__title'     => 'nodeTypeTitle',
            'isys_obj_type__color'     => 'nodeTypeColor',
            'isys_obj_type__icon'      => 'nodeTypeIcon',
            'isys_obj_type__container' => 'isContainer',
        ]);

        $sql = 'SELECT ' . $select . '
            FROM isys_obj 
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id 
            WHERE isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

        $result = $this->retrieve($sql)->get_row();

        $result['nodeTypeColor'] = '#' . ($result['nodeTypeColor'] ?: 'ffffff');
        $result['nodeTypeIcon'] = $result['nodeTypeIcon'] ?: 'images/icons/silk/page_white.png';
        $result['isContainer'] = (bool)$result['isContainer'];
        $result['children'] = [];

        if (strpos($result['nodeTypeIcon'], '/') === false) {
            $result['nodeTypeIcon'] = 'images/tree/' . $result['nodeTypeIcon'];
        }

        return $result;
    }

    /**
     * @param  int  $objectId
     * @param  bool $onlyContainer
     * @param  bool $considerRights
     *
     * @see    ID-3236 and ID-6808  Instead of simply displaying children of the root-location, display objects that we are allowed to see.
     * @return array
     * @throws \isys_exception_database
     */
    private function getPhysicalChildren($objectId, $onlyContainer = null, $considerRights = null)
    {
        $result = [];

        if ($considerRights) {
            if ($objectId == C__OBJ__ROOT_LOCATION) {
                $daoResult = isys_auth_cmdb_objects::instance()->get_allowed_locations();

                if (!($daoResult instanceof isys_component_dao_result)) {
                    return $result;
                }

                while ($row = $daoResult->get_row()) {
                    if ($onlyContainer && !$row['isys_obj_type__container']) {
                        continue;
                    }

                    $result[] = (int)$row['isys_obj__id'];
                }
            } else {
                $daoResult = isys_cmdb_dao_location::instance($this->m_db)
                    ->get_child_locations($objectId, true, $onlyContainer, $considerRights);

                while ($row = $daoResult->get_row()) {
                    $result[] = (int)$row['isys_obj__id'];
                }
            }
        } else {
            $sql = 'SELECT isys_obj__id 
                FROM isys_catg_location_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id 
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id 
                WHERE isys_catg_location_list__parentid = ' . $this->convert_sql_id($objectId) . '
                AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

            if ($onlyContainer) {
                $sql .= ' AND isys_obj_type__container = 1';
            }

            $daoResult = $this->retrieve($sql . ';');

            while ($row = $daoResult->get_row()) {
                $result[] = (int)$row['isys_obj__id'];
            }
        }

        return $result;
    }

    /**
     * @param  int  $objectId
     * @param  bool $onlyContainer
     * @param  bool $considerRights
     *
     * @see    ID-3236 and ID-6808  Instead of simply displaying children of the root-location, display objects that we are allowed to see.
     * @return array
     * @throws \isys_exception_database
     */
    private function getLogicalChildren($objectId, $onlyContainer = null, $considerRights = null)
    {
        $result = [];

        if ($considerRights) {
            $daoResult = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db)->get_data_by_parent($objectId, $considerRights);

            while ($row = $daoResult->get_row()) {
                if ($onlyContainer && !$row['isys_obj_type__container']) {
                    continue;
                }

                $result[] = (int)$row['isys_obj__id'];
            }
        } else {
            $sql = 'SELECT isys_obj__id 
                FROM isys_catg_logical_unit_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_logical_unit_list__isys_obj__id 
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id 
                WHERE isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($objectId) . '
                AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

            if ($onlyContainer) {
                $sql .= ' AND isys_obj_type__container = 1';
            }

            $daoResult = $this->retrieve($sql . ';');

            while ($row = $daoResult->get_row()) {
                $result[] = (int)$row['isys_obj__id'];
            }
        }

        return $result;
    }
}
