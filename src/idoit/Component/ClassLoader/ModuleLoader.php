<?php

namespace idoit\Component\ClassLoader;

use idoit\Controller\CatchallController;

/**
 * i-doit Module Loader
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ModuleLoader extends CatchallController
{
    /**
     * @param string         $moduleName
     * @param \isys_register $request
     */
    public function boot($moduleName, \isys_register $request)
    {
        $request->module = $moduleName;

        return $this->handle($request);
    }
}