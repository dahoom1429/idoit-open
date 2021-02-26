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

use idoit\Module\Cmdb\Model\Ci\Table\Config;

class TableConfigDefaultsSource implements SourceInterface
{
    /**
     * @var Config
     */
    private $tableConfig;

    public function __construct(Config $tableConfig)
    {
        $this->tableConfig = $tableConfig;
    }

    /**
     * Gets the stored data
     *
     * @return Array
     */
    public function get()
    {
        $filters = [];
        $filterValue = $this->tableConfig->getFilterValue();
        $filterField = $this->tableConfig->getFilterProperty();
        if ($filterField && !is_array($filterField) && $filterValue && !is_array($filterValue)) {
            $filters[$filterField] = $filterValue;
        }

        return $filters;
    }
}