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
 * Proceeds the list of formatters
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class CollectionFormatter implements FormatterInterface
{
    /**
     * @var array<FormatterInterface>
     */
    protected $formatters = [];

    /**
     * @param FormatterInterface $formatter
     *
     * @return $this
     */
    public function add(FormatterInterface $formatter)
    {
        $this->formatters[] = $formatter;

        return $this;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function format($value)
    {
        foreach ($this->formatters as $formatter) {
            $value = $formatter->format($value);
        }

        return $value;
    }
}
