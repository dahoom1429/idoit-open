<?php

use idoit\Component\Csv\Writer;
use idoit\Component\Helper\Purify;
use League\Csv\Writer as CsvWriter;

/**
 * i-doit
 *
 * Builds html-table for the object lists.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_list_csv extends isys_component_list
{
    /**
     * Creates the temporary table with the data from init.
     *
     * @return  void
     */
    public function createTempTable()
    {
        $tableConfig = $this->get_table_config();
        $allowed = array_map(function ($prop) {
            return $prop->getPropertyKey();
        }, $tableConfig->getProperties());

        $l_header = array_filter($this->m_arTableHeader, function ($name, $key) use ($allowed) {
            return !(empty($name) || $key == 'id') && (!empty($allowed) && in_array($key, $allowed, true));
        }, ARRAY_FILTER_USE_BOTH);

        $l_csv = Writer::createFromPath('php://temp');
        $l_csv->insertOne(array_map('_L', array_values($l_header)));

        if ($this->m_resData) {
            $this->write_generic_category_rows($l_csv, $l_header);
        }

        // Don't use a "else" here in case a list displays two tables (see guest systems).
        if ($this->m_arData && is_array($this->m_arData) && count($this->m_arData)) {
            // Right now this logic is optimized for custom categories.
            $this->write_custom_category_rows($l_csv, $l_header);
        }

        $l_csv->output($this->get_csv_filename());
        die;
    }

    /**
     * This method will write the contents of a generic multivalue-category to the given CSV file.
     *
     * @param  CsvWriter $p_csv
     * @param  array     $p_header
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function write_generic_category_rows(CsvWriter $p_csv, array $p_header)
    {
        $l_filter = isys_glob_get_param('filter');
        $l_method = $l_modify_row = $l_custom_modify_row = false;
        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Exchange row-array by using method modify_row which is defined in the specific listDao.
        if ($this->m_listdao != null && is_a($this->m_listdao, "isys_component_dao_object_table_list")) {
            $l_method = $this->m_row_method;

            $l_modify_row = method_exists($this->m_listdao, $l_method);
        }

        // Custom row modifier.. (Not needed to be a table_list..)
        if (is_object($this->m_row_modifier) && method_exists($this->m_row_modifier, $this->m_row_method)) {
            $l_method = $this->m_row_method;
            $l_custom_modify_row = true;
        }

        if ($l_modify_row || $l_custom_modify_row) {
            $this->m_modified = true;
        }

        while ($l_row = $this->m_resData->get_row()) {
            if ($l_modify_row) {
                $this->m_listdao->$l_method($l_row);
            }

            if ($l_custom_modify_row) {
                $this->m_row_modifier->$l_method($l_row);
            }

            $l_csv_row = [];

            foreach ($p_header as $l_key => $l_field) {
                if (isset($l_row[$l_key])) {
                    $l_val = $l_row[$l_key];
                } else {
                    $l_csv_row[$l_key] = $l_empty_value;
                    continue;
                }

                if (is_scalar($l_val)) {
                    if (strpos($l_val, "LC") === 0) {
                        $l_val = isys_application::instance()->container->get('language')
                            ->get($l_val);
                    }
                } elseif (is_array($l_val)) {
                    $l_val = implode(PHP_EOL, $l_val);
                } elseif (is_object($l_val) && is_a($l_val, 'isys_smarty_plugin_f')) {
                    /* @var  isys_smarty_plugin_f $l_val */
                    $parameters = $l_val->set_parameter('p_bEditMode', false)
                        ->set_parameter('p_editMode', false)
                        ->get_parameter();
                    $l_val = $l_val->navigation_view(isys_application::instance()->template, $parameters);
                }

                $l_csv_row[$l_key] = trim(strip_tags(isys_helper_textformat::remove_scripts(html_entity_decode($l_val, null, $GLOBALS['g_config']['html-encoding']))));
            }

            $l_csv_row = array_values($l_csv_row);

            if (empty($l_filter) || stripos(implode(' ', $l_csv_row), $l_filter) !== false) {
                $p_csv->insertOne($l_csv_row);
            }
        }
    }

    /**
     * This method will write the contents of a custom multivalue-category to the given CSV file.
     *
     * @param  CsvWriter $p_csv
     * @param  array     $p_header
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function write_custom_category_rows(CsvWriter $p_csv, array $p_header)
    {
        $l_filter = isys_glob_get_param('filter');

        foreach ($this->m_arData as $l_row) {
            $l_csv_row = [];

            foreach ($p_header as $l_identifier => $l_field) {
                $l_csv_row[] = trim(strip_tags(isys_helper_textformat::remove_scripts(html_entity_decode(isys_application::instance()->container->get('language')
                    ->get($l_row[$l_identifier]), null, $GLOBALS['g_config']['html-encoding']))));
            }

            if (empty($l_filter) || stripos(implode(' ', $l_csv_row), $l_filter) !== false) {
                $p_csv->insertOne($l_csv_row);
            }
        }
    }

    /**
     * This method will create a CSV filename according to object and category.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function get_csv_filename()
    {
        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_name = 'list';
        $l_object_id = null;

        if (isset($l_gets[C__CMDB__GET__OBJECT])) {
            $l_object_id = (int)$l_gets[C__CMDB__GET__OBJECT];
        } elseif (method_exists($this->m_listdao, 'get_dao_category') && method_exists($this->m_listdao->get_dao_category(), 'get_object_id')) {
            $l_object_id = (int)$this->m_listdao->get_dao_category()
                ->get_object_id();
        }

        if (isset($l_gets[C__CMDB__GET__CATG]) || isset($l_gets[C__CMDB__GET__CATS])) {
            // Category context...
            if ($l_gets[C__CMDB__GET__CATG] == defined_or_default('C__CATG__CUSTOM_FIELDS') && isset($l_gets[C__CMDB__GET__CATG_CUSTOM])) {
                $l_name = isys_cmdb_dao_category_g_custom_fields::instance(isys_application::instance()->database)
                    ->get_cat_custom_name_by_id_as_string($l_gets[C__CMDB__GET__CATG_CUSTOM]);
            } elseif (isset($l_gets[C__CMDB__GET__CATG]) && $l_gets[C__CMDB__GET__CATG] > 1) {
                $l_name = isys_cmdb_dao::instance(isys_application::instance()->database)
                    ->get_catg_name_by_id_as_string($l_gets[C__CMDB__GET__CATG]);
            } elseif (isset($l_gets[C__CMDB__GET__CATS]) && $l_gets[C__CMDB__GET__CATS] > 1) {
                $l_name = isys_cmdb_dao::instance(isys_application::instance()->database)
                    ->get_cats_name_by_id_as_string($l_gets[C__CMDB__GET__CATS]);
            }
        } elseif (isset($l_gets[C__CMDB__GET__OBJECTTYPE])) {
            // Object type context...
            $l_name = isys_cmdb_dao::instance(isys_application::instance()->database)
                ->get_objtype_name_by_id_as_string($l_gets[C__CMDB__GET__OBJECTTYPE]);
        }

        return isys_helper_textformat::clean_string(date('Y_m_d') . ($l_object_id !== null ? '__' . $l_object_id : '') . '__' .
                isys_application::instance()->container->get('language')
                    ->get($l_name)) . '.csv';
    }
}
