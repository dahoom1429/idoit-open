<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.5.3
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (include_once('isys_module_search_autoload.class.php')) {
    spl_autoload_register('isys_module_search_autoload::init');
}

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\Search', __DIR__ . '/src/');

$optionYesNo = get_smarty_arr_YES_NO();
$language = isys_application::instance()->container->get('language');

isys_tenantsettings::extend([
    'LC__UNIVERSAL__SEARCH' => [
        'defaults.search.mode'                => [
            'title'       => 'LC__SEARCH__CONFIG__MODE',
            'type'        => 'select',
            'options'     => \idoit\Module\Search\Query\Condition::$modes,
            'description' => $language->get('LC__SEARCH__CONFIG__SUGGESTION_NOTE') . '<br /><br />' .
                $language->get('LC__SEARCH__CONFIG__NORMAL_DESCRIPTION') . '<br /><br />' .
                $language->get('LC__SEARCH__CONFIG__DEEP_DESCRIPTION'),
            'default'     => '0',
        ],
        'search.global.autostart-deep-search' => [
            'title'       => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH',
            'type'        => 'select',
            'options'     => [
                isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE              => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE',
                isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT',
                isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE           => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_NONACTIVE'
            ],
            'description' => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_DESCRIPTION',
            'default'     => isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE
        ],
        'search.highlight-search-string'      => [
            'title'       => 'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION',
            'type'        => 'select',
            'options'     => $optionYesNo,
            'description' => 'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION_DESCRIPTION',
            'default'     => '1',
        ],
        'search.minlength.search-string'      => [
            'title'       => 'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING',
            'description' => 'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING_DESCRIPTION',
            'type'        => 'int',
            'placeholder' => 3,
            'default'     => 3
        ],
        'search.index.include_archived_deleted_objects' => [
            'title'       => 'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS',
            'type'        => 'select',
            'options'     => $optionYesNo,
            'description' => 'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS__DESCRIPTION',
            'default'     => '0',
        ],
        'search.index.location_paths' => [
            'title'       => 'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS',
            'type'        => 'select',
            'options'     => $optionYesNo,
            'description' => 'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS__DESCRIPTION',
            'default'     => '0',
        ]
    ]
]);
