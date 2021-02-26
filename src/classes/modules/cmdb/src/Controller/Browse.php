<?php

namespace idoit\Module\Cmdb\Controller;

use Exception;
use idoit\Component\Browser\Condition\DateCondition;
use idoit\Component\Browser\Condition\ObjectGroupCondition;
use idoit\Component\Browser\Condition\ObjectTypeCondition;
use idoit\Component\Browser\Condition\PersonGroupCondition;
use idoit\Component\Browser\Condition\RelationTypeCondition;
use idoit\Component\Browser\Condition\ReportCondition;
use idoit\Component\Browser\Condition\SearchCondition;
use idoit\Component\Browser\ConditionInterface;
use idoit\Component\Browser\Retriever;
use idoit\Component\Provider\DiInjectable;
use isys_application;
use isys_controller;
use isys_format_json as JSON;
use isys_popup_browser_object_ng as ObjectBrowser;
use isys_register;
use isys_tenantsettings;

/**
 * i-doit cmdb browser controller, used primarily for the object browser.
 *
 * @package     i-doit
 * @subpackage
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Browse extends Main implements isys_controller
{
    use DiInjectable;

    private $response;

    /**
     * Pre method gets called by the framework.
     */
    public function pre()
    {
        header('Content-Type: application/json');

        $this->response = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];
    }

    /**
     * Dispatch method for
     *
     * @param isys_register $request
     */
    public function handle(isys_register $request, isys_application $app)
    {
        // Do something.
    }

    /**
     * Method for receiving object data via given IDs.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/get-object-data
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function getObjectData(isys_register $request)
    {
        $post = (array)$request->get('POST');

        // Create new Retriever and Condition instances.
        $retriever = new Retriever();

        // @see  ID-6927  Make sure to always use the same "raw" instance name.
        $post['key'] = ObjectBrowser::uniformBrowserName($post['key']);

        $attributes = isys_tenantsettings::get('cmdb.object-browser.' . $post['key'] . '.attributes');

        // Set the (user-) defined attributes to display.
        if (is_array($attributes)) {
            $retriever->setAttributes($attributes);
        } elseif (!empty($attributes) && JSON::is_json_array($attributes)) {
            $retriever->setAttributes(JSON::decode($attributes));
        }

        // Set the condition in the retriever.
        $retriever->setCondition(new ObjectTypeCondition($this->getDi()->get('database')));

        $displayCategoryNames = isys_tenantsettings::get('cmdb.object-browser.' . $post['key'] . '.displayAttributeCategories', false);

        $retriever->showCategoryNames($displayCategoryNames);

        $this->response['data'] = $retriever->getObjectData(true, JSON::decode($post['objects']));
    }

    /**
     * Method for receiving "selection" data via given IDs (can be category data or anything else - works with a callback).
     *
     * @param isys_register $request
     *
     * @see    ID-5686 This method will help preselecting various data by given IDs.
     * @route  /cmdb/browse/get-selection-data
     * @throws Exception
     */
    public function getSelectionData(isys_register $request)
    {
        $post = (array)$request->get('POST');

        $selectionEntries = JSON::decode($post['objects']);

        [$class, $method] = explode('::', $post['secondListPreselectionCallback']);

        if (!class_exists($class) || !is_a($class, '\isys_cmdb_dao', true)) {
            throw new Exception('The class "' . $class . '" could not be found or is no instance of "isys_cmdb_dao".');
        }

        $daoInstance = new $class($this->getDi()->get('database'));

        if (!method_exists($daoInstance, $method)) {
            throw new Exception('The method "' . $method . '" does not exist in class "' . $class . '".');
        }

        $response = $daoInstance->$method(ObjectBrowser::C__CALL_CONTEXT__PRESELECTION, ['dataIds' => $selectionEntries]);

        // The response needs to provide a specific structure:
        if (!isset($response['header']) || !isset($response['data'])) {
            throw new Exception('The response of "' . $class . '->' . $method . '" needs to provide a specific structure: ["header" => [], "data" => []]');
        }

        $this->response['data'] = $daoInstance->$method(ObjectBrowser::C__CALL_CONTEXT__PRESELECTION, ['dataIds' => $selectionEntries]);
    }

    /**
     * Method for receiving data via ObjectTypeCondition condition.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/objectType/<object-type id>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function objectType(isys_register $request)
    {
        $this->processRequest(new ObjectTypeCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via ObjectGroup condition.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/objectGroup/<object id>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function objectGroup(isys_register $request)
    {
        $this->processRequest(new ObjectGroupCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via PersonGroup condition.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/personGroup/<object id>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function personGroup(isys_register $request)
    {
        $this->processRequest(new PersonGroupCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via RelationType condition.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/relationType/<relation type id>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function relationType(isys_register $request)
    {
        $this->processRequest(new RelationTypeCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via Date condition.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/date/<date span>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function date(isys_register $request)
    {
        $this->processRequest(new DateCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via Search.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/search/<search string>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function search(isys_register $request)
    {
        $this->processRequest(new SearchCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via Report.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/report/<report id>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function report(isys_register $request)
    {
        try {
            \isys_auth_report::instance()->check_report_right(\isys_auth_report::EXECUTE, $request->get('id'));
        } catch (\isys_exception_auth $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();

            return;
        }

        $this->processRequest(new ReportCondition($this->getDi()->get('database')), $request);
    }

    /**
     * Method for receiving data via a custom condition.
     * See the additional request data for more specific info.
     *
     * @param isys_register $request
     *
     * @route  /cmdb/browse/customCondition/<parameter>
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function customCondition(isys_register $request)
    {
        $additionalData = JSON::decode($request->get('POST')->get('additionalData'));

        // Convert from dot to backslash.
        $additionalData['name'] = str_replace('.', '\\', $additionalData['name']);

        if (!class_exists($additionalData['name']) || !is_a($additionalData['name'], ConditionInterface::class, true)) {
            throw new \isys_exception_objectbrowser('The given condition is not compatible!', 1);
        }

        $this->processRequest(new $additionalData['name']($this->getDi()->get('database')), $request, $additionalData[C__CMDB__GET__OBJECT]);
    }

    /**
     * @param ConditionInterface $condition
     * @param isys_register      $request
     * @param integer            $contextObjectId
     *
     * @throws Exception
     * @throws \isys_exception_database
     */
    private function processRequest(ConditionInterface $condition, isys_register $request, $contextObjectId = null)
    {
        $post = (array)$request->get('POST');

        // Create new Retriever and Condition instances.
        $retriever = new Retriever();
        $language = isys_application::instance()->container->get('language');

        // @see  ID-6927  Make sure to always use the same "raw" instance name.
        $post['key'] = ObjectBrowser::uniformBrowserName($post['key']);

        $attributes = isys_tenantsettings::get('cmdb.object-browser.' . $post['key'] . '.attributes');

        // Set the (user-) defined attributes to display.
        if (is_array($attributes)) {
            $retriever->setAttributes($attributes);
        } elseif (!empty($attributes) && JSON::is_json_array($attributes)) {
            $retriever->setAttributes(JSON::decode($attributes));
        }

        // Prepare filters.
        if (isset($post['filter']) && JSON::is_json_array($post['filter'])) {
            $filters = JSON::decode($post['filter']);

            $preparedFilters = [
                'CmdbStatusFilter'        => $filters['cmdbStatus'],
                'GlobalCategoryFilter'    => $filters['globalCategory'],
                'ObjectTypeFilter'        => $filters['objectType'],
                'ObjectTypeExcludeFilter' => $filters['objectTypeExclude'],
                'SpecificCategoryFilter'  => $filters['specificCategory'],
            ];

            $condition->registerFilterByArray($preparedFilters);
        }

        if (isset($post['customFilter']) && JSON::is_json_array($post['customFilter'])) {
            $customFilters = JSON::decode($post['customFilter']);

            foreach ($customFilters as $customFilter => $customFilterValue) {
                $condition->registerFilter((new $customFilter($this->getDi()->get('database')))->setParameter($customFilterValue));
            }
        }

        // Set the parameter (given by Route).
        $condition->setParameter($request->get('id'));

        // Set the condition in the retriever.
        $retriever->setCondition($condition);

        $displayCategoryNames = isys_tenantsettings::get('cmdb.object-browser.' . $post['key'] . '.displayAttributeCategories', false);

        $retriever->showCategoryNames($displayCategoryNames);

        if ($contextObjectId !== null) {
            $retriever->setContextObjectId($contextObjectId);
        }

        try {
            // Output overview and/or object data!
            switch ($post['output']) {
                default:
                case 'both':
                    // Get overview and object data!
                    $this->response['data'] = [
                        'overview' => $retriever->getOverviewData(),
                        'objects'  => $retriever->getObjectData()
                    ];
                    break;

                case 'objects':
                    // Get only the object data!
                    $this->response['data'] = $retriever->getObjectData();
                    break;

                case 'overview':
                    // Get only the object data!
                    $this->response['data'] = $retriever->getOverviewData();
                    break;
            }
        } catch (Exception $e) {
            $this->response['success'] = false;
            $this->response['message'] = $language->get('LC__CMDB__OBJECT_BROWSER__SCRIPT__OBJECT_DATA_LOAD_ERROR');

            // The extended message is for debug purposes and only visible in the web developer toolbar.
            $this->response['messageExtended'] = $e->getMessage();
        }
    }

    /**
     * Return the JSON and die.
     */
    public function post()
    {
        echo JSON::encode($this->response);
        die;
    }
}
