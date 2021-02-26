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
 * Formats the value as an integer
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class IntFormatter implements FormatterInterface
{
    public function format($value)
    {
        return (int)$value;
    }
}
