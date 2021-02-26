<?php

/**
 * i-doit
 *
 * Global cabling category UI class.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_virtual_cabling extends isys_cmdb_ui_category_g_virtual
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $isPro = (defined('C__MODULE__PRO') && C__MODULE__PRO);

        if ($isPro) {
            $this->processProView($p_cat);
        }

        $this->processCabling($p_cat);

        isys_component_template_navbar::getInstance()
            ->deactivate_all_buttons()
            ->hide_all_buttons();

        $this->get_template_component()
            ->assign('isPro', $isPro)
            ->assign('bShowCommentary', false);
    }

    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    private function processProView(isys_cmdb_dao_category $p_cat)
    {
        if (isys_core::is_ajax_request() && (isset($_POST['directlyRedirect']) || isset($_POST['dismissCablingInfo']))) {
            try {
                if (isset($_POST['directlyRedirect'])) {
                    isys_usersettings::set('gui.category.cabling.directly-open-cabling-addon', ($_POST['directlyRedirect'] ? 1 : 0));
                }

                if (isset($_POST['dismissCablingInfo'])) {
                    isys_usersettings::set('gui.category.cabling.display-cabling-info', 0);
                }

                $ajaxResult = [
                    'success' => true,
                    'data'    => null,
                    'message' => ''
                ];
            } catch (Exception $e) {
                $ajaxResult = [
                    'success' => false,
                    'data'    => null,
                    'message' => $e->getMessage()
                ];
            }

            header('Content-Type: application/json');
            echo isys_format_json::encode($ajaxResult);
            die;
        }

        $baseUrl = isys_application::instance()->www_path;
        $objectId = $p_cat->get_object_id();
        $isCablingInstalled = isys_module_manager::instance()
            ->is_installed('cabling');
        $isCablingActive = isys_module_manager::instance()
            ->is_active('cabling');

        $this->get_template_component()
            ->assign('ajaxUrl', isys_glob_add_to_query(C__GET__AJAX, 1))
            ->assign('objectId', $objectId)
            ->assign('baseUrl', $baseUrl)
            ->assign('isCablingInstalled', $isCablingInstalled)
            ->assign('isCablingActive', $isCablingActive)
            ->assign('displayCablingInfo', isys_usersettings::get('gui.category.cabling.display-cabling-info', 1))
            ->assign('openDirectlyInAddon', isys_usersettings::get('gui.category.cabling.directly-open-cabling-addon', 0));
    }

    /**
     * @param isys_cmdb_dao_category $p_cat
     */
    private function processCabling(isys_cmdb_dao_category $p_cat)
    {
        $cablingData = [];
        $cablingIteration = [];

        $daoConnector = isys_cmdb_dao_category_g_connector::instance($this->m_database_component);

        $result = $daoConnector->get_data(null, $p_cat->get_object_id(), '', null, C__RECORD_STATUS__NORMAL, 'isys_catg_connector_list__type', 'DESC');

        while ($row = $result->get_row()) {
            if ($row["isys_catg_connector_list__isys_catg_connector_list__id"]) {
                $cablingIteration[$row["isys_catg_connector_list__isys_catg_connector_list__id"]] = true;
            }

            if (isset($cablingIteration[$row["isys_catg_connector_list__id"]])) {
                continue;
            }

            // Cable run algorithm.
            $cableRun = $daoConnector->resolve_cable_run($row["isys_catg_connector_list__id"]);

            // Build cable run table.
            $leftConnections = $this->getChainReversed($cableRun[C__DIRECTION__LEFT], ' &larr; ');

            if (strlen($leftConnections)) {
                $leftConnections .= " &larr; ";
            }

            $rightConnections = '<table cellpadding="0" cellspacing="0"><tr><td>' . $this->get_chain($cableRun[C__DIRECTION__RIGHT], ' &rarr; ') . '</tr></td></table>';

            $cablingData[] = [
                'connection'       => $row['isys_catg_connector_list__title'],
                'leftConnections'  => $leftConnections,
                'rightConnections' => $rightConnections
            ];
        }

        usort($cablingData, function ($a, $b) {
            return strnatcasecmp($a['connection'], $b['connection']);
        });

        $this->get_template_component()
            ->assign('cablingData', $cablingData);
    }

    /**
     * @param array  $cableRun
     * @param string $separator
     * @param string $chain
     *
     * @return string
     */
    public function get_chain($cableRun, $separator = " &rarr; ", $chain = "")
    {
        // Print out object and connection title.
        $chain .= " " . $separator . " <a href=\"" . $cableRun["LINK"] . "\">" . $cableRun["CONNECTOR_TITLE"] . " (" . $cableRun["OBJECT_TITLE"] . ") " . "</a>";

        if (is_array($cableRun["CONNECTION"])) {
            // Recurse into this connection.
            $chain = $this->get_chain($cableRun["CONNECTION"], $separator, $chain);
        } else {
            if (is_array($cableRun["SIBLING"])) {
                // Every siblings needs a new TD for indentation.
                $chain .= "</td><td>";

                foreach ($cableRun["SIBLING"] as $l_sibling) {
                    // Recurse to get the complete chain.
                    $chain = $this->get_chain($l_sibling, $separator, $chain);

                    // Indent, if siblings are more than one.
                    if (is_countable($cableRun["SIBLING"]) && count($cableRun["SIBLING"]) > 1) {
                        $chain .= '</tr><tr rowspan="' . count($cableRun["SIBLING"]) . '">';

                        for ($i = 1;$i <= count($cableRun["SIBLING"]) - 1;$i++) {
                            $chain .= "<td></td>";
                        }

                        $chain .= "<td>";
                    }
                }
            }
        }

        return $chain;
    }

    /**
     * @param array  $cableRun
     * @param string $separator
     *
     * @return string
     */
    public function getChainReversed($cableRun, $separator = " &larr; ")
    {
        $l_chain = array_reverse($this->getChainAsArray($cableRun));

        return implode($separator, $l_chain);
    }

    /**
     * @param   array  $cableRun
     * @param   string $separator
     * @param   array  $chain
     *
     * @return  array
     */
    public function getChainAsArray($cableRun, $separator = " &larr; ", $chain = [])
    {
        if ($cableRun["OBJECT_TITLE"]) {
            $chain[] = "<a href=\"" . $cableRun["LINK"] . "\">" . $cableRun["CONNECTOR_TITLE"] . " (" . $cableRun["OBJECT_TITLE"] . ") " . "</a>";

            if (is_array($cableRun["CONNECTION"])) {
                $chain = $this->getChainAsArray($cableRun["CONNECTION"], $separator, $chain);
            } else {
                if (is_array($cableRun["SIBLING"])) {
                    $l_sibling = $cableRun["SIBLING"][0];
                    $chain = $this->getChainAsArray($l_sibling, $separator, $chain);
                }
            }
        }

        return $chain;
    }

    /**
     * isys_cmdb_ui_category_g_virtual_cabling constructor.
     *
     * @param isys_component_template $p_template
     */
    public function __construct(isys_component_template $p_template)
    {
        parent::__construct($p_template);

        $this->set_template('catg__virtual_cabling.tpl');
    }
}
