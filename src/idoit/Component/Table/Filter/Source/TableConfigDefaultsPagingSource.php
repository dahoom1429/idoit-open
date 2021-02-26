<?php
/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Source;

use idoit\Module\Cmdb\Model\Ci\Table\Config;

/**
 * TableConfigDefaultsPagingSource
 *
 * @package idoit\Component\Table\Filter\Source
 */
class TableConfigDefaultsPagingSource implements SourceInterface
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
     * @return array
     */
    public function get()
    {
        $configurationParameters = [];

        // Getting parameters based on availability
        $configurationParameters['page'] = $this->tableConfig->getPaging() ?: $_GET['page'];
        $configurationParameters['rowsPerPage'] = $this->tableConfig->getRowsPerPage() ?: $_GET['rowsPerPage'];

        return $configurationParameters;
    }
}