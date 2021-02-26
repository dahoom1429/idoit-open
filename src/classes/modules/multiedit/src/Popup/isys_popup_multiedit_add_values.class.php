<?php

class isys_popup_multiedit_add_values extends isys_component_popup
{

    /**
     * @var  isys_component_template
     */
    protected $template;

    /**
     * Handles SMARTY request for dialog plus lists and builds the list base on the specified table.
     *
     * @param   isys_component_template $tplclass
     * @param   array                   $params
     *
     * @return  string
     */
    public function handle_smarty_include(isys_component_template $tplclass, $params)
    {
        // Not used
        return null;
    }

    /**
     * Method for handling the module request.
     *
     * @param isys_module_request $modreq
     *
     * @return  null
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $modreq)
    {
        $gets = $modreq->get_gets();
        $ids = isys_format_json::decode($gets['ids']);
        $categoryInfo = $gets['category'];

        list($categoryIdInfo, $categoryClass) = explode(':', $categoryInfo);

        $language = isys_application::instance()->container->get('language');
        $key = $language->get('LC__UNIVERSAL__SELECTED_OBJECTS');

        $map = [
            $key => [],
            '-1' => 'Alle',
        ];

        $dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));

        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $map[$key][$id] = $dao->get_obj_name_by_id_as_string($id);
            }
        }

        $rules = [
            'add_values-object-selection' => [
                'p_arData' => $map
            ]
        ];

        $this->template
            ->activate_editmode()
            ->assign('allObjectIds', $gets['ids'])
            ->assign('categoryClass', $categoryClass)
            ->assign('categoryInfo', $categoryIdInfo)
            ->smarty_tom_add_rules('tom.popup.add_values', $rules)
            ->display(isys_module_multiedit::getPath() . 'templates/popups/add_values.tpl');
        die;
    }

    /**
     * Constructor method.
     */
    public function __construct()
    {
        parent::__construct();

        $this->template = isys_application::instance()->container->get('template');
    }
}
