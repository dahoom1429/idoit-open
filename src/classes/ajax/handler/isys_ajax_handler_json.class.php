<?php

/**
 * JSON Data Interface
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_json extends isys_ajax_handler
{
    public function init()
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $_GET = $this->m_get;
        $_POST = $this->m_post;

        $l_condition = '';
        $l_use_auth = (isset($_GET['useAuth']) && $_GET['useAuth']);
        $l_auth_condition = $l_use_auth ? ' ' . isys_auth_cmdb_objects::instance()->get_allowed_objects_condition() . ' ' : '';

        // Changing the memory limit, if possible/necessary. See ID-793
        $l_current_memory_limit = isys_convert::to_bytes(ini_get('memory_limit'));
        $l_desired_memory_limit = isys_convert::to_bytes(isys_tenantsettings::get('system.memory-limit.object-lists', '1024M'));

        if ($l_current_memory_limit < $l_desired_memory_limit) {
            ini_set('memory_limit', isys_tenantsettings::get('system.memory-limit.object-lists', '1024M'));
        }

        header('Content-Type: application/json');
        $l_return = [];

        // With a select structure, you can define which fields you would like to return.
        if (isset($_GET['select'])) {
            $l_select = isys_format_json::decode($_GET['select']);
        } else {
            $l_select = false;
        }

        if (isset($_GET[isys_popup_browser_object_ng::C__CMDB_FILTER])) {
            $l_status = explode(';', $_GET[isys_popup_browser_object_ng::C__CMDB_FILTER]);
            $l_status_array = [];

            foreach ($l_status as $l_cmdb_status) {
                if (defined($l_cmdb_status)) {
                    $l_status_array[] = (int)constant($l_cmdb_status);
                }
            }

            if (count($l_status_array) > 0) {
                $l_condition = ' AND isys_obj__isys_cmdb_status__id IN (' . implode(',', $l_status_array) . ') ';
            }
        }

        switch ($_GET['action']) {
            case 'getObjectsByCustomBrowserRequest':
                if (isset($_GET['request'])) {
                    $l_request = isys_format_json::decode($_GET['request']);

                    if (isset($l_request['callFunction'])) {
                        $l_filter = explode('::', $l_request['callFunction']);

                        if (count($l_filter) > 1 && class_exists($l_filter[0])) {
                            $l_filterObject = new $l_filter[0]($database);

                            if (method_exists($l_filterObject, $l_filter[1])) {
                                $l_return = $l_filterObject->{$l_filter[1]}(isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST, $l_request);
                            }
                        }
                    }
                }
                break;

            case 'filter':
                if ($_GET['filter'] !== '') {
                    $l_return = isys_cmdb_dao_category_g_global::instance($database)
                        ->search_objects(urldecode($_GET['filter']), $_GET['typeFilter'], $_GET['groupFilter'], $l_auth_condition);
                }
                break;

            case 'createObject':
                $objectTitle = $_POST['objectTitle'];
                $objectTypeId = $_POST['objectTypeID'];

                if ($objectTitle && $objectTypeId) {
                    $defaultTemplateId = false;

                    if ($_POST['useDefaultTemplate']) {
                        // Get template module.
                        $defaultTemplateId = isys_application::instance()->container->get('cmdb_dao')
                            ->get_default_template_by_obj_type($objectTypeId);
                    }

                    if (is_numeric($defaultTemplateId) && $defaultTemplateId > 0) {
                        $l_object_id = (new isys_module_templates())->create_from_template(
                            [$defaultTemplateId],
                            $objectTypeId,
                            $objectTitle,
                            null,
                            false
                        );
                    } else {
                        $l_dao = isys_cmdb_dao_category_g_global::instance($database);
                        $l_object_id = $l_dao->create_object($objectTitle, $objectTypeId);

                        $l_catID = $l_dao
                            ->retrieve('SELECT isys_catg_global_list__id FROM isys_catg_global_list WHERE isys_catg_global_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_object_id))
                            ->get_row_value('isys_catg_global_list__id');

                        // Emit category signal afterCategoryEntrySave
                        isys_component_signalcollection::get_instance()
                            ->emit('mod.cmdb.afterCategoryEntrySave', $l_dao, $l_catID, ($l_catID > 0), $l_object_id, $_POST, []);
                    }

                    echo $l_object_id;
                } else {
                    if (!$objectTitle) {
                        isys_notify::error($language->get('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TITLE'), ['life' => 10]);
                    }

                    if (!$objectTypeId) {
                        isys_notify::error($language->get('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TYPE'), ['life' => 10]);
                    }

                    echo -1;
                }

                die();

                break;

            case 'createObjectGroup':
                if (!isset($_POST['objects'])) {
                    isys_notify::error('Request error');
                    die;
                }

                if (!isset($_POST['objectTitle']) || empty($_POST['objectTitle'])) {
                    isys_notify::warning($language->get('LC__TEMPLATES__NO_TITLE_GIVEN'));
                    die;
                }

                $objectIds = json_decode($_POST['objects']);

                if (!is_array($objectIds) || !count($objectIds)) {
                    isys_notify::warning($language->get('LC__CMDB__OBJECT_BROWSER__OBJECT_GROUP_NO_OBJECTS'));
                    die;
                }

                $l_dao = new isys_cmdb_dao($database);

                if ($_POST['forceOverwrite'] || !$l_dao->get_obj_id_by_title($_POST['objectTitle'], defined_or_default('C__OBJECT_TYPE__GROUP'))) {
                    $l_group_id = $l_dao->create_object($_POST['objectTitle'], defined_or_default('C__OBJECT_TYPE__GROUP'));

                    $l_dao_group = new isys_cmdb_dao_category_s_group($database);

                    foreach ($objectIds as $objectId) {
                        $l_dao_group->create($l_group_id, C__RECORD_STATUS__NORMAL, $objectId, '');
                    }

                    echo $l_group_id;
                } else {
                    echo json_encode(['exists' => true]);
                }

                die;

            case 'getRelationsByObjectId':
                $l_result = [];
                $l_objects = explode(';', trim($_POST['request'], ';'));

                $l_relation_dao = new isys_cmdb_dao_category_g_relation($database);

                if (count($l_objects) > 1) {
                    $objectId = $l_objects;
                } else {
                    $objectId = current($l_objects);
                }

                $l_relation_res = $l_relation_dao->get_data(null, $objectId, $l_condition);

                $amount = $l_relation_res->count();

                while ($relationData = $l_relation_res->get_row()) {
                    $selectedObjectId = (in_array(
                        $relationData['isys_catg_relation_list__isys_obj__id__master'],
                        $l_objects
                    ) ? $relationData['isys_catg_relation_list__isys_obj__id__master'] : $relationData['isys_catg_relation_list__isys_obj__id__slave']);

                    if ($amount < 10) {
                        $l_obj_name = $l_relation_dao->get_obj_name_by_id_as_string($selectedObjectId);

                        // If the object has no name, we need something to display.
                        if (empty($l_obj_name)) {
                            $l_obj_name = '(' . $language->get('LC__UNIVERSAL__NO_TITLE') . ' - ID ' . $selectedObjectId . ')';
                        }
                    } elseif (!isset($l_obj_name)) {
                        $l_obj_name = $language->get($l_relation_dao->get_obj_type_name_by_obj_id($selectedObjectId));
                    }

                    if (isset($l_return[$l_obj_name][$relationData['isys_relation_type__id']])) {
                        continue;
                    }

                    $l_return[$l_obj_name][$relationData['isys_relation_type__id']] = '- ' . _L($relationData['isys_relation_type__title']);
                }

                // Because JSON has some problems with utf8 encoded strings as key, we have to return everything as plain array.
                foreach ($l_return as $l_object => $l_categories) {
                    $l_result[] = $l_object;

                    foreach ($l_categories as $l_category) {
                        $l_result[] = $l_category;
                    }

                    // We use this for a nice blank line after each category-list.
                    $l_result[] = '';
                }

                echo isys_format_json::encode($l_result);
                $this->_die();
                break;

            case 'hasEditRightsByObjectType':
                // Checks if user has edit rights for the selected object type.
                $l_id = $l_constant = null;

                if (is_numeric($_POST['objTypeID'])) {
                    $l_id = $_POST['objTypeID'];
                } else {
                    if (is_string($_POST['objTypeID'])) {
                        $l_constant = $_POST['objTypeID'];
                    }
                }

                if (empty($_POST['right'])) {
                    $l_right = 'isys_auth::EDIT';
                } else {
                    $l_right = $_POST['right'];
                }

                $l_blindly_allow = false;
                $l_objtype = isys_cmdb_dao::instance($database)
                    ->get_object_type($l_id, $l_constant);

                if (($l_id === null && $l_constant === null) || !is_array($l_objtype)) {
                    // Somehow we did not receive an ID, a constant or a object-type result...
                    $l_blindly_allow = true;
                }

                if ($l_blindly_allow || isys_auth_cmdb::instance()
                        ->is_allowed_to(constant($l_right), 'OBJ_IN_TYPE/' . $l_objtype['isys_obj_type__const'])) {
                    $l_result = [
                        'success' => true,
                        'message' => null
                    ];
                } else {
                    $l_result = [
                        'success' => false,
                        'message' => $language->get('LC__AUTH__EXCEPTION__MISSING_RIGHTS_TO_CREATE_OBJECTTYPE', [
                            $language->get(isys_auth::get_right_name(constant($l_right))),
                            $language->get($l_objtype['isys_obj_type__title'])
                        ])
                    ];
                }

                echo isys_format_json::encode($l_result);
                die;

            case 'load_object_data':
                try {
                    $l_data = $l_objects_sort = [];
                    $l_objects = isys_format_json::decode($_POST['objects']);

                    if (is_countable($l_objects) && count($l_objects) > 0) {
                        $l_res = isys_cmdb_dao_category_g_global::instance($database)
                            ->get_data(null, $l_objects);

                        if (is_countable($l_res) && count($l_res) > 0) {
                            while ($l_row = $l_res->get_row()) {
                                $l_data[$l_row['isys_obj__id']] = [
                                    'id'         => $l_row['isys_obj__id'],
                                    'title'      => $l_row['isys_obj__title'],
                                    'type_title' => $language->get($l_row['isys_obj_type__title'])
                                ];
                            }
                        }

                        $l_objects_sort = array_flip($l_objects);
                    }

                    // Awesome PHP 5.3 code for sorting the resultset.
                    uksort($l_data, function ($l_a, $l_b) use ($l_objects_sort) {
                        return $l_objects_sort[$l_a] > $l_objects_sort[$l_b];
                    });

                    $l_return = [
                        'success' => true,
                        'message' => null,
                        'data'    => array_values($l_data)
                    ];
                } catch (Exception $e) {
                    $l_return = [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'data'    => null
                    ];
                }

                break;

            default:
                /* Process Parameters */
                if ((isset($_GET[C__CMDB__GET__OBJECT]) || isset($_GET["condition"])) && ($_GET[C__CMDB__GET__CATS] || $_GET[C__CMDB__GET__CATG])) {
                    // default
                    $l_get_param = C__CMDB__GET__CATG;
                    $l_cat_suffix = 'g';

                    if ($_GET[C__CMDB__GET__CATS]) {
                        $l_get_param = C__CMDB__GET__CATS;
                        $l_cat_suffix = "s";
                    } else {
                        if ($_GET[C__CMDB__GET__CATG]) {
                            $l_get_param = C__CMDB__GET__CATG;
                            $l_cat_suffix = "g";
                        }
                    }

                    $l_dao = new isys_cmdb_dao($database);
                    $l_isysgui = $l_dao->get_isysgui("isysgui_cat" . $l_cat_suffix, $database->escape_string($_GET[$l_get_param]))
                        ->__to_array();

                    /* Check class and instantiate it */
                    if (class_exists($l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"])) {

                        /* Process data */
                        if (($l_cat = new $l_isysgui["isysgui_cat{$l_cat_suffix}__class_name"]($database))) {
                            if (isset($_GET["method"])) {
                                $l_method = "get_" . $_GET["method"];
                            } else {
                                $l_method = "get_data";
                            }

                            if (method_exists($l_cat, $l_method)) {
                                $l_condition = $l_auth_condition . ' ' . $l_condition;

                                if (isset($_GET["condition"])) {
                                    $l_return = $l_cat->$l_method(null, null, $l_condition . urldecode($_GET["condition"]));
                                } else {
                                    $l_return = $l_cat->$l_method(null, $database->escape_string($_GET[C__CMDB__GET__OBJECT]), $l_condition);
                                }
                            } else {
                                $l_return[] = "Method does not exist";
                            }
                        }
                    }
                } else {
                    if ($_GET[C__CMDB__GET__OBJECT]) {
                        // @todo Where is this ever used? Please remove if possible.
                        $l_quicky = new isys_ajax_handler_quick_info();

                        $l_catg = filter_defined_constants([
                            'C__CATG__GLOBAL',
                            'C__CATG__CONTACT',
                            'C__CATG__MODEL',
                            'C__CATG__CPU',
                            'C__CATG__NETWORK'
                        ]);

                        $l_quicky->get_quick_info_content($_GET[C__CMDB__GET__OBJECT], $l_catg);
                        $l_qc = $l_quicky->get_info_array();

                        $l_dao = isys_cmdb_dao_category_g_global::instance($database);

                        $l_return["objtype"] = $language->get($l_dao->get_objtype_name_by_id_as_string($l_dao->get_objTypeID($_GET[C__CMDB__GET__OBJECT])));

                        if (defined('C__CATG__GLOBAL')) {
                            $l_return["title"] = $l_qc['g' . constant('C__CATG__GLOBAL')]["Name"];
                            $l_return["sysid"] = $l_qc['g' . constant('C__CATG__GLOBAL')]["SYS-ID"];
                        }
                        if (defined('C__CATG__MODEL')) {
                            $l_return["model"] = $l_qc['g' . constant('C__CATG__MODEL')]["LC__CMDB__CATG__MODEL_TITLE"];
                        }
                        if (defined('C__CATG__CPU')) {
                            $l_return["cpu_title"] = $l_qc['g' . constant('C__CATG__CPU')]["LC__CMDB__CATG__CPU_TITLE"];
                            $l_return["cpu_type"] = $l_qc['g' . constant('C__CATG__CPU')]["LC__CMDB__CATG__CPU_TYPE"];
                        }
                        if (defined('C__CATG__NETWORK')) {
                            $l_return["ip_address"] = $l_qc['g' . constant('C__CATG__NETWORK')]["LC__CATP__IP__ADDRESS"][0];
                            $l_return["interface"] = $l_qc['g' . constant('C__CATG__NETWORK')]["Interface"][0];
                            $l_return["netmask"] = $l_qc['g' . constant('C__CATG__NETWORK')]["LC__CMDB__CATS__NET__MASK"][0];
                        }
                        echo "[" . isys_format_json::encode($l_return) . "]";
                        $this->_die();
                    } else {
                        $l_condition = $l_auth_condition . ' ' . $l_condition;

                        $l_dao = new isys_cmdb_dao($database);
                        $l_data = $l_dao->get_objects_by_type_id($_GET[C__CMDB__GET__OBJECTTYPE], C__RECORD_STATUS__NORMAL, null, '', null, $l_condition);

                        while ($l_row = $l_data->get_row()) {
                            if ($l_row["isys_obj__title"] && $l_row["isys_obj__id"]) {
                                // Check for a predefined select.
                                if (is_object($l_select) || is_array($l_select)) {
                                    $l_rowdata = [];

                                    foreach ($l_select as $l_key => $l_value) {
                                        $l_rowdata[$language->get($l_value)] = $language->get($l_row[$l_key]);
                                    }

                                    $l_return[] = $l_rowdata;
                                } else {
                                    if ($_GET["raw"]) {
                                        $l_return[] = [
                                            "isys_obj__id"         => $l_row["isys_obj__id"],
                                            "isys_obj__title"      => $l_row["isys_obj__title"],
                                            "isys_obj__sysid"      => $l_row["isys_obj__sysid"],
                                            "isys_obj_type__title" => $language->get($l_row["isys_obj_type__title"]),
                                        ];
                                    } else {
                                        $l_return[] = [
                                            "id"    => $l_row["isys_obj__id"],
                                            "title" => $l_row["isys_obj__title"],
                                            "sysid" => $l_row["isys_obj__sysid"],
                                            "type"  => $l_row["isys_obj__isys_obj_type__id"]
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }

        // Check if the response variable is a dao result to handle the output generically.
        if (is_object($l_return) && is_a($l_return, "isys_component_dao_result")) {
            // Format data.
            while ($l_row = $l_return->get_row()) {
                if (is_object($l_select) || is_array($l_select)) {
                    $l_rowdata = [];
                    foreach ($l_select as $l_key => $l_value) {
                        $l_rowdata[$language->get($l_value)] = $language->get($l_row[$l_key]);
                    }

                    $l_ar_return[] = $l_rowdata;
                } else {
                    $l_ar_return[] = $l_row;
                }
            }

            $l_return = &$l_ar_return;
        }

        // Return an empty json array if there are no results.
        if (empty($l_return)) {
            $l_return = [];
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }
}
