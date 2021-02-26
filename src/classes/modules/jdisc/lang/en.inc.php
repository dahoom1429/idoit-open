<?php
/**
 * i-doit
 *
 * "Custom fields" Module language file
 *
 * @package        custom fields
 * @subpackage     Language
 * @author         Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright      2013 synetics GmbH
 * @version        1.2.1
 * @license        http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

return [
    'LC__MODULE__JDISC__IMPORT__FILTER_TYPE__FILTER_HOSTADDRESS'                      => 'Filter by host address(es)',
    'LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_A_HOST_ADDRESS'                    => 'Filter JDisc devices for a host address',
    'LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_HOST_ADDRESSES_FROM_A_FILE'        => 'Filter JDisc devices for host addresses from a file',
    'LC__MODULE__JDISC__IMPORT__LOGGING'                                              => 'Logging',
    'LC__MODULE__JDISC__IMPORT__SHOW_LOG'                                             => 'Show detailed log',
    'LC__MODULE__JDISC__IMPORT__LOGGING_LESS'                                         => 'less',
    'LC__MODULE__JDISC__IMPORT__LOGGING_DETAIL'                                       => 'detailed (slower)',
    'LC__MODULE__JDISC__IMPORT__LOGGING_DEBUG'                                        => 'detailed+debug (very slow & memory intensive)',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER'                         => 'Port filter',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__NORMAL'           => 'Normal import',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__NO_IMPORT'        => 'No import',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__LOGICAL_PORT'     => 'Logical port',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__PHYSICAL_PORT'    => 'Physical port',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PORT_FILTER__TYPE__FC_PORT'          => 'FC-Port',
    'LC__MODULE__JDISC__IMPORT__FILTER_DEVICES_FOR_IP_FROM_FILE__DESCRIPTION'         => 'File format should be host addresses line by line.',
    'LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_ACTIVATED'        => 'In order to use this feature the unique check for IP addresses in the system settings has to be activated.',
    'LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_DEACTIVATED'      => 'This function prevents that the imported objects automatically receive an IP address if it should lead to conflicts with other devices. The conflicted IP addresses will be assigned to one of the global networks without loosing their IP address.',
    'LC__MODULE__JDISC__ADD_CUSTOM_ATTRIBUTES'                                        => 'Import custom attributes',
    'LC__CMDB__CATG__JDISC_CUSTOM_ATTRIBUTES'                                         => 'JDisc Custom Attributes',
    'LC__CATG__ASSIGNED_USERS'                                                        => 'Assigned Users',
    'LC__CMDB__CATG__ASSIGNED_USERS__ASSIGNED_OBJECT'                                 => 'Assigned Object',
    'LC__CMDB__CATG__ASSIGNED_USERS__UUID'                                            => 'UUID',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID'                                       => 'UUID',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS'                             => 'Consumed units',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS'                      => 'Prepaid enabled units',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED'                          => 'Prepaid suspended',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING'                            => 'Prepaid warning',
    'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS'                                => 'Subscribers',
    'LC__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS'                                     => 'JDisc status',
    'LC__CATG__ASSIGNED_SUBSCRIPTIONS'                                                => 'Assigned Subscriptions',
    'LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__ASSIGNED_OBJECT'                         => 'Assigned Object',
    'LC__CMDB__CATG__ASSIGNED_SUBSCRIPTIONS__UUID'                                    => 'UUID',
    'LC__MODULE__JDISC__SUCCESS__CLOUD_IMPORT'                                        => 'Cloud import successful',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__ATTRIBUTE'                                   => 'Attribute',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__CONTENT'                                     => 'Value',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__FOLDER'                                      => 'Folder',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES__TYPE'                                        => 'Type',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__TEXT'                                   => 'Text',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__MULTITEXT'                              => 'Multitext',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__DATE'                                   => 'Date',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__TIME'                                   => 'Time',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__INTEGER'                                => 'Integer',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__ENUMERATION'                            => 'Enumeration',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__CURRENCY'                               => 'Currency',
    'LC__CATG__JDISC__CUSTOM_ATTRIBUTES_TYPE__DOCUMENT'                               => 'Document',
    'LC__MODULE__JDISC__VLAN_IMPORT__IMPORT_ALL'                                      => 'Include VLans',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__LOCATION'                            => 'Location',
    'LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_TO_JDISC_SERVER'                      => 'Could not establish a connection to the selected JDisc Server.',
    'LC__MODULE__JDISC__ERROR_OBJECTTYPES_NOT_DEFINED_OR_ACTIVATED'                   => 'There are no object types defined in the JDisc profile or deactivated in the object type configuration.',
    'LC__MODULE__JDISC__DISCOVERY__HOST'                                              => 'Host',
    'LC__MODULE__JDISC__DISCOVERY__JOBS__NO_DISCOVERY_JOBS'                           => 'No Discovery Jobs in JDisc defined. Please create at least 1 Job.',
    'LC__MODULE__JDISC__DISCOVERY__JOBS__NO_CONNECTION'                               => 'Connection could not be established with the current discovery settings or the JDisc web service has not been activated/installed.',
    'LC__MODULE__JDISC__BUTTON__START_DISCOVERY'                                      => 'Start JDisc discovery',
    'LC__MODULE__JDISC__IMPORT__REQUEST_ERROR'                                        => 'The request stopped unexpectedly! Please check the exception log in directory "%s" and the apache error log.',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_SETTINGS'                            => 'Discovery settings',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME'                            => 'Username (Discovery)',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PASSWORD'                            => 'Password (Discovery)',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT'                                => 'Port (Discovery)',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PROTOCOL'                            => 'Protocol (Discovery)',
    'LC__MODULE__JDISC__DISCOVERY__JOBS__SUCCESS'                                     => 'Discovery job has been started.',
    'LC__MODULE__JDISC__DISCOVERY__JOBS__FAILED'                                      => 'Failed to start the discovery job.',
    'LC__MODULE__JDISC__DISCOVERY__CONNECTION_SUCCESS'                                => 'The connection to the JDisc Web service could be established. Discovery settings are correct.',
    'LC__MODULE__JDISC__DISCOVERY__CONNECTION_FAILED'                                 => 'The connection to JDisc Web Service could not be established. Please check whether the settings are correct and that the web service is up and running',
    'LC__CMDB__CATG__JDISC_DISCOVERY'                                                 => 'JDisc Discovery',
    'LC__CMDB__CATG__JDISC_DISCOVERY__SCAN'                                           => 'Scan object anew with JDisc',
    'LC__CMDB__CATG__JDISC_DISCOVERY__UPDATE_OBJECT'                                  => 'Update object from JDisc',
    'LC__CMDB__CATG__JDISC_DISCOVERY__SCAN_UPDATE_OBJECT'                             => 'Scan anew and update',
    'LC__CMDB__CATG__JDISC_DISCOVERY__SCAN_STARTED'                                   => 'Scan for device "%s" has been started.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__SCAN_FAILED'                                    => 'Scan for device "%s" could not be started. Please check if the device is connected to the network.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__DEFAULT_PROFILE'                                => 'Default profile',
    'LC__CMDB__CATG__JDISC_DISCOVERY__UPDATE_MODE'                                    => 'Update mode',
    'LC__CMDB__CATG__JDISC_DISCOVERY__JDISC_PROFILE'                                  => 'JDisc profile',
    'LC__CMDB__CATG__JDISC_DISCOVERY__JDISC_SERVER'                                   => 'JDisc server',
    'LC__CMDB__CATG__JDISC_DISCOVERY__DISCOVERY_FINISHED'                             => 'JDisc scan has been completed.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__UPDATE_OBJECT_ERROR'                            => 'Object could not be found in JDisc database. (JDisc profile did not match?)',
    'LC__CMDB__CATG__JDISC_DISCOVERY__DISCOVERY_LOG'                                  => 'Discovery log',
    'LC__CMDB__CATG__JDISC_DISCOVERY__IMPORT_RESULT'                                  => 'Import result',
    'LC__MODULE__JDISC__REQUEST_ERROR'                                                => 'The request could not be completed correctly! Please check the logs.',
    'LC__MODULE__JDISC__OBJTYPE_CONFIG__DEFAULT_PROFILE'                              => 'JDisc default profile',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_USERNAME__DESCRIPTION'               => 'Username for the JDisc application.',
    'LC__MODULE__JDISC__DISCOVERY__JOBS__DISCOVERY_JOB_DESCRIPTION'                   => 'Description',
    'LC__MODULE__JDISC__ERROR_COULD_NOT_CONNECT_WITH_MESSAGE'                         => 'Could not establish connection: "%s". Please check your configuration of the selected jdisc server.',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS'                     => 'Import network interfaces as',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_INTERFACE' => 'Category: Interface',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__CATEGORY_CHASSIS'   => 'Category: Chassis (Assigned devices)',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_INTERFACES_CHASSIS__BOTH_CATEGORIES'    => 'Category: Interface and chassis (Assigned devices)',
    'LC__MODULE__JDISC__PROFILES__CHANGE_CMDB_STATUS_OF_OBJECTS_TO'                   => 'Change CMDB-Status of objects to',
    'LC__MODULE__JDISC__USE_DEFAULT_TEMPLATES'                                        => 'Consider default templates from object types (only for newly created objects)',
    'LC__MODULE__JDISC__IMPORT__MODE_UPDATE_NEW_DISCOVERED'                           => 'Update (New inventory)',
    'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE_NEW_DISCOVERED'                        => 'Overwrite (New inventory)',
    'LC__MODULE__JDISC__PROFILES__KEEP_CMDB_STATUS'                                   => 'Keep CMDB-Status',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER'                                    => 'Software filter',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER__DESCRIPTION'                       => 'A comma separated list of software to be imported (Whitelist) or skipped (blacklist). The filter only applies in connection with the category <b>Software assignment</b>. (Example: *windows*,Adobe*,SUSE)',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER__DESCRIPTION_REGEX'                 => '<br/><br/><b>Caution</b><br/>Please use a valid regular expression without new lines if you want to use "RegExp" for software filtering. <b>The comparison will be case sensitive</b>.',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE'                               => 'Software filter type',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__WHITELIST'                    => 'Whitelist',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE__BLACKLIST'                    => 'Blacklist',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP'                        => 'Use filter as regular expression',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP__STRING'                => 'Filter as string',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_FILTER_TYPE_REGEXP__REGEXP'                => 'Filter as regexp',
    'LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_OBJTYPE'                           => 'Object type of the assigned modules within a blade/chassis unit',
    'LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL'                                   => 'Include clusters',
    'LC__MODULE__JDISC__CLUSTER_IMPORT__IMPORT_ALL__DESCRIPTION'                      => 'Clusters, which are documented in JDisc can be imported into i-doit and with the components that are present in these clusters are referenced. Otherwise, no clusters are imported and the existing references will not be updated.',
    'LC__MODULE__JDISC__CLUSTER_IMPORT__COUNTER'                                      => 'Amount of clusters documented in JDisc',
    'LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL'                         => 'Include Blade/Chassis connections during import',
    'LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__IMPORT_ALL__DESCRIPTION'            => 'The documented blade/chassis connections in JDisc can be imported and referenced into i-doit. Otherwise, the connections will not be imported and existing references will not be updated.',
    'LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__COUNTER'                            => 'Amount of blade/chassis connections documented in JDisc',
    'LC__MODULE__JDISC__BLADE_CONNECTIONS_IMPORT__CONNECTION_TO_FOLLOWING_TYPES'      => 'Connections to follwing JDisc-types:',
    'LC__MODULE__JDISC__CONFIGURATION__DEFAULT_SERVER'                                => 'Default Server',
    'LC__MODULE__JDISC__CONFIGURATION__ALLOW_IMPORT_OLDER_VERSION'                    => 'Allow import of older JDisc version?',
    'LC__MODULE__JDISC__CONFIGURATION__VERSION_CHECK'                                 => 'Version Check',
    'LC__MODULE__JDISC__IMPORT__JDISC_SERVERS'                                        => 'JDisc Server',
    'LC__MODULE__JDISC__IMPORT__JDISC_SERVER__ERROR_MSG'                              => 'The login credentials for the currently selected JDisc server are incorrect.',
    'LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_CONFLICTS'                               => 'Overwrite Overlapping host addresses?',
    'LC__MODULE__JDISC'                                                               => 'JDisc',
    'LC__MODULE__JDISC__BROKEN_JDISC_CONFIGURATION'                                   => 'The configuration of JDisc is not complete (%s). <a href="%s">Please check the configuration.</a>',
    'LC__MODULE__JDISC__CONFIGURATION'                                                => 'JDisc configuration',
    'LC__MODULE__JDISC__CONFIGURATION__TITLE'                                         => 'Title',
    'LC__MODULE__JDISC__CONFIGURATION__COMMON_SETTINGS'                               => 'Common settings',
    'LC__MODULE__JDISC__CONFIGURATION__DATABASE'                                      => 'Database',
    'LC__MODULE__JDISC__CONFIGURATION__HOST'                                          => 'Host',
    'LC__MODULE__JDISC__CONFIGURATION__ID'                                            => 'Configuration-ID',
    'LC__MODULE__JDISC__CONFIGURATION__PASSWORD'                                      => 'Password',
    'LC__MODULE__JDISC__CONFIGURATION__PORT'                                          => 'Port',
    'LC__MODULE__JDISC__CONFIGURATION__USERNAME'                                      => 'Username',
    'LC__MODULE__JDISC__CONFIGURATION__USERNAME__DESCRIPTION'                         => 'The user only needs read-only rights and will be created by JDisc itself.',
    'LC__MODULE__JDISC__CONNECTION_CHECK'                                             => 'Check connection',
    'LC__MODULE__JDISC__CONNECTION_SUCCESS'                                           => 'The connection to the JDisc database is properly configured.',
    'LC__MODULE__JDISC__IMPORT'                                                       => 'JDisc import',
    'LC__MODULE__JDISC__IMPORT__GROUP'                                                => 'JDisc group',
    'LC__MODULE__JDISC__IMPORT__OPTIONS'                                              => 'Options',
    'LC__MODULE__JDISC__IMPORT__MODE'                                                 => 'Import mode',
    'LC__MODULE__JDISC__IMPORT__MODE_APPEND'                                          => 'Append',
    'LC__MODULE__JDISC__IMPORT__MODE_APPEND_NEW_ONLY'                                 => 'Only create newly scanned devices',
    'LC__MODULE__JDISC__IMPORT__MODE_UPDATE'                                          => 'Update',
    'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE'                                       => 'Overwrite',
    'LC__MODULE__JDISC__IMPORT__MODE__DESCRIPTION'                                    => "The import mode <b>\"Append\"</b> will create all found objects, without checking if they already exist.<br />The import mode <b>\"Update\"</b> will only create objects, which could not be found in i-doit. Categories of already existing objects will (if necessary) be updated with new data. <br />The addition of <b>\"(New inventory)\"</b> resets all idoit-to-jdisc-device connections and allocates them freshly.<br />The import mode <b>\"Overwrite\"</b> behaves exactly like the \"Update\" mode with the difference that lists categories are deleted and then recreated.<br />The import mode <b> \"Only create newly scanned devices\" </b> only creates newly scanned objects, existing ones are skipped.",
    'LC__MODULE__JDISC__IMPORT__PROFILE'                                              => 'JDisc profile',
    'LC__MODULE__JDISC__IMPORT__RESULT'                                               => 'Result',
    'LC__MODULE__JDISC__IMPORT__SUCCEEDED'                                            => 'Import was successful.',
    'LC__MODULE__JDISC__IMPORT__FAILED'                                               => 'Import was not successful.',
    'LC__MODULE__JDISC__IMPORT__CREATE_SEARCH_INDEX'                                  => 'Regenerate search index after successful import',
    'LC__MODULE__JDISC__MISSING_GROUPS'                                               => 'No JDisc groups were found.',
    'LC__MODULE__JDISC__MISSING_PROFILES'                                             => 'No JDisc profiles were found.',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS'                                      => 'Object-type-assignment',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__ID'                                  => 'ID of the Object-type-assignment',
    'LC__MODULE__JDISC__OBJECT_NAME_TRANSFORMATION'                                   => 'Object name transformation',
    'LC__MODULE__JDISC__OBJECT_NAME_TRANSFORMATION__DESCRIPTION'                      => 'Converts the object name to “all uppercase”, “all lowercase” or “leave the spelling of the original”',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS'                            => 'JDisc operating system',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_OS__DESCRIPTION'               => 'The data comes from JDisc and represents the field "Device Operating System". Leave empty to select all.',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE'                          => 'JDisc-type',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__JDISC_TYPE__DESCRIPTION'             => 'The data comes from JDisc and represents the field "Device Type". Leave empty to select all.',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE'                         => 'Object-type',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__OBJECT_TYPE__DESCRIPTION'            => 'The data comes from i-doit. If empty the assignment will be ignored.',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__PROFILE'                             => 'Profile',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED'                          => '[customized]',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__CUSTOMIZED__DESCRIPTION'             => 'Wildcards like "*" are allowed.',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__TITLE_TRANSFORM'                     => 'Objekt title transform',
    'LC__MODULE__JDISC__OBJECT_TYPE_ASSIGNMENTS__FQDN_ADDITION'                       => 'FQDN Addition',
    'LC__MODULE__JDISC__PROFILES'                                                     => 'JDisc profiles',
    'LC__MODULE__JDISC__PROFILES__ACTIONS'                                            => 'Actions',
    'LC__MODULE__JDISC__PROFILES__ADD_NEW_ASSIGNMENT'                                 => 'Add new assignment',
    'LC__MODULE__JDISC__PROFILES__DUPLICATE_ASSIGNMENT'                               => 'Duplicate assignment',
    'LC__MODULE__JDISC__PROFILES__COMMON_SETTINGS'                                    => 'General configuration',
    'LC__MODULE__JDISC__PROFILES__DELETE_THIS_ASSIGNMENT'                             => 'Remove this assignment',
    'LC__MODULE__JDISC__PROFILES__DESCRIPTION'                                        => 'Description',
    'LC__MODULE__JDISC__PROFILES__ID'                                                 => 'ID',
    'LC__MODULE__JDISC__PROFILES__TITLE'                                              => 'Title',
    'LC__MODULE__JDISC__PROFILES__CATEGORIES'                                         => 'Select categories',
    'LC__MODULE__JDISC__PROFILES__CATEGORIES__DESCRIPTION'                            => 'Only categories supported by JDisc are listed.',
    'LC__MODULE__JDISC__ADDITIONAL_OPTIONS'                                           => 'Additional options',
    'LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL'                                   => 'Include layer 3 nets',
    'LC__MODULE__JDISC__NETWORK_IMPORT__IMPORT_ALL__DESCRIPTION'                      => 'Layer 3 nets documented in i-doit may be imported from JDisc and referenced with components inside these nets. Otherwise no nets will be imported, but those nets will be referenced which are already documented in i-doit.',
    'LC__MODULE__JDISC__NETWORK_IMPORT__COUNTER'                                      => 'Amount of layer 3 nets documented in JDisc',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL'                                  => 'Include software',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__IMPORT_ALL__DESCRIPTION'                     => 'i-doit distinguish between software (incl. operation systems etc.) and software installations. If you activate this option every software documented in JDisc will be imported and referenced with these components on which these software is installed. Otherwise no additional software will be imported, but only those software will be referenced which is already documented in i-doit.',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__COUNTER'                                     => 'Amount of software documented in JDisc',
    'LC__MODULE__JDISC__START_IMPORT'                                                 => 'Start the import',
    'LC__MODULE__JDISC__LINK_TO_IMPORT'                                               => 'JDisc import',
    'LC__MODULE__JDISC__LINK_TO_CONFIGURATION'                                        => 'JDisc configuration',
    'LC__MODULE__JDISC__LINK_TO_PROFILES'                                             => 'JDisc profiles',
    'LC__MODULE__JDISC__DUPLICATE_PROFILES'                                           => 'Duplicate profiles',
    'LC__MODULE__JDISC__ORIGINAL_NAME'                                                => 'Translation profile\'s name',
    'LC__MODULE__JDISC__NEW_NAME'                                                     => 'New profile\'s name',
    'LC__MODULE__JDISC__SHOW_DEBUG'                                                   => 'Display debug notices',
    'LC__MODULE__JDISC__VERSION_CHECK_FAILED'                                         => 'Your current JDisc version %s is outdated. To use the import with all the benefits, please install the JDisc version >= %s. Optionally, you can configure the JDisc import in the JDisc configuration for older Versions.',
    'LC__MODULE__JDISC__POPUP__ERROR__NO_SELECTED_PROFILE'                            => 'No selected JDisc profile.',
    'LC__MODULE__JDISC__ERROR_JDISC_PROFILE__NO_OBJECT_MATCHING_PROFILE'              => 'No object matching profile defined in the jdisc profile.',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__LICENCES'                                    => 'Include software licences',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__SERVICES'                                    => 'Import system services',
    'LC__MODULE__JDISC__SOFTWARE_IMPORT__CLOUD_SUBSCRIPTIONS'                         => 'Import cloud subscriptions',
    'LC__MODULE__JDISC__ERROR_DEVICE_NOT_FOUND_VIA_OBJECT_MATCHING'                   => 'The device could not be found in jdisc via object matching profile.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__NO_PRIMARY_IP_ADDRESS_DEFINED'                  => 'There is no primary hostaddress defined.',
    'LC__MODULE__JDISC__PROFILES__UPDATE_OBJTYPE'                                     => 'Update objecttype',
    'LC__MODULE__JDISC__PROFILES__UPDATE_OBJ_TITLE'                                   => 'Update object title',
    'LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__USE_DEFAULT_TEMPLATES'                  => 'Use default template',
    'LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_MODULES_OBJTYPE'                   => 'Import assigned objecttype(Chassis)',
    'LC__MODULE__JDISC__PROFILES__CHASSIS_ASSIGNED_MODULES_UPDATE_OBJTYPE'            => 'Update the object type of the assigned modules',
    'LC__MODULE__JDISC__PROFILES__SOFTWARE_OBJ_TITEL'                                 => 'Use OS family (if available) instead of OS version as object title',
    'LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE'                                    => 'Identification of the device',
    'LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE__DESCRIPTION'                       => 'The device is identified and scanned within the JDisc server based on the selection.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE__ERROR_NO_TYPE_SET'                 => 'No device identification set.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE__ERROR_NO_FQDN_SET'                 => 'No FQDN set.',
    'LC__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE__ERROR_NO_HOSTADDRESS_SET'          => 'No IP set.',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT'                             => 'Timeout (Seconds)',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_TIMEOUT_DESCRIPTION'                 => 'Value between 60 - 9999',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_PORT_DESCRIPTION'                    => 'Value between 1 - 65535',
    'LC__MODULE__JDISC__DISCOVIERY__ORDER_TO_SCAN_DEVICE_SEND'                        => 'Order to scan the current device transmitted to JDisc server.',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_CATEGORY_SETTINGS'                   => 'Discovery category settings',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES'                      => 'Repeat attempts to import',
    'LC__MODULE__JDISC__CONFIGURATION__DISCOVERY_IMPORT_RETRIES_DESCRIPTION'          => 'Value between 1 - 9',
    'LC__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL'                                    => 'Use simple database modelling?',
    'LC__JDISC__TITLE_TRANSFORM__AS_IS'                                               => 'As is',
    'LC__JDISC__TITLE_TRANSFORM__UPPERCASE'                                           => 'Uppercase',
    'LC__JDISC__TITLE_TRANSFORM__LOWERCASE'                                           => 'Lowercase',
    'LC__MODULE__JDISC__USE_SIMPLE_DATABASE_MODEL'                                    => 'Use simple database modelling?',
    'LC__MODULE__JDISC__PROFILES__NETWORK_ADRESSES'                                   => 'Keep IP address types',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES'                          => 'Import type for DHCP IP addresses',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES__NORMAL'                  => 'Normal',
    'LC__MODULE__JDISC__PROFILES__IMPORT_TYPE_DHCP_ADRESSES__DHCP_IPS_UPDATE'         => 'With update',
    'LC_IPV4'                                                                         => 'IPv4 address',
    'LC_IPV6'                                                                         => 'IPv6 address',
    'LC_IP_LOOPBACK'                                                                  => 'Loopback address',
    'LC_IP_VIRTUAL'                                                                   => 'Virtual address',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER'                                 => 'Part Number',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE'                                 => 'Start Date',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE'                                   => 'End Date',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE'                                      => 'State',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES'                                    => 'Expires in Days',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRED'                                    => 'Expired',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_ACTIVE'                               => 'Active',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_INACTIVE'                             => 'Inactive',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_NOT_STARTED'                          => 'Not started',
    'LC__CMDB__CATG__SUPPORT_ENTITLEMENT'                                             => 'Support Entitlements',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT'                                             => 'Connection endpoint',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__TITLE'                                      => 'Title',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__INTERFACE'                                  => 'Interface',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO_TITLE'                         => 'Endpoint interface',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__CONNECTED_TO'                               => 'Endpoint object',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__TYPE'                                       => 'Type',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__SPEED'                                      => 'Speed',
    'LC__CMDB__CATG__CONNECTION_ENDPOINT__SPEED_UNIT'                                 => 'Unit',
    'LC__RELATION_TYPE__MASTER__CONNECTION_ENDPOINT'                                  => 'has connection to',
    'LC__RELATION_TYPE__SLAVE__CONNECTION_ENDPOINT'                                   => 'is conntected with',
    'LC__MODULE__JDISC__IMPORT_CONNECTION_ENDPOINT'                         => 'Import Connection endpoints',
    'LC__MODULE__JDISC__IMPORT_CONNECTION_ENDPOINT_DESCRIPTION'             => 'The connections are not imported into the cabling, but into the "Connection endpoint" category.'
];
