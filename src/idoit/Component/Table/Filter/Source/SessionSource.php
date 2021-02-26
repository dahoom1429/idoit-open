<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Source;

class SessionSource implements SourceInterface
{
    /**
     * @var
     */
    private $memorizeFilterTimer;

    private $objectTypeID;

    public function __construct($memorizeFilterTimer, $objectTypeID)
    {
        $this->memorizeFilterTimer = $memorizeFilterTimer;
        $this->objectTypeID = $objectTypeID;
    }

    /**
     * Gets the stored data
     *
     * @return Array
     */
    public function get()
    {
        $filters = [];
        if ($this->memorizeFilterTimer > 0 && isset($_SESSION['object-list-filter'], $_SESSION['object-list-filter']['obj-type-' . $this->objectTypeID]) &&
            !isset($_GET['filtered'])) {
            $memorizedFilters = $_SESSION['object-list-filter']['obj-type-' . $this->objectTypeID];

            foreach ($memorizedFilters as $memorizedFilter) {
                if (is_array($memorizedFilter) && isset($memorizedFilter['filterTime']) && isset($memorizedFilter['filterField'])) {
                    if ($memorizedFilter['filterTime'] + $this->memorizeFilterTimer > time()) {
                        $filters[$memorizedFilter['filterField']] = $memorizedFilter['filterValue'];
                    }
                }
            }
        }

        return $filters;
    }

    public function set(array $filters)
    {
        foreach ($filters as $filter => $value) {
            $_SESSION['object-list-filter']['obj-type-' . $this->objectTypeID][$filter] = [
                'filterField' => $filter,
                'filterValue' => $value,
                'filterTime'  => time()
            ];
        }
    }

    public function clear()
    {
        unset($_SESSION['object-list-filter']['obj-type-' . $this->objectTypeID]);
    }
}
