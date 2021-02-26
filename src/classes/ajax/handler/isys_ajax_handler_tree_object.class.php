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
class isys_ajax_handler_tree_object extends isys_ajax_handler_tree
{
    /**
     * Initialization for this AJAX request.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Method which defines, if the hypergate needs to be run.
     *
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }
}