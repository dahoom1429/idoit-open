<?php

namespace idoit\Controller;

use idoit\Component\ContainerFacade;
use idoit\Component\Provider\DiFactory;
use idoit\Component\Provider\DiInjectable;
use idoit\Context\Context;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * i-doit Base Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CatchallController extends Base
{

    /**
     * Factory with $container parameter
     *
     * @param ContainerFacade $container
     *
     * @return static
     */
    public static function factory(ContainerFacade $container)
    {
        $instance = new static();
        $instance->setDi($container);

        return $instance;
    }

    /**
     * Main Request handler
     *
     * @param \isys_register $p_request
     *
     * @throws \Exception
     */
    final public function handle(\isys_register $p_request)
    {
        Context::instance()->setOrigin(Context::ORIGIN_GUI);

        global $g_modman;

        /**
         * @todo remove this legacyRequest in future
         */
        $this->getDi()->legacyRequest = $p_request;

        if (isset($p_request->module)) {
            /**
             * Get module instance
             */
            if (($l_module_id = $g_modman->is_installed($p_request->module))) {

                /**
                 * Load and start the module
                 */
                $this->getDi()->application->module = $g_modman->load($l_module_id, $p_request);

                /**
                 * Preformat action to match class naming conventions
                 */
                $p_request->action = str_replace(' ', '', ucwords(str_replace('-', ' ', $p_request->action)));

                $loadClassName = 'idoit\\Module\\' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $p_request->module))) . '\\Controller\\';
                if (isset($p_request->action) && $p_request->action !== '') {
                    $loadClassName .= $p_request->action;
                } else {
                    $loadClassName .= 'Main';
                }

                /**
                 * Redirect to new controller
                 */
                if (class_exists($loadClassName)) {
                    /**
                     * @var $l_controller \isys_controller
                     */
                    $l_controller = new $loadClassName($this->getDi()->application->module);

                    if ($l_controller instanceof ContainerAwareInterface) {
                        $l_controller->setContainer($this->container);
                    }

                    // Call controller's pre route function
                    if (method_exists($l_controller, 'pre')) {
                        $l_controller->pre($p_request, $this);
                    }

                    if (isset($p_request->method) && $p_request->method !== '' && method_exists($l_controller, $p_request->method)) {
                        // Call controller's handler
                        //@todo: remove sending $this on future
                        $l_view = call_user_func([
                            $l_controller,
                            $p_request->method
                        ], $p_request, $this);
                    } else {
                        // Call controller's main handler
                        $l_view = $l_controller->handle($p_request, $this->getDi()->application);
                    }

                    // If controller is a NavbarHandable, also call onNew, onSave etc. events
                    if (is_a($l_controller, 'idoit\Controller\NavbarHandable')) {
                        $dispatcher = \idoit\Dispatcher\NavbarDispatcher::factory($this->getDi());

                        $l_view = $dispatcher->dispatch($l_controller, $p_request->get('POST')
                            ->get(C__GET__NAVMODE));
                    }

                    /**
                     * Process main tree
                     */
                    \idoit\Tree\TreeProcessor::factory($this->getDi())
                        ->process($l_controller, $p_request);

                    // Call controller's post route funciton
                    if (method_exists($l_controller, 'post')) {
                        $l_controller->post($p_request, $this);
                    }

                    /**
                     * Process and Render the view, if view is renderable
                     */
                    if ($l_view && is_a($l_view, 'idoit\View\Renderable', true)) {
                        $l_view->process($this->getDi()->application->module, $this->getDi()->template, $l_controller->dao($this->getDi()->application));

                        /* auto assign data
                        isys_component_template::instance()->assign(
                            'data', $l_view->getData()
                        );
                        */

                        $l_view->render();
                    }
                } else {
                    /**
                     * Load module via old deprecated way
                     *
                     * @deprecated
                     */
                    if (($l_mod_id = $g_modman->is_installed($p_request->module))) {
                        // Boot load the module in it's legacy way
                    } else {
                        // Call 404 handler
                        $this->getDi()->application->error404($p_request);
                    }
                }
            }
        } else {
            throw new \Exception('Request error for request ' . \isys_request_controller::instance()
                    ->path() . ' : ' . var_export($p_request, true));
        }
    }
}
