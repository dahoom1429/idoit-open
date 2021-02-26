<?php

/**
 * i-doit
 *
 * DAO: Global category versions.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_version extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'version';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Dynamic property handling for getting the property patchlevel
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_patchlevel($p_row)
    {
        return $this->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__hotfix');
    }

    /**
     * Dynamic property handling for getting the property kernel
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_kernel($p_row)
    {
        return $this->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__kernel');
    }

    /**
     * Dynamic property handling for getting the property servicepack
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_servicepack($p_row)
    {
        return $this->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__servicepack');
    }

    /**
     * Dynamic property handling for getting the property version
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_version($p_row)
    {
        return $this->handle_property_callbacks($p_row['isys_obj__id'], 'isys_catg_version_list__title');
    }

    /**
     * Dynamic property handling for getting the specified property
     *
     * @param  integer $p_obj_id
     * @param  string  $p_field
     *
     * @return string
     */
    public function handle_property_callbacks($p_obj_id, $p_field)
    {
        global $g_comp_database;

        $l_sql = 'SELECT ' . $p_field . ' 
            FROM isys_catg_version_list 
            WHERE isys_catg_version_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' 
            ORDER BY isys_catg_version_list__id;';

        $l_res = $g_comp_database->query($l_sql);
        $l_return = [];

        while ($l_row = $g_comp_database->fetch_row_assoc($l_res)) {
            $l_return[] = $l_row[$p_field];
        }

        return empty($l_return) ? isys_tenantsettings::get('gui.empty_value', '-') : implode(', ', $l_return);
    }

    /**
     * @param  integer $p_cat_level
     * @param  integer &$p_intOldRecStatus
     * @param  boolean $p_create
     *
     * @return bool|int
     * @throws Exception
     * @throws isys_exception_dao
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_backup_list__status"];

        if ($p_create) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__VERSION_TITLE'],
                $_POST['C__CATG__VERSION_SERVICEPACK'],
                $_POST['C__CATG__VERSION_PATCHLEVEL'],
                $_POST['C__CATG__VERSION_KERNEL'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }

            $p_cat_level = null;

            return $l_id;
        } else {
            if ($l_catdata['isys_catg_version_list__id'] != "") {
                $l_bRet = $this->save(
                    $l_catdata['isys_catg_version_list__id'],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__VERSION_TITLE'],
                    $_POST['C__CATG__VERSION_SERVICEPACK'],
                    $_POST['C__CATG__VERSION_PATCHLEVEL'],
                    $_POST['C__CATG__VERSION_KERNEL'],
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                $this->m_strLogbookSQL = $this->get_last_query();

                return $l_bRet;
            }
        }

        return $l_intErrorCode;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param  integer $p_cat_level
     * @param  integer $p_status
     * @param  string  $p_title
     * @param  string  $p_servicePack
     * @param  string  $p_hotfix
     * @param  string  $p_kernel
     * @param  string  $p_description
     *
     * @return boolean
     * @throws isys_exception_dao
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save($p_cat_level, $p_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_servicePack = null, $p_hotfix = null, $p_kernel = null, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_version_list SET 
            isys_catg_version_list__title  = " . $this->convert_sql_text($p_title) . ",
            isys_catg_version_list__servicepack  = " . $this->convert_sql_text($p_servicePack) . ",
            isys_catg_version_list__hotfix  = " . $this->convert_sql_text($p_hotfix) . ",
            isys_catg_version_list__kernel  = " . $this->convert_sql_text($p_kernel) . ",
            isys_catg_version_list__description  = " . $this->convert_sql_text($p_description) . ",
            isys_catg_version_list__status = " . $this->convert_sql_int($p_status) . " 
            WHERE isys_catg_version_list__id = " . $this->convert_sql_id($p_cat_level);

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * Executes the query to create the category entry referenced by isys_catd_version__id $p_fk_id.
     *
     * @param  integer $p_objID
     * @param  integer $p_rec_status
     * @param  string  $p_title
     * @param  string  $p_servicePack
     * @param  string  $p_hotfix
     * @param  string  $p_kernel
     * @param  string  $p_description
     *
     * @return int|bool
     * @throws isys_exception_dao
     */
    public function create($p_objID, $p_rec_status = C__RECORD_STATUS__NORMAL, $p_title = null, $p_servicePack = null, $p_hotfix = null, $p_kernel = null, $p_description = '')
    {
        $l_strSql = 'INSERT INTO isys_catg_version_list SET 
            isys_catg_version_list__title = ' . $this->convert_sql_text($p_title) . ',
            isys_catg_version_list__servicepack = ' . $this->convert_sql_text($p_servicePack) . ',
            isys_catg_version_list__hotfix = ' . $this->convert_sql_text($p_hotfix) . ',
            isys_catg_version_list__kernel = ' . $this->convert_sql_text($p_kernel) . ',
            isys_catg_version_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_catg_version_list__status = ' . $this->convert_sql_int($p_rec_status) . ',
            isys_catg_version_list__isys_obj__id = ' . $this->convert_sql_id($p_objID) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function properties()
    {
        return [
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Versionnumber'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_version_list__title',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_version_list__title FROM isys_catg_version_list',
                        'isys_catg_version_list',
                        'isys_catg_version_list__id',
                        'isys_catg_version_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_version_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__VERSION_TITLE'
                ]
            ]),
            'servicepack' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_SERVICEPACK',
                    C__PROPERTY__INFO__DESCRIPTION => 'Servicepack'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_version_list__servicepack',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_version_list__servicepack FROM isys_catg_version_list',
                        'isys_catg_version_list',
                        'isys_catg_version_list__id',
                        'isys_catg_version_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_version_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__VERSION_SERVICEPACK'
                ]
            ]),
            'kernel'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_KERNEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Kernel'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_version_list__kernel',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_version_list__kernel FROM isys_catg_version_list',
                        'isys_catg_version_list',
                        'isys_catg_version_list__id',
                        'isys_catg_version_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_version_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__VERSION_KERNEL'
                ]
            ]),
            'patchlevel'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__VERSION_PATCHLEVEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Patchlevel'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_version_list__hotfix',
                    C__PROPERTY__DATA__INDEX  => true,
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_version_list__hotfix FROM isys_catg_version_list',
                        'isys_catg_version_list',
                        'isys_catg_version_list__id',
                        'isys_catg_version_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_version_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__VERSION_PATCHLEVEL'
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_version_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_version_list__description FROM isys_catg_version_list',
                        'isys_catg_version_list',
                        'isys_catg_version_list__id',
                        'isys_catg_version_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_version_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VERSION', 'C__CATG__VERSION')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param  array   $p_category_data
     * @param  integer $p_object_id
     * @param  integer $p_status
     *
     * @return mixed
     * @throws isys_exception_dao
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['servicepack'][C__DATA__VALUE],
                        $p_category_data['properties']['patchlevel'][C__DATA__VALUE],
                        $p_category_data['properties']['kernel'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );

                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                    }
                    break;

                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save(
                        $p_category_data['data_id'],
                        C__RECORD_STATUS__NORMAL,
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['servicepack'][C__DATA__VALUE],
                        $p_category_data['properties']['patchlevel'][C__DATA__VALUE],
                        $p_category_data['properties']['kernel'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }
}
