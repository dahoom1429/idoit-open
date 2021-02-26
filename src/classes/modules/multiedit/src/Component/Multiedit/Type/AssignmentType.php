<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Type;

use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Renderable;
use idoit\Module\Multiedit\Component\Multiedit\Row\AssignmentRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\HeaderRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\ChangeAllRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\SimpleRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\EmptyRow;

/**
 * Class AssignmentType
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Type
 */
class AssignmentType extends Type
{
    const CATEGORYTYPE = 'Assignment';

    /**
     * @param array                                                               $objects
     * @param \idoit\Module\Multiedit\Component\Multiedit\Source\DataSource       $dataSource
     * @param \idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource $properties
     *
     * @return string
     */
    public function render($objects, $dataSource, $properties)
    {
        $content = '<table class="mainTable border-bottom border-grey">' . (new HeaderRow())->setProperties($properties)
                ->render() . (new ChangeAllRow())->setProperties($properties)
                ->render();

        $callbackScript = '';

        // Callbacks
        $callbackRegister = \isys_register::factory('callbacks');

        $listData = $dataSource->getData();

        foreach ($listData as $objectId => $categoryContent) {
            $content .= (new AssignmentRow())->setObjectData($objects[$objectId])
                ->setObjectId($objectId)
                ->setProperties($properties)
                ->setData($categoryContent)
                ->render();

            /*if ($categoryContent) {
                foreach ($categoryContent as $categoryId => $data) {
                    $content .= (new SimpleRow())
                        ->setObjectData($objects[$objectId])
                        ->setProperties($properties)
                        ->setObjectId($objectId)
                        ->setId($categoryId)
                        ->setData($data)
                        ->render();
                }
            } else{
                $content .= (new EmptyRow())
                    ->setObjectId($objectId)
                    ->setObjectData($objects[$objectId])
                    ->setProperties($properties)
                    ->setData($categoryContent)
                    ->render();
            }*/
        }

        $content .= '</table>';

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

        return $content . $callbackScript;
    }
}
