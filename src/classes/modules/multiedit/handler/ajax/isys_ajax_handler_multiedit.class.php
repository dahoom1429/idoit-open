<?php
/**
 * i-doit
 *
 * AJAX controller
 *
 * @package     modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class isys_ajax_handler_multiedit extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = array(
            'success' => true,
            'message' => null,
            'data' => null
        );

        try
        {
            switch ($_GET['func'])
            {
                case 'function name':
                    $l_return['data'] = 'call a method here';
                    break;
            } // switch
        }
        catch (isys_exception $e)
        {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        } // try

        echo isys_format_json::encode($l_return);

        $this->_die();
    } // function


    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate ()
    {
        return true;
    } // function
} // class