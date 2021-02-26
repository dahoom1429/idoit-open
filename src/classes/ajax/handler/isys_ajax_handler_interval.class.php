<?php

use idoit\Component\Interval\Config;

/**
 * AJAX handler for the Interval component
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.10.1
 */
class isys_ajax_handler_interval extends isys_ajax_handler
{
    /**
     * Init method.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                // Get a human readable
                case 'humanReadableInterval':
                    try {
                        $return['data'] = Config::byJSON($_POST['config'])
                            ->getHumanReadable(true);
                    } catch (Exception $e) {
                        $return['data'] = isys_application::instance()->container->get('language')
                            ->get('LC__INTERVAL__NO_INTERVAL_DEFINED');
                    }
                    break;
            }
        } catch (Exception $e) {
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($return);

        $this->_die();
    }
}
