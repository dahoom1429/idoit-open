<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Upload\Types\ObjectTypeIcon;
use idoit\Component\Upload\Types\ObjectTypeImage;
use idoit\Component\Upload\UploadType;

if (class_exists('\idoit\Psr4AutoloaderClass')) {
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Cmdb', __DIR__ . '/src/');
}

if (isys_application::instance()->container->get('session')->is_logged_in()) {
    // Connect search signals.
    idoit\Module\Cmdb\Search\Index\Signals::instance()
        ->connect();

    // Connect a new route to match old "/cmdb/object/123" URLs.
    isys_request_controller::instance()
        ->addModuleRoute('GET', '/cmdb/object/[i:id]', 'cmdb', 'ObjectController')
        ->addModuleRoute('POST', '/cmdb/browse-location/[i:id]', 'cmdb', 'BrowseLocation', 'getObjectData')
        ->addModuleRoute('POST', '/cmdb/browse/get-object-data', 'cmdb', 'Browse', 'getObjectData')
        ->addModuleRoute('POST', '/cmdb/browse/get-selection-data', 'cmdb', 'Browse', 'getSelectionData')
        ->addModuleRoute('POST', '/cmdb/browse/[s:method]/[**:id]', 'cmdb', 'Browse')
        ->addModuleRoute('POST', '/cmdb/list-config/[s:method]/[**:id]', 'cmdb', 'ListConfig');

    isys_register::factory('ajax-file-upload')
        ->set('object-type-icon', (new UploadType())
            ->setUploadDirectory('/images/icons/silk/')
            ->setValidExtensions(['jpeg', 'jpg', 'png', 'gif', 'bmp'])
            ->setSizeLimit(1048576) // 1 MB
            ->setCallbackAfterUpload([ObjectTypeIcon::class, 'processUpload']))
        ->set('object-type-image', (new UploadType())
            ->setUploadDirectory('/images/objecttypes/')
            ->setValidExtensions(['jpeg', 'jpg', 'png', 'gif', 'bmp'])
            ->setSizeLimit(5242880) // 5 MB
            ->setCallbackAfterUpload([ObjectTypeImage::class, 'processUpload']));
}
