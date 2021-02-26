<?php

/**
 * i-doit
 *
 * Dashboard widget class object information
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_properties extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $m_config_tpl_file = '';

    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

    /**
     * Returns the js script for the table
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function init($p_config = [])
    {
        // Set the cache lifetime to 60 seconds.
        isys_core::expire(isys_convert::MINUTE);

        $this->m_tpl_file = __DIR__ . '/templates/properties.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param  array   $p_row The current widget row from "isys_widgets".
     * @param  integer $p_id  The ID from "isys_widgets_config".
     *
     * @todo   Refactor the config parameters - they are no longer used/needed
     * @return string
     * @throws SmartyException
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $this->m_config['selected_props'] = isys_format_json::encode($this->m_config['selected_props']);

        $l_ajax_url = isys_helper_link::create_url([
            C__GET__AJAX_CALL => 'dashboard_widgets_properties',
            C__GET__AJAX      => 1
        ]);

        return $this->m_tpl->activate_editmode()
            ->assign('title', $this->language->get('LC__WIDGET__OBJECT_INFORMATION_LIST'))
            ->assign('config_data', $this->m_config)
            ->assign('provide', C__PROPERTY__PROVIDES__LIST)
            ->assign('ajax_url', $l_ajax_url)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Abstract render method.
     *
     * @param  string $p_unique_id
     *
     * @return string
     * @throws SmartyException
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public function render($p_unique_id)
    {
        $propertyDao = new isys_cmdb_dao_category_property_ng($this->database);
        $objectIds = isys_format_json::decode($this->m_config['obj_id']);
        $attributes = [];
        $header = [];

        if (is_array($this->m_config['config']) && count($this->m_config['config'])) {
            foreach ($this->m_config['config'] as $propertySet) {
                $property = $propertySet[1];
                $daoClass = explode('::', $propertySet[4])[0];

                if (class_exists($daoClass) && is_a($daoClass, 'isys_cmdb_dao_category', true)) {
                    /** @var  isys_cmdb_dao_category $dao */
                    $dao = new $daoClass($this->database);

                    $sql = 'SELECT isys_property_2_cat__id, isys_property_2_cat__prop_title
                        FROM isys_property_2_cat
                        WHERE isys_property_2_cat__cat_const = ' . $propertyDao->convert_sql_text($dao->get_category_const()) . '
                        AND isys_property_2_cat__prop_key = ' . $propertyDao->convert_sql_text($property) . '
                        LIMIT 1;';

                    $propertyRow = $propertyDao->retrieve($sql)->get_row();

                    $attributes[] = $propertyRow['isys_property_2_cat__id'];

                    $header[$daoClass . '__' . $property] = $propertyRow['isys_property_2_cat__prop_title'];
                }
            }
        }

        if (!is_array($objectIds)) {
            $objectIds = [];
        }

        $sql = $propertyDao->create_property_query_for_lists($attributes, null, $objectIds, [], true);

        $list = new isys_component_list(null, $propertyDao->retrieve($sql));

        $list->config($header, '', '', false, false);
        $list->disableCheckboxes()
            ->disableFilter()
            ->disableResizeColumns()
            ->setScoped(true)
            ->setAjaxMethod('post')
            ->setAjaxParams([
                'id' => $_POST['id'],
                'identifier' => 'properties',
                'unique_id' => $p_unique_id,
                'config' => $this->m_config,
            ])
            ->createTempTable();

        return $this->m_tpl
            ->assign('unique_id', $p_unique_id)
            ->assign('table', $list->getTempTableHtml())
            ->fetch($this->m_tpl_file);
    }

    /**
     * Replace placeholders for properties
     *
     * @param $row
     *
     * @return array
     */
    private function formatProperties(array $row)
    {
        foreach ($row as &$property) {
            $property = preg_replace([
                '/\{(#[0-9a-fA-F]{3,6})\}/', // Replace "{#123456}" with the cmdb-marker.
                '/([^\{\>,]+) \{([0-9]+)\}/', // Replace object links with format "Title {id}".
                '/[\w- ]+ \{1\}/' // Replace the "Title {1}" with the root location house.
            ], [
                '<div class="dynamic-replacement cmdb-marker" style="background-color: $1;"></div>',
                ' <a class="dynamic-replacement quickinfo" href="?objID=$2" data-object-id="$2">$1</a>',
                '<img class="vam" src="\' + window.dir_images + \'icons/silk/house.png">'
            ], $property);
        }

        return $row;
    }
}
