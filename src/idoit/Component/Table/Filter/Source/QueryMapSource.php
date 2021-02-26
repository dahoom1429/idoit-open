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

/**
 * Get query source
 *
 * @package idoit\Component\Table\Filter\Source
 */
class QueryMapSource implements SourceInterface
{
    private $map = [];

    public function add($name, $value)
    {
        $this->map[$name] = $value;
    }

    /**
     * Gets the stored data
     *
     * @return Array
     */
    public function get()
    {
        $filter = [];
        foreach ($this->map as $k => $v) {
            if (isset($_GET[$k], $_GET[$v])) {
                $filter[$_GET[$k]] = $_GET[$v];
            }
        }

        return $filter;
    }
}
