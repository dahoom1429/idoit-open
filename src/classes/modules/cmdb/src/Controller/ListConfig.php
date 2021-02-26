<?php

namespace idoit\Module\Cmdb\Controller;

use idoit\Component\Provider\DiInjectable;
use isys_cmdb_dao_category;
use isys_controller;
use isys_format_json as JSON;
use isys_module_cmdb;
use isys_register;

/**
 * i-doit cmdb List Config controller, used primarily for the object browser.
 *
 * @package     i-doit
 * @subpackage
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ListConfig extends Main implements isys_controller
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


    public function getFilterProperty(isys_register $request)
    {
        $filterField = $request->get('id');
        $postData = (array)$request->get('POST');
        $filterValue = (string)$postData['filterValue'] ?: '';

        [$className, $propertyKey] = explode('__', $filterField);

        $propertyData = [
            'type' => 'text'
        ];

        /**
         * @var $propertyClass isys_cmdb_dao_category
         */
        if (class_exists($className)) {
            $propertyClass = new $className($this->getDi()
                ->get('database'));
            $property = $propertyClass->get_property_by_key($propertyKey);

            if (in_array(
                $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                [C__PROPERTY__INFO__TYPE__DIALOG, C__PROPERTY__INFO__TYPE__DIALOG_PLUS, C__PROPERTY__INFO__TYPE__DIALOG_LIST]
            )) {
                $propertyData = [
                    'type'   => 'list',
                    'params' => $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]
                ];
            }
        }

        $template = $this->getDi()->get('template');
        $template->assign('property', $propertyData);
        $template->assign('filterValue', $filterValue);

        $this->response['data'] = $template->fetch(isys_module_cmdb::getPath() . 'templates/filter_property.tpl');
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
