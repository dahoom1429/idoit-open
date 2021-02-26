<?php

/**
 * i-doit
 *
 * DAO: global category for object images
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_image extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'image';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__IMAGE';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property assigned_variant.
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_image_selection()
    {
        global $g_absdir;
        $l_uploadedImages = [];
        $imagesDirectoryPath = isys_application::instance()->getImageDir();
        if (file_exists($imagesDirectoryPath) && is_dir($imagesDirectoryPath)) {
            $l_directory = dir($imagesDirectoryPath);
            while ($l_file = $l_directory->read()) {
                if (strpos($l_file, '.') !== 0) {
                    if (is_dir($imagesDirectoryPath . $l_file)) {
                        $subDirectory = dir($imagesDirectoryPath . $l_file);
                        while ($subDirFile = $subDirectory->read()) {
                            if (strpos($l_file, '.') !== 0) {
                                $l_uploadedImages[$subDirFile] = $l_file . DS . $subDirFile;
                            }
                        }
                    } else {
                        $l_uploadedImages[$l_file] = $l_file;
                    }
                }
            }
        }

        return $l_uploadedImages;
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_image_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_image_list__isys_obj__id
			WHERE TRUE $p_condition " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND (isys_catg_image_list__id = " . $this->convert_sql_id($p_catg_list_id) . ") ";
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_catg_image_list__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'image_selection' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IMAGE_UPLOADED_IMAGES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Image selection'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__TYPE  => C__TYPE__JSON, // @see API-52
                    C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__image_link'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__IMAGE_TITLEX',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_image',
                            'callback_property_image_selection'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => false
                ]
            ]),
            'image'           => array_replace_recursive(isys_cmdb_dao_category_pattern::upload(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__IMAGE_OBJ_FILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'File'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__image_link'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__IMAGE_TITLEX'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object_image'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT  => false,
                    C__PROPERTY__PROVIDES__LIST    => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VIRTUAL => true
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_image_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__IMAGE', 'C__CATG__IMAGE')
                ],
            ])
        ];
    }

    /**
     * Sync-method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        global $g_dirs;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_image = $p_category_data['properties']['image_selection'][C__DATA__VALUE];

            if (isset($p_category_data['properties']['image'][C__DATA__VALUE])) {
                if (empty($p_category_data['properties']['image']['file_name'])) {
                    /**
                     * Mapping of allowed mime types to file extension
                     */
                    $allowedMimeTypes = [
                        'image/png' => 'png',
                        'image/jpg' => 'jpg',
                        'image/jpeg' => 'jpeg',
                    ];

                    // Decode image
                    $decodedImage = base64_decode($p_category_data['properties']['image'][C__DATA__VALUE]);

                    // Create intermediate image file path
                    $imageFilePath = isys_application::instance()->getImageDir() . 'tempImage-' . uniqid();

                    // Create temporary image file to detect mime type
                    isys_file_put_contents(
                        $imageFilePath,
                        base64_decode($p_category_data['properties']['image'][C__DATA__VALUE])
                    );

                    // Detect mime type
                    $detectedMimeType = mime_content_type($imageFilePath);

                    // Check whether mime type is allowed and supported
                    if (isset($allowedMimeTypes[$detectedMimeType])) {
                        // Build image file path
                        $l_image = isys_component_filemanager::create_new_filename('object image.' . $allowedMimeTypes[$detectedMimeType], $p_object_id);

                        $subDirName = isys_application::instance()->getOrCreateUploadImageDir($l_image);

                        // Rename file based on the default naming schema
                        rename($imageFilePath, $subDirName . $l_image);
                    } else {
                        // Unsupported mime type detected: remove image file
                        unlink($imageFilePath);
                    }
                } else {
                    if (!is_string($l_image) || strlen(trim($l_image)) === 0) {
                        if (($l_file_extension = pathinfo($p_category_data['properties']['image'][C__DATA__VALUE], PATHINFO_EXTENSION))) {
                            $l_image = 'object image.' . $l_file_extension;
                        } else {
                            $l_image = 'object image.jpg';
                        }
                    }

                    $l_image = isys_component_filemanager::create_new_filename($l_image, $p_object_id);
                    $newFileDir = isys_application::instance()->getOrCreateUploadImageDir($l_image);

                    // Source file exists already
                    $prevFilePath = isys_application::instance()->getUploadImagePath($p_category_data['properties']['image']['file_name']);
                    if (file_exists($prevFilePath)) {
                        isys_file_put_contents(
                            $newFileDir . $l_image,
                            file_get_contents($prevFilePath)
                        );
                    } else {
                        // This works only if C__DATA__VALUE is really a base64_encoded string
                        isys_file_put_contents($newFileDir . $l_image, base64_decode($p_category_data['properties']['image'][C__DATA__VALUE]));
                    }
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create($p_object_id, $l_image, $p_category_data['properties']['description'][C__DATA__VALUE]);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save($p_category_data['data_id'], $l_image, $p_category_data['properties']['description'][C__DATA__VALUE]);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Gets the image name from database by obeject id.
     *
     * @param   integer $p_id
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_image_name_by_object_id($p_id)
    {
        $l_sql = "SELECT isys_catg_image_list__image_link AS image
			FROM isys_catg_image_list
			WHERE isys_catg_image_list__isys_obj__id = " . $this->convert_sql_id($p_id) . ";";

        return $this->retrieve($l_sql)
            ->get_row_value('image');
    }

    /**
     * Delete method.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id = null, $p_image_file = null)
    {
        global $g_dirs;

        if ($p_id === null && $p_image_file === null) {
            return false;
        }

        $l_filemanager = new isys_component_filemanager();
        $l_file_name = [];

        $l_sql = 'DELETE FROM isys_catg_image_list';

        if ($p_id !== null) {
            $l_data = $this->get_data($p_id)
                ->__to_array();
            $l_file_name[$l_data['isys_catg_image_list__image_link']] = $l_data['isys_catg_image_list__image_link'];

            $l_sql .= ' WHERE (isys_catg_image_list__id = ' . $this->convert_sql_id($p_id) . ');';
        }

        if ($p_image_file !== null) {
            // Check if other objects have this image
            $l_file_name[$p_image_file] = $p_image_file;

            $l_sql = 'SELECT isys_catg_image_list__id FROM isys_catg_image_list ' . 'WHERE isys_catg_image_list__image_link = ' . $this->convert_sql_text($p_image_file) . ';';

            $l_amount = $this->retrieve($l_sql)
                ->num_rows();

            if ($l_amount == 0) {
                foreach ($l_file_name as $l_file) {
                    if (empty($l_file)) {
                        continue;
                    }
                    $fullPath = isys_application::instance()->getUploadImagePath($l_file);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }

                return true;
            }
        }

        if ($this->update($l_sql)) {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * Saves the uploaded image.
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     * @return  boolean
     */
    public function upload_image()
    {
        global $g_dirs;

        $l_filemanager = new isys_component_filemanager();
        $l_image_name = $l_filemanager->create_new_filename($_FILES["C__CATG__IMAGE_UPLOAD"]['name'], $this->m_object_id);

        if (is_dir(isys_application::instance()->getImageDir())) {
            $l_filemanager->set_upload_path(isys_application::instance()->getOrCreateUploadImageDir($l_image_name));
            if ($l_filemanager->receive($_FILES["C__CATG__IMAGE_UPLOAD"], $l_image_name)) {
                return $l_image_name;
            } else {
                return false;
            }
        } else {
            $l_dirlen = strlen(isys_application::instance()->getImageDir());
            $l_dir = ($l_dirlen > 35) ? ".." . substr(isys_application::instance()->getImageDir(), $l_dirlen - 35, $l_dirlen) : isys_application::instance()->getImageDir();

            isys_notify::error('The Directory ' . $l_dir . ' does not exist. Correct your config.inc.php or create it.', ['sticky' => true]);
        }

        return false;
    }

    /**
     * Save global category image element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @throws  isys_exception_dao_cmdb
     * @return  mixed
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_image_list__status"];
        $this->m_object_id = $_GET[C__CMDB__GET__OBJECT];
        $l_image_file = null;

        if (!empty($_POST['C__CATG__IMAGE_SELECTION']) && $_POST['C__CATG__IMAGE_SELECTION'] != '-1' && $_FILES['C__CATG__IMAGE_UPLOAD']['name'] == '') {
            $l_image_file = basename($_POST['C__CATG__IMAGE_SELECTION']);
        }

        if ($l_image_file === null) {
            $l_image_file = $this->upload_image();
        }

        if ($l_catdata['isys_catg_image_list__id'] != "") {
            $this->save($l_catdata["isys_catg_image_list__id"], $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
            $this->m_strLogbookSQL = $this->get_last_query();
        } else {
            $l_id = $this->create($this->m_object_id, $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }
        }

        return null;
    }

    /**
     * Updates the current image.
     *
     * @param   integer $p_cat_level
     * @param   string  $p_link
     * @param   string  $p_description
     *
     * @return  boolean
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_link, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_image_list SET
			isys_catg_image_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_image_list__image_link  = " . $this->convert_sql_text($p_link) . ",
			isys_catg_image_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE isys_catg_image_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Creates a new image.
     *
     * @param   int    $p_object_id
     * @param   string $p_link
     * @param   string $p_description
     *
     * @return  mixed   The newly created ID (integer) or boolean false.
     */
    public function create($p_object_id, $p_link, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_image_list', $p_object_id);
        if ($this->save($l_id, $p_link, $p_description)) {
            return $l_id;
        }

        return false;
    }
}
