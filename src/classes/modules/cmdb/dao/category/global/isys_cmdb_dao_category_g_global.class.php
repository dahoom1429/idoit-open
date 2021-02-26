<?php

use idoit\Component\Property\Type\DynamicProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: global category for global properties
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_global extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'global';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__GLOBAL';

    /**
     * Dynamic property handling for retrieving the objects CMDB status.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public static function dynamic_property_callback_cmdb_status(array $p_row)
    {
        $l_row = isys_cmdb_dao_status::instance(isys_application::instance()->database)
            ->get_cmdb_status_as_array($p_row['isys_obj__isys_cmdb_status__id']);

        return '<div class="cmdb-marker" style="background-color:#' . $l_row["isys_cmdb_status__color"] . ';"></div> ' .
            isys_application::instance()->container->get('language')
                ->get($l_row["isys_cmdb_status__title"]);
    }

    /**
     * Callback method for property status.
     *
     * @param   isys_request|null $p_request
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function callback_property_status($p_request = null)
    {
        return [
            C__RECORD_STATUS__ARCHIVED              => 'LC__CMDB__RECORD_STATUS__ARCHIVED',
            C__RECORD_STATUS__BIRTH                 => 'LC__CMDB__RECORD_STATUS__BIRTH',
            C__RECORD_STATUS__DELETED               => 'LC__CMDB__RECORD_STATUS__DELETED',
            C__RECORD_STATUS__NORMAL                => 'LC__CMDB__RECORD_STATUS__NORMAL',
            C__RECORD_STATUS__TEMPLATE              => 'LC__CMDB_STATUS__IDOIT_STATUS_TEMPLATE',
            C__RECORD_STATUS__MASS_CHANGES_TEMPLATE => 'LC__MASS_CHANGE__CHANGE_TEMPLATE'
        ];
    }

    /**
     * Callback method for property type.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_type(isys_request $p_request)
    {
        global $g_comp_database;

        $l_return = [];
        $l_objtypes = isys_cmdb_dao_category_g_global::instance($g_comp_database)
            ->get_object_type();

        if (is_countable($l_objtypes) && count($l_objtypes)) {
            foreach ($l_objtypes as $l_data) {
                $l_return[$l_data['isys_obj_type__id']] = $l_data['isys_obj_type__title'];
            }
        }

        return $l_return;
    }

    /**
     * Dynamic property handling for getting creation time of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_created(array $p_row)
    {
        // In order to sort the fields correctly, surrounding elements are not allowed.
        if (!empty($p_row["isys_obj__created_by"])) {
            // This string helps sorting!
            return '<span data-date="' . $p_row["isys_obj__created"] . '" class="hide"></span>' . isys_locale::get_instance()
                    ->fmt_date($p_row["isys_obj__created"]) . " (" . $p_row["isys_obj__created_by"] . ")";
        } else {
            return '<span data-date="' . $p_row["created"] . '" class="hide"></span>' . isys_locale::get_instance()
                    ->fmt_date($p_row["isys_obj__created"]);
        }
    }

    /**
     * Dynamic property handling for getting the last change time of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_changed(array $p_row)
    {
        // In order to sort the fields correctly, surrounding elements are not allowed.
        if (!empty($p_row["isys_obj__updated_by"])) {
            return '<span data-date="' . $p_row["isys_obj__updated"] . '" class="hide"></span>' . isys_locale::get_instance()
                    ->fmt_datetime($p_row["isys_obj__updated"]) . " (" . $p_row["isys_obj__updated_by"] . ")";
        } else {
            return '<span data-date="' . $p_row["isys_obj__updated"] . '" class="hide"></span>' . isys_locale::get_instance()
                    ->fmt_datetime($p_row["isys_obj__updated"]);
        }
    }

    /**
     * Dynamic property handling for retrieving the object ID.
     *
     * @param   array $p_row
     *
     * @return  integer
     */
    public function dynamic_property_callback_id(array $p_row)
    {
        return (int)($p_row["isys_obj__id"] ?: ($p_row['__obj_id__'] ?: ($p_row['__id__'] ?: null))); // @fixes ID-3158
    }

    /**
     * Dynamic property handling for retrieving the object name with a link.
     *
     * @param   array $p_row
     *
     * @deprecated Check if this is still used
     *
     * @return  string
     */
    public function dynamic_property_callback_title(array $p_row)
    {
        $l_title = isys_glob_htmlentities((string)$p_row["isys_obj__title"]);

        // The hidden span Element is used for frontend sorting.
        return '<i title="' . isys_glob_htmlentities($l_title) . '"></i>' . isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info(
                    $p_row["isys_obj__id"],
                    $l_title,
                    isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_row["isys_obj__id"]]),
                    100 // Prior: "maxlength.object.lists"
                );
    }

    /**
     * Specialized and query optimized search statement for search module
     *
     * @note isys_purpose__title search is removed from search due to dropping performance!! could be handled via UNION SELECT in future.
     *
     * @param isys_search_filter_interface $p_filter
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     *
     * @deprecated
     */
    public function search(isys_search_filter_interface $p_filter = null)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj__isys_obj_type__id, isys_obj_type__id, isys_obj__status, isys_obj_type__title, isys_obj__title, isys_catg_global_category__title, isys_obj.isys_obj__sysid, isys_obj.isys_obj__description
FROM isys_obj

INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id

LEFT JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id
LEFT JOIN isys_catg_global_category ON isys_catg_global_list__isys_catg_global_category__id = isys_catg_global_category__id

WHERE
(
          (isys_obj.isys_obj__title ' . $p_filter->get() . ')
       OR (isys_catg_global_category__title ' . $p_filter->get() . ')
       OR (isys_obj.isys_obj__sysid ' . $p_filter->get() . ')
       OR (isys_obj.isys_obj__description ' . $p_filter->get() . ')

) AND (isys_obj__status = ' . C__RECORD_STATUS__NORMAL . ')';

        if (isys_tenantsettings::get('search.exclude-relations', true)) {
            $blacklist = filter_defined_constants([
                'C__OBJTYPE__RELATION',
                'C__OBJTYPE__PARALLEL_RELATION'
            ]);
            if (!empty($blacklist)) {
                $l_sql .= ' AND isys_obj__isys_obj_type__id NOT IN (' . implode(',', $blacklist) . ')';
            }
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Saves object description.
     *
     * @param   integer $p_object_id
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save_description($p_object_id, $p_description)
    {
        $l_sql = "UPDATE isys_obj " . "SET isys_obj__description = " . $this->convert_sql_text($p_description) . " " . "WHERE isys_obj__id = " .
            $this->convert_sql_id($p_object_id) . ";";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Saves object title.
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     *
     * @return  boolean
     */
    public function save_title($p_object_id, $p_title)
    {
        // Create unique object title
        $p_title = $this->generate_unique_obj_title($p_title, $p_object_id);

        // Build sql statement
        $l_sql = "UPDATE isys_obj SET isys_obj__title = " . $this->convert_sql_text($p_title) . " WHERE isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        // Update object title
        if ($this->update($l_sql) && $this->apply_update()) {
            $l_dao = isys_cmdb_dao_object_type::instance($this->m_db);

            // Check whether object owns organization category and update its data
            if ($l_dao->has_cat($this->get_objTypeID($p_object_id), ['C__CATS__ORGANIZATION'])) {
                isys_cmdb_dao_category_s_organization_master::instance($this->m_db)
                    ->set_organization_title($p_object_id, $p_title);

            // Check whether object owns person group category and update its data
            } elseif ($l_dao->has_cat($this->get_objTypeID($p_object_id), ['C__CATS__PERSON_GROUP'])) {
                isys_cmdb_dao_category_s_person_group_master::instance($this->m_db)
                    ->set_person_group_title($p_object_id, $p_title);

            // Check whether object owns person category and update its data
            } elseif ($l_dao->has_cat($this->get_objTypeID($p_object_id), ['C__CATS__PERSON'])) {
                $l_first_name = strstr($p_title, ' ', true);
                $l_last_name = trim(strstr($p_title, ' '));

                isys_cmdb_dao_category_s_person_master::instance($this->m_db)
                    ->set_person_name($p_object_id, $l_first_name, $l_last_name);
            }

            return true;
        }

        return false;
    }

    /**
     * Saves a category to an object.
     *
     * @param   integer $p_object_id
     * @param   integer $p_category_id
     *
     * @return  boolean
     */
    public function save_category($p_object_id, $p_category_id)
    {
        $l_sql = "UPDATE isys_catg_global_list
			SET isys_catg_global_list__isys_catg_global_category__id = " . $this->convert_sql_id($p_category_id) . "
			WHERE isys_catg_global_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Saves a purpose to an object.
     *
     * @param   integer $p_object_id
     * @param   integer $p_purpose_id
     *
     * @return  boolean
     */
    public function save_purpose($p_object_id, $p_purpose_id)
    {
        $l_sql = "UPDATE isys_catg_global_list
			SET isys_catg_global_list__isys_purpose__id = " . $this->convert_sql_id($p_purpose_id) . "
			WHERE isys_catg_global_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Updates existing category data.
     *
     * @param   integer $p_catlevel        Category data identifier
     * @param   integer $p_status          Record status
     * @param   string  $p_title           Title
     * @param   string  $p_sysid           SYSID
     * @param   integer $p_catID           Category identifier
     * @param   integer $p_purposeID       Purpose
     * @param   string  $p_description     Description
     * @param   integer $p_obj_id          Object identifier
     * @param   boolean $p_overwrite_sysid Overwrite SYSID? Defaults to false.
     * @param   integer $p_cmdb_status_id  CMDB status. Defaults to null.
     * @param   integer $p_objtype_id
     * @param   array   $p_tags
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function save(
        $p_catlevel,
        $p_status,
        $p_title,
        $p_sysid,
        $p_catID,
        $p_purposeID,
        $p_description,
        $p_obj_id,
        $p_overwrite_sysid = false,
        $p_cmdb_status_id = null,
        $p_objtype_id = null,
        $p_tags = null
    ) {
        $l_old_title = $this->get_obj_name_by_id_as_string($p_obj_id);

        if (isys_cmdb_dao_category_g_accounting::has_placeholders($p_title) && $p_status != C__RECORD_STATUS__TEMPLATE &&
            $p_status != C__RECORD_STATUS__MASS_CHANGES_TEMPLATE) {
            $p_title = isys_cmdb_dao_category_g_accounting::instance($this->get_database_component())
                ->replace_placeholders($p_title, $p_obj_id, // @see ID-4968
                    (($p_objtype_id === null) ? $this->get_objTypeID($p_obj_id) : $p_objtype_id), null, $this->get_sysid_by_obj_id($p_obj_id), 'isys_catg_global_list');
        }

        $p_title = $this->generate_unique_obj_title($p_title, $p_obj_id);

        $l_strSql = 'UPDATE isys_obj
            SET isys_obj__title = ' . $this->convert_sql_text($p_title) . ',
            isys_obj__description = ' . $this->convert_sql_text($p_description) . ', ';

        if ((!C__SYSID__READONLY && !empty($p_sysid)) || $p_overwrite_sysid) {
            $l_strSql .= 'isys_obj__sysid = ' . $this->convert_sql_text($p_sysid) . ', ';
        }

        if (!empty($p_objtype_id) && is_numeric($p_objtype_id)) {
            $l_create_object_types = isys_auth_cmdb_object_types::instance()
                ->get_allowed_objecttypes(isys_auth::CREATE);
            if (in_array($p_objtype_id, $l_create_object_types)) {
                $_GET[C__CMDB__GET__OBJECTTYPE] = $p_objtype_id;
                $l_strSql .= "isys_obj__isys_obj_type__id = " . $this->convert_sql_id($p_objtype_id) . ", ";
            } else {
                $lang = isys_application::instance()->container->get('language');
                isys_notify::error(
                    sprintf(
                        $lang->get('LC__AUTH__CMDB_EXCEPTION__MISSING_CREATE_RIGHT_FOR_OBJ_TYPE'),
                        $lang->get($this->get_objtype_name_by_id_as_string($p_objtype_id))
                    )
                );
            }
        }

        if (!empty($p_cmdb_status_id)) {
            $l_strSql .= "isys_obj__isys_cmdb_status__id = " . $this->convert_sql_id($p_cmdb_status_id) . ", ";

            if ($p_cmdb_status_id > 0) {
                $l_dao_status = isys_cmdb_dao_status::instance($this->m_db);
                $l_dao_status->add_change($p_obj_id, $p_cmdb_status_id);
            }
        }

        $this->assign_tag($p_obj_id, $p_tags);

        $l_strSql .= 'isys_obj__status = ' . $this->convert_sql_int($p_status) . ' WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        $l_strSql2 = 'UPDATE isys_catg_global_list
            SET isys_catg_global_list__isys_purpose__id = ' . $this->convert_sql_id($p_purposeID) . ',
            isys_catg_global_list__isys_catg_global_category__id = ' . $this->convert_sql_id($p_catID) . ',
            isys_catg_global_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
            WHERE isys_catg_global_list__id = ' . $this->convert_sql_id($p_catlevel) . ';';

        if ($this->update($l_strSql) && $this->update($l_strSql2)) {
            if ($this->apply_update()) {
                if ($l_old_title != $p_title) {
                    /**
                     * @todo EVENT TRIGGERING
                     */

                    // onchange: relation.
                    $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
                    $l_relations = $l_dao_relation->get_data(null, $p_obj_id);
                    while ($l_row = $l_relations->get_row()) {
                        $this->save_title(
                            $l_row["isys_obj__id"],
                            $l_dao_relation->format_relation_name($l_row["slave_title"], $l_row["master_title"], $l_row["isys_relation_type__master"])
                        );
                    }

                    // Onchange: cluster services and their relation pool.
                    $l_dao_cluster = new isys_cmdb_dao_category_g_cluster_service($this->m_db);
                    $l_clusterdata = $l_dao_cluster->get_data(
                        null,
                        null,
                        " AND isys_connection__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . " AND isys_catg_cluster_service_list__isys_cats_relpool_list__id > 0"
                    );
                    while ($l_row = $l_clusterdata->get_row()) {
                        $this->save_title($l_row["isys_cats_relpool_list__isys_obj__id"], $l_dao_cluster->prepare_relpool_title($p_title, $l_row["isys_cluster_type__id"]));
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Creates new category data.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     */
    public function create($p_obj_id)
    {
        return $this->create_connector('isys_catg_global_list', $p_obj_id);
    }

    /**
     * @param int  $p_cat_level
     * @param int  $p_new_id
     * @param bool $p_create
     *
     * @return int|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function save_element(&$p_cat_level, &$p_new_id, $p_create)
    {
        $l_intErrorCode = -1;
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();
        $l_intOldRecStatus = $l_catdata["isys_obj__status"];

        if (empty($l_catdata['isys_catg_global_list__id'])) {
            $l_catdata['isys_catg_global_list__id'] = $this->create($_GET[C__CMDB__GET__OBJECT]);
        }

        // Change status for birth entries.
        if ($_POST["C__OBJ__STATUS"] == C__RECORD_STATUS__BIRTH) {
            $_POST["C__OBJ__STATUS"] = C__RECORD_STATUS__NORMAL;
        }

        if (empty($_POST["C__OBJ__CMDB_STATUS"])) {
            $_POST["C__OBJ__CMDB_STATUS"] = defined_or_default('C__CMDB_STATUS__IN_OPERATION');
        }

        $l_success = $this->save(
            $l_catdata['isys_catg_global_list__id'],
            $_POST["C__OBJ__STATUS"],
            $_POST["C__CATG__GLOBAL_TITLE"],
            $_POST["C__CATG__GLOBAL_SYSID"],
            $_POST["C__CATG__GLOBAL_CATEGORY"],
            $_POST["C__CATG__GLOBAL_PURPOSE"],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
            $l_catdata["isys_obj__id"],
            false,
            $_POST["C__OBJ__CMDB_STATUS"],
            $_POST["C__OBJ__TYPE"],
            $_POST['C__CATG__GLOBAL_TAG']
        );

        if ($l_success) {
            $this->m_strLogbookSQL = $this->get_last_query();

            // Template handling.
            $l_new_status = $this->handle_template_status($l_intOldRecStatus, $l_catdata["isys_obj__id"]);

            // Replace placeholders and update accounting inventory number
            if (C__RECORD_STATUS__BIRTH == (int)$l_intOldRecStatus && $l_new_status != C__RECORD_STATUS__TEMPLATE &&
                $l_new_status != C__RECORD_STATUS__MASS_CHANGES_TEMPLATE &&
                ($l_auto_inventory = trim(isys_tenantsettings::get('cmdb.objtype.' . $_POST["C__OBJ__TYPE"] . '.auto-inventory-no', ''))) !== '') {
                if (strpos(' ' . $l_auto_inventory, '%OBJTITLE%')) {
                    $l_sql_inventory = 'SELECT isys_catg_accounting_list__inventory_no FROM isys_catg_accounting_list
						WHERE isys_catg_accounting_list__isys_obj__id = \'' . $l_catdata["isys_obj__id"] . '\';';
                    $l_inventory_no = $this->retrieve($l_sql_inventory)
                        ->get_row_value('isys_catg_accounting_list__inventory_no');
                    $l_new_inventory_no = null;
                    $l_dao = isys_cmdb_dao_category_g_accounting::instance($this->m_db);
                    if ($l_inventory_no) {
                        $l_new_inventory_no = $l_dao->replace_placeholders(
                            $l_inventory_no,
                            $l_catdata["isys_obj__id"],
                            $_POST["C__OBJ__TYPE"],
                            $_POST["C__CATG__GLOBAL_TITLE"],
                            $_POST["C__CATG__GLOBAL_SYSID"]
                        );
                    } else {
                        $l_new_inventory_no = $l_dao->replace_placeholders(
                            $l_auto_inventory,
                            $l_catdata["isys_obj__id"],
                            $_POST["C__OBJ__TYPE"],
                            $_POST["C__CATG__GLOBAL_TITLE"],
                            $_POST["C__CATG__GLOBAL_SYSID"]
                        );
                    }
                    if ($l_new_inventory_no) {
                        $l_update = 'UPDATE isys_catg_accounting_list SET
							isys_catg_accounting_list__inventory_no = ' . $this->convert_sql_text($l_new_inventory_no) .
                            ' WHERE isys_catg_accounting_list__isys_obj__id = \'' . $l_catdata["isys_obj__id"] . '\';';
                        $this->update($l_update);
                    }
                }
            } elseif (C__RECORD_STATUS__BIRTH == $l_intOldRecStatus &&
                ($l_new_status == C__RECORD_STATUS__TEMPLATE || $l_new_status == C__RECORD_STATUS__MASS_CHANGES_TEMPLATE)) {
                $l_update = 'UPDATE isys_catg_accounting_list SET
							isys_catg_accounting_list__inventory_no = \'\'
							WHERE isys_catg_accounting_list__isys_obj__id = \'' . $l_catdata["isys_obj__id"] . '\';';
                $this->update($l_update);
            }

            // ID-1671: Saving the person, persongroup and organization titles, if those fields are not filled.

            /* @var  $l_dao  isys_cmdb_dao_object_type */
            $l_dao = isys_cmdb_dao_object_type::instance($this->m_db);

            if (!isset($_POST["C__CONTACT__ORGANISATION_TITLE"]) && $l_dao->has_cat($this->get_objTypeID($l_catdata["isys_obj__id"]), ['C__CATS__ORGANIZATION'])) {
                isys_cmdb_dao_category_s_organization_master::instance($this->m_db)
                    ->set_organization_title($l_catdata["isys_obj__id"], $_POST["C__CATG__GLOBAL_TITLE"]);
            } else {
                if (!isset($_POST['C__CONTACT__GROUP_TITLE']) && $l_dao->has_cat($this->get_objTypeID($l_catdata["isys_obj__id"]), ['C__CATS__PERSON_GROUP'])) {
                    isys_cmdb_dao_category_s_person_group_master::instance($this->m_db)
                        ->set_person_group_title($l_catdata["isys_obj__id"], $_POST["C__CATG__GLOBAL_TITLE"]);
                } else {
                    if (!isset($_POST['C__CONTACT__PERSON_FIRST_NAME']) && !isset($_POST['C__CONTACT__PERSON_LAST_NAME']) &&
                        $l_dao->has_cat($this->get_objTypeID($l_catdata["isys_obj__id"]), ['C__CATS__PERSON'])) {
                        $l_first_name = strstr($_POST["C__CATG__GLOBAL_TITLE"], ' ', true);
                        $l_last_name = trim(strstr($_POST["C__CATG__GLOBAL_TITLE"], ' '));

                        isys_cmdb_dao_category_s_person_master::instance($this->m_db)
                            ->set_person_name($l_catdata["isys_obj__id"], $l_first_name, $l_last_name);
                    }
                }
            }

            return null;
        }

        return $l_intErrorCode;
    }

    /**
     * Checks whether object should be a template and changes it's status.
     *
     * @param  integer $p_current_status Record status.
     * @param  integer $p_object_id      Object identifier.
     */
    public function handle_template_status($p_current_status, $p_object_id)
    {
        // Template handling.
        if ($p_current_status == C__RECORD_STATUS__BIRTH && $_POST["template"] == "1") {
            $this->set_object_status($p_object_id, C__RECORD_STATUS__TEMPLATE);

            return C__RECORD_STATUS__TEMPLATE;
        } elseif ($p_current_status == C__RECORD_STATUS__BIRTH && $_POST["template"] == C__RECORD_STATUS__MASS_CHANGES_TEMPLATE) {
            $this->set_object_status($p_object_id, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE);

            return C__RECORD_STATUS__MASS_CHANGES_TEMPLATE;
        } else {
            if ($p_current_status == C__RECORD_STATUS__BIRTH) {
                $this->set_object_status($p_object_id, C__RECORD_STATUS__NORMAL);

                return C__RECORD_STATUS__NORMAL;
            }
        }
    }

    /**
     * Checks whether SYSID already exists.
     *
     * @param   string $p_sysid
     *
     * @return  boolean
     */
    public function sysid_exists($p_sysid)
    {
        if (!empty($p_sysid)) {
            return (count($this->retrieve("SELECT isys_obj__id FROM isys_obj WHERE isys_obj__sysid = " . $this->convert_sql_text($p_sysid) . ";")) > 0);
        }

        return false;
    }

    /**
     * Checks whether title already exists.
     *
     * @param   string  $p_title         Title
     * @param   integer $p_exclude       (optional) Excluded object identifier. Defaults to null.
     * @param   integer $p_record_status (optional) Record status. Defaults to null.
     *
     * @return  boolean
     */
    public function object_title_exists($p_title, $p_exclude = null, $p_record_status = null)
    {
        $l_obj = $this->get_obj_id_by_title($p_title, null, $p_record_status);

        return ($l_obj > 0 && ($p_exclude > 0 && $l_obj != $p_exclude));
    }

    /**
     * Retrieve selected tags
     *
     * @param            $p_obj_id
     * @param bool|false $p_as_array
     *
     * @return array|isys_component_dao_result
     * @throws isys_exception_database
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_assigned_tag($p_obj_id, $p_as_array = false)
    {
        $l_sql = 'SELECT isys_tag__id FROM isys_tag_2_isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        $l_res = $this->retrieve($l_sql);

        if ($p_as_array) {
            $l_return = [];
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_row['isys_tag__id'];
            }

            return $l_return;
        }

        return $l_res;
    }

    /**
     * Deletes tags assignment
     *
     * @param $p_obj_id
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function remove_tag_assignment($p_obj_id)
    {
        $l_sql = 'DELETE FROM isys_tag_2_isys_obj WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Assigns tags to object
     *
     * @param $p_obj_id
     * @param $p_tags
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function assign_tag($p_obj_id, $p_tags)
    {
        $l_return = false;
        $l_tags = [];

        if (is_array($p_tags)) {
            $l_tags = $p_tags;
        } elseif (isys_format_json::is_json_array($p_tags)) {
            $l_tags = isys_format_json::decode($p_tags);
        } elseif (is_string($p_tags) && strpos($p_tags, ',')) {
            $l_tags = explode(',', $p_tags);
        } elseif (is_numeric($p_tags)) {
            $l_tags = [$p_tags];
        }

        if ($this->remove_tag_assignment($p_obj_id)) {
            $l_return = true;

            if (is_array($l_tags) && count($l_tags)) {
                $l_dialog_dao = isys_factory_cmdb_dialog_dao::get_instance('isys_tag', isys_application::instance()->database);

                $l_inserts = [];

                foreach ($l_tags as $l_tag_id) {
                    if (is_numeric($l_tag_id) && $l_dialog_dao->get_data($l_tag_id)) {
                        $l_inserts[] = '(' . $this->convert_sql_id($p_obj_id) . ', ' . $this->convert_sql_id($l_tag_id) . ')';
                    }
                }

                if (count($l_inserts)) {
                    $l_return = ($this->update('INSERT INTO isys_tag_2_isys_obj (isys_obj__id, isys_tag__id) VALUES ' . implode(',', $l_inserts)) && $this->apply_update());
                }
            }
        }

        return $l_return;
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_created' => new DynamicProperty(
                'LC__TASK__DETAIL__WORKORDER__CREATION_DATE',
                'isys_obj__created',
                'isys_obj',
                [
                    $this,
                    'dynamic_property_callback_created'
                ]
            ),
            '_changed' => new DynamicProperty(
                'LC__CMDB__LAST_CHANGE',
                'isys_obj__updated',
                'isys_obj',
                [
                    $this,
                    'dynamic_property_callback_changed'
                ]
            ),
            '_tag' => new DynamicProperty(
                'LC__CMDB__CATG__GLOBAL_TAG',
                'isys_obj__id',
                'isys_obj',
                [
                    $this,
                    'getAssignedTags'
                ]
            )
        ];
    }

    /**
     * Fetches category data from database.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_obj " . "LEFT JOIN isys_catg_global_list ON isys_catg_global_list__isys_obj__id = isys_obj__id " .
            "INNER JOIN isys_cmdb_status ON isys_obj__isys_cmdb_status__id = isys_cmdb_status__id " .
            "INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id " .
            "LEFT OUTER JOIN isys_catg_global_category ON isys_catg_global_list__isys_catg_global_category__id = isys_catg_global_category__id " .
            "LEFT OUTER JOIN isys_purpose ON isys_catg_global_list__isys_purpose__id = isys_purpose__id " . "WHERE TRUE " . $p_condition . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= "AND isys_catg_global_list__id = " . $this->convert_sql_id($p_catg_list_id) . " ";
        }

        if ($p_status !== null) {
            $l_sql .= "AND (isys_catg_global_list__status = " . $this->convert_sql_int($p_status) . ")";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'id'          => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                    C__PROPERTY__DATA__INDEX       => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__OBJ__ID'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false
                ]
            ]),
            'title' => (new TextProperty(
                'C__CATG__GLOBAL_TITLE',
                'LC__UNIVERSAL__TITLE',
                'isys_obj__title',
                'isys_obj'
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    'CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')'
                ),
                Property::C__PROPERTY__DATA__JOIN => [],
                Property::C__PROPERTY__DATA__INDEX => true
            ]),
            'status' => (new DialogDataProperty(
                'C__OBJ__STATUS',
                'LC__UNIVERSAL__CONDITION',
                'isys_obj__status',
                'isys_obj',
                new isys_callback([
                    'isys_cmdb_dao_category_g_global',
                    'callback_property_status'
                ])
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT   => SelectSubSelect::factory('(CASE ' .
                    implode(' ', array_map(function ($item, $key) {
                        return ' WHEN isys_obj__status = ' . $this->convert_sql_int($key) . ' THEN ' . $this->convert_sql_text($item);
                    }, $this->callback_property_status(), array_keys($this->callback_property_status()))) . ' END)'),
                Property::C__PROPERTY__DATA__JOIN     => [],
                Property::C__PROPERTY__DATA__READONLY => true,
                Property::C__PROPERTY__DATA__INDEX    => true,
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__SEARCH     => false,
                C__PROPERTY__PROVIDES__VALIDATION => false,
                C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                C__PROPERTY__PROVIDES__IMPORT     => false
            ]),
            'created'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                    C__PROPERTY__INFO     => [
                        C__PROPERTY__INFO__TITLE       => 'LC__TASK__DETAIL__WORKORDER__CREATION_DATE',
                        C__PROPERTY__INFO__DESCRIPTION => 'Created'
                    ],
                    C__PROPERTY__DATA     => [
                        C__PROPERTY__DATA__FIELD       => 'isys_obj__created',
                        C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                        C__PROPERTY__DATA__READONLY    => true,
                        C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('CONCAT(' .
                            self::build_query_date_format('isys_obj__created', true) . ', \' (\' , isys_obj__created_by, \')\')'),
                        C__PROPERTY__DATA__SORT_ALIAS  => 'obj_main.isys_obj__created'
                    ],
                    C__PROPERTY__UI       => [
                        C__PROPERTY__UI__ID     => 'C__CATG__GLOBAL_CREATED',
                        C__PROPERTY__UI__PARAMS => [
                            'p_bReadonly' => true,
                            'default'     => 'n/a'
                        ]
                    ],
                    C__PROPERTY__PROVIDES => [
                        C__PROPERTY__PROVIDES__SEARCH     => false,
                        C__PROPERTY__PROVIDES__LIST       => true,
                        C__PROPERTY__PROVIDES__REPORT     => true,
                        C__PROPERTY__PROVIDES__VALIDATION => false,
                        C__PROPERTY__PROVIDES__MULTIEDIT  => false
                    ]
                ]),
            'created_by'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CREATED_BY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Created by'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__created_by',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                    C__PROPERTY__DATA__READONLY    => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__GLOBAL_CREATED_BY'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'changed'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LAST_CHANGE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Changed'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__SORT_ALIAS  => 'isys_obj__updated',
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__updated',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                    C__PROPERTY__DATA__READONLY    => true,
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('CONCAT(' .
                        self::build_query_date_format('isys_obj__updated', true) . ', \' (\' , isys_obj__updated_by, \')\')'),
                    C__PROPERTY__DATA__INDEX       => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GLOBAL_UPDATED',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bReadonly' => true,
                        'default'     => 'n/a'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false
                ]
            ]),
            'changed_by'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LAST_CHANGE_BY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Changed'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__SORT_ALIAS  => 'isys_obj__updated_by',
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__updated_by',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                    C__PROPERTY__DATA__READONLY    => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__GLOBAL_UPDATED_BY'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false
                ]
            ]),
            'purpose'     => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_PURPOSE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Purpose'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_global_list__isys_purpose__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_purpose',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_purpose',
                        'isys_purpose__id'
                    ],
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_purpose__title
                                FROM isys_catg_global_list
                                JOIN isys_purpose
                                  ON isys_catg_global_list__isys_purpose__id = isys_purpose__id',
                        'isys_catg_global_list',
                        'isys_catg_global_list__id',
                        'isys_catg_global_list__isys_obj__id',
                        'isys_purpose__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_global_list', 'LEFT', 'isys_catg_global_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_purpose', 'LEFT', 'isys_catg_global_list__isys_purpose__id', 'isys_purpose__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GLOBAL_PURPOSE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_purpose'
                    ]
                ]
            ]),
            'category'    => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_CATEGORY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Category'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_global_list__isys_catg_global_category__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_catg_global_category',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_catg_global_category',
                        'isys_catg_global_category__id'
                    ],
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_global_category__title
                                FROM isys_catg_global_list
                                JOIN isys_catg_global_category
                                  ON isys_catg_global_list__isys_catg_global_category__id = isys_catg_global_category__id',
                        'isys_catg_global_list',
                        'isys_catg_global_list__id',
                        'isys_catg_global_list__isys_obj__id',
                        'isys_catg_global_category__id'
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_global_list', 'LEFT', 'isys_catg_global_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_global_category',
                            'LEFT',
                            'isys_catg_global_list__isys_catg_global_category__id',
                            'isys_catg_global_category__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GLOBAL_CATEGORY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable' => 'isys_catg_global_category'
                    ]
                ]
            ]),
            'sysid'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_SYSID',
                    C__PROPERTY__INFO__DESCRIPTION => 'SYSID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__sysid',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                    C__PROPERTY__DATA__INDEX       => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GLOBAL_SYSID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bDisabled' => '1'
                    ]
                ]
            ]),
            'cmdb_status' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__CMDB_STATUS',
                    C__PROPERTY__INFO__DESCRIPTION => 'CMDB status'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_obj__isys_cmdb_status__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_cmdb_status',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_cmdb_status',
                        'isys_cmdb_status__id'
                    ],
                    C__PROPERTY__DATA__READONLY     => true,
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT("{#", isys_cmdb_status__color, "} ", isys_cmdb_status__title) FROM isys_cmdb_status',
                        'isys_cmdb_status',
                        'isys_cmdb_status__id',
                        '',
                        'isys_cmdb_status__id'
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__OBJ__CMDB_STATUS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strTable'   => 'isys_cmdb_status',
                        'condition'    => 'isys_cmdb_status__id NOT IN (' . defined_or_default('C__CMDB_STATUS__IDOIT_STATUS') . ', ' . defined_or_default('C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE') . ')',
                        'default'      => 'n/a',
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'type'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__REPORT__FORM__OBJECT_TYPE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object-Type'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD      => 'isys_obj__isys_obj_type__id',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj_type',
                        'isys_obj_type__id'
                    ],
                    C__PROPERTY__DATA__INDEX      => true,
                    C__PROPERTY__DATA__READONLY   => true,
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj_type__title FROM isys_obj_type',
                        'isys_obj_type',
                        'isys_obj_type__id',
                        '',
                        'isys_obj_type__id'
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__OBJ__TYPE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'     => new isys_callback([
                            'isys_cmdb_dao_category_g_global',
                            'callback_property_type'
                        ]),
                        'default'      => 'n/a',
                        'p_bDbFieldNN' => 1
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false
                ]
            ]),
            'tag'         => array_replace_recursive(isys_cmdb_dao_category_pattern::multiselect(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__GLOBAL_TAG',
                    C__PROPERTY__INFO__DESCRIPTION => 'Tag'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD        => 'isys_obj__id',
                    C__PROPERTY__DATA__TABLE_ALIAS  => 'global_tag',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_tag',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_tag_2_isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT tag.isys_tag__title
                                FROM isys_tag_2_isys_obj AS tobj
                                INNER JOIN isys_tag AS tag ON tag.isys_tag__id = tobj.isys_tag__id',
                        'isys_tag_2_isys_obj',
                        '',
                        'tobj.isys_obj__id',
                        'tobj.isys_obj__id',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['tobj.isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__INDEX        => true
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__GLOBAL_TAG',
                    C__PROPERTY__UI__PARAMS  => [
                        'type'           => 'f_popup',
                        'p_strPopupType' => 'dialog_plus',
                        'p_strTable'     => 'isys_tag',
                        'emptyMessage'   => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__GLOBAL__NO_TAGS_FOUND'),
                        'p_onComplete'   => "idoit.callbackManager.triggerCallback('cmdb-catg-global-tag-update', selected);",
                        'multiselect'    => true
                    ],
                    C__PROPERTY__UI__DEFAULT => null
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT       => true,
                    C__PROPERTY__PROVIDES__LIST         => true,
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => true,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT    => true
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__MANDATORY  => false,
                    C__PROPERTY__CHECK__VALIDATION => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'dialog_multiselect'
                    ]
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__description',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_obj',
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__GLOBAL', 'C__CATG__GLOBAL')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param  array   $p_category_data Values of category data to be saved.
     * @param  integer $p_object_id     Current object identifier (from database)
     * @param  integer $p_status        Decision whether category data shouldx be created or just updated.
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_overwrite_sysid = true;

            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $l_data_id = $this
                    ->retrieve("SELECT isys_catg_global_list__id FROM isys_catg_global_list WHERE isys_catg_global_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . " LIMIT 1")
                    ->get_row_value('isys_catg_global_list__id');

                if ($l_data_id === null) {
                    return false;
                } else {
                    $p_category_data['data_id'] = $l_data_id;
                }

                $p_category_data['properties']['sysid'][C__DATA__VALUE] = null;
                $l_overwrite_sysid = false;
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                if (!empty($this->m_data)) {
                    if (is_array($this->m_data[0])) {
                        $l_catdata = $this->m_data[0];
                    } else {
                        $l_catdata = $this->m_data;
                    }
                } else {
                    $l_catdata_dao = $this->get_data_by_object($p_object_id);
                    $l_catdata = $l_catdata_dao->get_row();
                    $l_catdata_dao->free_result();
                }

                if (isset($l_catdata['isys_catg_global_list__id']) && $l_catdata['isys_catg_global_list__id']) {
                    if (!empty($l_catdata['isys_obj__sysid'])) {
                        $l_overwrite_sysid = false;
                    }

                    if (!is_numeric($p_category_data['properties']['cmdb_status'][C__DATA__VALUE])) {
                        if (defined($p_category_data['properties']['cmdb_status'][C__DATA__VALUE])) {
                            $p_category_data['properties']['cmdb_status'][C__DATA__VALUE] = constant($p_category_data['properties']['cmdb_status'][C__DATA__VALUE]);
                        } else {
                            $p_category_data['properties']['cmdb_status'][C__DATA__VALUE] = null;
                        }
                    }

                    // Save category data:
                    $this->save(
                        $l_catdata['isys_catg_global_list__id'], // Using isys_catg_global_list__id fixes ID-835
                        $l_catdata['isys_obj__status'],
                        $p_category_data['properties']['title'][C__DATA__VALUE],
                        $p_category_data['properties']['sysid'][C__DATA__VALUE],
                        $p_category_data['properties']['category'][C__DATA__VALUE],
                        $p_category_data['properties']['purpose'][C__DATA__VALUE],
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_object_id,
                        $l_overwrite_sysid,
                        $p_category_data['properties']['cmdb_status'][C__DATA__VALUE],
                        $p_category_data['properties']['type'][C__DATA__VALUE],
                        $p_category_data['properties']['tag'][C__DATA__VALUE]
                    );

                    return $l_catdata['isys_catg_global_list__id'];
                }
            }
        }

        return false;
    }

    /**
     * Validates data sent by HTTP POST.
     *
     * @return  boolean
     * @todo    Get rid of it.
     */
    public function validate_user_data()
    {
        $l_retValid = true;
        $l_arrTomAdditional = [];

        if (!C__SYSID__READONLY && C__SYSID__UNIQUE) {
            $l_data = $this->get_general_data();

            if ($l_data["isys_obj__sysid"] != $_POST["C__CATG__GLOBAL_SYSID"] && $this->sysid_exists($_POST["C__CATG__GLOBAL_SYSID"])) {
                // @todo  Check if "p_strInfoIconError" can be removed.
                $l_arrTomAdditional["C__CATG__GLOBAL_SYSID"]["p_strInfoIconError"] = "SYS-ID already exists";
                $l_arrTomAdditional["C__CATG__GLOBAL_SYSID"]["message"] = "SYS-ID already exists";
                $l_retValid = false;
            }
        }

        if (isys_tenantsettings::get('cmdb.unique.object-title')) {
            if ($this->object_title_exists($_POST["C__CATG__GLOBAL_TITLE"], $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL)) {
                // @todo  Check if "p_strInfoIconError" can be removed.
                $l_arrTomAdditional["C__CATG__GLOBAL_TITLE"]["p_strInfoIconError"] = isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__GLOBAL__TITLE_EXISTS");
                $l_arrTomAdditional["C__CATG__GLOBAL_TITLE"]["message"] = isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__CATG__GLOBAL__TITLE_EXISTS");
                $l_retValid = false;
            }
        }

        // @todo Maybe use "trim()" here?
        if ($_POST["C__CATG__GLOBAL_TITLE"] === '') {
            // @todo  Check if "p_strInfoIconError" can be removed.
            $l_arrTomAdditional["C__CATG__GLOBAL_TITLE"]["p_strInfoIconError"] = isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL_ERROR__SELECT_NAME');
            $l_arrTomAdditional["C__CATG__GLOBAL_TITLE"]["message"] = isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL_ERROR__SELECT_NAME');
            $l_retValid = false;
        }

        $this->set_additional_rules($l_retValid == false ? $l_arrTomAdditional : null);
        $this->set_validation($l_retValid);

        // When the return value is true, we'll also check the parent "validate_user_data" method.
        if ($l_retValid) {
            $l_retValid = parent::validate_user_data();
        }

        return $l_retValid;
    }

    /**
     * Dynamic property handling for getting the last change time of an object.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function getAssignedTags(array $data)
    {
        $tagProperty = $this->get_property_by_key('tag');

        $objectId = ($data["isys_catg_global_list__isys_obj__id"] ?: ($data["isys_obj__id"] ?: null));

        // In order to sort the fields correctly, surrounding elements are not allowed.
        if ($objectId !== null) {
            /**
             * @var $selectObject idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
             */
            $selectObject = $tagProperty[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
            $result = $this->retrieve($selectObject->getSelectQuery() . ' WHERE ' . $selectObject->getSelectReferenceKey() . ' = ' .
                $this->convert_sql_id($objectId));
            $return = [];
            while ($tagData = $result->get_row()) {
                $return[] = $tagData['isys_tag__title'];
            }

            return implode(', ', $return);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }
}
