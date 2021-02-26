<?php

namespace idoit\Module\Search\View;

use idoit\Model\Dao\Base as DaoBase;
use idoit\Module\Search\Index\Engine\Searchable;
use idoit\Module\Search\Query\Protocol\QueryResult;
use idoit\View\Base;
use idoit\View\Renderable;
use isys_application;
use isys_component_template as ComponentTemplate;
use isys_module as ModuleBase;
use isys_module_search;
use isys_tenantsettings;

/**
 * i-doit cmdb controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SearchResultList extends Base implements Renderable
{
    /**
     * Search result data
     *
     * @var QueryResult
     */
    private $data;

    /**
     * @var string
     */
    private $searchString = '';

    /**
     * @var int
     */
    private $searchMode;

    /**
     * @var Searchable
     */
    private $engine;

    /**
     * @param QueryResult $data
     *
     * @return $this
     */
    public function setData(QueryResult $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param int $searchMode
     *
     * @return $this
     */
    public function setSearchMode($searchMode)
    {
        $this->searchMode = $searchMode;

        return $this;
    }

    /**
     * @param $searchString
     *
     * @return $this
     */
    public function setSearchString($searchString)
    {
        $this->searchString = strip_tags($searchString);

        return $this;
    }

    /**
     * @param Searchable $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param string $searchString
     *
     * @return string
     */
    public function getHtmlList($searchString = '')
    {
        $list = '<th>' . isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__SOURCE') . '</th>';

        if (isys_tenantsettings::get('search.index.include_archived_deleted_objects', false)) {
            $list .= '<th>' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__STATUS') . '</th>';
        }

        $list .= '<th>' . isys_application::instance()->container->get('language')
                ->get('LC__MODULE__SEARCH__FOUND_MATCH') . '</th>';

        if ($this->engine->isScoringAvailable()) {
            $list .= '<th class="desc">Score</th>';
        }
        $list = '<tr>' . $list .'</tr>';

        foreach ($this->data->getResult() as $item) {
            $value = nl2br(htmlentities($item->getValue()));

            if (stripos($value, $this->searchString) !== false) {
                $value = str_replace(substr($value, stripos($value, $this->searchString), strlen($this->searchString)), '<strong>' . substr($value, stripos($value, $this->searchString), strlen($this->searchString)) . '</strong>', $value);
            }

            $value = '<a href="' . $item->getLink() . '">' . $value . '</a>';

            $row = '<td>' . ucfirst($item->getType()) . ': ' . $item->getKey() . '</td>';

            if (isys_tenantsettings::get('search.index.include_archived_deleted_objects', false)) {
                $row .= '<td>' . $item->getStatus() . '</td>';
            }

            $row .= '<td>' . $value . '</td>';

            if ($this->engine->isScoringAvailable()) {
                $row .= '<td><div class="progress"><div class="progress-bar" data-width-percent="' . floatval($item->getScore()) .'"></div></div></td>';
            }
            $list .= '<tr>' . $row . '</tr>';
        }

        return '<table id="mainTable" cellspacing="0" class="mainTable">' . $list . '</table>';
    }

    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     * @param DaoBase           $p_model
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        try {
            $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->assign('searchMode', $this->searchMode);

            /**
             * Check access rights
             */
            \isys_auth_search::instance()
                ->check(\isys_auth::EMPTY_ID_PARAM, "search");

            /**
             * Set paths to templates
             */
            $this->paths['contentbottomcontent'] = isys_module_search::getPath() . 'templates/main.tpl';
            $this->paths['contenttop'] = false;

            $p_template->assign("objectTableList", $this->getHtmlList($this->searchString))
                ->assign('headline', isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__SEARCH__RESULT_HEADLINE', [
                        $this->searchString,
                        count($this->data->getResult())
                    ]));

            if (count($this->data->getResult()) > 0) {
                if (count($this->data->getResult()) === \isys_tenantsettings::get('search.limit', 2500)) {
                    \isys_application::instance()->container['notify']->warning(isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__SEARCH__LIMIT_INFO', [\isys_tenantsettings::get('search.limit', 2500)]));
                }

                return $this;
            } else {
                $p_template->assign("error", isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__SEARCH__NO_RESULTS', [$this->searchString]));
            }
        } catch (\isys_exception_auth $e) {
            $p_template->assign("exception", $e->write_log());
            $p_template->include_template('contentbottomcontent', 'exception-auth.tpl');
        } catch (\Exception $e) {
            \isys_application::instance()->container['notify']->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $p_row
     */
    public function rowModifier(&$p_row)
    {
        try {
            $p_row['value'] = '<a href="' . $p_row['link'] . '">' . $p_row['value'] . '</a>';
        } catch (\Exception $e) {
        }
    }
}
