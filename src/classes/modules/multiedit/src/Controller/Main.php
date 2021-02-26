<?php

namespace idoit\Module\Multiedit\Controller;

use idoit\Module\Multiedit\Model\Dao;

class Main implements \isys_controller
{
    /**
     * @var \isys_module_multiedit
     */
    private $module;

    /**
     * Default request handler, gets called in every "/cabling" request.
     *
     * @param \isys_register    $request
     * @param \isys_application $application
     *
     * @return  \idoit\View\Renderable|void
     */
    public function handle(\isys_register $request, \isys_application $application)
    {
        return new \idoit\Module\Multiedit\View\Main($request);
    }

    /**
     * @param \isys_application $p_application
     *
     * @return  \idoit\Module\Multiedit\Model\Dao
     */
    public function dao(\isys_application $p_application)
    {
        return Dao::instance($p_application->database);
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
     * Main constructor.
     *
     * @param \isys_module $p_module
     */
    public function __construct(\isys_module $p_module)
    {
        $this->module = $p_module;
    }
}
