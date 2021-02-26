<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;

/**
 * i-doit
 *
 * DAO: specific category for applications with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_application_assigned_obj extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'application_assigned_obj';

    /**
     * Field which holds the connected object id field if defined.
     *
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_application_list__isys_obj__id';

    /**
     * Name of property which should be used as identifier.
     *
     * @var string
     */
    protected $m_entry_identifier = 'object';

    /**
     * Should we generically handle a relation creation via property C__PROPERTY__DATA__RELATION_TYPE.
     *
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * Category's title.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_application_list';

    /**
     * Template name, because it re-uses another one.
     *
     * @var  string
     */
    protected $m_tpl = 'catg__application.tpl';

    /**
     * Create connector (for multivalue).
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * @param   integer $p_obj_id
     *
     * @return  integer
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $this->m_object_id;

        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        }

        $l_sql = 'SELECT COUNT(isys_catg_application_list__id) AS count
			FROM isys_catg_application_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_application_list__isys_obj__id
			INNER JOIN isys_connection ON isys_catg_application_list__isys_connection__id = isys_connection__id
			WHERE TRUE';

        if ($l_obj_id > 0) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        $l_sql .= ' AND isys_catg_application_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return (int)$this->retrieve($l_sql)
            ->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao = new isys_cmdb_dao_category_g_application($this->m_db);

        if ($p_obj_id > 0) {
            $l_condition = ' AND isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($p_obj_id);
        } else {
            $l_condition = '';
        }

        return $l_dao->get_data($p_cats_list_id, null, $l_condition, $p_filter, $p_status);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        $l_return = [
            'object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATS__APPLICATION_OBJ_APPLICATION',
                'LC__UNIVERSAL__INSTALLED_ON',
                'isys_catg_application_list__isys_obj__id',
                'isys_catg_application_list',
                '',
                [],
                'C__CATG__APPLICATION'
            ))->mergePropertyUiParams([
                \isys_popup_browser_object_ng::C__MULTISELECTION => false
            ])->setPropertyDataRelationType(
                new isys_callback([
                    'isys_cmdb_dao_category_s_application_assigned_obj',
                    'callback_property_relation_type_handler'
                ])
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_s_application_assigned_obj',
                    'callback_property_relation_handler'
                ], [
                    'isys_cmdb_dao_category_s_application_assigned_obj',
                    true
                ])
            )->mergePropertyData(
                [
                    Property::C__PROPERTY__DATA__REFERENCES => []
                ]
            )->setPropertyCheck([
                Property::C__PROPERTY__CHECK__MANDATORY => true
            ]),
            'application_type' => (new DialogProperty(
                'C__CATG__APPLICATION_TYPE',
                'LC__CATG__APPLICATION_TYPE',
                'isys_catg_application_list__isys_catg_application_type__id',
                'isys_catg_application_list',
                'isys_catg_application_type'
            ))->mergePropertyUiParams(
                [
                    'p_bDbFieldNN' => true
                ]
            )->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_application_type__title
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_application_list__isys_obj__id
                            INNER JOIN isys_catg_application_type ON isys_catg_application_type__id = isys_catg_application_list__isys_catg_application_type__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_type',
                        'LEFT',
                        'isys_catg_application_list__isys_catg_application_type__id',
                        'isys_catg_application_type__id'
                    )
                ]
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH     => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                Property::C__PROPERTY__PROVIDES__VIRTUAL    => true
            ]),
            'application_priority' => (new DialogProperty(
                'C__CATG__APPLICATION_PRIORITY',
                'LC__CATG__APPLICATION_PRIORITY',
                'isys_catg_application_list__isys_catg_application_priority__id',
                'isys_catg_application_list',
                'isys_catg_application_priority'
            ))->mergePropertyUiParams(
                [
                    'p_bDbFieldNN' => true
                ]
            )->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_application_priority__title
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_application_list__isys_obj__id
                            INNER JOIN isys_catg_application_priority ON isys_catg_application_priority__id = isys_catg_application_list__isys_catg_application_priority__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_priority',
                        'LEFT',
                        'isys_catg_application_list__isys_catg_application_priority__id',
                        'isys_catg_application_priority__id'
                    )
                ]
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH     => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                Property::C__PROPERTY__PROVIDES__VIRTUAL    => true
            ]),
            'assigned_license' => (new ObjectBrowserSecondListProperty(
                'C__CATG__LIC_ASSIGN__LICENSE',
                'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
                'isys_catg_application_list__isys_cats_lic_list__id',
                'isys_catg_application_list',
                'isys_cats_lic_list',
                'isys_cmdb_dao_category_s_lic::object_browser',
                'isys_cmdb_dao_category_s_lic::format_selection',
                [
                    'isys_global_application_export_helper',
                    'applicationLicence'
                ],
                'C__CATS__LICENCE',
                null,
                'isys_cats_lic_list__key'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, " {", isys_obj__id, "}")
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_cats_lic_list__isys_obj__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_cats_lic_list',
                        'LEFT',
                        'isys_catg_application_list__isys_cats_lic_list__id',
                        'isys_cats_lic_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_lic_list__isys_obj__id', 'isys_obj__id')
                ]
            ]),
            'assigned_license_key'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE_KEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned licence key for the application'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_application_list__isys_cats_lic_list__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_lic_list__key
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'assigned_database_schema' => (new ObjectBrowserProperty(
                'C__CATG__APPLICATION_DATABASE_SCHEMATA',
                'LC__CMDB__CATS__DATABASE_SCHEMA',
                'isys_catg_application_list__isys_catg_relation_list__id',
                'isys_catg_application_list',
                [
                    'isys_global_application_export_helper',
                    'applicationDatabaseSchema'
                ],
                'C__CATS__DATABASE_SCHEMA'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__REFERENCES => [
                    'isys_cats_database_access_list',
                    'isys_cats_database_access_list__id'
                ],
                Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, " {", isys_obj__id, "}")
                                FROM isys_cats_database_access_list
                                INNER JOIN isys_obj ON isys_obj__id = isys_cats_database_access_list__isys_obj__id
                                INNER JOIN isys_connection con1 ON con1.isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_catg_application_list ON isys_catg_application_list__isys_catg_relation_list__id = isys_catg_relation_list__id
                                INNER JOIN isys_connection con2 ON con2.isys_connection__id = isys_catg_application_list__isys_connection__id',
                    'isys_connection',
                    'con2.isys_connection__id',
                    'con2.isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['con2.isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_relation_list',
                        'LEFT',
                        'isys_catg_application_list__isys_catg_relation_list__id',
                        'isys_catg_relation_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_catg_relation_list__isys_obj__id',
                        'isys_connection__isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_cats_database_access_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_cats_database_access_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_cats_database_access_list__isys_obj__id', 'isys_obj__id')
                ]
            ]),
            'assigned_it_service' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATG__APPLICATION_IT_SERVICE',
                'LC__CMDB__CATG__IT_SERVICE',
                'isys_catg_application_list__isys_catg_relation_list__id',
                'isys_catg_application_list',
                '',
                [
                    'isys_global_application_export_helper',
                    'applicationItService'
                ],
                'C__CATG__SERVICE'
            ))->mergePropertyUiParams(
                [
                    'p_strSelectedID'                           => new isys_callback([
                        'isys_cmdb_dao_category_g_application',
                        'callback_property_assigned_it_service'
                    ])
                ]
            )->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, " {", isys_obj__id, "}")
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection AS con1 ON con1.isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_catg_application_list__isys_catg_relation_list__id
                            INNER JOIN isys_connection AS con2 ON con2.isys_connection__isys_obj__id = isys_catg_relation_list__isys_obj__id
                            INNER JOIN isys_catg_its_components_list ON isys_catg_its_components_list__isys_connection__id = con2.isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_its_components_list__isys_obj__id',
                    'con1.isys_connection',
                    'con1.isys_connection__id',
                    'con1.isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['con1.isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_relation_list',
                        'LEFT',
                        'isys_catg_application_list__isys_catg_relation_list__id',
                        'isys_catg_relation_list__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_catg_relation_list__isys_obj__id',
                        'isys_connection__isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_its_components_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_its_components_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_its_components_list__isys_obj__id', 'isys_obj__id')
                ]
            ]),
            'assigned_variant' => (new DialogProperty(
                'C__CATG__APPLICATION_VARIANT__VARIANT',
                'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
                'isys_catg_application_list__isys_cats_app_variant_list__id',
                'isys_catg_application_list',
                'isys_cats_app_variant_list',
                false,
                [
                    'isys_specific_application_assigned_obj_export_helper',
                    'applicationAssignedVariant'
                ]
            ))->mergePropertyUiParams([
                'p_arData' => new isys_callback([
                    'isys_cmdb_dao_category_s_application_assigned_obj',
                    'callback_property_assigned_variant'
                ])
            ])->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_cats_app_variant_list__title
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_cats_app_variant_list ON isys_cats_app_variant_list__id = isys_catg_application_list__isys_cats_app_variant_list__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_cats_app_variant_list',
                        'LEFT',
                        'isys_catg_application_list__isys_cats_app_variant_list__id',
                        'isys_cats_app_variant_list__id'
                    )
                ]
            ]),
            'assigned_version' => (new DialogProperty(
                'C__CATG__APPLICATION_VERSION',
                'LC__CATG__VERSION_TITLE',
                'isys_catg_application_list__isys_catg_version_list__id',
                'isys_catg_application_list',
                'isys_catg_version_list',
                false,
                [
                    'isys_specific_application_assigned_obj_export_helper',
                    'applicationAssignedVersion'
                ]
            ))->mergePropertyUiParams([
                'p_arData' => new isys_callback([
                    'isys_cmdb_dao_category_s_application_assigned_obj',
                    'callback_property_assigned_version'
                ])
            ])->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_version_list__title
                            FROM isys_catg_application_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
                            INNER JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_version_list',
                        'LEFT',
                        'isys_catg_application_list__isys_catg_version_list__id',
                        'isys_catg_version_list__id'
                    )
                ]
            ]),
            'bequest_nagios_services' => (new DialogYesNoProperty(
                'C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES',
                'LC__CMDB__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES',
                'isys_catg_application_list__bequest_nagios_services',
                'isys_catg_application_list',
                1
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT (CASE WHEN isys_catg_application_list__bequest_nagios_services = "1" THEN ' .
                    $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                        WHEN isys_catg_application_list__bequest_nagios_services = "0" THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                        FROM isys_catg_application_list
                        INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    )
                ]
            ]),
            'assigned_databases' => (new ObjectBrowserMultiselectProperty(
                'C__CATG__APPLICATION_ASSIGNED_DATABASES',
                'LC__CATG__APPLICATION_ASSIGNED_DATABASES',
                'isys_catg_application_list__id',
                'isys_catg_application_list',
                'isys_catg_application_list_2_isys_catg_database_sa_list',
                null,
                'isys_catg_database_sa_list',
                [
                    'isys_global_application_export_helper',
                    'assignedDatabases'
                ],
                'C__CATG__DATABASE_FOLDER',
                'CONCAT(obj.isys_obj__title, \' >> \', ref.isys_catg_database_sa_list__title)'
            ))->mergePropertyUiParams([
                \isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                \isys_popup_browser_object_ng::C__SECOND_LIST => 'isys_cmdb_dao_category_g_application::objectBrowserAssignedDatabases',
                \isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_g_application::formatSelectionAssignedDatabases',
                'p_strSelectedID' => new isys_callback([
                    'isys_cmdb_dao_category_g_application',
                    'callbackGetAttachedDatabaseIds'
                ])
            ]),
            'description' => (new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__APPLICATION', 'C__CATG__APPLICATION'),
                'isys_catg_application_list__description',
                'LC__CMDB__CATG__DESCRIPTION'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT isys_catg_application_list__description
                        FROM isys_catg_application_list
                        INNER JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_application_list',
                        'LEFT',
                        'isys_connection__id',
                        'isys_catg_application_list__isys_connection__id'
                    )
                ]
            ])
        ];

        return $l_return;
    }

    /**
     * @param   $p_objects
     * @param   $p_direction
     * @param   $p_table
     *
     * @return  boolean
     */
    public function rank_records($p_objects, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        return parent::rank_records($p_objects, $p_direction, "isys_catg_application_list", $p_checkMethod, $p_purge);
    }

    /**
     * Rank single record
     *
     * @param int    $categoryEntryId
     * @param int    $p_direction
     * @param string $p_table
     * @param null   $p_checkMethod
     * @param bool   $p_purge
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function rank_record($categoryEntryId, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        // Get data, status, targetStatus and relation id of application
        $data = $this->get_data($categoryEntryId, null, null, null, null)
            ->get_row();
        $status = $data['isys_catg_application_list__status'];
        $targetStatus = ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) ? $status + 1 : $status - 1;
        $applicationRelationEntryId = $data['isys_catg_application_list__isys_catg_relation_list__id'];

        // Run default ranking procedure
        $mainRanking = parent::rank_record($categoryEntryId, $p_direction, $p_table, $p_checkMethod, $p_purge);

        if ($targetStatus >= C__RECORD_STATUS__PURGE) {
            return $mainRanking;
        }

        // Check whether it was successfully
        if ($mainRanking && !empty($applicationRelationEntryId)) {
            // Querying db access relation
            $sql = '
                SELECT rel2.isys_catg_relation_list__id AS dbAccessRelation FROM isys_catg_relation_list rel1
                INNER JOIN isys_catg_relation_list rel2 ON rel2.isys_catg_relation_list__isys_obj__id__slave = rel1.isys_catg_relation_list__isys_obj__id 
                WHERE rel1.isys_catg_relation_list__id = ' . $this->convert_sql_id($applicationRelationEntryId) . '
            ';

            $resource = $this->retrieve($sql);

            // Check whether it exists or not
            if ($resource->num_rows()) {
                $relationEntryId = $resource->get_row_value('dbAccessRelation');

                // Update db access relation status
                if (!empty($relationEntryId)) {
                    $sql = '
                        UPDATE isys_catg_relation_list
                        SET isys_catg_relation_list__status = ' . $this->convert_sql_id($targetStatus) . '
                        WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($relationEntryId) . '
                    ';

                    $this->update($sql) && $this->apply_update();
                }
            }
        }

        return $mainRanking;
    }

    /**
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['object'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_license'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_it_service'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_variant'][C__DATA__VALUE],
                        $p_category_data['properties']['bequest_nagios_services'][C__DATA__VALUE],
                        $p_category_data['properties']['application_type'][C__DATA__VALUE],
                        $p_category_data['properties']['application_priority'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_version'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_databases'][C__DATA__VALUE]
                    );

                    if ($p_category_data['data_id'] > 0) {
                        $l_indicator = true;
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['object'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_license'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_database_schema'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_it_service'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_variant'][C__DATA__VALUE],
                        $p_category_data['properties']['bequest_nagios_services'][C__DATA__VALUE],
                        $p_category_data['properties']['application_type'][C__DATA__VALUE],
                        $p_category_data['properties']['application_priority'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_version'][C__DATA__VALUE],
                        $p_category_data['properties']['assigned_databases'][C__DATA__VALUE]
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Callback method which returns the relation type because application assignment has two relation types:
     * - C__RELATION_TYPE__OPERATION_SYSTEM
     * - C__RELATION_TYPE__SOFTWARE
     *
     * @param   isys_request $p_request
     *
     * @return  integer
     * @throws  isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_type_handler(isys_request $p_request)
    {
        $l_dao = isys_cmdb_dao_category_s_application_assigned_obj::instance($this->m_db);
        $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
            ->get_row();

        if ($l_data['isys_catg_application_list__isys_catg_application_type__id'] == defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            return defined_or_default('C__RELATION_TYPE__OPERATION_SYSTEM');
        }
        return defined_or_default('C__RELATION_TYPE__SOFTWARE');
    }

    /**
     * Callback method for property assigned_variant.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_assigned_variant(isys_request $p_request)
    {
        return isys_cmdb_dao_category_s_application_assigned_obj::instance(isys_application::instance()->database)
            ->get_variants($p_request->get_object_id());
    }

    /**
     * Callback method for property versions.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_assigned_version(isys_request $p_request)
    {
        return isys_cmdb_dao_category_s_application_assigned_obj::instance(isys_application::instance()->database)
            ->get_versions($p_request->get_object_id());
    }

    /**
     * Gets all entries from category variant from the given object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_variants($p_obj_id)
    {
        if (is_null($p_obj_id)) {
            return [];
        }

        global $g_comp_database;

        $l_dao = isys_cmdb_dao_category_s_application_variant::instance($g_comp_database);
        $l_res = $l_dao->get_data(null, $p_obj_id);
        $l_data = [];

        while ($l_row = $l_res->get_row()) {
            $l_data[$l_row['isys_cats_app_variant_list__id']] = $l_row['isys_cats_app_variant_list__variant'] .
                ($l_row['isys_cats_app_variant_list__title'] != '' ? ' (' . $l_row['isys_cats_app_variant_list__title'] . ')' : '');
        }

        return $l_data;
    }

    /**
     * Gets all entries from category version from the given object id.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_versions($p_obj_id, $withPatchLevel = false)
    {
        if (is_null($p_obj_id)) {
            return [];
        }

        global $g_comp_database;

        $l_data = [];
        $l_res = isys_cmdb_dao_category_g_version::instance($g_comp_database)
            ->get_data(null, $p_obj_id);

        if (is_countable($l_res) && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_data[$l_row['isys_catg_version_list__id']] = $l_row['isys_catg_version_list__title'] .
                    ($withPatchLevel && !empty($l_row['isys_catg_version_list__hotfix']) ? ' (' . $l_row['isys_catg_version_list__hotfix'] . ')' : '');
            }
        }

        return $l_data;
    }

    /**
     * Save global category application element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @throws  isys_exception_general
     * @return  int|null
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
        }

        if ($_POST['C__CATG__APPLICATION_TYPE'] != defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            $_POST['C__CATG__APPLICATION_PRIORITY'] = null;
        }

        if ($p_create) {
            // Overview page and no input was given
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && empty($_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN'])) {
                return null;
            }

            $l_applications = $_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN'];

            if (isys_format_json::is_json_array($l_applications)) {
                $l_applications = isys_format_json::decode($l_applications);
            }

            if (!is_array($l_applications)) {
                $l_applications = [$l_applications];
            }

            foreach ($l_applications as $l_application) {
                $l_id = $this->create(
                    $_GET[C__CMDB__GET__OBJECT],
                    C__RECORD_STATUS__NORMAL,
                    $l_application,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                    $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_DATABASE_SCHEMATA__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_IT_SERVICE__HIDDEN"],
                    $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"],
                    $_POST['C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES'],
                    $_POST['C__CATG__APPLICATION_TYPE'],
                    $_POST['C__CATG__APPLICATION_PRIORITY'],
                    $_POST['C__CATG__APPLICATION_VERSION'],
                    $_POST['C__CATG__APPLICATION_ASSIGNED_DATABASES__HIDDEN']
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                if ($l_id) {
                    $l_catdata['isys_catg_application_list__id'] = $l_id;
                    $l_bRet = true;
                    $p_cat_level = null;
                } else {
                    throw new isys_exception_dao("Could not create category element application");
                }
            }
        } else {
            $l_catdata = $this->get_result()
                ->get_row();
            $p_intOldRecStatus = $l_catdata["isys_catg_application_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_application_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATS__APPLICATION_OBJ_APPLICATION__HIDDEN'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST["C__CATG__LIC_ASSIGN__LICENSE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_DATABASE_SCHEMATA__HIDDEN"],
                $_POST["C__CATG__APPLICATION_IT_SERVICE__HIDDEN"],
                $_POST["C__CATG__APPLICATION_VARIANT__VARIANT"],
                $_POST['C__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES'],
                $_POST['C__CATG__APPLICATION_TYPE'],
                $_POST['C__CATG__APPLICATION_PRIORITY'],
                $_POST['C__CATG__APPLICATION_VERSION'],
                $_POST['C__CATG__APPLICATION_ASSIGNED_DATABASES__HIDDEN']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        if ($p_create) {
            return $l_catdata["isys_catg_application_list__id"];
        }

        return $l_bRet == true ? null : $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param integer $p_cat_level
     * @param integer $p_newRecStatus
     * @param integer $p_connectedObjID
     * @param string  $p_description
     * @param integer $p_licence
     * @param integer $p_database_schemata_obj
     * @param mixed   $p_it_service_obj
     * @param integer $p_variant
     * @param integer $p_bequest_nagios_services
     * @param integer $p_type
     * @param integer $p_priority
     * @param null    $p_version
     * @param null    $assignedDatabases
     *
     * @return  null
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_connectedObjID,
        $p_description,
        $p_licence,
        $p_database_schemata_obj,
        $p_it_service_obj,
        $p_variant = null,
        $p_bequest_nagios_services = null,
        $p_type = null,
        $p_priority = null,
        $p_version = null,
        $assignedDatabases = null
    ) {
        if ($p_type === null) {
            $p_type = defined_or_default('C__CATG__APPLICATION_TYPE__SOFTWARE');
        }

        if ($p_type != defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            $p_priority = null;
        }

        $p_it_service_obj = (is_array($p_it_service_obj)) ? $p_it_service_obj : trim($p_it_service_obj);

        if (isys_format_json::is_json_array($p_it_service_obj)) {
            $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
        }

        if (isys_format_json::is_json_array($assignedDatabases)) {
            $assignedDatabases = isys_format_json::decode($assignedDatabases);
        }

        if (empty($assignedDatabases)) {
            $assignedDatabases = [];
        }

        $l_old_data = $this->get_data($p_cat_level)
            ->__to_array();
        $l_app_obj_id = $l_old_data['isys_connection__isys_obj__id'];

        // Update software assignment
        $l_strSql = "UPDATE isys_catg_application_list SET " . "isys_catg_application_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ", " .
            "isys_catg_application_list__description = " . $this->convert_sql_text($p_description) . ", " . "isys_catg_application_list__status = " .
            $this->convert_sql_id($p_newRecStatus) . ", " . "isys_catg_application_list__isys_cats_app_variant_list__id = " . $this->convert_sql_id($p_variant) . ", " .
            "isys_catg_application_list__isys_cats_lic_list__id = " . $this->convert_sql_id($p_licence) . ", " . "isys_catg_application_list__bequest_nagios_services = " .
            $this->convert_sql_boolean($p_bequest_nagios_services) . ", " . "isys_catg_application_list__isys_catg_application_type__id = " . $this->convert_sql_id($p_type) .
            ", " . "isys_catg_application_list__isys_catg_application_priority__id = " . $this->convert_sql_id($p_priority) . ", " .
            "isys_catg_application_list__isys_catg_version_list__id = " . $this->convert_sql_id($p_version) . " " . "WHERE isys_catg_application_list__id = " .
            $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {
            // Handle relation
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_data = $this->get_data($p_cat_level)
                ->__to_array();

            $l_relation_dao->handle_relation(
                $p_cat_level,
                "isys_catg_application_list",
                ($p_type == defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')? defined_or_default('C__RELATION_TYPE__OPERATION_SYSTEM') : defined_or_default('C__RELATION_TYPE__SOFTWARE')),
                $l_data["isys_catg_application_list__isys_catg_relation_list__id"],
                $p_connectedObjID,
                $l_app_obj_id
            );

            if ($p_connectedObjID > 0) {
                $l_data = $this->get_data($l_data["isys_catg_application_list__id"])
                    ->__to_array();

                if ($l_data["isys_catg_application_list__isys_catg_relation_list__id"] != "") {
                    $l_rel_data = $l_relation_dao->get_data($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                        ->__to_array();
                    $l_dao_dbms_access = isys_cmdb_dao_category_s_database_access::instance($this->get_database_component());
                    $l_dao_its_comp = isys_cmdb_dao_category_g_it_service_components::instance($this->get_database_component());

                    if (is_numeric($p_database_schemata_obj) && $p_database_schemata_obj > 0) {
                        $l_dbms_res = $l_dao_dbms_access->get_data(
                            null,
                            null,
                            "AND isys_connection__isys_obj__id = " . $l_dao_dbms_access->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                            null,
                            C__RECORD_STATUS__NORMAL
                        );
                        if ($l_dbms_res->num_rows() < 1) {
                            $l_dao_dbms_access->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                        } else {
                            if ($l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"])) {
                                $l_dao_dbms_access->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                            }
                        }
                    } else {
                        $l_dao_dbms_access->delete_connection($l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                    }

                    $l_assigned_it_services = array_flip(isys_cmdb_dao_category_g_application::instance($this->m_db)
                        ->get_assigned_it_services($l_data["isys_catg_application_list__isys_catg_relation_list__id"]));

                    if (is_array($p_it_service_obj) && count($p_it_service_obj)) {
                        foreach ($p_it_service_obj as $l_it_service) {
                            $l_it_service_res = $l_dao_its_comp->get_data(
                                null,
                                $l_it_service,
                                "AND isys_connection__isys_obj__id = " . $l_dao_its_comp->convert_sql_id($l_rel_data["isys_catg_relation_list__isys_obj__id"]),
                                null,
                                C__RECORD_STATUS__NORMAL
                            );

                            if ($l_it_service_res->num_rows() < 1) {
                                $l_dao_its_comp->create($l_it_service, C__RECORD_STATUS__NORMAL, $l_rel_data["isys_catg_relation_list__isys_obj__id"], "");
                            } else {
                                unset($l_assigned_it_services[$l_it_service]);
                            }
                        }
                    }

                    if (count($l_assigned_it_services) > 0) {
                        foreach ($l_assigned_it_services as $l_it_serv_obj_id => $l_dummy) {
                            $l_dao_its_comp->remove_component($l_it_serv_obj_id, $l_rel_data["isys_catg_relation_list__isys_obj__id"]);
                        }
                    }

                    if (is_array($assignedDatabases)) {
                        isys_cmdb_dao_category_g_application::instance($this->get_database_component())
                            ->attachDatabases($p_cat_level, $assignedDatabases);
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Create method.
     *
     * @param integer $p_objID
     * @param integer $p_newRecStatus
     * @param integer $p_connectedObjID
     * @param string  $p_description
     * @param integer $p_licence
     * @param integer $p_database_schemata_obj
     * @param integer $p_it_service_obj
     * @param integer $p_variant
     * @param integer $p_bequest_nagios_services
     * @param integer $p_type
     * @param integer $p_priority
     * @param null    $p_version
     * @param null    $assignedDatabases
     *
     * @return  mixed
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_newRecStatus,
        $p_connectedObjID,
        $p_description,
        $p_licence = null,
        $p_database_schemata_obj = null,
        $p_it_service_obj = null,
        $p_variant = null,
        $p_bequest_nagios_services = 1,
        $p_type = null,
        $p_priority = null,
        $p_version = null,
        $assignedDatabases = null
    ) {
        if ($p_type === null) {
            $p_type = defined_or_default('C__CATG__APPLICATION_TYPE__SOFTWARE');
        }

        if ($p_type != defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM')) {
            $p_priority = null;
        }

        $l_connection = isys_cmdb_dao_connection::instance($this->m_db);

        $l_sql = "INSERT INTO isys_catg_application_list SET
			isys_catg_application_list__isys_connection__id = " . $this->convert_sql_id($l_connection->add_connection($p_objID)) . ",
			isys_catg_application_list__isys_obj__id = " . $this->convert_sql_id($p_connectedObjID) . ",
			isys_catg_application_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_application_list__status = " . $this->convert_sql_id($p_newRecStatus) . ",
			isys_catg_application_list__isys_cats_app_variant_list__id = " . $this->convert_sql_id($p_variant) . ",
			isys_catg_application_list__isys_cats_lic_list__id = " . $this->convert_sql_id($p_licence) . ",
			isys_catg_application_list__bequest_nagios_services = " . $this->convert_sql_boolean($p_bequest_nagios_services) . ",
			isys_catg_application_list__isys_catg_application_type__id = " . $this->convert_sql_id($p_type) . ",
			isys_catg_application_list__isys_catg_version_list__id = " . $this->convert_sql_id($p_version) . ",
			isys_catg_application_list__isys_catg_application_priority__id = " . $this->convert_sql_id($p_priority) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();

            // Handle software relation.
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_relation_dao->handle_relation(
                $l_last_id,
                "isys_catg_application_list",
                ($p_type == defined_or_default('C__CATG__APPLICATION_TYPE__OPERATING_SYSTEM') ? defined_or_default('C__RELATION_TYPE__OPERATION_SYSTEM') : defined_or_default('C__RELATION_TYPE__SOFTWARE')),
                null,
                $p_connectedObjID,
                $p_objID
            );

            if ($p_connectedObjID > 0) {
                $l_data = $this->get_data($l_last_id)
                    ->get_row();

                if ($l_data["isys_catg_application_list__isys_catg_relation_list__id"] != "") {
                    $l_rel_data = $l_relation_dao->get_data($l_data["isys_catg_application_list__isys_catg_relation_list__id"])
                        ->get_row();

                    if (is_numeric($p_database_schemata_obj) && $p_database_schemata_obj > 0) {
                        isys_cmdb_dao_category_s_database_access::instance($this->get_database_component())
                            ->create($p_database_schemata_obj, $l_rel_data["isys_catg_relation_list__isys_obj__id"], C__RECORD_STATUS__NORMAL);
                    }

                    // Handle IT-Service
                    if (isys_format_json::is_json_array($p_it_service_obj)) {
                        $p_it_service_obj = isys_format_json::decode($p_it_service_obj);
                    }

                    if (isys_format_json::is_json_array($assignedDatabases)) {
                        $assignedDatabases = isys_format_json::decode($assignedDatabases);
                    }

                    if (is_array($p_it_service_obj) && count($p_it_service_obj) > 0) {
                        $l_dao_its_comp = isys_cmdb_dao_category_g_it_service_components::instance($this->get_database_component());

                        foreach ($p_it_service_obj as $l_it_serv_obj_id) {
                            $l_dao_its_comp->create($l_it_serv_obj_id, C__RECORD_STATUS__NORMAL, $l_rel_data["isys_catg_relation_list__isys_obj__id"], "");
                        }
                    }

                    if (is_array($assignedDatabases) && count($assignedDatabases)) {
                        isys_cmdb_dao_category_g_application::instance($this->get_database_component())
                            ->attachDatabases($l_last_id, $assignedDatabases);
                    }
                }
            }

            return $l_last_id;
        } else {
            return false;
        }
    }
}
