<?php
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for accesses.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_access extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'access';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ACCESS';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Build query for property format_url
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function build_formatted_url_query()
    {
        // Replace %objectname%
        $l_title = 'REPLACE(
            REPLACE(acc.isys_catg_access_list__url, \'%idoit_host%\', \'%idoit_host_%\'),
            \'%objectname%\',
            ob.isys_obj__title
            )';

        // Replace %objectname_lowercase%
        $l_title = 'REPLACE(
            ' . $l_title . ',
            \'%objectname_lowercase%\',
            LOWER(ob.isys_obj__title)
            )';

        // Replace %objectname_upppercase%
        $l_title = 'REPLACE(
            ' . $l_title . ',
            \'%objectname_upppercase%\',
            UPPER(ob.isys_obj__title)
            )';

        // Replace %ipaddress%
        $l_title = 'REPLACE(
            ' . $l_title . ',
            \'%ipaddress%\',
            (CASE WHEN isys_cats_net_ip_addresses_list__title IS NULL OR isys_cats_net_ip_addresses_list__title = \'\' THEN \'\' ELSE isys_cats_net_ip_addresses_list__title END)
            )';

        // Replace %serial_no%
        $l_title = 'REPLACE(' . $l_title . ', \'%serial_no%\',
            (CASE WHEN isys_catg_model_list__serial IS NULL OR isys_catg_model_list__serial = \'\' THEN \'\' ELSE isys_catg_model_list__serial END)
            )';

        // Replace %sysid%
        $l_title = 'REPLACE(' . $l_title . ', \'%sysid%\',
            ob.isys_obj__sysid
            )';

        // Replace %objid%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%objid%\',
            ob.isys_obj__id
        )';

        // Replace %hostname%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%hostname%\',
            (CASE WHEN isys_catg_ip_list__hostname IS NULL OR isys_catg_ip_list__hostname = \'\' THEN \'\' ELSE isys_catg_ip_list__hostname END)
        )';

        // Replace %date_acquirement%
        $l_date_formats = isys_application::instance()->container->get('locales')->get_user_settings(LC_TIME);

        $l_title = 'REPLACE(' . $l_title . ',
            \'%date_acquirement%\',
            (CASE WHEN isys_catg_accounting_list__acquirementdate IS NULL OR isys_catg_accounting_list__acquirementdate = \'\' THEN \'\' ELSE DATE_FORMAT(isys_catg_accounting_list__acquirementdate, \'' .
            $l_date_formats['d_fmt_s'] . '\') END)
        )';

        // Replace %inventory_no%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%inventory_no%\',
            (CASE WHEN isys_catg_accounting_list__inventory_no IS NULL OR isys_catg_accounting_list__inventory_no = \'\' THEN \'\' ELSE isys_catg_accounting_list__inventory_no END)
        )';

        // Replace %date_changed%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%date_changed%\',
            DATE_FORMAT(isys_obj__updated, \'' . $l_date_formats['d_fmt_s'] . '\')
        )';

        // Replace %date_changed_raw%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%date_changed_raw%\',
            isys_obj__updated
        )';

        // Replace %date_created%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%date_created%\',
            DATE_FORMAT(isys_obj__created, \'' . $l_date_formats['d_fmt_s'] . '\')
        )';

        // Replace %date_created_raw%
        $l_title = 'REPLACE(' . $l_title . ',
            \'%date_created_raw%\',
            isys_obj__created
        )';

        // Maximum amount of possible ip addresses
        // @todo Placehodler for %ip_address#N% deactivated
        /*
        $l_sql = 'select COUNT(isys_catg_ip_list__isys_obj__id) AS cnt from isys_catg_ip_list
            WHERE isys_catg_ip_list__status = 2
            GROUP BY isys_catg_ip_list__isys_obj__id ORDER BY cnt DESC LIMIT 1';

        $l_count = $this->retrieve($l_sql)->get_row_value('cnt');


        $l_count = 4;
        $l_ip_joins = '';

        for($i = 1; $i <= $l_count; $i++)
        {
            $l_ip_query = 'IFNULL((SELECT isys_cats_net_ip_addresses_list__title FROM isys_catg_ip_list
              LEFT JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
              WHERE isys_catg_ip_list__isys_obj__id = ob.isys_obj__id limit ' . $i . ', 1), \'\')';
            $l_title = 'REPLACE(' . $l_title .', ' . $this->convert_sql_text('%ipaddress#' . $i . '%') . ', ' . $l_ip_query . ') ';
        }
        */

        return 'SELECT
             ' . $l_title . '

            FROM isys_catg_access_list as acc
            INNER JOIN isys_obj AS ob ON ob.isys_obj__id = acc.isys_catg_access_list__isys_obj__id
            LEFT JOIN isys_catg_model_list ON isys_obj__id = isys_catg_model_list__isys_obj__id
            LEFT JOIN isys_catg_accounting_list ON isys_obj__id = isys_catg_accounting_list__isys_obj__id
            LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id AND isys_catg_ip_list__primary = 1
            LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id';

        /*return 'SELECT
             ' . $l_title .  ' AS title

            FROM isys_catg_access_list
            INNER JOIN isys_obj AS ob ON ob.isys_obj__id = acc.isys_catg_access_list__isys_obj__id ' . ($p_primary ? ' AND acc.isys_catg_access_list__primary = 1 AND acc.isys_catg_access_list__url != \'\' ': '') . '
            LEFT JOIN isys_catg_model_list ON isys_obj__id = isys_catg_model_list__isys_obj__id
            LEFT JOIN isys_catg_accounting_list ON isys_obj__id = isys_catg_accounting_list__isys_obj__id
            LEFT JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id AND isys_catg_ip_list__primary = 1
            LEFT JOIN isys_cats_net_ip_addresses_list ON isys_cats_net_ip_addresses_list__id = isys_catg_ip_list__isys_cats_net_ip_addresses_list__id';
        */
    }

    /**
     * Dynamic property handling for getting the primary access url
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_primary_url($p_row)
    {
        $l_objectID = ($p_row['isys_catg_access_list__isys_obj__id'] ?: ($p_row['__id__'] ?: (($p_row['isys_obj__id']) ?: null)));

        if ($l_objectID) {
            $l_res = $this->get_primary_element($l_objectID);

            if ($l_res->num_rows() > 0) {
                $l_data = $l_res->get_row();

                return isys_helper_link::handle_url_variables($l_data['isys_catg_access_list__url'], $l_objectID);
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Dynamic property handling for getting the access url
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_url($p_row)
    {
        if (isset($p_row['isys_catg_access_list__id'])) {
            $sql = 'SELECT isys_catg_access_list__url, isys_catg_access_list__isys_obj__id 
                FROM isys_catg_access_list
                WHERE isys_catg_access_list__id = ' . $this->convert_sql_id($p_row['isys_catg_access_list__id']) . ';';

            $l_data = $this
                ->retrieve($sql)
                ->get_row();

            if (!empty($l_data)) {
                return isys_helper_link::handle_url_variables($l_data['isys_catg_access_list__url'], $l_data['isys_catg_access_list__isys_obj__id']);
            }
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Save global category access element
     *
     * @param  int $p_cat_level        level to save, default 0
     * @param  int &$p_intOldRecStatus __status of record before update
     * @param bool $p_create
     *
     * @return bool|null
     * @version Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_result()
            ->__to_array();
        $l_bRet = true;
        $p_intOldRecStatus = $l_catdata["isys_catg_access_list__status"];

        if ($p_create || !$l_catdata["isys_catg_access_list__id"]) {
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && $_POST['C__CATG__ACCESS_TITLE'] == "" && $_POST['C__CATG__ACCESS_TYPE'] == -1 &&
                $_POST['C__CATG__ACCESS_URL'] == "" && $_POST['C__CATG__ACCESS_PRIMARY'] == "0") {
                return null;
            }

            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__ACCESS_TITLE'],
                $_POST['C__CATG__ACCESS_TYPE'],
                $_POST['C__CATG__ACCESS_URL'],
                $_POST['C__CATG__ACCESS_PRIMARY'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id) {
                $this->m_strLogbookSQL = $this->get_last_query();
                $p_cat_level = 1;

                /**
                 * Return new category entry id
                 *
                 * @see ID-6022
                 */
                $l_bRet = $l_id;
            }
        } else {
            $l_bRet = $this->save(
                $l_catdata['isys_catg_access_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__ACCESS_TITLE'],
                $_POST['C__CATG__ACCESS_TYPE'],
                $_POST['C__CATG__ACCESS_URL'],
                $_POST['C__CATG__ACCESS_PRIMARY'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
            $l_id = $l_catdata['isys_catg_access_list__id'];
        }

        if ($_POST['C__CATG__ACCESS_PRIMARY'] == "1") {

            // if the entry is primary we set all other entries for this object to NOT primary
            // toggle all other isys_catg_access_list__primary to zero
            // which belong to the actual object
            $l_strSql = "UPDATE isys_catg_access_list SET " . "isys_catg_access_list__primary = 0 " . // not primary
                "WHERE isys_catg_access_list__id <> " . $this->convert_sql_id($l_id) . " " . // all but not the actual primary entry
                "AND isys_catg_access_list__isys_obj__id = " . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]);

            $this->m_strLogbookSQL .= "\n" . $l_strSql;
            $this->update($l_strSql) && $this->apply_update();
        }

        return $l_bRet;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_newRecStatus
     * @param   string  $p_title
     * @param   integer $p_accessTypeID
     * @param   string  $p_url
     * @param   integer $p_primary
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_access_list SET
			isys_catg_access_list__title = " . $this->convert_sql_text($p_title) . ",
			isys_catg_access_list__isys_access_type__id = " . $this->convert_sql_id($p_accessTypeID) . ",
			isys_catg_access_list__url  = " . $this->convert_sql_text($p_url) . ",
			isys_catg_access_list__primary = " . $this->convert_sql_boolean($p_primary) . ",
			isys_catg_access_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_access_list__status = " . $this->convert_sql_int($p_newRecStatus) . "
			WHERE isys_catg_access_list__id = " . $this->convert_sql_id($p_cat_level) . "";

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_access__id $p_fk_id
     *
     * @param int    $p_fk_id
     * @param int    $p_newRecStatus
     * @param String $p_title
     * @param String $p_manufacturerID
     * @param int    $p_frequencyID
     * @param int    $p_typeID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create($p_objID, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_access_list', $p_objID);
        if ($this->save($l_id, $p_newRecStatus, $p_title, $p_accessTypeID, $p_url, $p_primary, $p_description)) {
            return $l_id;
        }

        return false;
    }

    /**
     * Return result set for current primary access.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_primary_element($p_object_id = null)
    {
        $l_sql = "SELECT * FROM isys_catg_access_list
			LEFT OUTER JOIN isys_access_type ON isys_access_type__id = isys_catg_access_list__isys_access_type__id
			WHERE isys_catg_access_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . "
			AND isys_catg_access_list__primary = 1
			LIMIT 1;";

        return $this->retrieve($l_sql);
    }

    /**
     * Return URL from access list, or null.
     *
     * @param   integer $p_object_id
     *
     * @return  mixed
     * @author  Niclas Potthast <npotthast@i-doit.org> - 2006-03-02
     */
    public function get_url($p_object_id = null)
    {
        $l_res = $this->get_primary_element($p_object_id);

        if (is_countable($l_res) && count($l_res)) {
            return $l_res->get_row_value('isys_catg_access_list__url');
        }

        return null;
    }

    /**
     * Replaces all placeholders of the given url
     *
     * @deprecated Use isys_helper_link::handle_url_variables()
     * @param      $p_url
     * @param null $p_objID
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function format_url($p_url, $p_objID = null)
    {
        global $g_comp_database;

        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($g_comp_database);
        $l_primary_ip_data = $l_dao_ip->get_primary_ip($p_objID)
            ->get_row();

        $l_base_dir = rtrim(isys_helper_link::get_base(), '/');

        $l_replace_pairs = [
            '%idoit_host%' => $l_base_dir,
            '%hostname%'   => $l_primary_ip_data['isys_catg_ip_list__hostname'],
            '%ipaddress%'  => $l_primary_ip_data['isys_cats_net_ip_addresses_list__title'],
            '%objid%'      => $p_objID
        ];

        if (strpos(' ' . $p_url, '%ipaddress#') && $p_objID) {
            preg_match_all("/\%ipaddress\#\d*\%/", $p_url, $l_matches);
            if (isset($l_matches[0])) {
                $l_data = isys_cmdb_dao_category_data::initialize($p_objID)
                    ->path('C__CATG__IP')
                    ->data()
                    ->pluck('hostaddress')
                    ->toArray();

                foreach ($l_matches[0] as $l_key => $l_match) {
                    $l_pos = ((int)substr($l_match, strpos($l_match, '#') + 1, -1) - 1);
                    if (isset($l_data[$l_pos])) {
                        $l_replace_pairs['%ipaddress#' . ($l_pos + 1) . '%'] = $l_data[$l_pos];
                    }
                }
                isys_cmdb_dao_category_data::free($p_objID);
            }
        }

        return strtr($p_url, $l_replace_pairs);
    }

    public function pre_rank($p_list_id, $p_direction, $p_table)
    {
        if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE) {
            $l_primary_element = $this->get_data($p_list_id, $_GET[C__CMDB__GET__OBJECT], " AND isys_catg_access_list__primary = 1")
                ->get_row();

            if ($l_primary_element) {
                $this->set_primary($l_primary_element['isys_catg_access_list__id'], "unprimary");
            }
        }
    }

    public function post_rank($p_list_id, $p_direction, $p_table)
    {
        $l_rows = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT], null, null, C__RECORD_STATUS__NORMAL);
        $l_primary_element = $this->get_primary_element($_GET[C__CMDB__GET__OBJECT])
            ->get_row();
        $l_num = $l_rows->num_rows();

        if ($l_num && !$l_primary_element) {
            $l_row = $l_rows->get_row();
            $this->set_primary($l_row["isys_catg_access_list__id"], "primary");
        }
    }

    public function set_primary($p_list_id, $p_mode = null)
    {
        $l_sql = "UPDATE isys_catg_access_list SET isys_catg_access_list__primary = ";

        switch ($p_mode) {
            case 'primary':
                $l_sql .= "1 WHERE isys_catg_access_list__id = " . $p_list_id . ";";
                $this->update($l_sql);
                break;
            default:
            case 'unprimary':
                $l_sql .= "0 WHERE isys_catg_access_list__id  = " . $p_list_id . ";";
                $this->update($l_sql);
                break;
        }
        $this->apply_update();
    }

    /**
     * Dynamic properties for the report
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_primary_url' => new DynamicProperty(
                'LC__OBJECTDETAIL__ACCESS',
                'isys_catg_access_list__isys_obj__id',
                'isys_catg_access_list',
                [
                    $this,
                    'dynamic_property_callback_primary_url'
                ]
            ),
            '_url' => new DynamicProperty(
                'LC__CMDB__CATG__ACCESS_URL',
                'isys_catg_access_list__id',
                'isys_catg_access_list',
                [
                    $this,
                    'dynamic_property_callback_url'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'primary_url'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__OBJECTDETAIL__ACCESS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary URL'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__url',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        self::build_formatted_url_query(),
                        'isys_catg_access_list',
                        'acc.isys_catg_access_list__id',
                        'acc.isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([' AND acc.isys_catg_access_list__primary = 1']),
                        null,
                        '',
                        1
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_access_list', 'LEFT', 'isys_catg_access_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false
                ]
            ]),
            'title'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_access_list__title FROM isys_catg_access_list',
                        'isys_catg_access_list',
                        'isys_catg_access_list__id',
                        'isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_access_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCESS_TITLE'
                ]
            ]),
            'type'          => (new DialogPlusProperty(
                'C__CATG__ACCESS_TYPE',
                'LC__CMDB__CATG__ACCESS_TYPE',
                'isys_catg_access_list__isys_access_type__id',
                'isys_catg_access_list',
                'isys_access_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'url'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_URL',
                    C__PROPERTY__INFO__DESCRIPTION => 'URL'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__url',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_access_list__url FROM isys_catg_access_list',
                        'isys_catg_access_list',
                        'isys_catg_access_list__id',
                        'isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_access_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_access_list', 'LEFT', 'isys_catg_access_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCESS_URL',
                    C__PROPERTY__UI__PARAMS => [
                        'disableInputGroup' => true,
                        'p_bInfoIconSpacer' => 0
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'formatted_url' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_URL',
                    C__PROPERTY__INFO__DESCRIPTION => 'URL'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        self::build_formatted_url_query(),
                        'isys_catg_access_list',
                        'acc.isys_catg_access_list__id',
                        'acc.isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['acc.isys_catg_access_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'access_property_formatted_url'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'primary'       => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCESS_PRIMARY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Primary?'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__primary',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_access_list__primary = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . '
                                WHEN isys_catg_access_list__primary = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END) FROM isys_catg_access_list',
                        'isys_catg_access_list',
                        'isys_catg_access_list__id',
                        'isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_access_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_access_list', 'LEFT', 'isys_catg_access_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__ACCESS_PRIMARY',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_arData'     => get_smarty_arr_YES_NO(),
                        'p_bDbFieldNN' => 1
                    ],
                    C__PROPERTY__UI__DEFAULT => 1
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'description'   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_access_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_access_list__description FROM isys_catg_access_list',
                        'isys_catg_access_list',
                        'isys_catg_access_list__id',
                        'isys_catg_access_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_access_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ACCESS', 'C__CATG__ACCESS')
                ]
            ])
        ];
    }

    /**
     * @param array   $p_category_data
     * @param integer $p_object_id
     * @param integer $p_status
     *
     * @return bool|int
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['url'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            $p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['title'][C__DATA__VALUE],
                            $p_category_data['properties']['type'][C__DATA__VALUE],
                            $p_category_data['properties']['url'][C__DATA__VALUE],
                            $p_category_data['properties']['primary'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }
}
