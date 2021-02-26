<?php

namespace idoit\Module\Search\View;

use idoit\Model\Dao\Base as DaoBase;
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
class SearchBox extends Base implements Renderable
{
    /**
     * @param ModuleBase        $p_module
     * @param ComponentTemplate $p_template
     *
     * @return $this|Renderable
     */
    public function process(ModuleBase $p_module, ComponentTemplate $p_template, DaoBase $p_model)
    {
        try {
            $p_template->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->assign('searchWordMinLength', (int)isys_tenantsettings::get('search.minlength.search-string', 3))
                ->assign('headline', isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__SEARCH__TITLE'));

            /**
             * Check access rights
             */
            \isys_auth_search::instance()
                ->check(\isys_auth::EMPTY_ID_PARAM, "search");

            /**
             * Set paths to templates
             */
            $this->paths['contentbottomcontent'] = isys_module_search::getPath() . 'templates/index.tpl';
            $this->paths['contenttop'] = false;
        } catch (\isys_exception_auth $e) {
            $p_template->assign("exception", $e->write_log());
            $p_template->include_template('contentbottomcontent', 'exception-auth.tpl');
        }

        return $this;
    }
}
