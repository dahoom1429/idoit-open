<?php

namespace idoit\View;

use idoit\Component\Provider\DiInjectable;
use idoit\Component\Provider\Factory;

/**
 * i-doit Exception viewer
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ExceptionView
{
    use DiInjectable, Factory;

    /**
     * @param \Exception $e
     */
    public function draw(\Exception $e)
    {
        \isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->show_navbar();

        $tree = new \isys_cmdb_view_tree_objecttype(\isys_module_request::get_instance());

        $this->getDi()->template->assign('menu_tree', $tree->process())
            ->assign('message', $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')')
            ->assign('backtrace', $e->getTraceAsString())
            ->display('error.tpl');

        die();
    }
}