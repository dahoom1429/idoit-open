<?php

use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for file versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_file_version extends isys_cmdb_dao_category_s_file
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file_version';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATS__FILE_VERSIONS';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'file_title';

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list = 'isys_cmdb_dao_list_cats_file_version';

    /**
     * Category's table
     *
     * @var  string
     */
    protected $m_table = 'isys_file_version';

    /**
     * Category's template
     *
     * @var  string
     */
    protected $m_tpl = 'cats__file__version.tpl';

    /**
     * @param string $p_table
     * @param null   $p_obj_id
     *
     * @return null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /**
     * Deletes file physically.
     *
     * @param   string $p_strFile
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function delete_file($p_strFile)
    {
        $l_obj = new isys_component_filemanager();

        return $l_obj->delete($p_strFile);
    }

    /**
     * Get the number of items.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if (!empty($p_obj_id)) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT count(version.isys_file_version__id) AS count
			FROM isys_file_physical physical
			INNER JOIN isys_file_version version ON version.isys_file_version__isys_file_physical__id = physical.isys_file_physical__id
			WHERE version.isys_file_version__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . "
			AND isys_file_version__status < " . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ";";

        $l_row = $this->retrieve($l_sql)
            ->get_row();

        return $l_row["count"];
    }

    /**
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_catg_list_id
     * @param   integer                 $p_obj_id
     * @param   string                  $p_condition
     * @param   array                   $p_filter
     * @param   integer                 $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_file_by_obj_id($p_obj_id);
    }

    /**
     * Set the recstatus to normal.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intRecStatus (reference)
     *
     * @return  string errorText
     */
    public function get_rec_status($p_cat_level = 0, &$p_intRecStatus)
    {
        $p_intRecStatus = $this->get_rec_status_by_id_as_string($_GET["cateID"]);

        return null;
    }

    /**
     * Method for returning the properties. Currently only for print view.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'file_physical'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Filename'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_physical__filename_original',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_physical__filename_original
                            FROM isys_file_version
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_file_version',
                        'isys_file_version__id',
                        'isys_file_version__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_file_version__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_file_version', 'LEFT', 'isys_file_version__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'file_content'        => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_CONTENT',
                    C__PROPERTY__INFO__DESCRIPTION => 'File content'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_file_physical__filename'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'file_version'
                    ]
                ],
                C__PROPERTY__CHECK => null,
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                ]
            ]),
            'file_title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_version__title
                            FROM isys_file_version
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_file_version',
                        'isys_file_version__id',
                        'isys_file_version__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_file_version__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_file_version', 'LEFT', 'isys_file_version__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ],
            ]),
            'revision'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__REVISION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Revision'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_file_version__revision'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'upload_date'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_UPLOAD_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Upload date'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_physical__date_uploaded',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_physical__date_uploaded
                            FROM isys_file_version
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_file_version',
                        'isys_file_version__id',
                        'isys_file_version__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_file_version__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_file_version', 'LEFT', 'isys_file_version__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'version_description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION_DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_file_version__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__FILE_VERSION_DESCRIPTION_2'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'md5_hash'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_MD5',
                    C__PROPERTY__INFO__DESCRIPTION => 'Category'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_physical__md5',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_physical__md5
                            FROM isys_file_version
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_file_version',
                        'isys_file_version__id',
                        'isys_file_version__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_file_version__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_file_version', 'LEFT', 'isys_file_version__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__REPORT     => false
                ]
            ]),
            'uploaded_by'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_UPLOAD_FROM',
                    C__PROPERTY__INFO__DESCRIPTION => 'Category'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_physical__user_id_uploaded',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_cats_person_list__title
                            FROM isys_file_version
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id
                            INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_file_physical__user_id_uploaded',
                        'isys_file_version',
                        'isys_file_version__id',
                        'isys_file_version__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_file_version__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_file_version', 'LEFT', 'isys_file_version__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_persion_list',
                            'LEFT',
                            'isys_file_physical__user_id_uploaded',
                            'isys_cats_persion_list__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__REPORT     => false
                ]
            ])
        ];
    }

    /**
     * Rank records method.
     *
     * @param   mixed $p_ids
     *
     * @return  boolean
     * @author  dennis stuecken <dstuecken@synetics.de>
     */
    public function rank_records($p_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        if (is_array($p_ids)) {
            foreach ($p_ids as $l_value) {
                $this->rank_file_version($l_value);
            }
        } else {
            $this->rank_file_version($p_ids);
        }

        return true;
    }

    /**
     * Save element method.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        // Only call the parent method, if this is a "create" (and no "update").
        if ($_POST['C__CATS__FILE_VERSION_UPDATE'] != '1') {
            return parent::save_element($p_cat_level, $p_intOldRecStatus, $p_create);
        }

        $l_sql = 'UPDATE isys_file_version SET
			isys_file_version__title = ' . $this->convert_sql_text($_POST['C__CATS__FILE_VERSION_TITLE']) . ',
			isys_file_version__description = ' . $this->convert_sql_text($_POST['C__CATS__FILE_VERSION_DESCRIPTION']) . '
			WHERE isys_file_version__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__CATLEVEL]) . ';';

        return ($this->update($l_sql) && $this->apply_update()) ? null : -1;
    }

    /**
     * Dummy sync.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  null
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        global $g_dirs;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_filename_physical_complete = $l_filename = null;

            if (isset($p_category_data['properties']['file_content'][C__DATA__VALUE]) && !empty($p_category_data['properties']['file_content'][C__DATA__VALUE])) {
                $decodedContent = base64_decode($p_category_data['properties']['file_content'][C__DATA__VALUE], true);

                if ($decodedContent) {
                    $l_filename = $p_category_data['properties']['file_physical'][C__DATA__VALUE];

                    if (!$l_filename) {
                        $l_filename = 'Default filename';
                    }

                    $l_filename_physical = isys_component_filemanager::create_new_filename($l_filename, $p_object_id);
                    $newFileDirectoryPath = isys_application::instance()->getOrCreateUploadFileDir($l_filename_physical);
                    $l_filename_physical_complete = $newFileDirectoryPath . $l_filename_physical;

                    isys_file_put_contents($l_filename_physical_complete, $decodedContent);

                    if (!file_exists($l_filename_physical_complete)) {
                        isys_notify::info(isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__FILE_VERSION_SYNC_ERROR'), ['sticky' => true]);

                        return false;
                    }
                } else {
                    $l_filename_physical = $p_category_data['properties']['file_content'][C__DATA__VALUE];

                    $fileWithPath = isys_application::instance()->getUploadFilePath($l_filename_physical);

                    if (file_exists($fileWithPath)) {
                        $l_filename_physical = $p_category_data['properties']['file_content'][C__DATA__VALUE];
                        $l_filename_physical_complete = $fileWithPath;
                    }
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        $l_physical_id = $this->create_physical_file($l_filename_physical,  // Filename on the disk '1__124124124__filename.jpg'.
                            $l_filename,           // Original filename.
                            md5_file($l_filename_physical_complete), null);

                        if ($l_physical_id > 0) {
                            $l_version_id = $this->create_version(
                                $p_object_id,
                                $l_physical_id,
                                $p_category_data['properties']['file_title'][C__DATA__VALUE],
                                $p_category_data['properties']['version_description'][C__DATA__VALUE]
                            );

                            // As a bonus we set the "current file version" to the latest (just created) file version.
                            $l_cats_id = parent::get_data(null, $p_object_id)
                                ->get_row_value('isys_cats_file_list__id');

                            // ID-3948  Create a new entry if none was found.
                            if (!$l_cats_id) {
                                // We can not simply use the parent sync, save_element or dynamic methods :/
                                $l_file_create_sql = 'INSERT INTO isys_cats_file_list
                                    SET isys_cats_file_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
                                    isys_cats_file_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

                                $this->update($l_file_create_sql);

                                $l_cats_id = $this->get_last_insert_id();
                            }

                            $this->update_cats_file_list($l_cats_id, $l_version_id, -1, '');

                            return $l_version_id;
                        }
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $l_sql = 'UPDATE isys_file_version SET
                            isys_file_version__title = ' . $this->convert_sql_text($p_category_data['properties']['file_title'][C__DATA__VALUE]) . ',
                            isys_file_version__description = ' . $this->convert_sql_text($p_category_data['properties']['version_description'][C__DATA__VALUE]) . '
                            WHERE isys_file_version__id = ' . $this->convert_sql_id($p_category_data['data_id']) . ';';

                        if ($this->update($l_sql) && $this->apply_update()) {
                            return $p_category_data['data_id'];
                        }
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @return  boolean
     */
    public function validate_user_data()
    {
        $l_retValid = true;
        $l_arrTomAdditional = [];

        // Only validate, if the version is about to be "created" - On update, we can't upload a new image. So we skip this.
        if (empty($_FILES["C__CATS__FILE_UPLOAD"]["name"]) && $_POST['C__CATS__FILE_VERSION_UPDATE'] != '1') {
            // @todo  Check if "p_strInfoIconError" can be removed.
            $l_arrTomAdditional["C__CATS__FILE_UPLOAD"]["p_strInfoIconError"] = "Please upload a file for this version!";
            $l_arrTomAdditional["C__CATS__FILE_UPLOAD"]["message"] = "Please upload a file for this version!";
            $l_retValid = false;
        }

        $this->set_additional_rules(($l_retValid == false) ? $l_arrTomAdditional : null)
            ->set_validation($l_retValid);

        return $l_retValid;
    }

    /**
     * Deletes entries in isys_file_physical and isys_file_version and removes the file physically.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    private function rank_file_version($p_id)
    {
        $l_nID = null;
        $l_objID = null;
        $l_prev_file_version = null;
        $l_bRet = true;
        $l_strFile = '';

        $l_ret = $this->get_file_by_version_id($p_id);

        if ($l_ret->num_rows() > 0) {
            $l_row = $l_ret->get_row();
            $l_nID = $l_row['isys_file_physical__id'];
            $l_strFile = $l_row['isys_file_physical__filename'];
            $l_objID = $l_row['isys_file_version__isys_obj__id'];
        }

        if (strlen($l_strFile) > 0) {
            $l_bRet = $this->delete_file($l_strFile);
        }

        if ($l_bRet) {
            if ($l_objID) {
                $l_prev_file_version = $this->get_current_version_id($l_objID, ' AND isys_file_version__id != ' . $this->convert_sql_id($p_id));
            }

            if ($l_prev_file_version) {
                $l_bRet = $this->update('UPDATE isys_cats_file_list SET isys_cats_file_list__isys_file_version__id = ' . $this->convert_sql_id($l_prev_file_version) .
                    ' WHERE isys_cats_file_list__isys_file_version__id = ' . $this->convert_sql_id($p_id) . ';');
            } else {
                $l_bRet = $this->update('DELETE FROM isys_cats_file_list WHERE isys_cats_file_list__isys_file_version__id = ' . $this->convert_sql_id($p_id) . ';');
            }
        }

        if ($l_bRet) {
            $l_bRet = $this->update('DELETE FROM isys_file_version WHERE isys_file_version__id = ' . $this->convert_sql_id($p_id) . ';');
        }

        if ($l_nID && $l_bRet) {
            $l_bRet = $this->update('DELETE FROM isys_file_physical WHERE isys_file_physical__id = ' . $this->convert_sql_id($l_nID) . ';');
        }

        if ($l_bRet) {
            $l_bRet = $this->apply_update();
        }

        return $l_bRet;
    }

    /**
     * Get current file version id of the current object
     *
     * @param        $p_obj_id
     * @param string $p_condition
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_current_version_id($p_obj_id, $p_condition = '')
    {
        $l_sql = 'SELECT isys_file_version__id FROM isys_file_version
          WHERE isys_file_version__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ' . $p_condition . ' ORDER BY isys_file_version__revision DESC LIMIT 1;';

        return $this->retrieve($l_sql)
            ->get_row_value('isys_file_version__id');
    }
}
