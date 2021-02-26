<?php

namespace idoit\View;

use idoit\Model\Dao\Base as DaoBase;
use isys_component_template as ComponentTemplate;
use isys_module as Module;

/**
 * i-doit View Base class
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Renderable
{
    /**
     * Process view details, do smarty assignments, and so on..
     *
     * @param Module            $module
     * @param ComponentTemplate $template
     * @param DaoBase           $model
     *
     * @return Renderable
     */
    public function process(Module $module, ComponentTemplate $template, DaoBase $model);

    /**
     * Get the evaluated contents of the object.
     *
     * @return Renderable
     */
    public function render();
}
