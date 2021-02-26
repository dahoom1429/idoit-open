<?php
/**
 * i-doit
 *
 * "Search" Module language file
 *
 * @package
 * @subpackage     Language
 * @copyright      2016 synetics GmbH
 * @version        1.7.1
 * @license        http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

return [
    'LC__SEARCH__CONFIG__MODE'                                                 => 'Default search mode',
    'LC__SEARCH__CONFIG__NORMAL_DESCRIPTION'                                   => '<strong>Normal</strong>: Normal search, partial matching works only from the <strong>beginning</strong> of a keyword. (e.g. a search for "Micr Office" delivers "Microsoft Windows")',
    'LC__SEARCH__CONFIG__DEEP_DESCRIPTION'                                     => '<strong>Deep</strong>All kinds of martial matchings (e.g. "icrosoft" finds "Microsoft") - Since partial strings won\'t get indexed this search option is ways more cpu intensive and slower.',
    'LC__SEARCH__CONFIG__SUGGESTION_NOTE'                                      => 'Please note: Defaults do not work in auto suggestion',
    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH'                                => 'Automatic DeepSearch',
    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_NONACTIVE'                      => 'Deactivated',
    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE'                         => 'Active',
    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT'            => 'Active when no results are found.',
    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_DESCRIPTION'                    => 'Automatically starts the DeepSearch after the NormalSearch did not find anything.',
    'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION'                                  => 'Highlight the search string',
    'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION_DESCRIPTION'                      => 'Please note: If this setting is enabled, the links / quickinfos will be deactivated',
    'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING'                               => 'Minimum search string length',
    'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING_DESCRIPTION'                   => 'Notice: Also take a look at the <a href="https://kb.i-doit.com/display/de/Suche#Suche-Wortlänge" target="_blank">article in our Knowledge Base</a>!',
    'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS'              => 'Show archived/deleted objects in search results',
    'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS__DESCRIPTION' => 'Show archived/deleted objects in search results',
    'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS'                                => 'Show location paths in search results',
    'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS__DESCRIPTION'                   => 'Show location paths in search results',
    'LC__MODULE__SEARCH__START_INDEXING'                                       => 'Re-new search index',
    'LC__MODULE__SEARCH__START_INDEXING_CONFIRMATION'                          => 'Depending on the size of the database this job can take several minutes. Continue?',
];
