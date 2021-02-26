<?php

namespace idoit\Module\Cmdb\Controller;

use Exception;
use idoit\Component\Provider\DiInjectable;
use idoit\Module\Cmdb\Model\Tree;
use isys_application;
use isys_controller;
use isys_format_json as JSON;
use isys_register;

/**
 * i-doit Location browser controller.
 *
 * @package     idoit\Module\Cmdb\Controller
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.11.1
 */
class BrowseLocation extends Main implements isys_controller
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
        $objectId = (int)$request->get('id');
        $postData = (array)$request->get('POST');
        $mode = (string)$postData['mode'] ?: Tree::MODE_COMBINED;
        $onlyContainer = (bool)$postData['onlyContainer'] ?: false;
        // @see  ID-6808  Consider the rights.
        $considerRights = (bool)$postData['considerRights'] ?: false;

        $tree = new Tree($this->getDi()->get('database'));

        $this->response['data'] = $tree->getLocationChildren($objectId, $mode, $onlyContainer, null, $considerRights);
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
