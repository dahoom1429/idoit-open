<?php

namespace idoit\Component\Protocol;

use idoit\Component\ContainerFacade;

/**
 * i-doit Protocols
 *
 * @package     idoit\Component\Protocol
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ContainerAware
{

    /**
     * @return ContainerFacade
     */
    public function getDi();

    /**
     * @param ContainerFacade $container
     *
     * @return mixed
     */
    public function setDi(ContainerFacade $container);

}