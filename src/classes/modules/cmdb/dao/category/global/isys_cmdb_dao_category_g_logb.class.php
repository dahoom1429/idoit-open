<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\IntProperty;
use idoit\Component\Property\Type\TextAreaProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * DAO: global category for logbook entries
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_logb extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'logb';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATG__LOGBOOK';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
//     protected $m_category_id = C__CATG__LOGBOOK;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Return Category Data
     *
     * @param [int $p_id]
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null, $p_limit = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_catg_logb_list " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = " . "isys_catg_logb_list__isys_obj__id " .
            "INNER JOIN isys_logbook " . "ON " . "isys_logbook__id = " . "isys_catg_logb_list__isys_logbook__id " . "LEFT JOIN isys_logbook_source " . "ON " .
            "isys_logbook__isys_logbook_source__id = " . "isys_logbook_source__id " .

            "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_catg_list_id)) {
            $l_sql .= " AND (isys_catg_logb_list__id = " . $this->convert_sql_id($p_catg_list_id) . ")";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_catg_logb_list__status = '{$p_status}')";
        }

        if ($p_limit) {
            $l_sql .= ' LIMIT ' . $this->m_db->escape_string($p_limit);
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * @param isys_component_dao_result $p_daores
     *
     * @return bool
     */
    public function init(isys_component_dao_result &$p_daores)
    {
        $this->m_daores = $p_daores;

        return true;
    }

    /**
     * @return array|DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    protected function dynamic_properties()
    {
        return [
            '_title' => new DynamicProperty(
                'LC__CMDB__LOGBOOK__TITLE',
                'isys_catg_logb_list__id',
                'isys_catg_logb_list',
                [
                    $this,
                    'dynamicPropertyCallbackTitle'
                ]
            ),
            '_category' => (new DynamicProperty(
                'LC__CMDB__CATG__GLOBAL_CATEGORY',
                'isys_catg_logb_list__isys_logbook__id',
                'isys_catg_logb_list',
                [
                    $this,
                    'dynamicPropertyCategory'
                ]
            ))
        ];
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamicPropertyCategory($p_row)
    {
        $query = 'SELECT isys_logbook__category_static as categoryTitle 
            FROM isys_logbook 
            WHERE isys_logbook__id = ' . $this->convert_sql_id($p_row['isys_catg_logb_list__isys_logbook__id']);
        $categoryTitle = trim($this->retrieve($query)->get_row_value('categoryTitle'));

        return !empty($categoryTitle) ? isys_application::instance()->container->get('language')->get($categoryTitle):
            isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        $query = 'SELECT %s FROM isys_catg_logb_list INNER JOIN isys_logbook ON isys_logbook__id = isys_catg_logb_list__isys_logbook__id';
        $joins = [
            SelectJoin::factory(
                'isys_catg_logb_list',
                'LEFT',
                'isys_catg_logb_list__isys_obj__id',
                'isys_obj__id'
            ),
            SelectJoin::factory(
                'isys_logbook',
                'LEFT',
                'isys_catg_logb_list__isys_logbook__id',
                'isys_logbook__id'
            )
        ];

        return [
            'date' => (new DateProperty(
                'C__CMDB__CATG__LOGB__DATE',
                'LC__CMDB__LOGBOOK__DATE',
                'isys_logbook__date',
                'isys_logbook',
                true
            ))->mergePropertyData(
                [
                    Property::C__PROPERTY__DATA__SELECT  => SelectSubSelect::factory(
                        sprintf($query, 'isys_logbook__date'),
                        'isys_catg_logb_list',
                        'isys_catg_logb_list__id',
                        'isys_catg_logb_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                    ),
                    Property::C__PROPERTY__DATA__JOIN => $joins
                ]
            ),
            'object' => (new IntProperty(
                '',
                'LC__CMDB__LOGBOOK__SOURCE__OBJECT',
                'isys_catg_logb_list__isys_obj__id',
                'isys_catg_logb_list',
                [
                    'isys_export_helper',
                    'object'
                ]
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT  => SelectSubSelect::factory(
                    'SELECT isys_obj__title
                              FROM isys_catg_logb_list
                              INNER JOIN isys_obj ON isys_obj__id = isys_catg_logb_list__isys_obj__id',
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => [
                    SelectJoin::factory(
                        'isys_catg_logb_list',
                        'LEFT',
                        'isys_catg_logb_list__isys_obj__id',
                        'isys_obj__id'
                    )
                ]
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'event' => (new DialogProperty(
                '',
                'LC__UNIVERSAL__EVENT',
                'isys_logbook__isys_logbook_event__id',
                'isys_catg_logb_list',
                'isys_logbook_event'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook_event__title') .
                    ' INNER JOIN isys_logbook_event ON isys_logbook_event__id = isys_logbook__isys_logbook_event__id',
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => array_merge($joins, [SelectJoin::factory(
                    'isys_logbook_event',
                    'LEFT',
                    'isys_logbook__isys_logbook_event__id',
                    'isys_logbook_event__id'
                )])
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false,
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'source' => (new DialogProperty(
                '',
                'LC__UNIVERSAL__SOURCE',
                'isys_logbook__isys_logbook_source__id',
                'isys_catg_logb_list',
                'isys_logbook_source'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook_source__title') .
                    ' INNER JOIN isys_logbook_source ON isys_logbook_source__id = isys_logbook__isys_logbook_source__id',
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => array_merge($joins, [SelectJoin::factory(
                    'isys_logbook_source',
                    'LEFT',
                    'isys_logbook__isys_logbook_source__id',
                    'isys_logbook_source__id'
                )])
            ])->mergePropertyProvides([
                    Property::C__PROPERTY__PROVIDES__SEARCH => false,
                    Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'user' => (new IntProperty(
                '',
                'LC__CMDB__LOGBOOK__SOURCE__USER',
                'isys_logbook__isys_obj__id',
                'isys_catg_logb_list',
                [
                    'isys_export_helper',
                    'object'
                ]
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__isys_obj__id'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'object_type' => (new TextProperty(
                '',
                'LC__CMDB__OBJTYPE',
                'isys_logbook__obj_type_static',
                'isys_logbook'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__obj_type_static'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'category' => (new TextProperty(
                '',
                'LC__CMDB__CATG__GLOBAL_CATEGORY',
                'isys_logbook__category_static',
                'isys_catg_logb_list'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__category_static'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false,
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'alert_level' => (new DialogProperty(
                '',
                'LC__CMDB__LOGBOOK__LEVEL',
                'isys_logbook__isys_logbook_level__id',
                'isys_catg_logb_list',
                'isys_logbook_level'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook_level__title') .
                    'INNER JOIN isys_logbook_level ON isys_logbook_level__id = isys_logbook__isys_logbook_level__id',
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => array_merge($joins, [SelectJoin::factory(
                    'isys_logbook_level',
                    'LEFT',
                    'isys_logbook__isys_logbook_level__id',
                    'isys_logbook_level__id'
                )])
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'user_name_static' => (new TextProperty(
                '',
                'LC__CMDB__LOGBOOK__USER',
                'isys_logbook__user_name_static',
                'isys_logbook'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__user_name_static'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__IMPORT => false,
                Property::C__PROPERTY__PROVIDES__EXPORT => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'event_static' => (new TextProperty(
                '',
                'LC__CMDB__CATG__LOGB__EVENT_STATIC',
                'isys_logbook__event_static',
                'isys_catg_logb_list'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__event_static'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__IMPORT => false,
                Property::C__PROPERTY__PROVIDES__EXPORT => false,
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false,
            ]),
            'comment' => (new TextAreaProperty(
                '',
                'LC__CATG__CONTACT_COMMENT',
                'isys_logbook__comment',
                'isys_logbook'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__comment'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__LIST => false
            ]),
            'changes' => (new TextProperty(
                '',
                'LC__UNIVERSAL__CHANGES',
                'isys_logbook__changes',
                'isys_catg_logb_list',
                C__RECORD_STATUS__NORMAL,
                [
                    'isys_export_helper',
                    'logbook_changes'
                ]
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__changes'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])->mergePropertyProvides([
                    Property::C__PROPERTY__PROVIDES__IMPORT => false,
                    Property::C__PROPERTY__PROVIDES__LIST   => false,
                    Property::C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'reason' => (new DialogPlusProperty(
                'C__CMDB__LOGBOOK__REASON',
                'LC__CMDB__CATG__ACCESS_TYPE',
                'isys_logbook__isys_logbook_reason__id',
                'isys_catg_logb_list',
                'isys_logbook_reason'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook_reason__title') .
                    ' INNER JOIN isys_logbook_reason ON isys_logbook_reason__id = isys_logbook__isys_logbook_reason__id',
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => array_merge($joins, [SelectJoin::factory(
                    'isys_logbook_reason',
                    'LEFT',
                    'isys_logbook__isys_logbook_reason__id',
                    'isys_logbook_reason__id'
                )])
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__LIST   => false
            ]),
            'description' => (new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL') . defined_or_default('C__CATG__LOGBOOK', 'C__CATG__LOGBOOK'),
                'isys_logbook__description',
                'isys_logbook'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT       => SelectSubSelect::factory(
                    sprintf($query, 'isys_logbook__description'),
                    'isys_catg_logb_list',
                    'isys_catg_logb_list__id',
                    'isys_catg_logb_list__isys_obj__id',
                    '',
                    '',
                    null,
                    SelectGroupBy::factory(['isys_catg_logb_list__isys_obj__id'])
                ),
                Property::C__PROPERTY__DATA__JOIN => $joins
            ])
        ];
    }

    /**
     * @param $dataSet
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamicPropertyCallbackTitle($dataSet)
    {
        $id = null;
        $return = '';
        if (isset($dataSet['isys_logbook__id'])) {
            $id = $dataSet['isys_logbook__id'];
        } elseif ($dataSet['isys_catg_logb_list__id']) {
            $id = $dataSet['isys_catg_logb_list__id'];
        }

        if ($id) {
            $dao = isys_cmdb_dao_category_g_logb::instance(isys_application::instance()->database);
            $query = 'SELECT 
                isys_logbook__event_static, 
                isys_logbook__obj_name_static, 
                isys_logbook__category_static, 
                isys_logbook__obj_type_static, 
                isys_logbook__entry_identifier_static, 
                isys_logbook__changecount
                FROM isys_catg_logb_list INNER JOIN isys_logbook ON isys_catg_logb_list__isys_logbook__id = isys_logbook__id
                WHERE isys_catg_logb_list__id = ' . $dao->convert_sql_id($id);

            $logbookRow = $dao->retrieve($query)
                ->get_row();
            $return = isys_event_manager::getInstance()
                ->translateEvent(
                    $logbookRow["isys_logbook__event_static"],
                    $logbookRow["isys_logbook__obj_name_static"],
                    $logbookRow["isys_logbook__category_static"],
                    $logbookRow["isys_logbook__obj_type_static"],
                    $logbookRow["isys_logbook__entry_identifier_static"],
                    $logbookRow["isys_logbook__changecount"]
                );
        }

        return $return;
    }

    /**
     * Sync method
     *
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_dao = new isys_component_dao_logbook($this->m_db);
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_dao->set_entry(
                        $p_category_data['properties']['event'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['date'][C__DATA__VALUE],
                        2,
                        $p_category_data['properties']['object'][C__DATA__VALUE],
                        'Test',
                        $p_category_data['properties']['object_type'][C__DATA__VALUE],
                        $p_category_data['properties']['category'][C__DATA__VALUE],
                        null,
                        null,
                        null
                    );

                    return $p_category_data['data_id'];
                    break;
            }
        }

        return false;
    }

    /**
     * @return int|mixed
     */
    public function save_element()
    {
        $l_dao = new isys_component_dao_logbook($this->m_db);

        $l_message = $_POST["C__CATG__LOGBOOK__MESSAGE"];
        $l_description = $_POST["C__CATG__LOGBOOK__DESCRIPTION"];

        if ($_POST["C__CATG__LOGBOOK__ALERTLEVEL"] > 0) {
            $l_alert_level = $_POST["C__CATG__LOGBOOK__ALERTLEVEL"];
        } else {
            $l_alert_level = defined_or_default('C__LOGBOOK__ALERT_LEVEL__0');
        }

        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];
        } else {
            $l_object_id = null;
        }

        $l_dao->set_entry(
            $l_message,
            $l_description,
            null,
            $l_alert_level,
            $l_object_id,
            $this->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]),
            $this->get_objtype_name_by_id_as_string($_GET[C__CMDB__GET__OBJECTTYPE]),
            null,
            defined_or_default('C__LOGBOOK_SOURCE__USER'),
            "",
            $_POST['LogbookCommentary'],
            $_POST['LogbookReason']
        );

        return 2;
    }
}
