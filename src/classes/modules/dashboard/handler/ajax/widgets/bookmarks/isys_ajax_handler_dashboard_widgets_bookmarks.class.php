<?php

/**
 * Example ajax handler for the widget bookmark
 * To use the widget ajax handler
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.2.0
 */
class isys_ajax_handler_dashboard_widgets_bookmarks extends isys_ajax_handler_dashboard
{
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        // do something

        $this->update_widget($_POST[C__GET__ID], $_POST['config'], $_POST['unique_id']);

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => $_POST['config']
        ];

        echo isys_format_json::encode($l_return);
        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    }
}