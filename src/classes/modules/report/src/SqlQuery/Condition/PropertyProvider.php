<?php
namespace idoit\Module\Report\SqlQuery\Condition;

use idoit\Component\Property\Property;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PropertyProvider extends AbstractProvider
{
    /**
     * @return AbstractProvider
     */
    public static function factory()
    {
        $obj = new self();

        $namespace = 'idoit\Module\Report\SqlQuery\Condition\Property\\';

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\isys_module_report::getPath() . 'src/SqlQuery/Condition/Property', \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            // Exclude dot, abstract classes, and interfaces
            $class = $namespace . $file->getBasename('.' . $file->getExtension());

            if (class_exists($class)) {
                $obj->addConditionType(new $class());
            }
        }

        return $obj;
    }
}
