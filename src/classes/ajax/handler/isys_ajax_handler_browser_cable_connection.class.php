<?php

class isys_ajax_handler_browser_cable_connection extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Illia Polianskyi
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'load_connector_name':
                    $l_return['data'] = $this->load_connector_name($_POST['connector_id']);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * @param $connectorId
     *
     * @return string
     */
    public function load_connector_name($connectorId)
    {
        return isys_cmdb_action_category_update::getConnectorNameByConnectorId($connectorId);
    }
}
