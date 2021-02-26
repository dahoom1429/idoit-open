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
 * Fetches the filters from different sources
 *
 * @package idoit\Component\Table\Filter\Source
 */
class SourceProvider
{
    /**
     * @var array<SourceInterface>
     */
    protected $sources = [];

    /**
     * Add source
     *
     * @param SourceInterface $source
     */
    public function addSource(SourceInterface $source)
    {
        $this->sources[] = $source;
    }

    /**
     * Fetch parameters from sources till found
     *
     * @return array
     */
    public function fetch()
    {
        foreach ($this->sources as $source) {
            if ($source instanceof SourceInterface) {
                $filters = $source->get();
                if (is_array($filters) && count($filters) > 0) {
                    return $filters;
                }
            }
        }

        return [];
    }
}
