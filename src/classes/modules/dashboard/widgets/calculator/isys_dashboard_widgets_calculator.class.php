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
class isys_dashboard_widgets_calculator extends isys_dashboard_widgets
{

    const C__WIDGET__CALCULATOR__NET    = 1;
    const C__WIDGET__CALCULATOR__MEMORY = 2;
    const C__WIDGET__CALCULATOR__RAID   = 3;
    const C__WIDGET__CALCULATOR__POWER  = 4;

    /**
     * Calculator types
     *
     * @var array
     */
    protected $m_calculator_types = [
        self::C__WIDGET__CALCULATOR__NET    => 'LC__WIDGET__CALCULATOR__NETWORK_BANDWIDTH_CALCULATOR',
        self::C__WIDGET__CALCULATOR__MEMORY => 'LC__WIDGET__CALCULATOR__MEMORY_CALCULATOR',
        self::C__WIDGET__CALCULATOR__RAID   => 'LC__WIDGET__CALCULATOR__RAID_CAPACITY_CALCULATOR',
        self::C__WIDGET__CALCULATOR__POWER  => 'LC__WIDGET__CALCULATOR__POWER'
    ];

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
     * Init method.
     *
     * @param   array $p_config
     *
     * @return  isys_dashboard_widgets_quicklaunch
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/calculator.tpl';

        return parent::init($p_config);
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
        $l_ajax_url = isys_helper_link::create_url([
            C__GET__AJAX_CALL => 'dashboard_widgets_calculator',
            C__GET__AJAX      => 1
        ]);

        return $this->m_tpl->activate_editmode()
            ->assign('ajax_url', $l_ajax_url)
            ->assign('unique_id', $p_unique_id)
            ->assign('tabledata', $this->m_config)
            ->assign('calculator_types', $this->m_calculator_types)
            ->fetch($this->m_tpl_file);
    }
}