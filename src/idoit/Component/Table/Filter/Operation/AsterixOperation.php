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

namespace idoit\Component\Table\Filter\Operation;

use idoit\Module\Cmdb\Model\Ci\Table\Config;
use isys_cmdb_dao_list_objects;

/**
 * Apply search for all properties
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class AsterixOperation extends Operation
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var OperationProvider
     */
    private $provider;

    public function __construct(Config $config, OperationProvider $provider)
    {
        $this->config = $config;
        $this->provider = $provider;
    }

    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        $filter = [];
        foreach ($this->config->getProperties() as $property) {
            $filter[$property->getPropertyKey()] = $value;
        }
        $tmpList = new isys_cmdb_dao_list_objects($dao->get_database_component());

        $provider = new OperationProvider($tmpList);
        foreach ($this->provider->getOperations() as $operation) {
            $provider->addOperation($operation);
        }
        $provider->apply($filter);
        $cond = $tmpList->getAdditionalConditions();
        foreach ($cond as $i => &$v) {
            $v = substr($v, strlen('AND '));
        }

        $having = $tmpList->getAdditionalHavingConditions();
        $dao->set_additional_selects($tmpList->getAdditionalSelects());
        if (is_countable($cond) && count($cond) > 0) {
            $dao->add_additional_selects('IF(' . implode(' OR ', $cond) . ', 1, 0)', 'where_conditions');
            $having[] = '`where_conditions` > 0';
        }
        if (is_array($having) && count($having) > 0) {
            $dao->set_additional_having_conditions(['(' . implode(' OR ', $having) . ')']);
        }

        return true;
    }

    public function isApplicable($filter, $value)
    {
        return $filter === '*';
    }
}
