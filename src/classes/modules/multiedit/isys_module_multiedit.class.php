<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * Listedit
 *
 * @package     i-doit
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_multiedit extends isys_module implements AuthableInterface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = true;
    const DISPLAY_IN_SYSTEM_MENU = false;
    const MAIN_MENU_REWRITE_LINK = true;

    /**
     * Variable which the module request class.
     *
     * @var  isys_module_request
     */
    protected $m_modreq;

    /**
     * Variable which holds the template component.
     *
     * @var  isys_component_template
     */
    protected $m_tpl;

    /**
     * Variable which holds the analytics DAO class.
     *
     * @var  isys_multiedit_dao
     */
    protected $m_dao;

    /**
     * Variable which holds the database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Initializes the module.
     *
     * @param isys_module_request & $p_req
     *
     * @return  isys_module_multiedit
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_modreq = $p_req;
        $this->m_db = $p_req->get_database();
        $this->m_tpl = $p_req->get_template()->assign('tpl_dir', __DIR__ . '/templates/main.tpl');
        $this->m_dao = new isys_multiedit_dao($this->m_db);

        return $this;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param isys_component_tree $p_tree
     * @param boolean             $p_system_module
     * @param integer             $p_parent
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     * @see     isys_module_cmdb->build_tree();
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        (new isys_module_cmdb)->build_tree($p_tree, $p_system_module, $p_parent);
    }

    /**
     * Build breadcrumb navifation
     *
     * @param &$p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        return [];
    }

    /**
     * @return $this|isys_module|isys_module_interface
     * @throws Exception
     */
    public function start()
    {
        if ((int)$_GET[C__GET__MODULE_ID] === defined_or_default('C__MODULE__MULTIEDIT')) {
            $controller = new \idoit\Module\Multiedit\Controller\Main($this);
            $view = $controller->handle(isys_register::factory('request'), isys_application::instance());
            $view->process($this, $this->m_modreq->get_template(), new \idoit\Module\Multiedit\Model\Dao(isys_application::instance()->container->get('database')));
            $view->render();
        }

        return $this;
    }

    /**
     * @return isys_auth_multiedit
     */
    public static function getAuth()
    {
        return isys_auth_multiedit::instance();
    }
}
