<?php

namespace idoit\Module\Multiedit\Controller;

use idoit\Context\Context;
use idoit\Controller\CatchallController;
use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Synchronizer\Synchronizer;
use isys_cmdb_dao_category_g_custom_fields;
use idoit\Module\Multiedit\Component\Filter\DataSourceFilter;
use idoit\Module\Multiedit\Component\Multiedit\Row\SimpleRow;
use idoit\Module\Multiedit\Component\Multiedit\Source\DataSource;
use idoit\Module\Multiedit\Component\Multiedit\Source\FilterSource;
use idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource;
use idoit\Module\Multiedit\Component\Multiedit\Config\Config;
use idoit\Module\Multiedit\Component\Multiedit\EditList;
use idoit\Module\Multiedit\Component\Multiedit\Type\AssignmentType;
use idoit\Module\Multiedit\Component\Multiedit\Type\MatrixType;
use isys_cmdb_dao;
use idoit\Module\Multiedit\Component\Filter\CategoryFilter;
use idoit\Module\Multiedit\Model\GlobalCategories;
use idoit\Module\Multiedit\Model\SpecificCategories;
use idoit\Module\Multiedit\Model\CustomCategories;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Component\Property\Property;

use isys_format_json;
use idoit\Module\Multiedit\Component\Multiedit\Exception as MultiEditExceptions;
use idoit\Module\Multiedit\Model\Categories;

/**
 * i-doit cmdb controller
 *
 * @package     modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     2.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.12
 */
class Ajax extends Main
{
    protected $response;

    /**
     * Overwriting the "handle" method.
     *
     * @param   \isys_register    $request
     * @param   \isys_application $application
     *
     * @return  null
     */
    public function handle(\isys_register $request, \isys_application $application)
    {
        $post = $request->get('POST');
        $get = $request->get('GET');

        Context::instance()
            ->setContextTechnical(Context::CONTEXT_MULTIEDIT)
            ->setGroup(Context::CONTEXT_GROUP_MULTIEDIT)
            ->setContextCustomer(Context::CONTEXT_MULTIEDIT);

        try {
            switch ($post['request']) {
                case 'loadCategories':
                    $data = $this->loadCategories($post);
                    break;
                case 'loadFilter':
                    $data = $this->loadFilter($post);
                    break;
                case 'addNewEntry':
                    $data = $this->addNewEntry($post);
                    break;
                case 'saveList':
                    $data = $this->saveList($post);
                    break;
                case 'loadContent':
                default:
                    $listObject = $this->loadContent($post);
                    $data['content'] = $listObject->getList();
                    $data['header'] = $listObject->getHeader();
                    $data['type'] = $listObject->getConfig()->getType()->getCategoryType();
                    $data['multivalued'] = $listObject->getConfig()->getType()->isMultivalued();
                    break;
            }

            $this->response['data'] = $data;
        } catch (\Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    /**
     * * Add new Entry and Row
     *
     * @param $post
     *
     * @return mixed|null|string
     * @throws MultiEditExceptions\EmptySourceDaoException
     * @throws MultiEditExceptions\EmptyPropertiesSourceDataException
     * @throws \isys_exception_database
     */
    private function addNewEntry($post)
    {
        $categoryClass = $post['categoryClass'];
        $objectId = (int)$post['objectId'];
        $categoryInfo = $post['categoryInfo'];

        $return = null;

        if (class_exists($categoryClass) && $objectId > 0) {
            // Callbacks
            $callbackRegister = \isys_register::factory('callbacks');

            /**
             * @var $classInstance \isys_cmdb_dao_category
             */
            $classInstance = $categoryClass::instance(\isys_application::instance()->container->get('database'));

            if ($classInstance instanceof isys_cmdb_dao_category_g_custom_fields) {
                list($categoryType, $categoryId) = explode('_', $categoryInfo);
                $classInstance->set_category_type($categoryType);
                $classInstance->set_category_id($categoryId);
                $classInstance->set_catg_custom_id($categoryId);
            }

            $cmdbDao = \isys_application::instance()->container->get('cmdb_dao');

            $sourceTable = $classInstance->get_table();
            $entryId = $post['entryId'];
            $retriever = $this->getRetriever($classInstance->get_category_type());

            $dataSourceFilter = new DataSourceFilter();

            $categoryFilter = new CategoryFilter();
            $categoryFilter->setCategories([$classInstance->get_category_id()]);

            $properties = $retriever->setFilter($categoryFilter)
                ->setProperties()
                ->getProperties();
            $daoInstances = $retriever->getCategoryDao();

            $config = (new Config())->setObjects([$objectId]);
            $objectData = $config->getObjects();

            $propertiesSource = new PropertiesSource();
            $propertiesSource->setData($properties)
                ->formatData();

            if (current($daoInstances) instanceof ObjectBrowserReceiver && $propertiesSource->count() === 1) {
                $type = new AssignmentType();
            } else {
                $type = new MatrixType();
            }
            $categoryDao = current($daoInstances);
            $type->setMultivalued($categoryDao->is_multivalued());

            $dataSource = (new DataSource())->setObjectIds([$objectId])
                ->setDataSourceFilter($dataSourceFilter)
                ->setFormattedProperties($propertiesSource->getData())
                ->setDao($daoInstances)
                ->formatData();

            $listData = $dataSource->getData();

            $return = (new SimpleRow())->setObjectData($objectData[$objectId])
                ->setProperties($propertiesSource)
                ->setObjectId($objectId)
                ->setId($entryId)
                ->render();

            if ($callbackRegister->count()) {
                $callbackScript = "<script type='text/javascript'>";

                $callbacks = $callbackRegister->get();

                foreach ($callbacks as $observedTarget => $callback) {
                    $callbackScript .= "idoit.callbackManager.registerCallback('{$observedTarget}.changed', function () { ";
                    foreach ($callback as $call) {
                        $callbackScript .= $call;
                    }
                    $callbackScript .= '});';
                }

                $callbackScript .= '</script>';

                $return .= $callbackScript;
            }
        }

        return $return;
    }

    /**
     * @param $post
     *
     * @return mixed
     * @throws Exception
     * @throws \isys_exception_database
     */
    private function loadFilter($post)
    {
        list($categoryInfo, $categoryClass) = explode(':', $post['category']);
        list($categoryType, $categoryId) = explode('_', $categoryInfo);
        $database = \isys_application::instance()->container->get('database');
        $return = [];
        $language = \isys_application::instance()->container->get('language');

        $retriever = $this->getRetriever($categoryType);

        $filter = new CategoryFilter();
        $filter->setCategories([$categoryId]);

        $propertyCollection = $retriever->setFilter($filter)
            ->setProperties()
            ->getProperties();

        foreach ($propertyCollection as $categoryTitle => $properties) {
            foreach ($properties as $propertyKey => $property) {
                if (!$property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT]) {
                    continue;
                }

                if ($property instanceof Property) {
                    $propertyTitle = $property->getInfo()->getTitle();
                } else {
                    $propertyTitle = $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
                }

                $return[$categoryTitle][$propertyKey] = $language->get($propertyTitle);
            }
        }
        return $return;
    }

    /**
     * @param $type
     *
     * @return Categories
     */
    private function getRetriever($type)
    {
        $database = \isys_application::instance()->container->get('database');

        switch ($type) {
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                $retriever = CustomCategories::instance($database);
                break;
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $retriever = SpecificCategories::instance($database);
                break;
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                $retriever = GlobalCategories::instance($database);
                break;
            default:
                throw new MultiEditExceptions\UnknownCategoryTypeException('Category type is unknown.');
                break;
        }

        return $retriever;
    }

    /**
     * @param $post
     *
     * @return EditList
     * @return EditList
     * @throws \idoit\Exception\JsonException
     */
    private function loadContent($post)
    {
        list($categoryInfo, $categoryClass) = explode(':', $post['category']);
        list($categoryType, $categoryId) = explode('_', $categoryInfo);
        $objectIds = \isys_format_json::decode($post['objects']);
        $listFilter = \isys_format_json::decode($post['filter']);

        try {
            $retriever = $this->getRetriever($categoryType);

            $categoryFilter = new CategoryFilter();
            $categoryFilter->setCategories([$categoryId]);

            $dataSourceFilter = new DataSourceFilter();
            $dataSourceFilter->setProperty($listFilter['property'])
                ->setValue($listFilter['value']);

            $properties = $retriever->setFilter($categoryFilter)
                ->setProperties()
                ->getProperties();
            $daoInstances = $retriever->getCategoryDao();

            $propertiesSource = new PropertiesSource();
            $propertiesSource->setData($properties)
                ->formatData();

            $authCondition = \isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition(\isys_auth::VIEW);

            $categoryDao = current($daoInstances);
            $categoryDao->set_category_id($categoryId);

            if (Config::isAssignmentType($categoryDao, $propertiesSource)) {
                $type = new AssignmentType();
            } else {
                $type = new MatrixType();
            }

            $type->setMultivalued($categoryDao->is_multivalued());

            $dataSource = (new DataSource())->setObjectIds($objectIds)
                ->setDataSourceFilter($dataSourceFilter)
                ->setAuthCondition($authCondition)
                ->setFormattedProperties($propertiesSource->getData())
                ->setDao($daoInstances)
                ->formatData();

            $config = (new Config())->setObjects($objectIds)
                ->setPropertySource($propertiesSource)
                ->setDataSource($dataSource)
                ->setType($type);

            return (new EditList())->setConfig($config)
                ->init();
        } catch (\Exception $e) {
            throw new MultiEditExceptions\RenderListException('Failed to render list with error: ' . $e->getMessage());
        }
    }

    /**
     * @param $post
     */
    public function saveList($post)
    {
        list($categoryIdInfo, $class) = explode(':', $post['categoryInfo']);
        list($categoryType, $categoryId) = explode('_', $categoryIdInfo);

        $language = \isys_application::instance()->container->get('language');

        if (!class_exists($class)) {
            throw new Exception("Class {$class} does not exist.");
        }

        $database = \isys_application::instance()->container->get('database');

        /**
         * @var $categoryDao \isys_cmdb_dao_category
         */
        $categoryDao = $class::instance($database);

        if ((int)$categoryType == defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM')) {
            $categoryDao->set_catg_custom_id((int)$categoryId);
            $categoryInfo = $categoryDao->get_category_info((int)$categoryId);
            $categoryDao->set_catgory_const($categoryInfo['isysgui_catg_custom__const']);
            $categoryDao->set_category_type(C__CMDB__CATEGORY__TYPE_CUSTOM);
        }

        $categoryTitle = $categoryDao->getCategoryTitle();
        $multiValued = $categoryDao->is_multivalued();
        $data = \isys_format_json::decode($post['data']);
        $dataChanges = \isys_format_json::decode($post['dataChanges']);

        try {
            $properties = (new PropertiesSource())->setData([$categoryTitle => $categoryDao->get_properties()])
                ->activateAllProperties()
                ->formatData()
                ->getData();

            $syncer = new Synchronizer();
            $syncer->setData($data)
                ->setDataChanges($dataChanges)
                ->setCategoryDao($categoryDao)
                ->setProperties(current($properties))
                ->setMultivalued($multiValued)
                ->handle();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!empty($syncer->getValidationErrors())) {
            $this->response['validation'] = $syncer->getValidationErrors();

            if ((bool)\isys_tenantsettings::get('import.validation.break-on-error', true) === false) {
                $this->response['messageType'] = 'warning';
                $this->response['message'] = $language->get('LC__MODULE__MULTIEDIT__SKIP_ATTRIBUTES_WITH_VALIDATION_ERROR');
            } else {
                $this->response['messageType'] = 'error';
                $this->response['message'] = $language->get('LC__MODULE__MULTIEDIT__SKIP_DATASETS_WITH_VALIDATION_ERROR');
            }
            $this->response['success'] = false;
        }

        return true;
    }

    /**
     * @param $post
     *
     * @return array
     * @throws \idoit\Exception\JsonException
     * @throws \isys_exception_database
     */
    private function loadCategories($post)
    {
        $objectIds = \isys_format_json::decode($post['objectIds']);

        $categoryFilter = new CategoryFilter();
        $categoryFilter->setObjects($objectIds);

        $allowedObjectsCondition = \isys_auth_cmdb_objects::instance()
            ->get_allowed_objects_condition(\isys_auth::EDIT);

        $allowedCategories = \isys_auth_cmdb_categories::instance()
            ->get_allowed_categories();
        if (is_array($allowedCategories)) {
            $categoryFilter->setCategories($allowedCategories);
        }

        $database = \isys_application::instance()->container->get('database');
        $language = \isys_application::instance()->container->get('language');

        /**
         * Categories
         */
        $globalCategories = GlobalCategories::instance($database)
            ->setFilter($categoryFilter)
            ->setData();
        $specificCategories = SpecificCategories::instance($database)
            ->setFilter($categoryFilter)
            ->setData();
        $customCategories = CustomCategories::instance($database)
            ->setFilter($categoryFilter)
            ->setData();

        $multivalueCategoies = $globalCategories->getMultivalueCategories() + $specificCategories->getMultivalueCategories() + $customCategories->getMultivalueCategories();

        $data = [];

        $data[$language->get('LC__CMDB__GLOBAL_CATEGORIES')] = $globalCategories->getData();
        $data[$language->get('LC__CMDB__SPECIFIC_CATEGORIES')] = $specificCategories->getData();
        $data[$language->get('LC__CMDB__CUSTOM_CATEGORIES')] = $customCategories->getData();

        asort($data[$language->get('LC__CMDB__GLOBAL_CATEGORIES')]);
        asort($data[$language->get('LC__CMDB__SPECIFIC_CATEGORIES')]);
        asort($data[$language->get('LC__CMDB__CUSTOM_CATEGORIES')]);

        return $data;
    }

    /**
     * Overwriting the "onDefault" method.
     *
     * @param   \isys_register    $request
     * @param   \isys_application $application
     *
     * @return  null
     */
    public function onDefault(\isys_register $request, \isys_application $application)
    {
        return null;
    }

    /**
     * Pre method gets called by the framework.
     *
     * @author      Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function pre()
    {
        header('Content-Type: application/json');

        $this->response = [
            'success' => true,
            'messageType' => 'success',
            'data'    => null,
            'message' => null
        ];
    }

    /**
     * Post method gets called by the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function post()
    {
        echo \isys_format_json::encode($this->response);
        die;
    }
} // class
