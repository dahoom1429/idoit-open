<?php

namespace idoit\Component\Table\Pagerfanta\View;

use Pagerfanta\View\Template\DefaultTemplate;

/**
 * Pagerfanta Template
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class IdoitTemplate extends DefaultTemplate
{
    /**
     * @param $results
     *
     * @return string
     */
    public function results($current, $max)
    {
        return '<span class="results">' . $current . '<span class="separator">/</span>' . $max . '</span>';
    }
}