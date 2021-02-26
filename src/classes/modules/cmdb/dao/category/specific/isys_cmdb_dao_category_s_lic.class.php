<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\IntProperty;

define('C__CATS__LICENCE_TYPE__SINGLE_LICENCE', 1);
define('C__CATS__LICENCE_TYPE__VOLUME_LICENCE', 2);

/**
 * i-doit
 *
 * DAO: specific category for license lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @todo        rename to licence key list
 */
class isys_cmdb_dao_category_s_lic extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'lic';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__LICENCE_LIST';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATS__LICENCE_LIST;

    /**
     * @var string
     */
    protected $m_entry_identifier = 'key';

    /**
     * Is the category multi-valued?
     *
     * @var  bool
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the amount of used licenses.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_used_licenses($p_row)
    {
        global $g_comp_database;

        $result = $g_comp_database->retrieveArrayFromResource($g_comp_database->query('SELECT isys_cats_lic_list__isys_obj__id, isys_cats_lic_list__amount FROM isys_cats_lic_list WHERE isys_cats_lic_list__id = ' . $this->convert_sql_int($p_row['isys_cats_lic_list__id'])))[0];

        $l_dao_licence = new isys_cmdb_dao_licences($g_comp_database, $result['isys_cats_lic_list__isys_obj__id']);

        return count($l_dao_licence->get_licences_in_use(C__RECORD_STATUS__NORMAL, $p_row['isys_cats_lic_list__id'])) . ' / ' . $result['isys_cats_lic_list__amount'];
    }

    /**
     * Dynamic property handling for getting the amount of free licenses.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function dynamic_property_callback_free_licenses($p_row)
    {
        global $g_comp_database;

        $result = $g_comp_database->retrieveArrayFromResource($g_comp_database->query('SELECT isys_cats_lic_list__isys_obj__id FROM isys_cats_lic_list WHERE isys_cats_lic_list__id = ' . $this->convert_sql_int($p_row['isys_cats_lic_list__id'])))[0];

        $l_dao_licence = new isys_cmdb_dao_licences($g_comp_database, $result['isys_cats_lic_list__isys_obj__id']);

        return $l_dao_licence->calculate_sum($result['isys_cats_lic_list__isys_obj__id'], $p_row['isys_cats_lic_list__id']) - count($l_dao_licence->get_licences_in_use(C__RECORD_STATUS__NORMAL, $p_row['isys_cats_lic_list__id']));
    }

    /**
     * Callback method for the licence type.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_type(isys_request $p_request)
    {
        $lang = isys_application::instance()->container->get('language');
        return [
            C__CATS__LICENCE_TYPE__SINGLE_LICENCE => $lang->get('LC__CMDB__CATS__LICENCE_TYPE__SINGLE'),
            C__CATS__LICENCE_TYPE__VOLUME_LICENCE => $lang->get('LC__CMDB__CATS__LICENCE_TYPE__VOLUME')
        ];
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return array|string
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        $language = isys_application::instance()->container->get('language');

        $l_lic_dao = new isys_cmdb_dao_licences($this->m_db, (int)$_GET[C__CMDB__GET__OBJECT]);

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                // Handle Ajax-Request.
                $l_return = [];

                try {
                    $l_licences = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL);
                } catch (isys_exception_dao_cmdb $l_e) {
                    die($l_e->getMessage());
                }

                if ($l_licences->num_rows() > 0) {
                    while ($l_line = $l_licences->get_row()) {
                        $l_title = $l_line['isys_obj__title'];

                        if (!empty($l_line['isys_cats_lic_list__key'])) {
                            $l_title = $l_line['isys_cats_lic_list__key'];
                        }

                        $l_free_licences = $l_line['isys_cats_lic_list__amount'] - $l_lic_dao->get_licences_in_use(C__RECORD_STATUS__NORMAL, $l_line['isys_cats_lic_list__id'])
                                ->num_rows();

                        if ($l_free_licences < 0) {
                            $l_free_licences = 0;
                        }

                        // Prepare return array.
                        $l_return[] = [
                            '__checkbox__'                                  => $l_line['isys_cats_lic_list__id'],
                            $language->get('LC__CMDB__CATS__LICENCE_TITLE') => $l_title,
                            $language->get('LC__UNIVERSAL__AVAILABLE')      => $l_free_licences . ' / ' . $l_line['isys_cats_lic_list__amount'],
                        ];
                    }
                }

                return json_encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                // Preselection
                $l_return = [
                    'category' => defined_or_default('C__OBJTYPE__LICENCE'),
                    'first'    => [],
                    'second'   => []
                ];

                if ($p_parameters['preselection'] > 0) {
                    // Save a bit memory: Only select needed fields!
                    $l_sql = "SELECT item.isys_cats_lic_list__amount, obj.isys_obj__id, obj.isys_obj__isys_obj_type__id, obj.isys_obj__title, obj.isys_obj__sysid
                        FROM isys_cats_lic_list AS item 
                        LEFT JOIN isys_obj AS obj ON isys_cats_lic_list__isys_obj__id = obj.isys_obj__id
                        WHERE item.isys_cats_lic_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " 
                        AND item.isys_cats_lic_list__id = " . $this->convert_sql_id($p_parameters['preselection']) . " 
                        LIMIT 1;";

                    $l_row = $this->retrieve($l_sql)->get_row();

                    $l_free_licences = $l_row['isys_cats_lic_list__amount'] - $l_lic_dao->get_licences_in_use(C__RECORD_STATUS__NORMAL, $p_parameters['preselection'])
                            ->num_rows();

                    if ($l_free_licences < 0) {
                        $l_free_licences = 0;
                    }

                    // @see  ID-6220  Also return a 'first' selection.
                    $l_return['first'][] = (int)$l_row['objectId'];

                    $l_return['second'] = [
                        $p_parameters['preselection'],
                        $l_row['isys_cats_lic_list__title'],
                        $l_free_licences . ' / ' . $l_row['isys_cats_lic_list__amount'],
                    ];
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = $this->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_port_list__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title']),
                            $categoryRow['isys_cats_lic_list__key']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                        $language->get('LC__CMDB__CATS__LICENCE_TITLE')
                    ],
                    'data'   => $preselection
                ];
        }
    }

    /**
     * This method returns the formatted text for the selection.
     *
     * @param   integer $p_license_id
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function format_selection($p_license_id, $p_plain = false)
    {
        $p_license_id = (int)$p_license_id;

        if ($p_license_id > 0) {
            $l_sql = "SELECT isys_obj__id, isys_obj__title, isys_cats_lic_list__key
				FROM isys_cats_lic_list
				LEFT JOIN isys_obj ON isys_obj__id = isys_cats_lic_list__isys_obj__id
				WHERE isys_cats_lic_list__id = " . $this->convert_sql_id($p_license_id) . ";";

            $l_license = $this->retrieve($l_sql)
                ->get_row();

            if (is_array($l_license)) {
                $l_editmode = ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT || isys_glob_get_param("editMode") == C__EDITMODE__ON ||
                    isys_glob_get_param("edit") == C__EDITMODE__ON);

                if (!empty($l_license["isys_cats_lic_list__key"])) {
                    return ((!$l_editmode && !$p_plain) ? (new isys_ajax_handler_quick_info)->get_quick_info(
                        $l_license['isys_obj__id'],
                        $l_license["isys_obj__title"] . ' >> ' . $l_license["isys_cats_lic_list__key"],
                        C__LINK__CATS,
                        false,
                        [
                            C__CMDB__GET__OBJECT   => $l_license['isys_obj__id'],
                            C__CMDB__GET__CATS     => defined_or_default('C__CATS__LICENCE_LIST'),
                            C__CMDB__GET__CATLEVEL => $p_license_id
                        ]
                    ) : $l_license["isys_obj__title"] . ' >> ' . $l_license["isys_cats_lic_list__key"]);
                }

                return ((!$l_editmode && !$p_plain) ? (new isys_ajax_handler_quick_info)->get_quick_info(
                    $l_license["isys_obj__id"],
                    $l_license["isys_obj__title"],
                    C__LINK__OBJECT
                ) : $l_license["isys_obj__title"]);
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for price
     *
     * @param   array $p_row
     *
     * @return  string
     * @throws Exception
     */
    public function dynamic_property_callback_cost($p_row)
    {
        $objId = ($p_row['isys_cats_lic_list__isys_obj__id'] ?: ($p_row['isys_obj__id'] ?: null));

        if ($objId === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $this->dynamicMonetaryFormatter($p_row, 'isys_cats_lic_list__cost', $objId);
    }

    /**
     * Method for retrieving the dynamic properties of every category dao.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_used_licenses' => new DynamicProperty(
                'LC__CMDB__CATS__LICENCE_IN_USE',
                'isys_cats_lic_list__id',
                'isys_cats_lic_list',
                [
                    $this,
                    'dynamic_property_callback_used_licenses'
                ]
            ),
            '_free_licenses' => new DynamicProperty(
                'LC__CMDB__CATS__LICENCE_FREE',
                'isys_cats_lic_list__id',
                'isys_cats_lic_list',
                [
                    $this,
                    'dynamic_property_callback_free_licenses'
                ]
            ),
            '_cost' => new DynamicProperty(
                'LC__CMDB__CATS__LICENCE_UNIT_PRICE',
                'isys_cats_lic_list__isys_obj__id',
                'isys_cats_lic_list',
                [
                    $this,
                    'dynamic_property_callback_cost'
                ]
            ),
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'key'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_KEY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Key'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__key',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_lic_list__key FROM isys_cats_lic_list',
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__LICENCE_KEY'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'serial'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Serial'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__serial',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_lic_list__serial FROM isys_cats_lic_list',
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__LICENCE_SERIAL'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'type'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Licence type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__type',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_lic_list__type = ' .
                        C__CATS__LICENCE_TYPE__SINGLE_LICENCE . ' THEN ' . $this->convert_sql_text('LC__CMDB__CATS__LICENCE_TYPE__SINGLE') . '
                                 WHEN isys_cats_lic_list__type = ' . C__CATS__LICENCE_TYPE__VOLUME_LICENCE . ' THEN ' .
                        $this->convert_sql_text('LC__CMDB__CATS__LICENCE_TYPE__VOLUME') . '
                                 ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value')) . ' END)
                                FROM isys_cats_lic_list',
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_lic_list', 'LEFT', 'isys_cats_lic_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => new isys_callback([
                            'isys_cmdb_dao_category_s_lic',
                            'callback_property_type'
                        ]),
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'amount'         => (new IntProperty(
                'C__CATS__LICENCE_AMOUNT',
                'LC__CMDB__CATS__LICENCE_AMOUNT',
                'isys_cats_lic_list__amount',
                'isys_cats_lic_list'
            )),
            'used_licences'  => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_IN_USE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Licences in use'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(
                              (
                                SELECT COUNT(isys_catg_application_list__id)
                                FROM isys_catg_application_list
                                INNER JOIN isys_cats_lic_list AS child2 ON child2.isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
                                INNER JOIN isys_obj AS client ON client.isys_obj__id = isys_catg_application_list__isys_obj__id
                                WHERE
                                isys_cats_lic_list__id = parent.isys_cats_lic_list__id
                                AND isys_cats_lic_list__status = ' . C__RECORD_STATUS__NORMAL . '
                                AND client.isys_obj__status = ' . C__RECORD_STATUS__NORMAL . '
                                AND isys_catg_application_list__status = ' . C__RECORD_STATUS__NORMAL . '
                              ),
                              \' / \',
                              parent.isys_cats_lic_list__amount
                            )
                            FROM isys_cats_lic_list AS parent',
                        'isys_cats_lic_list',
                        'parent.isys_cats_lic_list__id',
                        'parent.isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['parent.isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper_license',
                        'licence_property_used_licence'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false
                ]
            ]),
            'lic_not_in_use' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_FREE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Free licences'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(
                              parent.isys_cats_lic_list__amount
                              -
                              (
                                SELECT COUNT(isys_catg_application_list__id)
                                FROM isys_catg_application_list
                                INNER JOIN isys_cats_lic_list AS child2 ON child2.isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
                                INNER JOIN isys_obj AS client ON client.isys_obj__id = isys_catg_application_list__isys_obj__id
                                WHERE
                                isys_cats_lic_list__id = parent.isys_cats_lic_list__id
                                AND isys_cats_lic_list__status = ' . C__RECORD_STATUS__NORMAL . '
                                AND client.isys_obj__status = ' . C__RECORD_STATUS__NORMAL . '
                                AND isys_catg_application_list__status = ' . C__RECORD_STATUS__NORMAL . '
                              ), \'/\', parent.isys_cats_lic_list__amount
                            )
                            FROM isys_cats_lic_list parent',
                        'isys_cats_lic_list',
                        'parent.isys_cats_lic_list__id',
                        'parent.isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['parent.isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper_license',
                        'licence_property_lic_not_in_use'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false
                ]
            ]),
            'start'          => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_START',
                    C__PROPERTY__INFO__DESCRIPTION => 'Start Date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__start',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_lic_list__start FROM isys_cats_lic_list',
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_START',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'calendar'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'expire'         => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_EXPIRE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Expiration Date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__expire',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_lic_list__expire FROM isys_cats_lic_list',
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__LICENCE_EXPIRE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'calendar'
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true
                ]
            ]),
            'cost'           => array_replace_recursive(isys_cmdb_dao_category_pattern::money(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_UNIT_PRICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Costs'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__cost',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        isys_cmdb_dao_category_g_accounting::build_costs_select_join(
                            'isys_cats_lic_list',
                            'isys_cats_lic_list__cost'
                        ),
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_lic_list', 'LEFT', 'isys_cats_lic_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__LICENCE_COST'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST => true,
                    C__PROPERTY__PROVIDES__REPORT => false,
                ]
            ]),
            'overall_costs'  => array_replace_recursive(isys_cmdb_dao_category_pattern::money(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LICENCE_COST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Overall costs'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_lic_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        isys_cmdb_dao_category_g_accounting::build_costs_select_join(
                            'isys_cats_lic_list',
                            '(isys_cats_lic_list__cost * isys_cats_lic_list__amount)'
                        ),
                        'isys_cats_lic_list',
                        'isys_cats_lic_list__id',
                        'isys_cats_lic_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_lic_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper_license',
                        'licence_property_overall_costs'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                ]
            ]),
            // @todo properties for overal licences in use or overall free licences for object-list
            'description'    => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_lic_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__LICENCE_LIST', 'C__CATS__LICENCE_LIST')
                ],
            ])
        ];
    }
}
