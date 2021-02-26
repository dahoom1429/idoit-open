<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @version     1.12.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (class_exists('\idoit\Psr4AutoloaderClass')) {
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Console', __DIR__ . '/src/');
}
