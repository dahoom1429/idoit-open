<?php

namespace idoit\Component\Table\Pagerfanta\View;

use Pagerfanta\View\DefaultView;

/**
 * View for Pagerfanta results
 *
 * @package     idoit\Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class IdoitView extends DefaultView
{
    /**
     * @var IdoitTemplate
     */
    protected $template;

    /**
     * @return IdoitTemplate
     */
    protected function createDefaultTemplate()
    {
        return new IdoitTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Idoit';
    }
}