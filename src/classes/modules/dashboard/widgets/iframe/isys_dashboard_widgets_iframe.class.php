<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_iframe extends isys_dashboard_widgets
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
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Init method.
     *
     * @param  array $p_config
     *
     * @return $this
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/iframe.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param  array   $p_row The current widget row from "isys_widgets".
     * @param  integer $p_id  The ID from "isys_widgets_config".
     *
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function load_configuration(array $p_row, $p_id)
    {
        return $this->m_tpl->activate_editmode()
            ->assign('title', isys_application::instance()->container->get('language')->get('LC__WIDGET__IFRAME__CONFIG'))
            ->assign('rules', $this->m_config)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Render method.
     *
     * @param  string $p_unique_id
     *
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function render($p_unique_id)
    {
        $url = $this->m_config['url'];
        $sandbox = 'sandbox="allow-forms allow-pointer-lock allow-popups allow-scripts"';

        if (strpos($url, 'https://www.i-doit.com/') === 0) {
            $sandbox = '';
        }

        return $this->m_tpl
            ->assign('sandbox', $sandbox)
            ->assign('url', $url)
            ->assign('height', $this->m_config['height'])
            ->assign('title', $this->m_config['title'])
            ->fetch($this->m_tpl_file);
    }
}
