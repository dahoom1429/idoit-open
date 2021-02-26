<?php
/**
 * i-doit
 *
 * FileUpload wrapper
 * Implements the qqFileUploader API for uploading files.
 * Is licensed under MIT, GPL 2 and LGPL.
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

include_once("fileupload/file_upload.php");

class isys_library_fileupload extends qqFileUploader
{
    /**
     * This is used for setting an own name.
     *
     * @var  string
     */
    private $m_prefix = null;

    /**
     * Method for setting a customa filename. This method may break future updates!!
     *
     * @param   string $p_prefix
     *
     * @return  qqFileUploader
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_prefix($p_prefix)
    {
        $this->m_prefix = $p_prefix;

        return $this;
    }

    /**
     * Overwritten method from qqFileUploader, for usage of "$this->prefix".
     *
     * @param   string  $p_upload_dir
     * @param   boolean $p_replace_old_file
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    function handleUpload($p_upload_dir, $p_replace_old_file = true)
    {
        if (!is_writable($p_upload_dir)) {
            return [
                'error' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE_UPLOAD__NO_WRITE_PERMISSIONS')
            ];
        }

        if (!$this->file) {
            return [
                'error' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED')
            ];
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return [
                'error' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED')
            ];
        }

        if ($size > $this->sizeLimit) {
            return [
                'error' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG')
            ];
        }

        $pathinfo = pathinfo($this->file->getName());

        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);

            return [
                'error' => isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__FILE_UPLOAD__EXTENSION_ERROR') . ' - ' . $these
            ];
        }

        $ext = ($ext == '') ? $ext : '.' . $ext;

        if (!$p_replace_old_file) {
            while (file_exists($p_upload_dir . $filename . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        $this->uploadName = $filename = $filename . $ext;

        if ($this->m_prefix !== null) {
            $this->uploadName = $filename = $this->m_prefix . $this->uploadName;
        }

        if ($this->file->save($p_upload_dir . $filename)) {
            return ['success' => true];
        } else {
            return [
                'error' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE_UPLOAD__FILE_UPLOADED_PARTIALLY')
            ];
        }
    }

    /**
     * qqFileUpload constructor, for automatically setting the size-limit to the values, defined in php.ini.
     *
     * @param   array   $p_allowed_extensions
     * @param   integer $p_size_limit
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(array $p_allowed_extensions = [], $p_size_limit = null)
    {
        if ($p_size_limit === null) {
            // For setting the size limit, we read the ini-configurations.
            $postSize = $this->toBytes(ini_get('post_max_size'));
            $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

            // Choose the smaller value for "size limitation".
            if ($postSize > $uploadSize) {
                $p_size_limit = $uploadSize;
            } else {
                $p_size_limit = $postSize;
            }
        }

        parent::__construct($p_allowed_extensions, $p_size_limit);
    }
}
