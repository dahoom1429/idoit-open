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
class isys_dashboard_widgets_rss extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var  string
     */
    protected $configTemplateFile = '';

    /**
     * Path and Filename of the template.
     *
     * @var  string
     */
    protected $templateFile = '';

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
     * @param array $config
     *
     * @return $this
     */
    public function init($config = [])
    {
        $this->templateFile = __DIR__ . '/templates/rss.tpl';
        $this->configTemplateFile = __DIR__ . '/templates/config.tpl';

        return parent::init($config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $rules = [
            'url'   => $this->m_config['url'],
            'count' => $this->m_config['count']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', isys_application::instance()->container->get('language')
                ->get('LC__WIDGET__RSS__CONFIG'))
            ->assign('rules', $rules)
            ->fetch($this->configTemplateFile);
    }

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     */
    public function render($p_unique_id)
    {
        global $g_absdir, $g_config;

        $rssLibrary = new isys_library_simplepie();
        $rssLibrary->set_feed_url($this->m_config['url']);
        $rssLibrary->set_item_limit($this->m_config['count']);
        $rssLibrary->set_cache_location($g_absdir . '/temp');
        $rssLibrary->set_useragent(SIMPLEPIE_NAME . '/' . SIMPLEPIE_VERSION . ' via i-doit (Feed Parser; ' . SIMPLEPIE_URL . '; Allow like Gecko) Build/' . SIMPLEPIE_BUILD);
        $rssLibrary->set_output_encoding($g_config['html-encoding']);

        if (isys_settings::get('proxy.active', false)) {
            $rssLibrary->set_proxy(isys_settings::get('proxy.host'), isys_settings::get('proxy.port'), isys_settings::get('proxy.username'),
                isys_settings::get('proxy.password'));
        }

        $rssLibrary->init();

        return $this->m_tpl->assign('count', $this->m_config['count'])
            ->assign('dateFormat', isys_application::instance()->container->locales->get_date_format())
            ->assign('rss', $rssLibrary)
            ->fetch($this->templateFile);
    }
} 
