<?php
/**
 * i-doit
 *
 * "Monitoring" Module language file
 *
 * @package        monitoring
 * @subpackage     Language
 * @author         Leonard Fischer <lfischer@i-doit.com>
 * @copyright      2013 synetics GmbH
 * @version        1.0.0
 * @license        http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

return [
    'LC__CATG__MONITORING__INSTANCE'                                       => 'Monitoring instance',
    'LC__CATG__MONITORING__ACTIVE'                                         => 'Active?',
    'LC__CATG__MONITORING__ALIAS'                                          => 'Alias',
    'LC__CATG__MONITORING__HOSTNAME'                                       => 'Hostname',
    'LC__CATG__MONITORING__HOSTNAME_SELECTION'                             => 'Hostname selection',
    'LC__CATG__MONITORING__HOST'                                           => 'Host',
    'LC__CATG__LIVESTATUS'                                                 => 'Livestatus',
    'LC__CATG__LIVESTATUS__CURRENT_STATE'                                  => 'Current state',
    'LC__CATG__LIVESTATUS__HOST_STATE'                                     => 'Host state',
    'LC__CATG__LIVESTATUS__SERVICE_STATE'                                  => 'Service state',
    'LC__CATG__LIVESTATUS__NO_DATA'                                        => 'Received no data from livestatus.',
    'LC__CATG__NDO'                                                        => 'NDO',
    'LC__CATG__NDO__STATUS_CGI'                                            => 'Status CGI',
    'LC__MONITORING'                                                       => 'Monitoring',
    'LC__MONITORING__LIVESTATUS'                                           => 'Livestatus',
    'LC__MONITORING__LIVESTATUS_STATUS'                                    => 'Livestatus',
    'LC__MONITORING__LIVESTATUS_STATUS_BUTTON'                             => 'Livestatus (button)',
    'LC__MONITORING__NDO'                                                  => 'NDO',
    'LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION'                        => 'Livestatus / NDO',
    'LC__MONITORING__LIVESTATUS_NDO__CONFIGURATION_EDIT'                   => 'Edit Livestatus / NDO configuration',
    'LC__MONITORING__EXPORT__CONFIGURATION'                                => 'Export configuration',
    'LC__MONITORING__EXPORT__CONFIGURATION_EDIT'                           => 'Edit export configuration',
    'LC__MONITORING__EXPORT__CONFIGURATION_OPTIONS'                        => 'Options for export configuration',
    'LC__MONITORING__EXPORT__PATH'                                         => 'Export directory',
    'LC__MONITORING__ACTIVE'                                               => 'Active?',
    'LC__MONITORING__TYPE'                                                 => 'Monitoring type',
    'LC__MONITORING__CONNECTION'                                           => 'Connection',
    'LC__MONITORING__PATH'                                                 => 'Path',
    'LC__MONITORING__ADDRESS'                                              => 'Address',
    'LC__MONITORING__PORT'                                                 => 'Port',
    'LC__MONITORING__DBNAME'                                               => 'Databasename / Schema',
    'LC__MONITORING__DBPREFIX'                                             => 'DB Prefix',
    'LC__MONITORING__USERNAME'                                             => 'Username',
    'LC__MONITORING__PASSWORD'                                             => 'Password',
    'LC__MONITORING__MONITORING_ADDRESS'                                   => 'Link to your monitoring tool',
    'LC__MONITORING__MONITORING_ADDRESS_INFO'                              => 'Please notice: Nagios needs the suffix "/status.cgi?host="',
    'LC__MONITORING__MONITORING_TYPE'                                      => 'Type',
    'LC__MONITORING__CHECK_MK__SITE'                                       => 'Site',
    'LC__MONITORING__CHECK_MK__ROLE_EXPORT'                                => 'Export assigned contacts',
    'LC__MONITORING__CHECK_MK__ROLE_EXPORT_DESCRIPTION'                    => 'Assigned contacts need the following role to get exported',
    'LC__MONITORING__CHECK_MK__MULTISITE'                                  => 'Multisite',
    'LC__MONITORING__CHECK_MK__MULTISITE_INFO'                             => 'Distributed monitoring',
    'LC__MONITORING__CHECK_MK__MASTER_SITE'                                => 'Master Site',
    'LC__MONITORING__CHECK_MK__MASTER_SITE_OF'                             => 'Master Site of',
    'LC__MONITORING__CHECK_MK__LOCK_HOSTS'                                 => 'Lock hosts',
    'LC__MONITORING__CHECK_MK__LOCK_FOLDERS'                               => 'Lock folders',
    'LC__MONITORING__CHECK_MK__UTF8DECODE_EXPORT'                          => 'UTF-8 decode exported files',
    'LC__MONITORING__EXPORT_PATH'                                          => 'local path',
    'LC__MONITORING__EXPORT_PATH_WARNING'                                  => 'Attention: the directory needs to be typed in absolute. Also its content will be deleted before each export!',
    'LC__MONITORING__NDO__STATUS'                                          => 'NDO state',
    'LC__MONITORING__NDO__STATUS_BUTTON'                                   => 'NDO state button',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__NO_CONFIG'                      => 'Please configure at least one monitoring host.',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__PHP_EXTENSION_MISSING'          => 'It seems, as if the "sockets" PHP Extension is missing!',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'        => 'Could not create socket to livestatus.',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS'   => 'Unable to connect to livestatus',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET'     => 'Problem while reading from socket: %s',
    'LC__MONITORING__LIVESTATUS_EXCEPTION__INVALID_FORMAT'                 => 'The response has an invalid format!',
    'LC__MONITORING__NDO_EXCEPTION__NO_CONFIG'                             => 'Please configure at least one NDO host.',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS'                                 => 'Monitoring: Affected hosts',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__HOST_SELECTION'                 => 'Hostselection',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__SERVICE'                        => 'Service',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__NO_SERVICES'                    => 'Services can not be retrieved by this monitoring instance.',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__UNSUPPORTED_HOST'               => 'The selected monitoring host is not supported!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__PLEASE_SELECT_HOST'             => 'You need to select a (active) monitoring host!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_HOSTS_OK'                   => 'All hosts seem to be OK!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_HOSTS_THAT_ARE_NOT_OK'    => 'Attention! Found hosts with a other status than "OK":',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__ALL_SERVICES_OK'                => 'All services seem to be OK!',
    'LC__MONITORING__WIDGET__NOT_OK_HOSTS__FOUND_SERVICES_THAT_ARE_NOT_OK' => 'Attention! Found services with a other status than "OK":',
];