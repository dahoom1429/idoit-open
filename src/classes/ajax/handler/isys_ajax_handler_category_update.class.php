<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_category_update extends isys_ajax_handler
{
    /**
     * Initialization method.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function init()
    {
        isys_application::instance()->template->display("file:" . $this->m_smarty_dir . "templates/ajax/category_update.tpl");
        $this->_die();
    }

    /**
     * This method defines, if the hypergate has to be included for this handler.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public static function needs_hypergate()
    {
        return true;
    }
}