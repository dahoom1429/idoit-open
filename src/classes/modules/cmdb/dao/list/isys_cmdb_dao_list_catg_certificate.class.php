<?php

/**
 * i-doit
 *
 * DAO: Category list for certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_certificate extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Get category id
     *
     * @return int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CERTIFICATE');
    }

    /**
     * Get category type id
     *
     * @return int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Formats isys_catg_certificate_list__created and isys_catg_certificate_list__expire to the specified date configuration.
     *
     * @param array $data
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function modify_row(&$data)
    {
        if (!empty($data['isys_catg_certificate_list__created'])) {
            $data['isys_catg_certificate_list__created'] = isys_application::instance()->container->locales->fmt_date($data['isys_catg_certificate_list__created']);
        } else {
            $data['isys_catg_certificate_list__created'] = isys_tenantsettings::get('gui.empty_value', '-');
        }

        if (!empty($data['isys_catg_certificate_list__expire'])) {
            $data['isys_catg_certificate_list__expire'] = isys_application::instance()->container->locales->fmt_date($data['isys_catg_certificate_list__expire']);
        } else {
            $data['isys_catg_certificate_list__expire'] = isys_tenantsettings::get('gui.empty_value', '-');
        }
    }

    /**
     * Returns array with database fields and language constants for the list component.
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_fields()
    {
        return [
            'isys_catg_certificate_list__common_name' => 'LC__CMDB__CATG__CERTIFICATE__COMMON_NAME',
            'isys_certificate_type__title'            => 'LC__CMDB__CATG__TYPE',
            'isys_catg_certificate_list__created'     => 'LC__CMDB__CATG__CERTIFICATE__CREATE_DATE',
            'isys_catg_certificate_list__expire'      => 'LC__CMDB__CATG__CERTIFICATE__EXPIRE_DATE',
        ];
    }
}