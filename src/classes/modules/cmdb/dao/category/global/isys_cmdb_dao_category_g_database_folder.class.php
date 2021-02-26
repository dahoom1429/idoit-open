<?php

/**
 * i-doit
 *
 * Folder Category dao for "Database"
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.13
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.13
 */
class isys_cmdb_dao_category_g_database_folder extends isys_cmdb_dao_category_g_virtual
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_folder';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__DATABASE_FOLDER';

    /**
     * Marks the category as "filled" as soon as at least one rack is inside.
     *
     * @param int $objId
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_count($objId = null)
    {
        return isys_cmdb_dao_category_g_database::instance(isys_application::instance()->container->get('database'))
            ->get_count($objId);
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function hasSchemas($id)
    {
        $query = 'SELECT 1 FROM isys_catg_database_sa_list_2_isys_database_schema WHERE isys_catg_database_sa_list__id = ' . $this->convert_sql_id($id) . ' LIMIT 1';
        $result = $this->retrieve($query);
        if ($result instanceof isys_component_dao_result && count($result) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Get all DBMS Nodes
     *
     * @param $nodeId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getRootNode($nodeId)
    {
        $objectTypesResult = $this->get_objtype_by_cats_id(defined_or_default('C__CATS__DBMS'));
        $return = [];

        if ($objectTypesResult instanceof isys_component_dao_result && count($objectTypesResult)) {
            $objTypes = [];

            while ($row = $objectTypesResult->get_row()) {
                $objTypes[] = $row['isys_obj_type__id'];
            }

            $currentObject = $this->get_object($nodeId)->get_row();

            $query = 'SELECT *, (
                        SELECT COUNT(*) FROM isys_catg_database_sa_list WHERE
                        isys_catg_database_sa_list__isys_catg_application_list__id = isys_catg_application_list__id AND
                        (isys_catg_database_sa_list__isys_catg_database_list__id = isys_catg_database_list__id OR isys_catg_database_sa_list__isys_catg_database_list__id IS NULL)
                      ) as countDatabases 
                    FROM isys_catg_application_list
                      INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                      INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
                      INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                      LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_obj__id
                      LEFT JOIN isys_application_manufacturer ON isys_application_manufacturer__id = isys_cats_application_list__isys_application_manufacturer__id
                      LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
                      LEFT JOIN isys_catg_database_list ON isys_catg_database_list__isys_catg_application_list__id = isys_catg_application_list__id
                      LEFT JOIN isys_database_instance_type ON isys_database_instance_type__id = isys_catg_database_list__isys_database_instance_type__id
                    WHERE isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_id($nodeId) . ' AND isys_obj__isys_obj_type__id IN (' . implode(',', $objTypes) . ');';

            $objectTypeInfo = $this->get_object_by_id($nodeId)->get_row();

            $return = [
                'nodeId' => 'root-' . $nodeId,
                'nodeTitle' => $this->obj_get_title_by_id_as_string($nodeId),
                'hasChildren' => false,
                'icon' => isys_application::instance()->www_path .'/' .
                    (file_exists($objectTypeInfo['isys_obj_type__icon']) ? $objectTypeInfo['isys_obj_type__icon'] : 'images/tree/' . $objectTypeInfo['isys_obj_type__icon']),
                'children' => []
            ];

            $result = $this->retrieve($query);
            if ($result instanceof isys_component_dao_result && count($result)) {
                $return['hasChildren'] = true;
                $language = isys_application::instance()->container->get('language');
                $linkTitle = $language->get('LC__CATG__DATABASE_FOLDER__JUMP_TO_OBJECT');

                while ($child = $result->get_row()) {
                    $link = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $child['isys_obj__id']]);

                    $return['children'][] = [
                        'nodeId' => 'app-' . $child['isys_catg_database_list__id'],
                        'nodeTitle' => $child['isys_obj__title'] . ($child['isys_catg_version_list__title'] ? ' ' . $child['isys_catg_version_list__title']: ''),
                        'hasChildren' => (bool)$child['countDatabases'],
                        'icon' => isys_application::instance()->www_path .'/' .
                            (file_exists($child['isys_obj_type__icon']) ? $child['isys_obj_type__icon'] : 'images/tree/' . $child['isys_obj_type__icon']),
                        'info' => [
                            'title' => $child['isys_obj__title'],
                            'link' => $link,
                            'type' => $language->get($child['isys_obj_type__title']),
                            'manufacturer' => $language->get($child['isys_application_manufacturer__title']),
                            'countDatabases' => $child['countDatabases'],
                            'instanceName' => $child['isys_catg_database_list__instance_name'],
                            'instanceType' => $child['isys_database_instance_type__title'],
                            'path' => $child['isys_catg_database_list__path'],
                            'port' => $child['isys_catg_database_list__port'],
                            'portName' => $child['isys_catg_database_list__port_name'],
                            'version' => $child['isys_catg_version_list__title'],
                            'entryLink' => $link,
                            'entryLinkTitle' => $linkTitle
                        ],
                        'children' => []
                    ];
                }
            }
        }
        return $return;
    }

    /**
     * Get Database nodes
     *
     * @param $nodeId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getDatabaseNodes($nodeId)
    {
        $query = 'SELECT *, 
            (SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = main.isys_catg_database_sa_list__size_unit) tableSizeUnit,  
            (SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = main.isys_catg_database_sa_list__max_size_unit) maxSizeUnit,
            (
                SELECT COUNT(*) FROM isys_catg_database_table_list 
                WHERE isys_catg_database_table_list__isys_catg_database_sa_list__id = main.isys_catg_database_sa_list__id LIMIT 1
            ) as tableCount,
            (
                SELECT COUNT(*) FROM isys_catg_database_sa_list_2_isys_database_schema nm
                WHERE nm.isys_catg_database_sa_list__id = main.isys_catg_database_sa_list__id LIMIT 1
            ) as schemaCount
        FROM isys_catg_database_sa_list main
        LEFT JOIN isys_catg_database_list ON isys_catg_database_list__id = isys_catg_database_sa_list__isys_catg_database_list__id
        WHERE main.isys_catg_database_sa_list__isys_catg_application_list__id = (
                  SELECT isys_catg_database_list__isys_catg_application_list__id FROM isys_catg_database_list 
                  WHERE isys_catg_database_list__id = ' . $this->convert_sql_id($nodeId) .
            ') AND (
                main.isys_catg_database_sa_list__isys_catg_database_list__id = ' . $this->convert_sql_id($nodeId) . ' OR
                main.isys_catg_database_sa_list__isys_catg_database_list__id IS NULL
             )';

        $return = [];
        $result = $this->retrieve($query);

        if ($result instanceof isys_component_dao_result && count($result)) {
            $return = ['children' => []];
            $currentObject = null;
            $linkTitle = isys_application::instance()->container->get('language')->get('LC__CATG__DATABASE_FOLDER__JUMP_TO_DATABASE');

            while ($row = $result->get_row()) {
                if ($currentObject === null) {
                    $currentObject = $this->get_object($row['isys_catg_database_sa_list__isys_obj__id'])->get_row();
                }

                $return['children'][] = [
                    'nodeId' => 'db-'.$row['isys_catg_database_sa_list__id'],
                    'nodeTitle' => $row['isys_catg_database_sa_list__title'],
                    'hasChildren' => (int)$row['tableCount'],
                    'icon' => isys_application::instance()->www_path . '/images/icons/silk/database_gear.png',
                    'info' => [
                        'title' => $row['isys_catg_database_sa_list__title'],
                        'instanceName' => $row['isys_catg_database_list__instance_name'],
                        'tableCount' => $row['tableCount'],
                        'schemaCount' => $row['schemaCount'],
                        'size' => isys_convert::memory(
                                $row['isys_catg_database_sa_list__size'],
                                $row['isys_catg_database_sa_list__size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['tableSizeUnit'],
                        'maxSize' => isys_convert::memory(
                                $row['isys_catg_database_sa_list__max_size'],
                                $row['isys_catg_database_sa_list__max_size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['maxSizeUnit'],
                        'entryLink' => isys_helper_link::create_url([
                            C__CMDB__GET__OBJECT => $currentObject['isys_obj__id'],
                            C__CMDB__GET__TREEMODE => defined_or_default('C__CMDB__VIEW__TREE_OBJECT'),
                            C__CMDB__GET__VIEWMODE => defined_or_default('C__CMDB__VIEW__CATEGORY_GLOBAL'),
                            C__CMDB__GET__OBJECTTYPE => $currentObject['isys_obj_type__id'],
                            C__CMDB__GET__CATG => defined_or_default('C__CATG__DATABASE_SA'),
                            C__CMDB__GET__CATLEVEL => $row['isys_catg_database_sa_list__id']
                        ]),
                        'entryLinkTitle' => $linkTitle
                    ],
                    'children' => []
                ];
            }
        }

        return $return;
    }

    /**
     * Get Database schema nodes from database
     *
     * @param $nodeId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getDatabaseSchemaNodes($nodeId)
    {
        $query = 'SELECT *, 
            (
                SELECT count(*) FROM isys_catg_database_table_list
                WHERE isys_catg_database_table_list__isys_catg_database_sa_list__id = main.isys_catg_database_sa_list__id
                AND isys_catg_database_table_list__isys_database_schema__id = main.isys_database_schema__id
            ) as tableCount 
            FROM isys_catg_database_sa_list_2_isys_database_schema main
            INNER JOIN isys_catg_database_sa_list subref ON subref.isys_catg_database_sa_list__id = main.isys_catg_database_sa_list__id
            INNER JOIN isys_database_schema ref ON ref.isys_database_schema__id = main.isys_database_schema__id 
            LEFT JOIN isys_catg_database_list refdb ON refdb.isys_catg_database_list__id = subref.isys_catg_database_sa_list__isys_catg_database_list__id
            WHERE subref.isys_catg_database_sa_list__id = ' . $this->convert_sql_id($nodeId);

        $result = $this->retrieve($query);
        $return = ['children' => []];
        if ($result instanceof isys_component_dao_result && count($result)) {
            $return = ['children' => []];
            $currentObject = null;
            $linkTitle = isys_application::instance()->container->get('language')->get('LC__CATG__DATABASE_FOLDER__JUMP_TO_DATABASE');

            while ($row = $result->get_row()) {
                if ($currentObject === null) {
                    $currentObject = $this->get_object($row['isys_catg_database_sa_list__isys_obj__id'])->get_row();
                }
                $return['children'][] = [
                    'nodeId' => 'schema-'.$row['isys_catg_database_sa_list__id']. '-' . $row['isys_database_schema__id'],
                    'nodeTitle' => $row['isys_database_schema__title'],
                    'hasChildren' => (int)$row['tableCount'],
                    'icon' => isys_application::instance()->www_path . '/images/icons/silk/database_table.png',
                    'info' => [
                        'database' => $row['isys_catg_database_sa_list__title'],
                        'title' => $row['isys_database_schema__title'],
                        'tableCount' => $row['tableCount'],
                        'size' => isys_convert::memory(
                                $row['isys_catg_database_sa_list__size'],
                                $row['isys_catg_database_sa_list__size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['tableSizeUnit'],
                        'maxSize' => isys_convert::memory(
                                $row['isys_catg_database_sa_list__max_size'],
                                $row['isys_catg_database_sa_list__max_size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['maxSizeUnit'],
                        'entryLink' => isys_helper_link::create_url([
                            C__CMDB__GET__OBJECT => $currentObject['isys_obj__id'],
                            C__CMDB__GET__TREEMODE => defined_or_default('C__CMDB__VIEW__TREE_OBJECT'),
                            C__CMDB__GET__VIEWMODE => defined_or_default('C__CMDB__VIEW__CATEGORY_GLOBAL'),
                            C__CMDB__GET__OBJECTTYPE => $currentObject['isys_obj_type__id'],
                            C__CMDB__GET__CATG => defined_or_default('C__CATG__DATABASE_SA'),
                            C__CMDB__GET__CATLEVEL => $row['isys_catg_database_sa_list__id']
                        ]),
                        'entryLinkTitle' => $linkTitle
                    ],
                    'children' => []
                ];
            }
        }
        return $return;
    }

    /**
     * Get all Database schema nodes
     *
     * @param $nodeId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getDatabaseSchemaTableNodes($nodeId)
    {
        if ($this->hasSchemas($nodeId)) {
            return $this->getDatabaseSchemaNodes($nodeId);
        }

        return $this->getDatabaseTableNodesByDatabaseId($nodeId);
    }

    /**
     * Get all Database Table nodes
     *
     * @param int $nodeId
     * @param int|null $schemaId
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getDatabaseTableNodesByDatabaseId($nodeId, $schemaId = null)
    {
        $query = 'SELECT *, 
            (SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = isys_catg_database_table_list__size_unit) tableSizeUnit,  
            (SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = isys_catg_database_table_list__schema_size_unit) schemaSizeUnit,
            (SELECT isys_memory_unit__title FROM isys_memory_unit WHERE isys_memory_unit__id = isys_catg_database_table_list__max_size_unit) maxSizeUnit
        FROM isys_catg_database_table_list
          INNER JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__id = isys_catg_database_table_list__isys_catg_database_sa_list__id
          LEFT JOIN isys_database_schema ON isys_database_schema__id = isys_catg_database_table_list__isys_database_schema__id
        WHERE isys_catg_database_table_list__isys_catg_database_sa_list__id = ' . $this->convert_sql_id($nodeId);

        if ($schemaId !== null) {
            $query .= ' AND isys_catg_database_table_list__isys_database_schema__id = ' . $this->convert_sql_id($schemaId);
        }

        $return = [];
        $result = $this->retrieve($query);

        if ($result instanceof isys_component_dao_result && count($result)) {
            $return = ['children' => []];
            $currentObject = null;
            $linkTitle = isys_application::instance()->container->get('language')->get('LC__CATG__DATABASE_FOLDER__JUMP_TO_TABLE');

            while ($row = $result->get_row()) {
                if ($currentObject === null) {
                    $currentObject = $this->get_object($row['isys_catg_database_table_list__isys_obj__id'])->get_row();
                }

                $return['children'][] = [
                    'nodeId' => 'db_table-'.$row['isys_catg_database_table_list__id'],
                    'nodeTitle' => $row['isys_catg_database_table_list__title'],
                    'hasChildren' => false,
                    'icon' => isys_application::instance()->www_path . '/images/icons/silk/table.png',
                    'info' => [
                        'database' => $row['isys_catg_database_sa_list__title'],
                        'schema' => $row['isys_database_schema__title'],
                        'title' => $row['isys_catg_database_table_list__title'],
                        'rowCount' => $row['isys_catg_database_table_list__row_count'],
                        'size' => isys_convert::memory(
                                $row['isys_catg_database_table_list__size'],
                                $row['isys_catg_database_table_list__size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['tableSizeUnit'],
                        'schemaSize' => isys_convert::memory(
                                $row['isys_catg_database_table_list__schema_size'],
                                $row['isys_catg_database_table_list__schema_size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['schemaSizeUnit'],
                        'maxSize' => isys_convert::memory(
                                $row['isys_catg_database_table_list__max_size'],
                                $row['isys_catg_database_table_list__max_size_unit'],
                                C__CONVERT_DIRECTION__BACKWARD
                            ) . ' ' . $row['maxSizeUnit'],
                        'entryLink' => isys_helper_link::create_url([
                            C__CMDB__GET__OBJECT => $currentObject['isys_obj__id'],
                            C__CMDB__GET__TREEMODE => defined_or_default('C__CMDB__VIEW__TREE_OBJECT'),
                            C__CMDB__GET__VIEWMODE => defined_or_default('C__CMDB__VIEW__CATEGORY_GLOBAL'),
                            C__CMDB__GET__OBJECTTYPE => $currentObject['isys_obj_type__id'],
                            C__CMDB__GET__CATG => defined_or_default('C__CATG__DATABASE_TABLE'),
                            C__CMDB__GET__CATLEVEL => $row['isys_catg_database_table_list__id']
                        ]),
                        'entryLinkTitle' => $linkTitle
                    ],
                    'children' => []
                ];
            }
        }
        return $return;
    }

    /**
     * Gets database hierarchy
     *
     * @param string $nodeId
     */
    public function getNodeHierarchy($nodeId)
    {
        list($nodeType, $id, $tableId) = explode('-', $nodeId);

        switch ($nodeType) {
            case 'app':
                $return = $this->getDatabaseNodes($id);
                break;
            case 'db':
                $return = $this->getDatabaseSchemaTableNodes($id);
                break;
            case 'schema':
                $return = $this->getDatabaseTableNodesByDatabaseId($id, $tableId);
                break;
            case 'root':
            default:
                $return = $this->getRootNode($id);
                break;
        }

        return $return;
    }
}
