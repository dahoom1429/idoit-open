<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Type;

use idoit\Module\Multiedit\Component\Multiedit\Renderable;
use idoit\Module\Multiedit\Component\Multiedit\Row\ChangeAllRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\EmptyRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\HeaderRow;
use idoit\Module\Multiedit\Component\Multiedit\Row\SimpleRow;
use org\idoit\viva\view\html\Header;

/**
 * Class MatrixType
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Type
 */
class MatrixType extends Type
{
    const CATEGORYTYPE = 'Matrix';

    /**
     * @param array                                                               $objects
     * @param \idoit\Module\Multiedit\Component\Multiedit\Source\DataSource       $dataSource
     * @param \idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource $properties
     *
     * @return string
     */
    public function render($objects, $dataSource, $properties)
    {
        $content = '<table class="mainTable border-bottom border-grey">';

        // Callbacks
        $callbackRegister = \isys_register::factory('callbacks');

        // Header
        $content .= (new HeaderRow())->setProperties($properties)
            ->render();

        // Change all row
        $content .= (new ChangeAllRow())->setProperties($properties)
            ->disableFields()
            ->render();

        $listData = $dataSource->getData();

        $dao = $dataSource->getDao();

        if ($properties->getDao() === null && $dao) {
            $properties->setDao(current($dao));
        }

        foreach ($listData as $objectId => $categoryContent) {
            if (is_array($categoryContent) && count($categoryContent)) {
                foreach ($categoryContent as $entryId => $data) {
                    $content .= (new SimpleRow())->setObjectData($objects[$objectId])
                        ->setProperties($properties)
                        ->setObjectId($objectId)
                        ->setId($entryId)
                        ->setData($data)
                        ->render();
                }
            } else {
                $content .= (new EmptyRow())->setObjectId($objectId)
                    ->setObjectData($objects[$objectId])
                    ->setProperties($properties)
                    ->setData($categoryContent)
                    ->render();
            }
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
