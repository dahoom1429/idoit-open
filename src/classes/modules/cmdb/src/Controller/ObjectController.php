<?php

namespace idoit\Module\Cmdb\Controller;

/**
 * i-doit cmdb object controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ObjectController extends Base implements \isys_controller
{
    /**
     * @param \isys_application $p_application
     *
     * @return \isys_cmdb_dao_nexgen
     */
    public function dao(\isys_application $p_application)
    {
        return new \isys_cmdb_dao_nexgen($p_application->database);
    }

    /**
     * Default request handler, gets called in every /cmdb/object request
     *
     * @param \isys_register $p_request
     *
     * @return \idoit\View\Renderable
     */
    public function handle(\isys_register $p_request, \isys_application $p_application)
    {
        if (isset($p_request->id)) {
            $_GET[C__CMDB__GET__OBJECT] = $p_request->id;
            $_GET[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
            $_GET[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
            //$_GET[C__CMDB__GET__CATG] = C__CATG__GLOBAL;

            \isys_module_request::get_instance()
                ->_internal_set_private('m_get', $_GET);

            // clear tree
            \isys_component_tree::factory('menu_tree')
                ->reinit();

            // re-initialize cmdb module
            $this->module->start($p_request);

            // remove dashboard default and reset contentarea
            $p_application->template->include_template('contentarea', 'content/main.tpl');

        }

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

}