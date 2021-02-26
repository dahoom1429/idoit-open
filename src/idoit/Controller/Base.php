<?php

namespace idoit\Controller;

use idoit\Component\Provider\DiInjectable;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * i-doit Base Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Base implements ContainerAwareInterface
{
    use DiInjectable;
}
