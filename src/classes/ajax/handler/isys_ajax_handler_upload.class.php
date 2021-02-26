<?php

use Intervention\Image\ImageManager;
use idoit\Component\Upload\UploadType;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_upload extends isys_ajax_handler
{
    /**
     * Init method for this AJAX request.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        try {
            $providedType = $_GET['type'];

            if (empty($providedType)) {
                throw new Exception('You have to select a upload type.');
            }

            $availableTypes = (new isys_smarty_plugin_f_file_ajax())->getUploadTypes();

            if (!isset($availableTypes[$providedType])) {
                throw new Exception('The selected upload type "' . $providedType . '" does not exit.');
            }

            /** @var UploadType $type */
            $type = $availableTypes[$providedType];

            $fileUploader = new isys_library_fileupload($type->getValidExtensions(), $type->getSizeLimit());
            
            $fileName = $fileUploader->getName();
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileDirectory = rtrim(isys_application::instance()->app_path, '/\\') . '/' . trim($type->getUploadDirectory(), '/\\') . '/';
            $fileWwwDirectory = rtrim(isys_application::instance()->www_path, '/\\') . '/' . trim($type->getUploadDirectory(), '/\\') . '/';

            if (!in_array($fileExtension, $type->getValidExtensions())) {
                throw new Exception('The filetype "' . $fileExtension . '" is not allowed.');
            }

            $uploadResult = $fileUploader->handleUpload($fileDirectory);
            $callbackResult = null;

            if (isset($uploadResult['error']) && !empty($uploadResult['error'])) {
                throw new Exception($uploadResult['error']);
            }

            // Prepare the filename to not include special chars and so on.
            $normalizedFileName = isys_helper_upload::prepare_filename($fileName);

            // Rename the filename.
            rename($fileDirectory . $fileName, $fileDirectory . $normalizedFileName);

            // Process the "after upload" callback, if available.
            if ($type->getCallbackAfterUpload() !== null) {
                try {
                    $callbackResult = call_user_func($type->getCallbackAfterUpload(), $fileDirectory . $normalizedFileName);
                } catch (Exception $e) {
                    // When a callback fails, we remove the file and re-throw the exception to trigger an error.
                    @unlink($fileDirectory . $normalizedFileName);

                    throw $e;
                }
            }

            $return = [
                'success' => isset($uploadResult['success']) && $uploadResult['success'],
                'data'    => [
                    'type' => $providedType,
                    'filePath' => $fileWwwDirectory . $normalizedFileName,
                    'callbackResult' => $callbackResult
                ],
                'message' => ''
            ];
        } catch (Exception $e) {
            $return = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        echo isys_format_json::encode($return);

        $this->_die();
    }
}
