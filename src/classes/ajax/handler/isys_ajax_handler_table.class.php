<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_table extends isys_ajax_handler
{
    /**
     * Method for initializing the AJAX request.
     */
    public function init()
    {
        header('Content-Type: application/json');

        try {
            $l_response = [
                'success' => true,
                'message' => null
            ];

            switch ($_GET['func']) {
                case 'saveColumnWidths':
                    $l_response['data'] = $this->saveColumnWidths($_GET['identifier'], isys_format_json::decode($_POST['columns']));
                    break;
                case 'saveTableWidth':
                    $l_response['data'] = $this->saveTableWidth($_GET['identifier'], isys_format_json::decode($_POST['table']));
                    break;
            }
        } catch (Exception $e) {
            $l_response = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        echo isys_format_json::encode($l_response);
        $this->_die();
    }

    /**
     * This method will save the column-widhts of a Table to the set identifier (will be placed inside the user settings).
     *
     * @param string $p_identifier
     * @param array  $p_columns
     *
     * @return boolean
     */
    protected function saveColumnWidths($p_identifier, array $p_columns)
    {
        isys_usersettings::set($p_identifier, $p_columns);

        return true;
    }

    /**
     * This method will save the widht of a Table to the set identifier (will be placed inside the user settings).
     *
     * @param string $p_identifier
     * @param string $p_table
     *
     * @return boolean
     */
    protected function saveTableWidth($p_identifier, $p_table)
    {
        isys_usersettings::set($p_identifier, $p_table);

        return true;
    }
}