<?php

namespace idoit\Module\System\SettingPage;

/**
 * Class SettingPage
 *
 * @package idoit\Module\System\SettingPage
 */
abstract class SettingPage implements SettingPageInterface
{
    /**
     * @var \isys_component_template
     */
    protected $tpl;

    /**
     * @var \isys_component_database
     */
    protected $db;

    /**
     * @var \isys_component_template_language_manager
     */
    protected $lang;

    /**
     * SettingPage constructor.
     *
     * @param \isys_component_template                  $template
     * @param \isys_component_database                  $database
     * @param \isys_component_template_language_manager $language
     */
    public function __construct(\isys_component_template $template, \isys_component_database $database, \isys_component_template_language_manager $language)
    {
        $this->tpl = $template;
        $this->db = $database;
        $this->lang = $language;
    }
}