<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_template_content extends isys_ajax_handler
{
    /**
     * Includes handling of special templates
     *
     * @var array
     */
    private $extraTemplates = [
        -2 => [
            'title' => 'LC__MASS_CHANGE__ACTIONS__ARCHIVE__TITLE',
            'extra' => 'LC__MASS_CHANGE__ACTIONS__ARCHIVE__SUBTITLE'
        ],
        -3 => [
            'title' => 'LC__MASS_CHANGE__ACTIONS__DELETE__TITLE',
            'extra' => 'LC__MASS_CHANGE__ACTIONS__DELETE__SUBTITLE'
        ],
        -4 => [
            'title' => 'LC__MASS_CHANGE__ACTIONS__PURGE__TITLE',
            'extra' => 'LC__MASS_CHANGE__ACTIONS__PURGE__SUBTITLE'
        ],
    ];

    /**
     * Initialization method.
     *
     * @return  boolean
     */
    public function init()
    {
        $language = isys_application::instance()->container->get('language');
        $templateId = (int) $_GET['template_id'];

        if (defined('C__MODULE__TEMPLATES')) {
            if ($templateId > 0) {
                // Object data.
                $globalCategory = new isys_cmdb_dao_category_g_global(isys_application::instance()->container->get('database'));
                $globalCategoryData = $globalCategory->get_data(null, $templateId)->get_row();

                // Object Type.
                $objectTypeId = $globalCategory->get_objTypeID($templateId);
                $objectTypeName = $globalCategory->get_objtype_name_by_id_as_string($objectTypeId);

                // Object image.
                $objectImage = (new isys_smarty_plugin_object_image())->navigation_view(isys_application::instance()->container->get('template'), [
                    'objType' => $objectTypeId,
                    'width'   => 45,
                    'height'  => 45
                ]);

                // Get affected categories.
                $affectedCategories = (new isys_module_templates())->get_affected_categories($templateId, $objectTypeId);

                // Output.
                echo '<table class="vat mr10">' .
                    '<colgroup><col width="55" /><col width="120" /><col width="180" /><col width="150" /></colgroup>' .
                    '<tr>' .
                    '<td rowspan="2">' . $objectImage . '</td>' .
                    '<td valign="top" class="bold">Name:</td>' .
                    '<td valign="top">' . $globalCategoryData['isys_obj__title'] . '</td>' .
                    '<td valign="top" class="bold">' . $language->get('LC__CMDB__AFFECTED_CATEGORIES') . ':</td>' .
                    '<td valign="top">' . implode(', ', $affectedCategories) . '</td>' .
                    '</tr><tr>' .
                    '<td class="bold">' . $language->get('LC__CMDB__OBJTYPE') . ':</td>' .
                    '<td>' . $language->get($objectTypeName) . '</td>' .
                    '<td class="bold">' . $language->get('LC__TASK__DETAIL__WORKORDER__CREATION_DATE') . ':</td>' .
                    '<td>' . $globalCategoryData['isys_obj__created'] . ' ' . strtolower($language->get('LC_UNIVERSAL__FROM')) . ' ' . $globalCategoryData['isys_obj__created_by'] . "</td>" .
                    '</tr>' . '</table>';
            } elseif (isset($this->extraTemplates[$templateId])) {
                $template = $this->extraTemplates[$templateId];

                echo '<div class="vat mr15"><strong>' . $language->get($template['title']) . '</strong><p>' . $language->get($template['extra']) . '</p></div>';
            }
        }

        $this->_die();

        return true;
    }
}
