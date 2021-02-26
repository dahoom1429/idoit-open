<?php

namespace idoit\Module\Search\Controller;

use idoit\Model\Dao\Base;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\Query\Engine\Mysql\SearchEngine as MysqlSearchEngine;
use idoit\Module\Search\Query\QueryManager;
use idoit\Module\Search\View\JsonResult;
use idoit\Module\Search\View\JsonSuggestResult;
use idoit\Module\Search\View\SearchBox;
use idoit\Module\Search\View\SearchResultList;
use isys_application;
use isys_module_search;
use isys_tenantsettings;

/**
 * i-doit
 *
 * Global Search Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Main implements \isys_controller
{
    /**
     * @var \isys_module_search
     */
    private $module;

    /**
     * @action /search/reindex
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        try {
            $searchString = $_GET['q'] ?: null;

            if ($searchString) {
                $mode = isset($_GET['mode']) ? $_GET['mode'] : \isys_tenantsettings::get('defaults.search.mode', Condition::MODE_DEFAULT);

                $engine = new MysqlSearchEngine();

                $manager = QueryManager::factory()
                    ->attachEngine($engine)
                    ->addSearchKeyword($searchString, 'AND', false, $mode);

                if (\isys_core::is_ajax_request() && $_GET['rand']) {
                    $view = new JsonSuggestResult($p_request);
                    $view->setData($manager->search());
                } else {
                    $minlength = (int)isys_tenantsettings::get('search.minlength.search-string', 3);

                    if (strlen($searchString) < $minlength) {
                        throw new \Exception(isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__SEARCH__NOTIFY__SEARCHPHRASE_TO_SHORT'));
                    }

                    $view = new SearchResultList($p_request);
                    $view->setEngine($engine);

                    if (strpos($searchString, 'title:') === 0) {
                        $searchString = str_replace('title:', '', $searchString);

                        $l_cmdb_dao = new \isys_cmdb_dao($p_application->database);
                        $l_found_object = $l_cmdb_dao->get_obj_id_by_title($searchString);
                        if ($l_found_object > 0) {
                            header('Location: ' . $p_application->www_path . '?objID=' . $l_found_object);
                            die;
                        }
                    }

                    $results = $manager->search();

                    $view->setData($results)
                        ->setSearchMode($mode)
                        ->setSearchString($searchString);

                    $json = new JsonResult($p_request);
                    $json->setData($results);
                    $json->setSearchString($searchString);

                    $p_application->template->assign('autostartDeepSearch', isys_tenantsettings::get('search.global.autostart-deep-search', isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE));
                    $p_application->template->assign('hasResults', count($results->getResult()) > 0);

                    /**
                     * Prevent multiple processing of search results
                     */
                    $p_application->template->assign('initialResponse', json_encode([]));
                    if (isys_tenantsettings::get('search.global.autostart-deep-search', isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE)) {
                        $p_application->template->assign('initialResponse', $json->getDataAsJson());
                    }

                    if (\isys_core::is_ajax_request()) {
                        $view = new JsonResult($p_request);
                        $view->setData($results);
                        $view->setSearchString($searchString);

                        return $view;
                    }
                }
            } else {
                $view = new SearchBox($p_request);
            }

            // Return the view
            return $view;
        } catch (\Exception $e) {
            $p_application->instance()->container['notify']->error($e->getMessage());
        }

        return null;
    }

    /**
     * @param \isys_application $p_application
     *
     * @return \isys_cmdb_dao_nexgen
     */
    public function dao(\isys_application $p_application)
    {
        return new Base($p_application->database);
    }

    /**
     * @param \isys_register       $p_request
     * @param \isys_application    $p_application
     * @param \isys_component_tree $p_tree
     *
     * @return null
     */
    public function tree(\isys_register $p_request, \isys_application $p_application, \isys_component_tree $p_tree)
    {
        return null;
    }

    /**
     * Index constructor.
     *
     * @param \isys_module_search $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }
}
