<?php

namespace idoit\Module\Cmdb\Model\Ci\Lists;

use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;
use isys_application;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_property_ng;
use isys_factory;

/**
 * i-doit
 *
 * Ci List Configuration Signals
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.0
 * @since       1.9.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ConfigurationSignals
{
    /**
     * Refreshes all table configs
     *
     * @author Kevin Mauel <kmauel@i-doit.com>
     */
    public function onRefreshTableConfigurations()
    {
        $sql = 'SELECT * FROM isys_obj_type_list WHERE 1;';
        $database = isys_application::instance()->container->database;

        $result = $database->query($sql);

        $dao = new isys_cmdb_dao_category_property_ng($database);

        while ($config = $database->fetch_row_assoc($result)) {
            /**
             * @var $tableConfig Config
             */
            $tableConfig = unserialize($config['isys_obj_type_list__table_config']);

            $propertyIds = [];
            $propertyIdentifiers = [];

            $properties = array_filter($tableConfig->getProperties(), function ($property) use (&$propertyIds, &$propertyIdentifiers, $database) {
                /**
                 * @var $property Property
                 */
                $categoryClass = $property->getClass();

                if (class_exists($categoryClass)) {
                    /**
                     * @var $categoryDao isys_cmdb_dao_category
                     */
                    $categoryDao = isys_factory::get_instance($categoryClass, $database);

                    $categoryId = $categoryDao->get_category_id();
                    $customCategory = false;

                    if (strpos($categoryClass, 'isys_cmdb_dao_category_g_custom_fields') !== false) {
                        $customCategory = true;
                        $categoryId = $property->getCustomCatID();
                    } else {
                        // Fixing a problem where it was not possible to re-edit the person properties in list configuration
                        if (is_value_in_constants($categoryId, ['C__CATS__PERSON'])) {
                            $categoryId = defined_or_default('C__CATS__PERSON_MASTER', 0);
                        }

                        // Fix a problem for person group. See ID-1217
                        if (is_value_in_constants($categoryId, ['C__CATS__PERSON_GROUP_MASTER'])) {
                            $categoryId = defined_or_default('C__CATS__PERSON_GROUP', 0);
                        }

                        // Fix a problem for organization. See ID-2780
                        if (is_value_in_constants($categoryId, ['C__CATS__ORGANIZATION_MASTER_DATA'])) {
                            $categoryId = defined_or_default('C__CATS__ORGANIZATION', 0);
                        }
                    }

                    if ($categoryId > 0) {
                        $categoryType = $customCategory ? C__CMDB__CATEGORY__TYPE_CUSTOM : $categoryDao->get_category_type();

                        switch ($categoryType) {
                            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                                $field = 'isys_property_2_cat__isysgui_catg__id';
                                break;
                            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                                $field = 'isys_property_2_cat__isysgui_cats__id';
                                break;
                            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                                $field = 'isys_property_2_cat__isysgui_catg_custom__id';
                                break;
                            default:
                                $field = 'isys_property_2_cat__isysgui_catg__id';
                        }

                        $propertyIdSql = sprintf('SELECT isys_property_2_cat__id FROM isys_property_2_cat WHERE %s = %s AND %s = \'%s\' LIMIT 1', $field, $categoryId,
                            'isys_property_2_cat__prop_key', $property->getKey());

                        $propertyIdResult = $database->query($propertyIdSql);

                        if ($database->num_rows($propertyIdResult) > 0) {
                            $propertyIds[] = $database->fetch_row_assoc($propertyIdResult)['isys_property_2_cat__id'];
                            $propertyIdentifiers[] = $property->getClass() . '__' . $property->getKey();

                            return true;
                        }
                    }
                }

                return false;
            });

            $tableConfig->setProperties($properties);

            $query = $dao->create_property_query_for_lists($propertyIds, $config['isys_obj_type_list__isys_obj_type__id']);

            $listConfig = json_encode(array_filter(json_decode($config['isys_obj_type_list__config'], true), function ($configElement) use ($propertyIdentifiers) {
                return in_array($configElement[8], $propertyIdentifiers);
            }));

            $database->query(sprintf("UPDATE isys_obj_type_list SET isys_obj_type_list__table_config = '%s', isys_obj_type_list__query = '%s', isys_obj_type_list__config = '%s' WHERE isys_obj_type_list__id = %s",
                $database->escape_string(serialize($tableConfig)), $database->escape_string($query), $database->escape_string($listConfig),
                $config['isys_obj_type_list__id']));
        }
    }
}
