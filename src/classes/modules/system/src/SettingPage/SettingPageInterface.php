<?php

namespace idoit\Module\System\SettingPage;

/**
 * Interface SettingPageInterface.
 *
 * @package idoit\Module\System\SettingPage
 */
interface SettingPageInterface
{
    /**
     * @param integer $navMode
     *
     * @return mixed
     */
    public function renderPage($navMode);
}