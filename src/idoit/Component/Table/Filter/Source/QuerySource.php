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
class QuerySource implements SourceInterface
{
    /**
     * Parameter name
     *
     * @var string
     */
    private $parameterName;

    public function __construct($parameterName)
    {
        $this->parameterName = $parameterName;
    }

    /**
     * Gets the stored data
     *
     * @return array
     */
    public function get()
    {
        if (isset($_GET[$this->parameterName]) && is_array($_GET[$this->parameterName])) {
            return $_GET[$this->parameterName];
        }

        return [];
    }
}
