<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_loggedinusers extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $m_tpl_file = '';

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
        $this->m_tpl_file = __DIR__ . '/templates/loggedinusers.tpl';

        return parent::init();
    }

    /**
     * Abstract render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function render($p_unique_id)
    {
        global $g_comp_database, $g_comp_session;

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

        $l_data = [];
        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                list($l_last_action_date, $l_last_action_time) = explode(' ', $l_row['last_action']);
                $l_last_action = $l_locales->fmt_date(strtotime($l_last_action_date)) . ' ' . $l_locales->fmt_time($l_last_action_time, false);

                $l_title = $l_row['username'];//($l_row['last_name'] != '')? $l_row['first_name'] . ', ' . $l_row['last_name']: $l_row['first_name'];

                $l_data[] = [
                    'title_link'  => $l_quicky->get_quick_info($l_row['id'], $l_title, C__LINK__OBJECT),
                    'last_action' => $l_last_action
                ];
            }
        }

        $l_ajax_url = isys_helper_link::create_url([
            C__GET__AJAX_CALL => 'dashboard_widgets_loggedinusers',
            C__GET__AJAX      => 1
        ]);

        return $this->m_tpl->assign('ajax_url', $l_ajax_url)
            ->assign('unique_id', $p_unique_id)
            ->assign('tabledata', $l_data)
            ->fetch($this->m_tpl_file);
    }
}