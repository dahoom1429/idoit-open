<?php

/**
 * AJAX handler for the Interval component
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.11
 */
class isys_ajax_handler_images extends isys_ajax_handler
{
    /**
     * Init method.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'getImagesData':
                    $return['data'] = $this->getImagesData(isys_format_json::decode($_POST['images']));
                    break;

                case 'saveImagesOrder':
                    $this->saveImagesOrder(isys_format_json::decode($_POST['images']));
                    break;
            }
        } catch (Exception $e) {
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($return);

        $this->_die();
    }

    /**
     * Method for saving a given order of images.
     *
     * @param $order
     *
     * @return void
     * @throws isys_exception_dao
     */
    private function saveImagesOrder($order)
    {
        $dao = isys_cmdb_dao_category_g_images::instance($this->m_database_component);

        if (!is_array($order)) {
            $order = [$order];
        }

        $order = array_filter($order);

        if (count($order)) {
            $dao->orderImages($order);
        }
    }

    /**
     * Returns the images data by given IDs.
     *
     * @param  integer|array $imageIds
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getImagesData($imageIds)
    {
        $images = [];
        $dao = isys_cmdb_dao_category_g_images::instance($this->m_database_component);

        if (!is_array($imageIds)) {
            $imageIds = [$imageIds];
        }

        $imagesResult = $dao->getImageMetadataByIds($imageIds);

        while ($imageRow = $imagesResult->get_row()) {
            $images[] = $imageRow;
        }

        return $images;
    }
}
