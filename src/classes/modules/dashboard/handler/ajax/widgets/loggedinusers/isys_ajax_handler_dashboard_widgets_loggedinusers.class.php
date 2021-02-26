<?php

/**
 * Example ajax handler for the widget bookmark
 * To use the widget ajax handler
 *
 * @author Van Quyen Hoang <qhoang@i-doit.org>
 */
class isys_ajax_handler_dashboard_widgets_loggedinusers extends isys_ajax_handler_dashboard
{
    /**
     * Iniit method
     */
    public function init()
    {
        global $g_comp_session, $g_comp_database;

        try {
            $l_locales = isys_locale::get_instance();
        } catch (Exception $e) {
            $l_locales = isys_locale::get($g_comp_database, $g_comp_session->get_user_id());
        }

        $l_quicky = new isys_ajax_handler_quick_info();

        $l_dao = isys_cmdb_dao::instance($g_comp_database);

        $l_time = time() - 300; // 5 minutes
        $l_datetime = date('Y-m-d H:i:s', $l_time);

        $l_sql = 'SELECT isys_cats_person_list__isys_obj__id AS id, isys_cats_person_list__title AS username,
			isys_cats_person_list__first_name AS first_name, isys_cats_person_list__last_name AS last_name,
			MAX(isys_user_session__time_last_action) AS last_action FROM isys_cats_person_list
			INNER JOIN isys_user_session ON isys_user_session__isys_obj__id = isys_cats_person_list__isys_obj__id
			GROUP BY isys_user_session__isys_obj__id
			HAVING last_action > ' . $l_dao->convert_sql_text($l_datetime);

        $l_res = $l_dao->retrieve($l_sql);
        $l_options = '';

        if (count($l_res) > 0) {
            $l_counter = 1;
            while ($l_row = $l_res->get_row()) {
                $l_options .= '<tr class="' . (($l_counter % 2 == 0) ? 'CMDBListElementsEven' : 'CMDBListElementsOdd') . '">';

                list($l_last_action_date, $l_last_action_time) = explode(' ', $l_row['last_action']);
                $l_last_action = $l_locales->fmt_date(strtotime($l_last_action_date)) . ' ' . $l_locales->fmt_time($l_last_action_time, false);

                $l_title = $l_row['username'];//($l_row['last_name'] != '')? $l_row['first_name'] . ', ' . $l_row['last_name']: $l_row['first_name'];

                $l_options .= '<td>' . $l_quicky->get_quick_info($l_row['id'], $l_title, C__LINK__OBJECT) . '</td>';
                $l_options .= '<td>' . $l_last_action . '</td>';

                $l_options .= '</tr>';
                $l_counter++;
            }
        }

        echo $l_options;
        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    }
}