<?php
/**
 * i-doit
 *
 * Static constant not registered by the dynamic constant manager.
 * Please empty this list every major release.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// @see ID-934 -- global and specific categories
$replacementConstants = [
    'C__CMDB__SUBCAT__NETWORK_PORT'                      => 'C__CATG__NETWORK_PORT',
    'C__CMDB__SUBCAT__NETWORK_INTERFACE_P'               => 'C__CATG__NETWORK_INTERFACE',
    'C__CMDB__SUBCAT__NETWORK_INTERFACE_L'               => 'C__CATG__NETWORK_LOG_PORT',
    'C__CMDB__SUBCAT__STORAGE__DEVICE'                   => 'C__CATG__STORAGE_DEVICE',
    'C__CMDB__SUBCAT__NETWORK_PORT_OVERVIEW'             => 'C__CATG__NETWORK_PORT_OVERVIEW',
    'C__CMDB__SUBCAT__LICENCE_LIST'                      => 'C__CATS__LICENCE_LIST',
    'C__CMDB__SUBCAT__LICENCE_OVERVIEW'                  => 'C__CATS__LICENCE_OVERVIEW',
    'C__CMDB__SUBCAT__EMERGENCY_PLAN_LINKED_OBJECT_LIST' => 'C__CATS__EMERGENCY_PLAN_LINKED_OBJECTS',
    'C__CMDB__SUBCAT__EMERGENCY_PLAN'                    => 'C__CATS__EMERGENCY_PLAN_ATTRIBUTE',
    'C__CMDB__SUBCAT__WS_NET_TYPE'                       => 'C__CATS__WS_NET_TYPE',
    'C__CMDB__SUBCAT__WS_ASSIGNMENT'                     => 'C__CATS__WS_ASSIGNMENT',
    'C__CMDB__SUBCAT__FILE_OBJECTS'                      => 'C__CATS__FILE_OBJECTS',
    'C__CMDB__SUBCAT__FILE_VERSIONS'                     => 'C__CATS__FILE_VERSIONS',
    'C__CMDB__SUBCAT__FILE_ACTUAL'                       => 'C__CATS__FILE_ACTUAL',
];

foreach ($replacementConstants as $oldConstant => $newConstant) {
    if (!defined($oldConstant) && defined($newConstant)) {
        define($oldConstant, constant($newConstant));
    }
}

// @see ID-6912 This can happen if `nostalgia` gets included before `import`.
if (defined('C__IMPORT__DIRECTORY')) {
    $csvImportDirectory = C__IMPORT__DIRECTORY;
} else {
    $csvImportDirectory = isys_tenantsettings::get('system.dir.csv-uploads', rtrim(isys_tenantsettings::get('system.dir.import-uploads', BASE_DIR . '/imports/'), '/') . '/') . isys_application::instance()->tenant->id . '/';
}

$constants = [
    'C__IMPORT__CSV_DIRECTORY'             => $csvImportDirectory, // @todo  Remove in 1.14
    'C__INFOBOX__LENGTH'                   => 150,
    'C__CMDB__GET__CATD_CHECK'             => 'catdCheck',      // @todo  Remove in 1.17
    'C__CMDB__GET__SUBCAT'                 => 'subcatID',       // @todo  Remove in 1.17
    'C__CMDB__GET__SUBCAT_ENTRY'           => 'subcatEntryID',  // @todo  Remove in 1.17
    'C__CMDB__GET__CONNECTION_TYPE'        => 'connectionType', // @todo  Remove in 1.17
    'C__CMDB__GET__LDEVSERVER'             => 'ldevserverID',   // @todo  Remove in 1.17

    // CMDB: DAO-inner constants for direction and type of network-type elements.
    'C__CMDB__DAO_NET_PORT__AHEAD'         => 0, // @todo  Remove in 1.17
    'C__CMDB__DAO_NET_PORT__REAR'          => 1, // @todo  Remove in 1.17
    'C__CMDB__DAO_NET_PORT__PHYSICAL'      => 1, // @todo  Remove in 1.17
    'C__CMDB__DAO_NET_PORT__VIRTUAL'       => 2, // @todo  Remove in 1.17
    'C__CMDB__DAO_NET_INTERFACE__PHYSICAL' => 1, // @todo  Remove in 1.17
    'C__CMDB__DAO_NET_INTERFACE__VIRTUAL'  => 2, // @todo  Remove in 1.17

    // CMDB: DAO-inner constants for endpoint selection of an universal interface.
    'C__CMDB__DAO_UI_ENDPOINT__AHEAD'      => 1, // @todo  Remove in 1.17
    'C__CMDB__DAO_UI_ENDPOINT__REAR'       => 2, // @todo  Remove in 1.17

    // CMDB: DAO-inner constants for endpoint selection of a FC storage connection.
    'C__CMDB__DAO_STOR_FC__AHEAD'          => 1, // @todo  Remove in 1.17
    'C__CMDB__DAO_STOR_FC__REAR'           => 2, // @todo  Remove in 1.17

    // System constants from table 'isys_const_system' (in 'idoit_system').
    'C__CAT_LISTVIEW__OFF'                  => 0,
    'C__CAT_LISTVIEW__ON'                   => 1,
    'C__CHECK_PERMISSION__APPEND'           => 4,
    'C__CHECK_PERMISSION__DELETE'           => 2,
    'C__CHECK_PERMISSION__DUPLICATE'        => 3,
    'C__CHECK_PERMISSION__EDIT'             => 1,
    'C__CHECK_PERMISSION__RECYCLE'          => 5,
    'C__CMDB__CATD'                         => 3,
    'C__CMDB__CATEGORY__POBJ_FEMALE_SOCKET' => 1,
    'C__CMDB__CATEGORY__POBJ_MALE_PLUG'     => 0,
    'C__CMDB__CATG'                         => 1,
    'C__CMDB__CATS'                         => 2,
    'C__CMDB_TREEMODE__GCAT'                => 1,
    'C__CMDB_TREEMODE__OBJTYPE'             => 1,
    'C__CMDB_VIEWMODE__LOCATION'            => 0,
    'C__CMDB_VIEWMODE__OBJECT'              => 0,
    'C__F_POPUP__CONTACT'                   => 3,
    'C__F_POPUP__DATETIME'                  => 2,
    'C__F_POPUP__LOCATION'                  => 1,
    'C__F_POPUP__PICTURE'                   => 4,
    'C__LINK__POBJ_FEMALE_SOCKET'           => 1,
    'C__LINK__POBJ_MALE_PLUG'               => 1,
    'C__MAX_COUNT__GET_HISTORY'             => 5,
    'C__NAVBAR_BUTTON__BLANK'               => 11,
    'C__RECORD_PROPERTY__NOT_SHOW_IN_LIST'  => 16,
];

foreach ($constants as $constantName => $constantValue) {
    if (!defined($constantName)) {
        define($constantName, $constantValue);
    }
}
