<?php

use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global category for accounting
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_accounting extends isys_cmdb_dao_category_global
{
    const C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE = 1;
    const C__GUARANTEE_PERIOD_BASE__ORDER_DATE      = 2;
    const C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE   = 3;

    protected static $m_placeholder_counter = [];

    protected static $m_placeholder_counter_arr = [];

    // Current counter for each object type
    protected static $m_placeholder_date_data = null;

    // Array for the placeholder %COUNTER% or %COUNTER#n%

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'accounting';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__ACCOUNTING';

    // Array with date data

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Get guarantee period base
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_guarantee_period_base()
    {
        return [
            self::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE => 'LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_BASE_BY_DATE_OF_INVOICE',
            self::C__GUARANTEE_PERIOD_BASE__ORDER_DATE      => 'LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_BASE_BY_ORDER_DATE',
            self::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE   => 'LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_BASE_BY_DELIVERY_DATE',
        ];
    }

    /**
     * String which is the last inventory number in the replacement recursion
     *
     * @var string
     */
    private $inventoryNoPlaceholdersReplaced = '';

    /**
     * Get all possible placeholders
     *
     * @param boolean $p_as_description
     * @param integer $p_obj_id
     * @param integer $p_obj_type_id
     * @param string  $p_obj_title
     * @param string  $p_obj_sysid
     *
     * @return array
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public static function get_placeholders_info_with_data($p_as_description = false, $p_obj_id = null, $p_obj_type_id = null, $p_obj_title = null, $p_obj_sysid = null)
    {
        $lang = isys_application::instance()->container->get('language');

        if (self::$m_placeholder_date_data === null) {
            $l_date_data = explode('_', date('Y_y_m_d_h_i_s'));
            self::$m_placeholder_date_data['%Y%'] = $l_date_data[0];
            self::$m_placeholder_date_data['%y%'] = $l_date_data[1];
            self::$m_placeholder_date_data['%m%'] = $l_date_data[2];
            self::$m_placeholder_date_data['%d%'] = $l_date_data[3];
            self::$m_placeholder_date_data['%h%'] = $l_date_data[4];
            self::$m_placeholder_date_data['%i%'] = $l_date_data[5];
            self::$m_placeholder_date_data['%s%'] = $l_date_data[6];
        }
        self::$m_placeholder_date_data['%TIMESTAMP%'] = time();

        $l_arr = [];

        if ($p_obj_id !== null) {
            $l_arr['%OBJID%'] = $p_obj_id;
        }

        if ($p_obj_type_id !== null) {
            $l_arr['%OBJTYPEID%'] = $p_obj_type_id;
        }

        if ($p_obj_title !== null) {
            $l_arr['%OBJTITLE%'] = $p_obj_title;
        }

        if ($p_obj_sysid !== null) {
            $l_arr['%SYSID%'] = $p_obj_sysid;
        }

        $l_arr['%TIMESTAMP%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__TIMESTAMP') . ' (' . self::$m_placeholder_date_data['%TIMESTAMP%'] . ')'
            : self::$m_placeholder_date_data['%TIMESTAMP%'];

        $l_arr['%Y%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__FULL_YEAR') . ' (' . self::$m_placeholder_date_data['%Y%'] . ')'
            : self::$m_placeholder_date_data['%Y%'];

        $l_arr['%y%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__YEAR') . ' (' . self::$m_placeholder_date_data['%y%'] . ')'
            : self::$m_placeholder_date_data['%y%'];

        $l_arr['%m%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__MONTH') . ' (' . self::$m_placeholder_date_data['%m%'] . ')'
            : self::$m_placeholder_date_data['%m%'];

        $l_arr['%d%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__DAY') . ' (' . self::$m_placeholder_date_data['%d%'] . ')'
            : self::$m_placeholder_date_data['%d%'];

        $l_arr['%h%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__HOUR') . ' (' . self::$m_placeholder_date_data['%h%'] . ')'
            : self::$m_placeholder_date_data['%h%'];

        $l_arr['%i%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__MINUTE') . ' (' . self::$m_placeholder_date_data['%i%'] . ')'
            : self::$m_placeholder_date_data['%i%'];

        $l_arr['%s%'] = ($p_as_description)
            ? $lang->get('LC__UNIVERSAL__PLACEHOLDER__SECOND') . ' (' . self::$m_placeholder_date_data['%s%'] . ')'
            : self::$m_placeholder_date_data['%s%'];

        if ($p_as_description) {
            $l_arr['%COUNTER%'] = $lang->get('LC__UNIVERSAL__PLACEHOLDER__COUNTER') . ' (42)';
            $l_arr['%COUNTER#N%'] = $lang->get('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N') . ' (' . $lang->get('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N_EXAMPLE') . ')';
            $l_arr['%COUNTER:N#N%'] = $lang->get('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N_N') . '<br />(' . $lang->get('LC__UNIVERSAL__PLACEHOLDER__COUNTER_N_N_EXAMPLE') . ')';
        }

        return $l_arr;
    }

    /**
     * Checks if string has any placeholders
     *
     * @param $p_check_string
     *
     * @return bool
     */
    public static function has_placeholders($p_check_string)
    {
        $p_check_string = strtolower($p_check_string);
        if (strpos($p_check_string, '%objid%') !== false || strpos($p_check_string, '%objtypeid%') !== false || strpos($p_check_string, '%objtitle%') !== false ||
            strpos($p_check_string, '%sysid%') !== false || strpos($p_check_string, '%timestamp%') !== false || strpos($p_check_string, '%y%') !== false ||
            strpos($p_check_string, '%m%') !== false || strpos($p_check_string, '%d%') !== false || strpos($p_check_string, '%counter') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Resets the member variables for the %COUNTER% placeholder.
     */
    public static function reset_placeholder_data()
    {
        //self::$m_placeholder_counter_arr = array();
        self::$m_placeholder_counter = [];
    }

    /**
     * Dynamic property handling for price
     *
     * @param   array $p_row
     *
     * @return  string
     * @throws Exception
     */
    public function dynamic_property_callback_price($p_row)
    {
        return $this->dynamicMonetaryFormatter($p_row, 'isys_catg_accounting_list__price', $p_row['isys_catg_accounting_list__isys_obj__id']);
    }

    /**
     * Dynamic property handling for operation expense
     *
     * @param $p_row
     *
     * @return null|string
     * @throws Exception
     * @throws isys_exception_general
     */
    public function dynamic_property_callback_operation_expense($p_row)
    {
        global $g_comp_database;

        $l_return = null;
        if (!empty($p_row['isys_catg_accounting_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_accounting::instance($g_comp_database);
            $l_data = $l_dao->get_data($p_row['isys_catg_accounting_list__id'])->get_row();

            if ($l_data['isys_catg_accounting_list__operation_expense'] > 0) {
                // Decimal seperator from the user configuration.
                $l_monetary = isys_application::instance()->container['locales']->fmt_monetary($l_data['isys_catg_accounting_list__operation_expense']);
                $l_monetary_tmp = explode(" ", $l_monetary);
                $l_return = $l_monetary_tmp[0] . ' ' . $l_monetary_tmp[1];
                if ($l_data['isys_catg_accounting_list__isys_interval__id'] > 0) {
                    $l_return .= ' ' . isys_application::instance()->container->get('language')
                            ->get(isys_factory_cmdb_dialog_dao::get_instance('isys_interval', $g_comp_database)
                                ->get_data($l_data['isys_catg_accounting_list__isys_interval__id'])['isys_interval__title']);
                }
            }
        }

        return $l_return;
    }

    /**
     * Dynamic property callback to handle contact only for object list
     *
     * @param $p_row
     *
     * @return string
     * @throws isys_exception_general
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_contact($p_row)
    {
        global $g_comp_database;
        $l_strOut = '-';

        if (isset($p_row['isys_catg_accounting_list__isys_contact__id'])) {
            $result = isys_cmdb_dao_category_g_contact::instance(isys_application::instance()->container->get('database'))
                ->get_assigned_contacts_by_relation_id($p_row['isys_catg_accounting_list__isys_contact__id']);

            while ($data = $result->get_row()) {
                $l_contacts[$data['isys_obj__id']] = $data['isys_obj__title'];
            }
        } else {
            $l_contacts = isys_cmdb_dao_category_g_accounting::instance($g_comp_database)
                ->get_purchased_at(null, $p_row);
        }

        if (is_countable($l_contacts) && count($l_contacts) > 0) {
            $l_strOut = '';
            foreach ($l_contacts as $l_cont_obj_id => $l_cont_obj_title) {
                $l_strOut .= $l_cont_obj_title . ' {' . $l_cont_obj_id . '}, ';
            }
        }

        return rtrim($l_strOut, ', ');
    }

    /**
     * Dynamic property callback for calculating the guarantee date for reports
     *
     * @param $p_row
     *
     * @return bool|null|string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_guarantee_date($p_row)
    {
        if (isset($p_row['isys_catg_accounting_list__id'])) {
            $l_dao = isys_cmdb_dao_category_g_accounting::instance(isys_application::instance()->database);
            $l_row = $l_dao->get_data($p_row['isys_catg_accounting_list__id'])
                ->get_row();

            if ($l_row["isys_catg_accounting_list__guarantee_period"] && $l_row["isys_guarantee_period_unit__id"]) {
                switch ($l_row['isys_catg_accounting_list__guarantee_period_base']) {
                    case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE:
                        $l_date = strtotime($l_row['isys_catg_accounting_list__delivery_date']);
                        break;
                    case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__ORDER_DATE:
                        $l_date = strtotime($l_row['isys_catg_accounting_list__order_date']);
                        break;
                    case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE:
                        $l_date = strtotime($l_row['isys_catg_accounting_list__acquirementdate']);
                        break;
                    default:
                        $l_date = time();
                        break;
                }

                return date(
                    isys_application::instance()->container->locales->get_date_format(true),
                    $l_dao->calculate_guarantee_date($l_date, $l_row["isys_catg_accounting_list__guarantee_period"], $l_row["isys_guarantee_period_unit__id"])
                );
            }
        }

        return null;
    }

    /**
     * @param $dataSet
     *
     * @return string|null
     *
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_guarantee_status($dataSet)
    {
        if ($dataSet['isys_catg_accounting_list__id'] > 0) {
            $categoryData = $this->get_data($dataSet['isys_catg_accounting_list__id'])->get_row();

            if (empty($categoryData)) {
                return null;
            }

            $period = $this->get_dialog("isys_guarantee_period_unit", $categoryData["isys_catg_accounting_list__isys_guarantee_period_unit__id"])
                ->get_row();

            switch ($categoryData['isys_catg_accounting_list__guarantee_period_base']) {
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE:
                    $date = strtotime($categoryData['isys_catg_accounting_list__delivery_date']);
                    break;
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__ORDER_DATE:
                    $date = strtotime($categoryData['isys_catg_accounting_list__order_date']);
                    break;
                case isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE:
                    $date = strtotime($categoryData['isys_catg_accounting_list__acquirementdate']);
                    break;
                default:
                    $date = time();
                    break;
            }

            return ($this->calculate_guarantee_status(
                $date,
                $categoryData["isys_catg_accounting_list__guarantee_period"],
                $period["isys_guarantee_period_unit__const"]
            ) ?: null);
        }
        return null;
    }

    /**
     * Callback method for the device dialog-field.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   isys_request            $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_contact(isys_request $p_request)
    {
        global $g_comp_database;

        return isys_cmdb_dao_category_g_accounting::instance($g_comp_database)
            ->get_purchased_at($p_request);
    }

    /**
     * Calculate guarantee end date
     *
     * @param $p_date_from
     * @param $p_guarantee_period
     * @param $p_guarantee_period_unit
     *
     * @return int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function calculate_guarantee_date($p_date_from, $p_guarantee_period, $p_guarantee_period_unit)
    {
        $l_period_unit = (!is_numeric($p_guarantee_period_unit)) ? constant($p_guarantee_period_unit) : $p_guarantee_period_unit;

        $units = filter_array_by_keys_of_defined_constants([
            'C__GUARANTEE_PERIOD_UNIT_DAYS'  => 'days',
            'C__GUARANTEE_PERIOD_UNIT_WEEKS' => 'weeks',
            'C__GUARANTEE_PERIOD_UNIT_MONTH' => 'months',
            'C__GUARANTEE_PERIOD_UNIT_YEARS' => 'years',
        ]);
        if (isset($units[$l_period_unit])) {
            $l_guarantee_enddate = strtotime("+{$p_guarantee_period} {$units[$l_period_unit]}", $p_date_from);
        } else {
            $l_guarantee_enddate = 0;
        }

        return $l_guarantee_enddate;
    }

    /**
     * Method for calculating the guarantee status.
     *
     * @param   string  $p_acquirementdate
     * @param   integer $p_guarantee_period
     * @param   mixed   $p_guarantee_period_unit
     *
     * @return  mixed
     */
    public function calculate_guarantee_status($p_acquirementdate, $p_guarantee_period, $p_guarantee_period_unit)
    {
        if (is_numeric($p_guarantee_period) && $p_guarantee_period_unit != '') {
            $l_calc_result = null;
            $l_guarantee_enddate = $this->calculate_guarantee_date($p_acquirementdate, $p_guarantee_period, $p_guarantee_period_unit);

            if (time() < $l_guarantee_enddate) {
                $l_guarantee_enddate_OBJ = new DateTime();
                $l_guarantee_enddate_OBJ->setTimestamp($l_guarantee_enddate);
                $l_date_diff = (array)date_diff(new DateTime(), $l_guarantee_enddate_OBJ);

                $l_calc_result = [];

                if ($l_date_diff["y"] > 0) {
                    $l_calc_result[] = $l_date_diff["y"] . ' ' . ($l_date_diff["y"] == 1 ? isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__YEAR") : isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__YEARS"));
                }

                if ($l_date_diff["m"] > 0) {
                    $l_calc_result[] = $l_date_diff["m"] . ' ' . ($l_date_diff["m"] == 1 ? isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__MONTH") : isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__MONTHS"));
                }

                if ($l_date_diff["w"] > 0) {
                    $l_calc_result[] = $l_date_diff["w"] . ' ' . ($l_date_diff["w"] == 1 ? isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__WEEK") : isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__WEEKS"));
                }

                if ($l_date_diff["d"] > 0) {
                    $l_calc_result[] = $l_date_diff["d"] . ' ' . ($l_date_diff["d"] == 1 ? isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__DAY") : isys_application::instance()->container->get('language')
                            ->get("LC__UNIVERSAL__DAYS"));
                }

                // Rendering a nice output!
                if (count($l_calc_result) > 1) {
                    $l_calc_result = implode(', ', array_slice($l_calc_result, 0, -1)) . ' ' . isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__AND') . ' ' . end($l_calc_result);
                } else {
                    $l_calc_result = current($l_calc_result);
                }
            } else {
                if ($p_guarantee_period > 0) {
                    $l_calc_result = isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__GUARANTEE_EXPIRED");
                }
            }

            return $l_calc_result;
        } else {
            return false;
        }
    }

    /**
     * Replaces all placeholders in the passed string
     *
     * @param string      $p_data_string
     * @param int|null    $p_obj_id
     * @param int|null    $p_obj_type_id
     * @param string|null $p_strTitle
     * @param string|null $p_strSYSID
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    public function replace_placeholders(
        $p_data_string,
        $p_obj_id = null,
        $p_obj_type_id = null,
        $p_strTitle = null,
        $p_strSYSID = null,
        $p_table = 'isys_catg_accounting_list'
    ) {
        try {
            $l_replaced_string = $p_data_string;
            if (strpos(' ' . $l_replaced_string, '%COUNTER')) {
                // Set current counter
                if (!isset(self::$m_placeholder_counter[$p_table][$p_data_string])) {
                    $l_sql = 'SELECT MAX(' . $p_table . '__id) AS cnt FROM ' . $p_table;

                    self::$m_placeholder_counter[$p_table][$p_data_string] = (string)$this->retrieve($l_sql)
                        ->get_row_value('cnt');
                }

                // Set placeholders
                if (!isset(self::$m_placeholder_counter_arr[$p_table][$p_data_string])) {
                    self::$m_placeholder_counter_arr[$p_table][$p_data_string] = [];
                    preg_match_all("/\%COUNTER([\#\,\:])\d*\%|\%COUNTER\%|\%COUNTER\:\d*\#\d*\%/", $l_replaced_string, $l_matches);

                    if ($l_matches !== false) {
                        if (count($l_matches[0]) > 0) {
                            foreach ($l_matches[0] as $l_placeholder) {
                                $l_length = $l_count_from = 0;
                                $l_count_from_pos = strpos($l_placeholder, ':');
                                $l_length_pos = strpos($l_placeholder, '#');

                                if ($l_count_from_pos && $l_length_pos) {
                                    $l_count_from = substr($l_placeholder, $l_count_from_pos + 1, ($l_length_pos - $l_count_from_pos - 1));
                                    self::$m_placeholder_counter[$p_table][$p_data_string] = $l_count_from;
                                }

                                if ($l_length_pos) {
                                    $l_length = substr($l_placeholder, $l_length_pos + 1, -1);
                                }

                                self::$m_placeholder_counter_arr[$p_table][$p_data_string][] = $l_length > 0 ?
                                    ($l_count_from > 0 ?
                                        ['%COUNTER:' . $l_count_from . '#' . $l_length . '%', $l_length] :
                                        ['%COUNTER#' . $l_length . '%', $l_length]) :
                                    ['%COUNTER%'];
                            }
                        }
                    }
                }

                // Replace placeholder %COUNTER% and %COUNTER#N% in string
                if (isset(self::$m_placeholder_counter_arr[$p_table][$p_data_string]) && is_array(self::$m_placeholder_counter_arr[$p_table][$p_data_string])) {
                    self::$m_placeholder_counter[$p_table][$p_data_string] = self::$m_placeholder_counter[$p_table][$p_data_string] + 1;
                    $l_counter = self::$m_placeholder_counter[$p_table][$p_data_string];
                    array_map(function ($p_placeholder) use (&$l_replaced_string, $l_counter) {
                        $l_zeros = '';

                        if (is_array($p_placeholder)) {
                            $l_replace = isset($p_placeholder[0]) ? $p_placeholder[0] : '';

                            if (isset($p_placeholder[1]) && is_numeric($p_placeholder[1])) {
                                $l_placeholder_cnt = (int)$p_placeholder[1];
                                $l_cnt = strlen($l_counter);
                                if ($l_cnt < $l_placeholder_cnt) {
                                    $l_zeros = str_repeat('0', $l_placeholder_cnt - $l_cnt);
                                }
                            }

                            if ($l_replace !== '') {
                                $l_replaced_string = str_replace($l_replace, $l_zeros . $l_counter, $l_replaced_string);
                            }
                        }
                    }, self::$m_placeholder_counter_arr[$p_table][$p_data_string]);
                }
            }
            $l_replaced_string = strtr($l_replaced_string, self::get_placeholders_info_with_data(false, $p_obj_id, $p_obj_type_id, $p_strTitle, $p_strSYSID));
            $result = $this->retrieve('SELECT isys_catg_accounting_list__id FROM isys_catg_accounting_list WHERE isys_catg_accounting_list__inventory_no = ' .
                $this->convert_sql_text($l_replaced_string));

            // Return string with replaced placeholders or if it did not change in the replacement to prevent infinite loop
            if ($result->count() == 0 || $this->inventoryNoPlaceholdersReplaced === $l_replaced_string) {
                $this->inventoryNoPlaceholdersReplaced = null;

                if ($p_table === 'isys_catg_accounting_list' && $this->validate(['inventory_no' => $l_replaced_string]) !== true) {
                    $message = isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO_VALIDATION_FAILED', $l_replaced_string);

                    isys_notify::warning($message, ['sticky' => true]);
                    return null;
                }

                return $l_replaced_string;
            } else {
                $this->inventoryNoPlaceholdersReplaced = $l_replaced_string;

                return $this->replace_placeholders($p_data_string, $p_obj_id, $p_obj_type_id, $p_strTitle, $p_strSYSID, $p_table);
            }
        } catch (Exception $e) {
            throw new Exception('Placeholders in ' . $p_data_string . ' could not be replaced. With message: ' . $e->getMessage());
        }
    }

    /**
     * @param  integer $p_object_id
     * @param  string  $p_strSYSID
     * @param  integer $p_obj_type_id
     * @param  string  $p_strTitle
     * @param          $p_unused1
     * @param          $p_unused2
     *
     * @return mixed
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function signal_auto_inventory_no($p_object_id, $p_strSYSID, $p_obj_type_id, $p_strTitle, $p_unused1, $p_unused2)
    {
        if (($l_auto_inventory = trim(isys_tenantsettings::get('cmdb.objtype.' . $p_obj_type_id . '.auto-inventory-no', ''))) !== '') {
            $l_insert = 'INSERT INTO isys_catg_accounting_list SET 
                isys_catg_accounting_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
                isys_catg_accounting_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

            if (!$this->update($l_insert)) {
                throw new isys_exception_cmdb("Unable to generate the inventory number.");
            } else {
                $this->apply_update();
                $accountingId = $this->get_last_insert_id();

                // inventory number is actual only after inserting ID. see ID-6844
                $actualInventoryNo = $this->replace_placeholders($l_auto_inventory, $p_object_id, $p_obj_type_id, $p_strTitle, $p_strSYSID);

                if ($actualInventoryNo) {
                    $updateSql = 'UPDATE isys_catg_accounting_list SET 
                    isys_catg_accounting_list__inventory_no = ' . $this->convert_sql_text($actualInventoryNo) . '
                    WHERE isys_catg_accounting_list__id = ' . $this->convert_sql_id($accountingId) . ';';

                    $this->update($updateSql);

                    $this->apply_update();
                }
            }
        }
    }

    /**
     * Dynamic property price
     *
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_price' => new DynamicProperty(
                'LC__CMDB__CATG__GLOBAL_PRICE',
                'isys_catg_accounting_list__isys_obj__id',
                'isys_catg_accounting_list',
                [
                    $this,
                    'dynamic_property_callback_price'
                ]
            ),
            '_operation_expense' => new DynamicProperty(
                'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE',
                'isys_catg_accounting_list__id',
                'isys_catg_accounting_list',
                [
                    $this,
                    'dynamic_property_callback_operation_expense'
                ]
            ),
            '_contact' => new DynamicProperty(
                'LC__CMDB__CATG__GLOBAL_PURCHASED_AT',
                'isys_catg_accounting_list__isys_contact__id',
                'isys_catg_accounting_list',
                [
                    $this,
                    'dynamic_property_callback_contact'
                ]
            ),
            '_guarantee_date' => new DynamicProperty(
                'LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE',
                'isys_catg_accounting_list__id',
                'isys_catg_accounting_list',
                [
                    $this,
                    'dynamic_property_callback_guarantee_date'
                ]
            ),
            '_guarantee_status' => new DynamicProperty(
                'LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS',
                'isys_catg_accounting_list__id',
                'isys_catg_accounting_list',
                [
                    $this,
                    'dynamic_property_callback_guarantee_status'
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
            'inventory_no'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Inventory number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__inventory_no'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_INVENTORY_NO'
                ]
            ]),
            'account' => new DialogPlusProperty(
                'C__CATG__ACCOUNTING__ACCOUNT',
                'LC__CMDB__CATG__ACCOUNTING_ACCOUNT',
                'isys_catg_accounting_list__isys_account__id',
                'isys_catg_accounting_list',
                'isys_account'
            ),
            // @todo need to retrieve the date format
            'acquirementdate'            => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_DATE_OF_INVOICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Acquirement date'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__acquirementdate'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_ACQUIRE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'calendar',
                        'p_bTime'        => 0
                    ]
                ]
            ]),
            'contact'                    => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PURCHASED_AT',
                    C__PROPERTY__INFO__DESCRIPTION => 'Purchased at'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_accounting_list__isys_contact__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_contact',
                        'isys_contact__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id ,\'}\')
                                FROM isys_catg_accounting_list
                                INNER JOIN isys_contact_2_isys_obj ON isys_contact_2_isys_obj__isys_contact__id = isys_catg_accounting_list__isys_contact__id
                                INNER JOIN isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id',
                        'isys_catg_accounting_list',
                        'isys_catg_accounting_list__id',
                        'isys_catg_accounting_list__isys_obj__id',
                        '',
                        '',
                        null,
                        \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_accounting_list__isys_obj__id']),
                        'isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_accounting_list',
                            'LEFT',
                            'isys_catg_accounting_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_2_isys_obj',
                            'LEFT',
                            'isys_catg_accounting_list__isys_contact__id',
                            'isys_contact_2_isys_obj__isys_contact__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_contact_2_isys_obj__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__PURCHASE_CONTACT',
                    C__PROPERTY__UI__PARAMS => [
                        'multiselection'  => true,
                        'catFilter'       => 'C__CATS__PERSON;C__CATS__PERSON_GROUP;C__CATS__ORGANIZATION',
                        'p_strSelectedID' => new isys_callback([
                            'isys_cmdb_dao_category_g_accounting',
                            'callback_property_contact'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'contact'
                    ]
                ]
            ]),
            'price'                      => array_replace_recursive(isys_cmdb_dao_category_pattern::money(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PRICE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Cash value / Price'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_accounting_list__price',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(self::build_costs_select_join(
                        'isys_catg_accounting_list',
                        'isys_catg_accounting_list__price'
                    ), 'isys_catg_accounting_list', 'isys_catg_accounting_list__id', 'isys_catg_accounting_list__isys_obj__id')
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_PRICE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'             => 'input-mini',
                        C__PROPERTY__UI__DEFAULT => null
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]),
            'operation_expense'          => array_replace_recursive(isys_cmdb_dao_category_pattern::money(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Operational expense'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_accounting_list__operation_expense',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        self::build_costs_select_join(
                            'isys_catg_accounting_list',
                            'isys_catg_accounting_list__operation_expense'
                        ),
                        'isys_catg_accounting_list',
                        'isys_catg_accounting_list__id',
                        'isys_catg_accounting_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING__OPERATION_EXPENSE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass'             => 'input-mini',
                        C__PROPERTY__UI__DEFAULT => null
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]),
            'operation_expense_interval' => (new DialogProperty(
                'C__CATG__ACCOUNTING__OPERATION_EXPENSE_INTERVAL',
                'LC__CMDB__CATG__ACCOUNTING__OPERATION_EXPENSE__UNIT',
                'isys_catg_accounting_list__isys_interval__id',
                'isys_catg_accounting_list',
                'isys_interval'
            ))->mergePropertyUiParams(
                [
                    'p_strClass' => 'input-mini'
                ]
            )->mergePropertyProvides(
                [
                    Property::C__PROPERTY__PROVIDES__REPORT => false
                ]
            ),
            'cost_unit' => new DialogPlusProperty(
                'C__CATG__ACCOUNTING_COST_UNIT',
                'LC__CMDB__CATG__ACCOUNTING_COST_UNIT',
                'isys_catg_accounting_list__isys_catg_accounting_cost_unit__id',
                'isys_catg_accounting_list',
                'isys_catg_accounting_cost_unit'
            ),
            'delivery_note_no'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_DELIVERY_NOTE_NO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Delivery note no.'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__delivery_note_no'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_DELIVERY_NOTE_NO'
                ]
            ]),
            'procurement' => new DialogPlusProperty(
                'C__CATG__ACCOUNTING_PROCUREMENT',
                'LC__CMDB__CATG__ACCOUNTING_PROCUREMENT',
                'isys_catg_accounting_list__isys_catg_accounting_procurement__id',
                'isys_catg_accounting_list',
                'isys_catg_accounting_procurement'
            ),
            'delivery_date'              => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_DELIVERY_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Delivery date'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__delivery_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_DELIVERY_DATE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'calendar',
                        'p_bTime'        => 0
                    ]
                ]
            ]),
            'invoice_no'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_INVOICE_NO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Invoice no.'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__invoice_no'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_INVOICE_NO'
                ]
            ]),
            'order_no'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_ORDER_NO',
                    C__PROPERTY__INFO__DESCRIPTION => 'Order no.'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__order_no'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_ORDER_NO'
                ]
            ]),
            'guarantee_period'           => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Period of warranty'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__guarantee_period'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_GUARANTEE_PERIOD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini',
                        C__PROPERTY__UI__DEFAULT => null
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'timeperiod',
                        [null],
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'guarantee_period_unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'guarantee_period_unit'      => (new DialogProperty(
                'C__CATG__ACCOUNTING_GUARANTEE_PERIOD_UNIT',
                'LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD_UNIT',
                'isys_catg_accounting_list__isys_guarantee_period_unit__id',
                'isys_catg_accounting_list',
                'isys_guarantee_period_unit'
            ))->mergePropertyUiParams(
                [
                    'p_strClass' => 'input-mini',
                    'p_bDbFieldNN' => 1
                ]
            )->setPropertyUiDefault(defined_or_default('C__GUARANTEE_PERIOD_UNIT_DAYS')),
            // its only used for print view
            'guarantee_period_status'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Order no.'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__id'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_global_accounting_export_helper',
                        'get_guarantee_status'
                    ]
                ]
            ]),
            'guarantee_period_base'      => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_PERIOD_BASE',
                    C__PROPERTY__INFO__DESCRIPTION => 'guarantee period base'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_accounting_list__guarantee_period_base',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT (CASE ' . implode(' ', array_map(function ($item, $key) {
                        return ' WHEN isys_catg_accounting_list__guarantee_period_base = ' . $this->convert_sql_int($key) . ' THEN ' . $this->convert_sql_text($item);
                    }, self::get_guarantee_period_base(), array_keys(self::get_guarantee_period_base()))) . ' END) FROM isys_catg_accounting_list', 'isys_catg_accounting_list', 'isys_catg_accounting_list__id', 'isys_catg_accounting_list__isys_obj__id'),
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__ACCOUNTING_GUARANTEE_PERIOD__BASE',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strClass'   => 'input-small',
                        'p_bDbFieldNN' => 1,
                        'p_arData'     => isys_cmdb_dao_category_g_accounting::get_guarantee_period_base()
                    ],
                    C__PROPERTY__UI__DEFAULT => isys_cmdb_dao_category_g_accounting::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true
                ]
            ]),
            'order_date'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_ORDER_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Order date'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__order_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__ACCOUNTING_ORDER_DATE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strPopupType' => 'calendar',
                        'p_bTime'        => 0
                    ]
                ]
            ]),
            'guarantee_date'             => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Guarantee date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_accounting_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE ' .
                        implode(' ', array_map(
                            function ($item1, $item2) {
                                return ' WHEN isys_catg_accounting_list__guarantee_period > 0 AND isys_guarantee_period_unit__const = ' . $this->convert_sql_text($item1) . ' THEN DATE_ADD((CASE
                                            WHEN isys_catg_accounting_list__guarantee_period_base = 1 AND isys_catg_accounting_list__acquirementdate IS NOT NULL THEN isys_catg_accounting_list__acquirementdate
                                            WHEN isys_catg_accounting_list__guarantee_period_base = 2 AND isys_catg_accounting_list__order_date IS NOT NULL THEN isys_catg_accounting_list__order_date
                                            WHEN isys_catg_accounting_list__guarantee_period_base = 3 AND isys_catg_accounting_list__delivery_date IS NOT NULL THEN isys_catg_accounting_list__delivery_date
                                            ELSE null
                                            END), INTERVAL isys_catg_accounting_list__guarantee_period ' . $item2 . ')';
                            },
                            ['C__GUARANTEE_PERIOD_UNIT_DAYS', 'C__GUARANTEE_PERIOD_UNIT_MONTH', 'C__GUARANTEE_PERIOD_UNIT_WEEKS', 'C__GUARANTEE_PERIOD_UNIT_YEARS'],
                            ['DAY', 'MONTH', 'WEEK', 'YEAR']
                        )) . ' END)
                            FROM isys_catg_accounting_list
                            INNER JOIN isys_guarantee_period_unit ON isys_guarantee_period_unit__id = isys_catg_accounting_list__isys_guarantee_period_unit__id',
                        'isys_catg_accounting_list',
                        'isys_catg_accounting_list__id',
                        'isys_catg_accounting_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__ACCOUNTING_GUARANTEE_PERIOD_DATE',
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ]),
            'guarantee_status'           => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_GUARANTEE_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Guarantee status'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_accounting_list__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(
                            CASE WHEN isys_catg_accounting_list__guarantee_period_base = '.self::C__GUARANTEE_PERIOD_BASE__DATE_OF_INVOICE.' THEN isys_catg_accounting_list__acquirementdate 
                                 WHEN isys_catg_accounting_list__guarantee_period_base = '.self::C__GUARANTEE_PERIOD_BASE__ORDER_DATE.' THEN isys_catg_accounting_list__order_date 
                                 WHEN isys_catg_accounting_list__guarantee_period_base = '.self::C__GUARANTEE_PERIOD_BASE__DELIVERY_DATE.' THEN isys_catg_accounting_list__delivery_date END,
                                 ",", isys_catg_accounting_list__guarantee_period, ",", isys_catg_accounting_list__isys_guarantee_period_unit__id
                        ) FROM isys_catg_accounting_list',
                        'isys_catg_accounting_list',
                        'isys_catg_accounting_list__id',
                        'isys_catg_accounting_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST    => true,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ]
            ]),
            'description'                => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_accounting_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ACCOUNTING', 'C__CATG__ACCOUNTING')
                ]
            ])
        ];
    }

    /**
     * Private method which handles the property contact for object list and report
     *
     * @param isys_request|null $p_request
     * @param null              $p_row
     *
     * @return array|null
     * @throws isys_exception_general
     */
    private function get_purchased_at(isys_request $p_request = null, $p_row = null)
    {
        global $g_comp_database;
        $l_return = [];

        $l_request = false;
        $l_object_id = null;
        if (is_object($p_request)) {
            $l_object_id = $p_request->get_object_id();
            $l_request = true;
        } elseif ($p_row !== null) {
            $l_object_id = $p_row['isys_obj__id'];
        }

        if ($l_object_id === null) {
            return null;
        }

        $l_accounting_data = $this->get_data(null, $l_object_id)
            ->get_row();

        /**
         * IDE Typehinting.
         *
         * @var  $l_person_dao  isys_cmdb_dao_category_g_contact
         */
        $l_person_res = isys_cmdb_dao_category_g_contact::instance($g_comp_database)
            ->get_assigned_contacts_by_relation_id($l_accounting_data["isys_catg_accounting_list__isys_contact__id"]);

        while ($l_row = $l_person_res->get_row()) {
            if ($l_request) {
                $l_return[] = $l_row['isys_obj__id'];
            } else {
                $l_return[$l_row['isys_obj__id']] = $l_row['isys_obj__title'];
            }
        }

        return $l_return;
    }

    /**
     * Build query for price and operation_expense
     *
     * @param string $p_field
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function build_costs_select_join($p_table = 'isys_catg_accounting_list', $p_field = 'isys_catg_accounting_list__price')
    {
        /**
         * Old SQL:
         * SELECT CONCAT_WS(\' \', ' . $p_field . ', (
         * SELECT SUBSTRING(isys_currency__title, LOCATE(\';\', isys_currency__title) + 1) FROM isys_currency WHERE isys_currency__id = (
         * SELECT isys_setting__value FROM isys_setting WHERE isys_setting__isys_setting_key__id =
         * (SELECT isys_setting_key__id FROM isys_setting_key WHERE isys_setting_key__const = \'C__MANDATORY_SETTING__CURRENCY\'))
         * )) FROM ' . $p_table
         *
         * I don´t know why but the value has to be multiplied because in some fields the real value
         * can only be retrieved if the value is multiplied by 1. (Example isys_cats_lic_list__cost = 50000.42)
         */
        return 'SELECT CONCAT(\'{currency,\', (' . $p_field . ' * 1), \',1}\') FROM ' . $p_table;
    }

    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        $purchaseContacts = isys_glob_get_param('C__CATG__PURCHASE_CONTACT__HIDDEN');

        if (empty($purchaseContacts)) {
            $this->get_database_component()->query(
                '
                DELETE FROM isys_contact_2_isys_obj WHERE isys_contact_2_isys_obj__isys_contact__id IN (
                    SELECT isys_catg_accounting_list__isys_contact__id 
                    FROM isys_catg_accounting_list 
                    WHERE isys_catg_accounting_list__isys_obj__id = ' .$this->convert_sql_int($_GET[C__CMDB__GET__OBJECT]). '
                );'
            );

            $this->apply_update();
        }

        parent::save_user_data($p_create);
    }
}
