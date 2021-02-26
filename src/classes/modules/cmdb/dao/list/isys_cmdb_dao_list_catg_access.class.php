<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for access.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_access extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__ACCESS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * This methode is called for every row in the methode getTempTableHtml (class isys_component_list).
     *
     * @param array $p_row
     *
     * @throws isys_exception_database
     */
    public function modify_row(&$p_row)
    {
        $language = isys_application::instance()->container->get('language');
        $imageDirectory = isys_application::instance()->www_path . 'images/icons/silk/';

        $p_row['primary'] = '<span class="text-red">' .
            '<img src="' . $imageDirectory . 'bullet_red.png" class="vam" /> ' . $language->get('LC__UNIVERSAL__NO') .
            '</span>';

        if ($p_row['isys_catg_access_list__primary']) {
            $p_row['primary'] = '<span class="text-green">' .
                '<img src="' . $imageDirectory . 'bullet_green.png" class="vam" /> ' . $language->get('LC__UNIVERSAL__YES') .
                '</span>';
        }

        $p_row['url'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($p_row['isys_catg_access_list__url'])) {
            $link = isys_helper_link::prependProtocol(isys_helper_link::handle_url_variables($p_row['isys_catg_access_list__url'], $p_row['isys_catg_access_list__isys_obj__id']));

            // ID-1344  Adding "event.stopPropagation();" stops the browser from opening the category itself.
            $p_row['url'] = '<a href="' . $link . '" target="_blank" onclick="event.stopPropagation();">' .
                '<img src="' . $imageDirectory . 'link.png" class="vam" /> ' . $link .
                '</a>';
        }
    }

    /**
     * Method for receiving the field names.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_access_list__title' => 'LC__CMDB__CATG__ACCESS_TITLE',
            'isys_access_type__title'      => 'LC__CMDB__CATG__ACCESS_TYPE',
            'url'                          => 'LC__CMDB__CATG__ACCESS_URL',
            'primary'                      => 'LC__CMDB__CATG__ACCESS_PRIMARY'
        ];
    }
}
