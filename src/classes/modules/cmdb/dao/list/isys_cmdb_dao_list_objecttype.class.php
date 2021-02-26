<?php

/**
 * i-doit
 *
 * DAO: ObjectType lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_objecttype extends isys_component_dao_object_table_list
{
    /**
     * Retrieve all obj_types.
     *
     * @param  string $p_strTableName
     * @param  int    $p_object_id
     * @param  int    $p_cRecStatus
     *
     * @todo   This is beeing used in the object type configuration, but not in the object list configuration.
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_result($p_strTableName = null, $p_object_id = null, $p_cRecStatus = null)
    {
        $l_sql = "SELECT
			isys_obj_type__id,
			isys_obj_type__title,
			isys_obj_type_group__title,
			isys_obj_type__color AS color,
			isys_obj_type__overview AS overview,
			isys_obj_type__container AS container,
			isys_obj_type__isysgui_cats__id AS cats,
			isysgui_cats__title AS cats_title,
			COUNT(isys_obj__id) AS object_count,
			isys_obj_type__show_in_tree AS show_in_tree
			FROM isys_obj_type
			LEFT JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_obj_type_group ON isys_obj_type__isys_obj_type_group__id = isys_obj_type_group__id
			LEFT JOIN isysgui_cats ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
			WHERE isys_obj_type__const != 'C__OBJTYPE__LOCATION_GENERIC' ";

        $l_allowed_objecttypes = isys_auth_cmdb_object_types::instance()->get_allowed_objecttype_configs();

        if (is_array($l_allowed_objecttypes) && count($l_allowed_objecttypes) > 0) {
            $l_sql .= ' AND isys_obj_type__id IN (' . implode(',', $l_allowed_objecttypes) . ') ';
        } elseif ($l_allowed_objecttypes === false) {
            $l_sql .= ' AND isys_obj_type__id = FALSE ';
        }

        if ($_GET[C__CMDB__GET__OBJECTGROUP]) {
            $l_sql .= " AND (isys_obj_type_group__id = " . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECTGROUP]) . ")";
        }

        $l_sql .= "GROUP BY isys_obj_type__id;";

        return $this->retrieve($l_sql);
    }

    /**
     * Method for modifying the single row-data.
     *
     * @param array &$row
     *
     * @todo  Depending on the context, different values will be used (object type config vs object list config).
     */
    public function modify_row(&$row)
    {
        static $objectTypeGroupCache = [];
        static $specificCategoryCache = [];

        $language = isys_application::instance()->container->get('language');

        if (!isset($row['isys_obj_type_group__title']) && isset($row['isys_obj_type__isys_obj_type_group__id'])) {
            if (!isset($objectTypeGroupCache[$row['isys_obj_type__isys_obj_type_group__id']])) {
                $objectTypeGroupQuery = 'SELECT isys_obj_type_group__title 
                    FROM isys_obj_type_group 
                    WHERE isys_obj_type_group__id = ' . $this->convert_sql_id($row['isys_obj_type__isys_obj_type_group__id']) . ';';

                $objectTypeGroupCache[$row['isys_obj_type__isys_obj_type_group__id']] = $this
                    ->retrieve($objectTypeGroupQuery)
                    ->get_row_value('isys_obj_type_group__title');
            }

            $row['isys_obj_type_group__title'] = $objectTypeGroupCache[$row['isys_obj_type__isys_obj_type_group__id']];
        }

        if (!isset($row['cats']) && isset($row['isys_obj_type__isysgui_cats__id'])) {
            if (!isset($specificCategoryCache[$row['isys_obj_type__isysgui_cats__id']])) {
                $specificCategoryQuery = 'SELECT isysgui_cats__title 
                    FROM isysgui_cats 
                    WHERE isysgui_cats__id = ' . $this->convert_sql_id($row['isys_obj_type__isysgui_cats__id']) . ';';

                $specificCategoryCache[$row['isys_obj_type__isysgui_cats__id']] = $this
                    ->retrieve($specificCategoryQuery)
                    ->get_row_value('isysgui_cats__title');
            }

            $row['cats_title'] = $specificCategoryCache[$row['isys_obj_type__isysgui_cats__id']];
        }

        if (isset($row['show_in_tree'])) {
            $row['show_in_tree'] = $row['show_in_tree']
                ? '<span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>'
                : '<span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>';
        } elseif (isset($row['isys_obj_type__show_in_tree'])) {
            $row['show_in_tree'] = $row['isys_obj_type__show_in_tree']
                ? '<span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>'
                : '<span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>';
        }

        if (isset($row['overview'])) {
            $row['overview'] = $row['overview']
                ? 'LC__UNIVERSAL__YES'
                : 'LC__UNIVERSAL__NO';
        } elseif (isset($row['isys_obj_type__overview'])) {
            $row['overview'] = $row['isys_obj_type__overview']
                ? 'LC__UNIVERSAL__YES'
                : 'LC__UNIVERSAL__NO';
        }

        if (isset($row['container'])) {
            $row['container'] = $row['container']
                ? 'LC__UNIVERSAL__YES'
                : 'LC__UNIVERSAL__NO';
        } elseif (isset($row['isys_obj_type__container'])) {
            $row['container'] = $row['isys_obj_type__container']
                ? 'LC__UNIVERSAL__YES'
                : 'LC__UNIVERSAL__NO';
        }

        $row['cats'] = $row['cats_title'] ?: isys_tenantsettings::get('gui.empty_value', '-');

        $color = $row['color'] ?: $row['isys_obj_type__color'];

        $row['isys_obj_type__title'] = '<span class="vam">' .
            '<div style="margin-left:15px;">' . $language->get($row['isys_obj_type__title']) . '</div>' .
            '<div class="cmdb-marker" style="position:absolute; top:5px; left:5px; background:#' . $color . ';"></div>' .
            '</span>';

        if (isset($row['object_count']) && $row['object_count'] !== null) {
            $count = $row['object_count'];
        } else {
            $count = (int)$this
                ->retrieve('SELECT COUNT(1) AS cnt FROM isys_obj WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($row['isys_obj_type__id']) . ';')
                ->get_row_value('cnt');
        }

        $row['counter'] = '<span class="text-grey">' . $count . '</span>';
    }

    /**
     * Method for returning the fields to display in the list.
     *
     * @return array
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_fields()
    {
        $language = isys_application::instance()->container->get('language');

        return [
            'isys_obj_type__id'          => 'LC__UNIVERSAL__ID',
            'isys_obj_type__title'       => 'LC__UNIVERSAL__TITLE',
            'isys_obj_type_group__title' => 'LC__CMDB__OBJTYPE__GROUP',
            'cats'                       => 'LC__REPORT__FORM__SELECT_PROPERTY_S',
            'overview'                   => 'LC__CMDB__CATG__OVERVIEW',
            'container'                  => 'LC__CMDB__OBJTYPE__LOCATION',
            'counter'                    => $language->get('LC_UNIVERSAL__OBJECT') . ' ' . $language->get('LC__POPUP__DUPLICATE__NUMBER'),
            'show_in_tree'               => 'LC__CMDB__OBJTYPE__SHOW_IN_TREE'
        ];
    }
}
