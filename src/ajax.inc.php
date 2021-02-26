<?php
/**
 * Handler for AJAX requests.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      i-doit-team
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
switch ($_GET["request"]) {
    case "breadcrumb":

        // Enable cache lifetime of 7 days
        isys_core::expire(isys_convert::WEEK);

        isys_application::instance()->container->get('session')->write_close()->include_mandator_cache();

        // Prepare module request and initialize module manager
        isys_module_manager::instance()
            ->init(isys_module_request::get_instance());

        $l_module_id = (isset($_GET[C__GET__MODULE_ID])) ? $_GET[C__GET__MODULE_ID] : defined_or_default('C__MODULE__CMDB', 2);

        $l_breadcrumb = new isys_component_template_breadcrumb();

        echo stripslashes($l_breadcrumb->include_home()
            ->process(false, '</li>', $l_module_id, '<li>'));
        die;

        break;

    case "mydoit":
    case "mydoit_addBookmark":
    case "mydoit_deleteBookmark":
    case "mysearch_viewCriterias":
    case "mysearch_addCriterion":
    case "mysearch_delCriterion":
        if (defined('C__MODULE__PRO')) {
            $g_output_done = true;
            include_once("hypergate.inc.php");
            require_once("classes/modules/pro/mydoit/my-doit.inc.php");
            isys_application::instance()->container->get('template')->display("file:content/my-doit.tpl");
            die;
        }
        break;

    case "toObjectView":
    case "toLocationView":
        break;

    case "object":
        $l_dao = new isys_cmdb_dao(isys_application::instance()->container->get('database'));

        if ($_GET[C__CMDB__GET__OBJECT]) {
            $l_out = "";

            if ($_GET["objtype"] == "1") {
                $l_type_id = $l_dao->get_objTypeID($_GET[C__CMDB__GET__OBJECT]);
                if ($l_type_id > 0) {
                    $l_out .= isys_application::instance()->container->get('language')
                            ->get($l_dao->get_objtype_name_by_id_as_string($l_type_id)) . ": ";
                }
            } else {
                $l_type_id = false;
            }

            $l_object_name = $l_dao->get_obj_name_by_id_as_string($_GET[C__CMDB__GET__OBJECT]);

            if ($l_object_name != "") {
                $l_out .= $l_object_name;
            } else {
                $l_out .= "N/A";
            }

            echo '<p class="bold">' . isys_glob_cut_string($l_out, 42) . '</p>';

            if ($l_type_id) {
                echo '<script type="javascript">if ($("obj_type")) {select_obj_type("' . $l_type_id . '");}</script>';
            }
        } else {
            echo isys_tenantsettings::get('gui.empty_value', '-');
        }

        die;

        break;
    default:
        $g_ajax = true;
        $g_output_done = true;

        /* Initialize the AJAX-Handler and call the request */
        $l_ajax = new isys_ajax($_GET, $_POST);

        /* Get locales class */
        include_once("locales.inc.php");

        /**
         * Check if ajax handler needs including the hypergate
         * note that hypergate automatically loads the CMDB module which can be a high overload.
         */
        if ($l_ajax->needs_hypergate($_GET[C__GET__AJAX_CALL])) {
            include_once("hypergate.inc.php");
        } else {
            isys_module_manager::instance()
                ->init(isys_module_request::get_instance());
        }

        /* Handle nag screen */
        if (class_exists('isys_module_licence')) {
            isys_module_licence::show_nag_screen();
        }

        $l_init = $l_ajax->init($_GET[C__GET__AJAX_CALL]);

        break;
}
