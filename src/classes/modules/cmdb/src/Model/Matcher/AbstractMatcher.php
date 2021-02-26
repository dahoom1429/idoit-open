<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Component\Provider\DiInjectable;
use idoit\Component\Provider\Factory;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class AbstractMatcher
{
    use Factory, DiInjectable;

    /**
     * @var MatchConfig
     */
    protected $config;

    /**
     * Matcher constructor.
     *
     * @param MatchConfig $config
     */
    public function __construct(MatchConfig $config)
    {
        $this->setDi($config->getDi());
        $this->config = $config;
    }

}