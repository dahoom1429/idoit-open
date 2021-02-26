<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Type;

use idoit\Module\Multiedit\Component\Multiedit\Renderable;
use idoit\Module\Multiedit\Component\Multiedit\Source\DataSource;
use idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource;
use idoit\Module\Multiedit\Component\Multiedit\Row\HeaderRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\ChangeAllRow;

/**
 * Class Type
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Type
 */
abstract class Type implements Renderable
{
    const CATEGORYTYPE = '';

    /**
     * @var bool
     */
    protected $multivalued = false;

    /**
     * @return string
     */
    public function getCategoryType()
    {
        return $this::CATEGORYTYPE;
    }

    /**
     * @return bool
     */
    public function isMultivalued()
    {
        return $this->multivalued;
    }

    /**
     * @param bool $multivalued
     *
     * @return Type
     */
    public function setMultivalued($multivalued)
    {
        $this->multivalued = $multivalued;
        return $this;
    }

    /**
     * @param array $objects
     * @param       $dataSource DataSource
     * @param       $data       PropertiesSource
     */
    public function render($objects, $dataSource, $data)
    {
    }

    /**
     * @param      $properties
     * @param      $objectId
     *
     * @return string
     */
    public function renderHeader($properties, $objectId)
    {
        // Callbacks
        $callbackRegister = \isys_register::factory('callbacks');

        $content = '<table class="mainTable border-bottom border-grey">';
        $content .= (new HeaderRow())->setProperties($properties)
            ->render();
        $content .= (new ChangeAllRow())->setObjectId($objectId)->setProperties($properties)
            ->render();

        // Register all Callbacks
        if ($callbackRegister->count()) {
            $callbackScript = "<script type='text/javascript'>";

            $callbacks = $callbackRegister->get();

            foreach ($callbacks as $observedTarget => $callback) {
                $callbackScript .= "idoit.callbackManager.registerCallback('{$observedTarget}.changed', function () { ";
                foreach ($callback as $call) {
                    $callbackScript .= $call;
                }
                $callbackScript .= '});';
            }

            $callbackScript .= '</script>';
        }

        return $content . '</table>' . $callbackScript;
    }
}
