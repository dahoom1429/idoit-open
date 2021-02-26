<?php

use idoit\Component\Csv\Writer;
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
class isys_component_table_csv extends isys_component_list_csv
{
    /**
     * Creates the temporary table with the data from init.
     *
     * @return  void
     */
    public function createTempTable()
    {
        $l_csv = Writer::createFromPath('php://temp');
        $l_csv->insertOne($this->m_arTableHeader);

        if ($this->m_arData) {
            $this->write_rows($l_csv);
        }

        $l_csv->output($this->get_csv_filename());
        die;
    }

    /**
     * This method will write the contents of a generic multivalue-category to the given CSV file.
     *
     * @param  CsvWriter $p_csv
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function write_rows(CsvWriter $p_csv)
    {
        if (is_array($this->m_arData) && count($this->m_arData)) {
            $locales = isys_application::instance()->container->get('locales');

            $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');

            // Check which property has callbacks.
            foreach ($this->m_arData[0] as $key => $value) {
                list($class, $property) = explode('__', str_replace('isys_cmdb_dao_category_', '', $key));

                $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $class)));
                $property = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));

                $callbackClass = '\\idoit\\Module\\Cmdb\\Model\\Ci\\Category\\' . substr($class, 0, 1) . '\\' . substr($class, 1) . '\\' . $property;

                if (!empty($class) && class_exists($callbackClass) && is_a($callbackClass, 'idoit\\Module\\Cmdb\\Model\\Ci\\Category\\DynamicCallbackInterface', true)) {
                    $callbacks[$key] = $callbackClass;
                }
            }

            $unitOverwrite = $this->get_table_config()->getAdvancedOptionMemoryUnit();

            foreach ($this->m_arData as $row) {
                foreach ($row as $key => &$value) {
                    if ($key === '__id__' || (strpos($key, '__') === false && strpos($key, '###') === false)) {
                        unset($row[$key]);
                        continue;
                    }

                    $value = isys_application::instance()->container->get('language')->get_in_text($value);

                    // Check if a callback is set and call it!
                    if (isset($callbacks[$key])) {
                        $value = call_user_func($callbacks[$key] . '::render', $value);
                    }

                    if (strpos($value, '{#') !== false) {
                        $value = preg_replace('~{#[a-fA-F0-9]{3,6}}~i', '', $value);
                    }

                    $value = preg_replace('~ {\d+}~i', '', $value);

                    // @see  ID-5159  Add logic for monetary calculation.
                    if (strpos($value, '{currency,') !== false) {
                        $value = preg_replace_callback('~{currency,([\d\.-]+),1}~i', function ($matches) use ($locales) {
                            return $locales->fmt_monetary($matches[1]);
                        }, $value);
                    }

                    // @see  ID-6672  Add logic for memory calculation.
                    if (strpos($value, '{mem,') !== false) {
                        $value = preg_replace_callback('~{mem,([\d\.-]+),(.?B)}~i', function ($matches) use ($emptyValue, $unitOverwrite) {
                            if (!is_numeric($matches[1]) || $matches[1] <= 0) {
                                return $emptyValue;
                            }

                            $units = [
                                'KB' => 'C__MEMORY_UNIT__KB',
                                'MB' => 'C__MEMORY_UNIT__MB',
                                'GB' => 'C__MEMORY_UNIT__GB',
                                'TB' => 'C__MEMORY_UNIT__TB'
                            ];

                            return isys_convert::memory($matches[1], $units[$unitOverwrite ?: $matches[2]], C__CONVERT_DIRECTION__BACKWARD) . ' ' . ($unitOverwrite ?: $matches[2]);
                        }, $value);
                    }

                    $value = trim(strip_tags($value));
                }

                $p_csv->insertOne($row);
            }
        }
    }
}
