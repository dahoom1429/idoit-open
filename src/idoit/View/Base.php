<?php

namespace idoit\View;

use isys_component_dao_result as DaoResult;

/**
 * i-doit View Base class
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class Base implements Renderable
{
    /**
     * @var DaoResult
     */
    protected $daoResult = null;

    /**
     * @var int
     */
    protected $id;

    /**
     * The path to the view file.
     *
     * Example: $this->paths['contentbottomcontent'] = $module->get_template_dir() . 'index.tpl';
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Request object
     *
     * @var \isys_request
     */
    protected $request;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setID($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return DaoResult
     */
    public function getDaoResult()
    {
        return $this->daoResult;
    }

    /**
     * @param DaoResult $p_data
     *
     * @return $this
     */
    public function setDaoResult(DaoResult $p_result)
    {
        $this->daoResult = $p_result;

        return $this;
    }

    /**
     * @return Base
     */
    public function render()
    {
        global $index_includes;

        if (is_array($this->paths)) {
            foreach ($this->paths as $key => $value) {
                if ($key) {
                    $index_includes[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * @param \isys_register        $request
     * @param \idoit\Model\Dao\Base $dao
     */
    public function __construct(\isys_register $request)
    {
        $this->request = $request;
    }

}