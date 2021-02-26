<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @version     1.3
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

\idoit\Psr4AutoloaderClass::factory()->addNamespace('idoit\Module\System', __DIR__ . '/src/');