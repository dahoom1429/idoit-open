<?php

namespace idoit\Module\Cmdb\Component\Table\Config;

use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;
use isys_cmdb_dao as cmdbDao;
use isys_cmdb_dao_category;
use isys_component_database as databaseComponent;
use isys_tenantsettings as tenantSettings;

/**
 * Class Refresher
 *
 * @package   idoit\Console\Command\Cleanup
 * @copyright synetics GmbH
 * @package idoit\Module\Cmdb\Component\Table\Config
 */
class Refresher
{
    /**
     * @var databaseComponent
     */
    private $database;

    /**
     * @var cmdbDao
     */
    private $dao;

    /**
     * TableConfigRefresher constructor.
     *
     * @param databaseComponent $database
     */
    public function __construct(databaseComponent $database)
    {
        $this->database = $database;
        $this->dao = cmdbDao::instance($database);
    }

    /**
     * Process method for refreshing all available object table configurations.
     *
     * @return void
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    public function processAll()
    {
        // First we'll have to fetch all defined object type lists.
        $result = $this->dao->retrieve('SELECT isys_obj_type_list__id AS id, isys_obj_type_list__table_config AS configuration FROM isys_obj_type_list;');

        while ($row = $result->get_row()) {
            $this->processUserConfiguration((int)$row['id'], (string)$row['configuration']);
        }

        // Second we'll fetch all defined 'standard' configurations.
        $result = $this->dao->retrieve('SELECT isys_obj_type__const AS constant FROM isys_obj_type;');

        while ($row = $result->get_row()) {
            // @see  ID-7463  It can happen that object types have no constant.
            if (empty($row['constant'])) {
                continue;
            }

            $this->processDefaultConfiguration($row['constant']);
        }
    }

    /**
     * @param string $objectTypeConstant
     *
     * @return void
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     * @throws \isys_exception_dao
     */
    public function processByObjectTypeConstant(string $objectTypeConstant)
    {
        // First we'll have to fetch all defined object type lists.
        $sql = 'SELECT isys_obj_type_list__id AS id, isys_obj_type_list__table_config AS configuration 
            FROM isys_obj_type_list
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_list__isys_obj_type__id
            WHERE isys_obj_type__const = ' . $this->dao->convert_sql_text($objectTypeConstant) . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $this->processUserConfiguration((int)$row['id'], (string)$row['configuration']);
        }

        $this->processDefaultConfiguration($objectTypeConstant);
    }

    /**
     * @param string $rawConfiguration
     *
     * @return bool|Config
     */
    private function checkConfiguration(string $rawConfiguration)
    {
        if (empty($rawConfiguration)) {
            return false;
        }

        $configuration = unserialize($rawConfiguration, ['allowed_classes' => [Config::class, Property::class]]);

        if (!$configuration || !is_a($configuration, Config::class)) {
            return false;
        }

        return $configuration;
    }

    /**
     * Process user configuration which are stored in the database.
     *
     * @param int    $id
     * @param string $rawConfiguration
     *
     * @return void
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    private function processUserConfiguration(int $id, string $rawConfiguration)
    {
        // We'll need to check if a configuration exists:
        $configuration = $this->checkConfiguration($rawConfiguration);
        $configurationId = $this->dao->convert_sql_id($id);

        if (!$configuration) {
            // Delete the (possibly broken) entry.
            $this->dao->update("DELETE FROM isys_obj_type_list WHERE isys_obj_type_list__id = {$configurationId} LIMIT 1;") && $this->dao->apply_update();

            return;
        }

        // Update the default table configuration with the "refreshed" instance.
        $refreshedConfiguration = $this->dao->convert_sql_text(serialize($this->refreshConfiguration($configuration)));

        $updateQuery = "UPDATE isys_obj_type_list 
            SET isys_obj_type_list__table_config = {$refreshedConfiguration},
            isys_obj_type_list__query = ''
            WHERE isys_obj_type_list__id = {$configurationId} 
            LIMIT 1;";

        $this->dao->update($updateQuery) && $this->dao->apply_update();
    }

    /**
     * Process default configuration which are stored inside the settings.
     *
     * @param string $constant
     *
     * @void
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    private function processDefaultConfiguration(string $constant)
    {
        if (!tenantSettings::has('cmdb.default-object-table.config.' . $constant)) {
            return;
        }

        $configuration = $this->checkConfiguration(tenantSettings::get('cmdb.default-object-table.config.' . $constant));

        if (!$configuration) {
            tenantSettings::remove('cmdb.default-object-table.config.' . $constant);

            return;
        }

        // Update the default table configuration with the "refreshed" instance.
        tenantSettings::set('cmdb.default-object-table.config.' . $constant, serialize($this->refreshConfiguration($configuration)));
    }

    /**
     * Method for refreshing the given configuration.
     * This will remove any stored properties which are not available any more.
     * Furthermore it will update the names and types of properties and categories.
     *
     * @param Config $configuration
     *
     * @return Config
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    public function refreshConfiguration(Config $configuration): Config
    {
        $properties = $configuration->getProperties();
        $refreshedProperties = [];

        foreach ($properties as $property) {
            // In order to check if a property still exists, we have to get it from the DAO.
            $daoName = $property->getClass();

            if (!class_exists($daoName) || !is_a($daoName, isys_cmdb_dao_category::class, true)) {
                // Skip this property, its class does not exist.
                continue;
            }

            /** @var isys_cmdb_dao_category $dao */
            /** @var isys_cmdb_dao_category $daoName */
            $dao = $daoName::instance($this->database);

            if ($property->getClass() === 'isys_cmdb_dao_category_g_custom_fields') {
                /** @var \isys_cmdb_dao_category_g_custom_fields $dao */
                $dao->set_catg_custom_id($property->getCustomCatID());
            }

            $propertyDefinition = $dao->get_property_by_key($property->getKey());

            if ($propertyDefinition === null) {
                // Skip this property, it seems to be missing.
                continue;
            }

            // If, until here, everything is fine, we will update the property with the latest state from.
            $property->setName($propertyDefinition[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
            $property->setType($propertyDefinition[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]);
            $property->setCategoryName($dao->getCategoryTitle());

            $refreshedProperties[] = $property;
        }

        $configuration->setProperties($refreshedProperties);

        return $configuration;
    }
}
