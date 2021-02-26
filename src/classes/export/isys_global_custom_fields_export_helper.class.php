<?php

use idoit\Context\Context;

/**
 * i-doit
 *
 * Export helper for custom fields
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_custom_fields_export_helper extends isys_export_helper
{
    /**
     * Export helper for masked text fields
     *
     * @param $data
     *
     * @return string
     */
    public function exportCustomFieldPassword($data)
    {
        if (Context::instance()->getContextCustomer() === Context::CONTEXT_EXPORT_PRINTVIEW) {
            return '*****';
        }

        return $data;
    }

    /**
     * Import helper for masked text fields
     *
     * @param $data
     *
     * @return string
     */
    public function exportCustomFieldPassword_import($data)
    {
        // Check whether data is set
        if (is_array($data)) {
            return $data[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * Export helper for yes-no values for custom categories
     *
     * @param $data
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportCustomFieldYesNoDialog($data)
    {
        if (!empty($data)) {
            return [
                "title"     => $data,
                "prop_type" => 'yes-no'
            ];
        }

        return ["prop_type" => 'yes-no'];
    }

    /**
     * Import helper method for yes-no values for custom categories
     *
     * @param $data
     *
     * @return mixed|null
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function exportCustomFieldYesNoDialog_import($data)
    {
        return (is_array($data) ? $data[C__DATA__VALUE] : null);
    }

    /**
     * Export helper for calendar values for custom categories
     *
     * @param $p_value
     *
     * @return array|bool
     * @internal param $p_object_id
     */
    public function exportCustomFieldCalendar($data)
    {
        if (!empty($data)) {
            return [
                "title"     => $data,
                "prop_type" => 'calendar'
            ];
        }

        return ["prop_type" => 'calendar'];
    }

    /**
     * Import method for calendar values (Custom categories).
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function exportCustomFieldCalendar_import($p_value)
    {
        if (is_array($p_value)) {
            return $p_value[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * Export helper for calendar values for custom categories
     *
     * @param $p_value
     *
     * @return array|bool
     * @internal param $p_object_id
     */
    public function exportCustomFieldCalendarWithTime($data)
    {
        if (!empty($data)) {
            return [
                "title"     => $data,
                "prop_type" => 'calendar'
            ];
        }

        return ["prop_type" => 'calendar'];
    }

    /**
     * Import method for calendar values (Custom categories).
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function exportCustomFieldCalendarWithTime_import($p_value)
    {
        if (is_array($p_value)) {
            return $p_value[C__DATA__VALUE];
        }

        return null;
    }

    /**
     * Export helper for object connections for custom categories
     *
     * @param  int|array $objectId
     *
     * @return array|isys_export_data
     */
    public function exportCustomFieldObject($objectId)
    {
        if (!empty($objectId)) {
            $data = [];
            if (is_array($objectId)) {
                foreach ($objectId as $l_obj_id) {
                    $data[] = $this->exportCustomFieldObjectHelper($l_obj_id, true);
                }

                return new isys_export_data($data);
            }

            return $this->exportCustomFieldObjectHelper($objectId);
        }

        return ["prop_type" => 'browser_object'];
    }

    /**
     * Helper Method for method "exportCustomFieldObject"
     *
     * @param  int  $objectId
     * @param  bool $multiSelection
     *
     * @return array|bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function exportCustomFieldObjectHelper($objectId, $multiSelection = null)
    {
        $dao = isys_cmdb_dao::instance($this->m_database);
        $objectDataResult = $dao->get_object_by_id($objectId);

        if ($objectDataResult->num_rows() == 0) {
            return false;
        }

        $objectData = $objectDataResult->get_row();

        $objectTypeId = $objectData['isys_obj__isys_obj_type__id'];

        $cacheObjectType = $this->getCacheContent('object_type_rows', $objectTypeId);

        if (!$cacheObjectType) {
            $cacheObjectType = $dao->get_objtype($objectTypeId)
                ->get_row();
            $this->setCacheContent('object_type_rows', $objectTypeId, $cacheObjectType);
        }

        if (Context::instance()->getContextCustomer() === Context::CONTEXT_EXPORT_PRINTVIEW) {
            // @see  ID-6870  The 'title' field does not get displayed in print view (don't know exactly why) - for now we simply rename the property.
            $return = [
                'id'          => $objectId,
                'type_title'  => $this->language->get($objectData['isys_obj_type__title']),
                'title'       => $objectData['isys_obj__title'],
                'sysid'       => $objectData['isys_obj__sysid'],
            ];
        } else {
            $return = [
                'id'         => $objectId,
                'title'      => $objectData['isys_obj__title'],
                'sysid'      => $objectData['isys_obj__sysid'],
                'type'       => $cacheObjectType['isys_obj_type__const'],
                'type_title' => $this->language->get($objectData['isys_obj_type__title']),
                'prop_type'  => 'browser_object'
            ];

            if ($multiSelection) {
                $return['multiselection'] = 1;
            }
        }

        // Check if its a "object-relation" field.
        if (isset($this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_identifier'])) {
            $return['identifier'] = $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_identifier'];
        }

        return $return;
    }

    /**
     * Export custom report
     *
     * @param $reportId
     *
     * @return isys_export_data
     * @throws Exception
     * @see ID-6059
     */
    public function exportCustomReport($reportId)
    {
        // Get report dao
        $reportDao = isys_report_dao::instance($this->m_database);

        // Get report by its id
        $report = $reportDao->get_report($reportId);

        // Replace placeholders
        $reportQuery = $reportDao->replacePlaceHolders($report['isys_report__query']);

        /**
         * @var $reportModule isys_module_report_open | isys_module_report_pro
         */
        $reportModule = isys_module_report::get_instance();

        // Process it: Dirty stuff :S
        $reportModule->process_show_report($reportQuery);

        // Get report information which was assigned to smarty by process_show_report()
        $reportListing = isys_application::instance()->container->get('template')->get_template_vars('listing');

        // Check for results before processing it
        if (!empty($reportListing['content'])) {
            // Create header line
            $header = array_keys(reset($reportListing['content']));

            // Map __id__ to ID
            if ($header[0] == '__id__') {
                $header[0] = 'ID';
            }

            // Treat header like any other result set
            $results = [
                $header
            ];

            // Iterate over content and extract values only
            if (is_array($reportListing['content'])) {
                foreach ($reportListing['content'] as $set) {
                    // Extract values first
                    $extractedValues = array_values($set);

                    // Translate language constants now
                    $extractedValues = array_map(function ($value) {
                        return isys_application::instance()->container->get('language')->get($value);
                    }, $extractedValues);

                    // Save result
                    $results[] = $extractedValues;
                }
            }

            // Add entry indicating selected report
            $results[] = ['title' => 'reportId', 'id' => $reportId];

            // Export it
            return new isys_export_data($results);
        }

        return $reportId;
    }

    /**
     * Custom report import
     *
     * @param $value
     *
     * @return int|null|string
     */
    public function exportCustomReport_import($value)
    {
        // Check whether value is an array
        if (is_array($value['value'])) {
            /**
             * This branch will be used in case if a selected report has results
             */
            $filteredArray = array_filter($value['value'], function ($val) {
                return $val['value'] == 'reportId';
            });

            if (count($filteredArray) === 1) {
                return reset($filteredArray)['id'];
            }
        } elseif (is_numeric($value['value'])) {
            /**
             * This branch will be used in cases if a report is selected but does not have any results
             */
            return $value['value'];
        }

        return null;
    }

    /**
     * Import method for objects (Custom categories).
     *
     * @param   array $p_value
     *
     * @return  mixed
     */
    public function exportCustomFieldObject_import($value)
    {
        if (is_array($value)) {
            if (isset($value['id'])) {
                if ($value['id'] != $this->m_object_ids[$value['id']]) {
                    return $this->m_object_ids[$value['id']];
                } else {
                    return $value['id'];
                }
            } elseif (isset($value[C__DATA__VALUE]) && is_array($value[C__DATA__VALUE])) {
                $return = [];
                foreach ($value[C__DATA__VALUE] as $data) {
                    if (isset($data['id'])) {
                        if ($data['id'] != $this->m_object_ids[$data['id']]) {
                            $return[] = $this->m_object_ids[$data['id']];
                        } else {
                            $return[] = $data['id'];
                        }
                    }
                }

                return $return;
            }
        }

        return null;
    }

    /**
     * Get dialog plus information by id for custom categories
     *
     * @param int|array $p_id
     * @param bool      $p_table_name
     *
     * @return array
     */
    public function exportCustomFieldDialogPlus($dialogId, $tableName = false)
    {
        if (is_array($dialogId) || is_numeric($dialogId)) {
            $return = $data = [];
            // Get corresponding table.
            if ($tableName) {
                $dialogTable = $tableName;
            } else {
                $dialogTable = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];
            }

            if (empty($dialogTable)) {
                // Data are generated in the ui
                if (isset($this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'])) {
                    $dialogData = $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_arData'];

                    if (is_object($dialogData) && method_exists($dialogData, 'execute')) {
                        $dialogData = $dialogData->execute();
                    }

                    if (is_string($dialogData)) {
                        $dialogData = unserialize($dialogData);
                    }

                    if (is_array($dialogId)) {
                        foreach ($dialogId as $id) {
                            $data[] = $this->exportCustomFieldDialogPlusHelper($dialogData, $id, null, true);
                        }
                        $return = new isys_export_data($data);
                    } elseif ($dialogId > 0) {
                        $return = $this->exportCustomFieldDialogPlusHelper($dialogData, $dialogId, null, false);
                    }
                }
            } else {
                // Data is in the db.
                if (is_array($dialogId)) {
                    foreach ($dialogId as $id) {
                        $dialogData = isys_factory_cmdb_dialog_dao::get_instance($this->m_database, $dialogTable)
                            ->get_data($id);

                        if (!empty($dialogData)) {
                            $data[] = $this->exportCustomFieldDialogPlusHelper($dialogData, $id, $dialogTable, true);
                        }
                    }
                    $return = new isys_export_data($data);
                } elseif ($dialogId > 0) {
                    $dialogData = isys_factory_cmdb_dialog_dao::get_instance($this->m_database, $dialogTable)
                        ->get_data($dialogId);

                    if (!empty($dialogData)) {
                        $return = $this->exportCustomFieldDialogPlusHelper($dialogData, $dialogId, $dialogTable, false);
                    }
                }
            }

            return $return;
        }

        return ['identifier' => $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_identifier']];
    }

    /**
     * Helper Method for method exportCustomFieldDialogPlus.
     *
     * @param  array   $dialogData
     * @param  integer $dialogId
     * @param  string  $dialogTable
     * @param  boolean $multiSelection
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function exportCustomFieldDialogPlusHelper($dialogData, $dialogId, $dialogTable = null, $multiSelection = false)
    {
        if (Context::instance()->getContextCustomer() === Context::CONTEXT_EXPORT_PRINTVIEW) {
            $return = [
                'id'        => $dialogId,
                'title'     => ($dialogTable !== null) ? $this->language->get($dialogData[$dialogTable . '__title']) : $this->language->get($dialogData[$dialogId]),
                'constants' => ($dialogTable !== null) ? $dialogData[$dialogTable . '__const'] : ''
            ];
        } else {
            $return = [
                'id'         => $dialogId,
                'title'      => ($dialogTable !== null) ? $this->language->get($dialogData[$dialogTable . '__title']) : $this->language->get($dialogData[$dialogId]),
                'const'      => ($dialogTable !== null) ? $dialogData[$dialogTable . '__const'] : '',
                'title_lang' => ($dialogTable !== null) ? $dialogData[$dialogTable . '__title'] : $dialogData[$dialogId],
                'identifier' => $this->m_ui_info[C__PROPERTY__UI__PARAMS]['p_identifier']
            ];

            if ($multiSelection) {
                $return['multiselection'] = 1;
            }
        }

        return $return;
    }

    /**
     * Import method for dialog properties (Custom categories).
     *
     * @param      $p_title_lang
     *
     * @return null
     */
    public function exportCustomFieldDialogPlus_import($importData)
    {
        if (!is_array($importData) || (is_array($importData) && !isset($importData[C__DATA__VALUE]))) {
            return null;
        }

        $data = [];

        if (is_array($importData[C__DATA__VALUE])) {
            foreach ($importData[C__DATA__VALUE] as $value) {
                $data[] = [
                    'title_lang' => ($value['title_lang'] != '' ? $value['title_lang'] : $value[C__DATA__VALUE]),
                    'identifier' => $value['identifier']
                ];
            }
        } else {
            $data[] = [
                'title_lang' => ($importData['title_lang'] != '' ? $importData['title_lang'] : $importData[C__DATA__VALUE]),
                'identifier' => $importData['identifier']
            ];
        }

        if (!empty($data)) {
            $return = [];

            $dialogTable = $this->m_data_info[C__PROPERTY__DATA__REFERENCES][0];

            $dao = isys_cmdb_dao::instance($this->m_database);

            foreach ($data as $dialogData) {
                $query = 'SELECT * FROM ' . $dialogTable . ' WHERE ' . $dialogTable . '__identifier = ' . $dao->convert_sql_text($dialogData['identifier']) . ' ' . 'AND ' .
                    $dialogTable . '__title = ' . $dao->convert_sql_text($dialogData['title_lang']);

                $result = $dao->retrieve($query);
                if (is_countable($result) && count($result) > 0) {
                    $return[] = $result->get_row_value($dialogTable . '__id');
                } else {
                    $l_insert = 'INSERT INTO ' . $dialogTable . ' (' . $dialogTable . '__identifier, ' . $dialogTable . '__title, ' . $dialogTable . '__status) ' .
                        'VALUES (' . $dao->convert_sql_text($dialogData['identifier']) . ',' . $dao->convert_sql_text($dialogData['title_lang']) . ',' .
                        C__RECORD_STATUS__NORMAL . ')';

                    $dao->update($l_insert);
                    $return[] = $dao->get_last_insert_id();
                }
            }
            $dao->apply_update();
            return (count($return) > 1) ? $return : $return[0];
        }

        return null;
    }
}
