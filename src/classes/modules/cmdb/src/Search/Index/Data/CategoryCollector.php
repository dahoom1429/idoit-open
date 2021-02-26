<?php

namespace idoit\Module\Cmdb\Search\Index\Data;

use idoit\Module\Cmdb\Search\Index\Data\Source\Category\AbstractCategorySource;
use idoit\Module\Search\Index\Data\AbstractCollector;
use idoit\Module\Search\Index\Data\Source\Config;
use idoit\Module\Search\Index\Data\Source\DynamicSource;
use idoit\Module\Search\Index\Data\Source\Indexable;
use isys_application;
use isys_cmdb_dao_category;
use isys_component_database;
use isys_tenantsettings;

/**
 * i-doit
 *
 * CategoryCollector
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CategoryCollector extends AbstractCollector
{
    /**
     * Blacklisted object types
     *
     * @type string[]
     */
    const BLACKLISTED_OBJECT_TYPES = [
        'C__OBJTYPE__CONTAINER',
        'C__OBJTYPE__GENERIC_TEMPLATE',
        'C__OBJTYPE__PARALLEL_RELATION',
        'C__OBJTYPE__RELATION',
        'C__OBJTYPE__LOCATION_GENERIC',
        'C__OBJTYPE__MIGRATION_OBJECT',
        'C__OBJTYPE__NAGIOS_HOST_TPL',
        'C__OBJTYPE__NAGIOS_SERVICE_TPL'
    ];

    /**
     * @var isys_component_database
     */
    private $database;

    /**
     * Only retrieve sources with *__const
     *
     * @var string[]
     */
    private $categoryConstants;

    /**
     * Only retrieve sources with specific objectIds
     *
     * @var int[]
     */
    private $objectIds;

    /**
     * Only retrieve sources with specific categoryIds
     *
     * @var int[]
     */
    private $categoryIds;

    /**
     * CategoryCollector constructor.
     *
     * @param isys_component_database $database
     * @param string[]                $categoryConstants
     * @param int[]                   $objectIds
     * @param int[]                   $categoryIds
     */
    public function __construct(
        isys_component_database $database,
        array $categoryConstants = [],
        array $objectIds = [],
        array $categoryIds = []
    ) {
        $this->database = $database;
        $this->categoryConstants = $categoryConstants;
        $this->objectIds = $objectIds;
        $this->categoryIds = $categoryIds;
        $defaultWhitelist = isys_tenantsettings::get('search.whitelist.categories', null);
        $this->setWhitelistedSources($defaultWhitelist !== null ? explode(',', $defaultWhitelist) : []);
        parent::__construct();
    }

    /**
     * @param string[] $categoryConstants
     */
    public function setCategoryConstants($categoryConstants)
    {
        $this->categoryConstants = $categoryConstants;
    }

    /**
     * @param int[] $objectIds
     */
    public function setObjectIds($objectIds)
    {
        $this->objectIds = $objectIds;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return Indexable[]
     */
    protected function getIndexableDataSources()
    {
        /**
         * @var $dataSources Indexable[]
         */
        $dataSources = [];

        $categoryConstants = !empty($this->categoryConstants) ?
            implode(',', array_map([
                isys_application::instance()->container->get('cmdb_dao'),
                'convert_sql_text'
            ], $this->categoryConstants)) : '';
        $categoryTypes = implode(', ', [
            isys_cmdb_dao_category::TYPE_FOLDER,
            isys_cmdb_dao_category::TYPE_VIEW,
            isys_cmdb_dao_category::TYPE_REAR
        ]);

        $globalCategories = $this->database->retrieveArrayFromResource($this->database->query(
            'SELECT isysgui_catg__class_name AS class, isysgui_catg__const as identifier FROM isysgui_catg WHERE isysgui_catg__type NOT IN (' .
            $categoryTypes . ')' . (($categoryConstants !== '') ? ' AND isysgui_catg__const IN (' . $categoryConstants . ')' : '')
        ));

        $specificCategories = $this->database->retrieveArrayFromResource($this->database->query(
            'SELECT isysgui_cats__class_name AS class, isysgui_cats__const as identifier FROM isysgui_cats WHERE isysgui_cats__type NOT IN (' .
            $categoryTypes . ')' . (($categoryConstants !== '') ? ' AND isysgui_cats__const IN (' . $categoryConstants . ')' : '')
        ));

        $categories = array_merge($globalCategories, $specificCategories);

        $namespace = 'idoit\Module\Cmdb\Search\Index\Data\Source\Category\\';

        foreach ($categories as &$category) {
            $category['dao'] = $category['class'];

            if (!class_exists($namespace . $category['dao'])) {
                $category['class'] = 'AbstractCategorySource';
            }
        }

        $customCategories = $this->database->retrieveArrayFromResource($this->database->query(
            'SELECT isysgui_catg_custom__id AS id, isysgui_catg_custom__const as identifier FROM isysgui_catg_custom WHERE isysgui_catg_custom__type NOT IN (' .
            $categoryTypes . ')' . (($categoryConstants !== '') ? ' AND isysgui_catg_custom__const IN (' . $categoryConstants . ')' : '')
        ));

        foreach ($customCategories as $customCategory) {
            $categories[] = [
                'id'         => $customCategory['id'],
                'identifier' => $customCategory['identifier'],
                'dao'        => 'isys_cmdb_dao_category_g_custom_fields',
                'class'      => 'isys_cmdb_dao_category_g_custom_fields'
            ];
        }

        $config = new Config();
        $config->setObjectIds($this->objectIds);
        $config->setCategoryIds($this->categoryIds);

        foreach ($categories as $categorySourceConfig) {
            if (!array_key_exists(Indexable::class, class_implements($namespace . $categorySourceConfig['class'], true)) ||
                !class_exists($categorySourceConfig['dao'])) {
                continue;
            }

            $categoryDao = call_user_func([
                $categorySourceConfig['dao'],
                'instance'
            ], $this->database);

            if ($categoryDao instanceof \isys_cmdb_dao_category_g_custom_fields) {
                // Singleton for custom categories should not be used, each custom category should have its own instance
                $categoryDao = new \isys_cmdb_dao_category_g_custom_fields($this->database);
                $categoryDao->set_catg_custom_id($categorySourceConfig['id']);
            }

            $class = $namespace . $categorySourceConfig['class'];

            /**
             * @var $categorySource AbstractCategorySource
             */
            $categorySource = new $class($categoryDao, $this->database);

            if (array_key_exists(DynamicSource::class, class_implements($namespace . $categorySourceConfig['class'], true))) {
                $categorySource->setIdentifier($categorySourceConfig['identifier']);
            }

            $dataSources[$categorySourceConfig['identifier']] = [
                'instance' => $categorySource,
                'config'   => $config
            ];
        }

        return $dataSources;
    }
}
