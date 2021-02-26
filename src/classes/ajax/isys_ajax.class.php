<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  Ajax
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax
{
    /**
     * GET parameters.
     *
     * @var  array
     */
    protected $m_get;

    /**
     * POST parameters.
     *
     * @var array
     */
    protected $m_post;

    /**
     * Request string.
     *
     * @var  string
     */
    protected $m_request;

    /**
     * Variable which holds the different handlers.
     *
     * @var  array
     */
    private $m_handlers;

    /**
     * Registers a new handler
     *
     * @param  string $p_handler
     */
    public function register($p_handler)
    {
        $this->m_handlers[] = $p_handler;
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @param   string $p_call
     *
     * @return  boolean
     */
    public function needs_hypergate($p_call)
    {
        if (array_key_exists($p_call, $this->m_handlers)) {
            $l_class = $this->m_handlers[$p_call];
        } else {
            $l_class = 'isys_ajax_handler_' . $p_call;
        }

        if (class_exists($l_class)) {
            return call_user_func([
                $l_class,
                "needs_hypergate"
            ]);
        }

        // In case of doubt...
        return true;
    }

    /**
     * Initializes an ajax call.
     *
     * @param   string $p_call
     *
     * @global  array  $g_dirs
     * @return  mixed
     * @throws  isys_exception_general
     */
    public function init($p_call)
    {
        global $g_dirs;

        if (empty($p_call)) {
            return false;
        }

        $this->m_request = $p_call;

        try {
            $l_call = $this->call($this->m_request);

            if (!$l_call) {
                throw new isys_exception_general('There is no request-handler defined for "' . $this->m_request . '"!');
            } else {
                return $l_call;
            }
        } catch (isys_exception_general $e) {
            isys_application::instance()->template->assign('error', $e->getMessage())
                ->display('file:' . $g_dirs['smarty'] . 'templates/ajax/error.tpl');
        }

        return false;
    }

    /**
     * Calls an ajax handler (isys_ajax_handler_xy) and returns it's "init()" result.
     *
     * @param   string $p_request
     *
     * @return  mixed
     * @throws  Exception|isys_exception_general
     * @throws  isys_exception_general
     */
    private function call($p_request)
    {
        if (isset($p_request) && preg_match("/[a-z]+/i", $p_request)) {
            if (array_key_exists($p_request, $this->m_handlers)) {
                $l_class = $this->m_handlers[$p_request];
            } else {
                $l_class = 'isys_ajax_handler_' . $p_request;
            }

            try {
                if (class_exists($l_class)) {
                    try {
                        return (new $l_class($this->m_get, $this->m_post))->init(isys_module_request::get_instance());
                    } catch (isys_exception_general $e) {
                        throw $e;
                    }
                } else {
                    // Filter_var to stop XSS in this error output (which is directly displayed as HTML).
                    throw new isys_exception_general(filter_var($l_class, FILTER_SANITIZE_STRING) . ' was not found.');
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Constructor.
     *
     * @param  array $p_get
     * @param  array $p_post
     */
    public function __construct($p_get = null, $p_post = null)
    {
        $this->m_handlers = ['different' => 'isys_module_cmdb'];
        $this->m_get = $p_get;
        $this->m_post = $p_post;
    }
}