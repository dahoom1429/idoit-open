<?php

/**
 * i-doit
 * DAO: Custom category list
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_custom_fields extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * @var array
     */
    private $m_config = [];

    /**
     * @var array
     */
    private $m_properties = [];

    /**
     * @var array
     */
    private $m_rows = [];

    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CUSTOM_FIELDS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Overwrite this for special count Handling.
     *
     * @return  array  Counts of several Status
     */
    public function get_rec_counts()
    {
        if ($this->m_rec_counts) {
            return $this->m_rec_counts;
        } else {
            $l_normal = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );
            $l_archived = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__ARCHIVED,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );
            $l_deleted = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__DELETED,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );

            $this->m_rec_counts = [
                C__RECORD_STATUS__NORMAL   => ($l_normal) ? $l_normal->num_rows() : 0,
                C__RECORD_STATUS__ARCHIVED => ($l_archived) ? $l_archived->num_rows() : 0,
                C__RECORD_STATUS__DELETED  => ($l_deleted) ? $l_deleted->num_rows() : 0,
            ];

            if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
                $l_template = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__TEMPLATE);
                $this->m_rec_counts[C__RECORD_STATUS__TEMPLATE] = ($l_template) ? $l_template->num_rows() : 0;
            }

            return $this->m_rec_counts;
        }
    }

    /**
     * Get result for list.
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_recStatus
     * @param   integer $p_config_id
     * @param   string  $p_condition
     * @param   string  $p_additional
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id, $p_recStatus = null, $p_config_id = null, $p_condition = null, $p_additional = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			AND isys_catg_custom_fields_list__status = ' . $this->convert_sql_int($p_recStatus) . ' ';

        if ($p_condition !== null) {
            $l_sql .= $p_condition . ' ';
        }

        if ($p_config_id !== null) {
            $l_sql .= 'AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_config_id) . ' ';
        }

        if ($p_additional !== null) {
            $l_sql .= $p_additional;
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method which build the row link
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function make_row_link()
    {
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        $l_gets[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        $l_gets['cateID'] = '[{id}]';

        return urldecode(isys_helper_link::create_catg_url($l_gets));
    }

    /**
     * Method for retrieving the displayable fields.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function get_fields()
    {
        $l_arr = [];
        $l_arr['id'] = 'ID';

        foreach ($this->m_properties as $l_prop_key => $l_property) {
            // Continue if field should not be visible in list.
            if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['show_in_list']) && !$l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['show_in_list']) {
                continue;
            }

            if ($l_prop_key != 'description' && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'hr' && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'html') {
                $l_arr[$l_prop_key] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
            }
        }

        return $l_arr;
    }

    /**
     * Sets properties.
     *
     * @param   array $p_properties
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_properties($p_properties)
    {
        $this->m_properties = $p_properties;

        return $this;
    }

    /**
     * Sets row.
     *
     * @param   array $p_row
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_rows($p_row)
    {
        $this->m_rows = $p_row;

        return $this;
    }

    /**
     * Gets row.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_rows()
    {
        return $this->m_rows;
    }

    /**
     * Sets custom category config.
     *
     * @param   array $p_config
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_config($p_config)
    {
        $this->m_config = $p_config;

        return $this;
    }

    /**
     * Reformats row for the list output.
     *
     * @param   integer $p_object_id
     *
     * @return  array|null
     * @throws  Exception
     * @throws  isys_exception_database
     * @throws  isys_exception_locale
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function reformat_rows($p_object_id)
    {
        $l_data_id = $l_arr = null;
        $l_req_obj = isys_request::factory()
            ->set_object_id($p_object_id);

        $l_quicklink = new isys_ajax_handler_quick_info();
        $l_counter = 0;

        if (is_array($this->m_rows) && count($this->m_rows) > 0) {
            $l_arr = [];

            $l_dao_custom_fields = isys_cmdb_dao_category_g_custom_fields::instance(isys_application::instance()->database);
            $l_already_used_keys = [];

            foreach ($this->m_rows as $l_key => $l_row) {
                $l_dao_custom_fields->set_catg_custom_id($l_row[0]['isys_catg_custom_fields_list__isysgui_catg_custom__id']);
                foreach ($this->m_properties as $l_prop_key => $l_property) {
                    $l_identifier = substr($l_prop_key, strpos($l_prop_key, '_c') + 1, strlen($l_prop_key));
                    $l_type = substr($l_prop_key, 0, strpos($l_prop_key, '_c'));
                    $l_arr[$l_counter][$l_prop_key] = null;

                    foreach ($l_row as $l_row_key => $l_val) {
                        $l_data_id = $l_val['isys_catg_custom_fields_list__data__id'];
                        if ($l_val['isys_catg_custom_fields_list__field_key'] == $l_identifier && $l_val['isys_catg_custom_fields_list__field_type'] == $l_type) {
                            switch ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                                case C__TYPE__DATE:
                                    // @see  ID-4984  Prepend a hidden date in format "yyyy-mm-dd".
                                    $l_arr[$l_counter][$l_prop_key] = '<span data-sort="' . htmlentities($l_val['isys_catg_custom_fields_list__field_content']) . '">' .
                                        isys_application::instance()->container->locales->fmt_date($l_val['isys_catg_custom_fields_list__field_content']) . '</span>';
                                    break;

                                case C__TYPE__DATE_TIME:
                                    // @see  ID-7507  New "date + time" property
                                    $l_arr[$l_counter][$l_prop_key] = '<span data-sort="' . htmlentities($l_val['isys_catg_custom_fields_list__field_content']) . '">' .
                                        isys_application::instance()->container->locales->fmt_datetime($l_val['isys_catg_custom_fields_list__field_content']) .
                                        '</span>';
                                    break;

                                case C__TYPE__INT:
                                    switch ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup']) {
                                        case 'browser_object':
                                        case 'file':
                                            if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] > 0) {
                                                if (!isset($l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                    $l_val['isys_catg_custom_fields_list__data__id']])) {
                                                    $l_objects = $l_dao_custom_fields->get_assigned_entries(
                                                        $l_val['isys_catg_custom_fields_list__field_key'],
                                                        $l_val['isys_catg_custom_fields_list__data__id']
                                                    );

                                                    $l_arr[$l_counter][$l_prop_key] = '<ul>';

                                                    foreach ($l_objects as $l_obj_id) {
                                                        $l_arr[$l_counter][$l_prop_key] .= '<li>' .
                                                            $l_quicklink->get_quick_info(
                                                                $l_obj_id,
                                                                $l_dao_custom_fields->get_obj_name_by_id_as_string($l_obj_id),
                                                                C__LINK__OBJECT
                                                            ) . '</li>';
                                                    }

                                                    $l_arr[$l_counter][$l_prop_key] .= '</ul>';
                                                    $l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                    $l_val['isys_catg_custom_fields_list__data__id']] = true;
                                                }
                                            } else {
                                                $l_arr[$l_counter][$l_prop_key] = $l_quicklink->get_quick_info(
                                                    $l_val['isys_catg_custom_fields_list__field_content'],
                                                    $l_dao_custom_fields->get_obj_name_by_id_as_string($l_val['isys_catg_custom_fields_list__field_content']),
                                                    C__LINK__OBJECT
                                                );
                                            }
                                            break;
                                        case 'calendar':
                                            $l_arr[$l_counter][$l_prop_key] = isys_locale::get_instance()
                                                ->fmt_date($l_val['isys_catg_custom_fields_list__field_content']);
                                            break;
                                        default:
                                            // dialog plus
                                            $l_callback_obj = $l_property[C__PROPERTY__UI]['params']['p_arData'];

                                            if ($l_callback_obj instanceof isys_callback) {
                                                if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] > 0) {
                                                    if (!isset($l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                        $l_val['isys_catg_custom_fields_list__data__id']])) {
                                                        $l_dialog_ids = $l_dao_custom_fields->get_assigned_entries(
                                                            $l_val['isys_catg_custom_fields_list__field_key'],
                                                            $l_val['isys_catg_custom_fields_list__data__id']
                                                        );

                                                        // Set assigned dialog ids to limit the result
                                                        $l_req_obj->set_data('selectedDialogIds', $l_dialog_ids);

                                                        // Execute data callback
                                                        $l_data = $l_callback_obj->execute($l_req_obj);

                                                        $l_arr[$l_counter][$l_prop_key] = '<ul>';
                                                        foreach ($l_dialog_ids as $l_id) {
                                                            if (isset($l_data[$l_id])) {
                                                                $l_arr[$l_counter][$l_prop_key] .= '<li>' . $l_data[$l_id] . '</li>';
                                                            }
                                                        }
                                                        $l_arr[$l_counter][$l_prop_key] .= '</ul>';
                                                        $l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                        $l_val['isys_catg_custom_fields_list__data__id']] = true;
                                                    }
                                                } elseif ($l_val['isys_catg_custom_fields_list__field_content']) {
                                                    // Set selected dialog ids
                                                    $l_req_obj->set_data('selectedDialogIds', [$l_val['isys_catg_custom_fields_list__field_content']]);

                                                    // Execute data callback
                                                    $l_data = $l_callback_obj->execute($l_req_obj);

                                                    if (isset($l_data[$l_val['isys_catg_custom_fields_list__field_content']])) {
                                                        $l_arr[$l_counter][$l_prop_key] = $l_data[$l_val['isys_catg_custom_fields_list__field_content']];
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                    break;
                                case C__TYPE__TEXT:
                                case C__TYPE__TIME:
                                case C__TYPE__TEXT_AREA:
                                    switch ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) {
                                        case 'hr':
                                            continue 3;
                                            break;
                                        case 'f_link':
                                            if (!empty($l_val['isys_catg_custom_fields_list__field_content'])) {
                                                $l_param = [
                                                    'p_strValue'        => $l_val['isys_catg_custom_fields_list__field_content'],
                                                    'p_strTarget'       => '_blank',
                                                    'p_bInfoIconSpacer' => '0'
                                                ];
                                                unset($_GET[C__CMDB__GET__EDITMODE]);

                                                $l_arr[$l_counter][$l_prop_key] = isys_factory::get_instance('isys_smarty_plugin_f_link', $this->m_db)
                                                    ->navigation_view(isys_application::instance()->template, $l_param);
                                            }
                                            break;
                                        case 'password':
                                            $l_arr[$l_counter][$l_prop_key] = '*****';
                                            break;
                                        default:
                                            $l_arr[$l_counter][$l_prop_key] = nl2br($l_val['isys_catg_custom_fields_list__field_content']);
                                            break;
                                    }
                                    break;
                            }

                            if (!isset($l_arr[$l_counter][$l_prop_key])) {
                                $l_arr[$l_counter][$l_prop_key] = null;
                            }

                            unset($l_row[$l_row_key]);
                            continue 2;
                        }
                    }
                }

                $l_arr[$l_counter]['id'] = $l_data_id;
                $l_counter++;
            }
        }

        return $l_arr;
    }
}
