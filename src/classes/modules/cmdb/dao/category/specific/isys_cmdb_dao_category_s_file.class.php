<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for files
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_file extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file';

    /**
     * Creates a physical file entry and returns its id.
     *
     * @param   string  $p_new_filename
     * @param   string  $p_filename
     * @param   string  $p_md5_hash
     * @param   integer $p_user_id
     *
     * @return  mixed  Integer of last inserted ID or boolean false.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function create_physical_file($p_new_filename, $p_filename, $p_md5_hash, $p_user_id)
    {
        $l_sql = 'INSERT INTO
			isys_file_physical SET
			isys_file_physical__date_uploaded = NOW(),
			isys_file_physical__filename = ' . $this->convert_sql_text($p_new_filename) . ',
			isys_file_physical__filename_original = ' . $this->convert_sql_text($p_filename) . ',
			isys_file_physical__md5 = ' . $this->convert_sql_text($p_md5_hash) . ',
			isys_file_physical__user_id_uploaded = ' . $this->convert_sql_id($p_user_id) . ',
			isys_file_physical__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Creates a new version with the given physical file id and associates it to an object.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_physical_id
     * @param   string  $p_version_title
     * @param   string  $p_version_description
     *
     * @return  mixed  Integer with the last inserted ID or boolean false.
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function create_version($p_obj_id, $p_physical_id, $p_version_title, $p_version_description)
    {
        $l_this_revision = 1 + (int)$this->get_revision_by_obj_id_as_int($p_obj_id);

        $l_sql = 'INSERT INTO isys_file_version SET
			isys_file_version__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
			isys_file_version__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
			isys_file_version__isys_file_physical__id = ' . $this->convert_sql_id($p_physical_id) . ',
			isys_file_version__title = ' . $this->convert_sql_text($p_version_title) . ',
			isys_file_version__description = ' . $this->convert_sql_text($p_version_description) . ',
			isys_file_version__revision = ' . $this->convert_sql_int($l_this_revision) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_cats_file_list
			LEFT JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id
			LEFT OUTER JOIN isys_file_category ON isys_file_category__id = isys_cats_file_list__isys_file_category__id
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_file_list__isys_obj__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_cats_file_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= 'AND (isys_cats_file_list__status = ' . $this->convert_sql_int($p_status) . ')';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Get record status.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intRecStatus
     *
     * @return  null
     */
    public function get_rec_status($p_cat_level, &$p_intRecStatus)
    {
        $p_intRecStatus = 2;

        return null;
    }

    /**
     * Get the directory of a file version
     *
     * @param  array $data
     *
     * @return string
     * @throws isys_exception_general
     * @author Oscar Pohl <opohl@i-doit.com>
     */
    public function getDirectory(array $data)
    {
        $result = $this->get_file_by_version_id($data['isys_file_version__id'])->get_row_value('isys_file_physical__filename');
        return basename(isys_application::instance()->getOrCreateUploadFileDir($result));
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @return array
     */
    protected function dynamic_properties()
    {
        return [
            '_directory' => new DynamicProperty(
                'LC__CMDB__CATS__FILE_DIRECTORY',
                'isys_file_version__id',
                'isys_file_version',
                [
                    $this,
                    'getDirectory'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'file_title'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__title',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_file_version__title', 'isys_file_version'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__FILE_OBJECT__TITLE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
            ]),
            'file_physical'       => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Filename'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__isys_file_physical__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_file_physical__filename_original
                            FROM isys_cats_file_list
                            INNER JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_cats_file_list',
                        'isys_cats_file_list__id',
                        'isys_cats_file_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__FILE_OBJECT__FILE_PHYSICAL'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__CHECK => null,
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'physical_file'
                    ]
                ]
            ]),
            'file_category' => (new DialogPlusProperty(
                'C__CATS__FILE_CATEGORY',
                'LC__CMDB__CATG__CATEGORY',
                'isys_cats_file_list__isys_file_category__id',
                'isys_cats_file_list',
                'isys_file_category'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'revision'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC_UNIVERSAL__REVISION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Revision'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__revision',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_file_version__revision', 'isys_file_version'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        )
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'version_description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION_DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_file_version__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('isys_file_version__description', 'isys_file_version'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__FILE_VERSION_DESCRIPTION_2'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_file_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__FILE', 'C__CATS__FILE')
                ],
            ]),
            // This is a virtual property, used for the overview page.
            'current_version'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__FILE_VERSION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Category'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_cats_file_list__isys_file_version__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_file_version',
                        'isys_file_version__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_file_version__title, \' (rev \', isys_file_version__revision, \')\')
                            FROM isys_cats_file_list
                            INNER JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id',
                        'isys_cats_file_list',
                        'isys_cats_file_list__id',
                        'isys_cats_file_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__FILE_VERSION',
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false
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
                            FROM isys_cats_file_list
                            INNER JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id
                            INNER JOIN isys_file_physical ON isys_file_physical__id = isys_file_version__isys_file_physical__id',
                        'isys_cats_file_list',
                        'isys_cats_file_list__id',
                        'isys_cats_file_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_file_list', 'LEFT', 'isys_cats_file_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_version',
                            'LEFT',
                            'isys_cats_file_list__isys_file_version__id',
                            'isys_file_version__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_file_physical',
                            'LEFT',
                            'isys_file_version__isys_file_physical__id',
                            'isys_file_physical__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATS__FILE_VERSION',
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
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        global $g_dirs;

        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $l_filename_arr = explode("__", $p_category_data['properties']['file_physical'][C__GET__FILE_NAME]);
                $l_new_file_name = $p_object_id . "__" . time() . "__" . str_replace('copy_', '', $l_filename_arr[2]);
                $p_category_data['data_id'] = $this->create_connector('isys_cats_file_list', $p_object_id);
                rename(
                    isys_application::instance()->getUploadFilePath($p_category_data['properties']['file_physical'][C__GET__FILE_NAME]),
                    isys_application::instance()->getUploadFilePath($l_new_file_name)
                );
                $l_md5_hash = md5_file(isys_application::instance()->getUploadFilePath($l_new_file_name));
                $l_physical_id = $this->create_physical_file($l_new_file_name, str_replace('copy_', '', $l_filename_arr[2]), $l_md5_hash, null);

                if ($l_physical_id > 0) {
                    $l_version_id = $this->create_version(
                        $p_object_id,
                        $l_physical_id,
                        $p_category_data['properties']['file_title'][C__DATA__VALUE],
                        $p_category_data['properties']['version_description'][C__DATA__VALUE]
                    );
                }

                $l_connected_to = $p_category_data['properties']['file_objects'][C__DATA__VALUE];

                if (is_array($l_connected_to)) {
                    foreach ($l_connected_to as $l_value) {
                        if (empty($l_value)) {
                            break;
                        }

                        switch ($l_value['category']) {
                            case 'C__CATG__MANUAL':
                                $l_dao = new isys_cmdb_dao_category_g_manual($this->get_database_component());
                                $l_dao->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, $p_object_id, null);
                                break;
                            case 'C__CATG__FILE':
                                $l_dao = new isys_cmdb_dao_category_g_file($this->get_database_component());
                                $l_dao->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, null, $p_object_id, null);
                                break;
                            case 'C__CATG__EMERGENCY_PLAN':
                                $l_dao = new isys_cmdb_dao_category_g_emergency_plan($this->get_database_component());
                                $l_dao->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, $p_object_id, null);
                                break;
                        }
                    }
                }

                if ($this->update_cats_file_list(
                    $p_category_data['data_id'],
                    $l_version_id,
                    $p_category_data['properties']['file_category'],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                )) {
                    $l_indicator = true;
                }
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Get persisted data
                $persistedData = $this->get_data($p_category_data['data_id'])->get_row();

                // Save version to prevent overwrite in non-updating situations
                $l_version_id = $persistedData['isys_cats_file_list__isys_file_version__id'];

                // Check for existing physical file before trying to rename it
                $oldFilePath = isys_application::instance()->getUploadFilePath($p_category_data['properties']['file_physical'][C__DATA__VALUE]);
                if (file_exists($oldFilePath)) {
                    // Save category data:
                    $l_filename_arr = explode("__", $p_category_data['properties']['file_physical'][C__DATA__VALUE]);
                    $l_new_file_name = $p_object_id . "__" . time() . "__" . str_replace('copy_', '', $l_filename_arr[2]);
                    $newDirectoryPath = isys_application::instance()->getOrCreateUploadFileDir($l_new_file_name);
                    rename(
                        $oldFilePath,
                        $newDirectoryPath . $l_new_file_name
                    );
                    $l_md5_hash = md5_file($newDirectoryPath . $l_new_file_name);
                    $l_physical_id = $this->create_physical_file($l_new_file_name, str_replace('copy_', '', $l_filename_arr[2]), $l_md5_hash, null);

                    if ($l_physical_id > 0) {
                        $l_version_id = $this->create_version(
                            $p_object_id,
                            $l_physical_id,
                            $p_category_data['properties']['file_title'][C__DATA__VALUE],
                            $p_category_data['properties']['version_description'][C__DATA__VALUE]
                        );
                    }
                }

                $l_connected_to = $p_category_data['properties']['file_objects'][C__DATA__VALUE];
                $l_dao_man = new isys_cmdb_dao_category_g_manual($this->get_database_component());
                $l_dao_file = new isys_cmdb_dao_category_g_file($this->get_database_component());
                $l_dao_ep = new isys_cmdb_dao_category_g_emergency_plan($this->get_database_component());
                $l_man_res = $l_dao_man->get_data(null, null, " AND isys_connection__isys_obj__id = " . $l_dao_man->convert_sql_id($p_object_id));

                if ($l_man_res->num_rows() > 0) {
                    while ($l_man_row = $l_man_res->get_row()) {
                        $l_dao_man->delete_entry($l_man_row['isys_catg_manual_list__id'], 'isys_catg_manual_list');
                    }
                }

                $l_file_res = $l_dao_file->get_data(null, null, " AND isys_connection__isys_obj__id = " . $l_dao_man->convert_sql_id($p_object_id));

                if ($l_file_res->num_rows() > 0) {
                    while ($l_file_row = $l_file_res->get_row()) {
                        $l_dao_file->delete_entry($l_file_row['isys_catg_file_list__id'], 'isys_catg_file_list');
                    }
                }

                $l_ep_res = $l_dao_ep->get_data(null, null, " AND isys_connection__isys_obj__id = " . $l_dao_man->convert_sql_id($p_object_id));

                if ($l_ep_res->num_rows() > 0) {
                    while ($l_ep_row = $l_ep_res->get_row()) {
                        $l_dao_ep->delete_entry($l_ep_row['isys_catg_emergency_plan_list__id'], 'isys_catg_emergency_plan_list');
                    }
                }

                if (is_array($l_connected_to)) {
                    foreach ($l_connected_to as $l_value) {
                        if (empty($l_value)) {
                            break;
                        }

                        switch ($l_value['category']) {
                            case 'C__CATG__MANUAL':
                                $l_dao_man->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, $p_object_id, null);
                                break;
                            case 'C__CATG__FILE':
                                $l_dao_file->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, null, $p_object_id, null);
                                break;
                            case 'C__CATG__EMERGENCY_PLAN':
                                $l_dao_ep->create($l_value[C__DATA__VALUE], C__RECORD_STATUS__NORMAL, null, $p_object_id, null);
                                break;
                        }
                    }
                }

                if ($this->update_cats_file_list(
                    $p_category_data['data_id'],
                    $l_version_id,
                    $p_category_data['properties']['file_category'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                )) {
                    $l_indicator = true;
                }
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Method for returning a filename and physical filename, by a given file ID.
     *
     * @param   integer $p_file_physical_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     *
     */
    public function get_filemanager_dao_by_physical_file_id($p_file_physical_id)
    {
        $l_sql = "SELECT
			physical.isys_file_physical__filename_original AS isys_filename,
			physical.isys_file_physical__filename AS isys_physical_filename
			FROM isys_file_physical physical
			WHERE (isys_file_physical__id = " . $this->convert_sql_id($p_file_physical_id) . ") LIMIT 1;";

        return $this->retrieve($l_sql);
    }

    /**
     * Returns the highest or lowest revision of the specified object.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_option
     *
     * @return  integer
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_revision_by_obj_id_as_int($p_obj_id, $p_option = "MAX")
    {
        $l_option = strtolower($p_option);

        switch ($l_option) {
            case "min":
                $l_statement = "MIN";
                break;
            default:
            case "max":
                $l_statement = "MAX";
                break;
        }

        $l_sql = "SELECT " . $l_statement . "(isys_file_version__revision) as isys_file_revision
			FROM isys_file_version
			WHERE isys_file_version__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . ";";

        return (int)$this->retrieve($l_sql)
            ->get_row_value('isys_file_revision');
    }

    /**
     * Returns a dao with all file versions associated to the given object, or all file objects with no param.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_file_by_obj_id($p_obj_id = null)
    {
        return isys_cmdb_dao_file::instance($this->m_db)
            ->get_files($p_obj_id);
    }

    /**
     * Returns a dao with all cats_file_list entrys by the given cats object id, or all entries with no param.
     *
     * @param   integer $p_cats_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_file_by_cats_id($p_cats_id = null)
    {
        $l_sql = "SELECT * FROM isys_file_physical physical
			INNER JOIN isys_file_version version ON version.isys_file_version__isys_file_physical__id = physical.isys_file_physical__id
			INNER JOIN isys_cats_file_list cats_list ON cats_list.isys_cats_file_list__isys_file_version__id = version.isys_file_version__id ";

        if ($p_cats_id !== null) {
            $l_sql .= " WHERE (cats_list.isys_cats_file_list__id = " . $this->convert_sql_id($p_cats_id) . ")";
        }

        return $this->retrieve($l_sql . ' AND isys_file_version__status < ' . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ' LIMIT 1;');
    }

    /**
     * Returns a dao with all file objects by version, or all with no param.
     *
     * @param   integer $p_version_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_file_by_version_id($p_version_id = null)
    {
        $l_sql = "SELECT * FROM isys_file_physical physical
			INNER JOIN isys_file_version version ON version.isys_file_version__isys_file_physical__id = physical.isys_file_physical__id";

        if ($p_version_id !== null) {
            $l_sql .= " WHERE (version.isys_file_version__id = " . $this->convert_sql_id($p_version_id) . ")";
        }

        return $this->retrieve($l_sql . ' AND isys_file_version__status < ' . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ' LIMIT 1;');
    }

    /**
     * Method for retrieving a file version by given obj-id.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_versions_by_obj_id($p_obj_id = null)
    {
        $l_sql = 'SELECT * FROM isys_file_version WHERE TRUE ';

        if ($p_obj_id !== null) {
            $l_sql .= 'AND isys_file_version__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ';
        }

        return $this->retrieve($l_sql . ' AND isys_file_version__status < ' . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ' ORDER BY isys_file_version__sort;');
    }

    /**
     * Returns the record status of the given version.
     *
     * @param   integer $p_id
     *
     * @return  integer
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function get_rec_status_by_id_as_string($p_id)
    {
        return $this->retrieve('SELECT isys_file_version__status AS stat FROM isys_file_version WHERE isys_file_version__id = ' . $this->convert_sql_id($p_id) . ' LIMIT 1;')
            ->get_row_value('stat');
    }

    /**
     * updates the isys_cats_file_list entry, called by create() and save_element();
     *
     * @param   integer      $p_cats_id
     * @param   integer      $p_version_id
     * @param   integer|null $p_category_id
     * @param   string       $p_description
     *
     * @return  boolean
     * @author   Dennis Stuecken <dstuecken@synetics.de>
     */
    public function update_cats_file_list($p_cats_id, $p_version_id, $p_category_id = null, $p_description = '')
    {
        if ($p_cats_id > 0) {
            $l_sql = 'UPDATE isys_cats_file_list SET
				isys_cats_file_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
				isys_cats_file_list__isys_file_version__id = ' . $this->convert_sql_id($p_version_id);

            if (!empty($p_description)) {
                $l_sql .= ', isys_cats_file_list__description = ' . $this->convert_sql_text($p_description);
            }

            if ($p_description === '') {
                $l_sql .= ', isys_cats_file_list__description = \'\'';
            }

            if (!empty($p_category_id)) {
                $l_sql .= ', isys_cats_file_list__isys_file_category__id = ' . $this->convert_sql_id($p_category_id);
            }

            $l_sql .= ' WHERE isys_cats_file_list__id = ' . $this->convert_sql_id($p_cats_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }

        return false;
    }

    /**
     * Save element.
     *
     * @param   integer $p_cat_level
     * @param   integer $p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     * @throws  Exception
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        global $g_comp_session, $g_dirs;

        // Init.
        $l_catdata = null;

        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        if (!$l_object_id && isset($_POST['C__HIDDEN__OBJECT_ID'])) {
            $l_object_id = $_POST['C__HIDDEN__OBJECT_ID'];
        }

        // Needed if new file version is added
        $l_dao_file = new isys_cmdb_dao_category_s_file(isys_application::instance()->database);

        $l_catdata = $l_dao_file->get_data(null, $l_object_id)
            ->get_row();

        $p_intOldRecStatus = $l_catdata["isys_cats_file_list__status"];
        $l_cats_id = $l_catdata["isys_cats_file_list__id"];

        if (empty($l_cats_id)) {
            $l_cats_id = parent::create_connector("isys_cats_file_list", $l_object_id);
        }

        // Get posts.
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        // Get version information.
        $l_version_title = $l_posts["C__CATS__FILE_VERSION_TITLE"];
        $l_version_description = $l_posts["C__CATS__FILE_VERSION_DESCRIPTION"];

        // Get the user id of current user
        $p_user_id = $g_comp_session->get_user_id();

        // Get selected category, group and version id.
        $l_category_id = $l_posts["C__CATS__FILE_CATEGORY"];
        $l_version_id = $l_posts["C__CATS__FILE_VERSION"];
        $l_description = $l_posts["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()];

        if (!empty($_FILES['C__CATS__FILE_UPLOAD'])) {
            $l_file = false;
            $l_new_filename = isys_helper_upload::prepare_filename($l_object_id . '__' . time() . '__' . $_FILES["C__CATS__FILE_UPLOAD"]['name']);

            try {
                $dirname = isys_application::instance()->getOrCreateUploadFileDir($l_new_filename);

                $l_file = isys_helper_upload::save($_FILES['C__CATS__FILE_UPLOAD'], $l_new_filename, $dirname, 0644);
            } catch (isys_exception_filesystem $e) {
                isys_application::instance()->container['notify']->error($e->getMessage() . '<br />' . isys_helper_upload::get_error('C__CATS__FILE_UPLOAD') . '<br />');
            }

            if ($l_file !== false) {
                $p_md5_hash = md5_file($l_file);
                $l_filename = $_FILES['C__CATS__FILE_UPLOAD']['name'];

                $l_physical_id = $this->create_physical_file($l_new_filename, $l_filename, $p_md5_hash, $p_user_id);

                if ($l_physical_id > 0) {
                    $l_version_id = $this->create_version($l_object_id, $l_physical_id, $l_version_title, $l_version_description);
                }
            }
        }

        // Standard values if nothing was selected.
        if ($l_category_id == -1) {
            $l_category_id = "NULL";
        }

        $l_retVal = $this->update_cats_file_list($l_cats_id, $l_version_id, $l_category_id, $l_description);

        $l_saveRet = null;

        if ($l_retVal === false) {
            /* Error occured */
            $l_saveRet = -1;
        } elseif ($p_create) {
            // Version has been created, now set new entry ID.
            $p_cat_level = 1;
            $l_saveRet = $l_version_id;
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_saveRet;
    }
}
