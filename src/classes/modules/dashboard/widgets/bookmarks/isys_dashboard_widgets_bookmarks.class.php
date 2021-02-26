<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_bookmarks extends isys_dashboard_widgets
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
     * Returns a boolean value, if the current widget has an own configuration page.
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
        $this->m_tpl_file = __DIR__ . '/templates/bookmarks.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        $this->set_ajax_url([
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'dashboard_widgets_bookmarks',
        ]);

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function load_configuration(array $p_row, $p_id)
    {
        return $this->m_tpl->activate_editmode()
            ->assign('title', isys_application::instance()->container->get('language')
                ->get('LC__WIDGET__MY_BOOKMARKS'))
            ->assign('bookmark_list', $this->m_config)
            ->assign('dialog_selection', get_smarty_arr_YES_NO())
            ->fetch($this->m_config_tpl_file);
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
        return $this->m_tpl->assign('unique_id', $p_unique_id)
            ->assign('tabledata', $this->m_config)
            ->fetch($this->m_tpl_file);
    }
}
