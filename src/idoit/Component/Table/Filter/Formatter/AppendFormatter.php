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
 * Appends the value with predefined string
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class AppendFormatter implements FormatterInterface
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
        return $value . $this->value;
    }
}
