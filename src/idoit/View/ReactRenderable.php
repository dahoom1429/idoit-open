<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\View;

use idoit\Model\Dao\Base as DaoBase;

class ReactRenderable implements Renderable
{
    private $component;

    /**
     * @var array
     */
    private $params;

    public function __construct($component, array $params = [])
    {
        $this->component = $component;
        $this->params = $params;
    }

    /**
     * Process view details, do smarty assignments, and so on..
     *
     * @return Renderable
     */
    public function process(\isys_module $p_module, \isys_component_template $template, DaoBase $p_model)
    {
        $template->assign('reactComponent', $this->component)
            ->assign('reactParams', $this->params)
            ->include_template('contentbottomcontent', 'react.tpl');
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return Renderable
     */
    public function render()
    {
        return $this;
    }
}
