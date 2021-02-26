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

namespace idoit\Component\Table\Filter\Formatter;

/**
 * Prepend the value with predefined string if it's not already starts with it
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class PrependFormatter implements FormatterInterface
{
    /**
     * @var
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function format($value)
    {
        if (strpos($value, $this->value) !== 0) {
            $value = $this->value . $value;
        }

        return $value;
    }
}
