<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\VirtualProperty;

/**
 * Class isys_cmdb_dao_category_g_jdisc_support_entitlement
 */
class isys_cmdb_dao_category_g_support_entitlement extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'support_entitlement';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATG__SUPPORT_ENTITLEMENT';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__SUPPORT_ENTITLEMENT';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_support_entitlement_list__isys_obj__id';

    /**
     * @return array
     */
    public function properties()
    {
        return [
            'partNumber' => new TextProperty(
                'C__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER',
                'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__PARTNUMBER',
                'isys_catg_support_entitlement_list__partnumber',
                'isys_catg_support_entitlement_list'
            ),
            'startDate' => new DateProperty(
                'C__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE',
                'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__START_DATE',
                'isys_catg_support_entitlement_list__start_date',
                'isys_catg_support_entitlement_list'
            ),
            'endDate' => new DateProperty(
                'C__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE',
                'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__END_DATE',
                'isys_catg_support_entitlement_list__end_date',
                'isys_catg_support_entitlement_list'
            ),
            'state' => (new VirtualProperty(
                'C__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE',
                'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE',
                'isys_catg_support_entitlement_list__id',
                'isys_catg_support_entitlement_list',
                '(CASE WHEN 
                    (CURDATE() BETWEEN isys_catg_support_entitlement_list__start_date AND isys_catg_support_entitlement_list__end_date) = 1 
                    THEN \'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_ACTIVE\' 
                    ELSE \'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__STATE_INACTIVE\' 
                END)'
            ))->mergePropertyData(
                [Property::C__PROPERTY__DATA__READONLY => true]
            ),
            'expires' => (new VirtualProperty(
                'C__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES',
                'LC__CMDB__CATG__SUPPORT_ENTITLEMENT__EXPIRES',
                'isys_catg_support_entitlement_list__id',
                'isys_catg_support_entitlement_list',
                '(DATEDIFF(isys_catg_support_entitlement_list__end_date, NOW()))'
            ))->mergePropertyData(
                [Property::C__PROPERTY__DATA__READONLY => true]
            ),
            'description' => (new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SUPPORT_ENTITLEMENT'),
                'isys_catg_support_entitlement_list__description',
                'isys_catg_support_entitlement_list'
            ))
        ];
    }

    /**
     * Compares category data for import.
     *
     * @param array    $p_category_data_values
     * @param          $currentCategoryDataSets
     * @param array    $p_used_properties
     * @param array    $p_comparison
     * @param          $badnessPoints
     * @param integer  $p_mode
     * @param integer  $p_category_id
     * @param string   $p_unit_key
     * @param array    $p_category_data_ids
     * @param mixed    $p_local_export
     * @param boolean  $dataSetChanged
     * @param          $dataSetId
     * @param isys_log $p_logger
     * @param null     $p_category_name
     * @param null     $p_table
     * @param mixed    $p_cat_multi
     * @param null     $p_category_type_id
     * @param null     $p_category_ids
     * @param null     $p_object_ids
     * @param null     $p_already_used_data_ids
     *
     * @throws Exception
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$currentCategoryDataSets,
        &$p_used_properties,
        &$p_comparison,
        &$badnessPoints,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$dataSetChanged,
        &$dataSetId,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        $importPartNumber = trim($p_category_data_values['properties']['partNumber'][C__DATA__VALUE]);
        $importStartDate = new DateTime($p_category_data_values['properties']['startDate'][C__DATA__VALUE]);
        $importEndDate = new DateTime($p_category_data_values['properties']['endDate'][C__DATA__VALUE]);
        $importDescription = trim($p_category_data_values['properties']['description'][C__DATA__VALUE]);

        // Iterate through local data sets:
        foreach ($currentCategoryDataSets as $dataSetKey => $dataSet) {
            $dataSetChanged = false;
            $dataSetId = $dataSet[$p_table . '__id'];
            $currentDataSetId = $dataSet[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$dataSetId])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$dataSetKey] = $dataSetId;
                $p_logger->debug('  Dateset ID "' . $dataSetId . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $dataSetId) {
                //$p_logger->debug('Category data identifier is different.');
                $badnessPoints[$dataSetId]++;
                $dataSetChanged = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            $currentDataPartNumber = trim($dataSet['isys_catg_support_entitlement_list__partnumber']);
            $currentDataDescription = trim($dataSet['isys_catg_support_entitlement_list__description']);
            $currentDataStartDate = new DateTime($dataSet['isys_catg_support_entitlement_list__start_date']);
            $currentDataEndDate = new DateTime($dataSet['isys_catg_support_entitlement_list__end_date']);
            $startDateDiff = $currentDataStartDate->diff($importStartDate);
            $endDateDiff = $currentDataEndDate->diff($importEndDate);

            $partNumberCheck = $currentDataPartNumber === $importPartNumber;
            $startDateCheck = ($startDateDiff->days === 0 && $startDateDiff->invert === 0);
            $endDateCheck = ($endDateDiff->days === 0 && $endDateDiff->invert === 0);
            $descriptionCheck = $currentDataDescription === $importDescription;

            if ($partNumberCheck && $startDateCheck && $endDateCheck && $descriptionCheck) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$dataSetKey] = $dataSetId;
                return;
            }

            $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$dataSetKey] = $dataSetId;
        }
    }
}
