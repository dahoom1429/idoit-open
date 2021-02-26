<?php

/**
 * AJAX
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_keepalive extends isys_ajax_handler
{

    public function init()
    {
        $this->_die();

        return true;
    }
}

?>