<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage Popups
 * @author     Dennis Stücken <dstuecken@synetics.de> 2010-08
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_objectpurge extends isys_component_popup
{
    /**
     * @param isys_component_template $p_tplclass
     * @param                         $p_params
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // This is never used - the popup will directly be triggered via JS callback.
    }

    /**
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        $affectedObjects = [];
        $l_cmdb_dao = new isys_cmdb_dao($this->database);
        $parameters = isys_format_json::decode(base64_decode($_POST['parameters']));

        if (is_array($parameters['objects'])) {
            foreach ($parameters['objects'] as $objectId) {
                if ($objectId > 0) {
                    $affectedObjects[$objectId] = $l_cmdb_dao->get_obj_name_by_id_as_string($objectId);
                }
            }
        }

        $this->template
            ->assign('message', $parameters['message'])
            ->assign('headline', $parameters['headline'])
            ->assign('objects', $affectedObjects)
            ->display('popup/objectpurge.tpl');
        die;
    }
}
