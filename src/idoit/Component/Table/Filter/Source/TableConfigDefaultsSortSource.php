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

class TableConfigDefaultsSortSource implements SourceInterface
{
    /**
     * @var Config
     */
    private $tableConfig;

    /**
     * Gets the stored data
     *
     * @return Array
     */
    public function get()
    {
        $sorts = [];
        $sortValue = $this->tableConfig->getSortingDirection();
        $sortField = $this->tableConfig->getSortingProperty();
        if ($sortField && !is_array($sortField) && $sortValue && !is_array($sortValue)) {
            $sorts[$sortField] = $sortValue;
        }

        return $sorts;
    }

    public function __construct(Config $tableConfig)
    {
        $this->tableConfig = $tableConfig;
    }
}