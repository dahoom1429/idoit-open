<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
if (include_once('isys_module_multiedit_autoload.class.php')) {
    spl_autoload_register('isys_module_multiedit_autoload::init');
}

if (class_exists('\idoit\Psr4AutoloaderClass')) {
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Multiedit', __DIR__ . '/src/');
}
