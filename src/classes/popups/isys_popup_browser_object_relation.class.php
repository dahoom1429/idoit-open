<?php

/**
 * i-doit
 *
 * Object browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_object_relation extends isys_popup_browser_object_ng
{
    /**
     * Make only relations selectable.
     * Example: (boolean) true, false.
     */
    const C__RELATION_ONLY = 'relationOnly';

    /**
     * This is mainly the same method as in the parent class, but we do a few quite specific things so we have to duplicate this whole method.
     *
     * @param   isys_module_request $p_modreq
     *
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Parameter retrieval.
        $l_params_decoded = base64_decode($_POST['params']);
        $l_params = isys_format_json::decode($l_params_decoded, true);

        // Parameter validation.
        if (is_array($l_params)) {
            $this->m_params = $l_params;

            try {

                // @see ID-6060 Sort by the configured field.
                $defaultSortingField = isys_tenantsettings::get('cmdb.object-browser.' . substr($this->m_params['hidden'], 0, -8) . '.defaultSortingFieldIndex', null);
                $defaultSortingDirection = isys_tenantsettings::get('cmdb.object-browser.' . substr($this->m_params['hidden'], 0, -8) . '.defaultSortingDirection', 'asc');

                // Assign some smarty configuration variables.
                $this->template->assign(self::C__MULTISELECTION, $l_params[self::C__MULTISELECTION])
                    ->assign(self::C__CALLBACK__ACCEPT, $l_params[self::C__CALLBACK__ACCEPT])
                    ->assign(self::C__CALLBACK__ABORT, $l_params[self::C__CALLBACK__ABORT])
                    ->assign(self::C__FORM_SUBMIT, $l_params[self::C__FORM_SUBMIT])
                    ->assign(self::C__TYPE_FILTER, $l_params[self::C__TYPE_FILTER])
                    ->assign('browser_title', $l_params[self::C__TITLE] ?: $this->language->get('LC__POPUP__BROWSER__OBJECT_BROWSER'))
                    ->assign('objectBrowserName', $this->m_params['name'])
                    ->assign('defaultSortingField', isys_format_json::encode($defaultSortingField))
                    ->assign('defaultSortingDirection', $defaultSortingDirection);

                // Automatically set the return element.
                if ((!isset($l_params[self::C__RETURN_ELEMENT]) || empty($l_params[self::C__RETURN_ELEMENT])) && isset($l_params['name'])) {
                    if (strpos($l_params['name'], '[') !== false && strpos($l_params['name'], ']') !== false) {
                        $l_tmp = explode('[', $l_params['name']);
                        $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
                        $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
                        unset($l_tmp);
                    } else {
                        $l_view = $l_params['name'] . '__VIEW';
                        $l_hidden = $l_params['name'] . '__HIDDEN';
                    }

                    $this->template
                        ->assign('return_element', $l_hidden)
                        ->assign('return_view', $l_view);
                } else {
                    $this->template->assign('return_element', $l_params[self::C__RETURN_ELEMENT]);
                }

                // Assign json encoded params.
                $this->template->assign('params', $l_params_decoded);

                // Call handlers.
                if (!$l_params[self::C__SELECTION] && $l_params['p_strSelectedID']) {
                    // @todo Why do we pack this inside an array !?
                    $l_params[self::C__SELECTION] = [$l_params['p_strSelectedID']];
                }

                $this->handle_preselection($l_params[self::C__SELECTION], $l_params[self::C__DATARETRIEVAL]);

                $objectTypeFilter = [];

                if (isset($this->m_params[self::C__TYPE_FILTER]) && !empty($this->m_params[self::C__TYPE_FILTER])) {
                    $objectTypeFilter = array_flip(explode(';', $this->m_params[self::C__TYPE_FILTER]));
                }

                // Preparations.
                $this->prepareConditionAssignments($objectTypeFilter);
            } catch (isys_exception_objectbrowser $e) {
                $this->template
                    ->assign('error', $e->getMessage())
                    ->assign('errorDetail', $e->getDetailMessage());
            } catch (Exception $e) {
                $this->template
                    ->assign('error', $e->getMessage());
            }
        } else {
            $this->template
                ->assign('error', 'Parameter error.');
        }

        // Javascript initialization
        $l_gets = $p_modreq->get_gets();

        // Disable the search for all second-selection browser.
        $this->m_tabconfig['location']['disabled'] = true;

        // Create the AJAX-string.
        $l_ajaxgets = [
            C__CMDB__GET__POPUP           => $l_gets[C__CMDB__GET__POPUP],
            C__GET__MODULE_ID             => defined_or_default('C__MODULE__CMDB'),
            C__CMDB__GET__CATG            => $l_gets[C__CMDB__GET__CATG],
            C__GET__AJAX_REQUEST          => 'handle_ajax_request',
            'request'                     => $l_params[self::C__SECOND_LIST],
        ];

        $this->template
            ->assign('ajax_url', isys_glob_build_url(isys_glob_http_build_query($l_ajaxgets)))
            ->assign('js_init', 'popup/object_relation.js')
            ->assign(self::C__SECOND_SELECTION, true)
            ->assign('tabs', $this->m_tabconfig)
            ->assign('relation_only', ($l_params[self::C__RELATION_ONLY] ? 1 : 0))
            ->display('popup/object_ng.tpl');
        die();
    }

    /**
     * Handles the smarty including and displays selected objects and a link to open the popup.
     *
     * @param  isys_component_template $p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template &$p_tplclass, $p_params)
    {
        // We don't need to copy the whole method, so we just do what we need and call the parent.
        if (!isset($p_params[self::C__SECOND_LIST])) {
            $p_params[self::C__SECOND_LIST] = [
                ['isys_cmdb_dao_category_g_relation::object_browser_get_data_by_object_and_relation_type'],
                $p_params[self::C__RELATION_FILTER]
            ];
        }

        $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_PRIMARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_SECONDARY_CONDITIONS];
        $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS] = (bool) $this->m_params[self::C__DISABLE_CUSTOM_CONDITIONS];

        return parent::handle_smarty_include($p_tplclass, $p_params);
    }
}
